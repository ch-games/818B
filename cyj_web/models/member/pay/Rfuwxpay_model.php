<?php if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Rfuwxpay_model extends Online_api_model {
	function __construct() {
		parent::__construct();
		$this->init_db();
	}

	function get_all_info($url,$order_num,$s_amount,$bank,$pay_id,$pay_key,$vircarddoin,$goods){
		$data=array();
		$req_url = 'http://'.$url.'/index.php/pay/payfor'; //第三方地址
		$ServerUrl = 'http://'.$url.'/index.php/pay/rfupay_callback'; //商戶後臺通知地址
		$return_url = 'http://'.$url.'/index.php/pay/return_url';//同步通知結果
		$form_url = 'http://payment.rfupay.com/prod/commgr/control/inPayService'; //跳轉地址
		$data = array();
		$data['req_url'] = $req_url;
		$data['form_url'] = $form_url;
		$data['partyId'] = $pay_id;
		$data['accountId'] = $vircarddoin;
		$data['goods'] = $goods;
		$data['orderNo'] = $data['goods'].$order_num;
		$data['orderAmount'] = $s_amount;
		$data['returnUrl'] = $return_url;
		$data['cardType'] = "01";
		$data['bank'] = $bank;
		$data['encodeType'] = "MD5";
		if($bank == "wechat"){
			$data['appType'] = "WECHAT";

		}else{
			$data['appType'] = "";
		}
		$data['checkUrl'] = $ServerUrl;
		$data['refCode'] = "00000000";
		$md5Key = $pay_key;
		$pStr = "orderNo" . $data['orderNo'] . "appType" . $data['appType'] . "orderAmount" . $data['orderAmount'] . "encodeType" . $data['encodeType'].$md5Key;
		$pStrMd5=md5($pStr);
		$signMD5 = strtolower($pStrMd5);
		$myvars = 'partyId=' . $data['partyId'] . '&appType=' . $data['appType'] . '&orderNo=' . $data['orderNo'] . '&refCode=' . $data['refCode'] . '&orderAmount=' . $data['orderAmount'] . '&goods=' . $data['goods'] . '&returnUrl=' . $data['returnUrl'] . '&cardType=' . $data['cardType'] . '&bank=' . $data['bank'] . '&encodeType=' . $data['encodeType'] . '&accountId=' . $data['accountId'] . '&signMD5=' . $signMD5. '&checkUrl=' . $ServerUrl;
		$data['form_url'] = $form_url;
		$data['myvars'] = $myvars;
		$data['act'] = "rfupay";
		return $data;

	}


	function test_input($data) {
		$data = trim($data);
		$data = stripslashes($data);
		$data = htmlspecialchars($data);
		return $data;
	}

	public function get_wx_info(){
		$map = array();
		$map['table'] = "pay_set";
		$map['select'] = "*";
		$map['where']['is_delete'] = 0;
		$map['where']['site_id'] = SITEID;
		$map['where']['index_id']=INDEX_ID;
		$map['where']['is_card'] = 0;
		$map['where']['is_wechat'] = 1;
		$result = $this->rfind($map);
		p($result);die;

	}
}