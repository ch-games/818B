<?php if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Alipay_model extends Online_api_model {
	function __construct() {
		parent::__construct();
		$this->init_db();
	}
	function get_all_info($url,$order_num,$s_amount,$bank,$pay_id,$pay_key,$uid,$username){
		$this->load->library('payapi/Alipay_submit');
		$req_url = 'http://'.$url.'/index.php/pay/payfor';//跳轉地址
		$ServerUrl = 'http://'.$url.'/index.php/pay/alipay_callback';//商戶後臺通知地址
		$return_url = 'http://'.$url.'/index.php/pay/return_url';
		$form_url = 'https://b.alipay.com/order/serviceIndex.htm';//第三方地址
		$conf = array();
		$data['partner'] = $pay_id;
		$data['seller_id'] = $pay_id;
		$data['key'] = $pay_key;
		$data['notify_url'] = $ServerUrl;
		$data['return_url'] = $return_url;
		$data['sign_type'] = strtoupper('MD5');
		$data['input_charset'] = strtolower('utf-8');
		//ca證書路徑地址，用於curl中ssl校驗
		//請保證cacert.pem文件在當前文件夾目錄中
		$data['cacert'] = getcwd().'\\public\\key\\cacert.pem';
		$data['transport'] = 'http';
		$data['payment_type'] = "1";
		$data['service'] = "create_direct_pay_by_user";
		//構造要請求的參數數組，無需改動
		$parameter = array(
			"service"       => $data['service'],
			"partner"       => $data['partner'],
			"seller_id"  => $data['seller_id'],
			"payment_type"	=> $data['payment_type'],
			"notify_url"	=> $data['notify_url'],
			"return_url"	=> $data['return_url'],
			"anti_phishing_key"=>$data['anti_phishing_key'],
			"exter_invoke_ip"=>$data['exter_invoke_ip'],
			"out_trade_no"	=> $order_num,
			"subject"	=> "PK充值",
			"total_fee"	=> $s_amount,
			"body"	=> "",
			"_input_charset"	=> trim(strtolower($data['input_charset']))
			//其他業務參數根據在線開發文檔，添加參數.文檔地址:https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.kiX33I&treeId=62&articleId=103740&docType=1
			//如"參數名"=>"參數值"

		);

		$alipaySubmit = new Alipay_submit($data);
		$html_text = $alipaySubmit->buildRequestForm($parameter,"get", "確認");
		echo $html_text;die;
	}

}