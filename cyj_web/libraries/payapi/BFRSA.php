<?php
//require_once '../Config/init.php';
if (!function_exists( 'hex2bin')) {
    function hex2bin($str) {
        $sbin = "";
        $len = strlen( $str );
        for ( $i = 0; $i < $len; $i += 2 ) {
            $sbin .= pack( "H*", substr( $str, $i, 2 ) );
        }
        return $sbin;
    }
}
	/*
	 * 宝付代付款SDK
	 * 供宝付商户快速集成使用
	 * 接口采用标准接口方法实现，数组作为传输数据类型
	 * 接口仅供参考，商户可自行根据实际需求修改此SDK
	 */

 class BFRSA{    
    public $private_key;
    public $public_key;
    
   /**
    * @Param  $private_key_path 商户证书路径（pfx）
    * @Param  $public_key_path 宝付公钥证书路径（cer）
    * @Param  $private_key_password 证书密码
    * @Param  $Debug 测试开关
    */
    function __construct($private_key_path,$public_key_path,$private_key_password){
         // 初始化商户私钥
         $pkcs12 = file_get_contents($private_key_path);
		 //var_dump($pkcs12);die;
         $private_key = array();
         openssl_pkcs12_read($pkcs12, $private_key, $private_key_password);
         //echo "私钥是否可用:", empty($private_key) == true ? '不可用':'可用', "\n";         
         //LOG::LogWirte(empty($private_key) == true ? "读取私钥是否可用:不可用":"读取私钥是否可用:可用");
         $this -> private_key = $private_key["pkey"];
		 
        //宝付公钥
		// $keyFile = file_get_contents($public_key_path);
		// var_dump(["keyFile"=>$keyFile,"bin2hex_keyFile"=>bin2hex($keyFile)]);
	//$this->public_key = openssl_get_publickey($keyFile); 
		$this->public_key = openssl_get_publickey($public_key_path);
		//$this->public_key = openssl_get_publickey($public_key_path);
	//$this->public_key = $public_key_path;
	
	/*//$this->public_key = $public_key_path;
        //echo "宝付公钥是否可用:", empty($this -> public_key) == true ? '不可用':'可用', "\n";
         //LOG::LogWirte(empty($this -> public_key) == true ? "读取宝付公钥是否可用:不可用":"读取宝付公钥是否可用:可用");
//var_dump($this -> private_key,$this->public_key);die;	
//$keyFile = file_get_contents($public_key_path);
	//$this->public_key = openssl_get_publickey($keyFile);	*/ 
    }
    
     // 私钥加密
    function encryptedByPrivateKey($data_content){
		
        $data_content = base64_encode($data_content);
         $encrypted = "";
         $totalLen = strlen($data_content);
         $encryptPos = 0;
         while ($encryptPos < $totalLen){
             openssl_private_encrypt(substr($data_content, $encryptPos, 117), $encryptData, $this -> private_key);
             $encrypted .= bin2hex($encryptData);
             $encryptPos += 117;
        }
        return $encrypted;
    }
		
	// 公钥解密
   function decryptByPublicKey($encrypted){
        $decrypt = "";
        $totalLen = strlen($encrypted);
        $decryptPos = 0;
            while ($decryptPos < $totalLen) {
                openssl_public_decrypt(hex2bin(substr($encrypted, $decryptPos, 256)), $decryptData, $this->public_key);
                $decrypt .= $decryptData;
                $decryptPos += 256;
            }
        $decrypt=base64_decode($decrypt);
        return $decrypt;
    }

}
?>