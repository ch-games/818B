<?php
if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}
//彩票核心处理model
class Fc_com_model extends MY_Model
{
    public function __construct(){
        $this->init_db();
    }

    //彩票中文标题
    public function fc_titles($type){
        //缓存redis
        $redis = RedisConPool::getInstace();
        $redis_key = 'fc_games_data';
        $fdata = $redis->get($redis_key);
        if ($fdata) {
            $fdata = json_decode($fdata,true);
            foreach ($fdata as $key => $val) {
                if ($val['type'] == $type) {
                    return $val['name'];
                }
            }
            return $fdata;
        }

        $this->public_db->where('state',1);
        $this->public_db->select("id,name,type,l_type");
        $fdata = $this->public_db->get('fc_games')->result_array();
        $fdata = $this->sort_fc_play($fdata);
        //写入redis
        $redis->set($redis_key,json_encode($fdata));
        foreach ($fdata as $key => $val) {
            if ($val['type'] == $type) {
                return $val['name'];
            }
        }
        return $fdata;
    }

    //彩票导航
    public function get_fc_header(){
        //彩票配置模块读取
        $redis = RedisConPool::getInstace();
        $redis_key = SITEID.'_'.INDEX_ID.'_web_config';
        $cdata = $redis->get($redis_key);
        if ($cdata) {
            $fc_module = json_decode($cdata,TRUE);
            if($fc_module['fc_module']){
                $fc_module = explode(",", $fc_module['fc_module']);
            }
        }else{
            $this->private_db->from('web_config');
            $this->private_db->where('site_id',SITEID);
            $this->private_db->where('index_id',INDEX_ID);
            $this->private_db->select('fc_module');
            $rows = $this->private_db->get()->row_array();
            $fc_module = explode(",", $rows['fc_module']);
        }
        $fc_module = array_unique($fc_module);
        
        //彩票游戏读取
        $fc_games = $this->fc_titles('');
        $fc_datas = array();
        //彩票过滤未开启类型
        foreach ($fc_module as $k => $v){
            foreach ($fc_games as $k1 => $v1){
                if($v == $v1['type']){
                    $fc_datas[$k]['id'] = $v1['id'];
                    $fc_datas[$k]['type'] = $v;
                    $fc_datas[$k]['name'] = $v1['name'];
                    $fc_datas[$k]['l_type'] = $v1['l_type'];
                    break;
                }
            }
        }
        
        $fc_datas = $this->sort_fc_play($fc_datas);
        
        if (count($fc_datas) == 0) {
            return FALSE;
        }
        return $fc_datas;
    }
    
    ///按照类别排序彩种
    public function sort_fc_play($fc_datas){
        $order = array(
            'yb','gpc','ssc','sf','11','k3','xy'
        );
        
        $order_fc_datas = array();
        
        foreach($order as $val){
           foreach($fc_datas as $v){
               if($v['l_type'] == $val){
                   $order_fc_datas[] = $v;
               }
           } 
        }
        return $order_fc_datas;
    }

    //彩票封盘时间
    public function get_fengpan_time($type, $adddate = '+12 hours'){
        $now_time = $this->fc_nowtime("H:i:s");
        $c_time = $this->fc_nowtime("Y-m-d");
        $now_time_day = $this->fc_nowtime("d");
        switch ($type) {
            case 'fc_3d':
                $where = "ok ='0'";
                break;
            case 'pl_3':
                $where = "ok ='0'";
                break;
            case 'liuhecai':
                $now_time = $this->fc_nowtime("Y-m-d H:i:s");
                $where = "ok ='0' and kaijiang > '" . $now_time . "' and kaipan < '" . $now_time . "'";
                break;
            default:
                $where = "ok ='0' and kaijiang > '" . $now_time . "'";
                break;
        }
        // 查询是否开盘
        $this->public_db->where($where);
        $this->public_db->select("*");
        $this->public_db->order_by('kaijiang', 'ASC');
        $data_time = $this->public_db->get($type.'_opentime')->result_array();
        $data_time = $data_time[0];

        if(empty($data_time)){
            $this->public_db->select("*");
            $this->public_db->order_by('kaijiang', 'ASC');
            $data_time = $this->public_db->get($type.'_opentime')->result_array();
            $data_time = $data_time[0];
        }
        
        if ($type != 'liuhecai') {
            $f_t = $data_time['fengpan'];
            $k_t = $data_time['kaijiang'];
            $o_t = $data_time['kaipan'];
            //封盘补丁
            if(($type == 'fc_3d' || $type == 'pl_3') && (strtotime($now_time) > strtotime($o_t) && strtotime($f_t) < strtotime($now_time)))
                $array['f_t_stro'] = strtotime($f_t)+24*60*60 - strtotime($now_time);
            else
                $array['f_t_stro'] = strtotime($f_t) - strtotime($now_time); // 距离封盘的时间
            
            if(strtotime($now_time) < strtotime($data_time['kaipan']) && $type != 'fc_3d' && $type != 'pl_3'){
                $array['f_t_stro'] = -1;
            }
            //距离开奖的时间
            $array['k_t_stro'] = strtotime($k_t) - strtotime($now_time);

            if((strtotime($k_t) - strtotime($now_time)) < 0){
                $array['k_t_stro'] = (strtotime($c_time.' '.$k_t)+24*60*60) - strtotime($c_time.' '.$now_time);
            }

            //距离开盘的时间
            $array['o_t_stro'] = strtotime($o_t) - strtotime($now_time);
            $array['f_state'] = $c_time. ' ' . $data_time['fengpan'];
            // 封盘状态判断时间
            $array['o_state'] = $c_time. ' ' . $data_time['kaipan']; // 开盘状态判断时间
            $array['c_time'] = $c_time. ' ' . $now_time;
        } else {
            $f_t = $data_time['fengpan'];
            $o_t = $data_time['kaipan'];
            $f_t_day = explode('-', $o_t);
            $f_t_day = $f_t_day[2];
            $left_hours = ($f_t_day - $now_time_day) * 24 * 60 * 60;
            // 距离下次开盘的天数换成秒

            $array['f_t_stro'] = strtotime($f_t) - strtotime($now_time); // 距离封盘的时间
            $array['o_t_stro'] = (strtotime($o_t) - strtotime($now_time)); // 距离开盘的时间
            $array['f_state'] = $data_time['fengpan']; // 封盘状态判断时间
            $array['o_state'] = $data_time['kaipan']; // 开盘状态判断时间
            $array['c_time'] = $now_time;
        }
        return $array;
    }

    //彩票美东时间转换中国时间
    public function fc_nowtime($type='',$hours = "+12 hours"){
        if(empty($type)){
            return date("H:i:s",strtotime($hours));
        }else{
            return date($type,strtotime($hours));
        }
    }

    //彩票一级玩法分类
    public function fc_games_type($fc_type){
        //缓存redis
        $redis = RedisConPool::getInstace();
        $redis_key = 'fc_games_type_'.$fc_type;
        $fdata = $redis->get($redis_key);
        if ($fdata) {
            return json_decode($fdata,true);
        }

        // 查询是否开盘
        if ($fc_type) {
            $this->public_db->where('fc_type',$fc_type);
        }
        $this->public_db->select("name,fc_type,id as gameid ,tep_type");
        $this->public_db->order_by("ID ASC");
        $fdatas = $this->public_db->get('fc_games_view')->result_array();
        //写入redis
        $redis->set($redis_key,json_encode($fdatas));
        return $fdatas;
    }

      //获取彩票单个玩法赔率
    public function get_fc_odds_one($fc_type,$gameid,$pankou = 'A'){
        //缓存redis
        $redis = RedisConPool::getInstace();
        if ($gameid) {
            $redis_key = $fc_type.'_'.SITEID.'_'.INDEX_ID.'_'.$gameid.$pankou;
        }else{
            $redis_key = $fc_type.'_'.SITEID.'_'.INDEX_ID.'_'.$pankou;
        }
        
        $fdata = $redis->get($redis_key);
        if ($fdata) {
            return json_decode($fdata,true);
        }

        //玩法
        if ($gameid) {
            $this->public_db->where('type_id',$gameid);
        }
        //彩种
        if ($fc_type) {
            $this->public_db->where('lottery_type',$fc_type);
        }
        
        $this->public_db->where('pankou',$pankou);
        $this->public_db->where('index_id',INDEX_ID);
        $this->public_db->select("id,input_name,odds_value,type_id,count_arr,type2");
        
        $this->public_db->order_by("type_id,sort,id ASC");
        
        $fdatas = $this->public_db->get('c_odds_'.SITEID)->result_array();

        //写入redis
        $redis->set($redis_key,json_encode($fdatas));
        return $fdatas;
    }

      //获取彩票注单概况
    public function get_fc_count($uid,$sdate,$edate){
        $redis = RedisConPool::getInstace();
        $redis_key = SITEID.'_notCount_'.$_SESSION['uid'];
        $fdata = $redis->get($redis_key);
        if ($fdata) {
            $fdata = json_decode($fdata,TRUE);
        }else{
            $this->db->select('sum(money) as money,count(1) as count,fc_type,type as name');
            $this->db->where('uid',$uid);
            $this->db->where('site_id',SITEID);
            $this->db->where('js',0);
            $this->db->where('addtime >=',$sdate);
            $this->db->where('addtime <=',$edate);
            $this->db->group_by("fc_type");
            $data = $this->db->get('c_bet')->result_array();

            if ($data) {
                foreach ($data as $key => $val) {
                    $fcdata[$val['fc_type']] = $val;
                }
            }
            //获取所有彩票种类
            $fc_titles = $this->fc_titles('');
            foreach ($fc_titles as $key => $val) {
                if ($fcdata[$val['type']]) {
                      $fdata[$val['type']] = $fcdata[$val['type']];
                }else{
                      $fdata[$val['type']]['money'] = 0;
                      $fdata[$val['type']]['count'] = 0;
                      $fdata[$val['type']]['fc_type'] = $val['type'];
                      $fdata[$val['type']]['name'] = $val['name'];
                }
            }
            $redis->setex($redis_key,'5',json_encode($fdata));
        }
        return $fdata;
    }

	///校验数据有效性
	public function check_vaild($lotteryType,$Parameters,$type=1,&$pankou='A'){
        //根据彩种封盘时间过滤注单下注
        $dates = $this->get_fengpan_time($lotteryType);
        $stopBetTime = (int)$dates['f_t_stro'];
        if($stopBetTime <= 0)
            return 1;
        
		if($type){										//pc端处理
			if(isset($Parameters[0]['mingxi_1']) && $Parameters[0]['mingxi_1']){
				if(!in_array($Parameters[0]['mingxi_1'],array('222','223','224','225','226','227','228','229','230','231','232','233','234','特码包三'))){
					return 1;			////明细问题
				}else{
					$gameid = $Parameters[0]['mingxi_1'];
				}
			}
			//过滤六合彩过关玩法
            if($lotteryType == 'liuhecai' && $Parameters[0]['mingxi_1'] == 226){
				$gname_array = explode(',',$Parameters[0]['gname']);
				$BetContext_array = explode(',',$Parameters[0]['BetContext']);
				$start_lenth = count($gname_array);
				$array1_6 = array(1,2,3,4,5,6);
				for($i=0;$i<$start_lenth-1;$i++){
					$a = intval(mb_substr($gname_array[$i],2));
					$b = intval(mb_substr($gname_array[$i+1],2));
					if($a > $b or !in_array($a,$array1_6) or !in_array($b,$array1_6)){
						return 1;
					}
				}
				$u_array = array();
				foreach($gname_array as $k => $v){
					$u_array[] = $v.$BetContext_array[$k];
				}
				$end_lenth = count(array_unique($u_array));
				if($start_lenth != $end_lenth){
					return 1;
				}
			}
            ////获取赔率数据
			if(isset($gameid))
				$odds = $this->get_fc_odds_one($lotteryType,$gameid,$pankou);
			else
				$odds = $this->get_fc_odds_one($lotteryType,0,$pankou);

			///赔率组
			$oddsGroup = array();
            ///可选值组
			$valGroup = array();

			foreach($odds as $v){
				$valGroup[trim($v['type2'])][] = trim($v['input_name']);
				$oddsGroup[trim($v['type2'])][trim($v['input_name'])] = $v['odds_value'];
			}
            
            ////判断预处理   用于补全数据库不存在的数据集
			$valGroup = $this->check_proprocess($lotteryType,$Parameters[0]['mingxi_1'],$valGroup);
			
            ////赔率预处理   用于补全数据库数据错漏
			$oddsGroup = $this->odds_preprocess($lotteryType,$Parameters[0]['mingxi_1'],$oddsGroup);
            
			///判断过关
			if(strstr(trim($Parameters[0]['gname']),',') && trim($Parameters[0]['gname']) != '總和,龍虎'){
				$gnames = explode(',',$Parameters[0]['gname']);

				foreach($gnames as $val){
					if(!isset($valGroup[trim($val)]))
						return 2;				////玩法问题
				}
			}

			$newParameters = $Parameters;
			///判断有效性
			foreach($Parameters as $k=>$v){
				$v['gname'] = trim($v['gname']);

				if((strstr($v['BetContext'],',') || strstr($v['BetContext'],'&')) && $v['gname'] != '两连' && $v['gname'] != '豹子' && $v['gname'] != '对子'){

					if(strstr($v['BetContext'],'@')){
						$v['BetContext'] = preg_replace('#(?:(@[^,]+)(?=,))|(@[^,]+$)#','',$v['BetContext']);
					}

					if(strstr($v['BetContext'],'&')){
						$v['BetContext'] = str_replace('&',',',$v['BetContext']);
					}
                    
					$inputs = explode(',',$v['BetContext']);
                    
                    ///去除非法字符
                    foreach($inputs as &$val){
                        $val = preg_replace('#[^\x{4e00}-\x{9fa5}\w,]+#u','',$val);
                    }
                    $clear_input = $inputs;
                    ///检验重复  除了过关
                    if(($lotteryType != 'liuhecai' || $gameid != '226') && $lotteryType != 'pc_28'){
                        if(count($inputs) != count(array_unique(array_filter($clear_input,'f_ltrim')))){
                            return 2;
                        }
                    }elseif($lotteryType == 'pc_28'){
                        if(count($inputs) != count(array_unique($inputs)))
                            return 2;
                    }
        
					foreach($inputs as $key=>$val){
						if(isset($gnames)){
							$v['gname'] = $gnames[$key];
						}

						if(!isset($valGroup[$v['gname']]))
							return 2;
                        
						if(!in_array(trim($val),$valGroup[$v['gname']]))
							return 3;				///选项有效性问题
					}
				}else{
					if(!in_array(trim($v['BetContext']),$valGroup[$v['gname']]))
						return 3;					///选项有效性问题
				}
			}
			///重定义赔率
			foreach($Parameters as $k=>$v){
                $v['gname'] = trim($v['gname']);
                $v['BetContext'] = trim($v['BetContext']);
                
                ///如果是11选5  如果在预处理解决  需要和六合彩同等对待  redis存储同六合彩不同  故而再次有所区分
                if($lotteryType == "gd_11" || $lotteryType == "jx_11" || $lotteryType == "sd_11"){
                    ///等价六合彩的pel1 mingxi1
                    if(isset($v['Txt']))
                        $v['BetContext'] = $v['Txt'];
                    
                    ///pc端
                    if(isset($v['mingxi_3']))
                        $v['BetContext'] = $v['mingxi_3'];
                }
                
                if($lotteryType == "pc_28"){
                    if($v["mingxi_2"] == "特码包三"){
                        $newParameters[$k]['Lines'] = $oddsGroup[$v['gname']][trim($v["mingxi_2"])];
                        continue;
                    }
                }
         
                if((strstr($v['BetContext'],',') || strstr($v['BetContext'],'&')) && $v['gname'] != '两连' && $v['gname'] != '豹子' && $v['gname'] != '对子'){
                    ///如果是过关
                    if(isset($gnames)){
                        $inputs = explode(',',$v['BetContext']);
                        $count = 0;
                        foreach($inputs as $key=>$val){
                            if(isset($gnames)){             ////过关判定
                                $v['gname'] = $gnames[$key];
                            }
                            
                            $odds = $oddsGroup[$v['gname']][$val];
                            if($count == 0){
                                $count = $odds;
                            }else{
                                $count = $count*$odds;
                            }
                        }
                        $newParameters[$k]['Lines'] = number_format($count,3);
                    }else{       
                        ///如果北京八    那朵奇葩选二到选五
                        if($lotteryType == 'bj_8' && ($v['gname'] == '选二' || $v['gname'] == '选三' || $v['gname'] == '选四' || $v['gname'] == '选五'))
                        {               ///北京8选12345
                            $newParameters[$k]['Lines'] = $oddsGroup[$v['gname']];
                        }else{
                            ////如果是@带赔率
                            if(strstr($v['BetContext'],'@')){
                                $BetContext = preg_replace('#(?:(@[^,]+)(?=,))|(@[^,]+$)#','',$v['BetContext']);
                                $selfInputs = explode(',',$BetContext);
                                $inputs = explode(',',$v['BetContext']);
                                $new = array();
                                foreach($inputs as $key=>$val){
                                    $new[] = preg_replace('#(?<=@).+#',$oddsGroup[$v['gname']][$selfInputs[$key]],$val);
                                }
                                $newParameters[$k]['BetContext'] = implode(',',$new);
                                
                            }else{
                                if(isset($oddsGroup[$v['gname']][$v['gname']])){
                                    ///多选玩法挂钩的赔率
                                    $newParameters[$k]['Lines'] = $oddsGroup[$v['gname']][$v['gname']];
                                }else{
                                    ///多选玩法非挂钩的赔率
                                    $keys = explode(',',$v['BetContext']);
                                    $key = $keys[0];
                                    if(isset($oddsGroup[$v['gname']][$key]))
                                        $newParameters[$k]['Lines'] = $oddsGroup[$v['gname']][$key];
                                    else                                                                            ///wap端补零问题 数据库
                                        $newParameters[$k]['Lines'] = $oddsGroup[$v['gname']][ltrim($key,'0')];
                                }
                            }
                                
                            //if(empty($newParameters[$k]['Lines']))
                            //    return 4;
                        }
                    }
                }else{
                    ///简单下注
                    $newParameters[$k]['Lines'] = $oddsGroup[$v['gname']][trim($v['BetContext'])];
                }
            }
			return array('lotteryId'=>$lotteryType,'betParameters'=>$newParameters);
		}else{
            ////传入一个引用
            $pankou = 'A';
            $Parameters = $this->preprocessmoblie($lotteryType,$Parameters,$pankou);
            
            return $this->check_vaild($lotteryType,$Parameters,$type=1,$pankou);
        }
	}
    
    ///wap端转换为pc端数据
    protected function preprocessmoblie($lotteryType,$Parameters,&$pankou){
        ///明细数组
        $keys = array(
            '特码'=>222,
            '正码'=>223,
            '正码特'=>224,
            '正码1-6'=>225,
            '过关'=>226,
            '连码'=>227,
            '半波'=>228,
            '一肖/尾数'=>229,
            '特码生肖'=>230,
            '合肖'=>231,
            '生肖连'=>232,
            '尾数连'=>233,
            '全不中'=>234
        );
        ///调整不同名称
        foreach($Parameters as &$v){
            ///统一输入内容键
            if(!isset($v['BetContext'])){
                $v['BetContext'] = $v['MBetContext'];
                unset($v['MBetContext']);
            }
            
            ///统一玩法
            if(!isset($v['pel'])){
                $v['pel'] = $v['Pel'];
                unset($v['Pel']);
            }
            $v['gname'] = $v['pel'];
            unset($v['pel']);
                 
            ///六合彩 映射出明细
            if($lotteryType == 'liuhecai'){
                if($v['gname'] == '过关')
                    $v['BetContext'] = $v['DisplayText'];          ///id=>val  赔率id换成值
                
                if($v['gname'] == '尾数连'){
                    ///尾数去尾
                    $v['BetContext'] = str_replace('尾','',$v['BetContext']);
                }
                
                if(isset($v['Pel1'])){
                    $v['mingxi_1'] = $keys[$v['gname']];
                    $v['gname'] = trim($v['Pel1'],',');
                    $v['BetContext'] = trim($v['BetContext'],',');
                    
                    /////特A 特B
                    if($v['mingxi_1'] == 222){
                        $pankou = ltrim($v['gname'],'特');
                        $v['gname'] = '特码';
                    }
                    unset($v['Pel']);
                }
            }
            
            ///和pc端玩法名同步
            if($lotteryType == "cq_ten" || $lotteryType == "gd_ten")
            {
                if($v['gname'] == "總和,龍虎")
                    $v['gname'] = '总和';
            }
            
            ////输入内容带:  一律改为@同步pc
            if(strstr($v['BetContext'],':'))
                $v['BetContext'] = str_replace(':','@',$v['BetContext']);
        }
        
        return $Parameters;
    }
    
    ////赔率预处理
    protected function odds_preprocess($lotteryType,$mingxi1,$oddsGroup){
        ///北京8赔率需要充足
        if($lotteryType == "bj_8")
		{
            $oddsGroup['选一']['选一'] = $oddsGroup['选一']['一中一'];            ///没有:
            for($i=1;$i<=80;$i++){
                $oddsGroup['选一'][$i] = $oddsGroup['选一']['一中一'];             ///补充单注下注问题
            }
            $oddsGroup['选二'] = '2/2:'.$oddsGroup['选二']['二中二'];             ///多赔率需要拼接
            $oddsGroup['选三'] = '3/3:'.$oddsGroup['选三']['三中三'].','.'3/2:'.$oddsGroup['选三']['三中二'];
            $oddsGroup['选四'] = '4/4:'.$oddsGroup['选四']['四中四'].','.'4/3:'.$oddsGroup['选四']['四中三'].','.'4/2:'.$oddsGroup['选四']['四中二'];
            $oddsGroup['选五'] = '5/5:'.$oddsGroup['选五']['五中五'].','.'5/4:'.$oddsGroup['选五']['五中四'].','.'5/3:'.$oddsGroup['选五']['五中三'];
		}
        
		if($lotteryType == "liuhecai")
		{
			switch($mingxi1){
				case '229':         ///尾数缺尾  
					foreach($oddsGroup['尾数'] as $k=>$v){
						$newk = $k.'尾';
                        $oddsGroup['尾数'][$newk] = $v;
                        unset($oddsGroup['尾数'][$k]);
					}
				break;
                
                case '227':         ///多赔率需要拼接
                    $oddsGroup['二中特']['二中特'] = $oddsGroup['二中特']['中特'].'/'.$oddsGroup['二中特']['中二'];
                    $oddsGroup['三中二']['三中二'] = $oddsGroup['三中二']['中二'].'/'.$oddsGroup['三中二']['中三'];
				break;
			}
		}
        
        ////快乐十分玩法同步数据库内容
		if($lotteryType == "cq_ten" || $lotteryType == "gd_ten")
		{
			$oddsGroup['总和'] = $oddsGroup["總和,龍虎"];
			unset($oddsGroup["總和,龍虎"]);
            
            $oddsGroup['任选二']['任选二']     = $oddsGroup['连码']['任选二'];
            $oddsGroup['任选二组']['任选二组'] = $oddsGroup['连码']['任选二组'];
            $oddsGroup['任选三']['任选三']     = $oddsGroup['连码']['任选三'];
            $oddsGroup['任选四']['任选四']     = $oddsGroup['连码']['任选四'];
            $oddsGroup['任选五']['任选五']     = $oddsGroup['连码']['任选五'];
		}
        
		return $oddsGroup;
    }
    
    
    ///检测预处理
	protected function check_proprocess($lotteryType,$mingxi1,$valGroup){
        ///北京快乐8没数据
        if($lotteryType == "bj_8")
		{
			$keys = array_keys($valGroup);
			for($i=0;$i<5;$i++)
			{

				for($j=1;$j<=80;$j++)
				{
					$valGroup[$keys[$i]][$j-1] = (string)$j;
				}

			}
		}
        
        ///11选五         部分玩法循环出值
        if($lotteryType == "gd_11" || $lotteryType == "jx_11" || $lotteryType == "sd_11")
        {
            unset($valGroup['任选']);
            unset($valGroup['组选']);
            unset($valGroup['直选']);
            for($i=1;$i<12;$i++){
                $valGroup['任选'][] = (string)$i;
                $valGroup['组选'][] = (string)$i;
                $valGroup['直选'][] = (string)$i;
            }
        }
    
        ///六合彩没数据  尾数缺尾
		if($lotteryType == "liuhecai")
		{
			switch($mingxi1){
				case '227':     ///连码
					$keys = array_keys($valGroup);
					foreach($keys as $k=>$v)
					{

						for($j=1;$j<=49;$j++)
						{
							$valGroup[$keys[$k]][$j-1] = (string)$j;
						}

					}
				break;

				case '229':
					foreach($valGroup['尾数'] as &$v){
						$v .= '尾';
					}
				break;
			}
		}

        ////快乐十分玩法错误修正 以及连码数据补全
		if($lotteryType == "cq_ten" || $lotteryType == "gd_ten")
		{
			$valGroup['总和'] = $valGroup["總和,龍虎"];
			unset($valGroup["總和,龍虎"]);

			foreach($valGroup['连码'] as $v){
				for($i=1;$i<21;$i++){
					$valGroup[$v][] = (string)$i;
				}
			}
			unset($valGroup['连码']);
		}
        
		return $valGroup;
	}

    //开奖结果
    public function get_fc_auto($fc_type,$count,$flag = true){
        //缓存redis
        $redis = RedisConPool::getInstace();
        //最近一期开奖
        if ($count == 1) {
            $redis_key = $fc_type.'_auto_data';
        }else{
            $redis_key = $fc_type.'_auto_'.$count.'_data';
        }
        $adata = $redis->get($redis_key);
        if ($adata && $flag) {
            return json_decode($adata,true);
        }
        $this->public_db->select("*");
        $this->public_db->order_by("qishu DESC");
        if ($count == 1) {
            $adata = $this->public_db->get($fc_type.'_auto')->row_array();
            $redis->set($redis_key,json_encode($adata));
        }else{
            $adata = $this->public_db->get($fc_type.'_auto',$count)->result_array();
            $redis->setex($redis_key,30,json_encode($adata));
        }
        return $adata;
    }
    
    // 获取当前期数，$type_y 表示类别，例：'liuhecai'
    function get_fc_qishu($type_y){
    	if ($type_y == 'liuhecai') {
    		$now_time = func_nowtime("Y-m-d H:i:s");
    	} else {
    		$now_time = func_nowtime();
    	}
    	$map = array(
    			'ok' => '0',
    			'kaijiang>' => $now_time
    	);
    	$this->public_db->order_by('kaijiang', 'ASC');
    	$data_time = $this->_opentime_list($type_y, $map);
    	$date_Y = func_nowtime('Y');
    	$date_y = func_nowtime('y');
    	$date_ymd = func_nowtime('ymd');
    	$date_Ymd = func_nowtime('Ymd');
        
        
    	// 判断是否是当天的最后一期,如果是显示明天第一期
    	if (empty($data_time['qishu'])) {
    		$data_time['qishu'] = 1;
    		$date_Y = func_nowtime('Y', "+24 hours");
    		$date_y = func_nowtime('y', "+24 hours");
    		$date_ymd = func_nowtime('ymd', "+24 hours");
    		$date_Ymd = func_nowtime('Ymd', "+24 hours");
    	}
    
    	///跨天彩种  越界补丁    逻辑错误源头：数据库存北京时间
    	if($type_y != 'liuhecai'){
    		$res = $this->public_db->select('`kaijiang`,qishu')->order_by('kaijiang','DESC')->limit(3)->get($type_y.'_opentime');
    		$rows = $res->result_array();
    		$maxtime_qishu = $rows[0]['qishu'];
    		$maxtime = $rows[0]['kaijiang'];
    		if($data_time['qishu'] > $maxtime_qishu && $now_time < $maxtime){
                $date_Ymd = date("Ymd",strtotime($date_Ymd)-24*3600);
    		}
    	}
    	////跨天彩种  越界补丁
        
    	if ($type_y == 'liuhecai') {
    		// 六合彩
    		return func_BuLings($data_time['qishu']);
    	} elseif ($type_y == 'fc_3d') {
    		return $date_Y . substr(strval(func_fc_qishu($rows[0]['kaijiang'])+1000),1,3);
    	} elseif ($type_y == 'pl_3') {
    		return $date_y . substr(strval(func_fc_qishu($rows[0]['kaijiang'])+1000),1,3);
    	} elseif ($type_y == 'bj_8' || $type_y == 'xy_28'  || $type_y == 'bj_28'  || $type_y == 'pc_28' ) {
    		// 北京快乐8
    		return func_com_qishu('bj_8');
    	} elseif ($type_y == 'bj_10') {
    		// 北京PK10
    		return func_com_qishu('bj_10');
    	} elseif ($type_y == 'cq_ten') {
    		// 重庆快乐10分
    		return $date_ymd . func_BuLings($data_time['qishu']);
    	} elseif ($type_y == 'gd_ten') {
    		// 广东快乐十分
    		return $date_Ymd . func_BuLing($data_time['qishu']);
    	} elseif ($type_y == 'gd_11' || $type_y == 'sd_11' || $type_y == 'jx_11') {
    		// 11选5
    		return $date_Ymd . func_BuLing($data_time['qishu']);
    	} else {
    		return $date_Ymd . func_BuLings($data_time['qishu']);
    	}
    }
    
    //获取当期开盘关盘时间
    public function _opentime_list($type, $map = array()){
    	if (! empty($map)) {
    		$this->public_db->where($map);
    	}
    	$query = $this->public_db->get($type . '_opentime');
    	$rows = $query->result_array();
        
    	//echo $this->public_db->last_query();exit;
    	return $rows[0];
    }
    
    ///所有彩种代理限额读取
    public function get_limit_agent($type,$agent_id = 0,$site_id='',&$datas=[]){
        $limit = [];
        foreach($type as $k=>$v){
            $data = $this->fc_games_type($k);                               ////通过type获取所有玩法
            
            foreach($data as $key => $val){
                $datas[$val['gameid']] = $val;                                                  ////获取每一个玩法
                $limit[$k][] = $this->get_limit_agent_one($k,$val['gameid'],$agent_id,$site_id);          ////通过彩种+玩法+代理id获取代理限额
            }
        }
        return $limit;
    }
    
    public function get_limit_agent_one($type,$gameid='',$agent_id = 0,$site_id=''){
        //缓存redis
        $redis = RedisConPool::getInstace();
        $redis_key = "{$site_id}_{$type}_{$gameid}_aid{$agent_id}";
        $fdata = $redis->get($redis_key);
        if ($fdata) {
            $fdata = json_decode($fdata,true);
            return $fdata;
        }
        
        
        $fcset = $this->private_db->from('k_user_agent_fc_set');
    	$where_fcset = "is_default = 0 and site_id='".$site_id."' and aid =".$agent_id." and type_id = ".$gameid;
    	$array_fcset = $fcset->where($where_fcset)->get()->row_array();

        if(empty($array_fcset)){
            //代理限额初始数据
            $fcset = $this->private_db->from('k_user_agent_fc_set');
            $where_fcseta = "is_default = 1 and site_id='".$site_id."' and aid ='0' and type_id = ".$gameid;
            $array_fcseta = $fcset->where($where_fcseta)->get()->row_array();
            $array_fcset = $array_fcseta;
        }
        
        $redis->set($redis_key,json_encode($array_fcset));
        return $array_fcset;
    }
    
    ///用户限额读取
    public function get_limit_user($uid){
        $fcset = $this->private_db->from('k_user_fc_set')->where('uid',$uid)->get()->result_array();
        $new_fcset = [];
        foreach($fcset as $v){
            $new_fcset[$v['type_id']] = $v;
        }
        return $new_fcset;
    }

    //获取彩票图片
    public function get_fc_img(){
        $fc_img = $this->private_db->from('info_fc_img')->where(array('site_id'=>SITEID,'index_id'=>INDEX_ID))->get()->result_array();
        return $fc_img;
    }





    
}


                    
function f_ltrim(&$string_uninqu){
    if($string_uninqu !== '0')
        $string_uninqu = ltrim($string_uninqu,'0');
    else
        $string_uninqu = '00';
    return $string_uninqu;
}
?>