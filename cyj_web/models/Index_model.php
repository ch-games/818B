<?php
if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Index_model extends MY_Model {

	function __construct() {
		$this->init_db();
	}
	public function qrcode(){
		$query=$this->private_db->query('select * from web_config where site_id=\''.SITEID.'\' AND  index_id=\''.INDEX_ID.'\'');
		$row=$query->row_array();
		return $row;
	}

	//介绍人是否合法判断
	public function is_intr($intr){
        $mapS = array();
		$mapS['table'] = 'k_user_agent';
	    $mapS['select'] = 'id,intr';
		$mapS['where']['intr'] = $intr;
		$mapS['where']['site_id'] = SITEID;
		$mapS['where']['index_id'] = INDEX_ID;
		$mapS['where']['is_demo'] = 0;//屏蔽测试账号
		$mapS['where']['agent_type'] = 'a_t';
		return $this->rfind($mapS);
	}

		//判断会员id是否存在
	public function is_uuno_true($uuno,$ip){
		//判断是否开启会员推广
		$map = array();
		$map['table'] = 'k_user_spread_set';
		$map['where']['site_id'] = SITEID;
		$map['where']['index_id'] = INDEX_ID;
		$is_spread = $this->rfind($map);
		//开启
		if ($is_spread && $is_spread['state']) {
		    $mapS = array();
			$mapS['table'] = 'k_user';
		    $mapS['select'] = 'uid,agent_id';
			$mapS['where']['site_id'] = SITEID;
			$mapS['where']['index_id'] = INDEX_ID;
			if ($ip &&  $is_spread['is_ip']) {
			    $mapS['where']['reg_ip <>'] = $ip;
			}
			$mapS['where']['uid'] = $uuno;
			$log = $this->rfind($mapS);
			if ($log) {
				//存在则写入数据
			    $_SESSION['uuno_uid'] = $uuno;
			    if ($is_spread['is_up_agent']) {
			        $_SESSION['uuno_agent_id'] = $log['agent_id'];
			    }
			}else{
				unset($_SESSION['uuno_uid']);
		        unset($_SESSION['uuno_agent_id']);
			}
		}

	}

	//获取logo信息
	public function get_logo(){
	    if($_SESSION['ty'] == 11){
	    	$this->db->from('info_logo_edit');
	    }else{
	    	$this->db->from('info_logo_use');
	    }
		$this->db->where('site_id',SITEID);
		$this->db->where('index_id',INDEX_ID);
		$this->db->where('type',11);
		$this->db->where('state',1);
		$this->db->select('logo_url');
		$logo=$this->db->get()->row_array();
		$logo['logo_url']= $this->replacedomain($logo['logo_url']);
		return $logo;
	}

	//读取首页轮播图
	public function get_flash($value='') {

		//首页幻灯片
		if($_SESSION['ty'] == 13){
			$this->db->from('info_flash_edit');
		}else{
			$this->db->from('info_flash_use');
			$this->db->where('case_state',0);
		}
		$this->db->where('type',13);
		$this->db->where('site_id',SITEID);
		$this->db->where('index_id',INDEX_ID);
		$flash = $this->db->get()->row_array();
		$result = array();
		if(!empty($flash)){
			$arr = array('A','B','C','D','E');
			foreach ($arr as $k => $v) {
				 $img_field = 'img_'.$v;
				 $title_field = 'title_'.$v;
				 $url_field = 'url_'.$v;
	    	 	if(!empty($flash[$img_field]) && $flash[$img_field] != 1){
	    	 		$result[$v]['img'] = $this->replacedomain($flash[$img_field]);
	    	 		$result[$v]['title'] = $flash[$title_field];
	    	 		$result[$v]['url'] = $flash[$url_field];
	    	 	}
			}
		}else{
			$result[0]['img'] = '';
	    	$result[0]['title'] = '您未上传轮播图，请后台添加！！！';
	    	$result[0]['url'] = '#';
		}
		return $result;
	}

	/*
	 *视讯
	 */
	function get_livetop() {
	    //视讯配置
	    $map = array();
	    $map['site_id'] = SITEID;
	    $map['index_id'] = INDEX_ID;
	    $video_config = $this->db->from('web_config')->where($map)->select('video_module')->get()->row_array();
	    $video_config = explode(',',$video_config['video_module']);
	    return $video_config;
    }

    /*
	 *视讯
	 */
	function get_lottery() {
	    //视讯配置
	    $map = array();
	    $map['site_id'] = SITEID;
	    $map['index_id'] = INDEX_ID;

	    $video_config = $this->db->from('web_config')->where($map)->select('fc_module')->get()->row_array();
	    $video_config = explode(',',$video_config['fc_module']);
	    return $video_config;
    }

        //获取下拉菜单数组
    function get_xl_top() {
	    //获取电子配置
	    $map = array();
	    $map['site_id'] = SITEID;
	    $map['index_id'] = INDEX_ID;

	    $web_config = $this->db->from('web_config')->where($map)->select(array('video_module','dz_module'))->get()->row_array();

	    $video_config = explode(',',$web_config['video_module']);
        $egame_config = explode(',',$web_config['dz_module']);
        $video_arr =array('bbin','ag','og','mg','ct','lebo','lmg','gd','sa','ab','gpi','dg99');
        $this_video_arr = array();
        $this_egame_arr = array();
        foreach ($video_config as $k => $v) {
            if(in_array($v, $video_arr)&&$v!='dg99'){
                $this_video_arr[$k]['up'] =  strtoupper($v);
                $this_video_arr[$k]['low'] =  $v;
            }
        }

        //电子顺序匹配定位
        foreach ($egame_config as $key => $val) {
            if($val == 'bbdz'){
                $xltype = 'bbin';
            }elseif (substr($val, -2)=='dz') {
                $xltype = substr($val ,0,-2);
            }else{
                $xltype = $val;
            }
            $this_egame_arr[$key]['up'] =  strtoupper($xltype);
            $this_egame_arr[$key]['low'] =  $xltype;
        }
		
		$this_sport_arr = array();
		if(in_array("bbin", $video_config)){
			$this_sport_arr['bbin'] = 1;
		}
		if(in_array("im", $video_config)){
			$this_sport_arr['im'] = 1;
		}
		
		$this_lottory_arr = array();
		if(in_array("dg99",$video_config)){
			$this_lottory_arr['dg99'] = 1;
		}
		if(in_array("bbin",$video_config)){
			$this_lottory_arr['bbin'] = 1;
		}
	    return array($this_video_arr,$this_egame_arr,$this_sport_arr,$this_lottory_arr);
	}

	//获取视讯自定义图片
    public function get_video_imgs(){
    	//获取视讯自定义图片
    	$video_imgs = $tmp_video = array();
        $tmp_video = $this->db->from('info_video')->where(array('site_id'=>SITEID,'index_id'=>INDEX_ID))->select('type,img_url')->get()->result_array();

        foreach ($tmp_video as $key => $val) {
            $video_imgs[$val['type']] = $val;
        }
        return $video_imgs;
    }

    	/**
	 * 获取优惠分类
	 * @param  [array] $map [查询条件]
	 * @return [array]      [优惠]
	 * PK 黄
	 */
	public function get_promot_cate($map) {
		if($_SESSION['ty'] == 14){
			$data = $this->db->from("info_activity_edit")->where($map)->order_by('sort ASC')->get()->result_array();
		}else{
			$data = $this->db->from("info_activity_use")->where($map)->order_by('sort ASC')->get()->result_array();
		}
		return $data;
	}

	/**
	 * 优惠活动内页
	 * @return [array] [优惠活动分类和内容]
	 * PK 黄
	 */
	public function get_promotions(){
		$info = array();
		$info['index_id'] = INDEX_ID;
		$info['site_id'] = SITEID;
		$info['state'] = 1;
		$info['ctype'] = 2;
		$data = $this->get_promot_cate($info);
		if(!empty($data) && is_array($data)){
			foreach ($data as $key => $value) {
				$data[$key]['status'] = ','.$value['pid'];
			}
			$promotion['data'] = $data;
		}
		$info['ctype'] = 1;
		$cate = $this->get_promot_cate($info);
		if(!empty($cate) && is_array($cate)){
			$promotion['cate'] = $cate;
		}
		return $promotion;
	}

	//获取弹窗信息
	public function get_site_pop($type){
        $pop['pop_state'] = 0;
        if ($type==1) {
        	$pop_config = array();

		    //是否开启多前台
		    $map['index_id'] = INDEX_ID;
		    $map['site_id'] = SITEID;
		    $map['is_delete'] = 1;
		    $map['ad_type'] = $type;
	        $pop = $this->M(array('tab'=>'site_ad','type'=>1))
	                    ->where($map)
	                    ->order('add_date DESC')
	                    ->find();
    	    $pop_config = $this->M(array('tab'=>'site_pop_config','type'=>1))
                        ->where(['site_id'=>SITEID, 'index_id'=>INDEX_ID])
                        ->find();
            if (!empty($pop) && !empty($pop_config)) {
                $pop['pop_config'] = $pop_config;
                $pop['content'] = $this->replacedomain($pop['content']);
                $pop['pop_state'] = 1;
            }
        }elseif ($type == 3) {
            $db_model['tab'] = 'site_adv_management';
            $db_model['type'] = 4;
            $ad_pop2 = $this->M($db_model);
			$ad_pop = $ad_pop2
                        ->where("status=1 and start_time<=now() and end_time>=now() and(adv_source=2 or (site_id='".SITEID."' and index_id='".INDEX_ID."'))")
                        ->order('adv_source DESC,adv_sort ASC')
						->limit('0,7')
                        ->select();
            if (!empty($ad_pop)) {
                //判断广告剔除的站点
				$new_ad_pop = array();
				foreach($ad_pop as $k=>$v){
					$site_del = explode(',', $v['site_del']);
					if(!in_array(SITEID, $site_del)){
						$pop['pop_state'] = 1;
						$ad_pop[$k]['img'] = $this->replacedomain($ad_pop[$k]['adv_url']);
						$new_ad_pop[$k] = $ad_pop[$k];
						if($v['is_blank'] == 1 && $v['urltype'] != 20){
							$new_ad_pop[$k]['b_str'] = 'target="_blank"';
						}
						if($v['is_inter'] == 0){
							$new_ad_pop[$k]['click'] = $this->geturl($v['urltype'],$v['Ad_url'],$v['lottery'],$v['dz']);
							$new_ad_pop[$k]['url'] = "javascript:;";
						}else{
							$new_ad_pop[$k]['click'] = '';
							$new_ad_pop[$k]['url'] = $ad_pop[$k]['Ad_url'];
						}
					}
				}					
				$pop['data']=$new_ad_pop;
            }
        }
        return $pop;
	}

	//获取网址底部导航
	public function get_meau_footer(){
		$map_mf = array();
		if(empty($_SESSION['ty']) || $_SESSION['ty'] > 8 || $_SESSION['ty'] < 3){
	    	$map_mf['table'] = 'info_iword_use';
			$redis = RedisConPool::getInstace();
			$redis_key = SITEID.'_iword_title_'.INDEX_ID;
			//$redis->delete($redis_key);
			$vdata = $redis->hgetall($redis_key);
	  	}elseif($_SESSION['ty'] >= 3 && $_SESSION['ty'] <= 8){      //预览文案
	  		$map_mf['table'] = 'info_iword_edit';
	  		$map_mf['where']['case_state'] = 0;
	  	}

      if (empty($vdata)) {
      		$map_mf['where']['site_id'] = SITEID;
      		$map_mf['where']['index_id'] = INDEX_ID;
      		$map_mf['where']['state'] = 1;
      		$map_mf['order']= "sort desc";
            $map_mf['select']= "id,title,type";
      		$meau_foot = $this->rget($map_mf);
      		if ($meau_foot) {
      		    $i = count($meau_foot);
      			foreach ($meau_foot as $k => $v) {
      				if ($i > ($k+1)) {
      					$meau_foot[$k]['str_m'] = '|';
      				}
      			}
      		}

            foreach ($meau_foot as $key => $val) {
                $agent_json = json_encode($val, JSON_UNESCAPED_UNICODE);
                $agent_json = str_replace('"', '-', $agent_json);
                $hset[$key] = $agent_json;
            }

            $redis->hmset($redis_key,$hset);
        }else{
            $vdata = str_replace('-', '"', $vdata);
            foreach ($vdata as $key => $val) {
                $meau_foot[$key] = json_decode($val,true);//转数组

            }
        }
		return $meau_foot;
	}

	/*
	 *获取视讯样式
	 */
	function get_livetop_style() {
	    //视讯配置
		$map = array();
	    $map['site_id'] = SITEID;
	    $map['index_id'] = INDEX_ID;
	    $map['state'] = 1;
	    $data = $this->db->from('info_video_use')->where($map)->select()->get()->row_array();
		if (empty($data)) { return false;}
        foreach ($data as $key => $val) {
            $data_json = json_encode($val, JSON_UNESCAPED_UNICODE);
            $data_json = str_replace('"', '--', $data_json);
            $id = $val['id'];
			$hset[$id] = $data_json;
        }
		return $data;
   }

    public function get_promotions_width(){
   		//优惠活动宽度
		$this->db->from('info_activity_promotion_set');
    	$this->db->where("index_id= '". INDEX_ID."'and site_id = '".SITEID."'");
    	$this->db->select('max_width');
		$width=$this->db->get()->row_array();
		//获取优惠活动宽度 redis
      	$redis = RedisConPool::getInstace();
        $redis_key_width = SITEID . '_' . INDEX_ID . '_promotion_width';
      	if($redis->exists($redis_key_width)){
        	$max_width = json_decode($redis->get($redis_key_width),TRUE);
        }else{
        	$max_width = empty($width)?'960':$width['max_width'];
        	$redis->set($redis_key_width , json_encode($max_width));
        }
        return $max_width;
   }


   //获取站点状态
   public function get_site_info(){
       $db_model['tab'] = 'site_info';
       $db_model['type'] = 4;
       $map_m['index_id'] = INDEX_ID;
       $map_m['site_id'] = SITEID;
       $data = $this->M($db_model)->where($map_m)->find(); 
       return $data;
   }

   	//获取URL
	public function geturl($type,$url = '',$lottery_type = 'liuhecai',$dz = 'm'){
		switch ($type) {
			case '1':
		        return "getPager('-','sports','m');";
		        break;
		      case '2':
		        return "getPager('-','livetop','m');";
		        break;
		      case '3':
		        return "getPager('-','egame','".$dz."');";
		        break;
		      case '4':
		        return "getPager('-','lottery','".$lottery_type."');";
		        break;
		      case '5':
		        return "getPager('-','youhui');";
		        break;
		      case '6':
		        return "getPager('-','iword','5');";
		        break;
		      case '7':
		        return "getPager('-','shiwan_reg');";
		        break;
		      case '8':
		        return "getPager('-','iword','8');";
		        break;
		      case '9':
		        return "getPager('-','iword','3');";
		        break;
		      case '10':
		        return "getPager('-','iword','4');";
		        break;
		      case '11':
		        return "getPager('-','zhuce');";
		        break;
		      case '12':
		        return "getPager('-','iword','6');";
		        break;
		      case '13':
		        return "getPager('-','iword','7');";
		        break;
			  case '14':
		        return "getPager('-','detect');";
		        break;
		      case '19':
		        return "getPager('-','iword','19');";
		        break;
		      case '20':
		        return "OnlineService('".$url."')";
		        break;
    	}
	}
}