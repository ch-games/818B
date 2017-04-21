<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Dinpayrsacard_model extends Online_api_model
{
    function __construct()
    {
        parent::__construct();
        $this->init_db();

    }

    function get_all_info($order_num, $p3_Amt, $p4_FrpId, $pa7_cardAmt, $pa8_cardNo, $pa9_cardPwd, $payconf)
    {
        $interface_version = "V3.0";
        $service_type = "dcard_pay";
        $sign_type = "RSA-S";
        //商戶號，上線時請更換為商家自己ID
        $merchant_code = $payconf['pay_id'];
        $order_no = $order_num;
        $order_time = date('Y-m-d H:i:s');
        $card_code = $p4_FrpId;
        $card_amount = $pa7_cardAmt;
        $notify_url = 'http://' . $payconf['f_url'] . '/index.php/pay/DinpayrsaCard_callback';
        $card_no = $pa8_cardNo;
        $card_pwd = $pa9_cardPwd;
        $encrypt = $card_no . "|" . $card_pwd;
        $encryption_key = $payconf['public_key'];
        $merchant_private_key = $payconf['pay_key'];
        $encryption_key = openssl_get_publickey($encryption_key);
        openssl_public_encrypt($encrypt, $info, $encryption_key);
        $encrypt_info = base64_encode($info);
        $signStr = "";
        $signStr = $signStr . "card_amount=" . $card_amount . "&";
        $signStr = $signStr . "card_code=" . $card_code . "&";
        $signStr = $signStr . "encrypt_info=" . $encrypt_info . "&";
        $signStr = $signStr . "interface_version=" . $interface_version . "&";
        $signStr = $signStr . "merchant_code=" . $merchant_code . "&";
        $signStr = $signStr . "notify_url=" . $notify_url . "&";
        $signStr = $signStr . "order_no=" . $order_no . "&";
        $signStr = $signStr . "order_time=" . $order_time . "&";
        $signStr = $signStr . "service_type=" . $service_type;
        $merchant_private_key = openssl_get_privatekey($merchant_private_key);
        openssl_sign($signStr, $sign_info, $merchant_private_key, OPENSSL_ALGO_MD5);
        $sign = urlencode(base64_encode($sign_info));
        $encrypt_info = urlencode($encrypt_info);
        $postdata = "";
        $postdata = $postdata . "interface_version=" . $interface_version . "&";
        $postdata = $postdata . "service_type=" . $service_type . "&";
        $postdata = $postdata . "sign_type=" . $sign_type . "&";
        $postdata = $postdata . "merchant_code=" . $merchant_code . "&";
        $postdata = $postdata . "order_no=" . $order_no . "&";
        $postdata = $postdata . "order_time=" . $order_time . "&";
        $postdata = $postdata . "card_code=" . $card_code . "&";
        $postdata = $postdata . "card_amount=" . $card_amount . "&";
        $postdata = $postdata . "notify_url=" . $notify_url . "&";
        $postdata = $postdata . "encrypt_info=" . $encrypt_info . "&";
        $postdata = $postdata . "sign=" . $sign;
        return $postdata;
    }


}