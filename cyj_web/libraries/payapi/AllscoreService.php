<?php
/* *
 * 类名：AllscoreService
 * 功能：商银信接口构造类
 * 详细：构造商银信接口请求参数
 * 版本：1.0
 * 日期：2011-11-03
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究商银信接口使用，只是提供一个参考。
 */

require_once("Allscore_Submit.php");
class AllscoreService {

	var $allscore_config;
	/**
	 *商银信网关地址
	 */



	function __construct($allscore_config){
		$this->allscore_config = $allscore_config;
	}
    function AllscoreService($allscore_config) {
    	$this->__construct($allscore_config);
    }
	/**
     * 构造即时到帐接口
     * @param $para_temp 请求参数数组
     * @return 表单提交HTML信息
     */
	function bankPay($para_temp) {
		//设置按钮名称
		$button_name = "商银信网银支付";
		//生成表单提交HTML文本信息
		$allscoreSubmit = new Allscore_Submit();
		$html_text = $allscoreSubmit->buildForm($para_temp, $this->allscore_config['request_gateway'], "get", $button_name,$this->allscore_config);
		return $html_text;
	}

	/**
     * 构造多卡支付接口
     * @param $para_temp 请求参数数组
     * @return 表单提交HTML信息
     */
	function quickPay($para_temp) {


		//设置按钮名称
		$button_name = "商银信快捷支付";
		//生成表单提交HTML文本信息
		$allscoreSubmit = new Allscore_Submit();
		$html_text = $allscoreSubmit->buildForm($para_temp, $this->allscore_config['request_gateway'], "get", $button_name,$this->allscore_config);

		return $html_text;
	}


	/**
     * 构造查询接口
     * @param $para_temp 请求参数数组
     * @return 表单提交HTML信息
     */
	function query($para_temp) {


		//设置按钮名称
		$button_name = "订单查询";
		//生成表单提交HTML文本信息
		$allscoreSubmit = new AllscoreSubmit();
		$html_text = $allscoreSubmit->buildAutoForm($para_temp, $this->allscore_config['query_gateway'], "get", $button_name,$this->allscore_config);

		return $html_text;
	}



	/**
     * 构造提交地址
     * @param $para_temp 请求参数数组
     * @return 表单提交地址
     */
	function createBankUrl($para_temp) {

		//生成提交地址
		$allscoreSubmit = new AllscoreSubmit();
		$ItemUrl = $allscoreSubmit->buildRequestUrl($this->allscore_config['request_gateway'],$para_temp,$this->allscore_config);

		return $ItemUrl;
	}


	/**
     * 构造提交地址
     * @param $para_temp 请求参数数组
     * @return 表单提交地址
     */
	function createQuickUrl($para_temp) {

		//生成提交地址
		$allscoreSubmit = new AllscoreSubmit();
		$ItemUrl = $allscoreSubmit->buildRequestUrl($this->allscore_config['request_gateway'],$para_temp,$this->allscore_config);

		return $ItemUrl;
	}


	/**
     * 构造网银退货地址
     * @param $para_temp 请求参数数组
     * @return 退货结果
     */
	function createBankRefundUrl($para_temp) {

		//生成提交地址
		$allscoreSubmit = new AllscoreSubmit();
		$ItemUrl = $allscoreSubmit->buildRequestUrl($this->allscore_config['bank_refund_gateway'],$para_temp,$this->allscore_config);

		return $ItemUrl;
	}


	/**
	 * 构造快捷退货地址
	 * @param $para_temp 请求参数数组
	 * @return 退货结果
	 */
	function createQuickRefundUrl($para_temp) {

	    //生成提交地址
	    $allscoreSubmit = new AllscoreSubmit();
	    $ItemUrl = $allscoreSubmit->buildRequestUrl($this->allscore_config['quick_refund_gateway'],$para_temp,$this->allscore_config);

	    return $ItemUrl;
	}




	/**
     * 构造商银信其他接口
     * @param $para_temp 请求参数数组
     * @return 表单提交HTML信息
     */
	function allscore_interface($para_temp) {
		//获取远程数据
		$allscoreSubmit = new AllscoreSubmit();
		$html_text = "";
		//请根据不同的接口特性，选择一种请求方式
		//1.构造表单提交HTML数据:（$method可赋值为get或post）
		//$allscoreSubmit->buildForm($para_temp, $this->allscore_gateway, "get", $button_name,$this->allscore_config);

		return $html_text;
	}
}
?>