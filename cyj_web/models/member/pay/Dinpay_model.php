<?php if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Dinpay_model extends Online_api_model {
	function __construct() {
		parent::__construct();
		$this->init_db();

	}
	function get_all_info($url,$order_num,$s_amount,$bank,$pay_id,$pay_key){
		$req_url = 'http://'.$url.'/index.php/pay/payfor';//跳轉地址
		$ServerUrl = 'http://'.$url.'/index.php/pay/dinpay_callback';//商戶後臺通知地址
		$return_url = 'http://'.$url.'/index.php/pay/return_url';
		$form_url = 'https://pay.dinpay.com/gateway?input_charset=UTF-8 ';//第三方地址
		$data=array();
		$data['req_url'] = $req_url;
		$data['ServerUrl'] = $ServerUrl;
		$data['form_url'] = $form_url;
		$data['input_charset'] = "UTF-8";//參數編碼字符集(必選)
		$data['interface_version'] = "V3.0";//接口版本(必選)固定值:V3.0
		$data['merchant_code'] = $pay_id;//商家號（必填）
		$data['notify_url'] = $ServerUrl;//後臺通知地址(必填)
		$data['order_amount'] = $s_amount;//定單金額（必填）
		$data['order_no'] = $order_num;//商家定單號(必填)
		$data['order_time'] = date("Y-m-d H:i:s");//商家定單時間(必填)
		$data['sign_type'] = "MD5";//$_POST['sign_type'];//簽名方式(必填)
		$data['product_code'] = '';//商品編號(選填)
		$data['product_desc'] = 'PK';//商品描述（選填）
		$data['product_name'] = "在線充值";//商品名稱（必填）
		$data['product_num'] = '1';//端口數量(選填)
		$data['return_url'] = $return_url;//頁面跳轉同步通知地址(選填)
		$data['service_type'] = "direct_pay";//業務類型(必填)
		$data['show_url'] = '';//商品展示地址(選填);
		$data['extend_param'] = '';//公用業務擴展參數（選填）;
		$data['extra_return_param'] = '';//公用業務回傳參數（選填）
		$data['bank_code'] = $bank;// 直聯通道代碼（選填）
		$data['client_ip'] = '';//客戶端IP（選填）;
		if ($bank =="weixin") {
			$data['bank_code'] = "";
			//$data['pay_type'] = 'weixin';
		}else{
			//$data['pay_type'] = 'b2c';
		}
		/*
        **
        ** 簽名順序按照參數名a到z的順序排序，若遇到相同首字母，則看第二個字母，以此類推，同時將商家支付密鑰key放在最後參與簽名，
        ** 組成規則如下：
        ** 參數名1=參數值1&參數名2=參數值2&……&參數名n=參數值n&key=key值
        **/
		$signSrc= "";

		//組織訂單信息
		if($data['bank_code'] != "") {
			$signSrc = $signSrc."bank_code=".$data['bank_code']."&";
		}
		if($data['client_ip'] != "") {
			$signSrc = $signSrc."client_ip=".$data['client_ip']."&";
		}
		if($data['extend_param'] != "") {
			$signSrc = $signSrc."extend_param=".$data['extend_param']."&";
		}
		if($data['extra_return_param'] != "") {
			$signSrc = $signSrc."extra_return_param=".$data['extra_return_param']."&";
		}
		if($data['input_charset'] != "") {
			$signSrc = $signSrc."input_charset=".$data['input_charset']."&";
		}
		if($data['interface_version'] != "") {
			$signSrc = $signSrc."interface_version=".$data['interface_version']."&";
		}
		if($data['merchant_code'] != "") {
			$signSrc = $signSrc."merchant_code=".$data['merchant_code']."&";
		}
		if($data['notify_url'] != "") {
			$signSrc = $signSrc."notify_url=".$data['notify_url']."&";
		}
		if($data['order_amount'] != "") {
			$signSrc = $signSrc."order_amount=".$data['order_amount']."&";
		}
		if($data['order_no'] != "") {
			$signSrc = $signSrc."order_no=".$data['order_no']."&";
		}
		if($data['order_time'] != "") {
			$signSrc = $signSrc."order_time=".$data['order_time']."&";
		}
		if($data['product_code'] != "") {
			$signSrc = $signSrc."product_code=".$data['product_code']."&";
		}
		if($data['product_desc'] != "") {
			$signSrc = $signSrc."product_desc=".$data['product_desc']."&";
		}
		if($data['product_name'] != "") {
			$signSrc = $signSrc."product_name=".$data['product_name']."&";
		}
		if($data['product_num'] != "") {
			$signSrc = $signSrc."product_num=".$data['product_num']."&";
		}
		if($data['return_url'] != "") {
			$signSrc = $signSrc."return_url=".$data['return_url']."&";
		}
		if($data['service_type'] != "") {
			$signSrc = $signSrc."service_type=".$data['service_type']."&";
		}
		if($data['show_url'] != "") {
			$signSrc = $signSrc."show_url=".$data['show_url']."&";
		}
		//設置密鑰
		$signSrc = $signSrc."key=".$pay_key;
		$singInfo = $signSrc;
		//簽名
		$data['sign'] = md5($singInfo);
		return $data;
	}
}