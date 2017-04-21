<?php if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Member_record_model extends MY_Model {

	function __construct() {
		parent::__construct();
		$this->init_db();
	}

	//獲取體育投註記錄
	function get_record_b($sql,$map){
		$nsql = 'select * from '.$sql.' where '.$map['where'].$map['order'].' limit '.$map['limit'].','.$map['limit2'];
		//echo $nsql;exit;
		$query = $this->private_db->query($nsql);
		return $query->result_array();
	}

	//獲取體育投註記錄總數
	function get_record_b_count($sql,$map){
		$nsql = $sql.' where '.$map['where'];
		return $this->private_db->count_all_results($nsql);
	}

	//獲取體育串關記錄
	function get_record_cg($map){
		$this->private_db->from('k_bet_cg');
		$this->private_db->where($map['where']);
		$this->private_db->order_by($map['order']);
		$query = $this->private_db->get();
		//echo $this->private_db->last_query();
		return $query->result_array();
	}

	//獲取彩票投註記錄
	function get_record_cp($array,$map){
		$this->private_db->from('c_bet');
		$this->private_db->where($array);
		$this->private_db->order_by($map['order']);
		$this->private_db->limit($map['limit2'],$map['limit']);
		$query = $this->private_db->get();
		//echo $this->private_db->last_query();
		return $query->result_array();
	}

	//獲取彩票投註記錄
	function get_record_cp_count($array){
		$this->private_db->from('c_bet');
		$this->private_db->where($array);
		$query = $this->private_db->count_all_results();
		//echo $this->private_db->last_query();
		return $query;
	}

	//獲取交易記錄、往來記錄總條數
	function get_correspondence_count($map){
		$this->private_db->from('k_user_cash_record');
		$this->private_db->where($map['where']);
		$this->private_db->join('k_user', 'k_user.uid = k_user_cash_record.uid');
		$this->private_db->order_by('k_user_cash_record.id desc');
		$query = $this->private_db->count_all_results();
		//echo $this->private_db->last_query();
		return $query;
	}

	//獲取交易記錄、往來記錄
	function get_correspondence_record($map){
		$this->private_db->from('k_user_cash_record');
		$this->private_db->where($map['where']);
		$this->private_db->join('k_user', 'k_user.uid = k_user_cash_record.uid');
		$this->private_db->order_by('k_user_cash_record.id desc');
		$this->private_db->limit($map['limit2'],$map['limit']);
		$query = $this->private_db->get();
		//echo $this->private_db->last_query();
		return $query->result_array();
	}

	//獲取交易記錄、往來記錄總計
	function get_correspondence_totl($map){
		$this->private_db->from('k_user_cash_record');
		$this->private_db->where($map['where']);
		$this->private_db->select_sum('discount_num');
		$this->private_db->select_sum('cash_num');
		$query = $this->private_db->get();
		//echo $this->private_db->last_query();
		return $query->result_array();
	}

	//獲取報表統計
	function get_bb_count_sum($map){
		$this->private_db->from($map['table']);
		$this->private_db->where($map['where']);

		if(!empty($map['sum'])){
			foreach ($map['sum'] as $k=>$v){
				$this->private_db->select_sum($v);
			}
		}
		if(!empty($map['where_in'])) $this->private_db->where_in($map['where_in']['type'],$map['where_in']['val']);
		$query = $this->private_db->get();
		//echo $this->private_db->last_query();
		return $query->row_array();
	}

	//獲取報表統計總條數
	public function get_bb_count_co($map){
		$this->private_db->from($map['table']);
		$this->private_db->where($map['where']);
		$query = $this->private_db->count_all_results();
		return $query;
	}

	public function get_all_one($vtype) {    //視訊電子壹個種類的信息
		$this->db->from('k_video_games');
		$this->db->order_by("id ASC");
		$this->db->where('vtype',$vtype);
		$this->db->where('gtype',0);   //視訊
		return $this->db->get()->result_array();
	}

	//獲取可用彩票種類
	public function get_fc_games(){
		$redis = RedisConPool::getInstace();
		$redis_key = 'fc_games_all_data';
		//$fdata = $redis->get($redis_key);
		if ($fdata) {
			return json_decode($fdata,true);
		}else{
			$this->public_db->from('fc_games');
			$this->public_db->where('state',1);
			$fdata = $this->public_db->get()->result_array();
			//$redis->set($redis_key,json_encode($fdata));
		}
		return $fdata;
	}


	//報表
	public function report_data($type,$username,$site_id){
		$date = self::report_time($type);//時間區間

		$map = array();
		$map['username'] = $username;
		$map['site_id'] = $site_id;
		$map['day_time'] = array(array('>=',$date[0]),array('<=',$date[1]));

		//彩票
		$fdata['fc'] = $this->report_data_fc($map);
		//體育
		$fdata['sp'] = $this->report_data_sp($map);
		//視訊
		$vdata = $this->report_data_vd($map);
		//p($vdata);die;
		if ($vdata) {
			foreach ($vdata as $key => $val) {
				$fdata[$val['vtype']] = $val;
			}
		}

		return $fdata;
	}

	//當天 昨天 本周 上周
	public static function report_time($type){
		$date = array();
		switch ($type) {
			case 'today'://當天
				$mdate = date("Y-m-d");
				$date = array($mdate.' 00:00:00',$mdate.' 23:59:59');
				break;
			case 'yesterday'://昨天
				$mdate = date("Y-m-d", strtotime("-1 day"));
				$date = array($mdate.' 00:00:00',$mdate.' 23:59:59');
				break;
			case 'theweek'://本周
				$n_week = date('Y-m-d',(time()-((date('w')==0?7:date('w'))-1)*24*3600));
				$date = array($n_week.' 00:00:00',date("Y-m-d").' 23:59:59');
				break;
			case 'lastweek'://上周
				$n_week = date('Y-m-d',(time()-((date('w')==0?7:date('w'))-1)*24*3600));
				$l_week_s = date('Y-m-d', strtotime($n_week.'-7 day'));
				$l_week_e = date("Y-m-d", strtotime("last Sunday"));
				$date = array($l_week_s.' 00:00:00',$l_week_e.' 23:59:59');
				break;
			default:
				$mdate = date("Y-m-d");
				$date = array($mdate.' 00:00:00',$mdate.' 23:59:59');
				break;
		}
		return $date;
	}

	//彩票
	public function report_data_fc($map =array()){
		$db_model = array();
		$db_model['tab'] = 'c_bet_report';
		$db_model['type'] = 1;

		$data = $this->M($db_model)
			->field("username,sum(num) as num,sum(bet_all) as bet_all,sum(bet_yx) as bet_yx,sum(win-bet_yx) as win")
			->where($map)->find();
		return $data;
	}

	//體育
	public function report_data_sp($map =array()){
		$db_model = array();
		$db_model['tab'] = 'k_bet_report';
		$db_model['type'] = 1;

		$data = $this->M($db_model)
			->field("uid,username,sum(num) as num,sum(bet_all) as bet_all,sum(bet_yx) as bet_yx,sum(win-bet_yx) as win")
			->where($map)->find();
		return $data;
	}

	//電子
	public function report_data_vd($map =array()){
		$db_model = array();
		$db_model['tab'] = 'd_bet_report';
		$db_model['type'] = 1;

		$ddata = $this->M($db_model)
			->field("site_id,username,sum(num) as num,sum(bet_all) as bet_all,sum(bet_yx) as bet_yx,sum(win) as win,vtype")
			->where($map)->group("vtype")->select();
		//視訊
		$db_model['tab'] = 'v_bet_report';
		$vdata = $this->M($db_model)
			->field("site_id,username,sum(num) as num,sum(bet_all) as bet_all,sum(bet_yx) as bet_yx,sum(win) as win,vtype")
			->where($map)->group("vtype")->select();

		return array_merge_recursive($ddata,$vdata);
	}


}