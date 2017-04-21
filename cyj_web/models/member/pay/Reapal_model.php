<?php if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Reapal_model extends Online_api_model {
	function __construct() {
		parent::__construct();
		$this->init_db();
		$this->load->library('payapi/Rongpay_service');
	}
	function get_all_info($url,$order_num,$s_amount,$bank,$pay_id,$pay_key,$vircarddoin){
		$ServerUrl = 'http://'.$url.'/index.php/pay/reapal_callback';//商戶後臺通知地址
		$form_url = 'https://epay.reapal.com/portal?';//第三方地址
		$req_url = 'http://'.$url.'/index.php/pay/payfor';//跳轉地址
		$notify_url = 'http://'.$url.'/index.php/pay/reapal_notifyback';
		$return_url = 'http://'.$url.'/index.php/pay/return_url';
		$order_no = $order_num;// 請與貴網站訂單系統中的唯壹訂單號匹配
		$title = '在線充值';//訂單名稱，顯示在融寶支付收銀臺裏的“商品名稱”裏
		$body = 'pk在線充值';// 訂單描述、訂單詳細、訂單備註，顯示在融寶支付收銀臺裏的“商品描述”裏
		$total_fee = $s_amount;// 訂單總金額，顯示在融寶支付收銀臺裏的“應付總額”裏
		$buyer_email = "";// 默認買家融寶支付賬號
		$defaultbank = $bank;// 網銀代碼
		if ($defaultbank == "NO") {
			$paymethod = "bankPay"; // 支付方式，默認網關
			$defaultbank = "";
		} else {
			$paymethod = "directPay"; // 支付方式，銀行直連
			$defaultbank = $bank;
		}

		// ///////////////////////////////////////////////////////////////////////////////////////////////////
		$key = $pay_key;
		$sign_type = "MD5";
		$seller_email = $vircarddoin;
		$merchant_ID = $pay_id;

		// notify_url 交易過程中服務器通知的頁面 要用 http://格式的完整路徑，不允許加?id=123這類自定義參數
		//$notify_url = "http://".$result['f_url'] . "/index.php/pay/reapal_callback";

		//$return_url = "http://".$payconf['f_url'] . "/reapal_callback";
		// 構造要請求的參數數組，無需改動
		$parameter = array(
			"service" => "online_pay", // 接口名稱，不需要修改
			"payment_type" => "1", // 交易類型，不需要修改
			// 獲取配置文件(rongpay_config.php)中的值
			"merchant_ID" => $merchant_ID,
			"seller_email" => $seller_email,// 簽約融寶支付賬號或賣家收款融寶支付帳戶
			"return_url" => $return_url,
			"notify_url" => $notify_url,
			"charset" => "utf-8",
			// 從訂單數據中動態獲取到的必填參數
			"order_no" => $order_no,
			"title" => $title,
			"body" => $body,
			"total_fee" => $total_fee,
			// 擴展功能參數——銀行直連
			"paymethod" => $paymethod,
			"defaultbank" => $defaultbank
		);
		// 構造請求函數
		$rongpay = new rongpay_service();
		$temp  =   $rongpay->rongpay_sign($parameter, $key, $sign_type);
		$parameter['sign'] = $temp;
		$parameter['req_url'] = $req_url;
		$parameter['form_url'] = $form_url;
		$parameter['act'] = "reapal";
		$parameter['sign_type'] = $sign_type;
		$sHtmlText = $rongpay->BuildForm($req_url,$form_url,$temp,$sign_type);
		return $sHtmlText;
	}
}