<?php if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Funpay_model extends Online_api_model {
	function __construct() {
		parent::__construct();
		$this->init_db();
	}
	function get_all_info($url,$order_num,$s_amount,$bank,$pay_id,$pay_key,$uid,$username){
		//獲取ip
		$req_url = 'http://'.$url.'/index.php/pay/payfor';//跳轉地址
		$ServerUrl ='http://'.$url.'/index.php/pay/funpay_callback';//商戶後臺通知地址
		$return_url = 'http://'.$url.'/index.php/pay/return_url';
		$form_url = 'https://www.funpay.com/website/pay.htm';//第三方地址
		$data['version'] = "1.0";
		$data['form_url'] = $form_url;
		$data['req_url'] = $req_url;
		$data['serialID'] = $uid.$order_num;
		$data['submitTime'] = date('YmdHis', time());
		$data['failureTime'] = "";
		$data['customerIP'] = "127.0.0.1";
		$data['totalAmount'] = $s_amount*100;
		$data['buyerMarked'] = "";
		$data['type'] = "1000";
		$data['partnerID'] = $pay_id;
		$data['returnUrl'] = $return_url;
		$data['noticeUrl'] = $ServerUrl;
		$data['charset'] = 1;
		$data['signType'] = 2;
		if($bank!="wx"){
			$data['payType'] = "BANK_B2C";
		}else{

			$data['payType'] = "WX";
		}
		$data['orgCode'] = $bank;
		$data['currencyCode'] = 1;
		$data['directFlag'] = 1;
		$data['borrowingMarked'] = 0;
		$data['couponFlag'] = 1;
		$data['platformID'] = "";
		$data['remark'] = "chongzhi";
		$orderID = $order_num;
		$orderAmount = $s_amount*100;
		$displayName = $username;
		$goodsName = $username;
		$goodsCount = 1;
		$data['orderDetails'] = $orderID.",".$orderAmount.",".$displayName.",".$goodsName.",".$goodsCount;
		$sign_str = "";
		$sign_str .= 'version=1.0';
		$sign_str .= '&serialID='.$data['serialID'];
		$sign_str .= '&submitTime='.$data['submitTime'];
		$sign_str .= '&failureTime='.$data['failureTime'];
		$sign_str .= '&customerIP='.$data['customerIP'];
		$sign_str .= '&orderDetails='.$data['orderDetails'];
		$sign_str .= '&totalAmount='.$data['totalAmount'];
		$sign_str .= '&type='.$data['type'];
		$sign_str .= '&buyerMarked='.$data['buyerMarked'];
		$sign_str .= '&payType='.$data['payType'];
		$sign_str .= '&orgCode='.$data['orgCode'];
		$sign_str .= '&currencyCode='.$data['currencyCode'];
		$sign_str .= '&directFlag='.$data['directFlag'];
		$sign_str .= '&borrowingMarked='.$data['borrowingMarked'];
		$sign_str .= '&couponFlag='.$data['couponFlag'];
		$sign_str .= '&platformID='.$data['platformID'];
		$sign_str .= '&returnUrl='.$data['returnUrl'];
		$sign_str .= '&noticeUrl='.$data['noticeUrl'];
		$sign_str .= '&partnerID='.$data['partnerID'];
		$sign_str .= '&remark='.$data['remark'];
		$sign_str .= '&charset='.$data['charset'];
		$sign_str .= '&signType='.$data['signType'];
		$sign_str .= '&pkey='.$pay_key;
		$signMsg =  md5($sign_str);
		$data['signMsg'] = $signMsg;
		return $data;
	}
}