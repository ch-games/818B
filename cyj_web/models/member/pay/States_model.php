<?php if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class States_model extends Online_api_model {
	function __construct() {
		parent::__construct();
		$this->init_db();
	}
	function get_all_info($url,$order_num,$s_amount,$bank,$pay_id,$pay_key,$vircarddoin){
		$req_url = 'http://'.$url.'/index.php/pay/payfor';//跳轉地址
		$ServerUrl = 'http://'.$url.'/index.php/pay/states_callback';//商戶後臺通知地址
		$form_url = 'https://gateway.gopay.com.cn/Trans/WebClientAction.do';//第三方地址
		$return_url = 'http://'.$url.'/index.php/pay/return_url';
		$frontMerUrl = $return_url;     //商戶前臺通知地址
		$backgroundMerUrl = $ServerUrl; //商戶後臺通知地址
		$data=array();
		$data['req_url'] = $req_url;
		$data['form_url'] = $form_url;
		$data['frontMerUrl'] = $return_url;
		$data['backgroundMerUrl'] = $ServerUrl;
		$data['version'] = 2.1; //網關版本號
		$data['charset'] = 2;   //字符集 1:GBK,2:UTF-8 (不填則當成1處理)
		$data['language'] = 1;       //網關語言版本  1:ZH,2:EN
		$data['signType'] = 1;  //報文加密方式 1:MD5,2:SHA
		$data['tranCode'] = 8888; //交易代碼
		$data['merchantID'] = $pay_id;      //商戶ID
		$data['merOrderNum'] = $order_num;//訂單號
		$data['tranAmt'] = $s_amount;   //交易金額
		$data['feeAmt'] = '';           //商戶提取傭金金額
		$data['currencyType'] = 156;  //幣種
		$data['tranDateTime'] = date('YmdHis');    //交易時間
		$data['virCardNoIn'] = $vircarddoin;//   國付寶賬號
		$data['tranIP'] = '127.0.0.1';//'127.0.0.1';
		$data['isRepeatSubmit'] = 0;   //訂單是否允許重復 0不允許 1允許（默認）
		$data['goodsName'] = '';//商品名稱
		$data['goodsDetail'] = '';//商品詳情
		$data['buyerName'] = ''; //買家姓名
		$data['buyerContact'] = '';//買家聯系方式
		$data['merRemark1'] = '';//商戶備用信息字段
		$data['merRemark2'] = '';      //商戶備用信息字段
		$data['bankCode'] = $bank;         //銀行代碼
		$data['userType'] =  1;//$_POST["userType"];         //用戶類型
		$data['gopayServerTime'] = '';//HttpClient::getGopayServerTime();
		$data['Mer_key'] = $pay_key;//秘鑰
		$signStr='version=['.$data['version'].']tranCode=['.$data['tranCode'].']merchantID=['.$data['merchantID'].']merOrderNum=['.$data['merOrderNum'].']tranAmt=['.$data['tranAmt'].']feeAmt=['.$data['feeAmt'].']tranDateTime=['.$data['tranDateTime'].']frontMerUrl=['.$data['frontMerUrl'].']backgroundMerUrl=['.$data['backgroundMerUrl'].']orderId=[]gopayOutOrderId=[]tranIP=['.$data['tranIP'].']respCode=[]gopayServerTime=['.$data['gopayServerTime'].']VerficationCode=['.$pay_key.']';
		//VerficationCode是商戶識別碼為用戶重要信息請妥善保存
		//註意調試生產環境時需要修改這個值為生產參數
		$signValue = md5($signStr);
		$data['signValue'] = $signValue;
		return $data;
	}
}