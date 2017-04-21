<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Member_news extends MY_Controller {
	public function __construct() {
		parent::__construct();
		$this->load->model('member/Member_news_model');
		$this->Member_news_model->login_check($_SESSION['uid']);
	}

	//信息公告，最新消息
	public function latest_news(){
		$time=time()-60*60*24*3;
		$time=date('Y-m-d H:i:s',$time);
		$map['where'] = "is_delete ='0' and site_id='".SITEID."' and index_id = '".INDEX_ID."' and show_type='2' and add_time >'".$time."'";
		$map['order'] = "add_time desc";
		$news = $this->Member_news_model->get_latest_news($map);

		$this->add('news', $news);
		$this->add('new_type', 'now');
		$this->display('member/latest_news.html');
	}

	//信息公告，最新消息——>歷史消息
	public function histiry_news(){
		$map['where'] = "is_delete='0' and site_id='".SITEID."' and show_type='2' and index_id = '".INDEX_ID."'";
		$map['order'] = "add_time desc";
		$count = $this->Member_news_model->get_histiry_news_count($map);
		if ($count > 0) {
			//分頁
			$per_page =10;
			$perNumber = ($perNumber == 0) ? $per_page : $perNumber; //每頁顯示的記錄數

			$totalPage = ceil($count / $perNumber); //計算出總頁數
			$page =  $this->input->get("per_page");
			$page = ($page == 0) ? 1 :$page;
			$offset = ($page - 1) * $perNumber; //分頁開始,根據此方法計算出開始的記錄
			$hnews = $this->Member_news_model->get_histiry_news($perNumber,$offset ,$map);
		}

		$this->load->library('pagination');
		$config['base_url'] = '/index.php/member/news/histiry_news';	//分頁路徑
		$config['total_rows'] = $count;
		$config['per_page'] = $per_page;
		$this->pagination->initialize($config);
		$this->pagination->use_page_numbers = true;
		$this->pagination->page_query_string = true;

		$this->add("page_html", $this->pagination->create_links());
		$this->add("page", $page);
		$this->add('new_type', 'his');
		$this->add("news", $hnews);
		$this->display('member/latest_news.html');

	}

	//信息公告，個人信息
	public function member_informations(){
		$row = $this->Member_news_model->get_user_level_id($_SESSION['uid']);
		if(empty($row[0]['level_id'])){
			$data = array();
			$this->add("dqpage", 1);
			$this->add("totalPage",1);
			$this->add("data", $data);
			$this->display('member/member_informations.html');
			exit;
		}
		$start_date = date('Y-m-d H:m:s', strtotime('-7days'));
		if($row[0]['reg_date'] >= date('Y-m-d', strtotime('-7days'))){
			$start_date = $row[0]['reg_date'];
		}

		$map['where']="(uid='".$_SESSION["uid"]."' or (uid = '' and (level_id = '".$row[0]['level_id']."' or level_id = '-1')))   and is_delete = '0' and site_id = '".SITEID."' and index_id = '".INDEX_ID."' and msg_time > '".$start_date."'";
		$map['order']='islook asc,msg_id desc';
		$id = $this->Member_news_model->get_sms_id($map);
		if($id){
			foreach ($id as $v) {
				$bid .=	"'".$v['msg_id']."',";
			}
			$id		=	rtrim($bid,',');
			$count = $this->Member_news_model->get_sms_count($id);
			//分頁
			$perNumber=isset($_GET['page_num'])?$_GET['page_num']:10; //每頁顯示的記錄數
			$totalPage=ceil($count/$perNumber); //計算出總頁數
			$page=isset($_GET['page'])?$_GET['page']:1;
			if($totalPage<$page){
				$page = 1;
			}
			$startCount=($page-1)*$perNumber; //分頁開始,根據此方法計算出開始的記錄
			$map['limit']=$startCount;
			$map['limit2'] =$perNumber;

			$getpage = $this->input->get('page');
			if($getpage){
				$map['limit']=($getpage-1)*10;
				$this->add("dqpage", $getpage);
			}else{
				$this->add("dqpage", 1);
			}
			$data = $this->Member_news_model->get_sms($id,$map);
			//獲取該會員已讀記錄
			$look_log = $this->Member_news_model->get_look_log(array('uid'=>$_SESSION['uid']));
			foreach ($data as $key => $value) {
				unset($data[$key]['islook']);
				foreach ($look_log as $k => $v) {
					if($value['msg_id'] == $v['msg_id']){
						$data[$key]['islook'] = 1;
					}
				}
			}

			//p($data);die;
			$this->add("totalPage",$totalPage);
			$this->add("data", $data);

		}
		$this->display('member/member_informations.html');
	}

	//信息公告，個人信息，信息狀態改變
	public function sms_change(){
		$type = $this->input->post('type');
		$uid = $this->input->post('uid');
		if($type =='look'){
			$map['msg_id']=$uid;    //消息ID
			$map['uid'] = $_SESSION['uid'];   //會員ID
			$result = $this->Member_news_model->get_look_log($map);
			if($result){
				echo 1;exit;
			}else{
				$this->Member_news_model->add_look_log($map);
				echo 1;exit;
			}
		}
	}

	//信息公告，遊戲公告
	public function games_news(){
		$curr = $this->input->get('curr');
		$curr = !empty($curr) ? $curr : 'sp';
		$data = $this->Member_news_model->get_sports_news($curr);
		$ntitle = array('sp'=>'體育公告','tv'=>'視訊公告','fc'=>'彩票公告');
		$this->add("curr", $curr);
		$this->add("news_title", $ntitle[$curr]);
		$this->add("data", $data);
		$this->display('member/member_news.html');
	}

	//信息公告，個人信息， 信息刪除（邏輯刪除）
	public function sms_del(){
		//is_delete 1 刪除  0 未刪除
		$map['msg_id'] = $this->input->post("msg_id");
		$query = $this->Member_news_model->del_sms($map);
		if($query){
			echo  1;exit;
		}else{
			echo  2;exit;
		}
	}

}