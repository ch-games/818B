<?php if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Xbeipay_model extends Online_api_model {
	function __construct() {
		parent::__construct();
		$this->init_db();
	}
	function get_all_info($url,$order_num,$s_amount,$bank,$pay_id,$pay_key){
		$req_url = 'http://'.$url.'/index.php/pay/payfor';//跳轉地址
		$ServerUrl ='http://'.$url.'/index.php/pay/xbeipay_callback';//商戶後臺通知地址
		$form_url = 'http://gateway.xbeionline.com/Gateway/XbeiPay';//第三方地址
		$return_url = 'http://'.$url.'/index.php/pay/return_url';
		$data['Version'] = "V1.0";
		$data['MerchantCode'] = $pay_id;
		$data['OrderId'] = $order_num;
		$data['Amount'] = $s_amount;
		$data['TradeIp'] = "127.0.0.1";
		$data['AsyNotifyUrl'] = $ServerUrl;//異步通知
		$data['SynNotifyUrl'] = $return_url;
		$data['OrderDate'] = date('YmdHis', time());
		$data['PayCode'] = $bank;
		$data['req_url'] = $req_url;
		$data['form_url'] = $form_url;
		$signText = 'Version=['.$data['Version'].']MerchantCode=['.$pay_id.']OrderId=['.$order_num.']Amount=['.$s_amount.']AsyNotifyUrl=['.$ServerUrl.']SynNotifyUrl=['.$return_url.']OrderDate=['.$data['OrderDate'].']TradeIp=['.$data['TradeIp'].']PayCode=['.$bank.']TokenKey=['.$pay_key.']';
		$md5Sign = strtoupper(md5($signText));
		$data['SignValue'] = $md5Sign;
		return $data;
	}
}