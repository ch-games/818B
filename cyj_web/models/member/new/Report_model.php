<?php
if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Report_model extends MY_Model {

	function __construct() {
		parent::__construct();
		$this->load->model('Common_model');
		$this->init_db();
	}

	//獲取報表統計
	function get_count_list($map,$type,$valid=0){
		$db_model = array();
		$data = $this->get_tab_type($type,$valid);
		$db_model['tab'] = $data['table'];
		$db_model['type'] = '1';
		return $this->M($db_model)->field($data['field'])->where($map)->find();
	}

	//獲取報表統計總條數
	public function get_count_sum($table,$map){
		$db_model['tab'] = $table;
		$db_model['type'] = '1';
		return $this->M($db_model)->where($map)->count();
	}

	public function get_tab_type($type,$valid){

		switch ($type) {
			case '1':
				$data['table'] = "c_bet";
				if($valid == 1){
					$data['field'] = "sum(money) as money,sum(win) as win";
				}else{
					$data['field'] = "sum(money) as money";
				}
				break;
			case '2':
				$data['table']  = "k_bet";
				if($valid == 1){
					$data['field'] = "sum(bet_money) as bet_money,sum(win) as win";
				}else{
					$data['field'] = "sum(bet_money) as bet_money";
				}
				break;
			case '3':
				$data['table']  = "k_bet_cg_group";
				if($valid == 1){
					$data['field'] = "sum(bet_money) as bet_money,sum(win) as win";
				}else{
					$data['field'] = "sum(bet_money) as bet_money";
				}
				break;
			default:
				# code...
				break;

		}
		return $data;
	}




}