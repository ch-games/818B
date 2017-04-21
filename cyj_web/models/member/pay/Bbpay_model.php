<?php if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Bbpay_model extends Online_api_model {
	function __construct() {
		parent::__construct();
		$this->init_db();
		$this->load->library('payapi/BebePaymd5');
	}
	function get_all_info($url,$order_num,$s_amount,$bank,$pay_id,$pay_key,$username){
		$req_url = 'http://'.$url.'/index.php/pay/payfor';//跳轉地址
		$ServerUrl = 'http://'.$url.'/index.php/pay/bbpay_callback';//商戶後臺通知地址
		$return_url = 'http://'.$url.'/index.php/pay/return_url';//同步地址
		$form_url = 'http://api.bbpay.com/bbpayapi/api/pcpay/merpay';//第三方地址
		$data=array();
		//初始化支付類，第壹個參數為md5key，第二個參數為商戶號


		$pay=new bebePaymd5($pay_id,$pay_key);
		$tempdata=array(
			'order'=>$order_num, //商戶生成的唯壹訂單號，註意不能重復，最長50位
			'transtime'=>strtotime("now")*1000, //時間戳，例如：1361324896000，精確到毫秒
			'amount'=>($s_amount)*100, //以"分"為單位的整型，必須大於零
			'productcategory'=>'1', // 商品種類 詳見接口使用說明文檔附錄商品類別碼表
			'productname'=>'pk', //商品名稱
			'productdesc'=>'pk', //商品描述 最長500位
			'productprice'=>($s_amount)*100, //商品單價 以"分"為單位的整型，必須大於零
			'productcount'=>1, //商品數量 最大數量值99999
			'merrmk'=>$username, //商戶備註信息 最長1000位，商戶自用備註信息,回調時會將該信息原樣返回給商戶
			'userua'=>$username, //用戶使用的移動終端的UA信息，最大長度200
			'userip'=>'192.168.11.11', //用戶支付時使用的網絡終端IP
			'areturl'=>$ServerUrl, //用來通知商戶支付結果，後臺發送post請求，前後臺回調地址的回調內容相同，長度100位 ，商戶收到請求必須回復內容，內容不能為空,必須用http:// 格式
			'sreturl'=>$return_url, // 用來通知商戶支付結果，前後臺回調地址的回調內容相同。用戶在網頁支付成功頁面，點擊“返回商戶”時的回調地址,長度100位，必須用http:// 格式
			'pnc'=>$bank   //銀行代碼，如果使用幣幣收銀臺，請傳 'pnc'=>'00001'
		);
		$dataf=$pay->pcWebPay($tempdata);
		$data['req_url'] = $req_url;
		$data['data'] = $dataf['data'];
		$data['form_url'] = $form_url;
		$data['encryptkey'] = $pay_key;
		$data['merchantaccount'] = $pay_id;
		return $data;

	}
}