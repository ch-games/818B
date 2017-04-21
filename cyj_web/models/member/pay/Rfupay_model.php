<?php if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Rfupay_model extends Online_api_model {
	function __construct() {
		parent::__construct();
		$this->init_db();
	}

	function get_all_info($url,$order_num,$s_amount,$bank,$pay_id,$pay_key,$vircarddoin,$goods){
		$data=array();
		$req_url = 'http://'.$url.'/index.php/pay/payfor'; //第三方地址
		$ServerUrl = 'http://'.$url.'/index.php/pay/rfupay_callback'; //商戶後臺通知地址
		$form_url = 'http://payment.rfupay.com/prod/commgr/control/inPayService'; //跳轉地址
		$return_url = 'http://'.$url.'/index.php/pay/return_url';//同步通知結果
		$data = array();
		$data['req_url'] = $req_url;
		$data['form_url'] = $form_url;
		$data['partyId'] = $pay_id;
		$data['accountId'] = $vircarddoin;
		$data['goods'] = $goods;
		$data['orderNo'] = $data['goods'].$order_num;
		$data['orderAmount'] = $s_amount;
		$data['returnUrl'] = $return_url;
		$data['cardType'] = "01";
		$data['bank'] = $bank;
		$data['encodeType'] = "MD5";
		$data['checkUrl'] = $ServerUrl;
		if($bank == "wechat"){
			$data['appType'] = "WECHAT";
		}else{
			$data['appType'] = "";
		}
		$data['refCode'] = "00000000";
		$md5Key = $pay_key;
		$pStr = "orderNo" . $data['orderNo'] . "appType" . $data['appType'] . "orderAmount" . $data['orderAmount'] . "encodeType" . $data['encodeType'].$md5Key;
		$pStrMd5=md5($pStr);
		$signMD5 = strtolower($pStrMd5);
		$myvars = 'partyId=' . $data['partyId'] . '&appType=' . $data['appType'] . '&orderNo=' . $data['orderNo'] . '&refCode=' . $data['refCode'] . '&orderAmount=' . $data['orderAmount'] . '&goods=' . $data['goods'] . '&returnUrl=' . $data['returnUrl'] . '&cardType=' . $data['cardType'] . '&bank=' . $data['bank'] . '&encodeType=' . $data['encodeType'] . '&accountId=' . $data['accountId'] . '&signMD5=' . $signMD5. '&checkUrl=' . $ServerUrl;;
		$data['form_url'] = $form_url;
		$data['myvars'] = $myvars;
		$data['act'] = "rfupay";
		return $data;
		/*		$ch = curl_init($form_url);
                curl_setopt( $ch, CURLOPT_POST, 1);
                curl_setopt( $ch, CURLOPT_POSTFIELDS, $myvars);
                curl_setopt( $ch, CURLOPT_HEADER, 0);
                curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 0);
                $response = curl_exec( $ch );
                if (curl_errno($ch)) {
                    echo 'Curl error: ' . curl_error($ch);
                }
                curl_close($ch);*/


	}


	function test_input($data) {
		$data = trim($data);
		$data = stripslashes($data);
		$data = htmlspecialchars($data);
		return $data;
	}
}