<?php if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class News_model extends MY_Model {

	function __construct() {
		parent::__construct();
		$this->init_db();
	}

	//獲取最新的公告信息
	function get_latest_news($map){
		$this->private_db->from('k_message');
		$this->private_db->select('add_time,chn_simplified');
		$this->private_db->where($map['where']);
		$this->private_db->order_by($map['order']);
		$query = $this->private_db->get();
		return $query->result_array();
	}

	//獲取歷史公告信息
	function get_histiry_news($count, $offset,$map=array()){
		$this->private_db->select('add_time,chn_simplified');
		$this->private_db->where($map['where']);
		$this->private_db->order_by($map['order']);
		$query = $this->private_db->get('k_message', $count, $offset);
		return $query->result_array();
	}

	//獲取歷史公告總數
	function get_histiry_news_count($map){
		$this->private_db->from('k_message');
		$this->private_db->select('id');
		$this->private_db->where($map['where']);
		$query = $this->private_db->count_all_results();
		return $query;
	}

	//獲取用戶等級
	function get_user_level_id($uid){
		$this->private_db->from('k_user');
		$this->private_db->select('level_id');
		$this->private_db->where("uid = '$uid'");
		$query = $this->private_db->get();
		return $query->result_array();
	}

	//獲取信息公告，個人信息 id
	function get_sms_id($map){
		$this->private_db->from('k_user_msg');
		$this->private_db->select('msg_id');
		$this->private_db->where($map['where']);
		$this->private_db->order_by($map['order']);
		$query = $this->private_db->get();
		return $query->result_array();
	}

	//獲取信息公告，個人信息
	function get_sms($id,$map){
		$this->private_db->from('k_user_msg');
		$this->private_db->select("islook,msg_title,msg_time,msg_info,msg_id,is_delete,uid");
		$this->private_db->where("msg_id in($id)");
		$this->private_db->limit($map['limit2'],$map['limit']);
		$this->private_db->order_by($map['order']);
		$query = $this->private_db->get();
		return $query->result_array();
	}

	//獲取信息公告，個人信息總數
	function get_sms_count($id){
		$this->private_db->from('k_user_msg');
		$this->private_db->where("msg_id in($id)");
		$query = $this->private_db->count_all_results();
		return $query;
	}

	//獲取信息公告，個人信息，狀態改變
	function up_sms_change($map){
		$this->private_db->where("msg_id", $map['msg_id']);
		$this->private_db->set('islook',1);
		$this->private_db->update('k_user_msg');
		return $this->private_db->affected_rows();
	}

	//獲取信息公告，遊戲公告
	function get_sports_news($type){
		$ndate = date('Y-m-d H:i:s',(time()-30*24*60*60));
		$ntype = array('sp'=>4,'tv'=>5,'fc'=>3);

		$map = " sid = '0' and notice_state = '1' and (notice_cate='".$ntype[$type]."' or notice_cate='2') and notice_date > '".$ndate."' ";
		$this->private_db->from('site_notice');
		$this->private_db->where($map);
		$this->private_db->limit(8,0);
		$this->private_db->order_by('notice_date DESC');
		return $this->private_db->get()->result_array();
	}

	//刪除信息
	function del_sms($map){
		$this->private_db->where("msg_id", $map['msg_id']);
		$this->private_db->set('is_delete',1);
		$this->private_db->update('k_user_msg');
		return $this->private_db->affected_rows();
	}

	//獲取已讀信息記錄
	public function get_look_log($map){
		$db_model = array();
		$db_model['tab'] = 'msg_look_log';
		$db_model['type'] = '1';
		return $this->M($db_model)->field('*')->where($map)->select();
	}
	//添加已讀信息記錄
	public function add_look_log($map){
		$db_model = array();
		$db_model['tab'] = 'msg_look_log';
		$db_model['type'] = '1';
		return $this->M($db_model)->add($map);
	}

}