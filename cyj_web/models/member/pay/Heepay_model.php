<?php if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Heepay_model extends Online_api_model {
	function __construct() {
		parent::__construct();
		$this->init_db();
	}
	function get_all_info($url,$order_num,$s_amount,$bank,$pay_id,$pay_key){
		//獲取ip
		$user_ip = "";
		if(isset($_SERVER['HTTP_CLIENT_IP']))
		{
			$user_ip = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			$user_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else
		{
			$user_ip = $_SERVER['REMOTE_ADDR'];
		}
		$req_url = 'http://'.$url.'/index.php/pay/payfor';//跳轉地址
		$ServerUrl ='http://'.$url.'/index.php/pay/heepay_callback';//商戶後臺通知地址
		$return_url = 'http://'.$url.'/index.php/pay/return_url';
		$form_url = 'https://pay.Heepay.com/Payment/Index.aspx';//第三方地址
		$data['version'] = 1;
		$data['agent_id'] = $pay_id;
		$data['agent_bill_id'] = $order_num;
		$data['agent_bill_time'] = date('YmdHis', time());
		if($bank=="weixin"){
			$bank = "";
			$data['pay_code'] = $bank;
			$data['pay_type'] = 30;
		}else{
			$data['pay_code'] = $bank;
			$data['pay_type'] = 20;
		}
		$data['user_ip'] = $user_ip;
		$data['pay_amt'] = $s_amount;
		$data['notify_url'] = $ServerUrl;
		$data['return_url'] = $return_url;
		$data['goods_name'] = "充值";
		$data['goods_num'] = 1;
		$data['goods_note'] = "";
		$data['remark'] = "";
		$data['sign_key'] = $pay_key;
		$data['req_url'] = $req_url;
		$data['form_url'] = $form_url;
		$sign_str = '';
		$sign_str  = $sign_str . 'version=' . $data['version'];
		$sign_str  = $sign_str . '&agent_id=' . $data['agent_id'];
		$sign_str  = $sign_str . '&agent_bill_id=' . $data['agent_bill_id'];
		$sign_str  = $sign_str . '&agent_bill_time=' . $data['agent_bill_time'];
		$sign_str  = $sign_str . '&pay_type=' . $data['pay_type'];
		$sign_str  = $sign_str . '&pay_amt=' . $data['pay_amt'];
		//$sign_str  = $sign_str .  htmlspecialchars("&not")."ify_url=" . $data['return_url'];
		$sign_str  = $sign_str .  '&notify_url=' . $data['notify_url'];
		$sign_str  = $sign_str . '&return_url=' . $data['return_url'];
		$sign_str  = $sign_str . '&user_ip=' . $data['user_ip'];
		$sign_str = $sign_str . '&key=' .$data['sign_key'];
		$sign = MD5($sign_str); //計算簽名值

		$sign_str1 = '';
		$sign_str1  = $sign_str1 . 'version=' . $data['version'];
		$sign_str1  = $sign_str1 . '&agent_id=' . $data['agent_id'];
		$sign_str1  = $sign_str1 . '&agent_bill_id=' . $data['agent_bill_id'];
		$sign_str1  = $sign_str1 . '&agent_bill_time=' . $data['agent_bill_time'];
		$sign_str1  = $sign_str1 . '&pay_type=' . $data['pay_type'];
		$sign_str1  = $sign_str1 . '&pay_amt=' . $data['pay_amt'];
		$sign_str1  = $sign_str1 .  htmlspecialchars("&not")."ify_url=" . $data['notify_url'];
		//$sign_str  = $sign_str .  '&notify_url=' . $data['notify_url'];
		$sign_str1  = $sign_str1 . '&return_url=' . $data['return_url'];
		$sign_str1  = $sign_str1 . '&user_ip=' . $data['user_ip'];
		$form_url = 'https://pay.Heepay.com/Payment/Index.aspx';//第三方地址
		$sign_str1 = $sign_str1."&goods_name=1&pay_Code=0&sign=".$sign;
		$url1 = $form_url.'?'.$sign_str1;
		$data['form_url'] = $url1;
		$data['sign'] = $sign;
		return $data;
	}
}