<?php if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Yeepay_model extends Online_api_model {
	function __construct() {
		parent::__construct();
		$this->init_db();
		//$this->load->library('payapi/HttpClient');
		//$this->load->model('Common_model');
	}

	#簽名函數生成簽名串
	function getReqHmacString($p2_Order,$p3_Amt,$p4_Cur,$p5_Pid,$p6_Pcat,$p7_Pdesc,$p8_Url,$pa_MP,$pd_FrpId,$pr_NeedResponse,$p1_MerId,$merchantKey){
		$p0_Cmd = "Buy";
		$p9_SAF = "0";
		#進行簽名處理，壹定按照文檔中標明的簽名順序進行
		$sbOld = "";
		$sbOld = $sbOld.$p0_Cmd;#加入業務類型
		$sbOld = $sbOld.$p1_MerId;#加入商戶編號
		$sbOld = $sbOld.$p2_Order;#加入商戶訂單號
		$sbOld = $sbOld.$p3_Amt;#加入支付金額
		$sbOld = $sbOld.$p4_Cur; #加入交易幣種
		$sbOld = $sbOld.$p5_Pid; #加入商品名稱
		$sbOld = $sbOld.$p6_Pcat;#加入商品分類
		$sbOld = $sbOld.$p7_Pdesc;#加入商品描述
		$sbOld = $sbOld.$p8_Url;#加入商戶接收支付成功數據的地址
		$sbOld = $sbOld.$p9_SAF;#加入送貨地址標識
		$sbOld = $sbOld.$pa_MP;#加入商戶擴展信息
		$sbOld = $sbOld.$pd_FrpId;#加入支付通道編碼
		$sbOld = $sbOld.$pr_NeedResponse;#加入是否需要應答機制
		return $this->HmacMd5($sbOld,$merchantKey);

	}

	function getCallbackHmacString($r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType,$p1_MerId,$merchantKey){
		#取得加密前的字符串
		$sbOld = "";
		$sbOld = $sbOld.$p1_MerId;#加入商家ID
		$sbOld = $sbOld.$r0_Cmd;#加入消息類型
		$sbOld = $sbOld.$r1_Code;#加入業務返回碼
		$sbOld = $sbOld.$r2_TrxId;#加入交易ID
		$sbOld = $sbOld.$r3_Amt;#加入交易金額
		$sbOld = $sbOld.$r4_Cur;#加入貨幣單位
		$sbOld = $sbOld.$r5_Pid;#加入產品Id
		$sbOld = $sbOld.$r6_Order;#加入訂單ID
		$sbOld = $sbOld.$r7_Uid;#加入用戶ID
		$sbOld = $sbOld.$r8_MP;#加入商家擴展信息
		$sbOld = $sbOld.$r9_BType;#加入交易結果返回類型
		return $this->HmacMd5($sbOld,$merchantKey);

	}



	function CheckHmac($r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType,$hmac,$p1_MerId,$merchantKey)
	{
		if($hmac==$this->getCallbackHmacString($r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType,$p1_MerId,$merchantKey))
			return true;
		else
			return false;
	}


	function HmacMd5($data,$key)
	{
		// RFC 2104 HMAC implementation for php.
		// Creates an md5 HMAC.
		// Eliminates the need to install mhash to compute a HMAC
		// Hacked by Lance Rushing(NOTE: Hacked means written)

		//需要配置環境支持iconv，否則中文參數不能正常處理
		$key = iconv("GB2312","UTF-8",$key);
		$data = iconv("GB2312","UTF-8",$data);

		$b = 64; // byte length for md5
		if (strlen($key) > $b) {
			$key = pack("H*",md5($key));
		}
		$key = str_pad($key, $b, chr(0x00));
		$ipad = str_pad('', $b, chr(0x36));
		$opad = str_pad('', $b, chr(0x5c));
		$k_ipad = $key ^ $ipad ;
		$k_opad = $key ^ $opad;

		return md5($k_opad . pack("H*",md5($k_ipad . $data)));
	}



	function get_all_info($url,$order_num,$s_amount,$bank,$pay_id,$pay_key){
		$req_url = 'http://'.$url.'/index.php/pay/payfor';//跳轉地址
		$ServerUrl = 'http://'.$url.'/index.php/pay/yeepay_callback';//商戶後臺通知地址
		$form_url = 'http://www.yeepay.com/app-merchant-proxy/node';//第三方地址
		$data=array();

		$p0_Cmd = "Buy";
		$p2_Order					= $order_num;//訂單號
		$p3_Amt						= $s_amount;//支付金額
		$p4_Cur						= "CNY";//交易幣種,固定值"CNY".
		$p5_Pid						= '';//商品名稱
		$p6_Pcat					= '';//商品種類
		$p7_Pdesc					= '';//商品描述

		$p8_Url						= $ServerUrl;//$result['f_url'].'tyeepay/tyeepay_callback.php';	//商戶接收支付成功數據的地址,支付成功後易寶支付會向該地址發送兩次成功通知.
		$pa_MP						= '';//商戶擴展信息
		$pd_FrpId					= $bank;//支付通道編碼,即銀行卡
		$p9_SAF                     = '0';//送貨地址為“1”: 需要用戶將送貨地址留在易寶支付系統;為“0”: 不需要，默認為”0”
		$pr_NeedResponse	= "1";//應答機制  默認為"1": 需要應答機制;

		#調用簽名函數生成簽名串
		$hmac = $this->getReqHmacString($p2_Order,$p3_Amt,$p4_Cur,$p5_Pid,$p6_Pcat,$p7_Pdesc,$p8_Url,$pa_MP,$pd_FrpId,$pr_NeedResponse,$pay_id,$pay_key);

		$data['req_url'] = $req_url;
		$data['data'] = $dataf['data'];
		$data['form_url'] = $form_url;
		$data['p0_Cmd'] = $p0_Cmd;
		$data['p1_MerId'] = $pay_id;
		$data['p2_Order'] = $order_num;
		$data['p3_Amt'] = $p3_Amt;
		$data['p4_Cur'] = $p4_Cur;
		$data['p5_Pid'] = $p5_Pid;
		$data['p6_Pcat'] = $p6_Pcat;
		$data['p7_Pdesc'] = $p7_Pdesc;
		$data['p8_Url'] = $p8_Url;
		$data['p9_SAF'] = $p9_SAF;
		$data['pa_MP'] = $pa_MP;
		$data['pd_FrpId'] = $pd_FrpId;
		$data['pr_NeedResponse'] = $pr_NeedResponse;
		$data['hmac'] = $hmac;
		$data['merchantKey'] = $pay_key;
		return $data;

	}

}