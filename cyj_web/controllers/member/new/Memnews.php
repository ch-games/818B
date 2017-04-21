<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Memnews extends MY_Controller {
	public function __construct() {
		parent::__construct();
		$this->load->model('member/new/Memnews_model');
		$this->Memnews_model->login_check($_SESSION['uid']);
	}

	//個人信息
	public function memnews_personal_index(){
		$row = $this->Memnews_model->get_user_level_id($_SESSION['uid']);
		if(empty($row[0]['level_id'])){
			$this->add("page", 1);
			$this->add("totalPage",1);
			$this->add("data", '');
			$this->display('web_public/member/news/personal.html');
			exit;
		}
		//where條件
		$map['where'] = "";
		$map['where'] .= "(uid='".$_SESSION["uid"]."' or (uid = '' and (level_id = '".$row[0]['level_id']."' or level_id = '-1')))";
		$map['where'] .= "and is_delete = '0'";
		//站點判斷條件
		$map['where'] .= "and site_id = '".SITEID."'";
		$map['where'] .= "and index_id = '".INDEX_ID."'";
		//時間條件
		$start_date = date('Y-m-d H:m:s', strtotime('-7days'));
		if($row[0]['reg_date'] >= date('Y-m-d', strtotime('-7days'))){
			$start_date = $row[0]['reg_date'];
		}
		$map['where'] .= "and msg_time > '".$start_date."'";
		//排序條件
		$map['order']='islook asc,msg_id desc';
		$id = $this->Memnews_model->get_sms_id($map);
		if($id){
			foreach ($id as $v) {
				$bid .=	"'".$v['msg_id']."',";
			}
			$id		=	rtrim($bid,',');
		}
		//獲取信息總數
		$count = $this->Memnews_model->get_sms_count($id);
		//分頁
		$perNumber = 10; //每頁顯示的記錄數
		$totalPage = ceil($count / $perNumber); //計算出總頁數
		$page = $this->input->get('page') ? $this->input->get('page') : '1';
		if($totalPage < $page){
			$page = 1;
		}
		$startCount = ($page - 1) * $perNumber; //分頁開始,根據此方法計算出開始的記錄
		$map['limit'] = $startCount;
		$map['limit2'] = $perNumber;
		$data = $this->Memnews_model->get_sms($id,$map);
		$this->add("totalPage", $totalPage);	//輸出總頁數
		$this->add("page",$page);
		//獲取該會員已讀記錄
		$look_log = $this->Memnews_model->get_look_log(array('uid'=>$_SESSION['uid']));
		foreach ($data as $key => $value) {
			unset($data[$key]['islook']);
			foreach ($look_log as $k => $v) {
				if($value['msg_id'] == $v['msg_id']){
					$data[$key]['islook'] = 1;
				}
			}
		}
		$this->add("data", $data);

		$this->display('web_public/member/news/personal.html');
	}

	//讀取消息同步改變狀態
	public function memnews_read_do(){
		$map = array();
		$map['msg_id'] = $this->input->post('msg_id');
		$map['uid'] = $_SESSION["uid"];
		$islook = $this->Memnews_model->get_look_log($map);
		if(!$islook){
			$this->Memnews_model->add_look_log($map);
			echo 1;
		}else{
			echo 0;
		}
	}

	//刪除會員消息
	public function memnews_del_do(){
		$map = array();
		$map['msg_id'] = $this->input->post('msg_id');
		$map['uid'] = $_SESSION["uid"];
		$del = $this->Memnews_model->del_sms($map);
		if($del){
			echo 1;
		}else{
			echo 0;
		}
	}

}