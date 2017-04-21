<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cash extends MY_Controller {

	private $ewms = '';
	public function __construct() {
		parent::__construct();
		$this->load->model('member/Cash_model');
		$this->load->model('Common_model');
		$this->Common_model->login_check($_SESSION['uid']);
		$this->ewms= $this->Cash_model->get_setmoney_qr(); //獲取所有二維碼
		if (!empty($this->ewms)) {
			$this->add('ewm',1);
		}
	}

	//銀行交易 線上存款
	public function setmoney(){
		$deposit = $this->Cash_model->get_setmoney();    // 獲取存款文案信息
		foreach($deposit as $k => $v){
			$deposit[$k]['content'] = $this->Cash_model->replacedomain($deposit[$k]['content']);

		}

		$this->add('deposit',$deposit);
		$this->add('shiwan',$_SESSION['shiwan']);
		$this->display('member/setmoney.html');
	}

	//掃碼存款 1-微信 2-支付寶
	public function setmoney_qr(){
		$type = $this->input->get("type"); //二維碼類型
		$qrs = $this->ewms; //獲取所有二維碼
		if (empty($type)) {
			$type = $qrs[0]['type'];
		}

		$qr = $this->Cash_model->get_setmoney_qr($type); // 獲取存款二維碼
		$order=date("YmdHis").mt_rand(1000,9999);//訂單號
		$_SESSION['order'] = $order; //訂單號存入session

		$this->add('qr',$qr);
		$this->add('qrs',$qrs);
		$this->add('order',$order);
		$this->add('card_msg', $this->Cash_model->get_bank_in_one($type));

		$this->add('type',$type);
		$this->add('shiwan',$_SESSION['shiwan']);
		$this->display('member/setmoney_qr.html');
	}

	//額度轉換
	public function zr_money(){
		$istransf = $this->Cash_model->get_istransf();
		if ($istransf['is_transf'] == 0 && SITEID == 't') {
			echo "<script>";
			echo 'alert("額度轉換已被禁用，如有疑問請聯系在線客服！");window.history.go(-1)';
			echo "</script>";
			exit();
		}
		$userinfo = $this->Common_model->get_user_info($_SESSION['uid']);
		//獲取額度轉換最低限額
		//$MinInMoney = 10;
		$MinInMoney = $this->Cash_model->get_transf_min($userinfo['level_id']);
		if ($userinfo['shiwan'] == 1) {
			echo "<script>";
			echo 'alert("試玩賬號不能存取款，請註冊正式賬號！");window.history.go(-1)';
			echo "</script>";
			exit();
		}

		//視訊配置
		//$video_config = $this->Cash_model->get_video_config();
		$video_config = $this->Common_model->get_video_dz_config();
		$allmoney = $userinfo['money'];
		foreach ($video_config as $key => $val) {
			if ($val == 'eg') {
				unset($video_config[$key]);
			}else{
				$allmoney += $userinfo[$val.'_money'];
			}
		}
		$_SESSION['edcashtoken'] = md5(round(00000000,55555555));
		$allmoney = number_format($allmoney, '2');
		$this->add('video_config',$video_config);
		$this->add('userinfo',$userinfo);
		$this->add('MinInMoney',$MinInMoney);
		$this->add('allmoney',$allmoney);
		$this->add('edcashtoken',$_SESSION['edcashtoken']);
		$this->display('member/zr_money.html');
	}


	//額度轉換的數據處理
	public function edzh(){
		$istransf = $this->Cash_model->get_istransf();
		if ($istransf['is_transf'] == 0 && SITEID == 't') {
			echo json_encode(array("status" => 24, "info" => "額度轉換已被停用，如有疑問請聯系客服！"));
			die();
		}
		if(empty($_SESSION['edcashtoken']) || $_SESSION['edcashtoken'] != $_POST['edcashtoken']){
			echo json_encode(array("status" => 22, "info" => "請刷新頁面，重試！"));
			die();
		}
		unset($_SESSION['edcashtoken']);
		//if(SITEID == 't' &&  $_SESSION['username'] != 'pkaaanb') {
		$credit_bak = floatval($this->input->post('p3_Amt'));
		if(SITEID == 't' &&  $_SESSION['username'] != 'pkaaanb') {
			echo json_encode(array("status" => 21, "info" => "測試站點，不允許額度轉換"));
			die();
		}

		if ($_SESSION['shiwan'] == 1) {
			echo json_encode(array("status" => 20, "info" => "試玩賬號不能存取款，請註冊正式賬號！"));exit;
		}
		$uid = @$_SESSION['uid'];
		$username = @$_SESSION['username'];
		$userinfo = $this->Common_model->get_user_info($uid);

		//$cash_record = $this->Cash_model->get_cash_record($userinfo['uid']);  //獲取金額交易記錄
		if(isset($_SESSION['edzhtime'])){
			$time = time() - $_SESSION['edzhtime'];
			if ($time < 60) {
				echo json_encode(array("status" => 17, "info" => "請在" . (60 - $time) . "秒後操作"));exit;
			}
		}
		/*if ($cash_record && !empty($cash_record['cash_date'])) {
                $time = time() - strtotime($cash_record['cash_date']);
                if ($time < 60) {
                    echo json_encode(array("status" => 17, "info" => "請在" . (60 - $time) . "秒後操作"));exit;
                }
        }*/

		$copyright = $this->Common_model->get_copyright();
		$list = explode(',',$copyright['video_module']);
		$g_type_arr = array('sport');
		$g_type_arr = array_merge($g_type_arr,$list);


		$trtype1 = trim($this->input->post('trtype1'));
		$trtype2 = trim($this->input->post('trtype2'));
		if($trtype1 == $trtype2){
			echo json_encode(array("status" => 19, "info" => "轉入轉出平臺不能相同，請重新選擇" ));exit;
		}
		//$list = array('ag','og','mg','ct','bbin','lebo');
		foreach($list as $val){
			if($val == 'pt'){
				$strval = 'pt_game';
			}else{
				$strval = $val;
			}
			if($this->GetSiteStatus($this->SiteStatus,2,$strval,1)){
				if($trtype1 == $val || $trtype2 == $val){
					$str  = $val."遊戲正在進行維護中！\n請您選擇其他遊戲！祝您遊戲開心！";
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

	//線上取款
	public function getmoney(){
		$userinfo = $this->Common_model->get_user_info($_SESSION['uid']);
		$this->add('userinfo',$userinfo);
		$this->display('member/getmoney.html');
	}


	//綁定銀行卡
	public function hk_money($class,$method){
		$userinfo = $this->Common_model->get_user_info($_SESSION['uid']);
		$bank_arr = $this->Cash_model->get_bank_arr();//獲取銀行列表
		$this->add('userinfo',$userinfo);
		$this->add('copyright',$this->Common_model->get_copyright());
		$this->add('class',$this->router->class);
		$this->add('method',$this->router->method);
		$this->add('bank_arr',$bank_arr);
		$this->display('member/hk_money.html');
		exit;
	}
	//綁定銀行卡處理
	public function hk_money_do(){
		$into       = $this->input->post('into');
		$class      = $this->input->post('class');
		$method     = $this->input->post('method');
		$province   = $this->input->post('province');
		$city       = $this->input->post('city');
		$pay_card   = $this->input->post('pay_card');
		$pay_num    = $this->input->post('pay_num');

		$data = array();
		$data["pay_address"] = $province."-".$city;
		$data["pay_card"]    = $pay_card;
		$data['pay_num']     = $pay_num;
		if(preg_match("/([`~!@#$%^&*()_+<>?:\"{},\/;'[\]·~！#￥%……&*（）——+《》？：“{}，。\、；’‘【\】])/",$data["pay_num"])){
			message('銀行卡號格式錯誤！',$method);
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
					message('該銀行卡已經綁定到其他會員！',$method);
				}
			}
			$this->db->where('uid',$_SESSION['uid']);
			$this->db->update('k_user',$data);
			if($this->db->affected_rows()){
				echo '<script>alert("資料修改成功!")</script>';
				echo '<script>top.location.href="'.$method.'";</script>';exit;
			}else{
				message('對不起，由於網絡堵塞原因。\\n您提交的匯款信息失敗，請您重新提交。',$method);
			}
		}

	}

	public function show(){
		if($_SESSION['shiwan'] == 1 ){
			echo "試玩賬號不能存取款，請註冊正式賬號！";exit;
		}
		if(SITEID == 't'){
			echo "演示站禁止出入款！";exit;
		}
		//判斷用戶是否綁定銀行卡
		$userinfo = $this->Common_model->get_user_info($_SESSION['uid']);
		$type = $this->input->get("type");
		if(empty($userinfo['pay_num'])){
			if($type!="1"){
				echo "<script>alert('為方便能及時申請出款到賬，請在出款前設置您的銀行卡等信息，請如實填寫！');</script>";
			}
			$this->hk_money($this->router->class,$this->router->method);
		}
		//$this->load->model('member/Audit_model');
		//出款判斷
		$status = $this->Cash_model->out_cash_record();
		if(!empty($status['order_num'])){
			echo '您有訂單號：'.$status['order_num'].',出款尚未完成.請勿頻繁出款';
			exit();
		}
		$this->add('copyright',$this->Common_model->get_copyright());
		$this->display('member/show.html');
	}

	//取款數據請求
	public function show_data(){
		//判斷是否ajax
		if(!$this->input->is_ajax_request()){
			exit('error');
		}
		$this->load->model('member/Audit_model');
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

		//組合表格數據
		$audithtml = "";
		if ($audit_data) {
			foreach ($audit_data as $key => $v) {
				$audithtml .= "<tr class=\"m_cen\">
				<td style=\"width:160px;\">起始:".$v['begin_date']."</td>
		        <td rowspan=\"2\">".$v['deposit_money']."</td>
		        <td rowspan=\"2\">".($v['atm_give']+$v['catm_give'])."</td>
		        <td class=\"hide1\" rowspan=\"2\" style=\"display: none;\">".$v['cathectic_sport']."</td>
		        <td class=\"hide1\" rowspan=\"2\" style=\"display: none;\">".($v['cathectic_fc']+0)."</td>
		        <td class=\"hide1\" rowspan=\"2\" style=\"display: none;\">".($v['cathectic_video']+0)."</td>
		        <td class=\"hide2\" rowspan=\"2\" style=\"display: none;\">".$v['cathectic_sport']."</td>
		        <td class=\"hide2\" rowspan=\"2\" style=\"display: none;\">-</td>
		        <td class=\"hide2\" rowspan=\"2\" style=\"display: none;\">".($v['cathectic_fc']+0)."</td>
		        <td class=\"hide2\" rowspan=\"2\" style=\"display: none;\">-</td>
		        <td class=\"hide2\" rowspan=\"2\" style=\"display: none;\">".($v['video_audit']+0).'</td>
		        <td class="hide2" rowspan="2" style="display: none;">-</td>
		        <td class="hide2" rowspan="2" style="display: none;">'.$v['type_code_all'].'</td>
		        <td class="hide2" rowspan="2" style="display: none;">'.$v['is_pass_zh'].'</td>
		        <td rowspan="2">'.$v['normalcy_code'].'</td>
		        <td rowspan="2">'.$v['relax_limit'].'</td>
		        <td rowspan="2">'.$v['is_pass_ct'].'</td>
		        <td rowspan="2">'.$v['is_expenese_num'].'</td>';
				if ($v['deduction_e'] > 0) {
					$audithtml .= '<td rowspan="2" style="color:red">'.$v['deduction_e'].'</td>';
				}else{
					$audithtml .= '<td rowspan="2">'.$v['deduction_e'].'</td>';
				}
				$audithtml .= '</tr><tr class="m_cen"><td>結束:'.$v['end_date'].'</td></tr>';
			}
		}

		$adata['audithtml'] = $audithtml;
		echo json_encode($adata);
	}

	public function get_money_1(){
		if($_SESSION['shiwan'] == 1){
			echo "試玩賬號不能存取款，請註冊正式賬號！";exit;
		}
		if(SITEID == 't'){
			echo "演示站禁止存取款！";exit;
		}
		if(empty($_SESSION['out_money'])){
			echo "參數錯誤，請重試！";
			exit();
		}
		//扣除費用
		$out_data = array();
		$out_data = $_SESSION['out_money'];
		$userinfo = $this->Common_model->get_user_info($_SESSION['uid']);
		$pay_data = $this->Common_model->get_user_level_info($_SESSION['uid']);
		$userinfo['pay_card'] = $this->Common_model->bank_type($userinfo['pay_card']);
		$this->add('userinfo',$userinfo);
		$this->add('pay_data',$pay_data);
		$this->add('out_data',$out_data);
		$this->add('copyright',$this->Common_model->get_copyright());
		$this->display('member/get_money_1.html');
	}

	public function edit_pass(){
		if($_SESSION['shiwan'] == 1){
			echo "試玩賬號不能修改密碼，請註冊正式賬號！";exit;
		}
		$ok = $this->input->post('OK');
		if(!empty($ok)){
			$userinfo = $this->Common_model->get_user_info($_SESSION['uid']);
			$oldpw=$this->input->post('oldPW1').$this->input->post('oldPW2').$this->input->post('oldPW3').$this->input->post('oldPW4');
			$newpw=$this->input->post('newPW1').$this->input->post('newPW2').$this->input->post('newPW3').$this->input->post('newPW4');
			if(strlen($newpw) != 4){
				exit("<script>alert('密碼格式不正確！');history.go(-1);</script>");
			}
			if($userinfo['qk_pwd']!=$oldpw){
				exit("<script>alert('舊取款密碼不正確！');history.go(-1);</script>");
			}else{
				$data1 = $this->Cash_model->edit_pass($newpw);
				if($data1){
					echo "<script>alert('修改取款密碼成功');window.close();</script>";
				}else{
					echo "<script>alert('修改取款密碼失敗！');history.go(-1);</script>";
				}
			}
		}
		$this->display('member/edit_pass.html');
	}
	//出款數據處理
	public function getmoneydo(){
		if($_SESSION['shiwan'] == 1){
			echo "試玩賬號不能存取款，請註冊正式賬號！";exit;
		}
		if(empty($_SESSION['out_money'])){
			echo "參數錯誤，請重試！";
			exit();
		}
		//出款判斷
		$status = $this->Cash_model->out_cash_record();
		if(!empty($status['order_num'])){
			echo '您有訂單號：'.$status['order_num'].',出款尚未完成.請勿頻繁出款';
			exit();
		}
		$qk_pwd = $this->input->post('qk_pwd');
		$uu_out = $this->input->post('uu_out');
		$cash   = $this->input->post('cash');  //提款金額
		if(!empty($qk_pwd) && $uu_out == 'oucd'){
			$userinfo = $this->Common_model->get_user_info($_SESSION['uid']);
			if($userinfo['qk_pwd'] != $qk_pwd){
				echo "取款密碼錯誤，請重新輸入。";
				exit();
			}
		}
		$pay_value = doubleval($cash);//提款金額
		$pay_data  =  $this->Common_model->get_user_level_info($_SESSION['uid']);
		if(($pay_value<0)||($pay_value>$userinfo["money"])||($pay_value>$pay_data['ol_atm_max'])||($pay_value<$pay_data['ol_atm_min'])){
			echo "提款金額錯誤，請重新輸入。";
			exit();
		}
		//判斷是否首次出款
		$outward_style=1;
		$is_first = $this->Cash_model->is_first();
		if($is_first){
			$outward_style=0;//不是首次出款
		}else{
			$outward_style=1;
		}
		if(empty($_SESSION['agent_id']) || empty($_SESSION['username'])){
			echo "<script>alert('參數錯誤，請重試！');window.close();</script>";exit;
		}
		//獲取代理商賬號
		$agent_user = $this->Cash_model->get_agent_user();
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
			echo "減去扣除額度後提款額度小於0，請重新提交出款額度。";
			exit();
		}
		$order_num=date("YmdHis").mt_rand(1000,9999);//訂單號
		$this->db->trans_begin();
		$this->db->where('uid',$_SESSION['uid']);
		$this->db->set('money','money-'.$pay_value,FALSE);
		$this->db->update('k_user');
		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			echo "<script>alert('交易失敗,錯誤代碼cw0001');window.close();</script>";exit;
		}

		//寫入出款記錄
		//獲取用戶當前余額
		$umban = $this->Cash_model->now_money();
		if($umban['money'] < 0){
			$this->db->trans_rollback();
			echo "<script>alert('交易失敗,錯誤代碼cw0002');window.close();</script>";exit;
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
			echo "<script>alert('交易失敗,錯誤代碼cw0003');window.close();</script>";exit;
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
			echo "<script>alert('交易失敗,錯誤代碼cw0004');window.close();</script>";exit;
		}else{
			$this->db->trans_commit();
			//刪除監控緩存key
			$redis = RedisConPool::getInstace();
			$redis_key = SITEID.'_bankout_monitor';
			$redis->delete($redis_key);
			unset($_SESSION['out_money']);
			echo "<script>alert('提款申請已經提交，系統正在受理！');window.close();</script>";exit;
		}
	}

	//公司入款
	public function bank(){
		if($_SESSION['shiwan'] == 1){
			echo "試玩賬號不能存取款，請註冊正式賬號！";exit;
		}
		if(SITEID == 't'){
			echo "演示站不能存取款！";exit;
		}
		//判斷用戶是否綁定銀行卡
		$userinfo = $this->Common_model->get_user_info($_SESSION['uid']);
		/*if(empty($userinfo['pay_num'])){
			echo "<script>alert('為方便後續能及時申請出款到賬，請在入款前設置您的銀行卡等信息，請如實填寫！');</script>";
    		$this->hk_money($this->router->class,$this->router->method);
		}*/
		$bank_arr = $this->Cash_model->get_bank_arr();//獲取銀行列表
		//獲取已剔除的銀行卡
		$bank_del = $this->Cash_model->get_bank_reject();
		//入款銀行卡
		$banks = $this->Cash_model->get_bank_in_arr($_SESSION['level_id']);//獲取入款銀行列表
		if(empty($banks)){
			echo "<script>alert('沒有可用的銀行卡，請聯系客服！');";
			echo "window.close();</script>";exit;
		}else{
			foreach ($bank_arr as $key => $value) {
				if(in_array($value['id'], $bank_del)){
					unset($bank_arr[$key]);
				}
			}
		}
		$copyright = $this->Common_model->get_copyright();
		$levelinfo = $this->Common_model->get_user_level_info($_SESSION['uid']);
		$order=date("YmdHis").mt_rand(1000,9999);//訂單號
		$this->add('bank_arr',$bank_arr);
		$this->add('banks',$banks);
		$this->add('order',$order);
		$this->add('levelinfo',$levelinfo);
		$this->add('copyright',$copyright);
		$this->display('member/bank.html');
	}
	//公司入款流程解說
	public function pay_explain(){
		$this->display('member/pay_explain.html');
	}

	//公司入款處理
	public function bank_ajax(){
		if($_SESSION['shiwan'] == 1){
			echo $this->Common_model->JSON(array('statu'=>'4'));exit();
		}
		$deposit_num    = $this->input->post('deposit_num');//入款金額
		$bank_style     = $this->input->post('bank_style'); //會員使用的銀行
		$order_num      = $this->input->post('order_num');  //訂單號
		$deposit_way    = $this->input->post('deposit_way');//存款方式
		$in_name        = $this->input->post('in_name'); //存款人姓名
		$in_date        = $this->input->post('in_date'); //存入時間

		$in_atm_address = $this->input->post('bank_location1').$this->input->post('bank_location2').$this->input->post('bank_location4');

		$action         = $this->input->post('action');

		if(!empty($action) && $action == 'add_form'){

			$bid = $this->input->post('bid');
			if(!empty($bid)){
				$bank = $this->Cash_model->get_bank();   //獲取銀行官網
			}

			$user = $this->Common_model->get_user_info($_SESSION['uid']);

			$agent = $this->Cash_model->get_agent_user();   //獲取代理商帳號
			//查詢是不是首次入款
			$user_record = $this->Cash_model->is_first_in();
			$levelinfo = $this->Common_model->get_user_level_info($_SESSION['uid']);

			if($deposit_num > $levelinfo['line_catm_max']){
				echo $this->Common_model->JSON(array('statu'=>'1','infos'=>$levelinfo['line_catm_max']));exit();
			}

			if($deposit_num < $levelinfo['line_catm_min']){
				echo $this->Common_model->JSON(array('statu'=>'2','infos'=>$levelinfo['line_catm_min']));exit();
			}

			//防止用戶惡意提交表單
			$result = $this->Cash_model->get_order_num($order_num);
			if(!empty($result['order_num'])){
				echo $this->Common_model->JSON(array('statu'=>'3'));exit();
			}

			$level_des = $this->Cash_model->get_level_des();   //獲取層級

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

}
