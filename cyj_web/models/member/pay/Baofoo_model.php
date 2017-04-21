<?php if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Baofoo_model extends Online_api_model {
	function __construct() {
		parent::__construct();
		$this->init_db();
		$this->load->library('payapi/SdkXML');
		$this->load->library('payapi/BFRSA');
	}
	function get_all_info($url,$order_num,$s_amount,$bank,$pay_id,$pay_key,$username,$terminalid,$goods="",$public_key="",$key_domain="",$file_key=""){
		if($bank=="weixin"){
			return $this->get_all_info_weixin($url,$order_num,$s_amount,$bank,$pay_id,$pay_key,$username,$terminalid,$goods,$public_key,$key_domain,$file_key);
		}
		$req_url = 'http://'.$url.'/index.php/pay/payfor';//跳轉地址
		$ServerUrl = 'http://'.$url.'/index.php/pay/baofoo_callback';//商戶後臺通知地址
		$return_url = 'http://'.$url.'/index.php/pay/return_url';
		$form_url = 'https://gw.baofoo.com/payindex ';//第三方地址
		$data=array();
		$data['req_url'] = $req_url;
		$data['ServerUrl'] = $ServerUrl;
		$data['form_url'] = $form_url;
		$data['MemberID'] = $pay_id;//商戶號
		$data['TransID'] = $order_num;//流水號
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
		$Signature=md5($pay_id.$MARK.$bank.$MARK.$data['TradeDate'].$MARK.$order_num.$MARK.$data['OrderMoney'].$MARK.$return_url.$MARK.$ServerUrl.$MARK.$data['NoticeType'].$MARK.$pay_key);
		$payUrl=$form_url;//"http://".$result['pay_domain'];//借貸混合
		$data['TerminalID'] = $terminalid;//終端ID
		$data['InterfaceVersion'] = "4.0";
		$data['Signature'] = $Signature;
		$data['KeyType'] = "1";
		return $data;
	}


	function get_all_info_weixin($url,$order_num,$s_amount,$bank,$pay_id,$pay_key,$username,$terminalid,$goods,$public_key,$key_domain,$file_key){
		$private_key_password = $file_key;//文件密碼默認為123456
		$req_url = 'http://'.$url.'/index.php/pay/payfor';//跳轉地址
		$ServerUrl = 'http://'.$url.'/index.php/pay/baofoo_wx_callback';//
		$page_url = 'http://'.$url.'/index.php/pay/return_url';//商戶後臺通知地址
		$form_url = 'https://public.baofoo.com/platform/gateway/front';//第三方地址
		$txn_amt = $s_amount*100;//訂單金額
		$data=array();
		//================報文組裝=================================
		$DataContentParms =ARRAY();
		$DataContentParms["txn_sub_type"] ="01";//交易子類
		$DataContentParms["member_id"] = $pay_id;//商戶號
		$DataContentParms["terminal_id"] = $goods;//終端號
		$DataContentParms["trans_id"] = $order_num;
		$DataContentParms["trans_serial_no"] = "PHPTSN".($this->get_transid()).($this->rand4());
		$DataContentParms["txn_amt"] = $txn_amt;//訂單金額
		$DataContentParms["trade_date"] = $this->return_time();
		$DataContentParms["commodity_name"] = "商品名稱";
		$DataContentParms["commodity_amount"] = "1";//商品數量
		$DataContentParms["user_id"] = $username ;//平臺USER_ID(商戶傳)
		$DataContentParms["user_name"] = $username ;//平臺用戶姓名
		$DataContentParms["notice_type"] = 1;

		$DataContentParms["page_url"] = $page_url ;//頁面通知地址
		$DataContentParms["return_url"] = $ServerUrl;//異步接收通知地址。
		$DataContentParms["additional_info"] = "附加信息";
		$DataContentParms["req_reserved"] = "保留" ;
		$data_type="xml";//加密報文的數據類型（xml/json）
		//==================轉換數據類型=============================================
		if($data_type == "json"){
			$Encrypted_string = str_replace("\\/", "/",json_encode($DataContentParms,TRUE));//轉JSON
		}else{
			$toxml = new SdkXML();	//實例化XML轉換類
			$Encrypted_string = $toxml->toXml($DataContentParms);//轉XML
		}
		$public_key = $public_key;
		$private_key = $_SERVER['DOCUMENT_ROOT']."/public/key/".$key_domain;
		$BFRsa = new BFRSA($private_key,$public_key,$private_key_password); //實例化加密類。
		$Encrypted = $BFRsa->encryptedByPrivateKey($Encrypted_string);	//先BASE64進行編碼再RSA加密
		$version = "4.0.0.0";//版本號
		$txn_type = "10199";
		$txn_sub_type = "01";
		$data['form_url'] = $form_url;
		$data['req_url'] = $req_url;
		$data['version'] = $version;
		$data['txn_type'] = $txn_type;
		$data['txn_sub_type'] = $txn_sub_type;
		$data['terminalid'] = $goods;
		$data['pay_id'] = $pay_id;
		$data['data_type'] = $data_type;
		$data['data_content'] = $Encrypted;
		return $data;
	}


	function get_transid(){//生成時間戳
		return strtotime(date('Y-m-d H:i:s',time()));
	}
	function rand4(){//生成四位隨機數
		return rand(1000,9999);
	}
	function return_time(){//生成時間
		return date('YmdHis',time());
	}


}