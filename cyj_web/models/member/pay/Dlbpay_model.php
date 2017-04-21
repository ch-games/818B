<?php if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Dlbpay_model extends Online_api_model {
	function __construct() {
		parent::__construct();
		$this->init_db();
	}

	function get_all_info($url,$order_num,$s_amount,$bank,$pay_id,$pay_key,$public_key,$vircarddoin){
		$data=array();
		$req_url = 'http://'.$url.'/index.php/pay/payfor'; //第三方地址
		$ServerUrl = 'http://'.$url.'/index.php/pay/dlbpay_callback'; //商戶後臺通知地址
		$form_url = 'http://openapi.duolabao.cn/v1/customer/order/payurl/create'; //跳轉地址
		//$return_url = 'http://'.$url.'/index.php/pay/return_url';//同步通知結果
		$pay_data = array();
		$pay_data['req_url'] = $req_url;
		$pay_data['form_url'] = $form_url;
		$pay_data['accesskey'] = $pay_key;
		$pay_data['secretkey'] = $public_key;
		$pay_data['timestamp'] = time();
		$pay_data['path'] = '/v1/customer/order/payurl/create';
		$sign_data1 = array(
			'customerNum'=>$pay_id,           // 哆啦寶商戶號--請求傳遞
			'shopNum'=>$vircarddoin,                   // 哆啦寶店鋪號--請求傳遞
			'requestNum'=>$order_num,                 // 訂單號--請求傳遞
			'amount'=>$s_amount,                          // 金額--請求傳遞
			'callbackUrl'=>$ServerUrl,               // 回調服務器鏈接--請求傳遞
			'extraInfo'=>'pk',
			'source'=>'API'
		);
		foreach ($sign_data1 as $key=> $value){
			$string[] = $key.'->'.$value;
		}
		$sign_data = implode(',',$string);
		$pay_data['body'] = $sign_data;
		return $pay_data;
	}



	/**13位時間戳**/
	function getMillisecond() {
		list($t1, $t2) = explode(' ', microtime());
		return $t2.ceil( ($t1 * 1000) );
	}
}