<?php
defined('BASEPATH') OR exit('No direct script access allowed');

//消息公告
class News extends MY_Controller {
	public function __construct() {
		parent::__construct();
		$this->load->model('member/new/Memnews_model');
		$this->Memnews_model->login_check($_SESSION['uid']);
	}
	#個人消息--開始#
	//個人信息列表
	public function news_member_index(){
		$this->add("data", $data);

		$this->display('web_public/member/news/personal.html');
	}

	//個人消息已讀處理
	public function news_member_read_do(){
		$map = array();
		$map['msg_id'] = intval($this->input->post('msg_id'));
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
	public function news_member_del(){
		$map = array();
		$map['msg_id'] = intval($this->input->post('msg_id'));
		$map['uid'] = $_SESSION["uid"];
		$del = $this->Memnews_model->del_sms($map);
		if($del){
			echo 1;
		}else{
			echo 0;
		}
	}
	#個人消息--結束#

	#遊戲公告--開始#
	//遊戲公告 體育彩票視訊
	public function news_game_index(){
		$type = $this->input->get('type') ? intval($this->input->get('type')) : 4; //默認體育公告
		$page = $this->input->get('page') ? intval($this->input->get('page')) : 1; //當前頁數

		$type_title = array(3=>'彩票',4=>'體育',5=>'視訊');

		$news_count = $this->Gamenews_model->gamenews_list($type); //總數

		$page_size = 50; //每頁條數
		$page_count = ceil($news_count / $page_size); //總頁數
		if($page <= 0 || $page > $page_count) $page = 1;
		$limit = ($page-1) * $page_size . ',' . $page_size;

		$news = $this->Gamenews_model->gamenews_list($type,$limit); //新聞列表

		$this->add('type', $type);
		$this->add('type_title', $type_title);
		$this->add('page', $page);
		$this->add('page_count', $page_count);
		$this->add('news', $news);
		$this->display('web_public/member/news/announcement.html');
	}
	#遊戲公告--結束#

	#最新消息--開始#
	//最新消息 歷史消息
	public function news_index(){
		$type = intval($this->input->get('type'));//1表示最新消息 0表示歷史消息

		$this->display('web_public/member/news/news.html');
	}
	#最新消息--結束#	
}