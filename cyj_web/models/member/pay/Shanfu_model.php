<?php if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Shanfu_model extends Online_api_model {
	function __construct() {
		parent::__construct();
		$this->init_db();

	}
	function get_all_info($url,$order_num,$s_amount,$bank,$pay_id,$pay_key,$username,$terminalid){
		$form_url = 'http://gw.3yzf.com/v4.aspx';//第三方地址
		$req_url = 'http://'.$url.'/index.php/pay/payfor';//跳轉地址
		$ServerUrl = 'http://'.$url.'/index.php/pay/shanfu_callback';//商戶後臺通知地址
		$return_url = 'http://'.$url.'/index.php/pay/return_url';
		$data=array();
		$data['req_url'] = $req_url;
		$data['ServerUrl'] = $ServerUrl;
		$data['form_url'] = $form_url;
		$data['MemberID'] = $pay_id;//商戶號
		$data['TransID']=$order_num;//流水號
		$data['PayID'] = $bank;//銀行參數
		$data['TradeDate'] = date('YmdHis');//交易時間
		$data['OrderMoney'] = $s_amount*100;//訂單金額
		$data['ProductName'] = '';//產品名稱
		$data['Amount'] = 1;//商品數量
		$data['Username'] = $username;//支付用戶名
		$data['AdditionalInfo'] = '';//訂單附加消息
		$data['PageUrl'] = $return_url;//通知商戶頁面端地址
		$data['ReturnUrl'] = $ServerUrl;//服務器底層通知地址
		$data['NoticeType'] = 1;//通知類型
		$data['Md5key'] = $pay_key;//md5密鑰（KEY）
		$MARK = "|";
		//MD5簽名格式
		$Signature=md5($pay_id.$MARK.$bank.$MARK.$data['TradeDate'].$MARK.$order_num.$MARK.$data['OrderMoney'].$MARK.$data['PageUrl'].$MARK.$data['ReturnUrl'].$MARK.$data['NoticeType'].$MARK.$data['Md5key']);
		$payUrl="http://gw.3yzf.com/v4.aspx";//借貸混合
		$data['TerminalID'] = $terminalid;//終端ID
		$data['InterfaceVersion'] = "4.0";
		$data['Signature'] = $Signature;
		$data['KeyType'] = "1";
		return $data;
	}
}