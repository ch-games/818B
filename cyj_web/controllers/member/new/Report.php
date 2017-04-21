<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Report extends MY_Controller {
	public function __construct() {

		parent::__construct();
		$this->load->model('member/new/Report_model2');
		$this->load->model('member/new/Report_model');
		$this->load->model('Common_model');
		$this->Report_model->login_check($_SESSION['uid']);
	}

	//報表統計
	public function report_statist_index(){
		if($_SESSION['shiwan'] == 1){
			$video_config = array();
		}else{
			$video_config = $this->Common_model->get_video_dz_mem();
		}
		$this->add('video_config',$video_config);

		$this->display('web_public/member/report/report.html');
	}

	public function report_list_ajax(){
		$this->load->library('Games');
		$loginname = $_SESSION['username'];
		$uid = $_SESSION['uid'];
		$action = $this->input->post('action');
		if($action == "yesterday"){
			$starttime = date("Y-m-d",strtotime("-1 day"))." 00:00:00";
			$endtime   = date("Y-m-d",strtotime("-1 day"))." 23:59:59";
		}elseif($action == "theweek"){
			$starttime=date("Y-m-d",strtotime("last Monday"))." 00:00:00";
			$endtime=date("Y-m-d")." 23:59:59";
		}elseif($action == 'lastweek'){
			$starttime=date("Y-m-d",strtotime("last Monday")-604800)." 00:00:00";
			$endtime=date("Y-m-d",strtotime("last Monday")-86400)." 23:59:59";
			if(date("N",time()) == 1){
				$starttime=date("Y-m-d",strtotime("last Monday"))." 00:00:00";
				$endtime=date("Y-m-d",strtotime("last Monday")+518400)." 23:59:59";
			}
		}else{
			$starttime = date("Y-m-d")." 00:00:00";
			$endtime   = date("Y-m-d")." 23:59:59";
		}

		$redis = RedisConPool::getInstace();
		$redis_key = 'mreport_'.SITEID.'_'.md5($uid.$starttime.$endtime);
		$data = array();
		$vdata = $redis->get($redis_key);
		if(empty($vdata)){
			$map = array();
			$map['site_id'] = SITEID;
			$map['uid'] = $uid;
			$map['addtime'] = array(array('>',$starttime),array('<',$endtime));
			//彩票數據
			$cp = $this->Report_model->get_count_list($map,1,0);
			$cpc = $this->Report_model->get_count_sum('c_bet',$map);
			//有效彩票數據
			$valid_map = $map;
			$status = "1,2";
			$valid_map['status'] = array('in','('.$status.')');
			$valid_cp = $this->Report_model->get_count_list($valid_map,1,1);
			//體育數據
			$sp['site_id'] = SITEID;
			$sp['uid'] = $uid;
			$sp['bet_time'] = array(array('>',$starttime),array('<',$endtime));
			$ty = $this->Report_model->get_count_list($sp,2,0);
			$tyc = $this->Report_model->get_count_sum('k_bet',$sp);
			//有效體育數據
			$valid_sp = $sp;
			$valid_sp['is_jiesuan'] = 1;
			$status = "1,2,4,5";
			$valid_sp['status'] = array('in','('.$status.')');
			$valid_ty = $this->Report_model->get_count_list($valid_sp,2,1);
			//體育串關
			$cg_ty = $this->Report_model->get_count_list($sp,3,0);
			$cg_tyc = $this->Report_model->get_count_sum('k_bet_cg_group',$sp);
			//有效體育串關數據
			$cg_valid_ty = $this->Report_model->get_count_list($valid_sp,3,1);
			//體育、串關總和
			$tyc += $cg_tyc;
			$ty['bet_money']+=$cg_ty['bet_money'];
			$valid_ty['bet_money']+=$cg_valid_ty['bet_money'];
			$valid_ty['win']+=$cg_valid_ty['win'];

			$cpdata = array();
			$cpdata['name'] = '彩票';
			$cpdata['times'] = $cpc;
			$cpdata['count'] = 0+$cp['money'];
			$cpdata['valid_money'] = 0+$valid_cp['money'];
			$cpdata['valid_win'] = $valid_cp['win'] - $valid_cp['money'];

			$tydata = array();
			$tydata['name'] = '體育';
			$tydata['times'] = $tyc;
			$tydata['count'] = $ty['bet_money'];
			$tydata['valid_money'] = $valid_ty['bet_money'];
			$tydata['valid_win'] = $valid_ty['win'];


			$copyright = $this->Common_model->get_copyright();
			$video_config = explode(',',$copyright['video_module']);
			$data[] = $cpdata;
			$data[] = $tydata;
			if($_SESSION['shiwan'] == 0){
				$games = new Games();
				$type_str = '';
				foreach ($video_config as $key => $value) {
					if($value == 'mg'){
						$type_str .= $value.'|'.'mgdz'.'|';
					}else{
						$type_str .= $value.'|';
					}
				}

				$video_data = $games->GetUserAllAvailableAmount($type_str, $loginname, $starttime, $endtime);
				$video_data = json_decode($video_data);
				$video_data = $video_data->data->data;

				foreach ($video_config as $key => $value) {
					if($value == 'mg'){
						$video_config[] = 'mgdz';
					}
				}
				foreach ($video_config as $k => $v) {
					if($v == 'mgdz'){
						$info[$v]['name'] = 'MG電子';
					}elseif($v == 'pt'){
						$info[$v]['name'] = 'PT電子';
					}elseif($v == 'eg'){
						$info[$v]['name'] = 'EG電子';
					}elseif($v == 'im'){
						$info[$v]['name'] = 'IM體育';
					}else{
						$info[$v]['name'] = strtoupper($v).'視訊';
					}
					$abc = $video_data->$v;
					$info[$v]['times'] = !empty($abc[0]->BetBS) ? $abc[0]->BetBS : 0;
					$info[$v]['count'] = !empty($abc[0]->BetAll) ? $abc[0]->BetAll : 0;
					$info[$v]['valid_money'] = !empty($abc[0]->BetYC) ? $abc[0]->BetYC : 0;
					$info[$v]['valid_win'] = !empty($abc[0]->BetPC) ? $abc[0]->BetPC : 0;
					$data[] = $info[$v];
				}
			}
			$redis->set($redis_key,30,json_encode($data));
		}else{
			$data = json_decode($vdata);
		}
		//p($data);die;
		echo json_encode($data);exit;
	}

	public function report_list_ajax2(){
		$this->load->model('Common_model');
		$video_config = $this->Common_model->get_video_dz_mem();
		$site_id = SITEID;
		$username = $_SESSION['username'];
		$action = $this->input->post('action');
		$data = $Sdata = $Edata = array();
		$data = $this->Report_model2->report_data($action,$username,$site_id);
		foreach ($video_config as $key => $value) {
			foreach ($data as $k => $v) {
				if($key == $k){
					$video_config[$key]['name'] = $value['name'];
					$video_config[$key]['times'] = $v['num'];
					$video_config[$key]['count'] = $v['bet_all'];
					$video_config[$key]['valid_money'] = $v['bet_yx'];
					$video_config[$key]['valid_win'] = $v['win'];
				}
			}
		}
		$i = 0;
		foreach ($video_config as $key => $value) {
			$Edata[$i]['name'] = $value['name'];
			$Edata[$i]['times'] = $value['times']+0;
			$Edata[$i]['count'] = $value['count']+0;
			$Edata[$i]['valid_money'] = $value['valid_money']+0;
			$Edata[$i]['valid_win'] = $value['valid_win']+0;
			$i++;
		}
		//p($Edata);die;
		echo json_encode($Edata);exit;
	}


}