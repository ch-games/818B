<?php if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Ecpss_model extends Online_api_model {
	function __construct() {
		parent::__construct();
		$this->init_db();

	}
	function get_all_info($url,$order_num,$s_amount,$bank,$pay_id,$pay_key){
		$req_url = 'http://'.$url.'/index.php/pay/payfor';//跳轉地址
		$ServerUrl = 'http://'.$url.'/index.php/pay/ecpss_callback';//商戶後臺通知地址
		$return_url = 'http://'.$url.'/index.php/pay/return_url';
		$form_url = 'https://pay.ecpss.com/sslpayment';//第三方地址
		$data=array();
		$data['req_url'] = $req_url;
		$data['ServerUrl'] = $ServerUrl;
		$data['form_url'] = $form_url;
		$data['MD5key'] = $pay_key;		//MD5私鑰
		$data['MerNo'] = $pay_id;					//商戶號
		$data['BillNo'] = $order_num;		//[必填]訂單號(商戶自己產生：要求不重復)
		$data['Amount'] = $s_amount;				//[必填]訂單金額
		$data['ReturnURL'] = $ServerUrl; 			//[必填]返回數據給商戶的地址(商戶自己填寫):::註意請在測試前將該地址告訴我方人員;否則測試通不過
		$data['Remark'] = "";  //[選填]升級。
		$md5src = $pay_id."&".$order_num."&".$s_amount."&".$ServerUrl."&".$pay_key;		//校驗源字符串
		$data['SignInfo'] = strtoupper(md5($md5src));		//MD5檢驗結果
		$data['AdviceURL'] =$ServerUrl;   //[必填]支付完成後，後臺接收支付結果，可用來更新數據庫值
		$data['orderTime'] =date('YYYYMMDDHHMMSS');   //[必填]交易時間YYYYMMDDHHMMSS
		$data['defaultBankNumber'] =$bank;   //[選填]銀行代碼s
		//送貨信息(方便維護，請盡量收集！如果沒有以下信息提供，請傳空值:'')
		//因為關系到風險問題和以後商戶升級的需要，如果有相應或相似的內容的壹定要收集，實在沒有的才賦空值,謝謝。
		$data['products']="products info";// '------------------物品信息
		return $data;
	}
}