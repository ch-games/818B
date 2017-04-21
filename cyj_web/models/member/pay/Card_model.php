<?php if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Card_model extends Online_api_model {
	function __construct() {
		parent::__construct();
		$this->init_db();
	}

	function getReqHmacString($p0_Cmd,$p2_Order,$p3_Amt,$p4_verifyAmt,$p5_Pid,$p6_Pcat,$p7_Pdesc,$p8_Url,$pa_MP,$pa7_cardAmt,$pa8_cardNo,$pa9_cardPwd,$pd_FrpId,$pr_NeedResponse,$pz_userId,$pz1_userRegTime,$p1_MerId,$merchantKey)
	{

		#進行加密串處理，壹定按照下列順序進行
		$sbOld		=	"";
		#加入業務類型
		$sbOld		=	$sbOld.$p0_Cmd;
		#加入商戶代碼
		$sbOld		=	$sbOld.$p1_MerId;
		#加入商戶訂單號
		$sbOld		=	$sbOld.$p2_Order;
		#加入支付卡面額
		$sbOld		=	$sbOld.$p3_Amt;
		#是否較驗訂單金額
		$sbOld		=	$sbOld.$p4_verifyAmt;
		#產品名稱
		$sbOld		=	$sbOld.$p5_Pid;
		#產品類型
		$sbOld		=	$sbOld.$p6_Pcat;
		#產品描述
		$sbOld		=	$sbOld.$p7_Pdesc;
		#加入商戶接收交易結果通知的地址
		$sbOld		=	$sbOld.$p8_Url;
		#加入臨時信息
		$sbOld 		= $sbOld.$pa_MP;
		#加入卡面額組
		$sbOld 		= $sbOld.$pa7_cardAmt;
		#加入卡號組
		$sbOld		=	$sbOld.$pa8_cardNo;
		#加入卡密組
		$sbOld		=	$sbOld.$pa9_cardPwd;
		#加入支付通道編碼
		$sbOld		=	$sbOld.$pd_FrpId;
		#加入應答機制
		$sbOld		=	$sbOld.$pr_NeedResponse;
		#加入用戶ID
		$sbOld		=	$sbOld.$pz_userId;
		#加入用戶註冊時間
		$sbOld		=	$sbOld.$pz1_userRegTime;

		return $this->HmacMd5($sbOld,$merchantKey);

	}


	function annulCard($p2_Order,$p3_Amt,$p4_verifyAmt,$p5_Pid,$p6_Pcat,$p7_Pdesc,$p8_Url,$pa_MP,$pa7_cardAmt,$pa8_cardNo,$pa9_cardPwd,$pd_FrpId,$pz_userId,$pz1_userRegTime,$p1_MerId,$merchantKey,$reqURL_SNDApro){
		$this->load->library('payapi/HttpClient.php');

		/*global $db_config;
        include 'yeecar_config.php';*/
		/*include_once 'yeecarHttpClient.class.php';*/
		# 非銀行卡支付專業版支付請求，固定值 "ChargeCardDirect".
		$p0_Cmd					= "ChargeCardDirect";

		#應答機制.為"1": 需要應答機制;為"0": 不需要應答機制.
		$pr_NeedResponse	= "1";

		#調用簽名函數生成簽名串
		$hmac	= $this->getReqHmacString($p0_Cmd,$p2_Order,$p3_Amt,$p4_verifyAmt,$p5_Pid,$p6_Pcat,$p7_Pdesc,$p8_Url,$pa_MP,$pa7_cardAmt,$pa8_cardNo,$pa9_cardPwd,$pd_FrpId,$pr_NeedResponse,$pz_userId,$pz1_userRegTime,$p1_MerId,$merchantKey);
		//$reqURL_SNDApro =  "http://www.yeepay.com/app-merchant-proxy/command.action";
		#進行加密串處理，壹定按照下列順序進行
		$params = array(
			#加入業務類型
			'p0_Cmd'						=>	$p0_Cmd,
			#加入商家ID
			'p1_MerId'					=>	$p1_MerId,
			#加入商戶訂單號
			'p2_Order' 					=>	$p2_Order,
			#加入支付卡面額
			'p3_Amt'						=>	$p3_Amt,
			#加入是否較驗訂單金額
			'p4_verifyAmt'						=>	$p4_verifyAmt,
			#加入產品名稱
			'p5_Pid'						=>	$p5_Pid,
			#加入產品類型
			'p6_Pcat'						=>	$p6_Pcat,
			#加入產品描述
			'p7_Pdesc'						=>	$p7_Pdesc,
			#加入商戶接收交易結果通知的地址
			'p8_Url'						=>	$p8_Url,
			#加入臨時信息
			'pa_MP'					  	=> 	$pa_MP,
			#加入卡面額組
			'pa7_cardAmt'				=>	$pa7_cardAmt,
			#加入卡號組
			'pa8_cardNo'				=>	$pa8_cardNo,
			#加入卡密組
			'pa9_cardPwd'				=>	$pa9_cardPwd,
			#加入支付通道編碼
			'pd_FrpId'					=>	$pd_FrpId,
			#加入應答機制
			'pr_NeedResponse'		=>	$pr_NeedResponse,
			#加入校驗碼
			'hmac' 							=>	$hmac,
			#用戶唯壹標識
			'pz_userId'			=>	$pz_userId,
			#用戶的註冊時間
			'pz1_userRegTime' 		=>	$pz1_userRegTime
		);
		$HttpClient = new HttpClient();
		$pageContents	= $HttpClient->quickPost($reqURL_SNDApro, $params);
		$result 				= explode("\n",$pageContents);
		//var_dump($result);die;

		$r0_Cmd				=	"";							#業務類型
		$r1_Code			=	"";							#支付結果
		$r2_TrxId			=	"";							#易寶支付交易流水號
		$r6_Order			=	"";							#商戶訂單號
		$rq_ReturnMsg	=	"";							#返回信息
		$hmac					=	"";					 	  #簽名數據
		$unkonw				= "";							#未知錯誤


		for($index=0;$index<count($result);$index++){		//數組循環
			$result[$index] = trim($result[$index]);
			if (strlen($result[$index]) == 0) {
				continue;
			}
			$aryReturn		= explode("=",$result[$index]);
			$sKey					= $aryReturn[0];
			$sValue				= $aryReturn[1];
			if($sKey			=="r0_Cmd"){				#取得業務類型
				$r0_Cmd				= $sValue;
			}elseif($sKey == "r1_Code"){			        #取得支付結果
				$r1_Code			= $sValue;
			}elseif($sKey == "r2_TrxId"){			        #取得易寶支付交易流水號
				$r2_TrxId			= $sValue;
			}elseif($sKey == "r6_Order"){			        #取得商戶訂單號
				$r6_Order			= $sValue;
			}elseif($sKey == "rq_ReturnMsg"){				#取得交易結果返回信息
				$rq_ReturnMsg	= $sValue;
			}elseif($sKey == "hmac"){						#取得簽名數據
				$hmac 				= $sValue;
			} else{
				return $result[$index];
			}
		}


		#進行校驗碼檢查 取得加密前的字符串
		$sbOld="";
		#加入業務類型
		$sbOld = $sbOld.$r0_Cmd;
		#加入支付結果
		$sbOld = $sbOld.$r1_Code;
		#加入易寶支付交易流水號
		#$sbOld = $sbOld.$r2_TrxId;
		#加入商戶訂單號
		$sbOld = $sbOld.$r6_Order;
		#加入交易結果返回信息
		$sbOld = $sbOld.$rq_ReturnMsg;
		$sNewString = $this->HmacMd5($sbOld,$merchantKey);
		#校驗碼正確
		if($sNewString==$hmac) {
			if($r1_Code=="1"){
				echo "<br>提交成功!".$rq_ReturnMsg;
				echo "<br>商戶訂單號:".$r6_Order."<br>";
				#echo generationTestCallback($p2_Order,$p3_Amt,$p8_Url,$pa7_cardNo,$pa8_cardPwd,$pz_userId,$pz1_userRegTime);
				return;
			} else if($r1_Code=="2"){
				echo "<br>提交失敗".$rq_ReturnMsg;
				echo "<br>支付卡密無效!";
				return;
			} else if($r1_Code=="7"){
				echo "<br>提交失敗".$rq_ReturnMsg;
				echo "<br>支付卡密無效!";
				return;
			} else if($r1_Code=="11"){
				echo "<br>提交失敗".$rq_ReturnMsg;
				echo "<br>訂單號重復!";
				return;
			} else{
				echo "<br>提交失敗".$rq_ReturnMsg;
				echo "<br>請檢查後重新支付";
				return;
			}
		} else{
			echo "<br>localhost:".$sNewString;
			echo "<br>YeePay:".$hmac;
			echo "<br>交易簽名無效!";
			exit;
		}
	}

	function generationTestCallback($p2_Order,$p3_Amt,$p8_Url,$pa7_cardNo,$pa8_cardPwd,$pa_MP,$pz_userId,$pz1_userRegTime,$p1_MerId)
	{
		$this->load->library('payapi/HttpClient.php');
		# 非銀行卡支付專業版支付請求，固定值 "AnnulCard".
		$p0_Cmd					= "AnnulCard";

		#應答機制.為"1": 需要應答機制;為"0": 不需要應答機制.
		$pr_NeedResponse	= "1";

		# 非銀行卡支付專業版請求地址,無需更改.
		#$reqURL_SNDApro		= "https://www.yeepay.com/app-merchant-proxy/command.action";
		$reqURL_SNDApro		= "http://tech.yeepay.com:8080/robot/generationCallback.action";
		#調用簽名函數生成簽名串
		#$hmac	= getReqHmacString($p0_Cmd,$p2_Order,$p3_Amt,$p4_verifyAmt,$p5_Pid,$p6_Pcat,$p7_Pdesc,$p8_Url,$pa_MP,$pa7_cardAmt,$pa8_cardNo,$pa9_cardPwd,$pd_FrpId,$pr_NeedResponse,$pz_userId,$pz1_userRegTime);
		#進行加密串處理，壹定按照下列順序進行
		$params = array(
			#加入業務類型
			'p0_Cmd'						=>	$p0_Cmd,
			#加入商家ID
			'p1_MerId'					=>	$p1_MerId,
			#加入商戶訂單號
			'p2_Order' 					=>	$p2_Order,
			#加入支付卡面額
			'p3_Amt'						=>	$p3_Amt,
			#加入商戶接收交易結果通知的地址
			'p8_Url'						=>	$p8_Url,
			#加入支付卡序列號
			'pa7_cardNo'				=>	$pa7_cardNo,
			#加入支付卡密碼
			'pa8_cardPwd'				=>	$pa8_cardPwd,
			#加入支付通道編碼
			'pd_FrpId'					=>	$pd_FrpId,
			#加入應答機制
			'pr_NeedResponse'		=>	$pr_NeedResponse,
			#加入應答機制
			'pa_MP'							=>	$pa_MP,
			#用戶唯壹標識
			'pz_userId'			=>	$pz_userId,
			#用戶的註冊時間
			'pz1_userRegTime' 		=>	$pz1_userRegTime);
		$HttpClient = new HttpClient();
		$HttpClient ->HttpClient($reqURL_SNDApro, $port=80);
		$pageContents	= $HttpClient->quickPost($reqURL_SNDApro, $params);
		return $pageContents;
	}


#調用簽名函數生成簽名串.
	function getCallbackHmacString($r0_Cmd,$r1_Code,$p1_MerId,$p2_Order,$p3_Amt,$p4_FrpId,$p5_CardNo,
								   $p6_confirmAmount,$p7_realAmount,$p8_cardStatus,$p9_MP,$pb_BalanceAmt,$pc_BalanceAct,$merchantKey)
	{
		#進行校驗碼檢查 取得加密前的字符串
		$sbOld="";
		#加入業務類型
		$sbOld = $sbOld.$r0_Cmd;
		$sbOld = $sbOld.$r1_Code;
		$sbOld = $sbOld.$p1_MerId;
		$sbOld = $sbOld.$p2_Order;
		$sbOld = $sbOld.$p3_Amt;
		$sbOld = $sbOld.$p4_FrpId;
		$sbOld = $sbOld.$p5_CardNo;
		$sbOld = $sbOld.$p6_confirmAmount;
		$sbOld = $sbOld.$p7_realAmount;
		$sbOld = $sbOld.$p8_cardStatus;
		$sbOld = $sbOld.$p9_MP;
		$sbOld = $sbOld.$pb_BalanceAmt;
		$sbOld = $sbOld.$pc_BalanceAct;
		return $this->HmacMd5($sbOld,$merchantKey);

	}


#取得返回串中的所有參數.
	function getCallBackValue(&$r0_Cmd,&$r1_Code,&$p1_MerId,&$p2_Order,&$p3_Amt,&$p4_FrpId,&$p5_CardNo,&$p6_confirmAmount,&$p7_realAmount,
							  &$p8_cardStatus,&$p9_MP,&$pb_BalanceAmt,&$pc_BalanceAct,&$hmac)
	{

		$r0_Cmd = $_REQUEST['r0_Cmd'];
		$r1_Code = $_REQUEST['r1_Code'];
		$p1_MerId = $_REQUEST['p1_MerId'];
		$p2_Order = $_REQUEST['p2_Order'];
		$p3_Amt = $_REQUEST['p3_Amt'];
		$p4_FrpId = $_REQUEST['p4_FrpId'];
		$p5_CardNo = $_REQUEST['p5_CardNo'];
		$p6_confirmAmount = $_REQUEST['p6_confirmAmount'];
		$p7_realAmount = $_REQUEST['p7_realAmount'];
		$p8_cardStatus = $_REQUEST['p8_cardStatus'];
		$p9_MP = $_REQUEST['p9_MP'];
		$pb_BalanceAmt = $_REQUEST['pb_BalanceAmt'];
		$pc_BalanceAct = $_REQUEST['pc_BalanceAct'];
		$hmac = $_REQUEST['hmac'];

		return null;

	}


#驗證返回參數中的hmac與商戶端生成的hmac是否壹致.
	function CheckHmac($r0_Cmd,$r1_Code,$p1_MerId,$p2_Order,$p3_Amt,$p4_FrpId,$p5_CardNo,$p6_confirmAmount,$p7_realAmount,$p8_cardStatus,$p9_MP,$pb_BalanceAmt,
					   $pc_BalanceAct,$hmac,$merchantKey)
	{
		if($hmac==$this->getCallbackHmacString($r0_Cmd,$r1_Code,$p1_MerId,$p2_Order,$p3_Amt,
				$p4_FrpId,$p5_CardNo,$p6_confirmAmount,$p7_realAmount,$p8_cardStatus,$p9_MP,$pb_BalanceAmt,$pc_BalanceAct,$merchantKey))
			return true;
		else
			return false;

	}


	function HmacMd5($data,$key)
	{
		# RFC 2104 HMAC implementation for php.
		# Creates an md5 HMAC.
		# Eliminates the need to install mhash to compute a HMAC
		# Hacked by Lance Rushing(NOTE: Hacked means written)

		#需要配置環境支持iconv，否則中文參數不能正常處理
		$key = iconv("GBK","UTF-8",$key);
		$data = iconv("GBK","UTF-8",$data);

		$b = 64; # byte length for md5
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

	function arrToString($arr,$Separators)
	{
		$returnString = "";
		foreach ($arr as $value) {
			$returnString = $returnString.$value.$Separators;
		}
		return substr($returnString,0,strlen($returnString)-strlen($Separators));
	}

	function arrToStringDefault($arr)
	{
		return arrToString($arr,",");
	}













}