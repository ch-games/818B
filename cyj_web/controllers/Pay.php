<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pay extends MY_Controller {
    public function __construct() {
		parent::__construct();
		$this->load->model('member/pay/Online_api_model');
	}

	public function index(){
		echo "22";exit;
	}
	public function payfor(){
			$action = $_REQUEST["act"];
			$this->$action($action);

	}
	//宝付
	function baofoo($action){
			$data = $_REQUEST;
			$this->add("data",$data);
			$this->display('member/rep/'.$action.'.html');
	}
	function baofoo_callback(){
		$order_num = $_REQUEST['TransID'];//订单号
		$money = $_REQUEST['FactMoney'];//订单金额
		$payconf = $this->Online_api_model->get_states_conf($order_num);//根据订单号获取配置信息
		$user = $this->Online_api_model->get_in_cash($order_num);//根据订单号 获取入款注单数据
		if($user['make_sure'] == 1){
			echo '<script>alert("支付成功");</script>';exit;
		}elseif($user['make_sure'] == 2){
			echo '<script>alert("交易被取消！请联系管理员");</script>';exit;
		}
		$MemberID=$payconf['pay_id'];//$_REQUEST['MemberID'];//商户号
		$TerminalID =$payconf['terminalid'];//$_REQUEST['TerminalID'];//商户终端号
		$TransID =$_REQUEST['TransID'];//流水号
		$Result=$_REQUEST['Result'];//支付结果
		$ResultDesc=$_REQUEST['ResultDesc'];//支付结果描述
		$FactMoney=$_REQUEST['FactMoney'];//实际成功金额
		$AdditionalInfo=$_REQUEST['AdditionalInfo'];//订单附加消息
		$SuccTime=$_REQUEST['SuccTime'];//支付完成时间
		$Md5Sign=$_REQUEST['Md5Sign'];//md5签名
		$Md5key = $payconf['pay_key'];//$result['pay_key'];
		$MARK = "~|~";
		//MD5签名格式
		$WaitSign=md5('MemberID='.$MemberID.$MARK.'TerminalID='.$TerminalID.$MARK.'TransID='.$TransID.$MARK.'Result='.$Result.$MARK.'ResultDesc='.$ResultDesc.$MARK.'FactMoney='.$FactMoney.$MARK.'AdditionalInfo='.$AdditionalInfo.$MARK.'SuccTime='.$SuccTime.$MARK.'Md5Sign='.$Md5key);
		if ($Md5Sign == $WaitSign) {
			$this->Online_api_model->update_order($user['uid'],$order_num,$money*0.01);
		}else{
			echo "<script>alert('交易失败,错误代码cw0009');window.close();</script>";exit;
		}

	}

	//宝付
	function shanfu($action){
			$data = $_REQUEST;
			$this->add("data",$data);
			$this->display('member/rep/'.$action.'.html');
	}
	function shanfu_callback(){
		$order_num = $_REQUEST['TransID'];//订单号
		$money = $_REQUEST['FactMoney'];//订单金额
		$payconf = $this->Online_api_model->get_states_conf($order_num);//根据订单号获取配置信息
		$user = $this->Online_api_model->get_in_cash($order_num);//根据订单号 获取入款注单数据
		if($user['make_sure'] == 1){
			echo '<script>alert("支付成功");</script>';exit;
		}elseif($user['make_sure'] == 2){
			echo '<script>alert("交易被取消！请联系管理员");</script>';exit;
		}
		$MemberID=$payconf['pay_id'];//$_REQUEST['MemberID'];//商户号
		$TerminalID =$payconf['terminalid'];//$_REQUEST['TerminalID'];//商户终端号
		$TransID =$_REQUEST['TransID'];//流水号
		$Result=$_REQUEST['Result'];//支付结果
		$ResultDesc=$_REQUEST['ResultDesc'];//支付结果描述
		$FactMoney=$_REQUEST['FactMoney'];//实际成功金额
		$AdditionalInfo=$_REQUEST['AdditionalInfo'];//订单附加消息
		$SuccTime=$_REQUEST['SuccTime'];//支付完成时间
		$Md5Sign=$_REQUEST['Md5Sign'];//md5签名
		$Md5key = $payconf['pay_key'];//$result['pay_key'];
		$MARK = "~|~";
		//MD5签名格式
		$WaitSign=md5('MemberID='.$MemberID.$MARK.'TerminalID='.$TerminalID.$MARK.'TransID='.$TransID.$MARK.'Result='.$Result.$MARK.'ResultDesc='.$ResultDesc.$MARK.'FactMoney='.$FactMoney.$MARK.'AdditionalInfo='.$AdditionalInfo.$MARK.'SuccTime='.$SuccTime.$MARK.'Md5Sign='.$Md5key);
		if ($Md5Sign == $WaitSign) {
			$this->Online_api_model->update_order($user['uid'],$order_num,$money*0.01);
		}else{
			echo "<script>alert('交易失败,错误代码cw0009');window.close();</script>";exit;
		}

	}
	//新生
	function hnapay($action){
			$data = $_REQUEST;
			$this->add("data",$data);
			$this->display('member/rep/'.$action.'.html');
	}

	function hnapay_callback(){
		$order_num = $_REQUEST['orderID'];//订单号
		$money = $_REQUEST['payAmount']/100;//订单金额
		$payconf = $this->Online_api_model->get_states_conf($order_num);//根据订单号获取配置信息
		$user = $this->Online_api_model->get_in_cash($order_num);//根据订单号 获取入款注单数据
		if($user['make_sure'] == 1){
			echo '<script>alert("支付成功");</script>';exit;
		}elseif($user['make_sure'] == 2){
			echo '<script>alert("交易被取消！请联系管理员");</script>';exit;
		}
		$uid = $user['uid'];
		$orderID = $_REQUEST["orderID"];
		$resultCode = $_REQUEST["resultCode"];
		$stateCode = $_REQUEST["stateCode"];
		$orderAmount = $_REQUEST["orderAmount"];
		$payAmount = $_REQUEST["payAmount"];
		$acquiringTime = $_REQUEST["acquiringTime"];
		$completeTime = $_REQUEST["completeTime"];
		$orderNo = $_REQUEST["orderNo"];
		$partnerID = $_REQUEST["partnerID"];
		$remark = $_REQUEST["remark"];
		$charset = $_REQUEST["charset"];
		$signType = $_REQUEST["signType"];
		$signMsg = $_REQUEST["signMsg"];
		$src = "orderID=".$orderID
		."&resultCode=".$resultCode
		."&stateCode=".$stateCode
		."&orderAmount=".$orderAmount
		."&payAmount=".$payAmount
		."&acquiringTime=".$acquiringTime
		."&completeTime=".$completeTime
		."&orderNo=".$orderNo
		."&partnerID=".$partnerID
		."&remark=".$remark
		."&charset=".$charset
		."&signType=".$signType;
			$pkey = $payconf['pay_key'];
			$src = $src."&pkey=".$pkey;
		if($stateCode == 2){
			if ($signMsg == md5($src))
			{
				$this->Online_api_model->update_order($uid,$order_num,$money);
				exit;
			}else{
				echo "<script>alert('交易失败,错误代码cw0009');window.close();</script>";exit;
			}
		}else{
			    echo "<script>alert('交易正在处理中!错误代码cw0010');window.close();</script>";exit;
		}
	}
	//币付宝
	function bfpay($action){
		$data = $_REQUEST;
		$this->add("data",$data);
		$this->display('member/rep/'.$action.'.html');
	}

	function bfpay_callback(){
		$this->load->library('payapi/Befpay');
		$order_num = $_REQUEST['p3_xn'];//订单号
		$money = $_REQUEST['p4_amt'];//订单金额
		$payconf = $this->Online_api_model->get_states_conf($order_num);//根据订单号获取配置信息
		$user = $this->Online_api_model->get_in_cash($order_num);//根据订单号 获取入款注单数据
		if($user['make_sure'] == 1){
			echo '<script>alert("支付成功");</script>';exit;
		}elseif($user['make_sure'] == 2){
			echo '<script>alert("交易被取消！请联系管理员");</script>';exit;
		}
		$uid = $user['uid'];
		//初始化支付类，第一个参数为md5key，第二个参数为商户号
		$pay=new Befpay($payconf['pay_key'],$payconf['pay_id']);  //请填写相应商户对应的key和商户号
		$data=$pay->returnData();
		if($data['p7_st'] == 'success'){
			$this->Online_api_model->update_order($uid,$order_num,$money);
			echo "success";exit;//返回给币付宝success，以免漏单
		}elseif($data['p7_st'] == 'faile'){
			echo '<script>alert("支付失败，错误代码CW008！");window.close();</script>';
			echo '支付失败，错误代码CW008！';
			exit;
		}else{
			echo "<script>alert('交易失败,错误代码cw0009');window.close();</script>";exit;
		}
	}
	//环迅
	function ips($action){
		$data = $_REQUEST;
		$this->add("data",$data);
		$this->display('member/rep/'.$action.'.html');
	}

	function ips_callback(){
		$order_num = $_REQUEST['billno'];//订单号
		$money = $_REQUEST['amount'];//订单金额
		$payconf = $this->Online_api_model->get_states_conf($order_num);//根据订单号获取配置信息
		$user = $this->Online_api_model->get_in_cash($order_num);//根据订单号 获取入款注单数据
		if($user['make_sure'] == 1){
			echo '<script>alert("支付成功");</script>';exit;
		}elseif($user['make_sure'] == 2){
			echo '<script>alert("交易被取消！请联系管理员");</script>';exit;
		}
		$uid = $user['uid'];
		$billno = $order_num;
		$amount = $money;
		$mydate = $_REQUEST['date'];
		$succ = $_REQUEST['succ'];
		$msg = $_REQUEST['msg'];
		$attach = $_REQUEST['attach'];
		$ipsbillno = $_REQUEST['ipsbillno'];
		$retEncodeType = $_REQUEST['retencodetype'];
		$currency_type = $_REQUEST['Currency_type'];
		$signature = $_REQUEST['signature'];
		$content = 'billno'.$billno.'currencytype'.$currency_type.'amount'.$amount.'date'.$mydate.'succ'.$succ.'ipsbillno'.$ipsbillno.'retencodetype'.$retEncodeType;
		//请在该字段中放置商户登陆merchant.ips.com.cn下载的证书
		$cert = $payconf['pay_key'];
		$signature_1ocal = md5($content . $cert);
		if ($signature_1ocal == $signature)
		{
			if($succ == 'Y'){
				$this->Online_api_model->update_order($uid,$order_num,$money);
				exit;
			}else{
				echo "<script>alert('交易失败,错误代码cw0008');window.close();</script>";exit;
			}
		}else{
			echo "<script>alert('交易失败,错误代码cw0009');window.close();</script>";exit;
		}
	}
	//智付
	function dinpay($action){
		$data = $_REQUEST;
		$this->add("data",$data);
		$this->display('member/rep/'.$action.'.html');
	}

	function dinpay_callback(){
		$order_num = $_REQUEST['order_no'];//订单号
		$money = $_REQUEST['order_amount'];//订单金额
		$payconf = $this->Online_api_model->get_states_conf($order_num);//根据订单号获取配置信息
		$user = $this->Online_api_model->get_in_cash($order_num);//根据订单号 获取入款注单数据
		if($user['make_sure'] == 1){
			echo '<script>alert("支付成功");</script>';exit;
		}elseif($user['make_sure'] == 2){
			echo '<script>alert("交易被取消！请联系管理员");</script>';exit;
		}
		$uid = $user['uid'];
		/* *
		 功能：智付页面跳转同步通知页面
		 版本：3.0
		 日期：2013-08-01
		 说明：
		 以下代码仅为了方便商户安装接口而提供的样例具体说明以文档为准，商户可以根据自己网站的需要，按照技术文档编写。
		 * */
			//获取智付GET过来反馈信息
			$merchant_code	= $_REQUEST["merchant_code"];//商号号
			$notify_type = $_REQUEST["notify_type"];//通知类型
			$notify_id = $_REQUEST["notify_id"];//通知校验ID
			$interface_version = $_REQUEST["interface_version"];//接口版本
			$sign_type = $_REQUEST["sign_type"];//签名方式
			$dinpaySign = $_REQUEST["sign"];//签名
			$order_no = $order_num;//商家订单号
			$order_time = $_REQUEST["order_time"];//商家订单时间
			$order_amount = $money;//商家订单金额
			$extra_return_param = $_REQUEST["extra_return_param"];//回传参数
			$s_name=$extra_return_param;
			$trade_no = $_REQUEST["trade_no"];//智付交易定单号
			$trade_time = $_REQUEST["trade_time"];//智付交易时间
			$trade_status = $_REQUEST["trade_status"];//交易状态 SUCCESS 成功  FAILED 失败
			$bank_seq_no = $_REQUEST["bank_seq_no"];//银行交易流水号
			/**
			 *签名顺序按照参数名a到z的顺序排序，若遇到相同首字母，则看第二个字母，以此类推，
			*同时将商家支付密钥key放在最后参与签名，组成规则如下：
			*参数名1=参数值1&参数名2=参数值2&……&参数名n=参数值n&key=key值
			**/
			//组织订单信息
			$signStr = "";
			if($bank_seq_no != "") {
				$signStr = $signStr."bank_seq_no=".$bank_seq_no."&";
			}
			if($extra_return_param != "") {
			    $signStr = $signStr."extra_return_param=".$extra_return_param."&";
			}
			$signStr = $signStr."interface_version=V3.0&";
			$signStr = $signStr."merchant_code=".$merchant_code."&";
			if($notify_id != "") {
			    $signStr = $signStr."notify_id=".$notify_id."&notify_type=offline_notify&";
			}
		        $signStr = $signStr."order_amount=".$order_amount."&";
		        $signStr = $signStr."order_no=".$order_no."&";
		        $signStr = $signStr."order_time=".$order_time."&";
		        $signStr = $signStr."trade_no=".$trade_no."&";
		        $signStr = $signStr."trade_status=".$trade_status."&";
			if($trade_time != "") {
			     $signStr = $signStr."trade_time=".$trade_time."&";
			}
			$key=$payconf['pay_key'];   //"123456789a123456789_";
			$signStr = $signStr."key=".$key;
			$signInfo = $signStr;
			//将组装好的信息MD5签名
			$sign = md5($signInfo);
		if ($dinpaySign == $sign){
			echo "SUCCESS";
			$this->Online_api_model->update_order($uid,$order_num,$money);
			exit;
		}else{
			echo "<script>alert('交易失败,错误代码cw0009');window.close();</script>";exit;
		}
	}
	//汇潮
	function ecpss($action){
		$data = $_REQUEST;
		$this->add("data",$data);
		$this->display('member/rep/'.$action.'.html');
	}
	function ecpss_callback(){
		$order_num = $_REQUEST['BillNo'];//订单号
		$money = $_REQUEST['Amount'];//订单金额
		$payconf = $this->Online_api_model->get_states_conf($order_num);//根据订单号获取配置信息
		$user = $this->Online_api_model->get_in_cash($order_num);//根据订单号 获取入款注单数据
		if($user['make_sure'] == 1){
			echo '<script>alert("支付成功");</script>';exit;
		}elseif($user['make_sure'] == 2){
			echo '<script>alert("交易被取消！请联系管理员");</script>';exit;
		}
		$uid = $user['uid'];
		$MD5key = $payconf['pay_key'];//MD5私钥
		$BillNo = $order_num;//订单号
		$Amount = $money;//金额
		$Succeed = $_REQUEST["Succeed"];//支付状态
		$Result = $_REQUEST["Result"];//支付结果
		$SignMD5info = $_REQUEST["SignMD5info"]; //取得的MD5校验信息
		$Remark = $_REQUEST["Remark"];//备注
		$md5src = $BillNo."&".$Amount."&".$Succeed."&".$MD5key;//校验源字符串
		$md5sign = strtoupper(md5($md5src));//MD5检验结果
		if ($SignMD5info == $md5sign){
			if($Succeed == '88'){
				$this->Online_api_model->update_order($uid,$order_num,$money);
				exit;
			}else{
				echo "<script>alert('交易失败,错误代码cw0008');window.close();</script>";exit;
			}
		}else{
			echo "<script>alert('交易失败,错误代码cw0009');window.close();</script>";exit;
		}
	}
	//快捷通
	function kjtpay($action){
		$data = $_REQUEST;
		$this->add("data",$data);
		//var_dump($data);die;
		$this->display('member/rep/'.$action.'.html');
		/*$service = $this->input->post("service");
		$version = $this->input->post("version");
		$partner_id = $this->input->post("partner_id");
		$_input_charset = $this->input->post("_input_charset");
		$sign_type = $this->input->post("sign_type");
		$sign = $this->input->post("sign");
		$return_url = $this->input->post("return_url");
		$request_no = $this->input->post("request_no");
		$trade_list = $this->input->post("trade_list");
		$buyer_id = $this->input->post("buyer_id");
		$buyer_id_type = $this->input->post("buyer_id_type");
		$go_cashier = $this->input->post("go_cashier");
		$pay_method = $this->input->post("pay_method");
		$form_url = $this->input->post("form_url");
		$req_url = $this->input->post("req_url");
		$this->add('service',$service);
		$this->add('version',$version);
		$this->add('partner_id',$partner_id);
		$this->add('_input_charset',$_input_charset);
		$this->add('sign_type',$sign_type);
		$this->add('sign',$sign);
		$this->add('return_url',$return_url);
		$this->add('request_no',$request_no);
		$this->add('trade_list',$trade_list);
		$this->add('buyer_id',$buyer_id);
		$this->add('buyer_id_type',$buyer_id_type);
		$this->add('go_cashier',$go_cashier);
		$this->add('pay_method',$pay_method);
		$this->add('form_url',$form_url);
		$this->add('req_url',$req_url);
		$this->display('member/rep/'.$action.'.html');*/

	}
	function kjtpay_callback(){
		$order_num = $_REQUEST['outer_trade_no'];//订单号
		$money = $_REQUEST['trade_amount'];//订单金额
		$payconf = $this->Online_api_model->get_states_conf($order_num);//根据订单号获取配置信息
		$user = $this->Online_api_model->get_in_cash($order_num);//根据订单号 获取入款注单数据
		if($user['make_sure'] == 1){
			echo '<script>alert("支付成功");</script>';exit;
		}elseif($user['make_sure'] == 2){
			echo '<script>alert("交易被取消！请联系管理员");</script>';exit;
		}
		$uid = $user['uid'];
		$billno = $order_num;
		$amount = $money;
		$key = $payconf['pay_key'];
		$notify_id=$_REQUEST["notify_id"];
		$notify_type=$_REQUEST["notify_type"];
		$notify_time=$_REQUEST["notify_time"];
		$_input_charset=$_REQUEST["_input_charset"];
		$version=$_REQUEST["version"];
		$outer_trade_no=$_REQUEST["outer_trade_no"];
		$inner_trade_no=$_REQUEST["inner_trade_no"];
		$trade_status=$_REQUEST["trade_status"];
		$trade_amount=$_REQUEST["trade_amount"];
		$gmt_create=$_REQUEST["gmt_create"];
		$gmt_payment=$_REQUEST["gmt_payment"];
		$gmt_close=$_REQUEST["gmt_close"];
		$sign1=$_REQUEST["sign"];
		$sign_type=$_REQUEST["sign_type"];

		$str="_input_charset=".$_input_charset."&gmt_create=".$gmt_create."&gmt_payment=".$gmt_payment."&inner_trade_no=".$inner_trade_no."&notify_id=".$notify_id."&notify_time=".$notify_time."&notify_type=".$notify_type."&outer_trade_no=".$outer_trade_no."&trade_amount=".$trade_amount."&trade_status=".$trade_status."&version=".$version;
		$sign=md5($str.$key);
		if ($sign1 == $sign){
			if ("TRADE_SUCCESS"==$trade_status) {
				$this->Online_api_model->update_order($uid,$order_num,$money);
				exit;
			}else{
				echo "<script>alert('交易失败,错误代码cw0008');window.close();</script>";exit;
			}
		}else{
			echo "<script>alert('交易失败,错误代码cw0009');window.close();</script>";exit;
		}

	}
	//新环迅
	function newips($action){
		$pGateWayReq= $_REQUEST['pGateWayReq'];
		$form_url = $_REQUEST["form_url"];
		$this->add('form_url',$form_url);
		$this->add('pGateWayReq',$pGateWayReq);
		$this->display('member/rep/'.$action.'.html');

	}
	function newips_callback(){

		$paymentResult = $_REQUEST["paymentResult"];//获取信息
		$xml=simplexml_load_string($paymentResult,'SimpleXMLElement', LIBXML_NOCDATA);

		//读取相关xml中信息
		$ReferenceIDs = $xml->xpath("GateWayRsp/head/ReferenceID");//关联号
		//var_dump($ReferenceIDs);
		$ReferenceID = $ReferenceIDs[0];//关联号
		$RspCodes = $xml->xpath("GateWayRsp/head/RspCode");//响应编码
		$RspCode=$RspCodes[0];
		$RspMsgs = $xml->xpath("GateWayRsp/head/RspMsg"); //响应说明
		$RspMsg=$RspMsgs[0];
		$ReqDates = $xml->xpath("GateWayRsp/head/ReqDate"); // 接受时间
		$ReqDate=$ReqDates[0];
		$RspDates = $xml->xpath("GateWayRsp/head/RspDate");// 响应时间
		$RspDate=$RspDates[0];
		$Signatures = $xml->xpath("GateWayRsp/head/Signature"); //数字签名
		$Signature=$Signatures[0];
		$MerBillNos = $xml->xpath("GateWayRsp/body/MerBillNo"); // 商户订单号
		$MerBillNo=$MerBillNos[0];
		$CurrencyTypes = $xml->xpath("GateWayRsp/body/CurrencyType");//币种
		$CurrencyType=$CurrencyTypes[0];
		$Amounts = $xml->xpath("GateWayRsp/body/Amount"); //订单金额
		$Amount=$Amounts[0];
		$Dates = $xml->xpath("GateWayRsp/body/Date");    //订单日期
		$Date=$Dates[0];
		$Statuss = $xml->xpath("GateWayRsp/body/Status");  //交易状态
		$Status=$Statuss[0];
		$Msgs = $xml->xpath("GateWayRsp/body/Msg");    //发卡行返回信息
		$Msg=$Msgs[0];
		$Attachs = $xml->xpath("GateWayRsp/body/Attach");    //数据包
		$Attach=$Attachs[0];
		$IpsBillNos = $xml->xpath("GateWayRsp/body/IpsBillNo"); //IPS订单号
		$IpsBillNo=$IpsBillNos[0];
		$IpsTradeNos = $xml->xpath("GateWayRsp/body/IpsTradeNo"); //IPS交易流水号
		$IpsTradeNo=$IpsTradeNos[0];
		$RetEncodeTypes = $xml->xpath("GateWayRsp/body/RetEncodeType");    //交易返回方式
		$RetEncodeType=$RetEncodeTypes[0];
		$BankBillNos = $xml->xpath("GateWayRsp/body/BankBillNo"); //银行订单号
		$BankBillNo=$BankBillNos[0];
		$ResultTypes = $xml->xpath("GateWayRsp/body/ResultType"); //支付返回方式
		$ResultType=$ResultTypes[0];
		$IpsBillTimes = $xml->xpath("GateWayRsp/body/IpsBillTime"); //IPS处理时间
		$IpsBillTime=$IpsBillTimes[0];

		$order_num = $MerBillNo;//订单号
		$money = $Amount;//订单金额
		$payconf = $this->Online_api_model->get_states_conf($order_num);//根据订单号获取配置信息
		$user = $this->Online_api_model->get_in_cash($order_num);//根据订单号 获取入款注单数据
		if($user['make_sure'] == 1){
			echo '<script>alert("支付成功");</script>';exit;
		}elseif($user['make_sure'] == 2){
			echo '<script>alert("交易被取消！请联系管理员");</script>';exit;
		}
		$uid = $user['uid'];
		$Mer_code = $payconf['pay_id'];
		$Mer_key  = $payconf['pay_key'];
		$vircarddoin  = $payconf['terminalid'];
		$arrayMer=array (
             'mername'=>$Mer_code,//商户id
             'mercert'=>$Mer_key,//商户密钥
             'acccode'=>$vircarddoin
           );
			 $sbReq = "<body>"
									  . "<MerBillNo>" . $MerBillNo . "</MerBillNo>"
									  . "<CurrencyType>" . $CurrencyType . "</CurrencyType>"
									  . "<Amount>" . $Amount . "</Amount>"
									  . "<Date>" . $Date . "</Date>"
									  . "<Status>" . $Status . "</Status>"
									  . "<Msg><![CDATA[" . $Msg . "]]></Msg>"
									  . "<Attach><![CDATA[" . $Attach . "]]></Attach>"
									  . "<IpsBillNo>" . $IpsBillNo . "</IpsBillNo>"
									  . "<IpsTradeNo>" . $IpsTradeNo . "</IpsTradeNo>"
									  . "<RetEncodeType>" . $RetEncodeType . "</RetEncodeType>"
									  . "<BankBillNo>" . $BankBillNo . "</BankBillNo>"
									  . "<ResultType>" . $ResultType . "</ResultType>"
									  . "<IpsBillTime>" . $IpsBillTime . "</IpsBillTime>"
								   . "</body>";
			$sign=$sbReq.$Mer_code.$arrayMer['mercert'];
			$md5sign=  md5($sign);
		if ($Signature == $md5sign){
			if ($RspCode == '000000') {
					$this->Online_api_model->update_order($uid,$order_num,$money);
				exit;
			}else{
				echo "<script>alert('交易失败,错误代码cw0008');window.close();</script>";exit;
			}
		}else{
			echo "<script>alert('交易失败,错误代码cw0009');window.close();</script>";exit;
		}
	}
	//国付宝
	function states($action){
		$data = $_REQUEST;
		$this->add("data",$data);
		$this->display('member/rep/'.$action.'.html');

	}
	function states_callback(){
		$merOrderNum = $_REQUEST['merOrderNum'];//订单号
		$tranAmt = $_REQUEST['tranAmt'];//订单金额
		$payconf = $this->Online_api_model->get_states_conf($merOrderNum);
		$user = $this->Online_api_model->get_in_cash($merOrderNum);
		if($user['make_sure'] == 1){
			echo '<script>alert("支付成功");</script>';exit;
		}elseif($user['make_sure'] == 2){
			echo '<script>alert("交易被取消！请联系管理员");</script>';exit;
		}
		$uid = $user['uid'];
		//$userinfo = $this->Online_api_model->getinfo($uid);
		$payset = $this->Online_api_model->get_payset($uid);
		$version = $_REQUEST["version"];
		$charset = $_REQUEST["charset"];
		$language = $_REQUEST["language"];
		$signType = $_REQUEST["signType"];
		$tranCode = $_REQUEST["tranCode"];
		$merchantID = $payconf['pay_id'];//商户号
		$merOrderNum = $_REQUEST["merOrderNum"];
		$tranAmt = $_REQUEST["tranAmt"];
		$feeAmt = $_REQUEST["feeAmt"];
		$frontMerUrl = $_REQUEST["frontMerUrl"];
		$backgroundMerUrl = $_REQUEST["backgroundMerUrl"];
		$tranDateTime = $_REQUEST["tranDateTime"];
		$tranIP = $_REQUEST["tranIP"];
		$respCode = $_REQUEST["respCode"];
		$msgExt = $_REQUEST["msgExt"];
		$orderId = $_REQUEST["orderId"];
		$gopayOutOrderId = $_REQUEST["gopayOutOrderId"];
		$bankCode = $_REQUEST["bankCode"];
		$tranFinishTime = $_REQUEST["tranFinishTime"];
		$merRemark1 = $_REQUEST["merRemark1"];
		$merRemark2 = $_REQUEST["merRemark2"];
		$signValue = $_REQUEST["signValue"];
		$Mer_key = $payconf['pay_key'];//商户秘钥
		//注意md5加密串需要重新拼装加密后，与获取到的密文串进行验签
		$signValue2='version=['.$version.']tranCode=['.$tranCode.']merchantID=['.$merchantID.']merOrderNum=['.$merOrderNum.']tranAmt=['.$tranAmt.']feeAmt=['.$feeAmt.']tranDateTime=['.$tranDateTime.']frontMerUrl=['.$frontMerUrl.']backgroundMerUrl=['.$backgroundMerUrl.']orderId=['.$orderId.']gopayOutOrderId=['.$gopayOutOrderId.']tranIP=['.$tranIP.']respCode=['.$respCode.']gopayServerTime=[]VerficationCode=['.$Mer_key.']';
		$signValue2 = md5($signValue2);
		if($signValue==$signValue2){
			if($respCode=='0000'){
				$this->Online_api_model->update_order($uid,$merOrderNum,$tranAmt);
			}else{
				echo "<script>alert('交易失败,错误代码cw0008');window.close();</script>";exit;
			}
		}else{
			echo "<script>alert('交易失败,错误代码cw0009');window.close();</script>";exit;
		}
	}

	function card_callback(){
		$this->load->model('member/pay/Card_model');
		#	解析返回参数.
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

			$order_num = $_REQUEST['p2_Order'];//订单号
			$money = $_REQUEST['p3_Amt'];//订单金额
			$payconf = $this->Online_api_model->get_states_conf($order_num);//根据订单号获取配置信息
			$user = $this->Online_api_model->get_in_cash($order_num);//根据订单号 获取入款注单数据
			if($user['make_sure'] == 1){
				echo '<script>alert("success,支付成功");</script>';exit;
			}elseif($user['make_sure'] == 2){
				echo '<script>alert("交易被取消！请联系管理员");</script>';exit;
			}
			$uid = $user['uid'];

			#	解析返回参数.
	$return = $this->Card_model->getCallBackValue($r0_Cmd,$r1_Code,$p1_MerId,$p2_Order,$p3_Amt,$p4_FrpId,$p5_CardNo,$p6_confirmAmount,$p7_realAmount,$p8_cardStatus,
$p9_MP,$pb_BalanceAmt,$pc_BalanceAct,$hmac);

			#	判断返回签名是否正确（True/False）

			$bRet = $this->Card_model->CheckHmac($r0_Cmd,$r1_Code,$p1_MerId,$p2_Order,$p3_Amt,$p4_FrpId,$p5_CardNo,$p6_confirmAmount,$p7_realAmount,$p8_cardStatus,$p9_MP,$pb_BalanceAmt,$pc_BalanceAct,$hmac,$payconf['pay_key']);
			#	以上代码和变量不需要修改.
			#	校验码正确.
			if($bRet){
				echo "success";
			#在接收到支付结果通知后，判断是否进行过业务逻辑处理，不要重复进行业务逻辑处理
				if($r1_Code=="1"){
					$this->Online_api_model->update_order($uid,$order_num,$money);
				}else{
					echo "<script>alert('交易失败success,错误代码cw0008');window.close();</script>";exit;
				}
			}else{
				echo "<script>alert('交易失败success,错误代码cw0009');window.close();</script>";exit;
			}
	}

	//易宝
	function yeepay($action){
		$data = $_REQUEST;
		$this->add("data",$data);
		$this->display('member/rep/'.$action.'.html');
	}

	function yeepay_callback(){
		$this->load->model('member/pay/Yeepay_model');
		$order_num = $_REQUEST['r6_Order'];//订单号
		$money = $_REQUEST['r3_Amt'];//订单金额
		$payconf = $this->Online_api_model->get_states_conf($order_num);//根据订单号获取配置信息
		$user = $this->Online_api_model->get_in_cash($order_num);//根据订单号 获取入款注单数据
		if($user['make_sure'] == 1){
			echo '<script>alert("支付成功");</script>';exit;
		}elseif($user['make_sure'] == 2){
			echo '<script>alert("交易被取消！请联系管理员");</script>';exit;
		}
		$uid = $user['uid'];
		$r0_Cmd		= $_REQUEST['r0_Cmd'];//业务类型 固定值”Buy”.
		$r1_Code	= $_REQUEST['r1_Code'];//支付结果固定值“1”, 代表支付成功.
		$r2_TrxId	= $_REQUEST['r2_TrxId'];//易宝支付交易流水号
		$r3_Amt		= $_REQUEST['r3_Amt'];//支付金额
		$r4_Cur		= $_REQUEST['r4_Cur'];//交易币种 返回时是"RMB"
		$r5_Pid		= $_REQUEST['r5_Pid'];//商品名称易宝支付返回商户设置的商品名称.此参数如用到中文，请注意转码.
		$r6_Order	= $_REQUEST['r6_Order'];//商户订单号
		$r7_Uid		= $_REQUEST['r7_Uid'];//易宝支付会员ID 如果用户使用的易宝支付会员进行支付则返回该用户的易宝支付会员ID;反之为''.
		$r8_MP		= $_REQUEST['r8_MP'];//商户扩展信息 此参数如用到中文，请注意转码.
		$r9_BType	= $_REQUEST['r9_BType']; //交易结果返回类型 为“1”: 浏览器重定向; 为“2”: 服务器点对点通讯.
		$hmac			= $_REQUEST['hmac'];//签名数据
		$p1_MerId = $payconf['pay_id'];
		$merchantKey = $payconf['pay_key'];
		#	判断返回签名是否正确（True/False）
		$bRet = $this->Yeepay_model->CheckHmac($r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType,$hmac,$p1_MerId,$merchantKey);
		if($bRet){
			if($r1_Code=="1"){
				if($r9_BType=="1"){
					$this->Online_api_model->update_order($uid,$order_num,$money);
				}elseif($r9_BType=="2"){
					$this->Online_api_model->update_order($uid,$order_num,$money);
				}
			}
		}else{
			echo "<script>alert('操作非法，请重新操作！');</script>";
		}
	}
	//融宝

	function reapal($action){
		$data = $_REQUEST;
		$this->add("data",$data);
		$this->display('member/rep/'.$action.'.html');
	}

	function reapal_callback(){
		$this->load->library('payapi/Rongpay_notify.php');
		$order_no = $_REQUEST['order_no'];//订单号
		$total_fee = $_REQUEST['total_fee'];//订单金额
		$payconf = $this->Online_api_model->get_states_conf($order_no);
		$user = $this->Online_api_model->get_in_cash($order_no);
		if($user['make_sure'] == 1){
			echo '<script>alert("支付成功");</script>';exit;
		}elseif($user['make_sure'] == 2){
			echo '<script>alert("交易被取消！请联系管理员");</script>';exit;
		}
		$uid = $user['uid'];
		//$userinfo = $this->Online_api_model->getinfo($uid);
		$payset = $this->Online_api_model->get_payset($uid);
		$merchant_ID = $payset['pay_id'];
		$key = $payset['pay_key'];
		$sign_type = $_REQUEST['sign_type'];
		$form_url = $_REQUEST['form_url'];
		 $rongpay = new rongpay_notify();
		 $rongpay->rongpay_notify($merchant_ID,$key,$sign_type,$charset = "utf-8",$transport= "http");
		 $zhifu = $rongpay->return_verify($form_url);
		if($zhifu){
			echo "success";
				$this->Online_api_model->update_order($uid,$order_no,$total_fee);
		}else{
			   echo "<script>alert('交易失败,错误代码cw0009');window.close();</script>";exit;
		}
	}



	//通汇

	function remittance($action){
		$data = $_REQUEST;
		$this->add("data",$data);
		$this->display('member/rep/'.$action.'.html');
	}

	function remittance_callback(){
		$orderNo = $_REQUEST['order_no'];//订单号
		$orderAmount = $_REQUEST['order_amount'];//订单金额
		$payconf = $this->Online_api_model->get_states_conf($orderNo);
		$user = $this->Online_api_model->get_in_cash($orderNo);
		if($user['make_sure'] == 1){
			echo '<script>alert("支付成功");</script>';exit;
		}elseif($user['make_sure'] == 2){
			echo '<script>alert("交易被取消！请联系管理员");</script>';exit;
		}
		$uid = $user['uid'];
		//$userinfo = $this->Online_api_model->getinfo($uid);
		$payset = $this->Online_api_model->get_payset($uid);
		$orderNo = $_REQUEST['order_no'];
		$orderAmount = $_REQUEST['order_amount'];
		$notifyType = $_REQUEST['notify_type'];
        $merchantCode =  $_REQUEST['merchant_code'];
        $orderTime =  $_REQUEST['order_time'];
        $returnParams =  $_REQUEST['return_params'];
        $tradeNo =  $_REQUEST['trade_no'];
        $tradeTime =  $_REQUEST['trade_time'];
        $tradeStatus =  $_REQUEST['trade_status'];
        $sign =  $_REQUEST['sign'];
        $this->load->library('payapi/Helper');
        $Helper = new Helper();
        $data = array();
        $data['merchant_code'] = $merchantCode;
        $data['notify_type'] = $notifyType;
        $data['order_no'] = $orderNo;
        $data['order_amount'] = $orderAmount;
        $data['order_time'] = $orderTime;
        $data['return_params'] = $returnParams;
        $data['trade_no'] = $tradeNo;
        $data['trade_time'] = $tradeTime;
        $data['trade_status'] = $tradeStatus;
        $_sign = $Helper->sign($data,$payconf['pay_key']);
		if($_sign == $sign){
				if($tradeStatus == 'success'){
					$this->Online_api_model->update_order($uid,$orderNo,$orderAmount);
				}else{
					echo "<script>alert('交易失败,错误代码cw0008');window.close();</script>";exit;
				}
		}else{
			echo "<script>alert('交易失败,错误代码cw0009');window.close();</script>";exit;
		}
	}

	//智付rsa

	function Dinpayrsa($action){

		if($_REQUEST['bank_code'] == 'weixin'){
			$this->load->library('payapi/QRcode');
			$ptype = $this->input->get('ptype');
			$order_num = $this->input->get('order_num');
			if($ptype == 1){
				$redis = RedisConPool::getInstace();
				$redis_key = SITEID."_".$order_num;
				 $vdata2 = $redis->get($redis_key);
				if(empty($vdata2)){
					 echo "<script>alert('获取数据超时,请重新提交订单！');window.close();</script>";exit;
				}else{
			    	$data= json_decode($vdata2,TRUE);
			    	$redis->delete($redis_key);
				}
				$data['sign'] = str_replace(' ','+',$data['sign']);
			$postdata = "";

			if($data['extend_param'] != ""){
				$postdata = $postdata.'extend_param='.$data['extend_param']."&";
			}

			if($data['extra_return_param'] != ""){
				$postdata = $postdata.'extra_return_param='.$data['extra_return_param']."&";
			}

			if($data['product_code'] != ""){
				$postdata = $postdata.'product_code='.$data['product_code']."&";
			}

			if($data['product_desc'] != ""){
				$postdata = $postdata.'product_desc='.$data['product_desc']."&";
			}

			if($data['product_num'] != ""){
				$postdata = $postdata.'product_num='.$data['product_num']."&";
			}
			$postdata = $postdata.'merchant_code='.$data['merchant_code']."&";

			$postdata = $postdata.'service_type='.$data['service_type']."&";

			$postdata = $postdata.'notify_url='.$data['notify_url']."&";

			$postdata = $postdata.'interface_version='.$data['interface_version']."&";

			$postdata = $postdata. 'sign_type='.$data['sign_type']."&";

			$postdata = $postdata.'sign='.$data['sign']."&";

			$postdata = $postdata.'order_no='.$data['order_no']."&";

			$postdata = $postdata.'order_time='.$data['order_time']."&";

			$postdata = $postdata.'order_amount='.$data['order_amount']."&";

			$postdata = $postdata.'product_name='.$data['product_name'];

					//echo "发送到智付的数据为："."<br>".$postdata."<br>";

			$ch = curl_init();

			curl_setopt($ch,CURLOPT_URL,$data['form_url']);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_POST, true);
		    curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$response=curl_exec($ch);
			//p($response);
			$res=simplexml_load_string($response);


			$resp_code=$res->response->resp_code;
			//p($resp_code);die;
			if($resp_code=="SUCCESS"){

				$qrcode=$res->response->trade->qrcode;


				$errorCorrectionLevel = 'L';

				$matrixPointSize = 10;
				//var_dump($qrcode,$errorCorrectionLevel, $matrixPointSize);die;
				QRcode::png ( $qrcode, false, $errorCorrectionLevel, $matrixPointSize, 2 );
			}


			curl_close($ch);




			}else{


			$_REQUEST['sign'] = str_replace(' ','+',$_REQUEST['sign']);
			$postdata = "";

			if($_REQUEST['extend_param'] != ""){
				$postdata = $postdata.'extend_param='.$_REQUEST['extend_param']."&";
			}

			if($_REQUEST['extra_return_param'] != ""){
				$postdata = $postdata.'extra_return_param='.$_REQUEST['extra_return_param']."&";
			}

			if($_REQUEST['product_code'] != ""){
				$postdata = $postdata.'product_code='.$_REQUEST['product_code']."&";
			}

			if($_REQUEST['product_desc'] != ""){
				$postdata = $postdata.'product_desc='.$_REQUEST['product_desc']."&";
			}

			if($_REQUEST['product_num'] != ""){
				$postdata = $postdata.'product_num='.$_REQUEST['product_num']."&";
			}
			$postdata = $postdata.'merchant_code='.$_REQUEST['merchant_code']."&";

			$postdata = $postdata.'service_type='.$_REQUEST['service_type']."&";

			$postdata = $postdata.'notify_url='.$_REQUEST['notify_url']."&";

			$postdata = $postdata.'interface_version='.$_REQUEST['interface_version']."&";

			$postdata = $postdata. 'sign_type='.$_REQUEST['sign_type']."&";

			$postdata = $postdata.'sign='.$_REQUEST['sign']."&";

			$postdata = $postdata.'order_no='.$_REQUEST['order_no']."&";

			$postdata = $postdata.'order_time='.$_REQUEST['order_time']."&";

			$postdata = $postdata.'order_amount='.$_REQUEST['order_amount']."&";

			$postdata = $postdata.'product_name='.$_REQUEST['product_name'];

				//echo "发送到智付的数据为："."<br>".$postdata."<br>";

			$ch = curl_init();

			curl_setopt($ch,CURLOPT_URL,$_REQUEST['form_url']);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_POST, true);
		    curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$response=curl_exec($ch);
			$res=simplexml_load_string($response);


			$resp_code=$res->response->resp_code;
			//p($resp_code);die;
			if($resp_code=="SUCCESS"){

				$qrcode=$res->response->trade->qrcode;

				$errorCorrectionLevel = 'L';

				$matrixPointSize = 10;
				//var_dump($qrcode,$errorCorrectionLevel, $matrixPointSize);die;
				QRcode::png ( $qrcode, false, $errorCorrectionLevel, $matrixPointSize, 2 );
			}


			curl_close($ch);
		}
		}else{
			$data = $_REQUEST;
			$data['sign'] = str_replace(' ','+',$data['sign']);
			$this->add("data",$data);
			$this->display('member/rep/'.$action.'.html');
		}
	}

	function Dinpayrsa_callback(){
		$orderNo = $_POST['order_no'];//订单号
		$orderAmount = $_POST['order_amount'];//订单金额
		$payconf = $this->Online_api_model->get_states_conf($orderNo);
		$user = $this->Online_api_model->get_in_cash($orderNo);
		if($user['make_sure'] == 1){
			echo '<script>alert("支付成功");</script>';exit;
		}elseif($user['make_sure'] == 2){
			echo '<script>alert("交易被取消！请联系管理员");</script>';exit;
		}
		$uid = $user['uid'];
		//$userinfo = $this->Online_api_model->getinfo($uid);
		$payset = $this->Online_api_model->get_payset($uid);
		$pubKey = openssl_get_publickey($payconf['public_key']);

		$merchant_code	= $_POST["merchant_code"];
		$interface_version = $_POST["interface_version"];
		$sign_type = $_POST["sign_type"];

		$dinpaySign = base64_decode(str_replace(' ','+',$_POST["sign"]));
		$notify_type = $_POST["notify_type"];
		$notify_id = $_POST["notify_id"];
		$order_no = $_POST["order_no"];
		$order_time = $_POST["order_time"];
		$order_amount = $_POST["order_amount"];
		$trade_status = $_POST["trade_status"];
		$trade_time = $_POST["trade_time"];
		$trade_no = $_POST["trade_no"];
		$bank_seq_no = $_POST["bank_seq_no"];
		$extra_return_param = $_POST["extra_return_param"];
		$signStr = "";
		if($bank_seq_no != ""){
			$signStr = $signStr."bank_seq_no=".$bank_seq_no."&";
		}
		if($extra_return_param != ""){
			$signStr = $signStr."extra_return_param=".$extra_return_param."&";
		}
		$signStr = $signStr."interface_version=".$interface_version."&";
		$signStr = $signStr."merchant_code=".$merchant_code."&";
		$signStr = $signStr."notify_id=".$notify_id."&";
		$signStr = $signStr."notify_type=".$notify_type."&";
	    $signStr = $signStr."order_amount=".$order_amount."&";
	    $signStr = $signStr."order_no=".$order_no."&";
	    $signStr = $signStr."order_time=".$order_time."&";
	    $signStr = $signStr."trade_no=".$trade_no."&";
	    $signStr = $signStr."trade_status=".$trade_status."&";
		$signStr = $signStr."trade_time=".$trade_time;

		if(openssl_verify($signStr,$dinpaySign,$pubKey,OPENSSL_ALGO_MD5)){
			$this->Online_api_model->update_order($uid,$orderNo,$orderAmount);
		}else{
			echo "<script>alert('交易失败,错误代码cw0009');window.close();</script>";exit;
		}
	}

	//智付RSA 微信回调
	public function Dinpayrsa_weixin_callback(){
		$orderNo = $_POST['order_no'];//订单号
		$orderAmount = $_POST['order_amount'];//订单金额
		$payconf = $this->Online_api_model->get_states_conf($orderNo);
		$user = $this->Online_api_model->get_in_cash($orderNo);
		if($user['make_sure'] == 1){
			echo '<script>alert("支付成功");</script>';exit;
		}elseif($user['make_sure'] == 2){
			echo '<script>alert("交易被取消！请联系管理员");</script>';exit;
		}
		$uid = $user['uid'];
		//$userinfo = $this->Online_api_model->getinfo($uid);
		$payset = $this->Online_api_model->get_payset($uid);
		$pubKey = openssl_get_publickey($payconf['public_key']);
		$merchant_code	= $_POST["merchant_code"];
		$notify_type = $_POST["notify_type"];
		$notify_id = $_POST["notify_id"];
		$interface_version = $_POST["interface_version"];
		$sign_type = $_POST["sign_type"];
		$dinpaySign = base64_decode($_POST["sign"]);
		$order_no = $_POST["order_no"];
		$order_time = $_POST["order_time"];
		$order_amount = $_POST["order_amount"];
		$extra_return_param = $_POST["extra_return_param"];
		$trade_no = $_POST["trade_no"];
		$trade_time = $_POST["trade_time"];
		$trade_status = $_POST["trade_status"];
		$bank_seq_no = $_POST["bank_seq_no"];
		$signStr = "";
		if($bank_seq_no != ""){
		$signStr = $signStr."bank_seq_no=".$bank_seq_no."&";
		}
		if($extra_return_param != ""){
		$signStr = $signStr."extra_return_param=".$extra_return_param."&";
		}
		$signStr = $signStr."interface_version=".$interface_version."&";
		$signStr = $signStr."merchant_code=".$merchant_code."&";
		$signStr = $signStr."notify_id=".$notify_id."&";
		$signStr = $signStr."notify_type=".$notify_type."&";
		$signStr = $signStr."order_amount=".$order_amount."&";
		$signStr = $signStr."order_no=".$order_no."&";
		$signStr = $signStr."order_time=".$order_time."&";
		$signStr = $signStr."trade_no=".$trade_no."&";
		$signStr = $signStr."trade_status=".$trade_status."&";
		$signStr = $signStr."trade_time=".$trade_time;
		if(openssl_verify($signStr,$dinpaySign,$pubKey,OPENSSL_ALGO_MD5)){
			echo "SUCCESS";
			$this->Online_api_model->update_order($uid,$orderNo,$orderAmount);
		}else{
			echo "<script>alert('交易失败,错误代码cw0009');window.close();</script>";exit;
		}
	}
	//智付RSA 点卡回调
	function DinpayrsaCard_callback(){
		$orderNo = $_POST['order_no'];//订单号
		$orderAmount = $_POST['card_amount'];//充值卡金额
		$card_actual_amount = $_POST["card_actual_amount"];//卡实际面值
		if($orderAmount != $card_actual_amount){
			echo '<script>alert("充值金额不等于销卡实际面值");</script>';exit;
		}
		$payconf = $this->Online_api_model->get_states_conf($orderNo);
		$user = $this->Online_api_model->get_in_cash($orderNo);
		if($user['make_sure'] == 1){
			echo '<script>alert("支付成功");</script>';exit;
		}elseif($user['make_sure'] == 2){
			echo '<script>alert("交易被取消！请联系管理员");</script>';exit;
		}
		$uid = $user['uid'];
		$notify_id = $_POST["notify_id"];
		$interface_version = $_POST["interface_version"];
		$sign_type = $_POST["sign_type"];
		$dinpaySign = base64_decode($_POST["sign"]);
		$order_no = $_POST["order_no"];
		$trade_no = $_POST["trade_no"];
		$pay_date = $_POST["pay_date"];
		$card_code = $_POST["card_code"];
		$card_no = $_POST["card_no"];
		$card_amount = $_POST["card_amount"];
		$card_actual_amount = $_POST["card_actual_amount"];
		$trade_status = $_POST["trade_status"];
		$signStr = "";
		$signStr = $signStr."card_actual_amount=".$card_actual_amount."&";
		$signStr = $signStr."card_amount=".$card_amount."&";
		$signStr = $signStr."card_code=".$card_code."&";
		$signStr = $signStr."card_no=".$card_no."&";
		$signStr = $signStr."interface_version=".$interface_version."&";
		$signStr = $signStr."merchant_code=".$merchant_code."&";
		$signStr = $signStr."notify_id=".$notify_id."&";
	    $signStr = $signStr."order_no=".$order_no."&";
	    $signStr = $signStr."pay_date=".$pay_date."&";
	 	$signStr = $signStr."trade_no=".$trade_no."&";
		$signStr = $signStr."trade_status=".$trade_status."&";
		$dinpay_public_key = openssl_get_publickey($payconf['public_key']);
		if(openssl_verify($signStr,$dinpaySign,$dinpay_public_key,OPENSSL_ALGO_MD5)){
			echo "SUCCESS";
			$this->Online_api_model->update_order($uid,$orderNo,$card_actual_amount);
		}else{
			echo "<script>alert('交易失败,错误代码cw0009');window.close();</script>";exit;
		}
	}

	//币币

	function Bbpay($action){
		$data = $_REQUEST;
		if($data['paytype'] == 1){
			$data['data'] = base64_decode(str_replace(' ', '+', $data['data']));
		}
		$this->add("data",$data);
		$this->display('member/rep/'.$action.'.html');
	}

	function bbpay_callback(){
		$this->load->library('payapi/BebePaymd5');
	    $data = json_decode(urldecode($_REQUEST['data']));
	    $order_no = $data->order;//订单号
	    $orderAmount = ($data->amount)/100;//订单金额
	    $payconf = $this->Online_api_model->get_states_conf($order_no);
	    $user = $this->Online_api_model->get_in_cash($order_no);
	    if($user['make_sure'] == 1){
	            echo '<script>alert("支付成功");</script>';exit;
	    }elseif($user['make_sure'] == 2){
	            echo '<script>alert("交易被取消！请联系管理员");</script>';exit;
	    }
	    $uid = $user['uid'];
	    $payset = $this->Online_api_model->get_payset($uid);
	    $bebepay=new BebePaymd5($payconf['pay_id'],$payconf['pay_key']);
	    $data1=$bebepay->returnData($_REQUEST['data']);
	    if($data1['status'] == 1){
	            $this->Online_api_model->update_order($uid,$order_no,$orderAmount);
	    }else {
	        echo "<script>alert('交易失败,错误代码cw0009');window.close();</script>";exit;
	    }

	}
	//盈宝
	function eypal($action){
			$data = $_REQUEST;
			$ptype = $this->input->get('ptype');
			$order_num = $this->input->get('order_num');
			if($ptype == 1){
				$redis = RedisConPool::getInstace();
				$redis_key = SITEID."_".$order_num;
				 $vdata2 = $redis->get($redis_key);
				if(empty($vdata2)){
					 echo "<script>alert('获取数据超时,请重新提交订单！');window.close();</script>";exit;
				}else{
			    	$data= json_decode($vdata2,TRUE);
			    	$redis->delete($redis_key);
				}
			}
			//p($data);die;
			$this->add("data",$data);
			$this->display('member/rep/'.$action.'.html');
	}


	function eypal_callback(){
		$orderNo = trim($_REQUEST['orderid']);//订单号
		$orderAmount = trim($_REQUEST['payamount']);//订单金额
		$payconf = $this->Online_api_model->get_states_conf($orderNo);
		$user = $this->Online_api_model->get_in_cash($orderNo);
		if($user['make_sure'] == 1){
			echo '<script>alert("支付成功");</script>';exit;
		}elseif($user['make_sure'] == 2){
			echo '<script>alert("交易被取消！请联系管理员");</script>';exit;
		}
		$uid = $user['uid'];
		//$userinfo = $this->Online_api_model->getinfo($uid);
		$payset = $this->Online_api_model->get_payset($uid);
		$version = trim($_REQUEST['version']);
		$rpartner = trim($_REQUEST['partner']);
		$orderid = trim($_REQUEST['orderid']);
		$payamount = trim($_REQUEST['payamount']);
		$opstate = trim($_REQUEST['opstate']);
		$orderno = trim($_REQUEST['orderno']);
		$eypaltime = trim($_REQUEST['eypaltime']);
		$message = trim($_REQUEST['message']);
		$paytype = trim($_REQUEST['paytype']);
		$remark = trim($_REQUEST['remark']);
		$tokenKey = $payconf['pay_key'];
		$sign = trim($_REQUEST['sign']);
		$signText = "version=".$version."&partner=".$rpartner."&orderid=".$orderid."&payamount=".$payamount."&opstate=".$opstate."&orderno=".$orderno."&eypaltime=".$eypaltime."&message=".$message."&paytype=".$paytype."&remark=".$remark."&key=".$tokenKey;

		$signValue = strtolower(md5($signText));
		if($opstate == 2){
			if($sign == $signValue){
				echo "success";
				$this->Online_api_model->update_order($uid,$orderNo,$orderAmount);
			}else{
				echo "<script>alert('交易失败,错误代码cw0009');window.close();</script>";exit;
			}
		}else{
			  echo "<script>alert('交易进行中,错误代码cw0010');window.close();</script>";exit;
		}
	}

	//摩宝
	function mobao($action){
		$this->load->library('payapi/MobaoPay');
		$data1 = $_REQUEST;
		$mbp_key = $data1['mbp_key'];
		$mobaopay_gateway = $data1['form_url'];
			// 初始化
		$cMbPay = new MobaoPay($mbp_key, $mobaopay_gateway);
		// 商户APINMAE，WEB渠道一般支付
		$data['apiName'] = $data1['apiName'];
		// 商户API版本
		$data['apiVersion'] =$data1['apiVersion'];
		// 商户在Mo宝支付的平台号
		$data['platformID'] = $data1['platformID'];
		// Mo宝支付分配给商户的账号
		$data['merchNo'] = $data1['merchNo'];
		// 商户通知地址
		$data['merchUrl'] = $data1['merchUrl'];
		// 银行代码，不传输此参数则跳转Mo宝收银台
		$data['bankCode'] = "";
		//商户订单号
		$data['orderNo'] = $data1['orderNo'];
		// 商户订单日期
		$data['tradeDate'] = $data1['tradeDate'];
		// 商户交易金额
		$data['amt'] = $data1['amt'];
		// 商户参数
		$data['merchParam'] = $data1['merchParam'];
		// 商户交易摘要
		$data['tradeSummary'] = $data1['tradeSummary'];
		// 准备待签名数据
		$str_to_sign = $cMbPay->prepareSign($data);
		// 数据签名
		$sign = $cMbPay->sign($str_to_sign);
		$data['signMsg'] = $sign;
		// 生成表单数据
		echo $cMbPay->buildForm($data, $mobaopay_gateway);
		}

	function mobao_callback(){
		$this->load->library('payapi/MobaoPay');
		$orderNo = trim($_REQUEST["orderNo"]);//订单号
		$orderAmount = trim($_REQUEST["tradeAmt"]);//订单金额
		$payconf = $this->Online_api_model->get_states_conf($orderNo);
		$user = $this->Online_api_model->get_in_cash($orderNo);
		if($user['make_sure'] == 1){
			echo '<script>alert("支付成功");</script>';exit;
		}elseif($user['make_sure'] == 2){
			echo '<script>alert("交易被取消！请联系管理员");</script>';exit;
		}
		$uid = $user['uid'];
		$payset = $this->Online_api_model->get_payset($uid);
		// 请求数据赋值
		$data = "";
		$data['apiName'] = $_REQUEST["apiName"];
		// 通知时间
		$data['notifyTime'] = $_REQUEST["notifyTime"];
		// 支付金额(单位元，显示用)
		$data['tradeAmt'] = $_REQUEST["tradeAmt"];
		// 商户号
		$data['merchNo'] = $_REQUEST["merchNo"];
		// 商户参数，支付平台返回商户上传的参数，可以为空
		$data['merchParam'] = $_REQUEST["merchParam"];
		// 商户订单号
		$data['orderNo'] = $_REQUEST["orderNo"];
		// 商户订单日期
		$data['tradeDate'] = $_REQUEST["tradeDate"];
		// Mo宝支付订单号
		$data['accNo'] = $_REQUEST["accNo"];
		// Mo宝支付账务日期
		$data['accDate'] = $_REQUEST["accDate"];
		// 订单状态，0-未支付，1-支付成功，2-失败，4-部分退款，5-退款，9-退款处理中
		$data['orderStatus'] = $_REQUEST["orderStatus"];
		// 签名数据
		$data['signMsg'] = $_REQUEST["signMsg"];
		$mobaopay_gateway = "https://trade.mobaopay.com/cgi-bin/netpayment/pay_gate.cgi";
		//print_r( $data);
		// 初始化
		$cMbPay = new MobaoPay($payconf['pay_key'],$mobaopay_gateway);
		// 准备准备验签数据
		$str_to_sign = $cMbPay->prepareSign($data);
		// 验证签名
		$resultVerify = $cMbPay->verify($str_to_sign, $data['signMsg']);
			if($resultVerify){
				if ('1' == $_REQUEST["notifyType"]){
					$this->Online_api_model->update_order($uid,$orderNo,$_REQUEST["tradeAmt"]);
				}
			}else{
				echo "<script>alert('交易失败,验证签名失败');window.close();</script>";exit;
			}
		}


	//汇付宝
	function heepay($action){
			$data = $_REQUEST;
			$ptype = $this->input->get('ptype');
			$order_num = $this->input->get('order_num');
			if($ptype == 1){
				$redis = RedisConPool::getInstace();
				$redis_key = SITEID."_".$order_num;
				 $vdata2 = $redis->get($redis_key);
				if(empty($vdata2)){
					 echo "<script>alert('获取数据超时,请重新提交订单！');window.close();</script>";exit;
				}else{
			    	$data= json_decode($vdata2,TRUE);
			    	$redis->delete($redis_key);
				}
			}
		$this->add("data",$data);
			unset($data['act']);
			$this->display('member/rep/'.$action.'.html');
	}


	function heepay_callback(){
		$orderNo = trim($_REQUEST['agent_bill_id']);//订单号
		$orderAmount = trim($_REQUEST['pay_amt']);//订单金额
		$payconf = $this->Online_api_model->get_states_conf($orderNo);
		$user = $this->Online_api_model->get_in_cash($orderNo);
		if($user['make_sure'] == 1){
			echo '<script>alert("支付成功");</script>';exit;
		}elseif($user['make_sure'] == 2){
			echo '<script>alert("交易被取消！请联系管理员");</script>';exit;
		}
		$uid = $user['uid'];
		$payset = $this->Online_api_model->get_payset($uid);
		$result=$_GET['result'];
		$pay_message=$_GET['pay_message'];
		$agent_id=$_GET['agent_id'];
		$jnet_bill_no=$_GET['jnet_bill_no']; //
		$agent_bill_id=$_GET['agent_bill_id'];//商户系统内部的定单号
		$pay_type=$_GET['pay_type'];
		$pay_amt=$_GET['pay_amt'];
		$remark=$_GET['remark'];
		$returnSign=$_GET['sign'];
		//商户的KEY
		$key = $payconf['pay_key'];
		$signStr='';
		$signStr  = $signStr . 'result=' . $result;
		$signStr  = $signStr . '&agent_id=' . $agent_id;
		$signStr  = $signStr . '&jnet_bill_no=' . $jnet_bill_no;
		$signStr  = $signStr . '&agent_bill_id=' . $agent_bill_id;
		$signStr  = $signStr . '&pay_type=' . $pay_type;
		$signStr  = $signStr . '&pay_amt=' . $pay_amt;
		$signStr  = $signStr .  '&remark=' . $remark;
		$signStr = $signStr . '&key=' . $key;
		$sign='';
		$sign=md5($signStr);
		if($result==1){
			if($sign==$returnSign){   //比较MD5签名结果 是否相等 确定交易是否成功  成功返回ok 否则返回error
				echo 'ok';
				$this->Online_api_model->update_order($uid,$orderNo,$orderAmount);

			}else{
				echo "<script>alert('交易失败,验证签名失败');window.close();</script>";exit;
			}
		}else{
				echo "<script>alert('交易发生未知错误！');window.close();</script>";exit;
		}

	}


	//新贝
	function xbeipay($action){
			$data = $_REQUEST;
			$this->add("data",$data);
			$this->display('member/rep/'.$action.'.html');
	}

	function xbeipay_callback(){
		$orderNo = trim($_REQUEST['OrderId']);//订单号
		$orderAmount = trim($_REQUEST['Amount']);//订单金额
		$payconf = $this->Online_api_model->get_states_conf($orderNo);
		$user = $this->Online_api_model->get_in_cash($orderNo);
		if($user['make_sure'] == 1){
			echo '<script>alert("支付成功");</script>';exit;
		}elseif($user['make_sure'] == 2){
			echo '<script>alert("交易被取消！请联系管理员");</script>';exit;
		}
		$uid = $user['uid'];
		$payset = $this->Online_api_model->get_payset($uid);
		$version = $_POST["Version"];
		$merchantCode = $_POST["MerchantCode"];
		$orderId = $_POST["OrderId"];
		$orderDate = $_POST["OrderDate"];
		$tradeIp = $_POST["TradeIp"];
		$serialNo = $_POST["SerialNo"];
		$amount = $_POST["Amount"];
		$payCode = $_POST["PayCode"];
		$state = $_POST["State"];
		$message = $_POST["Message"];
		$finishTime = $_POST["FinishTime"];
		$qq = $_POST["QQ"];
		$telephone = $_POST["Telephone"];
		$goodsName = $_POST["GoodsName"];
		$goodsDescription = $_POST["GoodsDescription"];
		$remark1 = $_POST["Remark1"];
		$remark2 = $_POST["Remark2"];
		$signValue = $_POST["SignValue"];

		$signText = 'Version=['.$version.']MerchantCode=['.$merchantCode.']OrderId=['.$orderId.']OrderDate=['.$orderDate.']TradeIp=['.$tradeIp.']SerialNo=['.$serialNo.']Amount=['.$amount.']PayCode=['.$payCode.']State=['.$state.']FinishTime=['.$finishTime.']TokenKey=['.$payconf['pay_key'].']';
		$md5Sign = strtoupper(md5($signText));

		if($signValue == $md5Sign){

			if($state=='8888') {
				echo 'ok';
				$this->Online_api_model->update_order($uid,$orderNo,$orderAmount);

			}else{
				echo "<script>alert('交易发生未知错误！');window.close();</script>";exit;
			}
		}else{
			echo "<script>alert('交易失败,验证签名失败');window.close();</script>";exit;
		}

	}

	//乐盈
	function funpay($action){
		$data = $_REQUEST;
		$this->add("data",$data);
		$this->display('member/rep/'.$action.'.html');
	}


	function funpay_callback(){
		$order_num = $_REQUEST['orderID'];//订单号
		$money = $_REQUEST['payAmount']/100;//订单金额
		$payconf = $this->Online_api_model->get_states_conf($order_num);//根据订单号获取配置信息
		$user = $this->Online_api_model->get_in_cash($order_num);//根据订单号 获取入款注单数据
		if($user['make_sure'] == 1){
			echo '<script>alert("支付成功");</script>';exit;
		}elseif($user['make_sure'] == 2){
			echo '<script>alert("交易被取消！请联系管理员");</script>';exit;
		}
		$uid = $user['uid'];
		$orderID = $_REQUEST["orderID"];
		$resultCode = $_REQUEST["resultCode"];
		$stateCode = $_REQUEST["stateCode"];
		$orderAmount = $_REQUEST["orderAmount"];
		$payAmount = $_REQUEST["payAmount"];
		$acquiringTime = $_REQUEST["acquiringTime"];
		$completeTime = $_REQUEST["completeTime"];
		$orderNo = $_REQUEST["orderNo"];
		$partnerID = $_REQUEST["partnerID"];
		$remark = $_REQUEST["remark"];
		$charset = $_REQUEST["charset"];
		$signType = $_REQUEST["signType"];
		$signMsg = $_REQUEST["signMsg"];
		$src = "orderID=".$orderID
		."&resultCode=".$resultCode
		."&stateCode=".$stateCode
		."&orderAmount=".$orderAmount
		."&payAmount=".$payAmount
		."&acquiringTime=".$acquiringTime
		."&completeTime=".$completeTime
		."&orderNo=".$orderNo
		."&partnerID=".$partnerID
		."&remark=".$remark
		."&charset=".$charset
		."&signType=".$signType;
			$pkey = $payconf['pay_key'];
			$src = $src."&pkey=".$pkey;
		if($stateCode == 2){
			if ($signMsg == md5($src))
			{
				echo "200";
				$this->Online_api_model->update_order($uid,$order_num,$money);
				exit;
			}else{
				echo "<script>alert('交易失败,错误代码cw0009');window.close();</script>";exit;
			}
		}else{
			    echo "<script>alert('交易正在处理中!错误代码cw0010');window.close();</script>";exit;
		}

	}

	function payreturnMd5($parms, $md5Key){
		$pStr = "orderNo".$parms['orderNo']."appType".$parms['appType']."orderAmount".$parms['orderAmount']."succ".$parms['succ']."encodeType".$parms['encodeType'].$md5Key;
		//echo $pStr."<br>";die;
		$pStrMd5=md5($pStr);
		$signMD5 = strtolower($pStrMd5);
		return $signMD5;
	}

	//乐盈
	function rfupay($action){
		$data = $_REQUEST;
			$ptype = $this->input->get('ptype');
			$order_num = $this->input->get('order_num');
			if($ptype == 1){
				$redis = RedisConPool::getInstace();
				$redis_key = SITEID."_".$order_num;
				 $vdata2 = $redis->get($redis_key);
				if(empty($vdata2)){
					 echo "<script>alert('获取数据超时,请重新提交订单！');window.close();</script>";exit;
				}else{
			    	$data= json_decode($vdata2,TRUE);
			    	$redis->delete($redis_key);
				}
			}
		$form_url = $data['form_url'];
		$myvars = $data['myvars'];
		$ch = curl_init($form_url);
		curl_setopt( $ch, CURLOPT_POST, 1);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $myvars);
		curl_setopt( $ch, CURLOPT_HEADER, 0);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 0);
		$response = curl_exec( $ch );
		if (curl_errno($ch)) {
			echo 'Curl error: ' . curl_error($ch);
		}
		curl_close($ch);

	}

	function rfupay_callback(){
		$goods = $_REQUEST["goods"];
		$order_num = $_REQUEST['orderNo'];//订单号
		$order_num = str_replace($goods,"",$order_num);
		$money = $_REQUEST['orderAmount'];//订单金额
		$payconf = $this->Online_api_model->get_states_conf($order_num);//根据订单号获取配置信息
		$user = $this->Online_api_model->get_in_cash($order_num);//根据订单号 获取入款注单数据
		if($user['make_sure'] == 1){
			echo '<script>alert("支付成功");</script>';exit;
		}elseif($user['make_sure'] == 2){
			echo '<script>alert("交易被取消！请联系管理员");</script>';exit;
		}
		$uid = $user['uid'];
		$data = array();
		$data['partyId'] = $_REQUEST["partyId"];
		$data['appType'] = $_REQUEST["appType"];
		$data['orderNo'] = $_REQUEST["orderNo"];
		$data['orderAmount'] = $_REQUEST["orderAmount"];
		$data['goods'] = $_REQUEST["goods"];
		$data['encodeType'] = $_REQUEST["encodeType"];
		$data['signMD5'] = $_REQUEST["signMD5"];
		$data['tradeNo'] = $_REQUEST["tradeNo"];
		$data['bankBillNo'] = $_REQUEST["bankBillNo"];
		$data['succ'] = $_REQUEST["succ"];
		$signCheck = $this->payreturnMd5($data,$payconf['pay_key']);
		if($signCheck==$data['signMD5']){
			if ($data['succ'] =="Y")
			{
				echo "success";
				$this->Online_api_model->update_order($uid,$order_num,$money);
				exit;
			}else{
				echo "<script>alert('交易失败,错误代码cw0009');window.close();</script>";exit;
			}
		}else{
			    echo "<script>alert('验签失败!错误代码cw0010');window.close();</script>";exit;
		}

	}

	//聚宝云
	function jubao($action){
		$data = $_REQUEST;
		$ptype = $this->input->get('ptype');
			$order_num = $this->input->get('order_num');
			if($ptype == 1){
				$redis = RedisConPool::getInstace();
				$redis_key = SITEID."_".$order_num;
				 $vdata2 = $redis->get($redis_key);
				if(empty($vdata2)){
					 echo "<script>alert('获取数据超时,请重新提交订单！');window.close();</script>";exit;
				}else{
			    	$data= json_decode($vdata2,TRUE);
			    	$redis->delete($redis_key);
				}
				$this->add("data",$data);
				$this->display('member/rep/jubao_wx.html');
			}
		$this->add("data",$data);
		$this->display('member/rep/'.$action.'.html');

	}

	function jubaopay_callback(){
	    $this->load->library('payapi/Jubaopay');
		$payconf1 = $this->Online_api_model->get_key_content('24');
		$conf = array();
		$conf['privKey'] = $payconf1['pay_key'];
		$conf['pubKey'] = $payconf1['public_key'];
		$conf['psw'] = $payconf1['file_key'];
		$message=$_REQUEST["message"];
		$signature=$_REQUEST["signature"];
		$jubaopay=new Jubaopay($conf);
		$jubaopay->decrypt($message);
		// 校验签名，然后进行业务处理
		$result=$jubaopay->verify($signature);
		if($result==1) {
		   // 得到解密的结果后，进行业务处理
		   $order_num = $jubaopay->getEncrypt("payid");
		   $money = $jubaopay->getEncrypt("amount");
		   $state = $jubaopay->getEncrypt("state");
			$payconf = $this->Online_api_model->get_states_conf($order_num);//根据订单号获取配置信息
			$user = $this->Online_api_model->get_in_cash($order_num);//根据订单号 获取入款注单数据
			if($user['make_sure'] == 1){
				echo '<script>alert("支付成功");</script>';exit;
			}elseif($user['make_sure'] == 2){
				echo '<script>alert("交易被取消！请联系管理员");</script>';exit;
			}
			$uid = $user['uid'];
		   if($state == 2){
		   		echo "success"; // 像服务返回 "success"
		   			$this->Online_api_model->update_order($uid,$order_num,$money);
				exit;
		   }else{
		   		echo "<script>alert('三方未到账!正在支付中!错误代码cw0010');window.close();</script>";exit;
		   }
		} else {
			echo "ok";exit;
		}

	}

	//宝付微信支付
	function baofoo_wx($action){
		$data = $_REQUEST;
		$this->add("data",$data);
		$form_url = $data['form_url'];
		$version = $data['version'];
		$txn_type = $data['txn_type'];
		$txn_sub_type = $data['txn_sub_type'];
		$terminalid = $data['terminalid'];
		$pay_id = $data['pay_id'];
		$data_type = $data['data_type'];
		$Encrypted = $data['data_content'];
		//var_dump($Encrypted);die;
		$terminalid = $data['terminalid'];
		$FormString ="正在处理中，请稍候。。。。。。。。。。。。。。"
	."<body onload=\"document.pay.submit()\"><form id=\"pay\" name=\"pay\" action=\"".$form_url."\" method=\"post\">"
	."<input name=\"version\" type=\"hidden\" id=\"version\" value=\"".$version."\" />"
	."<input name=\"txn_type\" type=\"hidden\" id=\"txn_type\" value=\"".$txn_type."\" />"
	."<input name=\"txn_sub_type\" type=\"hidden\" id=\"txn_sub_type\" value=\"".$txn_sub_type."\" />"
	."<input name=\"terminal_id\" type=\"hidden\" id=\"terminal_id\" value=\"".$terminalid."\" />"
	."<input name=\"member_id\" type=\"hidden\" id=\"member_id\" value=\"".$pay_id."\" />"
	."<input name=\"data_type\" type=\"hidden\" id=\"data_type\" value=\"".$data_type."\" />"
	."<textarea name=\"data_content\" style=\"display:none;\" id=\"data_content\">".$Encrypted."</textarea>"
	."</form></body>";
		echo $FormString;
			die();

	}

	function baofoo_wx_callback(){
		$this->load->library('payapi/SdkXML');
		$this->load->library('payapi/BFRSA');
		$terminal_id = $_REQUEST["terminal_id"];
		$member_id = $_REQUEST["member_id"];
		$payconf1 = $this->Online_api_model->get_baofoo_config($member_id,$terminal_id);
		$EndataContent =  $_REQUEST["data_content"];
		$public_key_hex = $payconf1['public_key'];
		$private_key = $_SERVER['DOCUMENT_ROOT']."/public/key/".$payconf1['key_domain'];
		//$public_key = $_SERVER['DOCUMENT_ROOT']."/public/key/bfkey_926001@@30592.cer";
		$public_key = pack("H*",$public_key_hex);
		$private_key_password = $payconf1['file_key'];
		$BFRsa = new BFRSA($private_key,$public_key,$private_key_password); //实例化加密类。
		$ReturnDecode = $BFRsa->decryptByPublicKey($EndataContent);//解密返回的报文
		$data_type = "xml";
		if(!empty($ReturnDecode)){//解析
		    $ArrayContent=array();
		    if($data_type =="xml"){
		        $ArrayContent = SdkXML::XTA($ReturnDecode);
		    }else{
		        $ArrayContent = json_decode($ReturnDecode,TRUE);
		    }
		}
		//var_dump($ArrayContent);die;
		$order_num = $ArrayContent["trans_id"];
		$money = $ArrayContent["succ_amt"]/100;
		//$payconf = $this->Online_api_model->get_states_conf($order_num);//根据订单号获取配置信息
		$user = $this->Online_api_model->get_in_cash($order_num);//根据订单号 获取入款注单数据
		//var_dump($order_num);die;
		if($user['make_sure'] == 1){
			echo '<script>alert("支付成功");</script>';exit;
		}elseif($user['make_sure'] == 2){
			echo '<script>alert("交易被取消！请联系管理员");</script>';exit;
		}
		$uid = $user['uid'];
		if($ArrayContent["resp_code"] == "0000"){
			if($ArrayContent["resp_msg"] == "交易成功"){
				echo "OK";
				$this->Online_api_model->update_order($uid,$order_num,$money);
				exit;
			}else{
				echo "<script>alert('交易失败!错误代码cw0009');window.close();</script>";exit;
			}
		}else{
			 echo "<script>alert('支付尚未成功!错误代码cw0010');window.close();</script>";exit;
		}
	}

		//哆啦宝
	function dlb($action){
		$data = $_REQUEST;
		unset($data['act']);
		unset($data['PHPSESSID']);
		$ptype = $this->input->get('ptype');
			$order_num = $this->input->get('order_num');
			if($ptype == 1){
				$redis = RedisConPool::getInstace();
				$redis_key = SITEID."_".$order_num;
				 $vdata2 = $redis->get($redis_key);
				if(empty($vdata2)){
					 echo "<script>alert('获取数据超时,请重新提交订单！');window.close();</script>";exit;
				}else{
			    	$data= json_decode($vdata2,TRUE);
			    	$redis->delete($redis_key);
				}
			}
		$sign_data = $data['body'];
		$body = explode(",",$sign_data);
		$sing = array();
		foreach ($body as $key => $value) {
			$temp = explode("->",$value);
			$sing[$temp[0]] = $temp[1];
		}
		$data['body'] = json_encode($sing);
		$data['secretkey'] = $data['secretkey'];
		$infoArr = $this->creatTokenPost($data);
		 switch ($infoArr['result']) {
                case 'success'://成功
                    $payurl = $infoArr['data']['url'];
                    return  array('code'=>200,'msg'=>'订单支付创建成功','url'=>array('payurl'=>$payurl));
                    break;
                case 'fail'://失败
                    return array('code'=>502,'msg'=>'订单支付创建失败');
                    break;
                case 'error'://异常
                    return array('code'=>501,'msg'=>'服务器繁忙，支付调用失败');
                    break;
                default:
                    break;
            }
	}

//生成token并提交
function creatTokenPost($data) {
	$this->load->library('payapi/QRcode');
    $str = "secretKey={$data['secretkey']}&timestamp={$data['timestamp']}&path={$data['path']}&body={$data['body']}";
    $token = strtoupper(sha1($str));
    $url = 'http://openapi.duolabao.cn/v1/customer/order/payurl/create';
    $post_data = $data['body'];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'accesskey: ' . $data['accesskey'],
        'timestamp: ' . $data['timestamp'],
        'token: ' . $token)
    );
    $info = curl_exec($ch);
    $infoArr = json_decode($info,true);
    $errorCorrectionLevel = 'L';
	$matrixPointSize = 10;
	 $qrcode = $infoArr['data']['url'];
	QRcode::png ( $qrcode, false, $errorCorrectionLevel, $matrixPointSize, 2 );
    curl_close($ch);
    return $infoArr;
}

	public function dlbpay_callback(){
		$order_num = $_REQUEST['requestNum'];//订单号
		$payconf = $this->Online_api_model->get_states_conf($order_num);//根据订单号获取配置信息
		$user = $this->Online_api_model->get_in_cash($order_num);//根据订单号 获取入款注单数据
		if($user['make_sure'] == 1){
			echo '<script>alert("支付成功");</script>';exit;
		}elseif($user['make_sure'] == 2){
			echo '<script>alert("交易被取消！请联系管理员");</script>';exit;
		}
		$uid = $user['uid'];
		$money = $user['deposit_money'];//订单金额
		$status =  $_REQUEST['status'];//支付状态
		if (isset($_SERVER['HTTP_TIMESTAMP'])) {
		    $timestamp = $_SERVER['HTTP_TIMESTAMP'];
		}
		if (isset($_SERVER['HTTP_TOKEN'])) {
		    $token= $_SERVER['HTTP_TOKEN'];
		}
		$str = "secretKey=".$payconf['public_key']."&timestamp=".$timestamp;
		$token1 = strtoupper(sha1($str));
		if($token1 == $token){
			if($status == "SUCCESS"){
				$this->Online_api_model->update_order($uid,$order_num,$money);
			}else{
				echo "<script>alert('交易失败!错误代码cw0009');window.close();</script>";exit;
			}
		}else{
			 echo "<script>alert('token验证失败!错误代码cw0010');window.close();</script>";exit;
		}

	}

		//支付宝
	function alipay($action){
		$data = $_REQUEST;
		$ptype = $this->input->get('ptype');
			$order_num = $this->input->get('order_num');
			if($ptype == 1){
				$redis = RedisConPool::getInstace();
				$redis_key = SITEID."_".$order_num;
				 $vdata2 = $redis->get($redis_key);
				if(empty($vdata2)){
					 echo "<script>alert('获取数据超时,请重新提交订单！');window.close();</script>";exit;
				}else{
			    	$data= json_decode($vdata2,TRUE);
			    	$redis->delete($redis_key);
				}
			}
		$this->add("data",$data);
		$this->display('member/rep/'.$action.'.html');

	}

	public function alipay_callback(){
		$order_num = $_REQUEST['out_trade_no'];//订单号
		$total_fee = $_REQUEST['total_fee'];//订单金额
		$trade_status = $_REQUEST['trade_status'];//支付状态
		$payconf = $this->Online_api_model->get_states_conf($order_num);//根据订单号获取配置信息
		$user = $this->Online_api_model->get_in_cash($order_num);//根据订单号 获取入款注单数据
		if($user['make_sure'] == 1){
			echo '<script>alert("支付成功");</script>';exit;
		}elseif($user['make_sure'] == 2){
			echo '<script>alert("交易被取消！请联系管理员");</script>';exit;
		}
		$uid = $user['uid'];
		$this->load->library('payapi/Alipay_notify');
		$data['partner'] = $payconf['pay_id'];
		$data['seller_id'] = $payconf['pay_id'];
		$data['key'] = $payconf['pay_key'];
		$data['notify_url'] = 'http://'.$payconf['url'].'/index.php/pay/alipay_callback';;
		$data['return_url'] = 'http://'.$payconf['url'].'/index.php/pay/return_url';
		$data['sign_type'] = strtoupper('MD5');
		$data['input_charset'] = strtolower('utf-8');
		//ca证书路径地址，用于curl中ssl校验
		//请保证cacert.pem文件在当前文件夹目录中
		$data['cacert'] = getcwd().'\\public\\key\\cacert.pem';
		$data['transport'] = 'http';
		$data['payment_type'] = "1";
		$data['service'] = "create_direct_pay_by_user";
		$alipaySubmit = new Alipay_notify($data);
		$result = $alipaySubmit->verifyNotify();
		if($result){
			if($trade_status === "TRADE_SUCCESS"){
				$this->Online_api_model->update_order($uid,$order_num,$total_fee);
			}else{
				echo "<script>alert('订单尚未支付成功!错误代码cw0009');window.close();</script>";exit;
			}
		}else{
			 echo "<script>alert('token验证失败!错误代码cw0010');window.close();</script>";exit;
		}
	}

			//商银信
	function scorepay($action){
		$data = $_REQUEST;
		$ptype = $this->input->get('ptype');
			$order_num = $this->input->get('order_num');
				$redis = RedisConPool::getInstace();
				$redis_key = SITEID."_".$order_num;
				 $vdata2 = $redis->get($redis_key);
				if(empty($vdata2)){
					 echo "<script>alert('获取数据超时,请重新提交订单！');window.close();</script>";exit;
				}else{
			    	$data= json_decode($vdata2,TRUE);
			    	$redis->delete($redis_key);
				}
				$this->add("data",$data);
			if($ptype == 1){
				$this->display('member/rep/'.$action.'.html');
			}else{
				$this->display('member/rep/scorepaywap.html');
			}
	}

	public	function scorepay_callback(){
		$order_num = $_REQUEST['outOrderId'];//订单号
		$total_fee = $_REQUEST['transAmt'];//订单金额
		$tradeStatus = $_REQUEST['tradeStatus'];//支付状态
		$notifyId = $_REQUEST['notifyId'];
		$merchantId = $_REQUEST['merchantId'];
		$payconf = $this->Online_api_model->get_states_conf($order_num);//根据订单号获取配置信息
		$user = $this->Online_api_model->get_in_cash($order_num);//根据订单号 获取入款注单数据
		if($user['make_sure'] == 1){
			echo '<script>alert("支付成功");</script>';exit;
		}elseif($user['make_sure'] == 2){
			echo '<script>alert("交易被取消！请联系管理员");</script>';exit;
		}
		$uid = $user['uid'];
		$this->load->library('payapi/Allscore_notify');
		$data['merchantId'] = $payconf['pay_id'];
		$data['key'] = $payconf['pay_key'];
		$data['notify_url'] = 'http://'.$payconf['f_url'].'/index.php/pay/scorepay_callback';;
		$data['return_url'] = 'http://'.$payconf['f_url'].'/index.php/pay/return_url';
		$data['input_charset'] = 'UTF-8';

        $data['request_gateway'] = 'https://paymenta.allscore.com/olgateway/serviceDirect.htm';
		$data['transport'] = 'http';
		$data['http_verify_url'] = "https://paymenta.allscore.com/olgateway/noticeQuery.htm?";
		$alipaySubmit = new Allscore_notify($data);
		$result = $alipaySubmit->verifyNotify();
		if($result){
			if($tradeStatus === '2'){
				echo "success";
				$this->Online_api_model->update_order($uid,$order_num,$total_fee);
			}else{
				echo "success";
				echo "<script>alert('订单尚未支付成功!错误代码cw0009');window.close();</script>";exit;
			}
		}else{
			echo "fail";
			 echo "<script>alert('验签失败!错误代码cw0010');window.close();</script>";exit;
		}
	}

	//公用的同步地址
	function return_url(){
		echo "<script>alert('您已成功支付!系统将会为您自动加款!');window.close();</script>";exit;
	}




}


