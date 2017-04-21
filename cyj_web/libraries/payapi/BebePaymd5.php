<?php

// 包含RSA、AES相关加解密包
if (!class_exists('CRYPT_MD5'))
    include 'CRYPT_MD5.php';

class BebePaymd5 {

// CURL 请求相关参数
    public $useragent = 'Bebepay MD5 PHPSDK v1.5.2';
    public $connecttimeout = 30;
    public $timeout = 30;
    public $ssl_verifypeer = FALSE;

// CURL 请求状态相关数据
    public $http_header = array();
    public $http_code;
    public $http_info;
    public $url;

// 相关配置参数
    protected $account;
    protected $merchantMd5Key;

// 加密解密算法工具
    private $MD5;

    /**
     * - $account 商户账号
     * - $merchantPublicKey 商户公钥
     * - $merchantPrivateKey 商户私钥
     * - $bbpayPublicKey 币币公钥
     *
     * @param string $account
     * @param string $merchantPublicKey
     * @param string $merchantPrivateKey
     * @param string $bbpayPublicKey
     */
    public function __construct($account,$md5key){
        $this->account = $account;
        $this->merchantMd5Key = $md5key;
        $this->MD5=new CRYPT_MD5();
    }

    public function pcWebPay($query){
        return $this->post($query);
    }


    public function returnData($data)
    {
        $return = $this->MD5DecryptData($data);
        if(!array_key_exists('sign', $return)){
            if(array_key_exists('error_code', $return))
                throw new bebepayException($return['error_msg'],$return['error_code']);
            throw new bebepayException('请求返回异常',1001);
        }else{
            //if( !$this->RSAVerify($return, $return['sign']) )
           //	throw new bebepayException('请求返回签名验证失败',1002);

          //	if(array_key_exists('error_code', $return))
         //		for api : query/order
            if(array_key_exists('error_code', $return) && !array_key_exists('status', $return))
                throw new bebepayException($return['error_msg'],$return['error_code']);
            unset($return['sign']);
            return $return;
        }
    }

    public function returnDataQuery($data)
    {
        // return $this->parseReturnData(str_replace("'","\"",str_replace(",",",\"",str_replace(":","\":",str_replace("{","{\"",$data)))));
        return $this->parseReturnData($data);
    }

    /**
     * @param $orderid 商户订单id
     * @param $bborderid 币币订单id
     * @return array
     */
    public function getOrder($orderid, $bborderid)
    {
        $query['orderid'] = $orderid;
        $query['bborderid'] = $bborderid;
        $url = 'http://api.bbpay.com/bbpayapi/api/query/queryOrder';
        $data = $this->http($url, 'POST',http_build_query(($this->post($query))));
        if($this->http_info['http_code'] == 405)
            throw new bebepayException('此接口不支持使用POST方法请求',1003);
        return $this->returnDataQuery($data);
    }

    /**
     * @param string $orderid 币币原订单号
     * @param string $merOrderid 商户原订单号
     * @param string $merOutOrderid 商户出账单号
     * @param int $amount 单位分
     * @return array
     */

    public function Orderrefund($orderid='',$merOrderid='',$merOutOrderid='',$amount=0){
        $query['orderid']=$orderid;
        $query['merOrderid']=$merOrderid;
        $query['merOutOrderid']=$merOutOrderid;
        $query['amount']=$amount;
        $url = 'http://api.bbpay.com/bbpayapi/api/return/returnOrder';
        $data = $this->http($url, 'POST',http_build_query(($this->post($query))));
        if($this->http_info['http_code'] == 405)
            throw new bebepayException('此接口不支持使用POST方法请求',1003);
        return $this->returnDataQuery($data);
    }


    /**
     * @param string $orderid 商户订单号
     * @param string $bborderid 币币订单号
     * @return array
     */
    public function getRefund($orderid='', $bborderid='')
    {
        $query['orderid'] = $orderid;
        $query['bborderid'] = $bborderid;
        $url = 'http://api.bbpay.com/bbpayapi/api/query/queryOutOrder';
        $data = $this->http($url, 'POST',http_build_query(($this->post($query))));
        if($this->http_info['http_code'] == 405)
            throw new bebepayException('此接口不支持使用POST方法请求',1003);
        return $this->returnDataQuery($data);
    }

        /**
         * @param string $settle_type //结算类型    1201：自助结算，1302：委托结算
         * @param double $amount
         * @param string $bank_config
         * @return array
         */

        public function fundSettle($settle_type='',$amount,$bank_config='')
        {
            $query['settle_type'] = $settle_type;
            $query['amount']=$amount;
            $query['bank_config']=$bank_config;
            $url = 'http://api.bbpay.com/bbpayapi/api/settle/fundsSettleApply';
            $data = $this->http($url, 'POST',http_build_query(($this->post( $query))));
            if($this->http_info['http_code'] == 405)
                throw new bebepayException('此接口不支持使用POST方法请求',1003);
            return $this->returnDataQuery($data);
        }

    /**
     * 回调返回数据解析函数
     * $data = $_POST['data']
     * $encryptkey = $_POST['encryptkey']
     *
     * @param string $data
     * @param string $encryptkey
     * @return array
     */
    public function callback($data,$encryptkey){
        return $this->parseReturn($data, $encryptkey);
    }
    /**
     *
     * 使用POST的模式发出API请求
     *
     * @param string $type
     * @param string $method
     * @param array $query
     * @return array
     */
    protected function post($query){
        $request = $this->buildRequest($query);
        if($this->http_info['http_code'] == 405)
            throw new bebepayException('此接口不支持使用POST方法请求',1004);
       return $request;
    }



    /**
     * 创建提交到币币的最终请求
     *
     * @param array $query
     * @return array
     */
    protected function buildRequest(array $query){
        //if(!array_key_exists('merchantaccount', $query))
        //$query['merchantaccount'] = $this->account;
        $sign = $this->MD5Sign($query);
        $query['sign'] = $sign;
        $request = array();
        $request['merchantaccount'] = $this->account;
        $request['encryptkey'] = 1;
        $request['data'] = $this->MD5EncryptRequest($query);
        return $request;
    }

    /**
     *
     * @param string $url
     * @param string $method
     * @param string $postfields
     * @return mixed
     */
    protected function http($url, $method, $postfields = NULL) {
        $this->http_info = array();
        $ci = curl_init();
        curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
        curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ci, CURLOPT_HTTPHEADER, array('Expect:'));
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
        curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
        curl_setopt($ci, CURLOPT_HEADER, FALSE);
        $method = strtoupper($method);
        switch ($method) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if (!empty($postfields))
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
                break;
            case 'DELETE':
                curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($postfields))
                    $url = "{$url}?{$postfields}";
        }
        curl_setopt($ci, CURLOPT_URL, $url);
        $response = curl_exec($ci);
        $this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
        $this->http_info = array_merge($this->http_info, curl_getinfo($ci));
        $this->url = $url;
        curl_close ($ci);
        return $response;
    }

    protected function parseReturnClearData($data){
        if(strpos($data, 'data')===true )
        {
            $return = json_decode($data,true);

            if(array_key_exists('error_code', $return) && !array_key_exists('status', $return))
                throw new bebepayException($return['error_msg'],$return['error_code']);
            return $this->parseReturn($return['data'], $return['encryptkey']);
        }else{
            return $data;
        }
    }

    protected function parseReturnData($data){
        $return = json_decode($data,true);
        if(array_key_exists('error_code', $return) && !array_key_exists('status', $return))
            throw new bebepayException($return['error_msg'],$return['error_code']);
        if(empty($return['data']) || $return['data'] === ""){
            return array('error_code'=>'100006','error_msg'=>'数据为空,请检查你的传入参数');
        }
        return $this->parseReturn($return['data']);
    }
    protected function parseReturn($data){
        $return = $this->MD5DecryptData($data);
        if(!array_key_exists('sign', $return)){
            if(array_key_exists('error_code', $return))
                throw new bebepayException($return['error_msg'],$return['error_code']);
            throw new bebepayException('请求返回异常',1001);
        }else{
            //if( !$this->RSAVerify($return, $return['sign']) )
            //	throw new bebepayException('请求返回签名验证失败',1002);

            //		if(array_key_exists('error_code', $return))
            //		for api : query/order
            if(array_key_exists('error_code', $return) && !array_key_exists('status', $return))
                throw new bebepayException($return['error_msg'],$return['error_code']);
            unset($return['sign']);
            return $return;
        }
    }

    protected  function  MD5Sign($query){
        ksort($query);
        $tempValstr="";
        foreach($query as $k=>$v){
            $tempValstr.=$v;
        }
        $finallyStr=$tempValstr.$this->merchantMd5Key;
        $sign= md5($finallyStr);
        return $sign;
    }


        protected  function  MD5EncryptRequest($query){
            $data=urlencode(json_encode($query));
            return $data;
        }

        public  function  MD5DecryptData($query){
        $data=json_decode(urldecode($query),true);
        $sign=$data['sign'];
        if(array_key_exists('sign', $data)){
            unset($data['sign']);
        }
        if($sign==$this->MD5Sign($data)){
            $data['sign']=$sign;
            return $data;
        }
        throw new bebepayException('Error Message','the Md5 key invalid');
    }
    /**
     * Get the header info to store.
     */
    public function getHeader($ch, $header) {
        $i = strpos($header, ':');
        if (!empty($i)) {
            $key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
            $value = trim(substr($header, $i + 2));
            $this->http_header[$key] = $value;
        }
        return strlen($header);
    }
}

class bebepayException extends Exception{

}
?>