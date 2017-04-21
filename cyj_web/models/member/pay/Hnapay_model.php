<?php if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Hnapay_model extends Online_api_model {
	function __construct() {
		parent::__construct();
		$this->init_db();
	}

	function get_all_info($url,$order_num,$s_amount,$bank,$pay_id,$pay_key,$vircarddoin){
		$data=array();
		$req_url = 'http://'.$url.'/index.php/pay/payfor'; //第三方地址
		$ServerUrl = 'http://'.$url.'/index.php/pay/hnapay_callback'; //商戶後臺通知地址
		$return_url = 'http://'.$url.'/index.php/pay/return_url';
		$form_url = 'https://www.hnapay.com/website/pay.htm'; //跳轉地址
		$version = '2.6';//版本 version
		$serialID = $order_num;//訂單號
		$submitTime = date('YmdHis');//訂單時間
		$failureTime = '';//date('YmdHis',strtotime("+1 year"));//訂單失效時間
		$customerIP = '';//下單IP
		$jine = $s_amount*100;
		$orderDetails = $order_num.','.$jine.','.''.','.'PK'.','.'1';//訂單號，訂單金額*10，商戶名稱，商品名稱，商品數量
		$totalAmount = $jine;//訂單金額
		$type = '1000';//交易類型 1000為即時支付
		$buyerMarked = $vircarddoin;//新生賬戶號
		//$payType = 'BANK_B2C';//付款方支付方式
		$orgCode = $bank;//銀行編碼
		if($bank == "WECHATPAY"){
			$payType = 'QRCODE_B2C';//微信支付方式
		}else{
			$payType = 'BANK_B2C';//付款方支付方式
		}
		$currencyCode = '';//人民幣
		$directFlag = '1';//是否直連
		$borrowingMarked = '';//資金來源借貸標示
		$couponFlag = '';//優惠劵標示
		$platformID = '';//平臺商ID
		$returnUrl = $return_url;//返回地址
		$noticeUrl = $ServerUrl;//商戶通知地址
		$partnerID = $pay_id;//商戶ID
		$remark = '';//擴展字段
		$charset = "1";//編碼格式
		$signType = '2';//加密方式
		$str = '&';
		$signMsg = 'version='.$version.$str.'serialID='.$serialID.$str.'submitTime='.$submitTime.$str.'failureTime='.$failureTime.$str.'customerIP='.$customerIP.$str.'orderDetails='.$orderDetails.$str.'totalAmount='.$totalAmount.$str.'type='.$type.$str.'buyerMarked='.$buyerMarked.$str.'payType='.$payType.$str.'orgCode='.$orgCode.$str.'currencyCode='.$currencyCode.$str.'directFlag='.$directFlag.$str.'borrowingMarked='.$borrowingMarked.$str.'couponFlag='.$couponFlag.$str.'platformID='.$platformID.$str.'returnUrl='.$returnUrl.$str.'noticeUrl='.$noticeUrl.$str.'partnerID='.$partnerID.$str.'remark='.$remark.$str.'charset='.$charset.$str.'signType='.$signType;//加密字符串
		$pkey = $pay_key;//商戶秘鑰
		$signMsg = $signMsg."&pkey=".$pkey;
		$signMsg =  md5($signMsg);
		$data['req_url'] = $req_url;
		$data['ServerUrl'] = $ServerUrl;
		$data['form_url'] = $form_url;
		$data['version'] = '2.6';//版本 version
		$data['serialID'] = $order_num;//訂單號
		$data['submitTime'] = date('YmdHis');//訂單時間
		$data['failureTime'] ="";//date('YmdHis',strtotime("+1 year"));//訂單失效時間
		$data['customerIP'] ="";//下單IP
		$jine = $s_amount*100;
		$orderDetails = $order_num.','.$jine.','.''.','.'PK'.','.'1';//訂單號，訂單金額*10，商戶名稱，商品名稱，商品數量
		$data['orderDetails'] =$orderDetails;
		$totalAmount = $jine;//訂單金額
		$data['totalAmount'] = $totalAmount;
		$data['type'] = '1000';//交易類型 1000為即時支付
		//$buyerMarked = $buyerMarked;//新生賬戶號
		$data['buyerMarked'] = $buyerMarked;//新生賬戶號
		$data['payType'] = 'BANK_B2C';//付款方支付方式
		$data['orgCode'] =  $bank;//銀行
		$data['currencyCode'] = '';//人民幣
		$data['directFlag'] = '1';//是否直連
		$data['borrowingMarked'] = '';//資金來源借貸標示
		$data['couponFlag'] = '';//優惠劵標示
		$data['platformID'] = '';//平臺商ID
		$data['returnUrl'] = $returnUrl;//返回地址
		$data['noticeUrl'] = $ServerUrl;//商戶通知地址
		$data['partnerID'] = $pay_id;//商戶ID
		$data['remark'] = '';//擴展字段
		$data['charset'] = "1";//編碼格式
		$data['signType'] = '2';//加密方式
		$data['pkey'] = $pay_key;//商戶秘鑰
		$data['signMsg'] = $signMsg;
		return $data;



	}
}