<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Gamenews extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('member/new/Gamenews_model');
    }

    //遊戲公告
    public function gamenews_index(){
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
}