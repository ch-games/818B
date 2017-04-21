<?php if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Eypal_model extends Online_api_model {
	function __construct() {
		parent::__construct();
		$this->init_db();

	}
	function get_all_info($url,$order_num,$s_amount,$bank,$pay_id,$pay_key,$username){
		$req_url = 'http://'.$url.'/index.php/pay/payfor';//跳轉地址
		$ServerUrl = 'http://'.$url.'/index.php/pay/eypal_callback';//商戶後臺通知地址
		$return_url = 'http://'.$url.'/index.php/pay/return_url';
		$form_url = 'https://gateway.eypal.com/Eypal/Gateway';//第三方地址
		$data=array();
		$data['req_url'] = $req_url;
		$data['ServerUrl'] = $ServerUrl;
		$data['form_url'] = $form_url;
		$data['partner'] = $pay_id;//商戶號
		$data['tokenkey'] = $pay_key;//md5密鑰（KEY）
		$data['version'] = '1.0';
		$data['orderid'] = $order_num;//流水號
		$data['payamount'] = $s_amount;//訂單金額
		$data['paytype'] = $bank;//銀行參數
		$data['payip'] = "127.0.0.1";//支付IP
		$data['returnUrl'] = $return_url;//同步通知:
		$data['remark'] = $username;
		$data['notifyUrl'] = $ServerUrl;
		$signText = "version=".$data['version']."&partner=".$data['partner']."&orderid=".$data['orderid']."&payamount=".$data['payamount']."&payip=".$data['payip']."&notifyurl=".$data['notifyUrl']."&returnurl=".$data['returnUrl']."&paytype=".$data['paytype']."&remark=".$data['remark']."&key=".$data['tokenkey'];
		$signValue = strtolower(md5($signText));
		$data['sign'] = $signValue;
		return $data;
	}
}