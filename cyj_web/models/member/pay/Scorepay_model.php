<?php if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Scorepay_model extends Online_api_model {
	function __construct() {
		parent::__construct();
		$this->init_db();
	}
	function get_all_info($url,$order_num,$s_amount,$bank,$pay_id,$pay_key,$uid,$username){
		$this->load->library('payapi/AllscoreService');
		$req_url = 'http://'.$url.'/index.php/pay/payfor';//跳轉地址
		$ServerUrl = 'http://'.$url.'/index.php/pay/scorepay_callback';//商戶後臺通知地址
		$return_url = 'http://'.$url.'/index.php/pay/return_url';
		$form_url = 'https://paymenta.allscore.com/olgateway/serviceDirect.htm';//
		//$form_url = '119.61.12.89:8090/olgateway/serviceDirect.htm';//第三方地址
		$conf = array();
		$data['merchantId'] = $pay_id;
		$data['key'] = $pay_key;
		$data['input_charset'] = 'UTF-8';
		$data['notifyUrl'] = $ServerUrl;
		$data['returnUrl'] = $return_url;
		if($bank == "weixin"||$bank == "zhifubao"){
			$payMethod = "commonPay";
		}else{
			$payMethod = "bankPay";
		}
		$data['transport']    = 'http';
		$data['request_gateway'] = $form_url;
		$data['https_verify_url'] = "https://paymenta.allscore.com/olgateway/noticeQuery.htm?";
		if($payMethod == "bankPay"){
			// 構造要請求的參數數組
			$parameter = array(
				"service" => "directPay", //
				"inputCharset" => $data['input_charset'], //
				"merchantId" => $data['merchantId'], //
				"payMethod" => $payMethod, //
				"outOrderId" => $order_num, //
				"subject" => "chongzhi", //
				"body" => $username, //
				"transAmt" => $s_amount, //
				"notifyUrl" => $data['notifyUrl'], //
				"returnUrl" => $data['returnUrl'], //
				"signType" => "MD5",
				"defaultBank" => $bank,
				"channel" => "B2C",
				"cardAttr" => "01"
			);
			// 構造網銀支付接口
			//p($parameter);die;
			$allscoreService = new AllscoreService($data);
			$html_text = $allscoreService->bankPay($parameter);
			echo $html_text;die;
			//$ItemUrl = $allscoreService->createBankUrl($parameter);die;
		}else{
			$parameter = array(
				"service" => "directPay", //
				"inputCharset" => $data['input_charset'], //
				"merchantId" => $data['merchantId'], //
				"payMethod" => $payMethod, //
				"outOrderId" => $order_num, //
				"subject" => "chongzhi", //
				"body" => $username, //
				"detailUrl"=>$data['returnUrl'],
				"transAmt" => $s_amount, //
				"notifyUrl" => $data['notifyUrl'], //
				"returnUrl" => $data['returnUrl'], //
				"signType" => "MD5",
				//"cardType" => "debit",
				"outAcctId" => "lidong"

			);

			// 構造快捷支付接口
			$allscoreService = new AllscoreService($data);
			$html_text = $allscoreService->quickPay($parameter);
			echo $html_text;die;
			//$ItemUrl = $allscoreService->createQuickUrl($parameter);die;
		}

	}

}