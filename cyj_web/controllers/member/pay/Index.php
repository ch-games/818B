<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Index extends MY_Controller {
	public function __construct()
	{
		parent::__construct();
		$this->load->model('member/pay2/Online_api_model');
		$this->load->model('Common_model');
		$this->Online_api_model->login_check($_SESSION['uid']);
	}

	/*
	**1   新生
	**2   易寶
	**3   環迅
	**4   幣付寶
	**5   通匯卡
	**6   寶付
	**7   智付
	**8   匯潮
	**9   國付寶
	**10  融寶
	**11  快捷通
	**12  新環迅
	**13  易寶點卡
	**14  智付點卡
	**15  盈寶
	*/
	public function index(){
		//判斷用戶是否綁定銀行卡
		$userinfo = $this->Common_model->get_user_info($_SESSION['uid']);
		if(empty($userinfo['pay_num'])){
			echo "<script>alert('為方便後續能及時申請出款到賬，請在入款前設置您的銀行卡等信息，請如實填寫！');</script>";
			$this->hk_money($this->router->class,$this->router->method);
		}
		$level_info = $this->Common_model->get_user_level_info($userinfo['level_id']);
		//線上入款最小金額 最大金額
		$_SESSION['ol_catm_max'] = $level_info['ol_catm_max'];
		$_SESSION['ol_catm_min'] = $level_info['ol_catm_min'];
		if($_SESSION['pay']['is_card'] == 1){
			unset($_SESSION['pay']);
		}
		if($_SESSION['pay']['payid'] > 0){
			$status = $_SESSION['pay'];    //防止用戶壹直在銀行卡頁面刷新 而更換了支付方式   在成功跳轉到第三方的時候清除此session
		}
		$pay_type = $this->Online_api_model->get_paytype($userinfo,$status,0);
		if($pay_type['payid']>0){
			$_SESSION['pay'] = $pay_type;
			$this->add('bank_info',$this->Online_api_model->get_bankinfo($pay_type['paytype']));
		}else{
			show_error('非常抱歉，在線支付暫時無法使用！請聯系客服!<a href="javascript:history.go(-1)">返回</a>', 200, '提示');
		}
		$this->add('payset',$level_info);
		$this->add('info',$userinfo);
		$this->add('order_num',$this->Online_api_model->get_order_num());
		$this->add('copy_right',$this->Common_model->get_copyright());
		$this->display('member/pay2/online_api_index.html');
	}

	/*
	**確認訂單頁面
	*/
	public function confirm_order(){
		if(empty($_SESSION['pay']['payid'])){
			show_error('參數錯誤，請聯系管理員!<a href="javascript:history.go(-1)">返回</a>', 200, '提示');
		}
		$bank      = $this->input->post('bank');
		$s_amount  = floatval($this->input->post('s_amount'));
		$order_num = $this->input->post('order_num');

		$this->load->helper('url');
		if(empty($order_num)){
			redirect('index.php/member/income/');
		}
		$this->add('bank',$bank);
		$this->add('order_num',$order_num);
		$this->add('s_amount',$s_amount);
		$this->add('username',$_SESSION['username']);
		$this->add('url',"/member/pay/Index/common");
		$this->add('copy_right',$this->Common_model->get_copyright());
		$this->display('member/pay2/online_confirm_order.html');

		/*if($_SESSION['pay']['paytype'] == 5){
			$this->add('time',date('Y-m-d H:i:s'));
			$this->add('payid',$_SESSION['pay']['payid']);
			$this->display('member/pay/huitong_confirm_order.html');
		}
		if($_SESSION['pay']['paytype'] == 2){
			$this->display('member/pay/yeepay_confirm_order.html');
		}
		else{
			$this->display('member/pay/online_confirm_order.html');
		}*/
	}


	//通用入口方法
	function common(){
		$order_num = $this->input->post('order_num'); //訂單號
		$bank = $this->input->post('bank');   //銀行慘數
		$s_amount = floatval($this->input->post('s_amount'));   //支付金額
		$flag = $this->Online_api_model->order_num_unique($order_num);
		$payconf = $this->Online_api_model->get_pay_config($_SESSION['pay']['payid']);
		if($flag > 0){
			show_error('參數錯誤，請再次嘗試!<a href="javascript:history.go(-1)">返回</a>', 200, '提示');
		}
		$result = $this->Online_api_model->write_in_record($_SESSION['uid'],$_SESSION['agent_id'],$s_amount,$order_num);
		if($result){
			$type = $this->Online_api_model->get_pay_act($_SESSION['pay']['paytype']);
			$this->load->model('member/pay/'.$type.'_model');
			$mod = $type.'_model';
			if ($_SESSION['pay']['paytype'] == 1||$_SESSION['pay']['paytype'] == 9||$_SESSION['pay']['paytype'] == 10||$_SESSION['pay']['paytype'] == 11){
				$data = $this->$mod->get_all_info($payconf['f_url'],$order_num,$s_amount,$bank,$payconf['pay_id'],$payconf['pay_key'],$payconf['vircarddoin']);
				if($_SESSION['pay']['paytype'] == 10){
					unset($_SESSION['pay']);
					die;
				}
				unset($_SESSION['pay']);
			}else if($_SESSION['pay']['paytype'] == 4){
				$data = $this->$mod->get_all_info($payconf['f_url'],$order_num,$s_amount,$bank,$payconf['pay_id'],$payconf['pay_key'],$_SESSION['username']);
				unset($_SESSION['pay']);exit;

			}else if($_SESSION['pay']['paytype'] == 5){
				$data = $this->$mod->get_all_info($payconf['f_url'],$order_num,$s_amount,$bank,$payconf['pay_id'],$payconf['pay_key'],$returnParams,$TradeDate);
				unset($_SESSION['pay']);

			}else if($_SESSION['pay']['paytype'] == 6){
				$data = $this->$mod->get_all_info($payconf['f_url'],$order_num,$s_amount,$bank,$payconf['pay_id'],$payconf['pay_key'],$_SESSION['username'],$payconf['terminalid']);
				unset($_SESSION['pay']);
				//var_dump($data);die;

			}else if($_SESSION['pay']['paytype'] == 12){
				$data = $this->$mod->get_all_info($payconf['f_url'],$order_num,$s_amount,$bank,$payconf['pay_id'],$payconf['pay_key'],$_SESSION['username'],$payconf['vircarddoin']);
				unset($_SESSION['pay']);
				//var_dump($data);die;
			}else{
				var_dump($_SESSION);die;
				$data = $this->$mod->get_all_info($payconf['f_url'],$order_num,$s_amount,$bank,$payconf['pay_id'],$payconf['pay_key']);
				unset($_SESSION['pay']);
				//var_dump($data);die;
			}

			$this->add("data",$data);
			$this->display('member/pay/'.$type.'.html');
		}else{
			show_error('支付失敗，請三秒後重試!<a href="javascript:history.go(-1)">返 回</a>', 200, '提示');
		}
	}
}