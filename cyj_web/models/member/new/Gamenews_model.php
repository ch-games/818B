<?php 
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Gamenews_model extends MY_Model {
    function __construct() {
        parent::__construct();
        $this->load->model('Common_model');
        $this->init_db();
    }

    public function gamenews_list($type,$limit){
    	$db_model = array();
        $db_model['type'] = 1;
        $db_model['tab'] = 'site_notice';

        $map['sid'] = 0;
        $map['notice_state'] = 1;
        $map['notice_cate'] = $type;
        $map['notice_date'] = array('>',date('Y-m-d H:i:s',(time()-30*24*60*60))); //最近30天

        if($limit){
        	return $this->M($db_model)->where($map)->order('notice_date DESC')->limit($limit)->select();
        }else{
        	return $this->M($db_model)->where($map)->field('id')->count();
        }
    }
}