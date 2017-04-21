<?php if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Jubaopay_model extends Online_api_model {
	function __construct() {
		parent::__construct();
		$this->init_db();
	}
	function get_all_info($url,$order_num,$s_amount,$bank,$pay_id,$pay_key,$uid,$username,$public_key,$pwd){
		/*echo '<pre>';
		var_dump($url,$order_num,$s_amount,$bank,$pay_id,$pay_key,$uid,$username,$public_key,$pwd);exit;*/
		//$order_num = "160808125945129403";
		$req_url = 'http://'.$url.'/index.php/pay/payfor';//跳轉地址
		$ServerUrl = 'http://'.$url.'/index.php/pay/jubaopay_callback';//商戶後臺通知地址
		$return_url = 'http://'.$url.'/index.php/pay/return_url';
		$form_url = 'https://www.jubaopay.com/apipay.htm';//第三方地址
		$conf = array();
		$conf['privKey'] = $pay_key;
		$conf['pubKey'] = $public_key;
		$conf['psw'] = $pwd;
		$this->load->library('payapi/Jubaopay');
		$jubaopay=new jubaopay($conf);
		$jubaopay->setEncrypt("payid", $order_num);
		$jubaopay->setEncrypt("partnerid", $pay_id);
		$jubaopay->setEncrypt("amount", $s_amount);
		$jubaopay->setEncrypt("payerName", $uid);
		$jubaopay->setEncrypt("remark", $username);
		$jubaopay->setEncrypt("returnURL", $return_url);
		$jubaopay->setEncrypt("callBackURL", $ServerUrl);
		//對交易進行加密=$message並簽名=$signature
		$jubaopay->interpret();
		$message=$jubaopay->message;
		$signature=$jubaopay->signature;
		$data1['form_url'] = $form_url;
		$data1['req_url'] = $req_url;
		$data1['payMethod'] = "ALL";
		$data1['message'] = $message;
		$data1['signature'] = $signature;
		$data1['tab'] = "";
		return $data1;
	}

}