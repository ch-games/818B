<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Index extends MY_Controller {
    //定义全局变量，避免同一变量多次查询
    private $web_data;

    public function __construct() {
		parent::__construct();
		$this->load->model('Index_model');
		$this->load->model('Common_model');
		$this->initializationData();
	}

    //预加载参数
    public function initializationData(){
        //浮动数据
        $this->float_left();
        //会员消息数
        $this->notice_count();
        //中间弹窗
        $this->add('pop',$this->Index_model->get_site_pop(1));
        $this->parse('site_pop','web_public/site_pop.html');
        //会员信息
        $this->get_member();
        //获取首页轮播图
        $this->add("flash",$this->Index_model->get_flash());
        //手机投注连接
        $wapBetUrl = '/index.php/index/wapview';
        $this->add('wapBetUrl',$wapBetUrl);
        //时间输出
        $time_md = date('Y') . '/' . date('m') . '/' . date('d') . ' ' . date('H') . ':' . date('i') . ':' . date('s');
        $this->add("timemd", $time_md);
        //站点数据
        $this->web_data = $this->Common_model->get_copyright();
        $this->add('web_data',$this->web_data);
        $this->add('con_data',$this->web_data);
        $this->add('title',$this->web_data['web_name']);
        $this->add('copy_right',$this->web_data['copy_right']);
        //下拉菜单
        $xl = $this->Index_model->get_xl_top();
        $this->add('xl',$xl[0]);
        $this->add('xl2',$xl[1]);
        $this->add('xl3',$xl[2]);
        $this->add('xl4',$xl[3]);
        //前台logo
        $this->logo_html();
        //获取文案导航
        $this->add('meau_foot',$this->Index_model->get_meau_footer());
        //左下角弹窗
        $left_ad = $this->Index_model->get_site_pop(3);
        $this->add("left_ad",$left_ad);//左下角广告
        //加载底部文件
        $this->parse('bottom','web/bottom.html');
    }

	public function index() {
		$ty = $this->input->get('ty');     //获取type值，判断是否是预览
		$_SESSION['ty'] = $ty;
		//判断intr是否正确
		// $_GET = array_change_key_case($_GET, CASE_LOWER);
        // $intr = $this->input->get('intr');
		// $url = URL.'/index.php/index/N_index';

		$urldata = $_SERVER["QUERY_STRING"];
		$urldata = explode('=',strtolower($urldata));
		$urldata = array_change_key_case($urldata, CASE_LOWER);

        switch ($urldata[0]) {
        	case 'u'://会员推广
                $url = URL.'/index.php/index/zhuce';
        		break;
        	case 'intr'://注册页面
                $url = URL.'/index.php/index/zhuce';
        		break;
            case 'agen'://代理注册页面
                $url = URL.'/index.php/index/daili_shenqing';
                break;
        	case 'aff'://首页
                $url = URL.'/index.php/index/N_index';
        		break;
        	case 'live'://视讯页面
                $url = URL.'/index.php/index/livetop';
        		break;
        	case 'spor'://体育页面
                $url = URL.'/index.php/index/sports';
        		break;
        	case 'egam'://电子页面
                $url = URL.'/index.php/index/egame';
        		break;
        	case 'iyou'://优惠页面
                $url = URL.'/index.php/index/youhui';
        		break;
        	case 'lott'://彩票页面
                $url = URL.'/index.php/index/lottery';
        		break;
        	default:
        		$url = URL.'/index.php/index/N_index';
        		break;
        }
        

        if ($urldata[0] != 'u') {
            $intr = $urldata[1];
        }else{
        	$regip = $this->get_ip();
        	$this->Index_model->is_uuno_true($urldata[1],$regip);
        }


        if(!empty($intr)){
            $Astate = $this->Index_model->is_intr($intr);
            if ($Astate) {
				$_SESSION['intr'] = $Astate['intr'];
				setcookie('intr',$Astate['intr'],time()+36000);
                setcookie('agent_id',$Astate['id'],time()+36000);
				//$url = URL.'/index.php/index/zhuce';
			}else{
				//message('您输入的介绍人不存在！');
			}
            $this->add('intr',$intr);
        }
        $site_state = $this->Index_model->get_site_info();
        //p($site_state);die;
        if($site_state['site_state'] == 3){
            $rows = $this->Index_model->qrcode();
            $json = base64_encode(json_encode($rows));
            $url = "/wh.php?ps=".$json;
        }
		$this->add('url',$url);
		$this->display('web/index.html');
	}

	public function N_index(){
		$this->notice(1);
		$this->add('csstype',1);
		$this->parse('header','web/header.html');
		$this->display('web/N_index.html');
	}

	//手机介绍页
    public function wapview(){
        $this->add('sitename',$this->web_data['web_name']);
        $this->add('wapurl',$this->web_data['wap_url']);
        $this->display('wapview/wapview.html');
    }

    //体育
    public function sports(){
        $token=$_SESSION["token"];
        $uid=$_SESSION["uid"];
        $this->add('token',$token);
        $this->add('uid',$uid);
        $this->notice(8);
        $this->add('csstype',8);
        $this->parse('header','web/header.html');
        $this->display('web/sports.html');
    }

    //IM体育
    public function im_sports(){
    	$this->load->library('Games');
    	$this->load->model('Video_model');
		$userinfo = $this->Common_model->get_user_info($_SESSION['uid']);
		if ($userinfo['shiwan'] == '1') {
			message('请用正式账号登陆!');exit;
		}
		if (empty($userinfo) OR empty($userinfo["username"])) {
			$this->notice(8);
			$this->add('csstype',8);
			$this->add('url','http://sports.pk051.com');
			$this->parse('header','web/header.html');
			$this->display('web/im_sports.html');
			exit;
		}
		$video_config = explode(",",$this->web_data['video_module']);
		if(false == in_array("im",$video_config)){
			message('未知的游戏!');exit;
		}
		$g_type = 'im';
		$limitval = $this->Video_model->get_limitval($userinfo, $g_type);
		$loginname = $userinfo["username"];
		$gametype = "1";
		$lang = "CN";
		$cur = "RMB";
		$limitype = "";

		$games = new Games();
		$url = $games->forwardGame($loginname, $g_type, $gametype, $limitval, $limitype, $lang, $cur);
		$pos1 = strpos($url, "result");
		$pos2 = strpos($url, "data");
		if ($pos1 > 0 && $pos2 > 0) {
			$result = json_decode($url);
			if ($result->data->Code == 10006) {
				$data = $games->CreateAccount($loginname, $userinfo["agent_id"], $g_type, $userinfo['index_id'], $cur);
				if (!empty($data)) {
					$result = json_decode($data);
					if ($result->data->Code != 10011) {
						message('网络错误，请联系管理员!');exit;
					} else {
						$url = $games->forwardGame($loginname, $g_type, $gametype, $limitval, $limitype, $lang, $cur);
						$pos1 = strpos($url, "result");
						$pos2 = strpos($url, "data");
						if ($pos1 > 0 && $pos2 > 0) {
							message('网络错误，请联系管理员!');exit;
						}
					}
				}
			} else {
				message('网络错误，请联系管理员!');exit;
			}
		}
      $this->notice(8);
      $this->add('csstype',8);
      $this->add('url',$url);
      $this->parse('header','web/header.html');
      $this->display('web/im_sports.html');
    }

    //彩票
	public function lottery(){
		$lottery_type = $this->input->get('metype');
		if(empty($lottery_type) || $lottery_type == 'undefined'){
			$lottery_type = 'liuhecai';
		}
	    $this->notice(2);
		$this->add('csstype',2);
		$this->parse('header','web/header.html');
		$this->add('lottery_type',$lottery_type);
	    $this->display('web/lottery.html');
	}

	//视讯
	public function livetop(){
		$this->notice(3);
		$video_config = $this->Index_model->get_livetop();

		//在电子视讯页面去掉dg99 对应的图片
		foreach ($video_config as $i=>$v){
			if($v == 'dg99')
				unset($video_config[$i]);
		}

		$video_imgs = $this->Index_model->get_video_imgs();
		$style = $this->Index_model->get_livetop_style();
		$type1 = $this->input->get('type');
    	$style1 = $this->input->get('style');
    	if($type1&&$style1){
    		$style['style'] = $style1;
    		$style['type'] = $type1;
			$style['state'] = 1;
    	}
		 //初始图片路径替换
        foreach ($video_config as $key => $val) {
        	 if (empty($video_imgs[$val])) {
        	    $video_imgs[$val]['img_url'] = '/public/images/img_0'.$val.'.png';
        	}
        	if ($val == 'pt' || $val == 'eg'|| $val == 'im' || $val == 'gg' || $val == 'hb') {
        	    unset($video_config[$key]);
        	}
        }
        
        //判断视讯是否维护
        $main_data = $this->Maintain_model->maintain_state('vd',SITEID,INDEX_ID);

        foreach ($video_config as $k => $v){
        	if(in_array($v, $main_data['module'])){
        		$array[$k]['name'] = $v;
        		$array[$k]['is_maintain'] = $main_data['is_maintain'];
        	}else{
        		$array[$k]['name'] = $v;
        		$array[$k]['is_maintain'] = 0;
        	}
        }
        $this->add('csstype',3);
		$this->parse('header','web/header.html');
		$this->add('video_config',$video_config);
        $this->add('video_maintain',$array);
		$this->add('style',$style);
		$this->add('video_imgs',$video_imgs);

		if($style){
			$live = $this->select_style($style);

			$this->parse('livetope_html','web_public/'.$live.'.html');
		}
		 $this->display('web/livetop.html');
	}

	/**
	 * 电子游戏
	 */
	public function egame() {
		$this->load->model('webcenter/Egame_model');

		$type = $this->input->get('type');
		$topid = $this->input->get('top');
		$jgame = $this->input->get('metype');//电子下拉类型
        
        $conf['site_id'] = SITEID;
		$conf['index_id'] = INDEX_ID;
        $game_module = $this->Egame_model->get_gameconf($conf);
        if(empty($type)){
        	$type = $game_module[0];
        }
		//获取游戏数据 redis
      	$redis = new Redis();
      	$redis->connect(REDIS_HOST,REDIS_PORT);

      	//游戏数据
        $redis_key_data = SITEID . '_' . INDEX_ID . '_' . strtolower($type) . '_egame_data';
      	if($redis->exists($redis_key_data)){
        	$data = json_decode($redis->get($redis_key_data),TRUE);
      	}else{
        	//获取所有电子记录
			$data = $this->Egame_model->get_games_data($type);
        	//存入redis
        	if($data) $redis->set($redis_key_data , json_encode($data));
      	}

      	if($topid && $topid!=='undefined'){
      		$top_data = array();
      		foreach ($data as $k => $v) {
      			if($v['topid'] == $topid){
      				$top_data[] = $v;
      			}
      		}
      		$data = $top_data;
      	}

		//维护读取
		// $m_data = $this->Maintain_model->maintain_check('dz',SITEID,INDEX_ID);
		// $game_module_m = array();
		// if ($m_data) {
		//     foreach ($game_module as $key => $val) {
		//         if (in_array(strtolower($val), $m_data)) {
		//             $game_module_m[$key] = 1;
		//         }else{
		//         	$game_module_m[$key] = 0;
		//         }
		//     }
		// }
        $m_data = ['gg','gd'];
        if (in_array(strtolower($type), $m_data)) {
            $Edata['wh'] = 1; //维护状态 '' 不维护 1 维护
            $Edata['data'] = 0;
        }else{
            $Edata['wh'] = '';
            $Edata['data'] = $data;
        }
        //ajax请求输出
		if($this->input->is_ajax_request()){
			$Edata['gty']['top_id'] = $topid;
			$Edata['gty']['typeOf'] = $type;
			echo $this->Common_model->JSON($Edata);
			exit;
		}
		$this->notice(7);
		$this->add('csstype',7);
		$this->parse('header','web/header.html');
		$this->add('gametype',$type);
		$this->add('game_module', $game_module);
		if (!$jgame || $jgame == 'm') {
		    $jgame = $game_module[0];
		}

		//获取电子内页主题颜色
		$color_html = $this->Egame_model->get_game_color();
        $this->add("color_html",$color_html);
		
		$this->add("jgame",$jgame);
		$dz_i = array_search($jgame,$game_module);
		$this->add("dz_i",$dz_i);
		$this->parse('egame_html','web_public/egame_data.html');
		$this->display('web/egame.html');
	}


	//文案信息
	public function iword(){
	    if(empty($_SESSION['ty']) || $_SESSION['ty'] > 8 || $_SESSION['ty'] < 3){
	    	$type = $this->input->get('metype');
	    	$type = empty($type)?1:$type;
	    	$map['table'] = 'info_iword_use';
	  	}elseif($_SESSION['ty'] >= 3 && $_SESSION['ty'] <= 8){      //预览文案
	  		$map['table'] = 'info_iword_edit';
	  		$map['where']['case_state'] = 1;
	  		$type = $_SESSION['ty'];
	  	}
	    $map['where']['type'] = $type;
	    $map['where']['index_id'] = INDEX_ID;
	    $map['where']['site_id'] = SITEID;

	    $this->notice(10);
	    $this->add('csstype',10);
		$this->parse('header','web/header.html');

	    $this->add("agent_url",$this->web_data['agent_url']);
	    $this->add('iword',$this->Index_model->rfind($map));
		$this->display('web/iword.html');
	}

	//优惠活动
	public function youhui(){
		$this->get_member();
    	$data = $this->Index_model->get_promotions();
    	$this->notice(4);
    	$this->add('csstype',4);
		$this->parse('header','web/header.html');
		foreach($data['data'] as $key=>$value){
			$value['content']= $this->Index_model->replacedomain($value['content']);
			$value['img']= $this->Index_model->replacedomain($value['img']);
			$point = strpos($value['img'],'.');
			$point2 = strpos($value['content'],'.');
			if($point === 0){
				$img = substr($value['img'],1);
				$data['data'][$key]['img'] = $img;
			}else {
				$data['data'][$key]['img'] = $value['img'];
				// $data['data'][$key]['content'] = $value['content'];
			}
			if($point2 === 13){
				$content = substr_replace($value['content'],"",$point2,1);
				$data['data'][$key]['content'] = $content;
			}else {
				// $data['data'][$key]['img'] = $value['img'];
				$data['data'][$key]['content'] = $value['content'];
			}

		}
		$max_width = $this->Index_model->get_promotions_width();
		
		$this->add('max_width',$max_width);
		$this->add('promotion',$data);
		$this->parse('promotion_html','web_public/promotions.html');
		$this->add('old_url',OLD_URL);
	    $this->display('web/youhui.html');
	}

	//试玩注册
	public function shiwan(){
		$this->add('old_url',OLD_URL);
		$this->get_member();
		$this->notice();
		$this->parse('header','web/header.html');
	    $this->display('web/zhuce_shiwan.html');
	}

	//会员注册
	public function zhuce(){
		//获取后台用户注册设定
		$map = array();
		$map['table'] = 'k_user_reg_config';
		$map['where']['site_id'] = SITEID;
		$map['where']['index_id'] = INDEX_ID;
		$result = array();
		$result = $this->Index_model->rfind($map);
		if ($result['is_work'] == 0 || empty($result)) {
			echo "<script>alert('系统禁用了用户注册功能,请联系管理员!');window.location.href='/';</script>";exit;
		}
		$this->notice();
		$this->parse('header','web/header.html');
	    $this->display('web/zhuce.html');
	}

	//注册试玩
	public function shiwan_reg(){
		$this->notice();
		$this->parse('header','web/header.html');
	    $this->display('web/zhuce_shiwan.html');
	}


	//代理注册
	public function daili_shenqing(){
		$map = array();
		$map['table'] = 'k_user_agent_config';
		$map['select'] = 'is_daili';
		$map['where']['index_id'] = INDEX_ID;
		$map['where']['site_id'] = SITEID;
		$config = $this->Index_model->rfind($map);
		//print_r($config);die;
		if (empty($config) || $config['is_daili'] == 0) {
			message('系统关闭了代理注册功能', '/');
		}
		$this->get_member();
		$this->notice();
		$this->add('config',$config);
		$this->parse('header','web/header.html');
	    $this->display('web/zhuce_daili.html');
	}

	//用户信息
	public function get_member(){
        if (!empty($_SESSION['uid'])) {
        	$this->Index_model->login_check($_SESSION['uid']);
        	$map = array();
	        $map['table'] = 'k_user';
	        $map['select'] = 'username,money,ag_money,og_money,mg_money,ct_money,lebo_money,bbin_money,pt_money';
            $map['where']['uid'] = $_SESSION['uid'];
            $map['where']['site_id'] = SITEID;
            $map['where']['index_id'] = INDEX_ID;

            $data = $this->Index_model->rfind($map);
		    $this->add('uid',$_SESSION['uid']);
		    $this->add('money',$data['money']);
		    $this->add('ogmoney',$data['og_money']);
		    $this->add('ctmoney',$data['ct_money']);
		    $this->add('mgmoney',$data['mg_money']);
		    $this->add('agmoney',$data['ag_money']);
		    $this->add('lebomoney',$data['lebo_money']);
		    $this->add('bbinmoney',$data['bbin_money']);
		    $this->add('ptmoney',$data['pt_money']);
		    $this->add('username',$_SESSION['username']);
		    $this->add('user',$data);
        }
	}


	//logo
    public function logo_html() {
    	$lgObj = $this->Index_model->get_logo();
    	$logostr = strrchr($lgObj['logo_url'],'.');
    	if($logostr == '.swf'){
    		$msg = 1;
    	}
    	$this->add("msg", $msg);//判断格式
		$this->add("lgObj", $lgObj);//获取logo
        $this->add("logo", $lgObj);//兼容老版
		$this->parse('logo_html','web_public/logoimg.html');
    }

	//左右浮动
	public function float_left(){
		$this->load->model('webcenter/Float_model');
        $data = $this->Float_model->get_allfloat();
        $floatl = $data['floatl'];  //左浮动数据
        $floatr = $data['floatr'];  //右浮动数据
        $fleft = !empty($floatl) ? 1 : 0;
        $fright = !empty($floatr) ? 1 : 0;

		if($fleft == 1){
			foreach($floatl as $key=>$value){
				$point = strpos($value['img_A'],'.');
				$point2 = strpos($value['img_B'],'.');
				if($point === 0){
				   $img_A = substr($value['img_A'],1);
				   $floatl[$key]['img_A'] = $img_A;
				}else $floatl[$key]['img_A'] = $this->Float_model->replacedomain($value['img_A']);
				if($point2 === 0){
				   $img_B = substr($value['img_B'],1);
				   $floatl[$key]['img_B'] = $img_B;
				}else   $floatl[$key]['img_B'] = $this->Float_model->replacedomain($value['img_B']);
			}
		}
		if($fright == 1){
			foreach($floatr as $k=>$v){
				$rpoint = strpos($v['img_A'],'.');
				$rpoint2 = strpos($v['img_B'],'.');
				if($rpoint === 0){
					$img_A2 = substr($v['img_A'],1);
					$floatr[$k]['img_A'] = $img_A2;
				}else $floatr[$k]['img_A'] = $this->Float_model->replacedomain($v['img_A']);
				if($rpoint2 === 0){
					$img_B2 = substr($v['img_B'],1);
					$floatr[$k]['img_B'] = $img_B2;
				}else $floatr[$k]['img_B'] = $this->Float_model->replacedomain($v['img_B']);
			}
		}
        $this->add('fleft',$fleft);
		$this->add('fright',$fright);
        $this->add('floatl',$floatl);
		$this->add('floatr',$floatr);
    }

    //跑马灯公告
    public function notice($type=0) {
    	$this->load->model('webcenter/Notice_model');
        $data = $this->Notice_model->getNotice($type);
    	$this->add('notice',$data['left']);
    	$this->add('notice2',$data['up']);
		$this->parse('notice_html','web/notice.html');
    }

    //点击弹出历史消息
    public function notice_data() {
    	$this->load->model('webcenter/Notice_model');
    	$list = $this->Notice_model->get_notice_data();
    	$this->add('list',$list);
    	$this->display('web/notice_data.html');
    }

    //获取会员未读消息数
  //   public function notice_count(){
  //   	$this->get_member();
	 // 	$this->load->model('webcenter/Notice_model');
  //   	//$count = $this->Notice_model->get_notice_count();
  //   	$count = $this->Notice_model->new_notice_count();   //redis获取
  //   	$this->add('count',$count);

 	// }

 	//获取新版会员未读消息数
    public function notice_count(){
    	$this->get_member();
	 	$this->load->model('webcenter/Notice_model');
    	// $count = $this->Notice_model->new_notice_count();   //redis获取
    	$map = array();
    	$map['where'] = '';
    	$map['where'] .= "(uid='".$_SESSION["uid"]."' or (uid = '' and (level_id = '".$_SESSION['level_id']."' or level_id = '-1')))";
		$map['where'] .= "and is_delete = '0'";
		//站点判断条件
		$map['where'] .= "and site_id = '".SITEID."'";		
		$map['where'] .= "and index_id = '".INDEX_ID."'";
		//时间条件
		$start_date = date('Y-m-d H:m:s', strtotime('-7days'));
		if($row[0]['reg_date'] >= date('Y-m-d', strtotime('-7days'))){
			$start_date = $row[0]['reg_date'];
		}
		$map['where'] .= "and msg_time > '".$start_date."'";
		$id = $this->Notice_model->get_sms_id($map);
		$look_log = $this->Notice_model->get_look_log(array('uid'=>$_SESSION['uid']));
		foreach ($id as $key => $value) {
			foreach ($look_log as $k => $v) {
				if($value['msg_id'] == $v['msg_id']){
					unset($id[$key]);
				}
			}
		}
		$count = count($id);

    	$this->add('count',$count);

 	}


    //备用网址
    public function detect(){
    	$this->notice();
		$this->parse('header','web/header.html');
    	$this->display('web/detect.html');
    }

    //备用网址内页
    public function about(){
    	$this->notice();
    	$this->parse('header','web/header.html');
    	$this->db->from('info_detect_use');
    	$this->db->select('content');
    	$this->db->where('site_id',SITEID);
    	$this->db->where('index_id',INDEX_ID);
    	$this->db->where('is_status',1);
    	$data = $this->db->get()->result_array();
//		var_dump($data);die();
    	$this->add('data',json_encode($data));
    	$this->display('web/about.html');
    }

    //视讯模板风格
    public function select_style($tyle){
    	if($tyle['type'] == 1){//PK系列风格
    		if($tyle['style'] == 1){
    			$html = "live1";
    		}
            if($tyle['style'] == 2){
                $html = "live2";
            }
            if($tyle['style'] == 3){
                $html = "live3";
            }
            if($tyle['style'] == 4){
                $html = "live4";
            }
            if($tyle['style'] == 5){
                $html = "live5";
            }
            if($tyle['style'] == 6){
                $html = "live6";
            }
    	}else if($tyle['type'] == 4){//其他风格
    		if($tyle['style'] == 1){
    			$html = "other";
    		}
    	}
    	return $html;
    }

    //幸运大转盘
    public function good_lucky(){
    	$this->display('web/good_lucky.html');
    }

    //新版会员中心框架
    public function new_member_main(){
    	$in_type = $this->input->get('url');
		$this->notice(8);
		$this->add('in_type',$in_type);
		$this->parse('header','web/header.html');
		$this->display('web/new_member_main.html');
    }

    //新版会员中心
    public function new_member(){
        $data = $this->web_data;
        if(empty($data['membercolor'])){
            $style = 1;
        }else{
            $style = $data['membercolor'];
        }
		$this->add('in_type',$in_type);
        $this->add('style',$style);
		$this->parse('mem_header','web_public/member/mem_head.html');
		$this->parse('mem_foot','web_public/member/mem_foot.html');
		$this->display('web_public/member/account/new_member.html');
    }

	//返回首页跳动随机数
	public function GetJackpotsForGamePlatForm(){
		header('content-type:application/json;charset=utf-8');
		$rand = rand(359766200.00,359766299.00);
		$success = true;
		$arr = array(
			'success' => $success,
			'msg'     => (string)$rand,
		);
//		var_dump($arr);die();
		echo json_encode($arr);
	}

}
