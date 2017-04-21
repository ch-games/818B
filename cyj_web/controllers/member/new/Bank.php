
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bank extends MY_Controller {
	private $UserInfo,$wxewm,$zfbewm,$is_have_online_type,$DePosit;
	public function __construct() {
		parent::__construct();
		$this->load->model('member/Cash_model');
		$this->load->model('member/new/Bank_model');
		$this->load->model('Common_model');
		$this->load->model('member/pay/Online_api_model');
		$this->Online_api_model->login_check($_SESSION['uid']);
		$this->DePosit = $this->Cash_model->get_setmoney();
		$this->wxewm = $this->Cash_model->get_setmoney_qr('21'); //獲取微信二維碼
		$this->zfbewm = $this->Cash_model->get_setmoney_qr('22'); //獲取微信二維碼
		$this->is_have_online_type = $this->Online_api_model->is_have_online_type(); //獲取三方信息
		$this->UserInfo = $this->Common_model->get_user_info($_SESSION['uid']);
		$this->add('color',$this->Common_model->get_membercolor());
		$this->add('wxewm',$this->wxewm);
		$this->add('zfbewm',$this->zfbewm);
		$this->add('is_have_online_type',$this->is_have_online_type);
		$this->add('userinfo',$this->UserInfo);
		$this->add('copy_right',$this->Common_model->get_copyright());
	}

	//額度轉換
	public function bank_transf_index(){
		$istransf = $this->Bank_model->get_istransf();
		/*if ($istransf['is_transf'] == 0 && SITEID == 't') {
			echo "<h1>額度轉換已被禁用，如有疑問請聯系在線客服！</h1>";
			exit();
		}*/
		$data = array();
		$data['userinfo'] = $this->UserInfo;
		//獲取額度轉換最低限額
		$data['MinInMoney'] = $this->Bank_model->get_transf_min($data['userinfo']['level_id']);
		if ($data['userinfo']['shiwan'] == 1) {
			echo "<script>";
			echo 'alert("試玩賬號不能存取款，請註冊正式賬號！");';
			echo "</script>";
			exit();
		}

		//視訊配置
		$data['video_config'] = $this->Common_model->get_video_dz_config();
		$data['moneyarr']['sport']['money'] = $data['userinfo']['allmoney'] = $data['userinfo']['money'];
		$data['moneyarr']['sport']['name'] = '系統余額';
		$data['moneyarr']['sport']['key'] = 'sport';
		foreach ($data['video_config'] as $key => $val) {
			if ($val == 'eg') {
				unset($video_config[$key]);
				unset($data['video_config'][$key]);
			}else{
				$data['userinfo']['allmoney'] += $data['userinfo'][$val.'_money'];
				$data['moneyarr'][$val]['money'] = $data['userinfo'][$val.'_money'];
				$data['moneyarr'][$val]['name'] = strtoupper($val).'余額';
				$data['moneyarr'][$val]['key'] = $val;
			}
		}
		$_SESSION['edcashtoken'] = md5(round(00000000,55555555));
		$data['userinfo']['edcashtoken'] = $_SESSION['edcashtoken'];
		$data['moneyarr']['all']['money'] = $data['userinfo']['allmoney'];
		$data['moneyarr']['all']['name'] = '總計';
		$data['moneyarr']['all']['key'] = 'allmoney';
		$data['moneyarr'] = array_values($data['moneyarr']);
		$this->add('data',$data);
		$this->display('web_public/member/bank/bank.html');
	}

	//額度轉換處理
	public function bank_transf_index_do(){
		$istransf = $this->Cash_model->get_istransf();
		/*if ($istransf['is_transf'] == 0 && SITEID == 't') {
            echo json_encode(array("status" => 24, "info" => "額度轉換已被停用，如有疑問請聯系客服！"));
                die();
        }*/
		if(empty($_SESSION['edcashtoken']) || $_SESSION['edcashtoken'] != $_POST['edcashtoken']){
			echo json_encode(array("status" => 22, "info" => "請刷新頁面，重試！"));
			die();
		}
		unset($_SESSION['edcashtoken']);
		$credit_bak = floatval($this->input->post('p3_Amt'));

		if ($_SESSION['shiwan'] == 1) {
			echo json_encode(array("status" => 20, "info" => "試玩賬號不能存取款，請註冊正式賬號！"));exit;
		}
		$uid = @$_SESSION['uid'];
		$username = @$_SESSION['username'];
		$userinfo = $this->UserInfo;

		//$cash_record = $this->Cash_model->get_cash_record($userinfo['uid']);  //獲取金額交易記錄
		if(isset($_SESSION['edzhtime'])){
			$time = time() - $_SESSION['edzhtime'];
			if ($time < 60) {
				echo json_encode(array("status" => 17, "info" => "請在" . (60 - $time) . "秒後操作"));exit;
			}
		}

		$list = $this->Common_model->get_video_dz_config();
		$g_type_arr = array('sport');
		$g_type_arr = array_merge($g_type_arr,$list);

		$trtype1 = trim($this->input->post('trtype1'));
		$trtype2 = trim($this->input->post('trtype2'));
		if($trtype1 == $trtype2){
			echo json_encode(array("status" => 19, "info" => "轉入轉出平臺不能相同，請重新選擇" ));exit;
		}
		if(in_array('mg', $list)){
			$list[] = 'mg_game';
		}elseif(in_array('ag', $list)){
			$list[] = 'ag_game';
		}elseif(in_array('bbin', $list)){
			$list[] = 'bbin_game';
		}elseif(in_array('gd', $list)){
			$list[] = 'gd_game';
		}elseif(in_array('gpi', $list)){
			$list[] = 'gpi_game';
		}
		$wh = array();
		$arr = array('mg','ag','bbin','gd','gpi');
		foreach($list as $val){
			if($val == 'pt'){
				$strval = 'pt_game';
			}else{
				$strval = $val;
			}

			if($this->GetSiteStatus($this->SiteStatus,2,$strval,1)){
				$wh[$strval] = 9999;
			}else{
				$wh[$strval] = 1111;
			}
			foreach ($arr as $value) {
				if($wh[$value] && $wh[$value.'_game']){
					if($wh[$value] == $wh[$value.'_game']){
						$temp = $wh[$value];
						$wh[$value] = $temp;
					}else{
						$wh[$value] = 1111;
					}
					unset($wh[$value.'_game']);
				}
			}

		}

		foreach ($wh as $val2) {
			if($wh[$val2] == 9999){
				if($trtype1 == $val2 || $trtype2 == $val2){
					$str  = $val2."遊戲正在進行維護中！\n請您選擇其他遊戲！祝您遊戲開心！";
					echo json_encode(array("status" => 23, "info" => $str ));exit;
				}
			}
		}

		$tc_type = $g_type = "";
		if (!in_array($trtype1, $g_type_arr) && !in_array($trtype2, $g_type_arr)) {
			echo json_encode(array("status" => 1, "info" => "未知的遊戲!"));exit;
		}
		if (empty($username)) {
			echo json_encode(array("status" => 2, "info" => "請登錄再進行遊戲!"));exit;
		}
		if ($trtype1 == "sport" && $trtype2 != "sport") {
			$tc_type = "IN";
			$g_type = $trtype2;
			$do_type = 0;
		}
		if ($trtype2 == "sport" && $trtype1 != "sport") {
			$tc_type = "OUT";
			$g_type = $trtype1;
			$do_type = 1;
		}
		if (empty($tc_type)) {
			echo json_encode(array("status" => 3, "info" => "額度轉換,只能在系統余額和視訊余額之間轉換,視訊余額之間不能直接轉換!"));exit;
		}
		if($tc_type == 'IN'){
			if($credit_bak>$userinfo['money']){
				echo json_encode(array("status" => 25, "info" => "余額不足，請先充值！"));exit;
			}
		}
		$credit = floatval($this->input->post('p3_Amt'));
		$transf_money_min = intval($this->input->post('transf_money_min'));
		//$transf_money_min = 20;
		if ($credit < $transf_money_min) {
			echo json_encode(array("status" => 4, "info" => "轉換的額度，必須大於".$transf_money_min."!"));exit;
		}

		$this->load->library('Games');
		$games = new Games();
		if($tc_type == 'OUT'){
			$money_result = $games->GetBalance($username,$g_type);
			$money_result = json_decode($money_result);
			if($money_result->data->Code == 10017){
				if($credit > $money_result->data->balance){
					echo json_encode(array("status" => 26, "info" => "對應遊戲額度不足！"));exit;
				}
			}
		}
		$data = $games->TransferCredit($username, $g_type, $tc_type, $credit);

		$result = json_decode($data);
		if ($result->data->Code == 10006) {
			if($g_type == 'pt'){$cur="CNY";}
			$data = $games->CreateAccount($username, $userinfo["agent_id"], $g_type,INDEX_ID,$cur);
			if (!empty($data)) {
				$result = json_decode($data);
				if ($result->data->Code != 10011) {
					echo json_encode(array("status" => 12, "info" => "額度轉換失敗,錯誤代碼R007 "));exit;
				} else {
					//用戶添加成功轉賬重試
					$data = $games->TransferCredit($username, $g_type, $tc_type, $credit);
					if (empty($data)) {
						echo json_encode(array("status" => 13, "info" => "由於網絡原因，轉賬失敗，請聯系管理員 "));exit;
					}
					$result = json_decode($data);
				}
			}
		}

		//寫入額度轉換日誌
		$this->Cash_model->conversion_log($g_type,$do_type,$credit,$result->data->Code,json_encode($result));

		//視訊額度不足提示
		if ($result->data->Code == 100004) {
			$cmsg = array();
			$cmsg['status'] = 22;
			$cmsg['info'] = '錯誤碼08,請聯系客服!';
			exit(json_encode($cmsg));
		}
		$_SESSION['edzhtime'] = time();
		if ($result->data->Code == 10013) {
			echo json_encode(array("status" => 18, "info" => "轉賬成功 "));exit;
		}else{
			echo json_encode(array("status" => 19, "info" => "轉賬失敗，余額不足或第三方正在維護中 "));exit;
		}
	}

	//公司入款
	public function bank_onlinein_index(){
		if($_SESSION['shiwan'] == 1){
			echo "試玩賬號不能存取款，請註冊正式賬號！";exit;
		}
		$deposit = $this->DePosit;    // 獲取存款文案信息
		foreach($deposit as $k => $v){
			$deposit[$k]['content'] = $this->Cash_model->replacedomain($deposit[$k]['content']);
		}
		foreach($deposit as $k => $v){
			if($v['type'] == 2){
				$deposit_new = $v;
			}
		}

		$bank_arr = $this->Cash_model->get_bank_arr();//獲取銀行列表
		//獲取已剔除的銀行卡
		$bank_del = $this->Cash_model->get_bank_reject();
		//入款銀行卡
		$banks = $this->Cash_model->get_bank_in_arr($_SESSION['level_id']);//獲取入款銀行列表
		if(empty($banks)){
			// echo "<script>alert('沒有可用的銀行卡，請聯系客服！');";
			//echo "window.close();</script>";exit;
		}else{
			foreach ($bank_arr as $key => $value) {
				if(!in_array($value['id'], $bank_del)){
					$bank_array[] = $value;
				}
			}
		}
		foreach ($banks as $key => $value) {
			foreach ($bank_array as $k => $v) {
				if($value['bank_type'] == $v['id']){
					$banks[$key]['bank_name'] = $v['bank_name'];
				}
			}
		}
		$copyright = $this->Common_model->get_copyright();
		$levelinfo = $this->Common_model->get_user_level_info($_SESSION['uid']);
		$order=date("YmdHis").mt_rand(1000,9999);//訂單號
		$this->add('bank_arr',json_encode($bank_array,JSON_UNESCAPED_UNICODE));
		$this->add('banks',$banks);
		$this->add('order',$order);
		$this->add('date',date('Y-m-d H:i:s'));
		$this->add('levelinfo',$levelinfo);
		$this->add('copyright',$copyright);
		$this->add('deposit',$deposit_new);
		$this->display('web_public/member/bank/onlinein.html');
	}



	//公司入款處理
	public function bank_ajax(){
		if($_SESSION['shiwan'] == 1){
			echo $this->Common_model->JSON(array('statu'=>'4'));exit();
		}
		// if(SITEID == 't' && $_SESSION['username'] != 'pkaaanb'){
		// 	echo $this->Common_model->JSON(array('statu'=>'5'));exit();
		// }
		$deposit_num    = $this->input->post('deposit_num');//入款金額
		$bank_style     = $this->input->post('bank_style'); //會員使用的銀行
		$order_num_old  = $this->input->post('order_num');  //訂單號
		$res = preg_match('#^\d{18}$#', $order_num_old,$order_num_new);
		if(!$res){
			echo $this->Common_model->JSON(array('statu'=>'6'));exit();
		}
		$order_num = $order_num_new[0];
		$deposit_way    = $this->input->post('deposit_way');//存款方式
		$in_name        = $this->input->post('in_name'); //存款人姓名
		$in_date        = $this->input->post('in_date'); //存入時間

		$in_atm_address = $this->input->post('bank_location1').$this->input->post('bank_location2').$this->input->post('bank_location4');

		$action         = $this->input->post('action');

		if(!empty($action) && $action == 'add_form'){

			$bid = $this->input->post('bid');
			if(!empty($bid)){
				$bank = $this->Bank_model->get_bank();   //獲取銀行官網
			}

			$user = $this->UserInfo;

			$agent = $this->Bank_model->get_agent_user();   //獲取代理商帳號
			//查詢是不是首次入款
			$user_record = $this->Bank_model->is_first_in();
			$levelinfo = $this->Common_model->get_user_level_info($_SESSION['uid']);

			if($deposit_num > $levelinfo['line_catm_max']){
				echo $this->Common_model->JSON(array('statu'=>'1','infos'=>$levelinfo['line_catm_max']));exit();
			}

			if($deposit_num < $levelinfo['line_catm_min']){
				echo $this->Common_model->JSON(array('statu'=>'2','infos'=>$levelinfo['line_catm_min']));exit();
			}

			//防止用戶惡意提交表單
			$result = $this->Bank_model->get_order_num($order_num);
			if(!empty($result['order_num'])){
				echo $this->Common_model->JSON(array('statu'=>'3'));exit();
			}

			$level_des = $this->Bank_model->get_level_des();   //獲取層級

			$data = array();
			if($deposit_way==2||$deposit_way==3||$deposit_way==4){
				$data['in_info'] = $this->Common_model->bank_type($bank_style).','.$in_name.','.$in_date.','.$this->Common_model->in_type($deposit_way).','.$in_atm_address;
			}else{
				$data['in_info'] = $this->Common_model->bank_type($bank_style).','.$in_name.','.$in_date.','.$this->Common_model->in_type($deposit_way);
			}
			$data['in_date']        = $in_date;
			$data['in_type']        = $deposit_way;


			$data['bid']        = $bid;
			$data['into_style']     = 1;
			$data['bank_style']     = $bank_style;
			$data['in_atm_address'] = $in_atm_address;
			$data['in_name']        = $in_name;
			$data['log_time']       = date("Y-m-d H:i:s");//系統提交時間
			$data['deposit_num']    = $deposit_num;
			$data['order_num']      = $order_num;
			$data['username']       = $_SESSION['username'];
			$data['agent_user']     = $agent['agent_user'];
			$data['agent_id']       = $_SESSION['agent_id'];
			$data['uid']            = $_SESSION['uid'];
			$data['level_id']       = $_SESSION['level_id'];
			$data['level_des']      = $level_des['level_des'];
			$data['site_id']        = SITEID;
			$data['index_id']       = INDEX_ID;
			$data['is_firsttime']   = empty($user_record['id']) ? 1 : 0;
			//存款優惠判斷
			if ($data['deposit_num'] >= $levelinfo['line_discount_num']) {
				$data['favourable_num'] = (0.01*$data['deposit_num']*$levelinfo['line_discount_per']>$levelinfo['line_discount_max'])?$levelinfo['line_discount_max']:(0.01*$data['deposit_num']*$levelinfo['line_discount_per']);
			}

			//判斷是否開啟首存優惠
			if (($levelinfo['line_deposit'] == '2') && !$data['is_firsttime']) {
				$data['favourable_num'] = 0;
			}


			//其它優惠判斷
			if ($data['deposit_num'] >= $levelinfo['line_other_discount_num']) {
				$data['other_num'] = (0.01*$data['deposit_num']*$levelinfo['line_other_discount_per']>$levelinfo['line_other_discount_max'])?$levelinfo['line_other_discount_max']:(0.01*$data['deposit_num']*$levelinfo['line_other_discount_per']);
			}
			$data['deposit_money'] = $data['deposit_num']+$data['other_num']+$data['favourable_num'];//存入總金額

			$json_data = array();
			$json_data['deposit_num']=$this->input->post('deposit_num');//存入金額：
			$json_data['now_date']=$this->input->post('now_date');//存入時間：
			$json_data['in_name']=$this->input->post('in_name');//存款人姓名：
			$json_data['d_bank_location'] = $in_atm_address;
			$json_data['bank']= $bank['card_address'];

			$this->db->from('k_user_bank_in_record');
			$this->db->set($data);
			if ($this->db->insert()) {
				$json_data['ok']=1;
				$json_data['bank_style']=$this->Common_model->bank_type($data['bank_style']);
				//刪除監控緩存key
				$redis = RedisConPool::getInstace();
				$redis_key = SITEID.'_bankin_monitor';
				$redis->delete($redis_key);
				echo $this->Common_model->JSON($json_data);
			}else{
				$json_data['ok']=2;
				echo $this->Common_model->JSON($json_data);
			}
		}
	}








	//微信存款
	public function bank_wechatin_index(){
		$type = '21'; //二維碼類型
		$qrs = $this->wxewm; //獲取微信二維碼
		if (empty($type)) {
			$type = $qrs[0]['type'];
		}
		$order=date("YmdHis").mt_rand(1000,9999);//訂單號
		$_SESSION['order'] = $order; //訂單號存入session
		$card_msg = $this->Bank_model->get_bank_in_one($type);
		$levelinfo = $this->Common_model->get_user_level_info($_SESSION['uid']);
		$this->add('levelinfo',$levelinfo);
		$this->add('qr',$qrs);
		$this->add('order',$order);
		$this->add('card_msg', $card_msg);
		$this->add('type',$type);
		$this->add('shiwan',$_SESSION['shiwan']);
		$this->add('date',date('Y-m-d H:i:s',time()));
		$this->display('web_public/member/bank/wechatin.html');
	}

	//支付寶存款
	public function bank_alipayin_index(){
		$type = '22'; //二維碼類型
		$qrs = $this->zfbewm; //獲取微信二維碼
		if (empty($type)) {
			$type = $qrs[0]['type'];
		}
		$order=date("YmdHis").mt_rand(1000,9999);//訂單號
		$_SESSION['order'] = $order; //訂單號存入session
		$card_msg = $this->Bank_model->get_bank_in_one($type);
		$levelinfo = $this->Common_model->get_user_level_info($_SESSION['uid']);
		$this->add('levelinfo',$levelinfo);
		$this->add('qr',$qrs);
		$this->add('order',$order);
		$this->add('card_msg', $card_msg);
		$this->add('type',$type);
		$this->add('shiwan',$_SESSION['shiwan']);
		$this->add('date',date('Y-m-d H:i:s',time()));
		$this->display('web_public/member/bank/alipayin.html');
	}

	//第三方網銀
	public function bank_onlinebank_index(){
		if($_SESSION['shiwan'] == 1){
			echo "試玩賬號不能存取款，請註冊正式賬號！";exit;
		}

		$deposit = $this->DePosit;    // 獲取存款文案信息
		foreach($deposit as $k => $v){
			$deposit[$k]['content'] = $this->Cash_model->replacedomain($deposit[$k]['content']);

		}
		foreach ($deposit as $k => $v) {
			if($v['type'] == 1){
				$deposit_new = $v;
			}
		}
		$userinfo = $this->UserInfo;
		$level_info = $this->Common_model->get_user_level_info($userinfo['level_id']);
		//線上入款最小金額 最大金額
		$_SESSION['ol_catm_max'] = $level_info['ol_catm_max'];
		$_SESSION['ol_catm_min'] = $level_info['ol_catm_min'];
		if($_SESSION['pay']['is_card'] == 1){
			unset($_SESSION['pay']);
		}
		if($_SESSION['pay']['is_wechat'] == 1){
			unset($_SESSION['pay']);
		}
		if($_SESSION['pay']['payid'] > 0){
			$status = $_SESSION['pay'];    //防止用戶壹直在銀行卡頁面刷新 而更換了支付方式   在成功跳轉到第三方的時候清除此session
		}
		$pay_type = $this->Online_api_model->get_paytype_new($_SESSION['uid'],$status,0);
		if($pay_type['payid']>0){
			$_SESSION['pay'] = $pay_type;
			$this->add('bank_info',$this->Online_api_model->get_bankinfo($pay_type['paytype']));
		}else{
			//show_error('非常抱歉，在線支付暫時無法使用！請聯系客服!<a href="javascript:history.go(-1)">返回</a>', 200, '提示');
			echo "<script>alert('非常抱歉，沒有可用的第三方網銀支付！請聯系客服！');";
			echo "window.close();</script>";exit;
		}
		if($pay_type['paytype'] == 5){
			$this->add('time',date('Y-m-d H:i:s'));
			$this->add('payid',$_SESSION['pay']['payid']);
		}
		$this->add('site_id',SITEID);
		$this->add('username',$_SESSION['username']);
		$this->add('url',"/member/income/Index/common");
		$this->add('payset',$this->Online_api_model->get_payset($_SESSION['uid']));
		$this->add('info',$userinfo);
		$this->add('order_num',$this->Online_api_model->get_order_num());
		$this->add('userinfo',$userinfo);
		$this->add('deposit',$deposit_new);
		$this->add('copy_right',$this->Common_model->get_copyright());
		$this->display('web_public/member/bank/onlinebank.html');
	}
	//存款完成後跳轉頁面
	public function bank_online_card_index(){
		$this->add('data','bank_card_index');
		$this->display('web_public/member/bank/paid_index.html');
	}
	//存款完成後跳轉頁面
	public function bank_online_bank_index(){
		$this->add('data','bank_onlinebank_index');
		$this->display('web_public/member/bank/paid_index.html');
	}
	//存款完成後跳轉頁面
	public function bank_online_wechat_index(){
		$this->add('data','bank_onlinewechat_index');
		$this->display('web_public/member/bank/paid_index.html');
	}
	//第三方微信
	public function bank_onlinewechat_index(){
		if($_SESSION['shiwan'] == 1){
			echo "試玩賬號不能存取款，請註冊正式賬號！";exit;
		}
		$deposit = $this->DePosit;    // 獲取存款文案信息
		foreach($deposit as $k => $v){
			$deposit[$k]['content'] = $this->Cash_model->replacedomain($deposit[$k]['content']);
		}
		foreach ($deposit as $k => $v) {
			if($v['type'] == 2){
				$deposit_new = $v;
			}
		}
		$userinfo = $this->UserInfo;
		$level_info = $this->Common_model->get_user_level_info($userinfo['level_id']);
		//線上入款最小金額 最大金額
		$_SESSION['ol_catm_max'] = $level_info['ol_catm_max'];
		$_SESSION['ol_catm_min'] = $level_info['ol_catm_min'];
		if(empty($_SESSION['pay']['is_wechat'])){
			unset($_SESSION['pay']);
		}
		if($_SESSION['pay']['payid'] > 0){
			$status = $_SESSION['pay'];    //防止用戶壹直在銀行卡頁面刷新 而更換了支付方式   在成功跳轉到第三方的時候清除此session
		}
		$pay_type = $this->Online_api_model->get_paytype_new($_SESSION['uid'],$status,0,1);
		//p($pay_type);die;
		if($pay_type['payid']>0){
			$_SESSION['pay'] = $pay_type;
			$bank_info = $this->Online_api_model->get_wechat_code($pay_type['paytype']);
			$this->add('bank_info',$bank_info);
		}else{
			//show_error('非常抱歉，在線支付暫時無法使用！請聯系客服!<a href="javascript:history.go(-1)">返回</a>', 200, '提示');
			echo "<script>alert('非常抱歉，沒有可用的第三方微信支付！請聯系客服！');";
			echo "window.close();</script>";exit;
		}
		if($pay_type['paytype'] == 5){
			$this->add('time',date('Y-m-d H:i:s'));
			$this->add('payid',$_SESSION['pay']['payid']);
		}
		$this->add('site_id',$site_id);
		$this->add('username',$_SESSION['username']);
		$this->add('url',"/member/income/Index/common");
		$this->add('payset',$this->Online_api_model->get_payset($_SESSION['uid']));
		$this->add('info',$userinfo);
		$this->add('deposit',$deposit_new);
		$this->add('order_num',$this->Online_api_model->get_order_num());
		$this->add('userinfo',$userinfo);
		$this->display('web_public/member/bank/onlinewechat.html');
	}

	//三方點卡入款
	public function bank_card_index(){
		if($_SESSION['shiwan'] == 1){
			echo "試玩賬號不能存取款，請註冊正式賬號！";exit;
		}
		$deposit = $this->DePosit;    // 獲取存款文案信息
		foreach($deposit as $k => $v){
			$deposit[$k]['content'] = $this->Cash_model->replacedomain($deposit[$k]['content']);
		}
		foreach ($deposit as $k => $v) {
			if($v['type'] != 1&&$v['type'] != 2){
				$deposit_new = $v;
			}
		}
		$userinfo = $this->UserInfo;
		$level_info = $this->Common_model->get_user_level_info($userinfo['level_id']);
		//線上入款最小金額 最大金額
		$_SESSION['ol_catm_max'] = $level_info['ol_catm_max'];
		$_SESSION['ol_catm_min'] = $level_info['ol_catm_min'];
		if(empty($_SESSION['pay']['is_card'])){
			unset($_SESSION['pay']);
		}
		if($_SESSION['pay']['payid'] > 0){
			$status = $_SESSION['pay'];    //防止用戶壹直在銀行卡頁面刷新 而更換了支付方式   在成功跳轉到第三方的時候清除此session
		}
		$pay_type = $this->Online_api_model->get_paytype_new($_SESSION['uid'],$status,1);
		if($pay_type['payid']>0){
			$_SESSION['pay'] = $pay_type;
		}else{
			//show_error('非常抱歉，在線支付暫時無法使用！請聯系客服!<a href="javascript:history.go(-1)">返回</a>', 200, '提示');
			echo "<script>alert('非常抱歉，沒有可用的點卡支付！請聯系客服！');";
			echo "window.close();</script>";exit;
		}

		$this->add('deposit',$deposit_new);
		$this->add('site_id',SITEID);
		$this->add('payset',$this->Online_api_model->get_payset($_SESSION['uid']));
		$this->add('userinfo',$userinfo);
		$order_num = $this->Online_api_model->get_order_num();
		$this->add('order_num',$order_num);
		$this->add('paytype',$pay_type['paytype']);
		$this->add('bank_info',$this->Online_api_model->get_bankinfo($pay_type['paytype']));
		if($pay_type['paytype'] != 13){
			$this->add('url',"/member/income/Index/din_card");
			$this->display('web_public/member/bank/othercard.html');
		}else{
			$this->add('url',"/member/income/Index/card");
			$this->display('web_public/member/bank/card.html');
		}
	}

	//線上取款
	public function bank_onlineout_index(){
		$userinfo = $this->UserInfo;
		$this->add('userinfo',$userinfo);
		$this->display('web_public/member/bank/onlineout.html');
	}

	public function show(){
		if($_SESSION['shiwan'] == 1){
			echo "試玩賬號不能存取款，請註冊正式賬號！";exit;
		}

		//判斷用戶是否綁定銀行卡
		$userinfo = $this->UserInfo;
		if(!empty($userinfo['pay_name']) && !empty($userinfo['pay_card']) && !empty($userinfo['pay_num'])){
			$type = '1';
		}else{
			$type = '0';
		}
		//$type = $this->input->get("type");
		if(empty($userinfo['pay_num'])){
			if($type!="1"){
				echo "<script>alert('為方便能及時申請出款到賬，請在出款前設置您的銀行卡等信息，請到《取款銀行》如實填寫！');window.location.href='../../../index.php/index/new_member?url=2';</script>";exit;
			}
			//$this->hk_money($this->router->class,$this->router->method);
		}

		//出款判斷
		$status = $this->Bank_model->out_cash_record();
		if(!empty($status['order_num'])){
			echo '您有訂單號：'.$status['order_num'].',出款尚未完成.請勿頻繁出款';
			exit();
		}
		$this->load->model('member/new/Audit_model');
		$type = 'ag,og,mg,ct,bbin,lebo';
		$end_date = date('Y-m-d H:i:s');

		$pay_data = $this->Common_model->get_user_level_info($_SESSION['uid']);
		$audit_data = $this->Audit_model->get_user_audit($_SESSION['uid'],$pay_data,$_SESSION['username'],$type,$end_date);

		$count_dis = $audit_data['count_dis'];
		$count_xz = $audit_data['count_xz'];

		$out_data = array();
		$out_data['out_fee'] = $audit_data['out_fee'];//出款手續費
		$out_data['out_audit'] = $count_dis + $count_xz;//稽核扣除費用
		$out_data['fav_num'] = $count_dis;

		//輸出json
		$adata = array();
		$adata['count_dis'] = $audit_data['count_dis'];
		$adata['count_xz'] = $audit_data['count_xz'];
		$adata['out_fee'] = $out_data['out_fee'];//出款手續費
		$adata['out_audit'] = $out_data['out_audit'];//稽核扣除費用
		$adata['fav_num'] = $out_data['fav_num'];
		$adata['bet_all'] = $audit_data['bet_all'];

		unset($_SESSION['out_money']);
		$_SESSION['out_money'] = $out_data;

		unset($audit_data['bet_all']);
		unset($audit_data['count_dis']);
		unset($audit_data['count_xz']);
		unset($audit_data['out_fee']);
		$total = $count_dis + $count_xz + $out_data['out_fee'];
		$total = sprintf("%.2f", $total);

		if (!empty($audit_data)) {
			$userAudit = array();
			foreach ($audit_data as $key => $v){
				//當前取款稽核相關信息
				$ak = $v['id'];
				$userAudit[$ak] =  array(
					'id'=>$v['id'],
					'is_pass_zh'=>$v['is_pass_zh'],
					'is_pass_ct'=>$v['is_pass_ct'],
					'is_expenese_num'=>$v['is_expenese_num'],
					'deduction_e'=>$v['deduction_e'],
					'cathectic_sport'=>$v['cathectic_sport'],
					'cathectic_fc'=>$v['cathectic_fc'],
					'cathectic_video'=>$v['cathectic_video']
				);

				$audit_data[$key]['is_pass_zh'] = $this->zh_state($v['is_pass_zh']);
				$audit_data[$key]['is_pass_ct'] = $this->ct_state($v['is_pass_ct']);
				$audit_data[$key]['is_expenese_num'] = $this->xz_state($v['is_expenese_num']);
				$audit_data[$key]['deduction_e'] = sprintf("%.2f",$v['deduction_e']);
			}
			unset($_SESSION['userAudit']);
			$_SESSION['userAudit'] = $userAudit;
		}

		$count_xz = sprintf("%.2f", $count_xz);
		$count_dis = sprintf("%.2f", $count_dis);

		//輸出json
		$adata['total'] = $total;
		$adata['count_dis'] = $count_dis;
		$adata['count_xz'] = $count_xz;
		$adata['ndate'] = $end_date;
		//p($audit_data);die;
		$this->add('end_date',$end_date);
		$this->add('pay_data',$pay_data);
		$this->add('audit_data',$audit_data);
		$this->add('adata',$adata);
		$this->display('web_public/member/bank/show.html');
	}
	function GetOrderNum() {
		$json = '';
		$json = date("YmdHis") . mt_rand(1000, 9999); //訂單號
		echo $json;
	}

	//常態稽核狀態返回
	function ct_state($ct){
		switch ($ct) {
			case '0':
				return "<font color=\"#ff0000\">未通過</font>";
				break;
			case '1':
				return "<font color=\"#00cc00\">通過</font>";
				break;
			case '2':
				return "-";
				break;
		}
	}

	//扣除行政費用狀態
	function xz_state($xz){
		switch ($xz) {
			case '0':
				return "<font color=\"#ff0000\">否</font>";
				break;
			case '1':
				return "<font color=\"#00cc00\">是</font>";
				break;
			case '2':
				return "不需要稽核";
				break;
		}
	}

	//綜合稽核狀態返回
	function zh_state($st){
		switch ($st) {
			case '0':
				return "<font color=\"#ff0000\">否</font>";
				break;
			case '1':
				return "<font color=\"#00cc00\">是</font>";
				break;
			case '2':
				return "不需要稽核";
				break;
		}
	}


	public function out_money(){
		if($_SESSION['shiwan'] == 1){
			echo "試玩賬號不能存取款，請註冊正式賬號！";exit;
		}
		if(empty($_SESSION['out_money'])){
			echo "參數錯誤，請重試！";
			exit();
		}
		//扣除費用
		$out_data = array();
		$out_data = $_SESSION['out_money'];
		$userinfo = $this->UserInfo;
		$pay_data = $this->Common_model->get_user_level_info($_SESSION['uid']);
		//p($pay_data);die;
		$userinfo['pay_card'] = $this->Common_model->bank_type($userinfo['pay_card']);
		$this->add('userinfo',$userinfo);
		$this->add('pay_data',$pay_data);
		$this->add('out_data',$out_data);
		$this->add('copyright',$this->Common_model->get_copyright());
		$this->display('web_public/member/bank/out_money.html');
	}


	//出款數據處理
	public function getmoneydo(){
		if($_SESSION['shiwan'] == 1){
			echo "<script>alert('試玩賬號不能存取款，請註冊正式賬號！');window.location.href='../../../index/new_member?url=2';</script>";exit;
		}
		if(empty($_SESSION['out_money'])){
			echo "<script>alert('參數錯誤，請重試！');window.location.href='../../../index/new_member?url=2';</script>";exit;
		}
		//出款判斷
		$status = $this->Bank_model->out_cash_record();
		if(!empty($status['order_num'])){
			echo "<script>alert('您有訂單號：'".$status['order_num']."',出款尚未完成.請勿頻繁出款');window.location.href='../../../index/new_member?url=2';</script>";exit;
		}
		$qk_pwd = $this->input->post('qk_pwd');
		$uu_out = $this->input->post('uu_out');
		$cash   = $this->input->post('cash');  //提款金額
		if(!empty($qk_pwd) && $uu_out == 'oucd'){
			$userinfo = $this->UserInfo;
			if($userinfo['qk_pwd'] != $qk_pwd){
				echo "<script>alert('取款密碼錯誤，請返回重新操作，重新計算稽核！');window.location.href='../../../index/new_member?url=2';</script>";exit;
			}
		}
		$pay_value = doubleval($cash);//提款金額
		$pay_data  =  $this->Common_model->get_user_level_info($_SESSION['uid']);
		if(($pay_value<0)||($pay_value>$userinfo["money"])||($pay_value>$pay_data['ol_atm_max'])||($pay_value<$pay_data['ol_atm_min'])){
			echo "<script>alert('提款金額錯誤，請重新輸入。');window.location.href='../../../index/new_member?url=2';</script>";exit;
		}
		//判斷是否首次出款
		$outward_style=1;
		$is_first = $this->Bank_model->is_first();
		if($is_first){
			$outward_style=0;//不是首次出款
		}else{
			$outward_style=1;
		}
		if(empty($_SESSION['agent_id']) || empty($_SESSION['username'])){
			echo "<script>alert('參數錯誤，請重試！');window.location.href='../../../index/new_member?url=2';</script>";exit;
		}
		//獲取代理商賬號
		$agent_user = $this->Bank_model->get_agent_user();
		//扣除費用
		$out_data = array();
		$out_data = $_SESSION['out_money'];
		//是否扣除優惠
		if (!empty($out_data['fav_num'])) {
			$is_fav = 1;
		}else{
			$is_fav = 0;
		}
		//判斷提出額度是否大於扣除
		$tmpUY = $pay_value - $out_data['out_audit'] - $out_data['out_fee'];
		if ($tmpUY < 0) {
			echo "<script>alert('減去扣除額度後提款額度小於0，請重新提交出款額度。');window.location.href='../../../index/new_member?url=2';</script>";
			exit();
		}
		$order_num=date("YmdHis").mt_rand(1000,9999);//訂單號
		$this->db->trans_begin();
		$this->db->where('uid',$_SESSION['uid']);
		$this->db->set('money','money-'.$pay_value,FALSE);
		$this->db->update('k_user');
		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			echo "<script>alert('交易失敗,錯誤代碼cw0001');window.location.href='../../../index/new_member?url=2';</script>";exit;
		}

		//寫入出款記錄
		//獲取用戶當前余額
		$umban = $this->Bank_model->now_money();
		if($umban['money'] < 0){
			$this->db->trans_rollback();
			echo "<script>alert('交易失敗,錯誤代碼cw0002');window.location.href='../../../index/new_member?url=2';</script>";exit;
		}
		$data_o = array();
		$data_o['site_id'] = SITEID;
		$data_o['uid'] = $_SESSION['uid'];
		$data_o['index_id'] = INDEX_ID;
		$data_o['agent_user'] = $agent_user['agent_user'];
		$data_o['agent_id'] = $_SESSION['agent_id'];
		$data_o['username'] = $_SESSION['username'];
		$data_o['level_id'] = $userinfo['level_id'];
		$data_o['audit_id'] = '';
		$data_o['balance'] = $umban['money'];
		$data_o['do_url'] =  $_SERVER["HTTP_HOST"];//提交網址
		$data_o['order_num'] = $order_num;
		$data_o['out_time'] = date('Y-m-d H:i:s');
		$data_o['outward_style'] = $outward_style;//是否首次出款
		$data_o['outward_num'] = $pay_value;//提交額度
		$data_o['charge'] = $out_data['out_fee'];//手續費
		$data_o['favourable_num'] = $out_data['fav_num'];//優惠金額
		$data_o['expenese_num'] = ($out_data['out_audit'] - $out_data['fav_num']);//行政費用扣除
		$data_o['outward_money'] = ($pay_value - $out_data['out_audit'] - $out_data['out_fee']);//實際出款額度
		$data_o['favourable_out'] = $is_fav;//是否扣除優惠
		$this->db->insert('k_user_bank_out_record',$data_o);
		$log_2 = $this->db->insert_id();
		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			echo "<script>alert('交易失敗,錯誤代碼cw0003');window.location.href='../../../index/new_member?url=2';</script>";exit;
		}

		//寫入現金系統
		$dataC = array();
		$dataC['uid'] = $_SESSION['uid'];
		$dataC['username'] = $_SESSION['username'];
		$dataC['site_id'] = SITEID;
		$dataC['index_id'] = INDEX_ID;
		$dataC['agent_id'] = $_SESSION['agent_id'];
		$dataC['cash_balance'] = $umban['money'];
		$dataC['source_id'] = $log_2;
		$dataC['cash_type'] = 19;//線上取款
		$dataC['cash_do_type'] = 2;
		$dataC['source_type'] = 4;//線上取款類型
		$dataC['cash_num'] = $pay_value;
		$dataC['cash_date'] = date('Y-m-d H:i:s');
		$dataC['remark'] = $order_num;
		$this->db->insert('k_user_cash_record',$dataC);
		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			echo "<script>alert('交易失敗,錯誤代碼cw0004');window.location.href='../../../index/new_member?url=2';</script>";exit;
		}else{
			$this->db->trans_commit();
			//刪除監控緩存key
			$redis = RedisConPool::getInstace();
			$redis_key = SITEID.'_bankout_monitor';
			$redis->delete($redis_key);
			unset($_SESSION['out_money']);
			echo "<script>alert('提款申請已經提交，系統正在受理！');window.location.href='../../../index/new_member?url=2';</script>";exit;
		}
	}






	//綁定銀行卡
	public function hk_money($class = "bank",$method = "show"){
		$userinfo = $this->UserInfo;
		$bank_arr = $this->Bank_model->get_bank_arr();//獲取銀行列表
		$this->add('userinfo',$userinfo);
		$this->add('copyright',$this->Common_model->get_copyright());
		$this->add('class',$this->router->class);
		$this->add('method',$this->router->method);
		$this->add('bank_arr',$bank_arr);
		$this->display('web_public/member/bank/hk_money.html');

	}
	//綁定銀行卡處理
	public function hk_money_do(){
		$into       = $this->input->post('into');
		$class      = $this->input->post('class');
		//$method     = $this->input->post('method');
		$province   = $this->input->post('province');
		$city       = $this->input->post('city');
		$pay_card   = $this->input->post('pay_card');
		$pay_num    = $this->input->post('pay_num');

		$data = array();
		$data["pay_address"] = $province."-".$city;
		$data["pay_card"]    = $pay_card;
		$data['pay_num']     = $pay_num;
		if(preg_match("/([`~!@#$%^&*()_+<>?:\"{},\/;'[\]·~！#￥%……&*（）——+《》？：“{}，。\、；’‘【\】])/",$data["pay_num"])){
			echo "<script>alert('銀行卡號格式錯誤！');window.location.href='../../../index/new_member?url=2';</script>";exit;
		}
		if($into == 'true'){
			$this->db->from('k_user_reg_config');
			$this->db->where('site_id',SITEID);
			$this->db->where('index_id',INDEX_ID);
			$this->db->select('is_banknum');
			$is_banknum = $this->db->get()->row_array();
			if(intval($is_banknum['is_banknum'])===0){
				$this->db->from('k_user');
				$this->db->where('site_id',SITEID);
				$this->db->where('index_id',INDEX_ID);
				$this->db->where('pay_num',$data['pay_num']);
				$result = $this->db->get()->row_array();
				if($result['pay_num']){
					//message('該銀行卡已經綁定到其他會員！',$method);
					echo "<script>alert('該銀行卡已經綁定到其他會員！');window.location.href='../../../index/new_member?url=2';</script>";exit;
				}
			}
			$this->db->where('uid',$_SESSION['uid']);
			$this->db->update('k_user',$data);
			if($this->db->affected_rows()){
				echo "<script>alert('資料修改成功!');window.location.href='../../../index/new_member?url=2';</script>";exit;
			}else{
				//message('對不起，由於網絡堵塞原因。\\n您提交的匯款信息失敗，請您重新提交。',$method);
				echo "<script>alert('對不起，由於網絡堵塞原因。\\n您提交的匯款信息失敗，請您重新提交。');window.location.href='../../../index/new_member?url=2';</script>";exit;

			}
		}

	}





}