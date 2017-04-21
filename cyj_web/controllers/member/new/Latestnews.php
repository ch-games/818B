<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Latestnews extends MY_Controller {
	public function __construct() {
		parent::__construct();
		$this->load->model('member/new/Memnews_model');
		$this->Memnews_model->login_check($_SESSION['uid']);
	}

	//最新消息
	public function latestnews_new_index(){
		$time=time()-60*60*24*7;
		$time=date('Y-m-d H:i:s',$time);
		$map['where'] = "is_delete ='0' and site_id='".SITEID."' and index_id = '".INDEX_ID."' and show_type='2' and add_time >'".$time."'";
		$map['order'] = "add_time desc";
		$news = $this->Memnews_model->get_latest_news($map);
		$this->add('news', $news);
		$this->add('new_type', 'now');
		$this->display('web_public/member/news/news.html');
	}
	//歷史消息
	public function latestnews_history_index(){
		$map['where'] = "is_delete='0' and site_id='".SITEID."' and show_type='2' and index_id = '".INDEX_ID."'";
		$map['order'] = "add_time desc";
		//獲取信息總數
		$count = $this->Memnews_model->get_histiry_news_count($map);
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
		$hnews = $this->Memnews_model->get_histiry_news($map);
		$this->add("totalPage", $totalPage);	//輸出總頁數
		$this->add("page",$page);
		$this->add("news", $hnews);
		$this->display('web_public/member/news/history.html');
	}



}