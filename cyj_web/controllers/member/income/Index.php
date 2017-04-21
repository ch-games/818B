<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Index extends MY_Controller {
	public function __construct()
	{
		parent::__construct();
		$this->load->model('member/pay/Online_api_model');
		$this->load->model('Common_model');
		$this->Online_api_model->login_check($_SESSION['uid']);
	}
	/*
	**1   新生
	**2   易寶
	**3   環迅
	**4   幣付寶
	**5   通匯卡
	**6   寶付
	**7   智付
	**8   匯潮
	**9   國付寶
	**10  融寶
	**11  快捷通
	**12  新環迅
	**13  易寶點卡
	**14  智付點卡
	**15  智付rsa
	**16  幣幣支付
	**17  盈寶支付
	**18  摩寶支付
	**/

	public function index(){
		if(SITEID == 't'){
			echo "演示站不能存取款！";exit;
		}
		//判斷用戶是否綁定銀行卡
		$userinfo = $this->Common_model->get_user_info($_SESSION['uid']);
		/*if(empty($userinfo['pay_num'])){
			echo "<script>alert('為方便後續能及時申請出款到賬，請在入款前設置您的銀行卡等信息，請如實填寫！');</script>";
    		$this->hk_money($this->router->class,$this->router->method);
		}*/
		$level_info = $this->Common_model->get_user_level_info($userinfo['level_id']);
		//線上入款最小金額 最大金額
		$_SESSION['ol_catm_max'] = $level_info['ol_catm_max'];
		$_SESSION['ol_catm_min'] = $level_info['ol_catm_min'];
		if($_SESSION['pay']['is_card'] == 1){
			unset($_SESSION['pay']);
		}
		if($_SESSION['pay']['payid'] > 0){
			$status = $_SESSION['pay'];    //防止用戶壹直在銀行卡頁面刷新 而更換了支付方式   在成功跳轉到第三方的時候清除此session
		}
		$pay_type = $this->Online_api_model->get_paytype($_SESSION['uid'],$status,0);
		if($pay_type['payid']>0){
			$_SESSION['pay'] = $pay_type;
			$this->add('bank_info',$this->Online_api_model->get_bankinfo($pay_type['paytype']));
		}else{
			show_error('非常抱歉，在線支付暫時無法使用！請聯系客服!<a href="javascript:history.go(-1)">返回</a>', 200, '提示');
		}
		$this->add('payset',$this->Online_api_model->get_payset($_SESSION['uid']));
		$this->add('info',$userinfo);
		$this->add('order_num',$this->Online_api_model->get_order_num());
		$this->add('copy_right',$this->Common_model->get_copyright());
		$this->display('member/pay/online_api_index.html');

	}

	/*
	**確認訂單頁面
	*/
	public function confirm_order(){
		if(empty($_SESSION['pay']['payid'])){
			show_error('參數錯誤，請聯系管理員!<a href="javascript:history.go(-1)">返回</a>', 200, '提示');
		}

		$bank      = $this->input->post('bank');
		$s_amount  = $this->input->post('s_amount');
		$order_num = $this->input->post('order_num');

		$this->load->helper('url');
		if(empty($order_num)){
			redirect('index.php/member/income/');
		}
		$this->add('bank',$bank);
		$this->add('order_num',$order_num);
		$this->add('s_amount',$s_amount);
		$this->add('username',$_SESSION['username']);
		$this->add('url',"/member/income/Index/common");
		$this->add('copy_right',$this->Common_model->get_copyright());
		if($_SESSION['pay']['paytype'] == 5){
			$this->add('time',date('Y-m-d H:i:s'));
			$this->add('payid',$_SESSION['pay']['payid']);
			$this->display('member/pay/huitong_confirm_order.html');
		}
		if($_SESSION['pay']['paytype'] == 2){
			$this->display('member/pay/yeepay_confirm_order.html');
		}
		else{
			$this->display('member/pay/online_confirm_order.html');
		}
	}
	/*
	**寶付
	*/
	//新生
	//幣付寶
	//環迅
	//智付
	//匯潮
	//通匯卡
	//快捷通
	//新環迅
	//融寶
	//國付寶

	//綁定銀行卡
	public function hk_money($class,$method){
		$this->load->model('member/Cash_model');
		$userinfo = $this->Common_model->get_user_info($_SESSION['uid']);
		$bank_arr = $this->Cash_model->get_bank_arr();//獲取銀行列表
		$this->add('userinfo',$userinfo);
		$this->add('copyright',$this->Common_model->get_copyright());
		$this->add('class',$this->router->class);
		$this->add('method',$this->router->method);
		$this->add('bank_arr',$bank_arr);
		$this->display('member/online_money.html');
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





	//智付

	//入口方法
	function common(){
		$order_num = $this->input->post('order_num'); //訂單號
		$bank = $this->input->post('bank');   //銀行慘數
		$s_amount = $this->input->post('s_amount');   //支付金額

		if ($_SESSION['pay']['paytype'] == 2){
			$bank  = $this->input->post('pd_FrpId'); //銀行
			$s_amount  = $this->input->post('p3_Amt');
		}else if($_SESSION['pay']['paytype'] == 5){
			$returnParams = $this->input->post('return_params');
			$TradeDate =  $this->input->post('TradeDate');
		}
		$flag = $this->Online_api_model->order_num_unique($order_num);
		$payconf = $this->Online_api_model->get_pay_config($_SESSION['pay']['payid']);
		if($flag > 0){
			show_error('參數錯誤，請再次嘗試!<a href="javascript:history.go(-1)">返回</a>', 200, '提示');
		}
		$result = $this->Online_api_model->write_in_record($_SESSION['uid'],$_SESSION['agent_id'],$s_amount,$order_num,$payconf);
		if($result){
			$type = $this->Online_api_model->get_pay_act($_SESSION['pay']['paytype']);
			$this->load->model('member/pay/'.$type.'_model');
			$mod = $type.'_model';
			if ($_SESSION['pay']['paytype'] == 1||$_SESSION['pay']['paytype'] == 9||$_SESSION['pay']['paytype'] == 10||$_SESSION['pay']['paytype'] == 11){
				//echo $payconf['vircarddoin'];die;
				$data = $this->$mod->get_all_info($payconf['f_url'],$order_num,$s_amount,$bank,$payconf['pay_id'],$payconf['pay_key'],$payconf['vircarddoin']);
				if($_SESSION['pay']['paytype'] == 10){
					unset($_SESSION['pay']);
					die;
				}
				unset($_SESSION['pay']);
			}else if($_SESSION['pay']['paytype'] == 4){
				$data = $this->$mod->get_all_info($payconf['f_url'],$order_num,$s_amount,$bank,$payconf['pay_id'],$payconf['pay_key'],$_SESSION['username']);
				unset($_SESSION['pay']);exit;

			}else if($_SESSION['pay']['paytype'] == 17){
				$data = $this->$mod->get_all_info($payconf['f_url'],$order_num,$s_amount,$bank,$payconf['pay_id'],$payconf['pay_key'],$_SESSION['username']);
				unset($_SESSION['pay']);
			}else if($_SESSION['pay']['paytype'] == 5){
				$data = $this->$mod->get_all_info($payconf['f_url'],$order_num,$s_amount,$bank,$payconf['pay_id'],$payconf['pay_key'],$returnParams,$TradeDate);
				unset($_SESSION['pay']);
			}else if($_SESSION['pay']['paytype'] == 19){
				$data = $this->$mod->get_all_info($payconf['f_url'],$order_num,$s_amount,$bank,$payconf['pay_id'],$payconf['pay_key'],$_SESSION['username'],$payconf['terminalid']);
				unset($_SESSION['pay']);
				//var_dump($data);die;

			}else if($_SESSION['pay']['paytype'] == 12){
				$data = $this->$mod->get_all_info($payconf['f_url'],$order_num,$s_amount,$bank,$payconf['pay_id'],$payconf['pay_key'],$_SESSION['username'],$payconf['vircarddoin']);
				unset($_SESSION['pay']);
				//var_dump($data);die;
			}else if($_SESSION['pay']['paytype'] == 16){
				$data = $this->$mod->get_all_info($payconf['f_url'],$order_num,$s_amount,$bank,$payconf['pay_id'],$payconf['pay_key'],$_SESSION['username']);

				unset($_SESSION['pay']);
				//var_dump($data);die;
			}else if($_SESSION['pay']['paytype'] == 18){
				$data = $this->$mod->get_all_info($payconf['f_url'],$order_num,$s_amount,$bank,$payconf['pay_id'],$payconf['pay_key'],$payconf['vircarddoin'],$payconf['pay_domain']);
				unset($_SESSION['pay']);
				//var_dump($data);die;
			}else if($_SESSION['pay']['paytype'] == 22||$_SESSION['pay']['paytype'] == 27||$_SESSION['pay']['paytype'] == 28||$_SESSION['pay']['paytype'] == 29){
				$data = $this->$mod->get_all_info($payconf['f_url'],$order_num,$s_amount,$bank,$payconf['pay_id'],$payconf['pay_key'],$_SESSION['uid'],$_SESSION['username']);
				unset($_SESSION['pay']);
				//var_dump($data);die;
			}else if($_SESSION['pay']['paytype'] == 23||$_SESSION['pay']['paytype'] == 25){
				$data = $this->$mod->get_all_info($payconf['f_url'],$order_num,$s_amount,$bank,$payconf['pay_id'],$payconf['pay_key'],$payconf['vircarddoin'],$payconf['goods']);
				if($bank == "wechat"){
					$type = "Rfupay";
				}

				unset($_SESSION['pay']);

			}else if($_SESSION['pay']['paytype'] == 6){
				$data = $this->$mod->get_all_info($payconf['f_url'],$order_num,$s_amount,$bank,$payconf['pay_id'],$payconf['pay_key'],$_SESSION['username'],$payconf['terminalid'],$payconf['goods'],$payconf['public_key'],$payconf['key_domain'],$payconf['file_key']);
				if($bank == "weixin"){
					$type = "Baofoo_wx";
				}
				unset($_SESSION['pay']);
				//var_dump($data);die;

			}else if($_SESSION['pay']['paytype'] == 24){
				$data = $this->$mod->get_all_info($payconf['f_url'],$order_num,$s_amount,$bank,$payconf['pay_id'],$payconf['pay_key'],$_SESSION['uid'],$_SESSION['username'],$payconf['public_key'],$payconf['file_key']);
				unset($_SESSION['pay']);
				//var_dump($data);die;

			}else if($_SESSION['pay']['paytype'] == 26){
				$data = $this->$mod->get_all_info($payconf['f_url'],$order_num,$s_amount,$bank,$payconf['pay_id'],$payconf['pay_key'],$payconf['public_key'],$payconf['vircarddoin']);
				unset($_SESSION['pay']);
				//var_dump($data);die;

			}else{
				$data = $this->$mod->get_all_info($payconf['f_url'],$order_num,$s_amount,$bank,$payconf['pay_id'],$payconf['pay_key']);
				unset($_SESSION['pay']);
			}
			$this->add("data",$data);
			$this->display('member/pay/'.$type.'.html');
		}else{
			show_error('支付失敗，請三秒後重試!<a href="javascript:history.go(-1)">返 回</a>', 200, '提示');
		}
	}


	//點卡支付
	function card(){
		$this->load->model('member/pay/Card_model');
		$act = $this->input->post('act');   //支付方式
		$p2_Order  = $this->input->post('p2_Order');
		$order_num = $p2_Order; //訂單號
		$p3_Amt  = $this->input->post('p3_Amt');
		$p4_FrpId  = $this->input->post('p4_FrpId');
		$s_amount = $p3_Amt;   //支付金額
		$pa7_cardAmt  = $this->input->post('pa7_cardAmt'); //充值卡金額
		$pa8_cardNo  = $this->input->post('pa8_cardNo');   //充值卡賬號
		$pa9_cardPwd  = $this->input->post('pa9_cardPwd'); //充值卡密碼
		$flag = $this->Online_api_model->order_num_unique($p2_Order);
		$payconf = $this->Online_api_model->get_pay_config($_SESSION['pay']['payid']);
		if($flag > 0){
			show_error('參數錯誤，請再次嘗試!<a href="javascript:history.go(-1)">返回</a>', 200, '提示');
		}
		$result = $this->Online_api_model->write_in_record($_SESSION['uid'],$_SESSION['agent_id'],$s_amount,$order_num,$payconf);
		if($result){
			$p1_MerId = $payconf['pay_id'];//商戶id
			$merchantKey = $payconf['pay_key'];//商戶密鑰
			$ServerUrl = 'http://'.$payconf['f_url'].'/index.php/pay/card_callback';//返回地址
			$reqURL_SNDApro = 'https://www.yeepay.com/app-merchant-proxy/command.action';
			//提交地址
			$p2_Order = $order_num;#商家設置用戶購買商品的支付信息.#商戶訂單號.提交的訂單號必須在自身賬戶交易中唯壹.
			$p3_Amt	= $s_amount;#支付卡面額
			$p4_verifyAmt = 'true';#是否較驗訂單金額
			$p5_Pid	= '1';#產品名稱
			$p6_Pcat = '2';	#iconv("UTF-8","GBK//TRANSLIT",$_POST['p5_Pid']);#產品類型
			$p7_Pdesc = '3';#產品描述
			#iconv("UTF-8","GBK//TRANSLIT",$_POST['p7_Pdesc']);
			#商戶接收交易結果通知的地址,易寶支付主動發送支付結果(服務器點對點通訊).通知會通過HTTP協議以GET方式到該地址上.
			$p8_Url	= $ServerUrl;
			$pa_MP	= $_SESSION['username'];#臨時信息
			#iconv("UTF-8","GB2312//TRANSLIT",$_POST['pa_MP']);
			$pa7_cardAmt = $pa7_cardAmt;#卡面額
			$pa8_cardNo	= $pa8_cardNo;#支付卡序列號.
			$pa9_cardPwd = $pa9_cardPwd;#支付卡密碼.
			$pd_FrpId = $p4_FrpId;#支付通道編碼
			$pr_NeedResponse = "1";#應答機制
			$pz_userId = $_SESSION['uid'];#用戶唯壹標識
			$pz1_userRegTime = date('Y-m-d H:i:s');#用戶的註冊時間
			#非銀行卡支付專業版測試時調用的方法，在測試環境下調試通過後，請調用正式方法annulCard
			#兩個方法所需參數壹樣，所以只需要將方法名改為annulCard即可
			#測試通過，正式上線時請調用該方法
			/*var_dump($p2_Order,$p3_Amt,$p4_verifyAmt,$p5_Pid,$p6_Pcat,$p7_Pdesc,$p8_Url,$pa_MP,$pa7_cardAmt,$pa8_cardNo,$pa9_cardPwd,$pd_FrpId,$pz_userId,$pz1_userRegTime,$p1_MerId,$merchantKey);die;*/
			$this->Card_model->annulCard($p2_Order,$p3_Amt,$p4_verifyAmt,$p5_Pid,$p6_Pcat,$p7_Pdesc,$p8_Url,$pa_MP,$pa7_cardAmt,$pa8_cardNo,$pa9_cardPwd,$pd_FrpId,$pz_userId,$pz1_userRegTime,$p1_MerId,$merchantKey,$reqURL_SNDApro);
			$hmac1 = $this->HmacMd6('ChargeCardQuery'.$p1_MerId.$order_num,$merchantKey);
			$arr = [
				'p0_Cmd'=>'ChargeCardQuery',
				'p1_MerId'=>$p1_MerId,
				'p2_Order'=>$order_num,
				'hmac'=>$hmac1,
				'callback'=>$ServerUrl
			];
			$this->load->library('payapi/Curl_yibao');
			$cishu = 1;
			while(Curl_yibao::run('http://58.64.206.34:10000','post',$arr)!='success'&&$cishu<3){
				$cishu++;
			}
		}else{
			show_error('支付失敗，請三秒後重試!<a href="javascript:history.go(-1)">返回</a>', 200, '提示');
		}
	}


	function HmacMd6($data,$key){

		# RFC 2104 HMAC implementation for php.
		# Creates an md5 HMAC.
		# Eliminates the need to install mhash to compute a HMAC
		# Hacked by Lance Rushing(NOTE: Hacked means written)

		#需要配置環境支持iconv，否則中文參數不能正常處理
		$key = iconv("GBK","UTF-8",$key);
		$data = iconv("GBK","UTF-8",$data);

		$b = 64; # byte length for md5
		if (strlen($key) > $b) {
			$key = pack("H*",md5($key));
		}
		$key = str_pad($key, $b, chr(0x00));
		$ipad = str_pad('', $b, chr(0x36));
		$opad = str_pad('', $b, chr(0x5c));
		$k_ipad = $key ^ $ipad ;
		$k_opad = $key ^ $opad;

		return md5($k_opad . pack("H*",md5($k_ipad . $data)));

	}

	function din_card(){
		$this->load->model('member/pay/Dinpayrsacard_model');
		$p2_Order  = $this->input->post('p2_Order');
		$order_num = $p2_Order; //訂單號
		$p3_Amt  = $this->input->post('p3_Amt');
		$p4_FrpId  = $this->input->post('p4_FrpId');
		$s_amount = $p3_Amt;   //支付金額
		$pa7_cardAmt  = $this->input->post('pa7_cardAmt'); //充值卡金額
		$pa8_cardNo  = $this->input->post('pa8_cardNo');   //充值卡賬號
		$pa9_cardPwd  = $this->input->post('pa9_cardPwd'); //充值卡密碼
		$flag = $this->Online_api_model->order_num_unique($p2_Order);
		$payconf = $this->Online_api_model->get_pay_config($_SESSION['pay']['payid']);
		if($flag > 0){
			show_error('參數錯誤，請再次嘗試!<a href="javascript:history.go(-1)">返回</a>', 200, '提示');
		}
		$result = $this->Online_api_model->write_in_record($_SESSION['uid'],$_SESSION['agent_id'],$s_amount,$order_num,$payconf);

		if($result){
			$postdata = $this->DinpayrsaCard_model->get_all_info($order_num,$p3_Amt,$p4_FrpId,$pa7_cardAmt,$pa8_cardNo,$pa9_cardPwd,$payconf);
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL,"https://api.dinpay.com/gateway/api/dcard");
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			$data = curl_exec($ch);
			if($data){
				$result=simplexml_load_string($data);
				if($result->trade_status ==="ACCEPTED_SUCCESS"){
					echo "<br>提交成功!";
					echo "<br>商戶訂單號:".$order_num."<br>";
				}else{
					echo "<br>充值失敗!";
					echo "<br>失敗原因:".$result->error_msg."<br>";
				}
			}
			curl_close($ch);
			return;
		}else{
			show_error('支付失敗，請三秒後重試!<a href="javascript:history.go(-1)">返回</a>', 200, '提示');
		}
	}
}