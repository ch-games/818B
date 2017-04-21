<?php if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Mobao_model extends Online_api_model {
	function __construct() {
		parent::__construct();
		$this->init_db();
		//$this->load->library('payapi/MobaoPay');


	}
	function get_all_info($url,$order_num,$s_amount,$bank,$pay_id,$pay_key,$vircarddoin,$pay_domain){
		$req_url = 'http://'.$url.'/index.php/pay/payfor';//跳轉地址
		$ServerUrl = 'http://'.$url.'/index.php/pay/mobao_callback';//商戶後臺通知地址
		$return_url = 'http://'.$url.'/index.php/pay/return_url';
		//$form_url = "https://trade.mobaopay.com/cgi-bin/netpayment/pay_gate.cg";
		$form_url = $pay_domain;//第三方地址
		//https://trade.mobaopay.com/cgi-bin/netpayment/pay_gate.cgi
		$data=array();// 請求數據賦值
		$data['apiName'] ="WEB_PAY_B2C"; // 商戶APINMAE，WEB渠道壹般支付
		$data['apiVersion'] = "1.0.0.0";// 商戶API版本
		$data['platformID'] = $vircarddoin;// 商戶在Mo寶支付的平臺號
		$data['merchNo'] = $pay_id;// Mo寶支付分配給商戶的賬號
		$data['merchUrl'] = $ServerUrl;// 商戶通知地址
		$data['bankCode'] = $bank;// 銀行代碼，不傳輸此參數則跳轉Mo寶收銀臺
		if($bank="weixin"){
			$data['bankCode'] = "";
		}
		$data['orderNo'] = $order_num;//商戶訂單號
		$time = time();
		$data['tradeDate'] = date("Ymd",$time);// 商戶訂單日期
		$data['amt'] = $s_amount;	// 商戶交易金額
		$data['merchParam'] = "abcd";	// 商戶參數
		$data['tradeSummary'] = "充值";	// 商戶交易摘要
		$data['mbp_key'] = $pay_key;
		$data['act'] = "Mobao";
		$data['req_url'] = $req_url;
		$data['form_url'] = $form_url;



		// 將中文轉換為UTF-8
		if(!preg_match("/[\xe0-\xef][\x80-\xbf]{2}/", $data['merchUrl'])){

			$data['merchUrl'] = iconv("GBK","UTF-8", $data['merchUrl']);
		}
		if(!preg_match("/[\xe0-\xef][\x80-\xbf]{2}/", $data['merchParam'])){
			$data['merchParam'] = iconv("GBK","UTF-8", $data['merchParam']);
		}
		if(!preg_match("/[\xe0-\xef][\x80-\xbf]{2}/", $data['tradeSummary'])){
			$data['tradeSummary'] = iconv("GBK","UTF-8", $data['tradeSummary']);
		}
		return $data;

	}
}