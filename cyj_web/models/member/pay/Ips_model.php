<?php if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Ips_model extends Online_api_model {
	function __construct() {
		parent::__construct();
		$this->init_db();
	}
	function get_all_info($url,$order_num,$s_amount,$bank,$pay_id,$pay_key){
		$req_url = 'http://'.$url.'/index.php/pay/payfor';//跳轉地址
		$ServerUrl = 'http://'.$url.'/index.php/pay/ips_callback';//商戶後臺通知地址
		$form_url = 'https://pay.ips.com.cn/ipayment.aspx';//第三方地址
		$data=array();
		$data['req_url'] = $req_url;
		$data['ServerUrl'] = $ServerUrl;
		$data['form_url'] = $form_url;
		/* $data['Mer_code'] = $pay_id;
        $data['Mer_key'] = $pay_key;
        $data['Billno'] = $order_num;
        $data['Amount'] = number_format($s_amount, 2, '.', '');//訂單金額(保留2位小數)
        $data['Date'] = date('Ymd');//訂單日期
        $data['Currency_Type'] = 'RMB';//幣種
        $data['Gateway_Type'] = 01;//支付卡種
        $data['Lang'] = 'GB';//語言
        $data['Merchanturl'] = $ServerUrl;//支付結果成功返回的商戶URL
        $data['FailUrl'] = $ServerUrl;//支付結果失敗返回的商戶URL
        $data['ErrorUrl'] = $ServerUrl;//支付結果錯誤返回的商戶URL
        $data['Attach'] = '';//商戶數據包
        $data['DispAmount'] = $s_amount;//顯示金額
        $data['OrderEncodeType'] = 5;//訂單支付接口加密方式
        $data['RetEncodeType'] = 17;//交易返回接口加密方式
        $data['Rettype'] = 1;//返回方式
        $data['ServerUrl'] =  $ServerUrl;//Server to Server 返回頁面URL
        $orge = 'billno'.$order_num.'currencytype'.$data['Currency_Type'].'amount'.$data['Amount'].'date'.$data['Date'].'orderencodetype'.$data['OrderEncodeType'].$pay_key ;
        $data['SignMD5'] =  md5($orge);//Server to Server 返回頁面URL */




		$Mer_code = $pay_id;//商戶號
		$Mer_key = $pay_key;//商戶證書：登陸http://merchant.ips.com.cn/商戶後臺下載的商戶證書內容
		$Billno = $order_num;//商戶訂單編號
		$Amount = number_format($s_amount, 2, '.', '');//訂單金額(保留2位小數)
		$Date = date('Ymd');//訂單日期
		$Currency_Type = 'RMB';//幣種
		$Gateway_Type = 01;//支付卡種
		$Lang = 'GB';//語言
		$Merchanturl = $ServerUrl;//支付結果成功返回的商戶URL
		$FailUrl = $ServerUrl;//支付結果失敗返回的商戶URL
		$ErrorUrl = $ServerUrl;//支付結果錯誤返回的商戶URL
		$Attach = '';//商戶數據包
		$DispAmount = $s_amount;//顯示金額
		$OrderEncodeType = 5;//訂單支付接口加密方式
		$RetEncodeType = 17;//交易返回接口加密方式
		$Rettype = 1;//返回方式
		$ServerUrl = $ServerUrl;//Server to Server 返回頁面URL
		$orge = 'billno'.$Billno.'currencytype'.$Currency_Type.'amount'.$Amount.'date'.$Date.'orderencodetype'.$OrderEncodeType.$Mer_key ;

		$SignMD5 = md5($orge) ;
		//var_dump($SignMD5);die;
		$data['Mer_code'] = $Mer_code;
		$data['Billno'] = $Billno;
		$data['Amount'] = $Amount;
		$data['Date'] = $Date;
		$data['Currency_Type'] = $Currency_Type;
		$data['Gateway_Type'] = $Gateway_Type;
		$data['Lang'] = $Lang;
		$data['Merchanturl'] = $Merchanturl;
		$data['FailUrl'] = $FailUrl;
		$data['ErrorUrl'] = $ErrorUrl;
		$data['Attach'] = $Attach;
		$data['DispAmount'] = $DispAmount;
		$data['OrderEncodeType'] = $OrderEncodeType;
		$data['RetEncodeType'] = $RetEncodeType;
		$data['RetEncodeType'] = $RetEncodeType;
		$data['Rettype'] = $Rettype;
		$data['ServerUrl'] = $ServerUrl;
		$data['SignMD5'] = $SignMD5;

		return $data;
	}
}