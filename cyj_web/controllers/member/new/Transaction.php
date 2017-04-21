<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transaction extends MY_Controller {

	private $video_config;
	public function __construct() {
		parent::__construct();
		$this->load->model('member/new/Transaction_model','TransMod');
		$this->TransMod->login_check($_SESSION['uid']);
		$this->video_config = $this->TransMod->transaction_module_data();
	}

	//交易記錄
	public function transaction_bet_index(){
		$fc_types = $this->TransMod->get_fc_games();
		$videos = $this->video_config;
		$arr = Array(sp,fc,ag,agdz,agter,bbin,bbdz,lebo,im,mg,mgdz,ct,gd,gddz,gpi,gpidz,lmg,sa,og,gg,hb,eg,pt,dg99);
		$videos_arr = array();
		foreach ($arr as $key => $value){
			if($videos[$value]){
				$videos_arr[$value] = $videos[$value];
			}
		}
		//p($videos_arr);die;
		$this->add('fc_games',$fc_types);
		$this->add("s_date", date("Y-m-d",time()));
		$this->add("e_date", date("Y-m-d",time()));
		$this->add('video_config',$videos_arr);
		$this->display('web_public/member/report/transaction.html');
	}

	//ajax獲取交易記錄數據
	public function transaction_record_do(){
		$get = $this->input->post();
		$typeArr = array_keys($this->video_config);
		if (!in_array($get['g_type'], $typeArr)) { //過濾查詢視訊類型
			$data['errorLevel'] = '';
			$data['error'] = '參數錯誤！';
			echo json_encode($data,JSON_UNESCAPED_UNICODE);exit();
		}
		if (!empty($get['OrderId'])) { //過濾訂單號
			if (preg_match("/^[\W]*$/i",$get['OrderId'])){
				$data['errorLevel'] = 'prompt';
				$data['error'] = "您輸入的訂單號非法！";
				echo json_encode($data,JSON_UNESCAPED_UNICODE);exit();
			}
		}
		//獲取數據
		$data = $this->TransMod->transaction_bet_record_data($get);
		if (!empty($data['data'])) {
			$data['Code'] = 10021;
		}else{
			$data['Code'] = 10022;
		}
		$data['gtype'] = $get['Sptype'];;
		$data['company'] = $get['g_type'];
		echo json_encode($data,JSON_UNESCAPED_UNICODE);exit();
	}

	#往來記錄--開始#
	//往來記錄 頁面列表   
	public function transaction_contacts_index(){
		$s_date = $e_date = date('Y-m-d');
		$this->add('s_date',$e_date);
		$this->add('e_date',$s_date);
		$this->display('web_public/member/report/contacts.html');
	}

	//往來記錄 數據查詢
	public function transaction_contacts_ajax_do(){
		$get = $this->input->get();//接受get數據
		$data = $get;
		$uid = $_SESSION['uid'];
		$s_date = $get['start_date'];
		$e_date = $get['end_date'];
		$deptype = $get['deptype'];
		$page = $get['page'];
		$detype = empty($get['deptype'])?"Monitor":$get['deptype'];



		//方式
		if (!empty($deptype) && $deptype != "Monitor") {
			//時間判斷
			if (!empty($s_date) && !empty($e_date)) {
				$map['where'] = "k_user_cash_record.cash_date > '".$s_date." 00:00:00' and k_user_cash_record.cash_date < '".$e_date." 23:59:59' ";
				$con['where'] = "cash_date > '".$s_date." 00:00:00' and cash_date < '".$e_date." 23:59:59' ";
			}elseif (!empty($s_date)) {
				$map['where'] = "k_user_cash_record.cash_date > '".$s_date." 00:00:00' ";
				$con['where'] = "cash_date > '".$s_date." 00:00:00' ";
			}elseif (!empty($e_date)) {
				$map['where'] = "k_user_cash_record.cash_date < '".$e_date." 23:59:59' ";
				$con['where'] = "cash_date < '".$e_date." 23:59:59' ";
			}else{
				$map['where'] = "k_user_cash_record.cash_date like '".date('Y-m-d')."%' ";
				$con['where'] = "cash_date like '".date('Y-m-d')."%' ";
			}

			$map['where'] .= " and k_user_cash_record.is_show = 1 and k_user.site_id = '".SITEID."' ";


			$type;
			$type = $deptype;
			$arrType = explode('-', $deptype);
			if (count($arrType) > 1) {
				if($arrType[2] ==1){
					$map['where'] .= " and k_user_cash_record.cash_only = '".$arrType[2]."'  ";
					$con['where'] .= " and cash_only = '".$arrType[2]."'";
				}else{
					//表示檢索參數cash_do_type
					$map['where'] .= " and ((k_user_cash_record.cash_do_type = '".$arrType[0]."' and k_user_cash_record.cash_type = '".$arrType[1]."' ) or k_user_cash_record.cash_do_type = '".$arrType[2]."') ";
					$con['where'] .= " and ((cash_do_type = '".$arrType[0]."' and cash_type = '".$arrType[1]."' ) or cash_do_type = '".$arrType[2]."') ";
				}
			}else{
				if($type == 1 || $type == 2 || $type == 4 || $type == 3 || $type == 14 || $type == 15 || $type==19 || $type==7 ||$type==23){
					$map['where'] .= " and k_user_cash_record.cash_type = '".$type."'";
					$con['where'] .= " and cash_type = '".$type."'";
				}elseif($type == 'in'){
					//入款明細
					$map['where'] .= " and (k_user_cash_record.cash_do_type = '3' or k_user_cash_record.cash_type in (10,11)) ";
					$con['where'] .= " and (cash_do_type = '3' or cash_type in (10,11)) ";
				}elseif($type == 'out'){
					//出款明細
					$map['where'] .= " and ((k_user_cash_record.cash_do_type = '2' and k_user_cash_record.cash_type = '12') or k_user_cash_record.cash_type in (7,8,19)) ";
					$con['where'] .= " and ((cash_do_type = '2' and cash_type = '12') or cash_type in (7,8,19)) ";
				}
				else{
					$map['where'] .= " and k_user_cash_record.cash_type = '".$type."' ";
					$con['where'] .= " and cash_type = '".$type."' ";
				}

			}
		}

		//賬戶
		$map['where'] .= "and k_user.uid = '".$_SESSION['uid']."'";
		$con['where'] .= "and uid = '".$_SESSION['uid']."' ";

		//總數緩存redis 5秒
		$redis = RedisConPool::getInstace();
		$redis_key = SITEID.'_corres_'.$_SESSION['uid'].'_'.md5(md5(json_encode($map)));
		if ($redis->exists($redis_key)) {
			$count = $redis->get($redis_key);
		}else{
			//獲得記錄總數
			$count = $this->TransMod->get_correspondence_count($map);
			$redis->setex($redis_key,'5',$count);
		}
		//分頁
		$perNumber = 50; //每頁顯示的記錄數
		$totalPage = ceil($count/$perNumber); //計算出總頁數

		$page = isset($get['page']) ? $get['page'] : 1;
		$startCount = ($page - 1) * $perNumber; //分頁開始,根據此方法計算出開始的記錄
		$map['limit'] = $startCount.','.$perNumber;

		$data['data'] = $this->TransMod->get_correspondence_record($map);
		foreach ($data['data'] as $key => $value) {
			$data['data'][$key]['cash_type_fy'] = cash_type_r($data['data'][$key]['cash_type']);
			$data['data'][$key]['cash_do_type_fy'] = cash_do_type_r($data['data'][$key]['cash_do_type']);
		}
		$data['totalPage'] = $totalPage;
		$data['thisPage'] = $page;
		//小計
		foreach ($data as $k=>$val){
			$counts += $val['cash_num']+$val['discount_num'];
		}
		//總計
		$totl = $this->TransMod->get_correspondence_totl($con);
		if(!empty($totl)) $all_count=number_format($totl['0']['cash_num']+$totl['0']['discount_num'],2);

		//入款監控
		if($detype == 'Monitor'){
			$s_date = $get['start_date'];
			$e_date = $get['end_date'];
			$map['where'] = "site_id = '".SITEID."' and index_id = '".INDEX_ID."' and uid = '".$_SESSION['uid']. "'";
			//時間判斷
			if (!empty($s_date) && !empty($e_date)) {
				$map['where'] .= " and in_date > '".$s_date." 00:00:00' and in_date < '".$e_date." 23:59:59' ";
			}elseif (!empty($s_date)) {
				$map['where'] .= " and in_date > '".$s_date." 00:00:00' ";
			}elseif (!empty($e_date)) {
				$map['where'] .= " and in_date < '".$e_date." 23:59:59' ";
			}else{
				$map['where'] .= " and in_date like '".date('Y-m-d')."%' ";
			}

			//獲得記錄總數
			$count = $this->TransMod->get_monitor_count($map);

			//分頁
			$perNumber = 50; //每頁顯示的記錄數
			$totalPage = ceil($count/$perNumber); //計算出總頁數

			$page = isset($get['page']) ? $get['page'] : 1;
			$startCount = ($page - 1) * $perNumber; //分頁開始,根據此方法計算出開始的記錄
			$map['limit'] = $startCount.','.$perNumber;

			$result = $this->TransMod->get_monitor_record($map);

			$data['totalPage'] = $totalPage;
			$data['thisPage'] = $page;
			foreach ($result as $key => $value) {
				$data['data'][$key]['cash_date'] = $value['in_date'];
				$data['data'][$key]['cash_type_fy'] = "存入";
				$data['data'][$key]['cash_do_type_fy'] = $this->TransMod->into_style_zh($value['into_style']);
				$data['data'][$key]['cash_num'] = $value['deposit_num'];   //交易金額
				$data['data'][$key]['discount_num'] = $value['other_num'] + $value['favourable_num'];   //優惠金額
				$data['data'][$key]['remark'] = $this->TransMod->make_sure_zh(intval($value['make_sure']));
			}
		}
		//p($data);die;
		echo json_encode($data);exit;
	}
	#往來記錄--結束#


}