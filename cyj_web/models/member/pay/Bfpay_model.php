<?php if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Bfpay_model extends Online_api_model {
	function __construct() {
		parent::__construct();
		$this->init_db();
		$this->load->library('payapi/Befpay');
	}
	function get_all_info($url,$order_num,$s_amount,$bank,$pay_id,$pay_key,$username){
		$req_url = 'http://'.$url.'/index.php/pay/payfor';//跳轉地址
		$ServerUrl = 'http://'.$url.'/index.php/pay/bfpay_callback';//商戶後臺通知地址
		$form_url = 'http://i.5dd.com/pay.api';//第三方地址
		$data=array();
		//初始化支付類，第壹個參數為md5key，第二個參數為商戶號
		$pay=new Befpay($pay_key,$pay_id);
		//var_dump();die;
		$postData=array(
			'p1_md'=>1, //網銀1 卡類2
			'p2_xn'=>$order_num,//訂單號
			'p3_bn'=>$pay_id,//商戶號
			'p4_pd'=>$bank, //支付方式id
			'p5_name'=>$username, //產品名稱
			'p6_amount'=>$s_amount, //支付金額
			'p7_cr'=>1, //幣種，目前僅支持人民幣
			'p8_ex'=>'test the payment', //擴展信息
			'p9_url'=>$ServerUrl, //通知支付結果地址 格式：http://your url  壹定要加http
			'p10_reply'=>1, //是否通知 1通知 0不通知
			'p11_mode'=>0, //0 返回充值地址，由商戶負責跳轉 1顯示幣付寶充值界面，跳轉到充值 2不顯示幣付寶充值界面，直接跳轉到網銀
			'p12_ver'=>1
		);

		echo $pay->webPay($postData,$req_url,$form_url); //調用網銀接口
	}
}