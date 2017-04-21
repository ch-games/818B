<?php if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Login_model extends MY_Model {

	function __construct() {
		parent::__construct();
	}
   
	//会员登录日志
	public function user_login($uid,$zcname,$ip,$type){
		$dataf  = array();
		$dataf['ssid']   = session_id();
		$dataf['login_time'] = date("Y-m-d H:i:s");
		$dataf['is_login']   = 1;
		$dataf['www']        = $_SERVER['HTTP_HOST'];
		$dataf['ip']  = $ip;

		if ($type == 1) {
			$dataf['uid']        = $uid;
			$dataf['site_id']    = SITEID;
		    $dataf['index_id']   = INDEX_ID;
		    $this->radd('k_user_login',$dataf);
		}else{
            //更新会员登录
			$mapUL = array();
			$mapUL['table'] = 'k_user_login';
			$mapUL['where']['uid'] = $uid;
			$this->rupdate($mapUL,$dataf);
		}
	}

	//会员登陆
	public function member_login($uname,$pwd,$ip,$address){
        $mapL = array();
        $mapL['table'] = 'k_user';
        $mapL['select'] = 'username,uid,password,agent_id,ua_id,sh_id,level_id,is_delete,shiwan';
		$mapL['where']['site_id'] = SITEID;
		$mapL['where']['username'] = $uname;
		//$mapL['where']['shiwan'] = 0;
		$mapL['where']['index_id'] = INDEX_ID;

		$loginS = $this->rfind($mapL);

		if (empty($loginS)) {
			return 3;//账号不存在
		}
		if ($loginS['password'] != md5(md5($pwd))) {
			$this->user_history_login($loginS['uid'],$uname,$ip,$address,0);
		    return 4;//密码不对
		}
        //暂停 停止
		if ($loginS['is_delete'] == 1 || $loginS['is_delete'] == 2) {
			return 2;
		}
        //判断是否已经登陆
		$this->isLoginNow($loginS['uid'],$loginS['username'],$ip);

		//更新用户登录
		$this->db->where(array('uid'=>$loginS['uid']));
		$this->db->set('lognum','lognum + 1',FALSE);
		$this->db->set('login_ip',$ip);
		$this->db->set('login_time',date('Y-m-d H:i:s'));
		$log_2 = $this->db->update('k_user');

		//历史记录
	    $this->user_history_login($loginS['uid'],$uname,$ip,$address,1);
        $this->redis_update_user();
        $this->load->model('sports/User_model','User_model');
        $_SESSION["token"]=$this->User_model->redis_update_token([$loginS['uid'],$loginS['username'],$loginS['agent_id'],$loginS['level_id'],$loginS['shiwan'],SITEID,INDEX_ID,$loginS['ua_id'],$loginS['sh_id']]);

		$_SESSION['uid'] = $loginS['uid'];
        $_SESSION['agent_id'] = $loginS['agent_id'];
        $_SESSION['ua_id'] = $loginS['ua_id'];
        $_SESSION['sh_id'] = $loginS['sh_id'];
        $_SESSION['username'] = $loginS['username'];
        $_SESSION['level_id'] = $loginS['level_id'];;
        $_SESSION['shiwan']   = $loginS['shiwan'];
        $_SESSION['ssid'] = session_id();  

        return 1;
	}

	//会员历史登录
	public function user_history_login($uid,$uname,$ip,$address,$state){
		$dataO = array();
		$dataO['uid'] = $uid;
		$dataO['username'] = $uname;
		$dataO['ip'] = $ip;
		$dataO['state'] = $state;
		$dataO['ip_address'] = $address.':'.$this->get_browser();
		$dataO['login_time'] = date('Y-m-d H:i:s');
		$dataO['site_id'] = SITEID;
		$dataO['index_id'] = INDEX_ID;
		$dataO['www'] = $_SERVER['HTTP_HOST'];
		$this->radd('history_login',$dataO);
	}

	    //判断会员是否已经登录
    public function isLoginNow($iuid,$username,$ip){
    	    $map = array();
    	    $map['table'] = 'k_user_login';
    	    $map['select'] = 'uid,ssid,is_login';
    	    $map['where']['uid'] = $iuid;
    	    $nowLogin = $this->rfind($map);

			if ($nowLogin) {
				//登录过//已经在线
				if (isset($nowLogin['is_login']) && $nowLogin['is_login']) {
					session_start();
				}
				//更新会员登录
				$this->user_login($iuid,$username,$ip,0);
			}else{
					//添加会员登录
				$this->user_login($iuid,$username,$ip,1);
			}

    }
	

	 //读取视讯配置
    public function get_video_config($site_id){
		$map = array();
		$map['table'] = "web_config";
		$map['where']['site_id'] = $site_id;
		$map['select'] = 'video_module';
		$video_config = $this->rfind($map);
		$video_config = explode(',',$video_config['video_module']);
		return $video_config;
    }

     

    //代理推广域名匹配
    public function agent_domain_intr(){
        //匹配代理推广域名
		$mapdo = array();
		$mapdo['table'] = 'k_user_agent_domain';
		$mapdo['where']['domain'] = $_SERVER['HTTP_HOST'];
		$mapdo['where']['site_id'] = SITEID;
		$mapdo['where']['state'] = 1;
		$domain_data = $this->rfind($mapdo);
		if ($domain_data) {
		    $_SESSION['intr'] = $domain_data['intr'];
		    $_SESSION['domain_intr_id'] = $domain_data['id'];//标注是匹配代理域名推广
		}
    }

    	//获取浏览器类型
	public function get_browser(){
        $agent=$_SERVER["HTTP_USER_AGENT"];
        if(strpos($agent,'MSIE')!==false || strpos($agent,'rv:11.0')) //ie11判断
        return "ie";
        else if(strpos($agent,'Firefox')!==false)
        return "firefox";
        else if(strpos($agent,'Chrome')!==false)
        return "chrome";
        else if(strpos($agent,'Opera')!==false)
        return 'opera';
        else if((strpos($agent,'Chrome')==false)&&strpos($agent,'Safari')!==false)
        return 'safari';
        else
        return 'unknown';
    }


    //查询公告配置，匹配用户
    public function get_member_message(){
        $this->db->from('k_message');
        $this->db->where('site_id',SITEID);
        $this->db->where('index_id',INDEX_ID);
        $this->db->where('is_delete',0);
        $this->db->where('show_type',1);
        $this->db->order_by('add_time',"DESC");
        return $this->db->get()->result_array();
    }

    //获取会员层级
    public function get_member_level(){
        $this->db->from('k_user');
        $this->db->where('username',$_SESSION['username']);
        $this->db->where('site_id',SITEID);
        $this->db->select('level_id');
        return $this->db->get()->row_array();
    }

    //更新会员信息
    public function update_member_views($data_u){
        $this->db->where('site_id',$siteid);
        $this->db->where('username',$username);
        $this->db->set($data_u);
        return $this->db->update('k_user');
    }
}