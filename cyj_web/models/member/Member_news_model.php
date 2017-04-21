<?php if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Member_news_model extends MY_Model {

	function __construct() {
		parent::__construct();
		$this->init_db();
	}

	//獲取最新的公告信息
	function get_latest_news($map){
		$db_model = array();
		$db_model['tab'] = 'k_message';
		$db_model['type'] = '1';
		$sql = $this->M($db_model);
		return $sql->field('add_time,chn_simplified')
			->where($map['where'])
			->order($map['order'])
			->select();
	}

	//獲取歷史公告信息
	function get_histiry_news($count, $offset,$map=array()){
		$db_model = array();
		$limit = "$offset,$count";
		$db_model['tab'] = 'k_message';
		$db_model['type'] = '1';
		$sql = $this->M($db_model);
		return $sql->field('add_time,chn_simplified')
			->where($map['where'])
			->limit($limit)
			->order($map['order'])
			->select();
	}

	//獲取歷史公告總數
	function get_histiry_news_count($map){
		$db_model = array();
		$db_model['tab'] = 'k_message';
		$db_model['type'] = '1';
		$sql = $this->M($db_model);
		return $sql->field('id')
			->where($map['where'])
			->count();
	}

	//獲取用戶等級
	function get_user_level_id($uid){
		$db_model = array();
		$db_model['tab'] = 'k_user';
		$db_model['type'] = '1';
		$sql = $this->M($db_model);
		return $sql->field('level_id')
			->where("uid = '$uid'")
			->select();
	}

	//獲取信息公告，個人信息 id
	function get_sms_id($map){
		$db_model = array();
		$db_model['tab'] = 'k_user_msg';
		$db_model['type'] = '1';
		$sql = $this->M($db_model);
		return $sql->field('msg_id')
			->where($map['where'])
			->order($map['order'])
			->select();

	}

	//獲取信息公告，個人信息
	function get_sms($id,$map){
		$db_model = array();
		$pagNum = $map['limit'];
		$offset = $map['limit2'];
		$limit = "$pagNum,$offset";
		$maps['msg_id'] = array('in',"(".$id.")");
		$db_model['tab'] = 'k_user_msg';
		$db_model['type'] = '1';
		$sql = $this->M($db_model);
		return $sql->field("islook,msg_title,msg_time,msg_info,msg_id,is_delete,uid")
			->where($maps)
			->limit($limit)
			->order($map['order'])
			->select();//echo $sql->sql;die;
	}

	//獲取信息公告，個人信息總數
	function get_sms_count($id){
		$db_model = array();
		$db_model['tab'] = 'k_message';
		$db_model['type'] = '1';
		$sql = $this->M($db_model);
		return $sql->field('*')
			->where($map['msg_id'] = array('in',"(".$id.")"))
			->count();
	}

	//獲取信息公告，個人信息，狀態改變
	/*function up_sms_change($map){
		$this->private_db->where("msg_id", $map['msg_id']);
		$this->private_db->set('islook',1);
		$this->private_db->update('k_user_msg');
		return $this->private_db->affected_rows();
	}*/

	//獲取信息公告，遊戲公告
	function get_sports_news($type){
		$ndate = date('Y-m-d H:i:s',(time()-30*24*60*60));
		$ntype = array('sp'=>4,'tv'=>5,'fc'=>3);

		$map = " sid = '0' and notice_state = '1' and (notice_cate='".$ntype[$type]."' or notice_cate='2') and notice_date > '".$ndate."' ";
		$db_model = array();
		$db_model['tab'] = 'site_notice';
		$db_model['type'] = '1';
		$sql = $this->M($db_model);
		return $sql->field("*")
			->where($map)
			->limit('0,8')
			->order('notice_date DESC')
			->select();
	}

	//刪除信息
	function del_sms($map){
		$db_model = array();
		$db_model['tab'] = 'k_user_msg';
		$db_model['type'] = '1';
		$sql = $this->M($db_model);
		return $sql->where($map)->update(array('is_delete'=>'1'));
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