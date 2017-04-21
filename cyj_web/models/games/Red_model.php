<?php
defined('BASEPATH') or exit('No direct script access allowed');

//红包类
class Red_model extends MY_Model {

	public $redis = null;

	public function __construct()
	{
	  	parent::__construct();
		$this->redis = RedisConPool::getInstace();
		$this->load->model('Common_model');
	}

	//获取红包活动列表
	public function get_list(){
		//"redenvelopes";
		//提前3天返回
		$list = array();

		$day_1 = $this->redis->lrange("red_bag".SITEID."_".INDEX_ID."_".date("Y-m-d",strtotime("-1 day")),0,-1);

        $day_1 = str_replace('--', '"', $day_1);
        if (FALSE !== $day_1){
        	foreach ($day_1 as $value) {
        		$list[] = json_decode($value,TRUE);
        	}
        	/*
        	$temp = json_decode($day1);
        	if(is_array($temp)){
        		$list = array_merge($list ,$temp);
        	}*/
        }
        //当天的
        //$list = array();
        $day1 = $this->redis->lrange("red_bag".SITEID."_".INDEX_ID."_".date("Y-m-d",strtotime("+0 day")),0,-1);

        $day1 = str_replace('--', '"', $day1);
        if (FALSE !== $day1){
        	foreach ($day1 as $value) {
        		$list[] = json_decode($value,TRUE);
        	}
        	//var_dump($list);exit;
        	/*
        	$temp = json_decode($day1);
        	if(is_array($temp)){
        		$list = array_merge($list ,$temp);
        	}*/
        }
        //第一天的
        $day2 = $this->redis->lrange("red_bag".SITEID."_".INDEX_ID."_".date("Y-m-d",strtotime("+1 day")),0,-1);

        $day2 = str_replace('--', '"', $day2);
        //$day2 =  $this->redis->get("red_bag".SITEID."_".INDEX_ID."_".date("Y-m-d",strtotime("+1 day")));
		if (FALSE !== $day2){
        	foreach ($day2 as $value) {
        		$list[] = json_decode($value,TRUE);
        	}
        }
        //第二天的
        $day3 = $this->redis->lrange("red_bag".SITEID."_".INDEX_ID."_".date("Y-m-d",strtotime("-2 day")),0,-1);
        $day3 = str_replace('--', '"', $day3);
        //$day3 = $this->redis->get("red_bag".SITEID."_".INDEX_ID."_".date("Y-m-d",strtotime("+2 day")));
		if (FALSE !== $day3){
        	foreach ($day3 as $value) {
        		$list[] = json_decode($value,TRUE);
        	}
        }
        /*
	        $begin_date = date("Y-m-d",strtotime("Y-m-d"))." 00:00:00";
	        $end_date = date("Y-m-d",strtotime("+2 day"))." 23:59:59";
			$this->private_db->select("*")
				->private_db->where("site_id = '" . SITEID . "' and index_id = '" . INDEX_ID . "'")
				->private_db->where("make_sure = '1' and status = '0' ")
				->private_db->where("starttime > '" . $begin_date . "' and endtime < '" . $end_date . "'")
				->private_db->limit(0, 20)
				->private_db->order_by('starttime', 'ASC');

			$rows = $this->private_db->get('redenvelopes')->row_array();
			//按天存入redis
			$key = SITEID."_".INDEX_ID."_".date("Y-m-d",strtotime("Y-m-d"));
			$this->redis->set($key ,json_encode($rows));
		*/

		return $list;
	}

	//初始化红包数据到redis
	//$red红包活动的数据信息
	public function init_game($red = array()){

		if(intval($red["id"]) <= 0){
			return FALSE;
		}
		$key = "red_bag".SITEID."_".INDEX_ID."_littlered_".$red["id"];
		$lock_key = "red_bag".SITEID."_".INDEX_ID."_littlered_".$red["id"]."_lock";
		if($this->redis->EXISTS($key)){
			return;
		}
		//如果有加锁返回
		if(FALSE !== $this->redis->get($lock_key)){
			return;
		}
		//写入锁
		if(FALSE === $this->redis->set($lock_key,"lock")){
			return;
		}
		//是否终止
		$this->private_db->where('status', "1");
		$this->private_db->where('id', $red["id"]);
		$this->private_db->from('redenvelopes');
		$ii = $this->private_db->count_all_results();
		if($ii == 0){
			$this->redis->delete($key);
			return;
		}

		//查询小红包
		if(intval($red['red_num']) > 300){
			$maxpage = ceil(floatval($red['red_num'])/300.00);

			for ($i  =0;$i < intval($maxpage);$i++){
				$start_id = 300 * $i;

				$this->private_db->select("*")
					 ->where("site_id = '" . SITEID . "' and index_id = '" . INDEX_ID . "'")
					 ->where("make_sure = '1' ")
					 ->where("uid = '' ")
					 ->where("rid",$red["id"])
					 ->order_by('id', 'DESC');

				$rows = $this->private_db->get('redenvelopes_log',300,$start_id)->result_array();
				foreach ($rows as $value) {
					$l_vaue = json_encode($value,JSON_UNESCAPED_UNICODE);
					if(FALSE === $l_vaue){
						$this->redis->delete($key);
						return FALSE;
					}
					if(FALSE === $this->redis->lpush($key,$l_vaue)){

						$this->redis->delete($key);
						return FALSE;
					}
				}
			}
		} else {
			$this->private_db
				 ->where("site_id = '" . SITEID . "' and index_id = '" . INDEX_ID . "'")
				 ->where("make_sure = '1'")
				 ->where("uid = ''")
				 ->where("rid",$red["id"])
				 ->order_by('id', 'DESC');
			$rows = $this->private_db->get('redenvelopes_log')->result_array();
			foreach ($rows as $value) {
				$l_vaue = json_encode($value,JSON_UNESCAPED_UNICODE);
				if(FALSE === $l_vaue){
					$this->redis->delete($key);
					return FALSE;
				}
				if(FALSE === $this->redis->lpush($key,$l_vaue)){
					$this->redis->delete($key);
					return FALSE;
				}
			}
		}
		//解锁
		$this->redis->delete($lock_key);
		return TRUE;
	}


	public function log_error($key,$msg){
		$path=APPPATH .'/../../../../../cache/'.SITEID.INDEX_ID. 'cache/redbag';
		//$path='redbag';
		if(!is_dir($path)){
			mkdir($path);
		}
		file_put_contents($path.'/'.date('Ymd').".txt", chr(012).$key.'['.date('Y-m-d H:i:s').']=>'.$msg,FILE_APPEND);
		//$this->redis->set("red_error_".$key.date('Y-m-d H:i:s'),$msg);
	}

	//获取红包
	public function snatch($rid,$red = array()){

		$key = "red_bag".SITEID."_".INDEX_ID."_littlered_".$rid;

		$data = array('Code' =>99);
		$size  = $this->redis->lsize($key);
		if(FALSE === $size){
			$data = array('Code' =>1);//活动没开始
		}
		if($size === 0){
			$data = array('Code' =>2); //没有红包了
			$red_row['status'] = "2";
			$this->private_db->where('id', $rid);
			$this->private_db->update("redenvelopes",$red_row);
		}

		if($size > 0){
			if(empty($red)){
				$red = $this->private_db->from('redenvelopes')->where('id', $rid)->get()->first_row();
			}
			//更新大红包状态
			$red_row['status'] = "1";
			$this->private_db->where('id', $rid);
			$this->private_db->update("redenvelopes",$red_row);

			$temp = $this->get_finishlist($red['id']);
			if($temp["Code"] === 0 ){
				//查看今天是有抢过红包
				$this->private_db->where('createtime >= ',date('Y-m-d')." 00:00:00");
				$this->private_db->where('createtime <= ',date('Y-m-d')." 23:59:59");
				$this->private_db->where('uid', $_SESSION['uid']);
				$this->private_db->from('redenvelopes_log');
				$ii = $this->private_db->count_all_results();
				if($ii > 0){
					//你已经抢过
					return array('Code' =>3);
				}

				//查看此IP是有抢过红包
				$this->private_db->where('createip', $this->get_ip());
				$this->private_db->where('is_ip',1);
				$this->private_db->where('rid',$rid);
				$this->private_db->from('redenvelopes_log');
				$in = $this->private_db->count_all_results();
				if($in > 0){
					//你已经抢过
					return array('Code' =>22);
				}
				//查看存款金额
				if($red['in_sum']>0){
					$income_data = $this->all_income($red['instarttime'],$red['inendtime']);
					if($income_data['u-'.$_SESSION['uid']]){
						$own = $income_data['u-'.$_SESSION['uid']];
						if($own['deposit_num']+0<$red['in_sum']){
						//没有达到存款金额
						return array('Code' =>23);
						}
					}else{
						return array('Code' =>23);
					}

				}
				if($red['bet_sum']>0){
					$date[0] = $red['auditstarttime'];
					$date[1] = $red['auditendtime'];
					//稽核数据
					$audit_data = $this->all_audit($date);
					$own = $audit_data['all_audit']['u-'.$_SESSION['username']];
					if($own['audit']<$red['bet_sum']){
						//没有达到打码条件
						return array('Code' =>24);
					}
				}
			}

			//小红包
			$little_red = $this->redis->lpop($key);
			$little_red = str_replace('--', '"', $little_red);
			$little_red_row = json_decode($little_red,TRUE);
			$this->db->from('k_user');
			$this->db->select('money');
			$this->db->where('uid',$_SESSION['uid']);
			$this->db->where('site_id',SITEID);
			$this->db->where('index_id',INDEX_ID);
			$userinfo = $this->db->get()->row_array();

			$this->db->from('k_user_level');
			$this->db->select('level_des');
			$this->db->where('id',$_SESSION['level_id']);
			$this->db->where('site_id',SITEID);
			$this->db->where('index_id',INDEX_ID);
			$level_es = $this->db->get()->row_array();

			$this->db->from('add_bag_log');
			$this->db->where('uid',$_SESSION['uid']);
			$this->db->where('rid',$rid);
			$bag_log = $this->db->get()->row_array();
			if($bag_log){
				return array('Code' =>3);//已经抢过,不能重复在抢
			}
			$little_red_row['username'] = $_SESSION['username'];
			$little_red_row['uid'] = $_SESSION['uid'];
			$little_red_row['createip'] = $this->get_ip();
			$little_red_row['createtime'] = date('Y-m-d H:i:s');
			$little_red_row['level_es'] = $level_es['level_des'];
			$little_red_row['balance_money'] = $little_red_row['money'] + $userinfo['money'];

			$this->private_db->trans_begin();
			//插入抢红包日志
			$bag_logs['do_time'] = date('Y-m-d H:i:s');
			$bag_logs['site_id'] = SITEID;
			$bag_logs['index_id'] = INDEX_ID;
			$bag_logs['uid'] = $_SESSION['uid'];
			$bag_logs['rid'] = $rid;
			$bag_logs['remark'] = $_SESSION['uid']."已搶得紅包";

			$this->private_db->insert("add_bag_log",$bag_logs);
			if(FALSE === $this->private_db->trans_status()){
				$this->private_db->trans_rollback();
				//写入失败 红包写回redis putback
				if(FALSE ===$this->redis->lpush($key,$little_red)){
					$key_r = "snatch:rid:".$rid.":littelid:".$little_red_row['id'];
					$this->log_error($key_r,$little_red);
					return array('Code' =>98);
				}
				return array('Code' =>94);
			}

			$this->private_db->where('id', $little_red_row['id']);
			$this->private_db->where('uid',0);
			$this->private_db->update("redenvelopes_log",$little_red_row);
			$affected_rows = $this->private_db->affected_rows();
			if(intval($affected_rows) != 1){
				$this->private_db->trans_rollback();
				//写入失败 红包写回redis putback
				if(FALSE ===$this->redis->lpush($key,$little_red)){
					$key_r = "snatch:rid:".$rid.":littelid:".$little_red_row['id'];
					$this->log_error($key_r,$little_red);
					return array('Code' =>98);
					//exit;
				}
				return array('Code' =>95);
				//exit;
			}
			$this->private_db->where('uid',$_SESSION['uid']);
			$this->private_db->where('site_id',SITEID);
			$this->private_db->where('index_id', INDEX_ID);
			$this->private_db->set('money','money+'.$little_red_row['money'],FALSE);
			$this->private_db->update('k_user');     //加钱
			$result_ = $this->private_db->affected_rows();
			if(intval($result_) != 1){
				$this->private_db->trans_rollback();
				//写入失败 红包写回redis putback
				if(FALSE ===$this->redis->lpush($key,$little_red)){
					$key_r = "snatch:rid:".$rid.":littelid:".$little_red_row['id'];
					$this->log_error($key_r,$little_red);
					return array('Code' =>98);
				}
				return array('Code' =>96);
			}

			  //发送消息
		     $dataM = array();
		     $dataM['type'] = 2;//表示出入款类型
			 $dataM['site_id'] = SITEID;
			 $dataM['uid'] = $_SESSION['uid'];
			 $dataM['level'] = 2;
			 $dataM['msg_title'] = "抢得红包";
			 $dataM['msg_time'] = date('Y-m-d H:i:s');
			 $dataM['msg_info'] = $_SESSION['username'] . ',' . "會員抢得红包優惠" . $little_red_row['money'] . "元,祝您游戏愉快，财源广进！";
		     $this->private_db->insert('k_user_msg',$dataM);
			if(FALSE === $this->private_db->trans_status()){
				$this->private_db->trans_rollback();
				//写入失败 红包写回redis putback
				if(FALSE ===$this->redis->lpush($key,$little_red)){
					$key_r = "snatch:rid:".$rid.":littelid:".$little_red_row['id'];
					$this->log_error($key_r,$little_red);
					return array('Code' =>98);
				}
				return array('Code' =>97);
			}

			$now_date = date('Y-m-d H:i:s');

			//稽核状态查询
            $is_audit = $this->get_audit($_SESSION['uid']);
            //存在即更新上一次终止时间
            if ($is_audit && $little_red_row['amount_inpoint']) {
                $this->private_db->where(array('id'=>$is_audit['id']))
                 ->update('k_user_audit',array('end_date'=>$now_date));
            }

			//写入稽核记录
			if ($little_red_row['amount_inpoint'] > 0) {
				$datae                  = array();
				$datae['username']      = $_SESSION['username'];
				$datae['site_id']       = SITEID;
				$datae['uid']           = $_SESSION['uid'];
				$datae['source_type']   = 5; //注册优惠
				$datae['begin_date']    = $now_date;
				$datae['type']          = 1;
				$datae['is_zh']         = 1; //有综合稽核
				$datae['is_ct']         = 0;//无常态
				$datae['catm_give']     = $little_red_row['money']; //红包存款金额
				$datae['type_code_all'] = $little_red_row['amount_inpoint'] * $little_red_row['money']; //综合稽核打码
				$aid = $this->private_db->insert('k_user_audit',$datae);
			}

			//TODO写入入款记录
			$this->private_db->from('k_user');
			$this->private_db->where('uid', $_SESSION['uid']);
			$this->private_db->where('site_id',SITEID);
			$this->private_db->where('index_id', INDEX_ID);
			$this->private_db->select("username,money,agent_id,index_id,level_id");
			$user_info = $this->private_db->get()->row_array();      //更新会员余额
			$user_money = $user_info['money'];
			//写入流水记录
			$dataR                 = array();
			$dataR['uid']          = $_SESSION['uid'];
			$dataR['username']     = $_SESSION['username'];
			$dataR['agent_id']     = $_SESSION['agent_id'];
			$dataR['site_id']      = SITEID;
			$dataR['index_id']      = INDEX_ID;
			$dataR['cash_balance'] = floatval($user_money); // 用户当前余额;
			$dataR['cash_date']    = date('Y-m-d H:i:s');
			$dataR['cash_type']    = 6;  //注册优惠
			$dataR['cash_only']    = 1;
			$dataR['cash_do_type'] = 1;
			$dataR['discount_num'] = floatval($little_red_row['money']); // 红包金额
			$dataR['remark'] = $_SESSION['username'] . ',' . "會員抢得红包優惠" . $little_red_row['money'] . "元";
		    $cid = $this->private_db->insert('k_user_cash_record',$dataR);
			if(FALSE === $this->private_db->trans_status()){
				$this->private_db->trans_rollback();
				//写入失败 红包写回redis putback
				if(FALSE ===$this->redis->lpush($key,$little_red)){
					$key_r = "snatch:rid:".$rid.":littelid:".$little_red_row['id'];
					$this->log_error($key_r,$little_red);
				}
			}else{
				//写入完成对了队列
				$key = "red_bag".SITEID."_".INDEX_ID."_rid_".$rid."_"."finishlist_".$little_red_row['id'];
				if(FALSE === $this->redis->set($key,json_encode($little_red_row,JSON_UNESCAPED_UNICODE))){
					$key_r = "snatchpushfinishlist:rid:".$rid.":littelid:".$little_red_row['id'];
					$this->log_error($key_r,$little_red);
				}
				$this->private_db->trans_commit();
				unset($little_red_row["id"]);
				unset($little_red_row["amount_inpoint"]);
				unset($little_red_row["createip"]);
				unset($little_red_row["createtime"]);
				unset($little_red_row["endtime"]);
				unset($little_red_row["index_id"]);
				unset($little_red_row["make_sure"]);
				unset($little_red_row["min_inpoint"]);
				unset($little_red_row["rid"]);

				unset($little_red_row["site_id"]);
				unset($little_red_row["starttime"]);
				unset($little_red_row["uuid"]);

				$data = array('Code' =>0,"red"=>$little_red_row);
			}
			//抢成功金额
		}
		return $data; //未知错误
	}

	public function get_finishlist($rid){

		$data = array('Code' =>99);
		$list = array();
		$key = "red_bag".SITEID."_".INDEX_ID."_rid_".$rid."_"."finishlist_*";
		$keys = $this->redis->keys($key);
		if(FALSE === $keys){
			return array('Code' =>1);
		}
		foreach ($keys as $value) {
			$red = $this->redis->get($value);
			$red = str_replace('--', '"', $red);
			$red_arr = json_decode($red,TRUE);
			if(is_array($red_arr)){
				$list[] = $red_arr;
			}
		}
		return array('Code' =>0,"List" =>$list );
	}

	private function get_ip(){
	    $realip = '';
	    $unknown = 'unknown';
	    if (isset($_SERVER)){
	        if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], $unknown)){
	            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
	            foreach($arr as $ip){
	                $ip = trim($ip);
	                if ($ip != 'unknown'){
	                    $realip = $ip;
	                    break;
	                }
	            }
	        }else if(isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP']) && strcasecmp($_SERVER['HTTP_CLIENT_IP'], $unknown)){
	            $realip = $_SERVER['HTTP_CLIENT_IP'];
	        }else if(isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR']) && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown)){
	            $realip = $_SERVER['REMOTE_ADDR'];
	        }else{
	            $realip = $unknown;
	        }
	    }else{
	        if(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), $unknown)){
	            $realip = getenv("HTTP_X_FORWARDED_FOR");
	        }else if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), $unknown)){
	            $realip = getenv("HTTP_CLIENT_IP");
	        }else if(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), $unknown)){
	            $realip = getenv("REMOTE_ADDR");
	        }else{
	            $realip = $unknown;
	        }
	    }
	    $realip = preg_match("/[\d\.]{7,15}/", $realip, $matches) ? $matches[0] : $unknown;
	    return $realip;
	}

	//稽核查询
	public function get_audit($uid){
        $map['site_id'] = SITEID;
        $map['type'] = 1;
        $map['uid'] = $uid;
        $map['type'] = 1;
		$this->db->from("k_user_audit");
		$this->db->where($map)->order_by("id desc");
		return $this->db->get()->row_array();
	}

	public function edit_bag($red){
		//更新大红包状态
		$red_row['status'] = "1";
		$this->private_db->where('id', $red);
		$this->private_db->where('status', 0);
		$this->private_db->update("redenvelopes",$red_row);
	}

	public function edit_bag_($red){
		//更新大红包状态
		$red_row['status'] = "2";
		$this->private_db->where('id', $red);
		$this->private_db->where('status', 1);
		$this->private_db->update("redenvelopes",$red_row);

		$key = "red_bag".SITEID."_".INDEX_ID."_littlered_".$red;
		$this->redis->delete($key);
	}


	//获取大红包状态
	public function get_big_bag($id){
		$this->private_db->from('redenvelopes');
		$this->private_db->select('status');
		$this->private_db->where('id', $id);
		return $this->private_db->get()->row_array();
	}

	//公司入款 线上入款
	public function income_line($s_time,$e_time){
		$db_model['tab'] = 'k_user_bank_in_record';
		$db_model['type'] = 1;
		$map['do_time'] = array(array('>',$s_time),array('<',$e_time));
		$map['make_sure'] = 1;
		$map['site_id'] = SITEID;
		$map['index_id'] = INDEX_ID;
		return $this->M($db_model)->field("/* parallel */ sum(deposit_num) as deposit_num,uid,username,site_id,index_id")->where($map)->group('uid')->select('u-uid');
	}
    //人工入款
	public function income_catm($s_time,$e_time){
		$db_model['tab'] = 'k_user_catm';
		$db_model['type'] = 1;
		$map['updatetime'] = array(array('>',$s_time),array('<',$e_time));
		$map['type'] = 1;
		$map['catm_type'] = 1;
		$map['site_id'] = SITEID;
		$map['index_id'] = INDEX_ID;
		return $this->M($db_model)->field("/* parallel */ sum(catm_money) as deposit_num,uid,username,site_id,index_id")->where($map)->group('uid')->select('u-uid');
	}

	public function all_income($s_time,$e_time){
		$redis = RedisConPool::getInstace();
		$s_time1 = $this->trimall($s_time);
		$e_time1 = $this->trimall($e_time);
		$redis_key = SITEID."_".INDEX_ID."_".$s_time1."_".$e_time1;
		if ($redis->exists($redis_key)) {
            $data = $redis->get($redis_key);
            $data = json_decode($data,true);
        }else{
			//redis中数据为空 从数据库读取
			$data1 = $this->income_line($s_time,$e_time);
			$data2 = $this->income_catm($s_time,$e_time);
			$data = $this->v_array($data1,$data2);
			$redis->setex($redis_key,60*60*24,json_encode($data));
        }
		return $data;
	}

	public function all_audit($date){
		$redis = RedisConPool::getInstace();
		$date1 = $this->trimall($date[0]);
		$date2 = $this->trimall($date[1]);
		$redis_key = "all_audit_".SITEID."_".INDEX_ID."_".$date1."_".$date2;

            $data = $redis->get($redis_key);
            $ARR = json_decode($data,true);
        	if(empty($ARR)){
			//redis中数据为空 从数据库读取
			$vtype = $this->get_modules();
	        $type_video = $vtype['video_module'];
	        $type_dz = $vtype['dz_module'];
	        $type_video = explode(",", $type_video);
	        $type_dz = explode(",", $type_dz);
	        if(in_array("eg",$type_dz)){
	        	$type_video[] = "eg";
	        }
	        if(in_array("pt",$type_dz)){
	        	$type_video[] = "pt";
	        }
	        if(in_array("gpi",$type_dz)){
	        	$type_video[] = "gpi";
	        }
	        $bet =  array();
	        $fc_audit = $this->get_fc_audit($date);//彩票
	        $sp_audit = $this->get_sp_audit($date);//体育
	        $spcg_audit = $this->get_spcg_audit($date);//体育串关
	        $all_audit['fc_audit'] = $fc_audit;
	        $all_audit['sp_audit'] = $sp_audit;
	        $all_audit['spcg_audit'] = $spcg_audit;
	        $video_audit = array();
	        $video_bet = array();
	        foreach ($type_video as $k => $v) {
	        	$video_arr = $this->get_video_audit($date,$v);//视讯
	        	$video_bet[$v] = $video_arr;
	        	$video_audit = $this->v_array1($video_audit,$video_bet[$v]);
	        }
	        $all_audit['video_audit'] = $video_bet;
	        $arr_audit = array();
	        $arr_audit = $this->v_array1($arr_audit, $fc_audit);
	        $arr_audit = $this->v_array1($arr_audit, $sp_audit);
	        $arr_audit = $this->v_array1($arr_audit, $spcg_audit);
	        $arr_audit = $this->v_array1($arr_audit, $video_audit);
	        $all_audit['all_audit'] = $arr_audit;
	        $ARR = $all_audit;
	        }
			$redis->setex($redis_key,60*60*24,json_encode($all_audit));

        return $ARR;
	}

	        //彩票所有打码
    public  function get_fc_audit($date=array()){
        $mapFc = array();
        $mapFc['status'] = array('in','(1,2)');
        $mapFc['site_id'] = SITEID;
        $mapFc['index_id'] = INDEX_ID;
        if (!empty($date)) {
            $mapFc['addtime'] = array(
                            array('>=',$date[0].' 00:00:00'),
                            array('<=',$date[1].' 23:59:59')
                            );
        }
        $db_model = array();
        $db_model['tab'] = 'c_bet';
        $db_model['type'] = 1;
        $fc_arr = $this->M($db_model)->field("/* parallel */ sum(money) as audit,uid,username,site_id,index_id")->where($mapFc)->group('username')->select('u-username');
        return $fc_arr;
    }

     //股东体育下注
    public  function get_sp_audit($date=array()){
        $mapSp = array();
        $mapSp['status'] = array('in','(1,2,4,5)');
        $mapSp['site_id'] = SITEID;
        $mapSp['index_id'] = INDEX_ID;
        if (!empty($date)) {
            $mapSp['update_time'] = array(
                            array('>=',$date[0].' 00:00:00'),
                            array('<=',$date[1].' 23:59:59')
                            );
        }
        $db_model = array();
        $db_model['tab'] = 'k_bet';
        $db_model['type'] = 1;
        $sp_arr1 =array();
        $sp_arr = $this->M($db_model)->field("/* parallel */ sum(bet_money) as audit,uid,username,site_id,index_id")->where($mapSp)->group('username')->select('u-username');
         return $sp_arr;
    }
    //体育串关
    public  function get_spcg_audit($date=array()){
        $mapSp = array();
        $mapSp['status'] = array('in','(1,2)');
        $mapSp['site_id'] = SITEID;
        $mapSp['index_id'] = INDEX_ID;
        if (!empty($date)) {
            $mapSp['update_time'] = array(
                            array('>=',$date[0].' 00:00:00'),
                            array('<=',$date[1].' 23:59:59')
                            );
        }
        $db_model = array();
        $db_model['tab'] = 'k_bet_cg_group';
        $db_model['type'] = 1;
        $sp_cg_arr = $this->M($db_model)->field("/* parallel */ sum(bet_money) as audit,uid,username,site_id,index_id")->where($mapSp)->group('username')->select('u-username');
        return $sp_cg_arr;
    }

        //视讯打码 type视讯类别
    public  function get_video_audit($date=array(),$type){
        $map = array();
        $map['site_id'] = SITEID;
        $map['index_id'] = INDEX_ID;
        $ARR = array();
        $db_model = array();
        $db_model['type'] = 3;
    	switch ($type) {
    		case 'ag':
    			$db_model['tab'] = 'ag_bet_record';
    			$map['bet_time'] = array(
                                array('>=',$date[0]),
                                array('<=',$date[1])
                                );
                $data = $this->M($db_model)->field("/* parallel */ sum(valid_betamount) as audit,pkusername,site_id,index_id")->where($map)->group('pkusername')->select("u-pkusername");
    			break;
    		case 'bbin':
    			$db_model['tab'] = 'bbin_bet_record';
    			$map['wagers_date'] = array(
                                array('>=',$date[0]),
                                array('<=',$date[1])
                                );
                $data = $this->M($db_model)->field("/* parallel */ sum(commissionable) as audit,pkusername,site_id,index_id")->where($map)->group('pkusername')->select("u-pkusername");
    			break;
    		case 'ct':
    			$map['transaction_date_time'] = array(
                                array('>=',$date[0]),
                                array('<=',$date[1])
                                );
            	$db_model['tab'] = 'ct_bet_record';
                $data = $this->M($db_model)->field("/* parallel */ sum(availablebet) as audit,pkusername,site_id,index_id")->where($map)->group('pkusername')->select("u-pkusername");
    			break;
    		case 'og':
    			$map['add_time'] = array(
                                array('>=',$date[0]),
                                array('<=',$date[1])
                                );
            	$db_model['tab'] = 'og_bet_record';
                $data = $this->M($db_model)->field("/* parallel */ sum(valid_amount) as audit,pkusername,site_id,index_id")->where($map)->group('pkusername')->select("u-pkusername");
    			break;
    		case 'mg':
    			 $map['date'] = array(
                                array('>=',$date[0]),
                                array('<=',$date[1])
                                );
            	$db_model['tab'] = 'mg_bet_record';
                $data = $this->M($db_model)->field("/* parallel */ sum(income) as audit,pkusername,site_id,index_id")->where($map)->group('pkusername')->select("u-pkusername");
    			break;
    		case 'pt':
    			 $map['GameDate'] = array(
                                array('>=',$date[0]),
                                array('<=',$date[1])
                                );
            	$db_model['tab'] = 'pt_bet_record';
                $data = $this->M($db_model)->field("/* parallel */ sum(Bet) as audit,pkusername,site_id,index_id")->where($map)->group('pkusername')->select("u-pkusername");
    			break;
    		case 'im':
    			 $map['bet_time'] = array(
                                array('>=',$date[0]),
                                array('<=',$date[1])
                                );
    			$map["settled"] = 1;
            	$map['result'] =  array('<>','0');
            	$map['bt_status'] = 0;
            	$db_model['tab'] = 'im_bet_record';
                $data = $this->M($db_model)->field("/* parallel */ sum(bet_amt) as audit,pkusername,site_id,index_id")->where($map)->group('pkusername')->select("u-pkusername");
    			break;
    		default:
    			 $map['betstart_time'] = array(
                                array('>=',$date[0]),
                                array('<=',$date[1])
                                );
            	$db_model['tab'] = $type.'_bet_record';
            	$data = $this->M($db_model)->field("/* parallel */ sum(valid_betamount) as audit,pkusername,site_id,index_id")->where($map)->group('pkusername')->select("u-pkusername");
    			break;
    	}
    		return $data;
    }

	 //获取模块
   public function get_modules(){
       $db_model = array();
       $db_model['tab'] = 'web_config';
       $db_model['type'] = 1;
       $map = array();
       $map['site_id'] = SITEID;
       $map['index_id'] = INDEX_ID;
       return $this->M($db_model)->field("module,dz_module,video_module")->where($map)->find();
   }


	//数组合并
    public function v_array($arr = array(),$bet = array()){
        if (!empty($arr) && !empty($bet)) {

            $keys_uid = array_keys($arr);
            $keys_uid_cg = array_keys($bet);
            $intersect_arr = array_intersect($keys_uid, $keys_uid_cg);
            if (!empty($intersect_arr)) {
                foreach ($intersect_arr as $key => $val) {
                    $arr[$val]['deposit_num'] = $arr[$val]['deposit_num'] + $bet[$val]['deposit_num'];
                    unset($bet[$val]);
                }
            }
            $arr = array_merge_recursive($arr,$bet);
        }elseif (empty($arr) && !empty($bet)) {
            $arr = $bet;
        }
        return $arr;
    }

    //数组合并
    public function v_array1($arr = array(),$bet = array()){
        if (!empty($arr) && !empty($bet)) {

            $keys_uid = array_keys($arr);
            $keys_uid_cg = array_keys($bet);
            $intersect_arr = array_intersect($keys_uid, $keys_uid_cg);
            if (!empty($intersect_arr)) {
                foreach ($intersect_arr as $key => $val) {
                    $arr[$val]['audit'] = $arr[$val]['audit'] + $bet[$val]['audit'];
                    unset($bet[$val]);
                }
            }
            $arr = array_merge_recursive($arr,$bet);
        }elseif (empty($arr) && !empty($bet)) {
            $arr = $bet;
        }
        return $arr;
    }

    //删除空格
    function trimall($str){
	    $qian=array(" ","　","\t","\n","\r");
	    $hou=array("","","","","");
	    return str_replace($qian,$hou,$str);
    }

     public function get_rid_data($id){
        $db_model['tab'] = 'redenvelopes';
        $db_model['type'] = 1;
        $map['id'] = $id;
        return $this->M($db_model)->where($map)->find();
    }

}

?>