<?php

class RSA
{
	private $_privFile;
	private $_pubFile;
	
	private $_privKey;
	private $_pubKey;
	
	private $_algo ;
	private $_psw;
	
	private $siyou_key ;
	private $gg_key;
	public function __construct($conf)
	{
		/* $xml = new DOMDocument();
		$xml->load($conf);
		$items = $xml->getElementsByTagName("items");	
		$this->_privFile = $items->item(0)->getElementsByTagName('pfx')->item(0)->nodeValue;
		$this->_pubFile = $items->item(0)->getElementsByTagName('cer')->item(0)->nodeValue; 
		$this->_algo = OPENSSL_ALGO_SHA1;
		$this->_psw = $items->item(0)->getElementsByTagName('psw')->item(0)->nodeValue;*/
		$this->_psw = $conf['psw'];
		$this->siyou_key = $conf['privKey'];
		$this->gg_key = $conf['pubKey'];
		$this->_algo = OPENSSL_ALGO_SHA1;
		//var_dump($this->_psw,$this->siyou_key,$this->gg_key);
	}
	
	public function __destruct()
	{
		@ fclose($this->_privKey);
		@ fclose($this->_pubKey);
	}

	public function setupPrivKey()
	{
		/* if(is_resource($this->_privKey)){
			return true;
		} */

		//$prk = file_get_contents($this->_privFile);
		$prk = $this->siyou_key;
		$this->_privKey = openssl_pkey_get_private($prk);
		return true;
	}
	 
	public function setupPubKey()
	{
		/* if(is_resource($this->_pubKey)){
			return true;
		} */

		//$puk = file_get_contents($this->_pubFile);
		$puk = $this->gg_key;
		$this->_pubKey = openssl_pkey_get_public($puk);
		//var_dump($this->_pubKey);die;
		return true;
	}
	
	public function pubEncrypt($data)
	{
		if(!is_string($data)){
			return null;
		}
			
		$this->setupPubKey();
			
		$r = openssl_public_encrypt($data, $encrypted, $this->_pubKey);
		if($r){
			return base64_encode($encrypted);
		}
		return null;
	}
	
	public function sign($data)
	{
		$digest=$data.$this->_psw;
		//$privKey = file_get_contents($this->_privFile);
		$privKey = $this->siyou_key;
		/* echo '<pre>';
		var_dump($digest,$signature,$privKey,$this->_algo);die; */
		openssl_sign($digest, $signature, $privKey, $this->_algo);
		//var_dump($signature);exit;
		return base64_encode($signature);		
	}
	
	public function privDecrypt($encrypted)
	{
		if(!is_string($encrypted)){
			return null;
		}
			
		$this->setupPrivKey();
			
		$encrypted = base64_decode($encrypted);
	
		$r = openssl_private_decrypt($encrypted, $decrypted, $this->_privKey);
		if($r){
			return $decrypted;
		}
		return null;
	}
	
	public function verify($data,$signature)
	{				
		$digest=$data.$this->_psw;
		//$pubKey = file_get_contents($this->_pubFile);
		$pubKey = $this->gg_key;
		return openssl_verify($digest, base64_decode($signature), $pubKey, $this->_algo );		 
	}
	
	public function privEncrypt($data)
	{
		if(!is_string($data)){
			return null;
		}
		 
		$this->setupPrivKey();
		 
		$r = openssl_private_encrypt($data, $encrypted, $this->_privKey);
		if($r){
			return base64_encode($encrypted);
		}
		return null;
	}
	 
	public function pubDecrypt($crypted)
	{
		if(!is_string($crypted)){
			return null;
		}
		 
		$this->setupPubKey();
		 
		$crypted = base64_decode($crypted);

		$r = openssl_public_decrypt($crypted, $decrypted, $this->_pubKey);
		if($r){
			return $decrypted;
		}
		return null;
	}

}
