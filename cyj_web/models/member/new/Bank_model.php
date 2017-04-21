<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Bank_model extends MY_Model {

    function __construct() {
        parent::__construct();
        $this->init_db();
    }

    public function get_bank_arr(){
        $redis = RedisConPool::getInstace();
        $vdata = $redis->hgetall('bank_type');
        if (empty($vdata)) {
            //redis中數據為空 從數據庫讀取
            $this->db->from('k_bank_cate');
            $this->db->where('state',1);
            $this->db->order_by('id','asc');
            $mdata = $this->db->get()->result_array();
            foreach ($mdata as $key => $val) {
                $bank_json = json_encode($val, JSON_UNESCAPED_UNICODE);
                $bank_json = str_replace('"', '-', $bank_json);
                $hset[$val['id']] = $bank_json;
            }
            $redis->hmset('bank_type',$hset);
            $bank_arr = $mdata;
        }else{
            $vdata = str_replace('-', '"', $vdata);
            foreach($vdata as $key=>$value){
                $vdata[$key] = json_decode($value,true);//轉數組
            }
            $bank_arr = $vdata;
            ksort($bank_arr);//按照鍵名從小到大排序
        }
        return $bank_arr;
    }

    //獲取站點剔除銀行卡條件
    public function get_bank_reject(){
        $gameids = $game_ids = array();
        $this->db->select("bank_id");
        $this->db->where("site_id",SITEID);
        $this->db->where("index_id",INDEX_ID);
        $bank_ids = $this->db->get('site_bank_reject_record')->result_array();
        if ($bank_ids) {
            foreach ($bank_ids as $key => $val) {
                $bankids[] = $val['bank_id'];
            }
        }
        return $bankids;
    }


    public function get_bank_in_one($type){
        $this->db->from('k_bank');
        $this->db->select('id,card_ID,card_userName,remark');
        $this->db->where('is_delete',0);
        $this->db->where('site_id',SITEID);
        $this->db->where('index_id',INDEX_ID);
        if ($type == 21) {
            $this->db->where('bank_type',101);
        }else{
            $this->db->where('bank_type',100);
        }
        return $this->db->get()->row_array();
    }


    public function get_bank_in_arr($level_id){
        $this->db->from('k_bank');
        $this->db->where('is_delete',0);
        $this->db->where('site_id',SITEID);
        $banksa = $this->db->get()->result_array();

        if($banksa){
            $banks = $arr = $data = '';
            foreach ($banksa as $key => $value) {
                $arr = explode(',',$value['level_id']);
                if(in_array($level_id, $arr)){
                    $banks[] = $value;
                }
            }
        }
        foreach ($banks as $key => $value) {
            $this->db->from('k_user_bank_in_record');
            $this->db->select('sum(deposit_money) as c,bid');
            $this->db->where('bid',$value['id']);
            $this->db->where('log_time >',date("Y-m-d").' 00.00.00');
            $money = $this->db->get()->row_array();
            if($money['c']>$value['stop_amount']){
                unset($banks[$key]);
            }
        }
        shuffle($banks);
        return $banks;
    }


    public function get_setmoney(){   //線上存款，獲取存款文案和銀行信息
        if($_SESSION['ty'] == 1 || $_SESSION['ty'] == 2){        //判斷是否為預覽
            $this->db->from('info_deposit_edit');
            $this->db->where('type',$_SESSION['ty']);
        }else{
            //判斷是否存在銀行卡 第三方 點卡支付
            $this->load->model('member/pay/Online_api_model');
            $is_bank = $this->get_bank_in_arr($_SESSION['level_id']);
            $is_online = $this->Online_api_model->is_online_pay(0);
            $is_card = $this->Online_api_model->is_online_pay(1);
            $this->db->from('info_deposit_use');
            if(empty($is_bank)){
                $this->db->where('type <>',2);  //不存在銀行卡時 不顯示公司入款
            }
            if(!($is_online)){
                $this->db->where('type <>',1);  //不存在第三方時 不顯示線上入款
            }
            if(!($is_card)){
                $this->db->where('type <>',20);  //不存在點卡時 不顯示點卡支付
            }
        }

        $this->db->where('site_id',SITEID);
        $this->db->where('index_id',INDEX_ID);
        $this->db->where('state',1);
        $this->db->order_by('sort','DESC');
        $deposit = $this->db->get()->result_array();
        return $deposit;
    }

    //掃碼存款 獲取二維碼
    //by hk
    public function get_setmoney_qr($type =''){
        $this->db->from('info_qr_use');
        // $this->db->select('logo_url');
        $this->db->join('info_qr_cate', 'info_qr_use.type = info_qr_cate.id'); //連接查詢類型名稱
        $this->db->where('site_id',SITEID);
        $this->db->where('index_id',INDEX_ID);
        $this->db->where('state',1);

        if($type){
            $this->db->where('type',$type);
            $data = $this->db->get()->row_array();
            $data['logo_url'] = $this->replacedomain($data['logo_url']);
            if ($type == 21) {
                $data['name'] = '微信掃碼存款';
            }elseif($type == 22){
                $data['name'] = '支付寶掃碼存款';
            }
        }else{
            $data = $this->db->get()->result_array();
        }

        return $data;

    }

    public function get_video_config(){      //獲取視訊名單
        $map['where']['site_id'] = SITEID;
        $map['where']['index_id'] = INDEX_ID;
        $map['table'] = 'web_config';
        $video_config = $this->rfind($map);
        $video_config = explode(',',$video_config['video_module']);
        return $video_config;
    }


    public function get_cash_record($uid){      //獲取現金交易記錄
        $this->db->from('k_user_cash_record');
        $this->db->where('index_id',INDEX_ID);
        $this->db->where('site_id',SITEID);
        $this->db->where('uid',$uid);
        $this->db->where('is_show',1);//是否顯示
        $this->db->where('cash_type',1);
        $this->db->where('cash_do_type',1);
        $this->db->order_by('cash_date desc');
        $cash_record = $this->db->get()->row_array();
        return $cash_record;
    }


    public function add_cash_record($uid,$username,$credit,$money,$remark){ //插入現金交易記錄
        $data_c = array();
        $data_c['uid'] = $uid;
        $data_c['agent_id'] = $_SESSION['agent_id'];
        $data_c['username'] = $username;
        $data_c['site_id'] = SITEID;
        $data_c['index_id'] = INDEX_ID;
        $data_c['cash_type'] = 1;
        $data_c['cash_do_type'] = 1; //表示存入
        $data_c['cash_num'] = $credit;
        $data_c['cash_balance'] = $money;
        $data_c['cash_date'] = date('Y-m-d H:i:s');

        if ($remark) {
            $data_c['remark'] = $remark;
        }
        $result = $this->db->insert('k_user_cash_record',$data_c);
        return $result;
    }

    //會員額度轉換日誌寫入
    public function conversion_log($g_type,$do_type,$credit,$code,$c_remark){
        //如果錢包額度不夠寫入redis
        if ($code == 100004) {
            $redis = RedisConPool::getInstace();
            $redis_akey = 'conversion_log_'.SITEID;
            $redis_adata = array(
                'uid'=>$_SESSION['uid'],
                'username'=>$_SESSION['username'],
                'v_type'=>$g_type,
                'money'=>$credit);
            $redis->setex($redis_akey,'1200',json_encode($redis_adata));
        }

        $ldata = array();
        $ldata['site_id'] = SITEID;
        $ldata['index_id'] = INDEX_ID;
        $ldata['username'] = $_SESSION['username'];
        $ldata['uid'] = $_SESSION['uid'];
        $ldata['agent_id'] = $_SESSION['agent_id'];
        $ldata['v_type'] = $g_type;//視訊類型
        $ldata['do_type'] = $do_type;
        $ldata['money'] = $credit;
        $ldata['code'] = $code;
        $ldata['remark'] = $c_remark;
        $ldata['do_time'] =date('Y-m-d H:i:s');
        return $this->db->insert('k_user_conversion_log',$ldata);
    }

    public function out_cash_record(){      //獲取出款信息
        $this->db->from('k_user_bank_out_record');
        $this->db->where('uid',$_SESSION['uid']);
        $this->db->where_in('out_status',array(0,4));
        $this->db->where('site_id',SITEID);
        $this->db->select('order_num');
        $result = $this->db->get()->row_array();
        return $result;
    }


    public function edit_pass($newpw){     //修改密碼
        $this->db->from('k_user');
        $this->db->where('uid',$_SESSION['uid']);
        $this->db->where('site_id',SITEID);
        $this->db->set('qk_pwd',$newpw);
        $this->db->update();
        $result = $this->db->affected_rows();
        return $result;
    }

    public function is_first(){     //判斷是否是首次出款
        $this->db->from('k_user_bank_out_record');
        $this->db->where('uid',$_SESSION['uid']);
        $this->db->where('out_status',1);
        $result = $this->db->get()->row_array();
        return $result;
    }

    public function get_agent_user(){   //獲取代理商帳號
        $this->db->from('k_user_agent');
        $this->db->where('id',$_SESSION['agent_id']);
        $this->db->select('agent_user');
        $agent_user = $this->db->get()->row_array();
        return $agent_user;
    }

    public function now_money(){    //查詢用戶當前余額
        $this->db->from('k_user');
        $this->db->select('money');
        $this->db->where('uid',$_SESSION['uid']);
        $umban = $this->db->get()->row_array();
        return $umban;
    }

    public function get_bank(){      // 獲取銀行卡官網
        $this->db->from('k_bank');
        $this->db->select('card_address');
        $this->db->where('id',$bid);
        $this->db->where('site_id',SITEID);
        $bank = $this->db->get()->row_array();
        return $bank;
    }

    public function is_first_in(){    //查詢是否首次入款
        $this->db->from('k_user_bank_in_record');
        $this->db->select('id');
        $this->db->where('uid',$_SESSION['uid']);
        $this->db->where('make_sure',1);
        $this->db->where('site_id',SITEID);
        $user_record = $this->db->get()->row_array();
        return $user_record;
    }

    public function get_order_num($order_num){    //查詢提交次數
        $this->db->from('k_user_bank_in_record');
        $this->db->select('order_num');
        $this->db->where('order_num',$order_num);
        $this->db->where('site_id',SITEID);
        $result = $this->db->get()->row_array();
        return $result;
    }

    public function get_level_des(){       //獲取層級位置
        $this->db->from('k_user_level');
        $this->db->select('level_des');
        $this->db->where('id',$_SESSION['level_id']);
        $this->db->where('site_id',SITEID);
        $level_des = $this->db->get()->row_array();
        return $level_des;
    }


    //額度轉入
    public function conversionIn($credit, $g_type){
        $uid = $_SESSION['uid'];
        $username = $_SESSION['username'];

        //更新會員金額
        $this->db->trans_begin();
        $this->db->where('uid',$uid);
        $this->db->where('money >=',$credit);
        $this->db->set('money','money-'.$credit,FALSE);
        $this->db->update('k_user');
        if($this->db->affected_rows() == 0){
            $this->db->trans_rollback();
            return 11;
        }

        $userinfo = $this->get_user_info($uid);
        //現金記錄
        $remark = "系統轉出" . $g_type . ":" . $credit . " 元; ";
        $log_2 = $this->add_cash_record($uid,$userinfo['username'],$credit,$userinfo['money'],$remark);   //插入現金交易記錄
        $last_id = $this->db->insert_id();

        if($this->db->trans_status() === FALSE){
            $this->db->trans_rollback();
            return 5;
        }

        $this->load->library('Games');
        $games = new Games();

        $data = $games->GetBalance($username, $g_type);
        $result = json_decode($data);

        if ($result->data->Code == 10017) {
            $sxbalance = floatval($result->data->balance);
        } else if ($result->data->Code == 10006) {
            if($g_type == 'pt'){
                $cur = "CNY";
            }else{
                $cur = "RMB";
            }
            $data = $games->CreateAccount($username, $userinfo["agent_id"], $g_type, INDEX_ID, $cur);
            if (!empty($data)) {
                $result = json_decode($data);
                if ($result->data->Code != 10011) {
                    $this->db->trans_rollback(); //數據回滾
                    return 6;
                }
            } else {
                //網絡無響應
                $this->db->trans_rollback(); //數據回滾
                return 7;
            }
            $sxbalance = 0;
        } else {
            $this->db->trans_rollback(); //數據回滾
            return 8;
        }

        if(empty($_SESSION['agent_id'])){
            $this->db->trans_rollback(); //數據回滾
            return 9;
        }
        //現金記錄
        $remark = "系統轉出" . $g_type . ":" . $credit . " 元," . $g_type . "余額:" . ($sxbalance + $credit) . "元";

        //更新日誌
        $this->db->where('id',$last_id);
        $this->db->where('uid',$uid);
        $this->db->set('remark',$remark);
        $this->db->update('k_user_cash_record');

        if($this->db->trans_status() === FALSE){
            $this->db->trans_rollback();
            return 10;
        }else{
            $this->db->trans_commit();
            //視訊開始加款
        }
    }


    //額度轉出
    public function conversionOut($credit, $g_type){
        $uid = $_SESSION['uid'];
        $username = $_SESSION['username'];
        $this->db->trans_begin();
        $this->db->where('uid',$uid);
        $this->db->set('money','money+'.$credit,FALSE);
        $this->db->update('k_user');

        $this->load->library('Games');
        $games = new Games();

        if($this->db->trans_status() === FALSE){
            $this->db->trans_rollback();
            return 14;
        }
        $data = $games->GetBalance($username, $g_type);
        $result = json_decode($data);
        if ($result->data->Code == 10017) {
            $sxbalance = floatval($result->data->balance);
        } else {
            $this->db->trans_rollback(); //數據回滾
            return 15;
        }

        if(empty($_SESSION['agent_id'])){
            $this->db->trans_rollback(); //數據回滾
            return 16;
        }

        $userinfo = $this->get_user_info($uid);
        //現金記錄
        $remark = $g_type . "轉系統：" . $credit . " 元," . $g_type . ":" . $sxbalance . "元";

        $log_3 = $this->add_cash_record($uid,$userinfo['username'],$credit,$userinfo['money'],$remark);   //插入現金交易記錄

        if($this->db->trans_status() === FALSE){
            $this->db->trans_rollback();
            return 17;
        }else{
            $this->db->trans_commit();
            //視訊開始加款
        }
    }


    //根據uid 獲取用戶基本信息
    public function get_user_info($uid){
        if(!empty($uid)){
            $this->db->from('k_user');
            $this->db->where('uid',$uid);
            $this->db->where('site_id',SITEID);
            $this->db->where('index_id',INDEX_ID);
            $userinfo = $this->db->get()->row_array();
            return $userinfo;
        }
    }

    //獲取額度轉換控制
    public function get_istransf(){
        $this->db->from('web_config');
        $this->db->select('is_transf');
        $this->db->where('site_id',SITEID);
        $this->db->where('index_id','a');
        $is_transf = $this->db->get()->row_array();
        return $is_transf;
    }

    //根據level_id 獲取該層級額度轉換最低限額
    public function get_transf_min($level_id){
        //查出會員所在的會員層級
        $this->db->from('k_user_level');
        $this->db->where('id',$level_id);
        $this->db->where('site_id',SITEID);
        $this->db->where('index_id',INDEX_ID);
        $rmb_pay_set = $this->db->get()->row_array();
        //通過會員層級，查處會員所在支付層級的額度轉換下限
        $this->db->from('k_cash_config');
        $this->db->where('id',$rmb_pay_set['RMB_pay_set']);
        $this->db->where('site_id',SITEID);
        $userinfo = $this->db->get()->row_array();
        if($userinfo){
            return $userinfo['transf_money_min'];
        }else{
            return 1;
        }
    }


}