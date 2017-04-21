<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Newips_model extends Online_api_model
{
    function __construct()
    {
        parent::__construct();
        $this->init_db();
    }

    function get_all_info($url, $order_num, $s_amount, $bank, $pay_id, $pay_key, $username, $vircarddoin)
    {
        $req_url = 'http://' . $url . '/index.php/pay/payfor';//跳轉地址
        $ServerUrl = 'http://' . $url . '/index.php/pay/newips_callback';//商戶後臺通知地址
        $form_url = 'https://newpay.ips.com.cn/psfp-entry/gateway/payment.html';//第三方地址
        $data = array();
        $data['req_url'] = $req_url;
        $data['form_url'] = $form_url;
        //獲取輸入參數
        $pVersion = 'v1.0.0';//版本號
        $pMerCode = $pay_id;//商戶號
        $pMerName = '';//商戶名
        $pMerCert = $pay_key;//商戶證書
        $pAccount = $vircarddoin;//賬戶號
        $pMsgId = 'msg4488';//消息編號
        $pReqDate = date('YmdHis');//商戶請求時間
        $pMerBillNo = $order_num;//商戶訂單號
        $pAmount = $s_amount;//訂單金額
        $pDate = date('Ymd');//訂單日期
        $pCurrencyType = 156;//幣種
        $pGatewayType = '01';//支付方式
        $pLang = 'GB';//語言
        $pMerchanturl = $ServerUrl;//支付結果成功返回的商戶URL
        $pFailUrl = $ServerUrl;//支付結果失敗返回的商戶URL
        $pAttach = $username;//商戶數據包
        $pOrderEncodeTyp = 5;//訂單支付接口加密方式 默認為5#md5
        $pRetEncodeType = 17;//交易返回接口加密方式
        $pRetType = 1;//返回方式
        $pServerUrl = $ServerUrl;//Server to Server返回頁面
        $pBillEXP = 1;//訂單有效期(過期時間設置為1小時)
        $pGoodsName = $username;//商品名稱
        $pIsCredit = 0;//直連選項
        $pBankCode = 00018;//銀行號
        $pProductType = 1;//產品類型

        $reqParam = "商戶號" . $pMerCode
            . "商戶名" . $pMerName
            . "賬戶號" . $pAccount
            . "消息編號" . $pMsgId
            . "商戶請求時間" . $pReqDate
            . "商戶訂單號" . $pMerBillNo
            . "訂單金額" . $pAmount
            . "訂單日期" . $pDate
            . "幣種" . $pCurrencyType
            . "支付方式" . $pGatewayType
            . "語言" . $pLang
            . "支付結果成功返回的商戶URL" . $pMerchanturl
            . "支付結果失敗返回的商戶URL" . $pFailUrl
            . "商戶數據包" . $pAttach
            . "訂單支付接口加密方式" . $pOrderEncodeTyp
            . "交易返回接口加密方式" . $pRetEncodeType
            . "返回方式" . $pRetType
            . "Server to Server返回頁面 " . $pServerUrl
            . "訂單有效期" . $pBillEXP
            . "商品名稱" . $pGoodsName
            . "直連選項" . $pIsCredit
            . "銀行號" . $pBankCode
            . "產品類型" . $pProductType;


        if ($pIsCredit == 0) {
            $pBankCode = "";
            $pProductType = '';
        }

        //請求報文的消息體
        $strbodyxml = "<body>"
            . "<MerBillNo>" . $pMerBillNo . "</MerBillNo>"
            . "<Amount>" . $pAmount . "</Amount>"
            . "<Date>" . $pDate . "</Date>"
            . "<CurrencyType>" . $pCurrencyType . "</CurrencyType>"
            . "<GatewayType>" . $pGatewayType . "</GatewayType>"
            . "<Lang>" . $pLang . "</Lang>"
            . "<Merchanturl>" . $pMerchanturl . "</Merchanturl>"
            . "<FailUrl>" . $pFailUrl . "</FailUrl>"
            . "<Attach>" . $pAttach . "</Attach>"
            . "<OrderEncodeType>" . $pOrderEncodeTyp . "</OrderEncodeType>"
            . "<RetEncodeType>" . $pRetEncodeType . "</RetEncodeType>"
            . "<RetType>" . $pRetType . "</RetType>"
            . "<ServerUrl>" . $pServerUrl . "</ServerUrl>"
            . "<BillEXP>" . $pBillEXP . "</BillEXP>"
            . "<GoodsName>" . $pGoodsName . "</GoodsName>"
            . "<IsCredit>" . $pIsCredit . "</IsCredit>"
            . "<BankCode>" . $pBankCode . "</BankCode>"
            . "<ProductType>" . $pProductType . "</ProductType>"
            . "</body>";

        $Sign = $strbodyxml . $pMerCode . $pMerCert;//簽名明文


        $pSignature = md5($strbodyxml . $pMerCode . $pMerCert);//數字簽名
        //請求報文的消息頭
        $strheaderxml = "<head>"
            . "<Version>" . $pVersion . "</Version>"
            . "<MerCode>" . $pMerCode . "</MerCode>"
            . "<MerName>" . $pMerName . "</MerName>"
            . "<Account>" . $pAccount . "</Account>"
            . "<MsgId>" . $pMsgId . "</MsgId>"
            . "<ReqDate>" . $pReqDate . "</ReqDate>"
            . "<Signature>" . $pSignature . "</Signature>"
            . "</head>";

//提交給網關的報文
        $strsubmitxml = "<Ips>"
            . "<GateWayReq>"
            . $strheaderxml
            . $strbodyxml
            . "</GateWayReq>"
            . "</Ips>";
        $data['strsubmitxml'] = $strsubmitxml;

        return $data;
    }
}