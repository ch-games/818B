<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends MY_Controller {
    public function __construct() {
		parent::__construct();
	}
    //整体
	public function index(){
		//获取维护状态 是否维护
		$main_data = $this->Maintain_model->maintain_state('sp',SITEID,INDEX_ID);
		if($main_data['is_maintain']){
			$html = '<div class="wh-bg"><p class="wh-p">'.$main_data['content'].'</p></div>';
			$this->add('whhtml', $html);
		}
		
		$this->add('token',$this->input->post_get('token'));
		$this->add('uid',$this->input->post_get('uid'));
		$this->add('vlock',date('Y/m/d H:i:s'));
		$vlockjs='var dd2=new Date("'.date('Y/m/d H:i:s').'"); setInterval("RefTime()",1000);';
		$this->add('vlockjs',$vlockjs);
		if ($this->input->post_get('is_hg') == 1) {
            $this->load->model('Common_model');
            $this->add('web_data',$this->Common_model->get_copyright());
            $this->display('web/main_hg.html');
        }else{
    		$this->display('sports/main.html');
        }
	}
	public function bet(){
		$user = $this->get_token_info_is_login();
		$p=$this->input->post(['status','p','dsorcg','betpage','st','et','number','t_sport']);
		if ((empty($p['st']) || empty($p['et'])) || ($p['st']>$p['et'])){
			$p['st']=date('Y-m-d 00:00:00');
			$p['et']=date('Y-m-d 23:59:59');
		}
		if($p['t_sport']==''){
			$p['st']=date('Y-m-d 00:00:00',strtotime("-7 day"));
			$p['et']=date('Y-m-d 23:59:59');
		}
		$this->load->model('sports/user_model');
		$d=$this->user_model->getbet($p,$user);
		//if($p['bettype'])
		$d['menu']='';
		echo json_encode($d);
	}
	public function matchnum(){
		$this->load->model('sports/match_model');
		$d=$this->match_model->matchnum($p,$user); 
		echo json_encode($d);
	}
	//另开页面的比赛结果
        public function result(){
            $select = '';
            for($i=0;$i<7;$i++){
                $time = strtotime("-$i days");
                $date = date('Y-m-d',$time);
                $select .= "<option value='$i'>$date</option>";
            }
            $this->add('select',$select);
            $this->display('sports/result.html');
	}
	 


}
