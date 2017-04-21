<?php if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Dinpayrsa_model extends Online_api_model {
	function __construct() {
		parent::__construct();
		$this->init_db();

	}

	function get_all_info($url,$order_num,$s_amount,$bank,$pay_id,$pay_key){
		if($bank == 'weixin'){
			return $this->get_all_info_weixin($url,$order_num,$s_amount,$bank,$pay_id,$pay_key);
		}
		$req_url = 'http://'.$url.'/index.php/pay/payfor';//跳轉地址
		$ServerUrl = 'http://'.$url.'/index.php/pay/Dinpayrsa_callback';//商戶後臺通知地址
		$return_url = 'http://'.$url.'/index.php/pay/return_url';
		$form_url = 'https://pay.dinpay.com/gateway?input_charset=UTF-8';//第三方地址
		$data=array();
		$priKey= openssl_get_privatekey($pay_key);
		$merchant_code = $pay_id;
		$service_type ="direct_pay";
		$interface_version ="V3.0";

		$pay_type = "";

		$sign_type ="RSA-S";

		$input_charset = "UTF-8";

		$notify_url = $ServerUrl;	//商戶後臺通知地址

		$order_no = $order_num; // 訂單號

		$order_time = date('Y-m-d H:i:s');

		$order_amount = $s_amount;	   //訂單金額

		$product_name ="pk";

		$product_code = "";

		$product_desc = "";

		$product_num = "";

		$show_url = "";

		$client_ip ="" ;

		$bank_code = $bank;	//銀行代號
		if($bank_code == "card"){
			$bank_code = "";
		}
		$redo_flag = "";

		$extend_param = "";

		$extra_return_param = "";

		$return_url = $return_url; //商戶返回地址

		$signStr= "";

		if($bank_code != ""){
			$signStr = $signStr."bank_code=".$bank_code."&";
		}
		if($client_ip != ""){
			$signStr = $signStr."client_ip=".$client_ip."&";
		}
		if($extend_param != ""){
			$signStr = $signStr."extend_param=".$extend_param."&";
		}
		if($extra_return_param != ""){
			$signStr = $signStr."extra_return_param=".$extra_return_param."&";
		}

		$signStr = $signStr."input_charset=".$input_charset."&";
		$signStr = $signStr."interface_version=".$interface_version."&";
		$signStr = $signStr."merchant_code=".$merchant_code."&";
		$signStr = $signStr."notify_url=".$notify_url."&";
		$signStr = $signStr."order_amount=".$order_amount."&";
		$signStr = $signStr."order_no=".$order_no."&";
		$signStr = $signStr."order_time=".$order_time."&";

		if($pay_type != ""){
			$signStr = $signStr."pay_type=".$pay_type."&";
		}

		if($product_code != ""){
			$signStr = $signStr."product_code=".$product_code."&";
		}
		if($product_desc != ""){
			$signStr = $signStr."product_desc=".$product_desc."&";
		}

		$signStr = $signStr."product_name=".$product_name."&";

		if($product_num != ""){
			$signStr = $signStr."product_num=".$product_num."&";
		}
		if($redo_flag != ""){
			$signStr = $signStr."redo_flag=".$redo_flag."&";
		}
		if($return_url != ""){
			$signStr = $signStr."return_url=".$return_url."&";
		}

		if($show_url != ""){

			$signStr = $signStr."service_type=".$service_type."&";
			$signStr = $signStr."show_url=".$show_url;
		}else{

			$signStr = $signStr."service_type=".$service_type;
		}

		openssl_sign($signStr,$sign_info,$priKey,OPENSSL_ALGO_MD5);

		$sign = base64_encode($sign_info);

		$data=array();
		$data['req_url'] = $req_url;
		$data['sign'] = $sign;
		$data['merchant_code'] = $merchant_code;
		$data['bank_code'] = $bank_code;
		$data['order_no'] = $order_no;
		$data['order_amount'] = $order_amount;
		$data['service_type'] = $service_type;
		$data['input_charset'] = $input_charset;
		$data['notify_url'] = $notify_url;
		$data['interface_version'] = $interface_version;
		$data['sign_type'] = $sign_type;
		$data['order_time'] = $order_time;
		$data['product_name'] = $product_name;
		$data['client_ip'] = $client_ip;
		$data['extend_param'] = $extend_param;
		$data['extra_return_param'] = $extra_return_param;
		$data['pay_type'] = $pay_type;
		$data['product_code'] = $product_code;
		$data['product_desc'] = $product_desc;
		$data['product_num'] = $product_num;
		$data['return_url'] = $return_url;
		$data['show_url'] = $show_url;
		$data['redo_flag'] = $redo_flag;
		$data['form_url'] = $form_url;
		return $data;

	}

	public function get_all_info_weixin($url,$order_num,$s_amount,$bank,$pay_id,$pay_key){
		$req_url = 'http://'.$url.'/index.php/pay/payfor';//跳轉地址
		$ServerUrl = 'http://'.$url.'/index.php/pay/Dinpayrsa_weixin_callback';//商戶後臺通知地址
		$form_url = 'https://api.dinpay.com/gateway/api/weixin';//第三方地址
		$data=array();
		$priKey= openssl_get_privatekey($pay_key);
		$merchant_code = $pay_id;
		$service_type = "wxpay";
		$notify_url = $ServerUrl;
		$interface_version ="V3.0";
		$sign_type = "RSA-S";
		$order_no = $order_num;
		$order_time = date('Y-m-d H:i:s');
		$order_amount = $s_amount;
		$product_name = "shoes";
		$product_code = "";
		$product_num = "";
		$product_desc = "";
		$extra_return_param = "";
		$extend_param = "";
		$signStr = "";
		if($extend_param != ""){
			$signStr = $signStr."extend_param=".$extend_param."&";
		}
		if($extra_return_param != ""){
			$signStr = $signStr."extra_return_param=".$extra_return_param."&";
		}
		$signStr = $signStr."interface_version=".$interface_version."&";
		$signStr = $signStr."merchant_code=".$merchant_code."&";
		$signStr = $signStr."notify_url=".$notify_url."&";
		$signStr = $signStr."order_amount=".$order_amount."&";
		$signStr = $signStr."order_no=".$order_no."&";
		$signStr = $signStr."order_time=".$order_time."&";
		if($product_code != ""){
			$signStr = $signStr."product_code=".$product_code."&";
		}
		if($product_desc != ""){
			$signStr = $signStr."product_desc=".$product_desc."&";
		}
		$signStr = $signStr."product_name=".$product_name."&";
		if($product_num != ""){
			$signStr = $signStr."product_num=".$product_num."&";
		}
		$signStr = $signStr."service_type=".$service_type;
		openssl_sign($signStr,$sign_info,$priKey,OPENSSL_ALGO_MD5);
		$sign = urlencode(base64_encode($sign_info));
		$data=array();
		$data['req_url'] = $req_url;
		$data['sign'] = $sign;
		$data['merchant_code'] = $merchant_code;
		$data['bank_code'] = $bank;
		$data['order_no'] = $order_no;
		$data['order_amount'] = $order_amount;
		$data['service_type'] = $service_type;
		$data['notify_url'] = $notify_url;
		$data['interface_version'] = $interface_version;
		$data['sign_type'] = $sign_type;
		$data['order_time'] = $order_time;
		$data['product_name'] = $product_name;
		$data['extend_param'] = $extend_param;
		$data['extra_return_param'] = $extra_return_param;
		$data['product_code'] = $product_code;
		$data['product_desc'] = $product_desc;
		$data['product_num'] = $product_num;
		$data['form_url'] = $form_url;
		return $data;
	}
}