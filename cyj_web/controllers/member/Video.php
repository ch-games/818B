<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Video extends MY_Controller {
	public function __construct() {
		parent::__construct();
		$this->load->library('Games');
		$this->load->model('Common_model');
		$this->Common_model->login_check($_SESSION['uid']);
		$this->load->model('member/Video_model');
	}

	public function getallbalance(){
		if($_SESSION['shiwan'] == 1){
			$info = array();
			$info['error'] = '請申請正式賬號！祝您遊戲愉快';
			echo json_encode($info);exit;
		}
		$action = $this->input->get('action');
		$games = new Games();
		$loginname = $_SESSION['username'];
		$video_dz_moudel = $this->Common_model->get_video_dz_config();
		$types = implode("|",$video_dz_moudel);
		$data = $games->GetAllBalance($loginname,$types);
		$list = $video_dz_moudel;
		if(in_array('mg', $list)){
			$list[] = 'mg_game';
		}elseif(in_array('ag', $list)){
			$list[] = 'ag_game';
		}elseif(in_array('bbin', $list)){
			$list[] = 'bbin_game';
		}elseif(in_array('gd', $list)){
			$list[] = 'gd_game';
		}elseif(in_array('gpi', $list)){
			$list[] = 'gpi_game';
		}
		if ($action == "save") {
			$result = json_decode($data);
			$data = json_decode($data,true);
			foreach($list as $val){
				$str = $val.'info';
				if($val == 'pt'){
					$strval = 'pt_game';
				}else{
					$strval = $val;
				}
				if($this->GetSiteStatus($this->SiteStatus,2,$strval,1)){
					$data['data'][$str] = 9999;
				}else{
					$data['data'][$str] = 1111;
				}
			}

			$arr = array('mg','ag','bbin','gd','gpi','dg99');
			foreach ($arr as $value) {
				if($data['data'][$value.'info'] && $data['data'][$value.'_gameinfo']){
					if($data['data'][$value.'info'] === $data['data'][$value.'_gameinfo']){
						$temp = $data['data'][$value.'info'];
						$data['data'][$value.'info'] = $temp;
					}else{
						$data['data'][$value.'info'] = 1111;
					}
					unset($data['data'][$value.'_gameinfo']);
				}
			}
			//p($data);die;
			if ($result->data->Code == 10017) {
				$data_u = array();
				foreach ($list as $key => $value) {
					$strstatus = $value.'status';
					$strbalance = $value.'balance';
					if (!empty($result->data->$strstatus)) {
						$data_u[$value.'_money'] = floatval($result->data->$strbalance);
					}
				}
				if(!empty($data_u)){
					$this->db->from('k_user');
					$this->db->where('site_id',SITEID);
					$this->db->where('uid',$_SESSION['uid']);
					$this->db->set($data_u);
					$this->db->update();
				}
			}

			echo json_encode($data);
		} else {
			echo json_encode($data);
		}
	}


}