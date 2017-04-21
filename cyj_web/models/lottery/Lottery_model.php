<?php
if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}
//总彩票model
class Lottery_Model extends MY_Model
{
    public function __construct(){
        $this->init_db();
    }
	
    //获取导航分类
    public function _get_head(){
    	$this->load->model('lottery/Fc_com_model');
    	$rows = $this->Fc_com_model->get_fc_header();
    	if (count($rows) == 0) {
    		return FALSE;
    	}
    	$list = array('yb'=>array('name'=>'一般彩球'),
    			'gpc'=>array('name'=>'北京彩'),
    			'ssc'=>array('name'=>'时时彩'),
    			'sf'=>array('name'=>'快乐十分'),
    			'k3'=>array('name'=>'快三'),
    			'11'=>array('name'=>'十一选五'),
    			'xy'=>array('name'=>'幸运彩'),
    	);
    	foreach ($list as $key=>$val){
    		foreach($rows as $k=>$v){
    			if($key ==$v['l_type']){
    				$list[$key]['data'][] = $v;
    			}
    		}
    	}
    	return $list;
    }
    
    //判断当前彩种是否关盘
    public  function ifopen($type,$indexid,$siteid){
    	$where = "index_id='".$indexid."' and site_id='".$siteid."' and fc_module LIKE '%".$type."%'";
    	$this->manage_db->where($where);
    	$result=$this->manage_db->get('site_info');
    	$rows = $result->num_rows();
    	return $rows;
    }

    public function get_auto($map=array()){
        if (! empty($map['where'])) {
            $this->public_db->where($map['where']);
        }
        if (! empty($map['limit'])) {
            $this->public_db->limit($map['limit']);
        }
        if(!empty($map['order'])){
            $this->public_db->order_by($map['order']);
            $rows = $this->public_db->get($map['table'])->result_array();
        }
        return $rows;
    }

    //无出期数
    public function _get_miss($fc_type){
        if($fc_type == 'cq_ten' || $fc_type == 'gd_ten'){
            $num = 8;
            //$res = $this->_get_result($fc_type,150,1);
            $this->load->model('lottery/Fc_com_model');
            $res = $this->Fc_com_model->get_fc_auto($fc_type,150);//最近开奖
            $qiu = array();
            if($res){
                $chuqiu = array(0=>0,1=>0,2=>0,3=>0,4=>0,5=>0,6=>0,7=>0,8=>0,9=>0,10=>0,11=>0,12=>0,13=>0,14=>0,15=>0,16=>0,17=>0,18=>0,19=>0);
                for($i=1;$i<=20;$i++){
                    foreach($res as $k => $r){
                        if($r['ball_1'] != $i && $r['ball_2'] != $i && $r['ball_3'] != $i && $r['ball_4'] != $i && $r['ball_5'] != $i && $r['ball_6'] != $i && $r['ball_7'] != $i && $r['ball_8'] != $i){
                            $chuqiu[$i-1] = $chuqiu[$i-1] + 1;
                        }
                    }
                }
                return $chuqiu;
            }
        }else{
            return false;
        }
    }

    //出球率
    public function _get_chuqiu($fc_type){
        $num = 0;
        if($fc_type == 'cq_ten' || $fc_type == 'gd_ten'){
            $num = 8;
        }else{
            $num = 5;
        }
        if($fc_type && $num){
            $this->load->model('lottery/Fc_com_model');
            $res = $this->Fc_com_model->get_fc_auto($fc_type,150);//最近开奖
            $qiu = array();
            if($res){
                for($i=1;$i<=$num;$i++){
                    if($num == 8){
                        $chuqiu = array(0=>0,1=>0,2=>0,3=>0,4=>0,5=>0,6=>0,7=>0,8=>0,9=>0,10=>0,11=>0,12=>0,13=>0,14=>0,15=>0,16=>0,17=>0,18=>0,19=>0);
                    foreach($res as $k => $r){

                        if($r['ball_'.$i] == 1){
                            $chuqiu[0] = $chuqiu[0] + 1;
                        }elseif($r['ball_'.$i] == 2){
                            $chuqiu[1] = $chuqiu[1] + 1;
                        }elseif($r['ball_'.$i] == 3){
                            $chuqiu[2] = $chuqiu[2] + 1;
                        }elseif($r['ball_'.$i] == 4){
                            $chuqiu[3] = $chuqiu[3] + 1;
                        }elseif($r['ball_'.$i] == 5){
                            $chuqiu[4] = $chuqiu[4] + 1;
                        }elseif($r['ball_'.$i] == 6){
                            $chuqiu[5] = $chuqiu[5] + 1;
                        }elseif($r['ball_'.$i] == 7){
                            $chuqiu[6] = $chuqiu[6] + 1;
                        }elseif($r['ball_'.$i] == 8){
                            $chuqiu[7] = $chuqiu[7] + 1;
                        }elseif($r['ball_'.$i] == 9){
                            $chuqiu[8] = $chuqiu[8] + 1;
                        }elseif($r['ball_'.$i] == 10){
                            $chuqiu[9] = $chuqiu[9] + 1;
                        }elseif($r['ball_'.$i] == 11){
                            $chuqiu[10] = $chuqiu[10] + 1;
                        }elseif($r['ball_'.$i] == 12){
                            $chuqiu[11] = $chuqiu[11] + 1;
                        }elseif($r['ball_'.$i] == 13){
                            $chuqiu[12] = $chuqiu[12] + 1;
                        }elseif($r['ball_'.$i] == 14){
                            $chuqiu[13] = $chuqiu[13] + 1;
                        }elseif($r['ball_'.$i] == 15){
                            $chuqiu[14] = $chuqiu[14] + 1;
                        }elseif($r['ball_'.$i] == 16){
                            $chuqiu[15] = $chuqiu[15] + 1;
                        }elseif($r['ball_'.$i] == 17){
                            $chuqiu[16] = $chuqiu[16] + 1;
                        }elseif($r['ball_'.$i] == 18){
                            $chuqiu[17] = $chuqiu[17] + 1;
                        }elseif($r['ball_'.$i] == 19){
                            $chuqiu[18] = $chuqiu[18] + 1;
                        }elseif($r['ball_'.$i] == 20){
                            $chuqiu[19] = $chuqiu[19] + 1;
                        }
                    }
                    $qiu['n'.$i] = $chuqiu;
                    }elseif ($num == 5) {
                         $chuqiu = array(0=>0,1=>0,2=>0,3=>0,4=>0,5=>0,6=>0,7=>0,8=>0,9=>0);
                        foreach($res as $k => $r){

                            if($r['ball_'.$i] == 0){
                                $chuqiu[0] = $chuqiu[0] + 1;
                            }elseif($r['ball_'.$i] == 1){
                                $chuqiu[1] = $chuqiu[1] + 1;
                            }elseif($r['ball_'.$i] == 2){
                                $chuqiu[2] = $chuqiu[2] + 1;
                            }elseif($r['ball_'.$i] == 3){
                                $chuqiu[3] = $chuqiu[3] + 1;
                            }elseif($r['ball_'.$i] == 4){
                                $chuqiu[4] = $chuqiu[4] + 1;
                            }elseif($r['ball_'.$i] == 5){
                                $chuqiu[5] = $chuqiu[5] + 1;
                            }elseif($r['ball_'.$i] == 6){
                                $chuqiu[6] = $chuqiu[6] + 1;
                            }elseif($r['ball_'.$i] == 7){
                                $chuqiu[7] = $chuqiu[7] + 1;
                            }elseif($r['ball_'.$i] == 8){
                                $chuqiu[8] = $chuqiu[8] + 1;
                            }elseif($r['ball_'.$i] == 9){
                                $chuqiu[9] = $chuqiu[9] + 1;
                            }
                        }
                    $qiu['n'.$i] = $chuqiu;
                    }
                }
            }
            // p($qiu);
            return $qiu;
        }
        return false;
    }


 	/**
	 * 获取用户信息
	 * @param  [int] $uid 用户id
	 * @return [array]
	 */
    public function _get_userinfo($uid)
    {
        if (is_numeric($uid)) {
            $this->private_db->select('uid,agent_id,username,money,index_id,site_id');
            $this->private_db->where('uid', $uid);
            $query = $this->private_db->get("k_user");
            $rows = $query->row_array();
            if ($rows) {
                return $rows;
            }
        }
        return false;
    }

    public function get_fanshui(){
        $map = array();
        $map['site_id'] = SITEID;
        $map['is_delete'] = 0;
        $map['index_id'] = INDEX_ID;
        $this->private_db->where($map);
        $query = $this->private_db->get('k_user_discount_set');
        $rows = $query->result_array();
        if($rows[0]['liuhecai_discount']){
            return $rows[0]['liuhecai_discount'];
        }else{
            return FALSE;
        }
    }

    public function _get_mingxi_1($id){
        $map = array();
        $map['id'] = $id;
        $this->public_db->where($map);
        $query = $this->public_db->get('fc_games_type');
        $rows = $query->result_array();
        if($rows[0]['fc_type']){
            return $rows[0]['fc_type'];
        }else{
            return FALSE;
        }
    }

    public function get_lottery_name($lotteryId){
        $map = array();
        $map['type'] = $lotteryId;
        $this->public_db->where($map);
        $query = $this->public_db->get('fc_games');
        $rows = $query->result_array();
        if($rows[0]['name']){
            return $rows[0]['name'];
        }else{
            return FALSE;
        }
    }

    //下注验证
    public function check_form($data){
        $xiao_arr = array('鼠','牛','虎','兔','龙','蛇','马','羊','猴','鸡','狗','猪');
        $num_arr = array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49);
        $wei_arr = array(0,1,2,3,4,5,6,7,8,9);
        // p($data);
        $type = $data['lotteryId'];
        foreach ($data['betParameters'] as $k => $v) {
            if(empty($v['Money'])){
                return 1;
            }
            if(!is_numeric(floatval($v['Money'])) || floatval($v['Money']) <= 0 ){
                return 1;
            }
            if(($v['mingxi_1'] == 232 || $v['mingxi_1'] == 233 || $v['mingxi_1'] == 234) && empty($v['Lines'])  ){

            }else{
                if(empty($v['Lines'])){
                    return 1;
                }
            }
            //组合数判断
            if(isset($v['min'])){
                if($type == 'bj_8'){
                    if(($v['gname'] == '选一' && $v['min'] != 1) || ($v['gname'] == '选二' && $v['min'] != 2) || ($v['gname'] == '选三' && $v['min'] != 3) || ($v['gname'] == '选四' && $v['min'] != 4) || ($v['gname'] == '选五' && $v['min'] != 5)){
                        return 1;
                    }
                }elseif($type == 'gd_ten' || $type == 'cq_ten'){
                    if(($v['gname'] == '任选二' && $v['min'] != 2) || ($v['gname'] == '任选二组' && $v['min'] != 2) || ($v['gname'] == '任选三' && $v['min'] != 3) || ($v['gname'] == '任选四' && $v['min'] != 4) || ($v['gname'] == '任选五' && $v['min'] != 5)){
                        return 1;
                    }
                }elseif($type == 'liuhecai'){
                    if(($v['gname'] == '二全中' && $v['min'] != 2) || ($v['gname'] == '二中特' && $v['min'] != 2) || ($v['gname'] == '特串' && $v['min'] != 2) || ($v['gname'] == '三全中' && $v['min'] != 3) || ($v['gname'] == '三中二' && $v['min'] != 3) || ($v['gname'] == '四中一' && $v['min'] != 4)|| ($v['gname'] == '四全中' && $v['min'] != 4)|| ($v['gname'] == '二肖连中' && $v['min'] != 2)|| ($v['gname'] == '三肖连中' && $v['min'] != 3)|| ($v['gname'] == '四肖连中' && $v['min'] != 4)|| ($v['gname'] == '五肖连中' && $v['min'] != 5)|| ($v['gname'] == '二肖连不中' && $v['min'] != 2)|| ($v['gname'] == '三肖连不中' && $v['min'] != 3)|| ($v['gname'] == '四肖连不中' && $v['min'] != 4)|| ($v['gname'] == '二尾连中' && $v['min'] != 2)|| ($v['gname'] == '三尾连中' && $v['min'] != 3)|| ($v['gname'] == '四尾连中' && $v['min'] != 4)|| ($v['gname'] == '二尾连不中' && $v['min'] != 2)|| ($v['gname'] == '三尾连不中' && $v['min'] != 3)|| ($v['gname'] == '四尾连不中' && $v['min'] != 4)|| ($v['gname'] == '五不中' && $v['min'] != 5)|| ($v['gname'] == '六不中' && $v['min'] != 6)|| ($v['gname'] == '七不中' && $v['min'] != 7)|| ($v['gname'] == '八不中' && $v['min'] != 8)|| ($v['gname'] == '九不中' && $v['min'] != 9)|| ($v['gname'] == '十不中' && $v['min'] != 10)|| ($v['gname'] == '十一不中' && $v['min'] != 11)|| ($v['gname'] == '十二不中' && $v['min'] != 12)){
                        return 1;
                    }
                }

            }
            //组合重复性判断
            if(isset($v['min'])){
                if($v['mingxi_1'] == 226){//过关
                    $bet_arr = explode(',', $v['BetContext']);
                    if(count($bet_arr) < 2){
                        return 1;
                    }
                    $bet_arr = explode(',', $v['gname']);
                    if(count($bet_arr) < 2){
                        return 1;
                    }
                }elseif($v['mingxi_1'] == 232 || $v['mingxi_1'] == 233 || $v['mingxi_1'] == 234){
                    $true_arr = array();
                    switch ($v['mingxi_1']) {
                        case 232:
                            $true_arr = $xiao_arr;
                            break;
                        case 233:
                            $true_arr = $wei_arr;
                            break;
                        case 234:
                            $true_arr = $num_arr;
                            break;
                    }
                    $bet_arr = explode(',', $v['BetContext']);
                    $temp_arr = array();
                    foreach ($bet_arr as $kk => $vv) {
                        $bet_arr_1 = explode('@', $vv);
                        if( !in_array($bet_arr_1[0], $true_arr)){
                            return 1;
                        }
                        $temp_arr[] = $bet_arr_1[0];
                    }

                    if(!empty($temp_arr)){
                        $temp_arr_1 = array_flip(array_flip($temp_arr));
                        if(count($temp_arr_1) != count($temp_arr)){
                            return 1;
                        }
                    }
                }else{

                    $bet_arr = explode(',', $v['BetContext']);
                    if(!empty($bet_arr)){
                        $temp_arr = array_flip(array_flip($bet_arr));

                        if(count($bet_arr) != count($temp_arr)){
                            return 1;
                        }
                    }

                }
            }
        }
        return 2;
    }


    /**
     * 下注
     * @param [array] $data 数据用于循环插入下注
     * @param [array] $ptype 区分来源
     * @param [array] $pankou 盘口
     * @return [json]
     */
    public function _addlottery_bet($data,$ptype=0,$pankou=''){
        $fs = 0.01;
        if(empty($pankou))
            $pankou = $_SESSION['pankou'];
        $this->load->model('lottery/Fc_com_model');
        $now_qishu = $this->Fc_com_model->get_fc_qishu($data['lotteryId']);                             //获取当前期数
        $lotteryname = $this->get_lottery_name($data['lotteryId']);                                     ///id=>string type
        
        ///下注内容解包
        if(!empty($data['betParameters'][0]['min']) &&  strstr($data['betParameters'][0]['BetContext'],'&') && !empty($data['betParameters'][0]['mingxi_1']) && $data['betParameters'][0]['mingxi_1'] != '227'){
            //////下注内容中取出赔率
            $shuzu_temp = explode('&', $data['betParameters'][0]['BetContext']);
            $arr_temp = array();
            foreach ($shuzu_temp as $key => $value) {
                unset($data['betParameters'][0]['min']);
                $shuzu_1 = explode('@', $value);
                $data['betParameters'][0]['Lines'] = $shuzu_1[1];
                $data['betParameters'][0]['BetContext'] = $shuzu_1[0];
                $arr_temp[] =$data['betParameters'][0];
            }
            $data['betParameters'] = $arr_temp;
        }elseif(!empty($data['betParameters'][0]['min']) &&  strstr($data['betParameters'][0]['BetContext'],'&')){
            //六合彩连码     快速下注内容  转换为常规下注内容
            $shuzu_temp = explode('&', $data['betParameters'][0]['BetContext']);
            $shuzu_1 = explode(',', $shuzu_temp[0]);
            $shuzu_2 = explode(',', $shuzu_temp[1]);
            $arr_temp = array();
            if($data['betParameters'][0]['min'] ==2){
                //对碰
                foreach ($shuzu_1 as $k => $v) {
                    foreach ($shuzu_2 as $k1 => $v1) {
                        $data['betParameters'][0]['BetContext'] = $v.','.$v1;
                        $arr_temp[] =$data['betParameters'][0];
                    }
                }
                $data['betParameters'] = $arr_temp;
            }elseif($data['betParameters'][0]['min'] ==3 || $data['betParameters'][0]['min'] ==4){
                $mm = 2;
                //拖胆
                if(count($shuzu_2) >= floatval($data['betParameters'][0]['min']-$mm)){
                    $arr_t = func_get_zuhe($shuzu_2,floatval($data['betParameters'][0]['min']-$mm));
                    foreach ($arr_t as $k => $v) {
                        $data['betParameters'][0]['BetContext'] = $shuzu_temp[0].','.$v;
                        $arr_temp[] =$data['betParameters'][0];
                    }
                    $data['betParameters'] = $arr_temp;
                }else{
                    $data['betParameters'][0]['BetContext'] = implode(',', $shuzu_1).','.implode(',', $shuzu_2);
                }
            }
        }elseif(!empty($data['betParameters'][0]['min'])){
            ////按照最小前端最小值  分割出组合数    @隐患
            $zuhe_arr = func_get_zuhe(explode(',', $data['betParameters'][0]['BetContext']) ,$data['betParameters'][0]['min']);
            $arr_temp = array();
            foreach ($zuhe_arr as $k => $v) {
                $data['betParameters'][0]['BetContext'] = $v;
                $arr_temp[] =$data['betParameters'][0];
            }
            $data['betParameters'] = $arr_temp;
        }
        
        ////该过滤无法完全过滤重复数  无特别作用
        $error = $this->check_form($data);
        if($error == 1){
            $data=array("result"=>5,"msg"=>"参数错误9","errId"=>1026);
            return json_encode($data);exit;
        }
        
        
        if($data['lotteryId'] == 'liuhecai'){
            $mingxi_1 = trim($this->_get_mingxi_1($data['betParameters'][0]['mingxi_1']));
        }else{
            $mingxi_1 = trim($data['betParameters'][0]['gname']);
        }
        
        $all_money = 0;             ///下注总金额
        $games = [];                ///玩法集合
        
        foreach ($data['betParameters'] as $k => &$zzz) {
              //金额是否数字判断
            if ($zzz['Money'] <= 0 || !is_numeric($zzz['Money'])) {
                $data = array("result"=>5,"msg"=>"下注金额错误7","errId"=>1026);
                return json_encode($data);
            }

            $all_money+=floatval($zzz['Money']);
            $zzz['gname'] = trim($zzz['gname']);
            $games[$zzz['gname']] = 1;
        }


        $uid = $_SESSION['uid'];
        ////获取用户信息
        $user = $this->_get_userinfo($uid);

////限额过滤处理
        $status = false;
        $bet_limit = array();
        if(count($games) === 1){
            $status = true;
        }else{
            if(isset($games['正码1']) || isset($games['正码2']) || isset($games['正码3']) || isset($games['正码4']) || isset($games['正码5']) || isset($games['正码6']))
                $status = true;
        }
        
        ////全场限额改动
        $redis_key = 'c_lot'.$data['lotteryId'].'_'.$now_qishu.'_'.$user['uid'];
        $redis = RedisConPool::getInstace();
        if($redis->exists($redis_key))
            $has_bet = $redis->get($redis_key);
        else
            $has_bet = 0;
        ////全场限额改动
        
        if($status)
        {                ///pc端  或者wap端玩单一玩法
            $bet_limit = $this->_get_ball_limit($data['lotteryId'],trim($mingxi_1));
            $bet_limit = current($bet_limit);
            //读不到数据   默认限额
            if(empty($bet_limit['single_field_max'])){
                $bet_limit['single_field_max'] = 50000;
            }
            if(empty($bet_limit['single_note_max'])){
                $bet_limit['single_note_max'] = 50000;
            }
            if(empty($bet_limit['single_note_min'])){
                $bet_limit['single_note_min'] = 1;
            }
            if($all_money+$has_bet > $bet_limit['single_field_max']){
                $data=array("result"=>5,"msg"=>"单场下注金额过大","errId"=>1026);
                return json_encode($data);exit;
            }
            
            ///单注限额
            foreach ($data['betParameters'] as $k => $val) {
                if(floatval($val['Money']) < (int)$bet_limit['min']){
                    $data=array("result"=>5,"msg"=>"单注下注金额过小","errId"=>1026);
                    return json_encode($data);exit;
                }
                
                if(floatval($val['Money']) > (int)$bet_limit['single_note_max']){
                    $data=array("result"=>5,"msg"=>"单注下注金额过大","errId"=>1026);
                    return json_encode($data);exit;
                }
            }
        }
        else
        {
            ////wap端多玩法
            $bet_limit = $this->_get_ball_limit($data['lotteryId'],$games);
            $max = 0;
            $bet_limits = [];       ///名称做键   取出限额集合
            
            foreach($bet_limit as $v){
                $bet_limits[$v['name']] = $v;
                if($max > (int)$v['single_field_max'] || $max === 0)
                    $max = (int)$v['single_field_max'];
            }
            
            if(empty($max)){
                $max = 500000;
            }
            
            if($all_money+$has_bet > $max){
                $data=array("result"=>5,"msg"=>"单场下注金额过大","errId"=>1026);
                return json_encode($data);exit;
            }
            
            ///单注限额比对
            foreach ($data['betParameters'] as $k => $v) {
                if(empty($bet_limits[$v['gname']])){
                    $bet_limits[$v['gname']]['min'] = 1;
                    $bet_limits[$v['gname']]['single_note_max'] = 50000;
                }
                if(floatval($v['Money']) < (int)$bet_limits[$v['gname']]['min']){
                    $data=array("result"=>5,"msg"=>"单注下注金额过小","errId"=>1026);
                    return json_encode($data);exit;
                }
                
                if(floatval($v['Money']) > (int)$bet_limits[$v['gname']]['single_note_max']){
                    $data=array("result"=>5,"msg"=>"单注下注金额过大","errId"=>1026);
                    return json_encode($data);exit;
                }
            }
        }
////限额过滤处理
        
        if(floatval($all_money) > floatval($user['money'])){
            $data=array("result"=>5,"msg"=>"余额不足！","errId"=>1026);
            return json_encode($data);exit;
        }
        
        ///注单总详情  记录总详情   mongo记录
        $betInfos = '';
        $recordInfos = '';
        
        $mongo = [];
        ///循环下注
        foreach ($data['betParameters'] as $k => $v) {
            ////获取注单号
            $cbetordernum = func_getdid();
            $userinfo = &$user;
            $c_betdata['did'] = $cbetordernum;
            $c_betdata['ptype'] = $ptype;
            $c_betdata['uid'] = $user['uid'];
            $c_betdata['agent_id'] = $user['agent_id'];
            $c_betdata['ua_id'] = $_SESSION['ua_id'];
            $c_betdata['sh_id'] = $_SESSION['sh_id'];
            $c_betdata['is_shiwan'] = $_SESSION['shiwan'];//判断是否试玩账号
            $c_betdata['username'] = $user['username'];
            $c_betdata['addtime'] = func_nowtime('Y-m-d H:i:s','now');
            $c_betdata['type'] =$lotteryname;
            $c_betdata['qishu']=$now_qishu;
            if($data['lotteryId'] == 'liuhecai'){
                $c_betdata['mingxi_1'] = trim($this->_get_mingxi_1($data['betParameters'][0]['mingxi_1']));
                $c_betdata['mingxi_3'] = trim($v['gname']);
                //一肖尾数特殊转换
                if ($c_betdata['mingxi_3'] == '一肖') {
                    $c_betdata['mingxi_1'] = '生肖';

                }elseif($c_betdata['mingxi_3'] == '尾数'){
                    $c_betdata['mingxi_1'] = '尾数';

                }
            }else{
                $c_betdata['mingxi_1'] = $v['gname'];
            }
            
            ///11选五下注追加明细3
            if($data['lotteryId'] == 'gd_11' || $data['lotteryId'] == "jx_11" || $data['lotteryId'] == "sd_11"){
                if(isset($v['Txt']))
                    $c_betdata['mingxi_3'] = $v['Txt'];
                
                if(isset($v['mingxi_3']))
                    $c_betdata['mingxi_3'] = $v['mingxi_3'];
            }
            
            ///PC蛋蛋特码选三个玩法
            if($data['lotteryId'] == 'pc_28'){
                $c_betdata['mingxi_3'] = $v['mingxi_2'];
            }

            if($v['gname'] == '选二' ||$v['gname'] == '选三' || $v['gname'] == '选四' || $v['gname'] == '选五'){
                $c_betdata['mingxi_3'] = $v['Lines'];
                $ming3_arr = explode(':', $v['Lines']);
                $c_betdata['odds'] = trim($ming3_arr[(count($ming3_arr)-1)]);
            }elseif($v['gname'] == '二中特'){
                $ming3_arr = explode('/', $v['Lines']);
                $c_betdata['mingxi_3'] = '中特:'.$ming3_arr[0].';中二:'.$ming3_arr[1];
                $c_betdata['odds'] = trim($ming3_arr[(count($ming3_arr)-1)]);
            }elseif($v['gname'] == '三中二'){
                $ming3_arr = explode('/', $v['Lines']);
                $c_betdata['mingxi_3'] = '中二:'.$ming3_arr[0].';中三:'.$ming3_arr[1];
                $c_betdata['odds'] = trim($ming3_arr[(count($ming3_arr)-1)]);
            }else{
                $c_betdata['odds'] = $v['Lines'];
            }

            if($v['mingxi_1'] == 232 || $v['mingxi_1'] == 233 || ($v['mingxi_1'] == 234 && !empty($data['betParameters'][0]['min']))){
                $h_str = trim($v['BetContext']);//兔@4.35,羊@3.8
                $h_str_arr = explode(',', $h_str);
                $temp_arr = $mingxi_2_arr = $odd_arr_h = array();
                foreach ($h_str_arr as $k => $vv1) {
                    $temp_arr = explode('@', $vv1);
                    $mingxi_2_arr[] = $temp_arr[0];
                    $odd_arr_h[] = $temp_arr[1];
                }
                sort($odd_arr_h);
                $c_betdata['odds'] = $odd_arr_h[0];
                $c_betdata['mingxi_2'] = implode(',', $mingxi_2_arr);
            }else{
                $c_betdata['mingxi_2'] = trim($v['BetContext']);

                //尾数处理
                if ($c_betdata['mingxi_3'] == '尾数' && $data['lotteryId'] == 'liuhecai') {
                     $c_betdata['mingxi_2'] = str_replace('尾','',$c_betdata['mingxi_2']);
                }
            }
            $c_betdata['money'] = $v['Money'];
            $c_betdata['assets'] = $user['money'];//下注之前的余额
            $c_betdata['balance'] = $userinfo['money'];
            $c_betdata['fs'] = 0;
            $c_betdata['index_id'] = $userinfo['index_id'];
            $c_betdata['site_id'] = $userinfo['site_id'];
            $c_betdata['pankou'] = $pankou;

            $c_betdata['mingxi_1'] = trim($c_betdata['mingxi_1']);
            $c_betdata['mingxi_2'] = trim($c_betdata['mingxi_2']);
            $c_betdata['mingxi_3'] = trim($c_betdata['mingxi_3']);
            $c_betdata['fc_type'] = $data['lotteryId'];

            if($c_betdata['type'] == '北京快乐8'){
                if(strstr($c_betdata['mingxi_3'], '/')){
                    $str1 = '';
                    if($c_betdata['mingxi_1'] == '选二'){
                        $xia_arr_2 = explode(':', $c_betdata['mingxi_3']);
                        $xia_arr_3 = explode('/', $xia_arr_2[0]);
                        $str = '';
                        foreach ($xia_arr_3 as $kkk => $vvv) {
                            $str .= num2char($vvv).'中';
                        }
                        $str = substr($str,0,(strlen($str)-3));
                        $str1 = $str;
                    }else{
                        $xia_arr_1 =  explode(',', $c_betdata['mingxi_3']);
                        foreach ($xia_arr_1 as $kk => $vv) {
                            $xia_arr_2 = explode(':', $vv);
                            $xia_arr_3 = explode('/', $xia_arr_2[0]);
                            $str = '';
                            foreach ($xia_arr_3 as $kkkk => $vvvv) {
                                $str .= num2char($vvvv).'中';
                            }
                            $str = substr($str,0,(strlen($str)-3));
                            $aaa = $str.':'.$xia_arr_2[1];
                            $str1 .= $aaa.',';
                        }
                        $str1 = substr($str1,0,(strlen($str1)-1));
                    }
                    $c_betdata['mingxi_3'] = $str1;
                }
            }
            
            $c_betdata['win'] = $c_betdata['money']*$c_betdata['odds'];
            if($c_betdata['type'] == '重庆快乐十分' || $c_betdata['type'] == '广东快乐十分' ){
                if($c_betdata['mingxi_2'] == '合单'){
                    $c_betdata['mingxi_2'] = '合数单';
                }elseif($c_betdata['mingxi_2'] == '合双'){
                    $c_betdata['mingxi_2'] = '合数双';
                }
            }
            if($c_betdata['type'] == '重庆时时彩' || $c_betdata['type'] == '天津时时彩'|| $c_betdata['type'] == '新疆时时彩' || $c_betdata['type'] == '江西时时彩'  ){

                if($c_betdata['mingxi_1'] == '前三球' || $c_betdata['mingxi_1'] == '中三球' || $c_betdata['mingxi_1'] == '后三球'){
                    $c_betdata['mingxi_1'] = str_replace('球','',$c_betdata['mingxi_1']);
                }


            }
            if($c_betdata['type'] == '北京赛车pk拾' ){
                if($c_betdata['mingxi_2'] == '龙' || $c_betdata['mingxi_2'] == '虎'){
                    if($c_betdata['mingxi_1'] == '冠军'){
                        $c_betdata['mingxi_3'] = '1V10 龍虎';
                    }elseif($c_betdata['mingxi_1'] == '亚军'){
                        $c_betdata['mingxi_3'] = '2V9 龍虎';
                    }elseif($c_betdata['mingxi_1'] == '第三名'){
                        $c_betdata['mingxi_3'] = '3V8 龍虎';
                    }elseif($c_betdata['mingxi_1'] == '第四名'){
                        $c_betdata['mingxi_3'] = '4V7 龍虎';
                    }elseif($c_betdata['mingxi_1'] == '第五名'){
                        $c_betdata['mingxi_3'] = '5V6 龍虎';
                    }
                    $c_betdata['mingxi_1'] = '龍虎';
                }
                $c_betdata['type'] = '北京赛车PK拾';
                if($c_betdata['mingxi_1'] == '冠、亚军和'){
                    $c_betdata['mingxi_1'] = str_replace('、','',$c_betdata['mingxi_1']);
                }
            }

            //特码生肖处理
            if ($c_betdata['mingxi_3'] == '特肖' && $data['lotteryId'] == 'liuhecai' && $c_betdata['mingxi_1'] == '特码生肖') {
                $c_betdata['mingxi_1'] = '生肖';
            }
            //特码生肖处理
            if ($c_betdata['mingxi_1'] == '合肖' && $data['lotteryId'] == 'liuhecai') {
                $c_betdata['mingxi_1'] = '生肖';
            }

            //正码1-6处理
            if ($data['lotteryId'] == 'liuhecai' ) {
                if ($c_betdata['mingxi_1'] == '正码1-6') {
                    $c_betdata['mingxi_1'] = '正1-6';
                }elseif($c_betdata['mingxi_1'] == '正码特'){
                    $c_betdata['mingxi_1'] = '正特';
                }
            }
            
            if($pankou == 'B' && $c_betdata['mingxi_1'] == '特码'){//反水
                $fs_re =  $this->get_fanshui();
                $fs_re = $fs_re?$fs_re:0;
                $c_betdata['fs'] = $c_betdata['money'] * $fs * $fs_re;
            }
            $betInfos = $this->build_sql_insert($betInfos,$c_betdata);
            $mongo[] = $c_betdata;
            $c_betdata = array();
            
            
            $record['remark'] = "彩票注单：" . $cbetordernum . " , 類型:" . $lotteryname;
            $record['source_id'] = $cbetordernum;
            $record['index_id'] = $userinfo['index_id'];
            $record['source_type'] = 7;//彩票下注类型
            $record['site_id'] = $userinfo['site_id'];
            $record['uid'] = $user['uid'];
            $record['agent_id'] = $user['agent_id'];
            $record['username'] = $user['username'];
            $record['is_shiwan'] = $_SESSION['shiwan'];//判断是否试玩账号
            $record['cash_type'] = 3;
            $record['cash_do_type'] = 2;
            $record['cash_num'] =$v['Money'];
            $record['ptype'] =$ptype;
            $userinfo['money'] -= $v['Money'];
            $record['cash_balance'] = $userinfo['money'];
            $record['cash_date'] = func_nowtime('Y-m-d H:i:s','now');

            $recordInfos = $this->build_sql_insert($recordInfos,$record);
        }
        
        ///拼接sql
        $addbetsql = "insert into c_bet {$betInfos['keys']} values {$betInfos['values']}";
        $addrecordsql = "insert into k_user_cash_record {$recordInfos['keys']} values {$recordInfos['values']}";
        
        $this->private_db->trans_begin();
        ///修改用户余额
        $this->private_db->where('uid='.$user['uid'].' and money > 0 and money >= "'.$all_money.'"');
        $this->private_db->set('money','money-'.$all_money,FALSE);
        $update = $this->private_db->update('k_user');
        
        if(!$this->private_db->affected_rows()){
	        $this->private_db->trans_rollback();
	        $data=array("result"=>5,"msg"=>"下注失败3","errId"=>1026);
            return json_encode($data);
            exit();
	    }
        
        $betresult = $this->private_db->query($addbetsql);
        $recordresult = $this->private_db->query($addrecordsql);
            
        if(empty($update) || empty($betresult) || empty($recordresult)){
            $this->private_db->trans_rollback();
            $data = array("result"=>5,"msg"=>"由于网络堵塞，本次下注失败。","errId"=>1026);
            return json_encode($data);
        }
        
        $this->private_db->trans_commit();
        ////全场限额改动
        if($redis->exists($redis_key)){
            $old_money = $redis->get($redis_key);
            $all_money = $old_money+$all_money;
            $redis->set($redis_key,$all_money);
        }else{
            $redis->set($redis_key,$all_money);
            $redis->expire($redis_key,600);
        }
        ////全场限额改动
        
        ////写入mongo
        require(dirname(__FILE__).'/../../libraries/Mongo_cli.php');
        $conn = Mongo_cli::getInstace(MGO_HOST,MGO_PORT,MGO_FC_USERNAME,MGO_FC_PASSWORD,MGO_FC_DBNAME);
        $tab = MGO_FC_DBNAME;
    
        if(!isset($conn->isok)){
            foreach($mongo as $v){
                try{
                   $res = $conn->$tab->insert($v);
                }catch(Exception $e){
                    $data = json_encode($e);
                    error_log($data.PHP_EOL,3,'./error.log');
                }
            }
        }
        ////写入mongo
        
        $data=array("result"=>1,"msg"=>"");
        return json_encode($data);
    }
    
    protected function build_sql_insert($str,$info){
        $values = '';
        if(!isset($str['keys'])){
            $keys = '';
            foreach($info as $k => $v){
                $keys .= "`{$k}`,";
                $values .= "'{$v}',";
            }
            $str['keys'] = '('.trim($keys,',').')';
            $str['values'] .= '('.trim($values,',').')';
        }else{      ///首次会录入字段
            foreach($info as $v){
                $values .= "'{$v}',";
            }
            $str['values'] .= ',('.trim($values,',').')';
        }
        return $str;
    }

    /**
     * 读取最近8条下注记录
     * @param [int] $uid 用户id
     * @param [array] $fc_type 彩票类别
     * @return [array]
     */
    public function _get_bet($uid, $fc_type)
    {
        $this->private_db->select("*");
        $this->private_db->where("uid", $uid);
        $this->private_db->where("type", $fc_type);
        $this->private_db->order_by("addtime DESC");
        $query = $this->private_db->get('c_bet', 8);
        //echo $this->private_db->last_query();exit;
        $rows = $query->result_array();
        return $rows;
    }


    /**
     * 查看当前是否是封盘时间
     * @param [array] $type 彩票类别
     * @return [bool]
     */
    public function _is_fengpan($type){
        $now_time = func_nowtime();
        $map=array('ok' => 0 , 'kaijiang >' => $now_time , 'fengpan <' => $now_time );
        $this->public_db->where($map);
        $this->public_db->order_by('kaijiang', 'ASC');
        $query = $this->public_db->get($type.'_opentime');
        $rows = $query->result_array();

        if (!empty($rows)) {
            return false;
            //封盘中
        } else {
            return true;
            //开盘中
        }
    }

    /**
     *  查询当天玩法已下注的金额
     * @param [array] $fc_type 彩票类别
     * @param [array] $wanfa_id 彩票玩法id
     * @return [double]
     */
    public function _beted_limit_1($fc_type){
        if (empty($_SESSION['uid'])) {
            return 0;
        }
        $map = array();
        $map['addtime >='] = date("Y-m-d").' 00:00:00';
        $map['addtime <='] = date("Y-m-d").' 23:59:59';
        $map['fc_type'] = $fc_type;
        $map['uid'] = $_SESSION['uid'];
        $map['js'] = 0;
        $map['site_id'] = SITEID;

        $this->private_db->where($map);
        $this->private_db->select("sum(money) as bet");
        $query = $this->private_db->get('c_bet');
        $data = $query->result_array();
        if ($data[0]["bet"]) {
            return $data[0]["bet"];
        }
        return 0;
    }

    /**
     *  查询所有玩法
     * @param [array] $map
     * @return [array]
     */
    public function _get_fc_games($map=array()){
        $this->private_db->from('fc_games');
        $this->private_db->where("state=1");
        $this->private_db->order_by("id", "asc");
        $query = $this->private_db->get();
        $rows = $this->object_array($query->result());
        if (count($rows) == 0) {
            return FALSE;
        }
        return $rows;
    }

   /**
     *  查询封盘时间
     * @param [int] $type 彩票类别
     * @param [string] $adddate 服务器时间增加小时
     * @return [string]
     */
    public function _get_fengpan_time($type, $adddate = '+12 hours'){
        $now_time = func_nowtime("H:i:s");
        $c_time = func_nowtime("Y-m-d");
        $now_time_day = func_nowtime("d");
        switch ($type) {
            case 'fc_3d':
                $where = "ok ='0'";
                break;
            case 'pl_3':
                $where = "ok ='0'";
                break;
            case 'liuhecai':
                $now_time = func_nowtime("Y-m-d H:i:s");
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
        $query = $this->public_db->get($type.'_opentime');
        $data = $query->result_array();
        $data_time = $data[0];

        if(empty($data_time)){
            $this->public_db->select("*");
            $this->public_db->order_by('kaijiang', 'ASC');
            $query = $this->public_db->get($type.'_opentime');
            $data = $query->result_array();
            $data_time = $data[0];
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
            // exit;
            $array['k_t_stro'] = strtotime($k_t) - strtotime($now_time); // 距离开奖的时间
            if((strtotime($k_t) - strtotime($now_time)) < 0){
                $array['k_t_stro'] = (strtotime($c_time.' '.$k_t)+24*60*60) - strtotime($c_time.' '.$now_time);
            }
            $array['o_t_stro'] = strtotime($o_t) - strtotime($now_time); // 距离开盘的时间
            $array['f_state'] = $c_time. ' ' . $data_time['fengpan']; // 封盘状态判断时间
            $array['o_state'] = $c_time. ' ' . $data_time['kaipan']; // 开盘状态判断时间
            $array['c_time'] = $c_time. ' ' . $now_time;
        } else {

            $f_t = $data_time['fengpan'];
            $o_t = $data_time['kaipan'];
            $f_t_day = explode('-', $o_t);
            $f_t_day = $f_t_day[2];
            $left_hours = ($f_t_day - $now_time_day) * 24 * 60 * 60; // 距离下次开盘的天数换成秒
            $array['f_t_stro'] = strtotime($f_t) - strtotime($now_time); // 距离封盘的时间
            $array['o_t_stro'] = (strtotime($o_t) - strtotime($now_time)); // 距离开盘的时间
            $array['f_state'] = $data_time['fengpan']; // 封盘状态判断时间
            $array['o_state'] = $data_time['kaipan']; // 开盘状态判断时间
            $array['c_time'] = $now_time;
            //p($array);exit;
        }
        return $array;
    }
    
    
    ///玩法名称转换为id
    protected function playtoid(){
        
    }
    
    /**
     *  查询该用户的玩法限制金额
     * @param [int] $type 彩票类别
     * @param [string] $wanfa 玩法
     * @return [int]
     * wap端玩法为空
     */
    public function _get_ball_limit($type,$wanfa=''){
        ////pc端处理+wap端单一玩法处理
        if(!is_array($wanfa))
        {
            //连码转换
            if($type == 'gd_ten' || $type == 'cq_ten'){
                if($wanfa == '任选二' || $wanfa == '任选二组' || $wanfa == '任选三' || $wanfa == '任选四' || $wanfa == '任选五'){
                    $wanfa = '连码';
                }
            }
            
            $fc_gdata = $this->Fc_com_model->fc_games_type($type);          ///玩法集合
            
            foreach($fc_gdata as $k=>&$v){                                  ///如果有玩法限定就需要条件
                if($type == 'gd_ten' || $type == 'cq_ten'){
                    if($v['name'] == "總和,龍虎")
                        $v['name'] = '总和';
                }
                
                if($wanfa){                 
                    if($v['name'] != $wanfa){
                        unset($fc_gdata[$k]);
                    }
                }
                $v['id'] = $v['gameid'];
            }
            sort($fc_gdata);
            $fc_gdata = current($fc_gdata);                                 ///多维数据变一维
            ///代理限额
            $limit_data_a = $this->Fc_com_model->get_limit_agent_one($type,$fc_gdata['id'],$_SESSION['agent_id'],SITEID);
            
            ///用户限额
            $limit_data_b = $this->Fc_com_model->get_limit_user($_SESSION['uid']);
            foreach($limit_data_b as $v){
                if($v['type_id'] == $fc_gdata['id'])
                {
                    $v+=$fc_gdata;
                    $user_limit = $v;
                }
            }
            if(!$user_limit){
                $user_limit = $fc_gdata;
            }
            
            $limit = array_merge($limit_data_a,$user_limit);                 ///用户限额覆盖代理限额
            $arr[$limit['name']] = $limit;
            
            return $arr;
        }
        else
        {
            ////wap端处理   可以多玩法一起整所以是以彩种为单位的弄
            $fc_gdata = $this->Fc_com_model->fc_games_type($type);
            
            
            foreach($fc_gdata as $k=>&$v){
                $v['id'] = $v['gameid'];
                
                if($type == 'gd_ten' || $type == 'cq_ten'){
                    if($v['name'] == "總和,龍虎")
                        $v['name'] = '总和';
                }
                
                if(!isset($wanfa[$v['name']])){
                    unset($fc_gdata[$k]);
                }
            }
            
            $limit_data_a = [];
            $new_fc_gdata = [];
            ///代理限额
            foreach($fc_gdata as $val){
                $limit_data_a[] = $this->Fc_com_model->get_limit_agent_one($type,$val['id'],$_SESSION['agent_id'],SITEID);
                $new_fc_gdata[$val['id']] = $val;
            }
            ///用户限额
            $limit_data_b = $this->Fc_com_model->get_limit_user($_SESSION['uid']);
            $limit_data = [];
            foreach($limit_data_a as $val)
            {
                if(isset($limit_data_b[$val['type_id']]))
                    $val = $limit_data_b[$val['type_id']];
                $val += $new_fc_gdata[$val['type_id']];                     ///赋予name 可以在外部比对
                $limit_data[] = $val;
            }
            
            return $limit_data;
        }
    }

    /**
     *  查询盘口
     *  @param [str] $lottery_type 彩种
     *  @param [str] 查询类型：1彩种 2玩法 3盘口
     * @return [str]
     */
    public function _get_pankou($lottery_type){
        if(!empty($lottery_type)){
            
            if(!empty($arr[0])){
                return $arr[0]['value'];
            }else{
                $where = array('site_id' => SITEID);
                $this->private_db->where($where);
                $this->private_db->order_by("id",'ASC');
                $query = $this->private_db->get('web_config');
                $data = $query->result_array();
                $auto = $data[0]['lottery_pan'];
                return $auto;
            }
        }else{
            return false;
        }
    }

    public function _get_luzhu($type){
        //$result = $this->_get_result($type,30,1);
        $this->load->model('lottery/Fc_com_model');
        $result = $this->Fc_com_model->get_fc_auto($type,30);//最近开奖
        if($type == 'cq_ssc' || $type == 'jx_ssc' || $type == 'tj_ssc' || $type == 'xj_ssc'){
            for($i=1;$i<=5;$i++){
                $big_arr ="big_arr_".$i;
                $dan_arr ="dan_arr_".$i;
                $haoma_arr ="haoma_arr_".$i;
                $$big_arr = func_zoushi($result,$i,'大小',$type);
                $$dan_arr = func_zoushi($result,$i,'单双',$type);
                $$haoma_arr = func_zoushi($result,$i,'号码',$type);
            }
            $hedaxiao = func_zoushi($result,0,'总和大小',$type);
            $hedanshuang = func_zoushi($result,0,'总和单双',$type);
            $longhu = func_zoushi($result,0,'龙虎',$type);
            return array($haoma_arr_1,$haoma_arr_2,$haoma_arr_3,$haoma_arr_4,$haoma_arr_5,$hedaxiao,$hedanshuang,$big_arr_1,$dan_arr_1,$big_arr_2,$dan_arr_2,$big_arr_3,$dan_arr_3,$big_arr_4,$dan_arr_4,$big_arr_5,$dan_arr_5,$longhu);
        }elseif($type == 'cq_ten' || $type == 'gd_ten'){
             $re_arr = array();
            for($i=1;$i<=8;$i++){
                $big_arr ="big_arr_".$i;
                $dan_arr ="dan_arr_".$i;
                $heshu_dan_arr ="heshu_dan_arr_".$i;
                $weida_arr ="weida_arr_".$i;
                $fangwei_arr ="fangwei_arr_".$i;
                $zhongfabai_arr ="zhongfabai_arr_".$i;

                $$big_arr = func_zoushi($result,$i,'大小',$type);
                $$dan_arr = func_zoushi($result,$i,'单双',$type);
                $$heshu_dan_arr = func_zoushi($result,$i,'合数单双',$type);
                $$weida_arr = func_zoushi($result,$i,'尾大小',$type);
                $$fangwei_arr = func_zoushi($result,$i,'方位',$type);
                $$zhongfabai_arr = func_zoushi($result,$i,'中发白',$type);

                $re_arr[] = $$big_arr;
                $re_arr[] = $$dan_arr;
                $re_arr[] = $$heshu_dan_arr;
                $re_arr[] = $$weida_arr;
                $re_arr[] = $$fangwei_arr;
                $re_arr[] = $$zhongfabai_arr;


                if($i<=4){
                    $longhu_arr ="longhu_arr_arr_".$i;
                    $$longhu_arr = func_zoushi($result,$i,'龙虎',$type);
                    $re_arr[] = $$longhu_arr;
                }
            }
            $zonghe_da_arr = func_zoushi($result,0,'总和大小',$type);
            $zonghe_dan_arr = func_zoushi($result,0,'总和单双',$type);
            $zonghe_weida_arr = func_zoushi($result,0,'总和尾大小',$type);
            $re_arr[] = $zonghe_da_arr;
            $re_arr[] = $zonghe_dan_arr;
            $re_arr[] = $zonghe_weida_arr;

            $arr =  array_filter($re_arr);
            return $arr;
        }elseif($type == 'bj_10'){
            $re_arr = array();
            $zonghe_da_arr = func_zoushi($result,0,'冠亚和',$type);
            $zonghe_dan_arr = func_zoushi($result,0,'冠亚和大小',$type);
            $zonghe_weida_arr = func_zoushi($result,0,'冠亚和单双',$type);
            $re_arr[] = $zonghe_da_arr;
            $re_arr[] = $zonghe_dan_arr;
            $re_arr[] = $zonghe_weida_arr;
            $arr =  array_filter($re_arr);
            return $arr;
        }
    }

    public function _get_liangmian($type){
        //$result = $this->_get_result($type,30,1);
        $this->load->model('lottery/Fc_com_model');
        $result = $this->Fc_com_model->get_fc_auto($type,30);//最近开奖
        if($type == 'cq_ssc' || $type == 'jx_ssc' || $type == 'tj_ssc' || $type == 'xj_ssc'){
            for($i=1;$i<=5;$i++){
                $big_arr ="big_arr_".$i;
                $dan_arr ="dan_arr_".$i;
                $$big_arr = func_zoushi($result,$i,'大小',$type,true);
                $$dan_arr = func_zoushi($result,$i,'单双',$type,true);

            }
            $hedaxiao = func_zoushi($result,0,'总和大小',$type,true);
            $hedanshuang = func_zoushi($result,0,'总和单双',$type,true);
            $longhu = func_zoushi($result,0,'龙虎',$type,true);
            $arr =  array_filter(array($hedaxiao,$hedanshuang,$big_arr_1,$dan_arr_1,$big_arr_2,$dan_arr_2,$big_arr_3,$dan_arr_3,$big_arr_4,$dan_arr_4,$big_arr_5,$dan_arr_5,$longhu));
        }elseif($type == 'gd_ten' || $type == 'cq_ten'){
            $re_arr = array();
            for($i=1;$i<=8;$i++){
                $big_arr ="big_arr_".$i;
                $dan_arr ="dan_arr_".$i;
                $heshu_dan_arr ="heshu_dan_arr_".$i;
                $weida_arr ="weida_arr_".$i;
                $fangwei_arr ="fangwei_arr_".$i;
                $zhongfabai_arr ="zhongfabai_arr_".$i;

                $$big_arr = func_zoushi($result,$i,'大小',$type,true);
                $$dan_arr = func_zoushi($result,$i,'单双',$type,true);
                $$heshu_dan_arr = func_zoushi($result,$i,'合数单双',$type,true);
                $$weida_arr = func_zoushi($result,$i,'尾大小',$type,true);
                $$fangwei_arr = func_zoushi($result,$i,'方位',$type,true);
                $$zhongfabai_arr = func_zoushi($result,$i,'中发白',$type,true);

                $re_arr[] = $$big_arr;
                $re_arr[] = $$dan_arr;
                $re_arr[] = $$heshu_dan_arr;
                $re_arr[] = $$weida_arr;
                $re_arr[] = $$fangwei_arr;
                $re_arr[] = $$zhongfabai_arr;


                if($i<=4){
                    $longhu_arr ="longhu_arr_".$i;
                    $$longhu_arr = func_zoushi($result,$i,'龙虎',$type,true);
                    $re_arr[] = $$longhu_arr;
                }
            }
            $zonghe_da_arr = func_zoushi($result,0,'总和大小',$type,true);
            $zonghe_dan_arr = func_zoushi($result,0,'总和单双',$type,true);
            $zonghe_weida_arr = func_zoushi($result,0,'总和尾大小',$type,true);
            $re_arr[] = $zonghe_da_arr;
            $re_arr[] = $zonghe_dan_arr;
            $re_arr[] = $zonghe_weida_arr;

            $arr =  array_filter($re_arr);
        }elseif($type == 'bj_10'){
            for($i=1;$i<=10;$i++){
                $big_arr ="big_arr_".$i;
                $dan_arr ="dan_arr_".$i;
                $$big_arr =  func_zoushi($result,$i,'大小',$type,true);
                $$dan_arr = func_zoushi($result,$i,'单双',$type,true);
                $re_arr[] = $$big_arr;
                $re_arr[] = $$dan_arr;

                if($i<=5){
                    $longhu_arr ="longhu_arr_".$i;
                    $$longhu_arr = func_zoushi($result,$i,'龙虎',$type,true);
                    $re_arr[] = $$longhu_arr;
                }
            }
            // $zonghe_da_arr = func_zoushi($result,0,'冠亚和大小',$type,true);

            $arr =  array_filter($re_arr);

        }
        $ages = array();
        if(!empty($arr)){
            foreach ($arr as $v) {
                $ages[] = $v[1];
            }
            array_multisort($ages, SORT_DESC, $arr);
        }

        // p($arr);
        return $arr;
    }

    //JS需要的JSON排列
    function pailie_odds_json($arr){

        $arr1 = array();
        $str2 =$str3 = $str4 = $str5 = '';
        foreach ($arr as $k => $v) {
          if($v['id'] == 842 || $v['id'] == 7788 ){
            $str3 .= '3/3:'.$v['odds_value'].',';
            // $str3 = rtrim(',',$str3);
            $arr1['j'.$v['id']] = $str3;
          }elseif($v['id'] == 843 || $v['id'] == 7789){
            $str3 .= '3/2:'.$v['odds_value'].',';
            // $str3 = rtrim(',',$str3);
            $arr1['j'.$v['id']] = $str3;
          }elseif($v['id'] == 841 || $v['id'] == 7787){
            $str2 .= '2/2:'.$v['odds_value'].',';
            // $str3 = rtrim(',',$str3);
            $arr1['j'.$v['id']] = $str2;
          }elseif($v['id'] == 844  || $v['id'] == 7790 ){
            $str4 .= '4/4:'.$v['odds_value'].',';
            // $str4 = rtrim(',',$str4);
            $arr1['j'.$v['id']] = $str4;
          }elseif( $v['id'] == 845  || $v['id'] == 7791 ){
            $str4 .= '4/3:'.$v['odds_value'].',';
            // $str4 = rtrim(',',$str4);
            $arr1['j'.$v['id']] = $str4;
          }elseif($v['id'] == 846 || $v['id'] == 7792 ){
            $str4 .= '4/2:'.$v['odds_value'].',';
            // $str4 = rtrim(',',$str4);
            $arr1['j'.$v['id']] = $str4;
          }elseif($v['id'] == 847  || $v['id'] == 7793 ){
            $str5 .= '5/5:'.$v['odds_value'].',';
            // $str5 = rtrim(',',$str5);
            $arr1['j'.$v['id']] = $str5;
          }elseif( $v['id'] == 848  || $v['id'] == 7794 ){
            $str5 .= '5/4:'.$v['odds_value'].',';
            // $str5 = rtrim(',',$str5);
            $arr1['j'.$v['id']] = $str5;
          }elseif( $v['id'] == 849 || $v['id'] == 7795){
            $str5 .= '5/3:'.$v['odds_value'].',';
            //$str5 = rtrim(',',$str5);
            $arr1['j'.$v['id']] = $str5;
          }else{
            $arr1['j'.$v['id']] = $v['odds_value'];
          }
        }
        return $arr1;
    }

    public function _get_json($type){
        $this->load->model('lottery/Fc_com_model');
        $json_data;
        $fengpan_time = $this->_get_fengpan_time($type);
        $kaijiang = $this->Fc_com_model->get_fc_auto($type,1);               //最近开奖结果
        
        if(!is_array($kaijiang)){                                            ///报错补丁
            $kaijiang = $this->Fc_com_model->get_fc_auto($type,1,false);
        }
        
        if($kaijiang){
            $last_kaijiang = array();
            foreach ($kaijiang as $k => $v) {
                $balls = explode('ball_', $k);
                if( !empty($balls[1]) ){
                    $last_kaijiang[$balls[1]] = $v;
                }else{
                    continue;
                }
            }
            ksort($last_kaijiang,SORT_NUMERIC);
            $last_kaijiang = implode(',',$last_kaijiang);
        }
        $odds_json = $this->Fc_com_model->get_fc_odds_one($type,0,'A');

        $pailie_odds_json = $this->pailie_odds_json($odds_json);
        
        $money = $this->_get_userinfo($_SESSION['uid']);
        $NotCountSum = $this->_beted_limit_1($type);
        $changlong = $this->_get_liangmian($type);

        $json_data['Success'] = 1;
        $json_data['Msg'] = '';
        if($_SESSION['username']){
            $IsLogin = true;
        }else{
            $IsLogin = false;
        }

        $json_data['ExtendObj'] = array('IsLogin' => $IsLogin);//是否登录
        $json_data['OK'] = false;
        $json_data['PageCount'] = 0;

        $json_data['Obj']['IsLogin'] = $IsLogin;
        $json_data['Obj']['ChangLong'] = $changlong;
        $json_data['Obj']['CurrentPeriod'] = $this->Fc_com_model->get_fc_qishu($type);//当前期数
        $json_data['Obj']['CloseCount'] = $fengpan_time['f_t_stro'];//距离封盘时间
        
        $json_data['Obj']['OpenCount'] = $fengpan_time['k_t_stro'];//距离开奖时间
        $json_data['Obj']['PrePeriodNumber'] = $kaijiang['qishu'];//最近开奖期数
        $json_data['Obj']['PreResult'] = $last_kaijiang;//最近开奖结果
        
        $json_data['Obj']['NotCountSum'] = $NotCountSum?$NotCountSum:'0.00';//及时下注(当期下注)
        $json_data['Obj']['Balance'] = $money['money']?$money['money']:'0.00';//余额
        $chuqiulv = $this->_get_chuqiu($type);
        $miss = $this->_get_miss($type);
        $json_data['Obj']['ZongchuYilou'] = array("miss" => $miss , "hit" => $chuqiulv);//出球概率
        $json_data['Obj']['Lines'] = $pailie_odds_json;//所有的赔率
        $luzhu = $this->_get_luzhu($type);
        $json_data['Obj']['LuZhu'] = $luzhu;//最下面子出球概率
        
        return JSON($json_data);
    }
    
    public function get_lottery_type($LotteryId,$key='type'){
        $mapf['table'] = 'fc_games';
        $mapf['select'] = $key;
        $mapf['where']['id'] = $LotteryId;
        $list = $this->get_table_one($mapf,3);
        return $list[$key];
    }
    
    //排列单个彩种赔率 数据
    public function pl_odds($array){
    	foreach ($array as $k=>$v){
    		$row[$v['id']] = $v['odds_value'];
    	}
    	return $row;
    }
    
    public function _get_oneday_recored($uid,$date){
        //获取当前查询日期
        $begin_date = $date.' 00:00:00';
        $end_date = $date.' 23:59:59';
        $cstr = '';
        $cstr .= "sum(case when addtime >= '".$begin_date."' and addtime <= '".$end_date."' and `status` in ('1','2') then money end ) as bet".$key.",sum(case when addtime >= '".$begin_date."' and addtime <= '".$end_date."' and `status` = 0 then money end ) as nobet".$key.",sum(case when addtime >= '".$begin_date."' and addtime <= '".$end_date."' and `status` in ('1','2') then win end ) as win".$key.",sum(case when addtime >= '".$begin_date."' and addtime <= '".$end_date."'  and `status` = 0 then win end ) as nowin".$key.",count(case when addtime >= '".$begin_date."' and addtime <= '".$end_date."' then id end) as num".$key.',';
        $cstr .= 'uid,type';
        $this->db->from('c_bet');
        $this->db->where('uid',$uid);
        $this->db->where('site_id',SITEID);
        $this->db->group_by("type");
        $this->db->select($cstr);
        $result = $this->db->get()->result_array();
        return $result;
    }
    
    public function _get_week_recored($uid){
        //获取七天前日期
        for ($i=0; $i <7 ; $i++) {
          $tmp_date = date('Y-m-d', strtotime("-$i days"));
          $weeks[$i]['tmp_date'] = $tmp_date;
          $weeks[$i]['begin_date'] = $tmp_date.' 00:00:00';
          $weeks[$i]['end_date'] = $tmp_date.' 23:59:59';
        }
        $cstr = '';
        foreach ($weeks as $key => $val) {
            $cstr .= "sum(case when addtime >= '".$val['begin_date']."' and addtime <= '".$val['end_date']."' and `status` in ('1','2') then money end ) as bet".$key.",sum(case when addtime >= '".$val['begin_date']."' and addtime <= '".$val['end_date']."' and `status` = 0 then money end ) as nobet".$key.",sum(case when addtime >= '".$val['begin_date']."' and addtime <= '".$val['end_date']."' and `status` in ('1','2') then win end ) as win".$key.",sum(case when addtime >= '".$val['begin_date']."' and addtime <= '".$val['end_date']."'  and `status` = 0 then win end ) as nowin".$key.",count(case when addtime >= '".$val['begin_date']."' and addtime <= '".$val['end_date']."' then id end) as num".$key.',';

        }
        $cstr .= 'uid';
        $this->db->from('c_bet');
        $this->db->where('uid',$uid);
        $this->db->where('site_id',SITEID);
        //$this->db->where_in('status','1,2');
        $this->db->select($cstr);
        $result = $this->db->get()->row_array();
        foreach($weeks as $k => $v){
            $result["date".$k] = $v['tmp_date'];
        }
        return $result;
    }
}
?>