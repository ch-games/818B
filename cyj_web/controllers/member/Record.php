<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Record extends MY_Controller {
	public function __construct() {
		parent::__construct();
		$this->load->model('member/Member_record_model');
		$this->Member_record_model->login_check($_SESSION['uid']);
	}

	//會員專區，交易記錄，體育視圖
	public function tc_record(){
		$this->load->model('Common_model');
		$copyright = $this->Common_model->get_copyright();
		$video_config = explode(',',$copyright['video_module']);
		foreach ($video_config as $key => $value) {
			$video_config[$key] = strtoupper($value);
		}
		$s_date = $e_date = date("Y-m-d",time());
		$this->add("s_date", $s_date);
		$this->add("e_date", $e_date);
		$this->add('video_config',$video_config);
		$this->display('member/tc_record.html');
	}

	//會員專區，交易記錄，體育
	public function tc_record_do(){
		$this->load->model('Common_model');
		$copyright = $this->Common_model->get_copyright();
		$video_config = explode(',',$copyright['video_module']);
		foreach ($video_config as $key => $value) {
			$video_config[$key] = strtoupper($value);
		}
		$uid=$_SESSION['uid'];
		$start_date = $this->input->get('start_date');
		$end_date = $this->input->get('end_date');
		$order = $this->input->get('order');
		$gtype = $this->input->get('gtype');
		$gtype = empty($gtype)?1:$gtype;

		if($gtype == 1){
			$sql_union = "k_bet";
		}else if($gtype == 2){
			$this->db->from('k_bet_cg');
			$this->db->where('site_id',SITEID);
			$this->db->where('uid',$uid);
			$deposit = $this->db->get()->result_array();
			$this->db->from('k_bet_cg');
			$this->db->where('site_id',SITEID);
			$this->db->where('uid',$uid);
			$this->db->group_by('gid');
			$deposit2 = $this->db->get()->result_array();

			$sql_union=array();
			foreach($deposit2 as $key=>$value){
				$sql_union[] = "(select * from k_bet_cg_group where gid = '".$value['gid']."') as bet";
			}
			//var_dump($sql_union);die;
		}



		$map['where']="uid='".$uid."' and site_id='".SITEID."'";

		//時間判斷
		if (!empty($start_date)) {
			$s_date = $start_date;
			$this->add("s_date", $s_date);
		}else{
			$s_date  = date("Y-m-d",time());
			$this->add("s_date", $s_date);
		}

		if (!empty($end_date)) {
			$e_date = $end_date;
			$this->add("e_date", $e_date);
		}else{
			$e_date = date("Y-m-d",time());
			$this->add("e_date", $e_date);
		}
		//訂單號查詢
		if(empty($order)){
			$map['where'] .= " and bet_time > '".$s_date." 00:00:00' and bet_time < '".$e_date." 23:59:59'";

			$map['order'] =" order by bet_time desc";
		}else{
			if(preg_match("/^[\W]*$/i",$order)){
				echo '<script>alert("您輸入的訂單號非法")</script>';
			}else{
				$map['where'] .= " and number = '".$order."'";
				$this->add("order", $order);
			}
		}

		if($gtype == 1){
			$count = $this->Member_record_model->get_record_b_count($sql_union,$map);
		}else if($gtype == 2){
			foreach ($sql_union as $key => $value) {
				$count = $this->Member_record_model->get_record_b_count($value,$map);
			}
		}
		//分頁
		$perNumber=isset($_GET['page_num'])?$_GET['page_num']:10; //每頁顯示的記錄數
		$totalPage=ceil($count/$perNumber); //計算出總頁數
		$page=isset($_GET['page'])?$_GET['page']:1;
		if($totalPage<$page){
			$page = 1;
		}
		$startCount=($page-1)*$perNumber; //分頁開始,根據此方法計算出開始的記錄
		$map['limit']=$startCount;
		$map['limit2'] =$perNumber;

		$getpage = $this->input->get('page');
		if($getpage){
			$map['limit']=($getpage-1)*10;
			$this->add("dqpage", $getpage);
		}else{
			$this->add("dqpage", 1);
		}

		$data = array();
		if($gtype == 1){
			$data = $this->Member_record_model->get_record_b($sql_union,$map);
		}else if($gtype == 2){
			foreach($sql_union as $key =>$value){
				//var_dump($value);die;
				$data[] = $this->Member_record_model->get_record_b($value,$map);
			}
		}

		$array = array();
		foreach ($data as $k=>$val){
			if($gtype == 2 && !empty($val)){
				foreach ($val as $key => $value) {
					//$gtype == 2 $val['ball_sort'] == '串關'
					$map_c['where'] = "gid in (".$value['gid'].")";
					$map_c['order'] = "bid desc";
					$data_cg = $this->Member_record_model->get_record_cg($map_c);
				}
				$value['chuanlian'] = $data_cg;
				$value['ball_sort'] = '串關';
				$array[] = $value;
			}
			else if(!empty($val)){
				$array[] = $val;
			}else{
				$array = array();
			}
		}


		$this->add("gtype", $gtype);
		$this->add("totalPage",$totalPage);
		$this->add("data", $array);
		$this->add('video_config',$video_config);
		$this->display('member/tc_record.html');
	}

	//會員專區，交易記錄，彩票視圖
	public function lottery_today(){
		$this->load->model('Common_model');
		$copyright = $this->Common_model->get_copyright();
		$video_config = explode(',',$copyright['video_module']);
		foreach ($video_config as $key => $value) {
			$video_config[$key] = strtoupper($value);
		}

		$fc_games = $this->Member_record_model->get_fc_games();
		foreach ($fc_games as $key => $value) {
			$fc_types[$value['type']] = $value;
		}
		$this->add('fc_games',$fc_types);

		$s_date = date('Y-m-d');
		$e_date = date('Y-m-d');
		$this->add("s_date", $s_date);
		$this->add("e_date", $e_date);
		$this->add('video_config',$video_config);
		$this->display('member/lottery_today.html');
	}

	//會員專區，交易記錄，彩票
	public function lottery_today_do(){
		$this->load->model('Common_model');
		$copyright = $this->Common_model->get_copyright();
		$video_config = explode(',',$copyright['video_module']);
		foreach ($video_config as $key => $value) {
			$video_config[$key] = strtoupper($value);
		}
		$uid=$_SESSION['uid'];
		$arrry['uid'] = $uid;

		$start_date = $this->input->get('start_date');
		$s_date = empty($start_date)?date('Y-m-d'):$start_date;
		$end_date = $this->input->get('end_date');
		$e_date = empty($end_date)?date('Y-m-d'):$end_date;

		$order = $this->input->get('order');
		$gtype = $this->input->get('gtype');


		//獲取彩票類型
		$fc_games = $this->Member_record_model->get_fc_games();
		foreach ($fc_games as $key => $value) {
			$fc_types[$value['type']] = $value;
		}

		//彩票種類判斷
		if (!empty($gtype)) {
			//$arrry['type'] = $fctype[$gtype];
			$arrry['fc_type'] = $fc_types[$gtype]['type'];
		}

		//時間
		$arrry['addtime >'] = $s_date." 00:00:00";
		$arrry['addtime <'] = $e_date." 23:59:59";

		//訂單號查詢
		if(!empty($order)){
			if(preg_match("/^[\W]*$/i",$_GET['order'])){
				echo '<script>alert("您輸入的訂單號非法")</script>';
				exit();
			}else{
				$arrry['did'] = $order;
			}
		}

		//總數緩存redis 5秒
		$redis = RedisConPool::getInstace();
		$redis_key = SITEID.'_count_'.$_SESSION['uid'].'_'.md5(md5(json_encode($arrry)));
		if ($redis->exists($redis_key)) {
			$count = $redis->get($redis_key);
		}else{
			$count = $this->Member_record_model->get_record_cp_count($arrry);
			$redis->setex($redis_key,'5',$count);
		}

		//$count = $this->Member_record_model->get_record_cp_count($arrry);
		//分頁
		$perNumber=isset($_GET['page_num'])?$_GET['page_num']:10; //每頁顯示的記錄數
		$totalPage=ceil($count/$perNumber); //計算出總頁數
		$page=isset($_GET['page'])?$_GET['page']:1;
		if($totalPage<$page){
			$page = 1;
		}
		$startCount=($page-1)*$perNumber; //分頁開始,根據此方法計算出開始的記錄
		$map['limit']=$startCount;
		$map['limit2'] =$perNumber;

		$getpage = $this->input->get('page');
		if($getpage){
			$map['limit']=($getpage-1)*$perNumber;
		}else{
		}
		$map['order'] = 'addtime desc';

		//$data = $this->Member_record_model->get_record_cp($arrry,$map);

		$redis_keyf = SITEID.'_fcrecord_'.$_SESSION['uid'].'_'.md5(md5(json_encode($arrry).json_encode($map)));
		if ($redis->exists($redis_keyf)) {
			$data = $redis->get($redis_keyf);
			$data = json_decode($data,true);
		}else{
			$data = $this->Member_record_model->get_record_cp($arrry,$map);
			$redis->setex($redis_keyf,'5',json_encode($data));
		}

		$this->add("totalPage",$totalPage);
		$this->add("s_date", $s_date);
		$this->add("e_date", $e_date);
		$this->add("data", $data);
		$this->add('fc_games',$fc_types);
		$this->add('video_config',$video_config);
		$this->display('member/lottery_today.html');
	}

	//會員專區、交易記錄、視訊
	public function sx_today(){
		$this->load->model('Common_model');
		$copyright = $this->Common_model->get_copyright();
		$video_config = explode(',',$copyright['video_module']);
		foreach ($video_config as $key => $value) {
			$video_config[$key] = strtoupper($value);
		}
		$array['Company'] = $this->input->get('Company');
		$array['VideoType'] = $this->input->get('VideoType');
		if ($array['Company'] == 'AG') {
			$array['VideoType'] = '';
		}
		$array['gametype'] = $this->input->get('gametype');
		$this->add('Company',$array['Company']);
		$this->add('VideoType',$array['VideoType']);
		$this->add('gametype',$array['gametype']);
		$this->add('video_config',$video_config);
		if ($array['Company'] == 'IM') {
			$this->display('member/im_today.html');
		}else{
			$this->display('member/sx_today.html');
		}

	}
	public function sx_today_do(){
		if($_SESSION['shiwan'] == 1){
			$info = array();
			$info['error'] = '請申請正式賬號！祝您遊戲愉快';
			echo json_encode($info);exit;
		}
		$this->load->library('Games');
		$array['username'] = $_SESSION['username'];
		$array['Company'] = strtolower($this->input->get('g_type'));
		$array['VideoType'] = $this->input->get('VideoType');
		$array['gametype'] = $this->input->get('gametype');
		$array['start_date'] = $this->input->get('S_Time');
		$array['end_date'] = $this->input->get('E_Time');
		$array['OrderId'] = $this->input->get('OrderId');
		$array['page'] = $this->input->get('Page');
		$array['page_num'] = $this->input->get('Page_Num')?$this->input->get('Page_Num'):20;
		$array['agentid'] = $this->input->get('agentid');

		//判斷mg電子
		if($array['Company'] == 'mgc'){
			$array['Company'] = 'mg';
		}
		//時間判斷
		if (empty($array['start_date'])) {
			$array['start_date']  = date("Y-m-d");
		}
		if (empty($array['end_date'])) {
			$array['end_date'] = date("Y-m-d");
		}
		//訂單號查詢
		if(!empty($order)){
			if(preg_match("/^[\W]*$/i",$array['OrderId'])){
				echo '<script>alert("您輸入的訂單號非法")</script>';
			}
		}
		if(empty($array['page'])){
			$array['page'] = 1;
		}

		if(!empty($array['VideoType']))$this->add("ty_name", "VideoType");
		if(!empty($array['gametype']))$this->add("ty_name", "gametype");

		$games = new Games();
		$data = $games->GetBetRecord($array['Company'], $array['username'], $array['OrderId'], $array['VideoType'],$array['gametype'], $array['start_date']." 00:00:00", $array['end_date']." 23:59:59",$array['agentid'], $array['page'], $array['page_num']);

		$data = json_decode($data,true);
		//獲取視訊遊戲類型的名字
		$video = $this->Member_record_model->get_all_one($array['Company']);
		$result1 = $data['data'];
		$result = $data['data']['data'];
		if ($array['Company'] == 'im') {
			// $data['data']['type'] = 'im';
			// $data['data']['data'] = $result1;
			// $data['data']['Code'] = 10021;
			/*foreach ($result1 as $key => $value) {
                # code...
            }*/
			$data = $this->im_today_do($array);

		}
		if (!empty($result) && !empty($video) && $array['Company'] != 'bbin'){
			foreach($result as $key=>$value){
				foreach ($video as $k => $v) {
					if($v['type'] == $value['BetType']){
						$data['data']['data'][$key]['BetType'] = $v['name'];
					}
				}
			}
		}elseif (!empty($result) && !empty($video) && $array['Company'] == 'bbin') {
			foreach($result as $key=>$value){
				if($value['BetType'] == '3'){
					$data['data']['data'][$key]['BetType'] = '視訊';
				}elseif($value['BetType'] == '5'){
					$data['data']['data'][$key]['BetType'] = '電子';
				}
			}
		}
		echo json_encode($data);
	}


	public function im_today_do($array) {
		$ball_sort = $this->input->get('ball_sort');

		$this->video_db->from('im_bet_record');
		$this->video_db->where('site_id',SITEID);
		$this->video_db->where('pkusername',$array['username']);
		$this->video_db->where('bet_time >=',$array['start_date']." 00:00:00");
		$this->video_db->where('bet_time <=',$array['end_date']." 23:59:59");
		if ($ball_sort == 2) {
			$this->video_db->where('bet_type','PARLAYALL');
		}elseif($ball_sort == 1){
			//$this->video_db->where('bet_type <>','PARLAYALL');
			$this->video_db->where('bt_status','0');
		}else{
			$this->video_db->where('bt_status',1);
		}
		if (!empty($array['OrderId'])) {
			$this->video_db->where('bet_id',$array['OrderId']);
		}
		$deposit = $this->video_db->get()->result_array();
		$data = array();
		foreach ($deposit as $key => $val) {
			$data['count']['bet_amtAll'] += $val['bet_amt'] + 0;
			if ($val['bt_status'] == 0) {
				$data['count']['payoffAll'] += $val['payoff'] + 0;
				$data['count']['resultAll'] += $val['result'] + 0;
			}else{
				$data['count']['payoffAll'] += $val['bt_buyback'] + 0;
				$data['count']['resultAll'] += $val['bet_amt']-$val['bt_buyback'] + 0;
			}

		}
		$data['count']['ResultNums'] = count($deposit);
		$data['count']['pages'] = ceil($data['count']['ResultNums']/$array['page_num']);
		$data['type'] = $this->get_im_type();
		$deposit = array_chunk($deposit,$array['page_num']);
		$data['data'] = $deposit[$array['page']-1];
		if (!empty($data['data'])) {
			$data['Code'] = 10021;
		}else{
			$data['Code'] = 10022;
		}
		foreach ($data['data'] as $key => $val) {

			$data['count']['bet_amt'] += $val['bet_amt'] + 0;
			if ($val['bt_status'] == 0) {
				$data['data'][$key]['Canwin'] = $val['bet_amt'] * $val['odds'];
				$data['count']['payoff'] += $val['payoff'] + 0;
				$data['count']['result'] += $val['result'] + 0;
			}else{
				$data['data'][$key]['Canwin'] = $val['bt_buyback'];
				$data['count']['payoff'] += $val['bt_buyback'] + 0;
				$data['count']['result'] += $val['bet_amt']-$val['bt_buyback'] + 0;
			}

		}
		$data['count']['Nums'] = count($data['data']);
		return $data;
	}


	//IM 體育
	public function get_im_type($k,$type){
		$BetType=[
			'1stCS'       =>   [  '上半場比分'                     ,'First Half: Correct Score','1H – Correct Score'],
			'1stDC'       =>   [  '上半場:雙勝'                    ,'First Half: Double Chance','1H – Double Chance'],
			'1stFTLT'     =>   [  '上半場:第壹個隊得分/最後得分球隊'  ,'First Half: First Team to Score / Last Team to Score','1H – Team to Score'],
			'1STHALF1X2'  =>   [  '上半場:1X2'                     ,'First Half: 1X2','1H–1x2'],
			'1STHALFAH'   =>   [  '上半場:讓球'                 ,'First Half: Asian Handicap','1H – Handicap'],
			'1STHALFOU'   =>   [  '上半場:大/小'                   ,'First Half: Over / Under','1H – Over / Under'],
			'1STHFRB'     =>   [  '滾球上半場:讓球'             ,'Running Ball First Half: Asian Handicap','1H – RB handicap'],
			'1STHFRB1X2'  =>   [  '滾球上半場:1X2'                 ,'Running Ball First Half: 1X2','1H–RB1x2'],
			'1STHFRBOU'   =>   [  '滾球上半場:大/小'               ,'Running Ball First Half: Over / Under','1H–RBOver/Under'],
			'1stOE'       =>   [  '上半場:單/雙'                   ,'First Half: Odd / Even','1H–Odd/Even'],
			'1stTG'       =>   [  '上半場:總入球'                  ,'First Half: Total Goal','1H – Total Goal'],
			'1X2'         =>   [  '全場:1X2'                      ,'Fulltime: 1X2','FT–1x2'],
			'2nd1X2'      =>   [  '下半場:1X2'                    ,'Second Half: 1X2','2H–1x2'],
			'2ndAH'       =>   [  '下半場:讓球'                ,'Second Half: Asian Handicap','2H – Handicap'],
			'2ndCS'       =>   [  '下半場:正確比分'                ,'Second Half: Correct Score','2H – Correct Score'],
			'2ndDC'       =>   [  '下半場:雙勝'                    ,'Second Half: Double Chance','2H – Double Chance'],
			'2ndFTLT'     =>   [  '下半場:雙勝/最後得分球隊'         ,'Second Half: First Team to Score / Last Team to Score','2H – Team to Score'],
			'2ndOE'       =>   [  '下半場:單/雙'                   ,'Second Half: Odd / Even','2H–Odd/Even'],
			'2ndOU'       =>   [  '下半場:大/小'                   ,'Second Half: Over / Under','2H – Over / Under'],
			'2ndTG'       =>   [  '下半場:總入球'                  ,'Second Half: Total Goal','2H – Total Goal'],
			'AH'          =>   [  '全場:讓球'                  ,'Fulltime: Asian Handicap','FT – Handicap'],
			'CS'          =>   [  '全場:正確比分'                  ,'Fulltime: Correct Score','FT – Correct Score'],
			'DC'          =>   [  '全場:雙勝'                      ,'Fulltime: Double Chance','FT – Double Chance'],
			'HF'          =>   [  '全場:半全場贏/平局'              ,'Fulltime: Halftime Fulltime winner / draw','FT – Halftime / Fulltime'],
			'OE'          =>   [  '全場:單雙'                      ,'Fulltime: Odd / Even','FT – Odd / Even'],
			'OR'          =>   [  'Outright'                      ,'Outright','Outright'],
			'OU'          =>   [  '全場:大/小'                     ,'Fulltime: Over / Under','FT – Over / Under'],
			'PARLAYALL'   =>   [  '連串過關'                         ,'Parlay All','Combo'],
			'RB'          =>   [  '滾球全場:讓球'                  ,'Running Ball Fulltime: Asian Handicap','FT – RB Handicap'],
			'RB1stCS'     =>   [  '滾球上半場:比分'                 ,'Running Ball First Half: Correct Score','1H – RB Correct Score'],
			'RB1stDC'     =>   [  '滾球上半場:雙勝'                 ,'Running Ball First Half: Double Chance','1H – RB Double Chance'],
			'RB1stFTLT'   =>   [  '滾球上半場:'                    ,'Running Ball First Half: First Team to Score / Last Team to Score','1H – RB Team to Score'],
			'RB1stOE'     =>   [  '滾球上半場:'                    ,'Running Ball First Half: Odd / Even','1H – RB Odd / Even'],
			'RB1stTG'     =>   [  '滾球上半場:'                    ,'Running Ball First Half: Total Goal','1H – RB Total Goal'],
			'RB1X2'       =>   [  '滾球全場:1X2'                   ,'Running Ball Fulltime: 1X2','FT – RB 1x2'],
			'RB2nd1X2'    =>   [  '滾球下半場:1X2'                 ,'Running Ball Second Half: 1X2','2H – RB 1x2'],
			'RB2ndAH'     =>   [  '滾球下半場:讓球'             ,'Running Ball Second Half: Asian Handicap','2H – RB Handicap'],
			'RB2ndCS'     =>   [  '滾球下半場:比分'                 ,'Running Ball Second Half: Correct Score','2H – RB Correct Score'],
			'RB2ndDC'     =>   [  '滾球下半場:雙勝'                 ,'Running Ball Second Half: Double Chance','2H – RB Double Chance'],
			'RB2ndFTLT'   =>   [  '滾球下半場:第壹隊入球/最後得分球隊' ,'Running Ball Second Half: First Team to Score / Last Team to Score','2H – RB Team to Score'],
			'RB2ndOE'     =>   [  '滾球下半場:單/雙'                ,'Running Ball Second Half: Odd / Even','2H–RBOdd/Even'],
			'RB2ndOU'     =>   [  '滾球下半場:大/小'                ,'Running Ball Second Half: Over / Under','2H–RBOver/Under'],
			'RB2ndTG'     =>   [  '滾球下半場:總入球'                ,'Running Ball Second Half: Total Goal','2H – RB Total Goal'],
			'RBCS'        =>   [  '滾球全場:比分'                   ,'Running Ball Fulltime: Correct Score','FT – RB Correct Score'],
			'RBDC'        =>   [  '滾球全場:雙勝'                   ,'Running Ball Fulltime: Double Chance','FT – RB Double Chance'],
			'RBFTLT'      =>   [  '滾球全場:第壹隊入球/最後得分球隊'   ,'Running Ball Fulltime: First Team to Score / Last Team to Score','FT – RB Team to Score'],
			'RBHF'        =>   [  '滾球全場:半全場贏/平局'           ,'Running Ball Fulltime: Halftime Fulltime winner / draw','FT – RB Halftime / Fulltime'],
			'RBOE'        =>   [  '滾球全場:單/雙'                  ,'Running Ball Fulltime: Odd / Even','FT–RBOdd/Even'],
			'RBOU'        =>   [  '滾球全場:大/小'                  ,'Running Ball Fulltime: Over / Under','FT–RBOver/Under'],
			'RBTG'        =>   [  '滾球全場:總入球'                 ,'Running Ball Fulltime: Total Goal','FT – RB Total Goal'],
			'TG'          =>   [  '全場:總入球'                    ,'Fulltime: Total Goal','FT – Total Goal'],
			'TMSCO1ST'    =>   [  '全場:第壹隊入球/最後得分球隊'      ,'Fulltime: First Team to Score / Last Team to Score','FT – Team to Score'],
		] ;
		$OddsType=[
			'MALAY'=>'馬來盤',
			'HK'   =>'香港盤',
			'EURO' =>'歐洲盤',
		];
		if(!$type && !$k) return ['BetType'=>$BetType,"OddsType"=>$OddsType];
		elseif($type==1)  return $BetType[$k];
		else              return $OddsType[$k];
	}




	//會員專區、交易記錄、往來記錄
	public function correspondence(){
		$uid=$_SESSION['uid'];
		//接受get數據
		$s_date = $this->input->get('start_date');
		$e_date = $this->input->get('end_date');
		$username = $this->input->get('username');
		$deptype = $this->input->get('deptype');
		$page = $this->input->get('page');

		//時間判斷
		if (!empty($s_date) && !empty($e_date)) {
			$map['where'] = "k_user_cash_record.cash_date > '".$s_date." 00:00:00' and k_user_cash_record.cash_date < '".$e_date." 23:59:59' ";
			$con['where'] = "cash_date > '".$s_date." 00:00:00' and cash_date < '".$e_date." 23:59:59' ";
			$this->add('s_date',$s_date);
			$this->add('e_date',$e_date);
		}elseif (!empty($s_date)) {
			$map['where'] = "k_user_cash_record.cash_date > '".$s_date." 00:00:00' ";
			$con['where'] = "cash_date > '".$s_date." 00:00:00' ";
			$this->add('s_date',date('Y-m-d'));
		}elseif (!empty($e_date)) {
			$map['where'] = "k_user_cash_record.cash_date < '".$e_date." 23:59:59' ";
			$con['where'] = "cash_date < '".$e_date." 23:59:59' ";
			$this->add('e_date',date('Y-m-d'));
		}else{
			$map['where'] = "k_user_cash_record.cash_date like '".date('Y-m-d')."%' ";
			$con['where'] = "cash_date like '".date('Y-m-d')."%' ";
			$s_date = $e_date = date('Y-m-d');
			$this->add('s_date',date('Y-m-d'));
			$this->add('e_date',date('Y-m-d'));
		}
		$map['where'] .= " and k_user_cash_record.is_show = 1 and k_user.site_id = '".SITEID."' ";
		//賬戶查詢
		if(!empty($username)) $map['where'] .= "and k_user.username = '".$username."'";
		//方式
		if (!empty($deptype)) {
			$type;
			$type = $deptype;
			$arrType = explode('-', $deptype);
			if (count($arrType) > 1) {
				//表示檢索參數cash_do_type
				$map['where'] .= " and ((k_user_cash_record.cash_do_type = '".$arrType[0]."' and k_user_cash_record.cash_type = '".$arrType[1]."' ) or k_user_cash_record.cash_do_type = '".$arrType[2]."') ";
				$con['where'] .= " and ((cash_do_type = '".$arrType[0]."' and cash_type = '".$arrType[1]."' ) or cash_do_type = '".$arrType[2]."') ";
			}else{
				if($type == 1 || $type == 2 || $type == 4 || $type == 3 || $type == 14 || $type == 15 || $type==19 || $type==7 ||$type==23){
					$map['where'] .= " and k_user_cash_record.cash_type = '".$type."'";
					$con['where'] .= " and cash_type = '".$type."'";
				}elseif($type == 'in'){
					//入款明細
					$map['where'] .= " and (k_user_cash_record.cash_do_type = '3' or k_user_cash_record.cash_type in (10,11)) ";
					$con['where'] .= " and (cash_do_type = '3' or cash_type in (10,11)) ";
				}elseif($type == 'out'){
					//出款明細
					$map['where'] .= " and ((k_user_cash_record.cash_do_type = '2' and k_user_cash_record.cash_type = '12') or k_user_cash_record.cash_type in (7,8,19)) ";
					$con['where'] .= " and ((cash_do_type = '2' and cash_type = '12') or cash_type in (7,8,19)) ";
				}else{
					$map['where'] .= " and k_user_cash_record.cash_type = '".$type."' ";
					$con['where'] .= " and cash_type = '".$type."' ";
				}

			}
		}
		//賬戶
		$map['where'] .= "and k_user.uid = '".$_SESSION['uid']."'";
		$con['where'] .= "and uid = '".$_SESSION['uid']."' ";

		//總數緩存redis 5秒
		$redis = RedisConPool::getInstace();
		$redis_key = SITEID.'_corres_'.$_SESSION['uid'].'_'.md5(md5(json_encode($map)));
		if ($redis->exists($redis_key)) {
			$count = $redis->get($redis_key);
		}else{
			//$count = $this->Member_record_model->get_record_cp_count($arrry);
			//獲得記錄總數
			$count = $this->Member_record_model->get_correspondence_count($map);
			$redis->setex($redis_key,'5',$count);
		}

		//分頁
		$perNumber=isset($_GET['page_num'])?$_GET['page_num']:50; //每頁顯示的記錄數
		$totalPage=ceil($count/$perNumber); //計算出總頁數
		$page=isset($page)?$page:1;
		$startCount=($page-1)*$perNumber; //分頁開始,根據此方法計算出開始的記錄
		$map['limit'] = $startCount;
		$map['limit2'] = $perNumber;
		//print_r($map);exit;

		//緩存5秒
		$redis_keyf = SITEID.'_corresrecord_'.$_SESSION['uid'].'_'.md5(json_encode($map));
		if ($redis->exists($redis_keyf)) {
			$data = $redis->get($redis_keyf);
			$data = json_decode($data,true);
		}else{
			$data = $this->Member_record_model->get_correspondence_record($map);
			$redis->setex($redis_keyf,'5',json_encode($data));
		}

		//小計
		foreach ($data as $k=>$val){
			$counts += $val['cash_num']+$val['discount_num'];
		}

		//總計
		$totl = $this->Member_record_model->get_correspondence_totl($con);
		if(!empty($totl)) $all_count=number_format($totl['0']['cash_num']+$totl['0']['discount_num'],2);
		//print_r($totl);exit;
		$this->add('deptype',$deptype);
		$this->add('page',$page);
		$this->add("all_count", $all_count);
		$this->add("data", $data);
		$this->add('counts', $counts? $counts: 0);
		$this->add('count', $count);
		$this->add('num', count($data)?count($data):0);
		$this->add("totalPage", $totalPage);
		$this->display('member/correspondence.html');
	}

	//會員專區、報表統計
	public function bb_count(){
		$this->load->model('Common_model');
		$copyright = $this->Common_model->get_copyright();
		$video_config = explode(',',$copyright['video_module']);
		foreach ($video_config as $key => $value) {
			$video_config[$value]['name'] = strtoupper($value).'視訊';
			unset($video_config[$key]);
			if(strtoupper($value) == 'MG'){
				$video_config['mgdz']['name'] = strtoupper($value).'電子';
			}
			if(strtoupper($value) == 'IM'){
				$video_config[$value]['name'] = strtoupper($value).'體育';
			}
			if(strtoupper($value) == 'DG99'){
				$video_config[$value]['name'] = '818彩票';
			}
		}
		asort($video_config);//數組排序
		if($_SESSION['shiwan'] == 1){
			$video_config = array();
		}
		$this->add('video_config',$video_config);
		$this->display('member/bb_count.html');
	}

	public function bb_count_do(){
		$this->load->library('Games');
		$loginname = $_SESSION['username'];
		$uid = $_SESSION['uid'];
		$action = $this->input->post('action');

		if($action == "yesterday"){
			$starttime = date("Y-m-d",strtotime("-1 day"))." 00:00:00";
			$endtime   = date("Y-m-d",strtotime("-1 day"))." 23:59:59";
		}elseif($action == "theweek"){
			$starttime=date("Y-m-d",strtotime("last Monday"))." 00:00:00";
			$endtime=date("Y-m-d")." 23:59:59";
			if(date("N",time()) == 1){
				$starttime=date("Y-m-d")." 00:00:00";
				$endtime=date("Y-m-d")." 23:59:59";
			}
		}elseif($action == 'lastweek'){
			$starttime=date("Y-m-d",strtotime("last Monday")-604800)." 00:00:00";
			$endtime=date("Y-m-d",strtotime("last Monday")-86400)." 23:59:59";
			if(date("N",time()) == 1){
				$starttime=date("Y-m-d",strtotime("last Monday"))." 00:00:00";
				$endtime=date("Y-m-d",strtotime("last Monday")+518400)." 23:59:59";
			}
		}else{
			$starttime = date("Y-m-d")." 00:00:00";
			$endtime   = date("Y-m-d")." 23:59:59";
		}

		$redis = RedisConPool::getInstace();
		$redis_key = 'mreport_'.SITEID.'_'.md5($uid.$starttime.$endtime);
		$data = array();
		$vdata = $redis->get($redis_key);
		if(empty($vdata)){
			//$map['table'] = "c_bet";
			//$map['where']['site_id'] = SITEID;
			//$map['where']['uid'] = $uid;
			//$map['where']['addtime >'] = $starttime;
			//$map['where']['addtime <'] = $endtime;
			////彩票數據
			//$map['sum'] = array('money');
			//$cp = $this->Member_record_model->get_bb_count_sum($map);
			//$cpc = $this->Member_record_model->get_bb_count_co($map);
			//有效彩票數據
			$valid_map = $map;
			$valid_map['where_in']['type'] = 'status';
			$valid_map['where_in']['val'] = array(1,2);
			$valid_map['sum'] = array('money','win');
			//$valid_cp = $this->Member_record_model->get_bb_count_sum($valid_map);
			//體育數據
			$con['where']['site_id'] = SITEID;
			$con['where']['uid'] = $uid;
			$con['table'] = "k_bet";
			$con['where']['bet_time >'] = $starttime;
			$con['where']['bet_time <'] = $endtime;
			$con['sum'] = array('bet_money');
			$ty = $this->Member_record_model->get_bb_count_sum($con);
			$tyc = $this->Member_record_model->get_bb_count_co($con);

			//有效體育數據
			$valid_con = $con;
			$valid_con['where']['is_jiesuan'] = 1;
			$valid_con['where_in']['type'] = 'status';
			$valid_con['where_in']['val'] = array(1,2,4,5);
			$valid_con['sum'] = array('bet_money','win');
			$valid_ty = $this->Member_record_model->get_bb_count_sum($valid_con);

			//體育串關
			$con['table'] = 'k_bet_cg_group';
			$cg_ty = $this->Member_record_model->get_bb_count_sum($con);
			$cg_tyc = $this->Member_record_model->get_bb_count_co($con);

			$cg_where = $con;
			$cg_where['where']['is_jiesuan'] = 1;
			$cg_where['where_in']['type'] = 'status';
			$cg_where['where_in']['val'] = array(1,2,4,5);
			$cg_where['sum'] = array('bet_money','win');
			$cg_valid_ty = $this->Member_record_model->get_bb_count_sum($cg_where);
			//體育、串關總和
			$tyc += $cg_tyc;
			$ty['bet_money']+=$cg_ty['bet_money'];
			$valid_ty['bet_money']+=$cg_valid_ty['bet_money'];
			$valid_ty['win']+=$cg_valid_ty['win'];

			//$cpdata = array();
			//$cpdata['name'] = '彩票';
			//$cpdata['times'] = $cpc;
			//$cpdata['count'] = 0+$cp['money'];
			//$cpdata['valid_money'] = 0+$valid_cp['money'];
			//$cpdata['valid_win'] = $valid_cp['win'] - $valid_cp['money'];

			$tydata = array();
			$tydata['name'] = '體育';
			$tydata['times'] = $tyc;
			$tydata['count'] = $ty['bet_money'];
			$tydata['valid_money'] = $valid_ty['bet_money'];
			$tydata['valid_win'] = $valid_ty['win'];

			/*p($cpdata);
			p($tydata);die;*/
			$this->load->model('Common_model');
			$copyright = $this->Common_model->get_copyright();
			$video_config = explode(',',$copyright['video_module']);
			//$data[] = $cpdata;
			$data[] = $tydata;
			if($_SESSION['shiwan'] == 0){
				$games = new Games();
				$type_str = '';
				foreach ($video_config as $key => $value) {
					if($value == 'mg'){
						$type_str .= $value.'|'.'mgdz'.'|';
					}else{
						$type_str .= $value.'|';
					}
				}

				$video_data = $games->GetUserAllAvailableAmount($type_str, $loginname, $starttime, $endtime);
				$video_data = json_decode($video_data);
				$video_data = $video_data->data->data;

				foreach ($video_config as $key => $value) {
					if($value == 'mg'){
						$video_config[] = 'mgdz';
					}
				}
				foreach ($video_config as $k => $v) {
					if($v == 'mgdz'){
						$info[$v]['name'] = 'MG電子';
					}elseif($v == 'pt'){
						$info[$v]['name'] = 'PT電子';
					}elseif($v == 'eg'){
						$info[$v]['name'] = 'EG電子';
					}elseif($v == 'im'){
						$info[$v]['name'] = 'IM體育';
					}else{
						$info[$v]['name'] = strtoupper($v).'視訊';
					}
					$abc = $video_data->$v;
					$info[$v]['times'] = !empty($abc[0]->BetBS) ? $abc[0]->BetBS : 0;
					$info[$v]['count'] = !empty($abc[0]->BetAll) ? $abc[0]->BetAll : 0;
					$info[$v]['valid_money'] = !empty($abc[0]->BetYC) ? $abc[0]->BetYC : 0;
					$info[$v]['valid_win'] = !empty($abc[0]->BetPC) ? $abc[0]->BetPC : 0;
					$data[] = $info[$v];
				}
			}
			$redis->set($redis_key,'30',json_encode($data));
		}else{
			$data = json_decode($vdata);
		}
		echo json_encode($data);exit;
	}

	//從統計表中取數據
	public function getReportFromRecord(){
		$this->load->model('Common_model');
		$video_config = $this->Common_model->get_video_dz_mem();
		$site_id = SITEID;
		$username = $_SESSION['username'];
		$action = $this->input->post('action');
		$data = $Sdata = $Edata = array();
		$data = $this->Member_record_model->report_data($action,$username,$site_id);
		foreach ($video_config as $key => $value) {
			foreach ($data as $k => $v) {
				if($key == $k){
					$video_config[$key]['name'] = $value['name'];
					$video_config[$key]['times'] = $v['num'];
					$video_config[$key]['count'] = $v['bet_all'];
					$video_config[$key]['valid_money'] = $v['bet_yx'];
					$video_config[$key]['valid_win'] = $v['win'];
				}
			}
		}
		$i = 0;
		foreach ($video_config as $key => $value) {
			$Edata[$i]['name'] = $value['name'];
			$Edata[$i]['times'] = $value['times']+0;
			$Edata[$i]['count'] = $value['count']+0;
			$Edata[$i]['valid_money'] = $value['valid_money']+0;
			$Edata[$i]['valid_win'] = $value['valid_win']+0;
			$i++;
		}
		//p($Edata);die;
		echo json_encode($Edata);exit;


	}
}
?>