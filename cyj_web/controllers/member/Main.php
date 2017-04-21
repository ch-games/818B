<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends MY_Controller {
	public function __construct() {
		parent::__construct();
	}
	//我的賬戶
	public function account(){
		$this->load->model('member/Account_model');
		$this->load->model('Common_model');
		$in_type = $this->input->get('url');
		$redis = RedisConPool::getInstace();
		$redis_key = SITEID.'_userinfo_'.$_SESSION['uid'];
		$userinfo = $redis->get($redis_key);
		if ($userinfo) {
			$userinfo = json_decode($userinfo,TRUE);
		}else{
			$userinfo = $this->Common_model->get_user_info($_SESSION['uid']);
			$redis->setex($redis_key,'5',json_encode($userinfo));
		}

		$copyright = $this->Common_model->get_copyright();
		$video_config = explode(',',$copyright['video_module']);

		//余額總計計算
		$allmoney = $userinfo['money'];
		foreach ($video_config as $key => $val) {
			if ($val != 'eg') {
				$allmoney += $userinfo[$val.'_money'];
			}else{
				unset($video_config[$key]);
			}
		}


		$video_config1 = array_slice($video_config, 0,2);
		$video_config2 = array_slice($video_config, 2,3);
		$video_config3 = array_slice($video_config, 5,3);
		$video_config4 = array_slice($video_config, 8,3);

		$this->add('video_config1',$video_config1);
		$this->add('video_config2',$video_config2);
		$this->add('video_config3',$video_config3);
		$this->add('video_config4',$video_config4);
		//p($video_config);die;
		//判斷是否開啟自助返水
		$is_self = $this->Account_model->is_self_user();
		$this->add('is_self',$is_self['is_self_fd']);

		//$cash_data = $this->Account_model->get_cash_limit();
		//最近10記錄redis緩存
		$redis_key = SITEID.'_cash_limit_'.$_SESSION['uid'];
		$cash_data = $redis->get($redis_key);
		//$cash_data = '';
		if ($cash_data) {
			$cash_data = json_decode($cash_data,TRUE);
		}else{
			$cash_data = $this->Account_model->get_cash_limit();
			$redis->setex($redis_key,'5',json_encode($cash_data));
		}

		$this->add('cash_data',$cash_data);//最近十筆記錄
		$this->add('allmoney',$allmoney);//余額總計
		$this->add('userinfo',$userinfo);//用戶信息
		$this->add('video_config',$video_config);//站點視訊模塊
		//$this->display('member/userinfo.html');
		//p($in_type);die;
		$this->display('web_public/member/new_member.html');
	}
	//投註咨詢
	public function betting(){
		$this->display('web_public/member/betting.html');
	}
	//彩票投註咨詢
	public function lottery(){
		$this->display('web_public/member/lottery.html');
	}
	//自助反水
	public function defection(){
		$this->display('web_public/member/defection.html');
	}
	//銀行交易
	public function bank(){
		$this->display('web_public/member/bank.html');
	}
	//交易記錄
	public function transaction(){
		$this->display('web_public/member/transaction.html');
	}
	//往來記錄
	public function contacts(){
		$this->display('web_public/member/contacts.html');
	}

	//線上存款
	public function onlinein(){
		$this->display('web_public/member/onlinein.html');
	}
	//線上取款
	public function onlineout(){
		$this->display('web_public/member/onlineout.html');
	}
	//報表統計
	public function report(){
		$this->display('web_public/member/report.html');
	}
	//我要推廣
	public function spread(){
		$this->display('web_public/member/spread.html');
	}
	//排行榜
	public function ranking(){
		$this->display('web_public/member/ranking.html');
	}
	//最新消息
	public function news(){
		$this->display('web_public/member/news.html');
	}
	//歷史消息
	public function history(){
		$this->display('web_public/member/history.html');
	}
	//個人信息
	public function personal(){
		$this->display('web_public/member/personal.html');
	}
	//遊戲公告
	public function announcement(){
		$this->display('web_public/member/announcement.html');
	}
	//視訊公告
	public function livecement(){
		$this->display('web_public/member/livecement.html');
	}
	//彩票公告
	public function lotterycement(){
		$this->display('web_public/member/lotterycement.html');
	}



}