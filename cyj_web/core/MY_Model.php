<?php
/**
 *
 */
class MY_Model extends CI_Model {

	// protected $public_db = null;
	// protected $private_db = null;
	// protected $video_db = null;

	public function __construct() {
		$this->load->library('DBModel');
		require_once("./cyj_web/libraries/RedisConPool.php");
		$this->init_db();
	}

	//初始私有化數據庫
	protected function init_db() {
		/*if ($this->public_db == null) {
			$this->public_db = $this->load->database("public", TRUE, TRUE);
		}
		//連接私有數據庫
		if ($this->private_db == null) {
			$this->private_db = $this->load->database("private", TRUE, TRUE);
		}

		if ($this->video_db == null) {
			$this->video_db = $this->load->database("video", TRUE, TRUE);
		}*/

		// if ($this->manage_db == null) {
		// 	$this->manage_db = $this->load->database("manage", TRUE, TRUE);
		// }
	}


	public function __get($var){
		if($var == "public_db" || $var == "private_db" || $var == "video_db" || $var == "db"){
			if ($var == 'private_db' || $var == 'db') {
				if ($this->$var == null) {
					$this->private_db = $this->db = DB_Connect::getInstace("private");
				}
				return $this->$var;
			}
			if ($var == 'public_db') {
				if($this->public_db == null){
					$this->public_db = DB_Connect::getInstace("public");
				}
				return $this->$var;
			}
			if ($var == 'video_db') {
				if($this->video_db == null){
					$this->video_db = DB_Connect::getInstace("video");
				}
				return $this->$var;
			}
		}else{
			return parent::__get($var);
		}
	}

	//PHP stdClass Object轉array
	protected function object_array($array) {
		if(is_object($array)) {
			$array = (array)$array;
		} if(is_array($array)) {
			foreach($array as $key=>$value) {
				$array[$key] = $this->object_array($value);
			}
		}
		return $array;
	}

	public function M($mtab){
		if ($mtab['type'] == 1) {
			$data_con = DB_Connect::getInstace("private");
			$database = "private";
		}elseif ($mtab['type'] == 2) {
			$data_con = DB_Connect::getInstace("public");
			$database = "public";
		}elseif($mtab['type'] == 3){
			$data_con = DB_Connect::getInstace("video");
			$v_port = $data_con->port;
			$database = "video";
		}elseif($mtab['type'] == 4){
			$data_con = DB_Connect::getInstace("manage");
			$v_port = $data_con->port;
			$database = "manage";
		}else{
			$data_con = DB_Connect::getInstace("private");
			$database = "private";
		}

		$db_config = array();
		$db_config['dbname'] = $database;
		$db_config['link'] = $data_con->conn_id;
		return M($mtab['tab'],$db_config,$mtab['redis']);
	}





	//更新在線
	public function redis_update_user(){
		$redis = RedisConPool::getInstace();//調整為長連接
		$redis_key = 'ulg'.CLUSTER_ID.'_'.SITEID.$_SESSION['uid'];
		//只有正式賬號寫入在線
		if (empty($_SESSION['shiwan'])) {
			$redis->setex($redis_key,'1200','1');
		}

	}

	//退出
	public function redis_del_user(){
		$redis = RedisConPool::getInstace();
		$redis_key = 'ulg'.CLUSTER_ID.'_'.SITEID.$_SESSION['uid'];
		$redis->del($redis_key);
	}


	//刪除
	public function rdel($tab,$map){
		$obj_db = $this->tab_c($base_type);
		return $obj_db->delete($tab);
	}
	//更新
	public function rupdate($map,$arr=array(),$base_type = 1){
		$obj_db = $this->tab_c($base_type);
		$obj_db = $this->w_condition($obj,$map);
		return $obj_db->update($map['table'],$arr);
	}
	//添加
	public function radd($tab,$arr=array()){
		$this->db->insert($tab,$arr);
		return $this->db->insert_id();
	}
	//查詢單條
	public function rfind($map,$base_type = 1){
		$obj_db = $this->tab_c($base_type);
		$obj_db = $this->w_condition($obj,$map);
		$data=$obj_db->from($map['table'])->get()->result_array();
		if (!empty($data)) {
			return $data[0];
		}
	}
	//查詢列表
	public function rget($map,$base_type = 1){
		$obj_db = $this->tab_c($base_type);
		$obj_db = $this->w_condition($obj,$map);
		return $obj_db->from($map['table'])->get()->result_array();
	}

	//數據庫切換
	public function tab_c($type){
		switch ($type) {
			case '1':
				$obj_db = $this->db;
				break;
			case '2':
				$obj_db = $this->video_db;
				break;
			case '3':
				$obj_db = $this->public_db;
				break;
		}
		return $obj_db;
	}

	//條件拼接
	public function w_condition($obj,$map){
		$obj = empty($obj)?$this->db:$obj;
		if(!empty($map['select'])){
			$obj->select($map['select']);
		}else{
			$obj->select($map['table'].'.*');
		}
		if(!empty($map['join'])){
			$obj->join($map['join']['table'],$map['join']['action']);
		}
		if(!empty($map['where'])){
			$obj->where($map['where']);
		}

		if(!empty($map['where_in'])){
			$obj->where_in($map['where_in']);
		}
		if(!empty($map['where_not_in'])){
			$obj->or_where_in($map['where_not_in']);
		}
		if(!empty($map['or_where_in'])){
			$obj->or_where_in($map['or_where_in']);
		}
		if(!empty($map['or_where_not_in'])){
			$obj->or_where_not_in($map['or_where_not_in']);
		}
		if(!empty($map['like'])){
			$obj->like($map['like']['title'],$map['like']['match'],$map['like']['after']);
		}

		if(!empty($map['not_like'])){
			$obj->not_like($map['not_like']['title'],$map['not_like']['match'],$map['not_like']['after']);
		}

		if(!empty($map['or_like'])){
			$obj->or_like($map['or_like']['title'],$map['or_like']['match'],$map['or_like']['after']);
		}

		if(!empty($map['or_not_like'])){
			$obj->or_not_like($map['or_not_like']['title'],$map['or_not_like']['match'],$map['or_not_like']['after']);
		}

		if(!empty($map['order'])){
			$obj->order_by($map['order']);
		}


		if(!empty($map['pagecount'])){
			$obj->limit($map['pagecount'], $map['offset']);
		}
		if(!empty($map['limit_row'])){
			$obj->limit($map['limit_row'],$map['limit_start']);
		}
		if(!empty($map['limit'])){
			$obj->limit($map['limit']);
		}
		return $obj;
	}
	//統計總數
	public function rcount($tab,$map,$base_type){
		if (empty($tab)) {
			return 'system error 0000';
		}
		switch ($base_type) {
			case '1':
				if (!empty($map)) {
					$this->db->where($map);
				}
				return $this->db->count_all_results($tab);
				break;
			case '2':
				if (!empty($map)) {
					$this->video_db->where($map);
				}
				return $this->video_db->count_all_results($tab);
				break;
			case '3':
				if (!empty($map)) {
					$this->public_db->where($map);
				}
				return $this->public_db->count_all_results($tab);
				break;
		}

	}

	//操作日誌
	// public function Syslog($log){
	// 	$arr = array();
	// 	$arr['log_info'] = $log['log_info'].'(系統：'.getOS().')';
	// 	$arr['log_ip'] = $log['log_ip'];
	// 	$arr['site_id'] = $_SESSION['site_id'];
	//    	$arr['login_name'] = $_SESSION['login_name'];
	//    	$arr['log_time'] = date('Y-m-d H:i:s');
	//    	$arr['uid'] = $_SESSION['adminid'];
	// 	return $this->radd('sys_log',$arr);
	// }

	/**
	 * 查詢單個表
	 * @param  [array] $map 所有參數
	 * @param  [array] $map['select'] 查詢字段名
	 * @param  [array] $map['where'] 查詢where語句
	 * @param  [int] $map['pagecount'] 分頁的每頁總條數
	 * @param  [int] $map['offset'] limit的後參數
	 * @return [array]
	 */
	public function get_table_one($map,$base_type = 1){
		$one = $this->get_table($map,$base_type);
		return $one[0];
	}

	/**
	 * 查詢所有表
	 * @param  [array] $map 所有參數
	 * @param  [array] $map['select'] 查詢字段名
	 * @param  [array] $map['where'] 查詢where語句
	 * @param  [int] $map['pagecount'] 分頁的每頁總條數
	 * @param  [int] $map['offset'] limit的後參數
	 * @return [array]
	 */
	public function get_table($map=array(),$base_type = 1) {
		$obj_db = $this->tab_c($base_type);
		$obj_db->from($map['table']);

		if(!empty($map['select'])){
			$obj_db->select($map['select']);
		}else{
			$obj_db->select($map['table'].'.*');
		}
		if(!empty($map['join'])){
			$obj_db->join($map['join']['table'],$map['join']['action']);
		}
		if(!empty($map['where'])){
			$obj_db->where($map['where']);
		}

		if(!empty($map['where_in'])){
			$obj_db->where_in($map['where_in']);
		}
		if(!empty($map['where_not_in'])){
			$obj_db->or_where_in($map['where_not_in']);
		}
		if(!empty($map['or_where_in'])){
			$obj_db->or_where_in($map['or_where_in']);
		}
		if(!empty($map['or_where_not_in'])){
			$obj_db->or_where_not_in($map['or_where_not_in']);
		}
		if(!empty($map['like'])){
			$obj_db->like($map['like']['title'],$map['like']['match'],$map['like']['after']);
		}

		if(!empty($map['not_like'])){
			$obj_db->not_like($map['not_like']['title'],$map['not_like']['match'],$map['not_like']['after']);
		}

		if(!empty($map['or_like'])){
			$obj_db->or_like($map['or_like']['title'],$map['or_like']['match'],$map['or_like']['after']);
		}

		if(!empty($map['or_not_like'])){
			$obj_db->or_not_like($map['or_not_like']['title'],$map['or_not_like']['match'],$map['or_not_like']['after']);
		}

		if(!empty($map['order'])){
			$obj_db->order_by($map['order']);
		}
		if(!empty($map['group'])){
			$obj_db->group_by($map['group']);
		}


		if(!empty($map['pagecount'])){
			$obj_db->limit($map['pagecount'], $map['offset']);
		}
		if(!empty($map['limit_row'])){
			$obj_db->limit($map['limit_row'],$map['limit_start']);
		}
		if(!empty($map['limit'])){
			$obj_db->limit($map['limit']);
		}
		$query = $obj_db->get();

		$rows = $query->result_array();
		return $rows;
	}



	/**
	 *查詢表獲取條數
	 * @param  [array] $map 所有參數
	 * @param  [array] $map['where'] 查詢where語句
	 * @return [array]
	 */
	public function get_table_count($map=array(),$base_type = 1){
		$obj_db = $this->tab_c($base_type);
		if(!empty($map['where'])){
			$obj_db->where($map['where']);
		}
		if(!empty($map['where_in'])){
			$obj_db->where_in($map['where_in']['item'],$map['where_in']['data']);
		}
		// if (!empty($map['or_where'])) {
		//     $obj_db->or_where($map['or_where']);
		// }
		if(!empty($map['where_not_in'])){
			$obj_db->or_where_in($map['where_not_in']['item'],$map['where_not_in']['data']);
		}
		if(!empty($map['or_where_in'])){
			$obj_db->or_where_in($map['or_where_in']['item'],$map['or_where_in']['data']);
		}
		if(!empty($map['or_where_not_in'])){
			$obj_db->or_where_not_in($map['or_where_not_in']['item'],$map['or_where_not_in']['data']);
		}
		if(!empty($map['like'])){
			$obj_db->like($map['like']['title'],$map['like']['match'],$map['like']['after']);
		}

		if(!empty($map['not_like'])){
			$obj_db->not_like($map['not_like']['title'],$map['not_like']['match'],$map['not_like']['after']);
		}

		if(!empty($map['or_like'])){
			$obj_db->or_like($map['or_like']['title'],$map['or_like']['match'],$map['or_like']['after']);
		}

		if(!empty($map['or_not_like'])){
			$obj_db->or_not_like($map['or_not_like']['title'],$map['or_not_like']['match'],$map['or_not_like']['after']);
		}

		return  $obj_db->count_all_results($map['table']);
	}

	/**
	 * 表添加
	 * @param  [array] $map 所有參數
	 * @param  [array] $map['data'] 添加的數據數組
	 * @return [array]
	 */
	public function create_table($map=array()){
		$return = $this->db->insert($map['table'],$map['data']);
		return $return;
	}

	/**
	 * 表修改
	 * @param  [array] $map 所有參數
	 * @param  [array] $map['where'] 查詢where語句
	 * @return [array]
	 */
	public function update_table($map=array()){
		if(!empty($map['where'])){
			$this->db->where($map['where']);
		}
		if(!empty($map['where_in'])){
			$this->db->where_in($map['where_in']['item'],$map['where_in']['data']);
		}
		$return = $this->db->update($map['table'],$map['data']);
		return $return;
	}




	/**
	 * 表刪除
	 * @param  [array] $map 所有參數
	 * @param  [array] $map['where'] 查詢where語句
	 * @return [array]
	 */
	public function del_table($map=array()){
		$this->db->where($map['where']);
		$return = $this->db->del($map['table'],$map['data']);
		return $return;
	}

	//登錄判斷
	public function login_check($uid){
		if(!isset($uid))
		{
			echo "<script>alert(\"請先登錄再進行操作\");top.location.href='/';</script>";
			exit();
		}else{
			$this->db->from('k_user_login');
			$this->db->where('uid',$uid);
			$this->db->where('is_login',1);
			$ulog = $this->db->get()->row_array();
			//判斷會員是否已經禁用
			$this->db->from('k_user');
			$this->db->where('uid',$uid);
			$this->db->select('is_delete');
			$isUse = $this->db->get()->row_array();
			if ($isUse['is_delete'] == '1') {
				echo "<script>alert('對不起，您的賬號異常已被停止，請與在線客服聯系！');</script>";
				echo "<script>top.location.href='/';</script>";
				exit;
			}elseif($isUse['is_delete'] == '2'){
				echo "<script>alert('對不起，您的賬號異常已被暫停使用，請與在線客服聯系！');</script>";
				echo "<script>top.location.href='/';</script>";
				exit;
			}

			//屏蔽試玩賬號檢測
			if ($_SESSION['shiwan'] == '1') {
				$ulog['uid'] = $_SESSION['uid'];
			}

			if($ulog['uid'] > 0){
				if($ulog['ssid'] != $_SESSION["ssid"])
				{
					//別處登陸
					echo "<script  charset=\"utf-8\" language=\"javascript\" type=\"text/javascript\">alert(\"請重新登陸賬號\");</script>";
					session_destroy();
					echo "<script>top.location.href='/';</script>";
					exit();
				}else{
					//更新在線時間
					if (!$_SESSION['shiwan']) {
						$this->redis_update_user();
					}
				}

			}else{
				session_destroy();
				echo "<script>top.location.href='/';</script>";
			}
		}

	}
	//獲取附件域名
	public function getthumbdomain($a=null){
		$query=$this->private_db->get_where('info_thumb_domain',['status'=>1],1,0);
		$d=$query->row_array();
		return $d;
	}
	//替換附件域名標簽
	public  function replacedomain($d){
		$thumb_domain=$this->getthumbdomain();
		$d=str_replace('[_domain_]',$thumb_domain['get_domain'],$d);
		return $d;
	}

	public function set_redis_key($redis_key,$data){
		$redis = RedisConPool::getInstace();
		$redis->set($redis_key,json_encode($data));
	}

	public function get_redis_key($redis_key){
		$redis = RedisConPool::getInstace();
		$data = json_decode($redis->get($redis_key),TRUE);
		return $data;
	}

	public function del_redis_key($redis_key){
		$redis = RedisConPool::getInstace();
		$redis->delete($redis_key);
	}

	public function isset_redis_key($redis_key){
		$redis = RedisConPool::getInstace();
		$data = $redis->exists($redis_key);
		return $data;
	}
}

class DB_Connect extends CI_Model {
	private static $Instace = [];           ///對象

	public static function getInstace($params){
		if(!isset(static::$Instace[$params])){
			static::$Instace[$params] = & DB($params,TRUE);
		}
		return static::$Instace[$params];
	}
}