<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Audit_model extends MY_Model {

    function __construct() {

        parent::__construct();
        $this->init_db();
    }

    //獲取視訊配置
    public function get_video_config(){
        $this->db->from('web_config');
        $this->db->where('site_id',SITEID);
        $this->db->select('video_module');
        $data = $this->db->get()->row_array();
        if ($data) {
            return explode(',',$data['video_module']);
        }else{
            return 0;
        }
    }


    //會員稽核
    public function get_user_audit($uid,$pay_data,$username,$type,$end_date){
        $this->db->from('k_user_audit');
        $this->db->where('uid',$uid);
        $this->db->where('type',1);
        if (!empty($end_date)) {
            $this->db->where('begin_date <=',$end_date);
        }
        $this->db->where('site_id',SITEID);
        $this->db->order_by('id','desc');
        $audit_all = $this->db->get()->result_array();
        if (empty($audit_all)) {
            if (!empty($end_date)) {
                $this->db->where('begin_date <=',$end_date);
            }
            $this->db->from('k_user_audit');
            $this->db->where('uid',$uid);
            $this->db->where('site_id',SITEID);
            $this->db->order_by('id','desc');
            $this->db->select('begin_date');
            $enddate = $this->db->get()->row_array();
            if (empty($enddate['begin_date'])) {
                $enddate['begin_date'] = '2015-06-01 00:00:00';
            }
            $audit_all[0]['begin_date'] = $enddate['begin_date'];
            $audit_all[0]['end_date'] = date('Y-m-d H:i:s');
            $audit_all[0]['deposit_money'] = 0;
            $audit_all[0]['username'] = $username;
            $audit_all[0]['uid'] = $uid;

            $audit_all[0]['is_ct'] = 0;
            $audit_all[0]['is_zh'] = 0;
            $fc_arr['bet0'] = 0;
            $sp_arr[0]['bet0'] = 0;
            $sp_arr[1]['bet0'] = 0;
            $video_arr['ag']['bet0'] = $video_arr['og']['bet0'] = 0;
            $video_arr['mg']['bet0'] = $video_arr['ct']['bet0'] = 0;
            $video_arr['pt']['bet0'] = $video_arr['lebo']['bet0'] = 0;
            $video_arr['bbin']['bet0'] = $video_arr['bj']['bet0'] = 0;

        }else{
            $fc_arr = $this->fc_user_bet($audit_all,$end_date,$uid);
            $sp_arr = $this->sp_user_bet($audit_all,$end_date,$uid);
            $video_arr = $this->video_user_bet($audit_all,$end_date,$username);
        }

        $cdis;//扣除的所有優惠
        $relax_limit = 0;//常態放寬額度
        //獲取視訊配置
        $vdata = $this->get_video_config();
        if(in_array('ag', $vdata)){
            $vdata[] = 'agter';
        }
        //稽核判斷
        foreach ($audit_all as $key => $v) {
            //綜合稽核判斷
            $zh_state = 0;//綜合稽核
            $ct_state = 0;//常態稽核

            $fc_bet = array();
            $sp_bet = array();
            $video = array();
            if (empty($v['end_date'])) {
                $audit_all[$key]['end_date']=$v['end_date'] = date('Y-m-d H:i:s');
            }

            //結束時間為下壹次開始時間
            if ($key) {
                $i = $key-1;
                $audit_all[$key]['end_date'] = $audit_all[$i]['begin_date'];
                if ($audit_all[$i]['relax_limit'] == '-') {
                    $relax_limit = $audit_all[$key]['relax_limit'] - 0;
                }else{
                    $relax_limit = $audit_all[$key]['relax_limit'] - $audit_all[$i]['relax_limit'];
                }
            }else{
                $audit_all[$key]['end_date'] = $end_date;
                $relax_limit = $v['relax_limit'];
            }

            $fc_bet = $fc_arr['bet'.$key] + 0;//彩票打碼
            $sp_bet = $sp_arr[0]['bet'.$key] + $sp_arr[1]['bet'.$key]+ 0;//彩票打碼


            $video = 0;
            foreach($vdata as $vk=>$val){
                $video = $video + $video_arr[$val]['bet'.$key];
            }

            /*$video  = $video_arr['ag']['bet'.$key] + $video_arr['og']['bet'.$key] + $video_arr['mg']['bet'.$key] +  $video_arr['ct']['bet'.$key] + $video_arr['lebo']['bet'.$key] + $video_arr['bbin']['bet'.$key] + 0;*/

            $at_allbet = $fc_bet + $sp_bet + $video;//總計打碼

            $audit_all[$key]['bet_all']=$at_allbet;
            if ($key>0) {
                $jkey = $key-1;
                if ($audit_all[$jkey]['is_pass_zh']) {
                    $audit_all[$key]['zh_bet'] = $audit_all[$jkey]['zh_bet'] - $audit_all[$jkey]['type_code_all']+$at_allbet;//當筆綜合打碼
                }else{
                    $audit_all[$key]['zh_bet'] = $audit_all[$jkey]['zh_bet']+$at_allbet;//當筆綜合打碼
                }

                //常態
                if ($audit_all[$jkey]['is_pass_ct']) {
                    $audit_all[$key]['ct_bet'] = $audit_all[$jkey]['ct_bet'] - $audit_all[$jkey]['normalcy_code']+$at_allbet;//當筆綜合打碼
                }else{
                    $audit_all[$key]['ct_bet'] = $audit_all[$jkey]['ct_bet']+$at_allbet;//當筆綜合打碼
                }
            }else{
                $audit_all[$key]['ct_bet'] = $at_allbet + $relax_limit;
                $audit_all[$key]['zh_bet'] = $at_allbet;
            }
            $audit_all[$key]['cathectic_sport'] = $sp_bet;
            $audit_all[$key]['cathectic_fc'] = $fc_bet;
            $audit_all[$key]['cathectic_video'] =$video;


            $dis = $v['catm_give'] + $v['atm_give'];//所有優惠
            //當筆稽核盈利扣除比例
            $base_money = $dis + $v['deposit_money'];//存款總金額
            $win_money = $fc_bet['win'] + $sp_bet['win'];//總計盈利

            if ($v['is_zh']  == '1') {
                //有綜合稽核
                $return_audit = $this->zh_audit($v['type_code_all'],$audit_all[$key]['zh_bet'] ,$dis);

                if ($return_audit['state'] == '0') {
                    //表示綜合稽核沒有通過
                    $cdis += $dis;//扣除所有優惠
                    $audit_all[$key]['deduction_e'] = $dis;//扣除所有優惠
                    $audit_all[$key]['is_pass_zh'] = 0;//是否通過綜合稽核
                    $zh_state = 0;
                }else{

                    $audit_all[$key]['is_pass_zh'] = 1;//是否通過綜合稽核
                    $zh_state = 1;
                }
            }else{
                $audit_all[$key]['is_pass_zh'] = 2;//沒有綜合稽核
                $audit_all[$key]['type_code_all'] = 0;
                $zh_state = 1;
            }

            //常態稽核判斷
            if ($v['is_ct'] == '1') {
                //有常態稽核
                //判斷存款金額 是否大於最低放寬額度
                $return_ct = $this->ct_audit($v['deposit_money'],$v['relax_limit'],$v['expenese_num'],$audit_all[$key]['ct_bet'] ,$v['normalcy_code']);
                if ($return_ct['state'] == '0') {
                    //沒有通過常態稽核
                    $audit_all[$key]['is_pass_ct'] = 0;//沒有通過常態稽核
                    $audit_all[$key]['deduction_xz'] = $return_ct['money'];//扣除行政費用
                    $audit_all['count_xz'] += $return_ct['money'];//扣除所有行政費用
                    if ($v['is_zh'] == '0') {
                        $audit_all[$key]['deduction_e'] = $dis;
                        $cdis += $dis;
                    }
                    $audit_all[$key]['deduction_e'] += $return_ct['money'];
                    $audit_all[$key]['is_expenese_num'] = 1;
                    $ct_state = 0;
                }else{
                    $audit_all[$key]['is_pass_ct'] = 1;//通過常態稽核
                    $ct_state = 1;
                    $audit_all[$key]['is_expenese_num'] = 0;
                }
            }else{
                //沒有常態稽核
                $ct_state = 1;
                $audit_all[$key]['is_pass_ct'] = 2;//不需要常態稽核
                $audit_all[$key]['normalcy_code'] = '-';
                $audit_all[$key]['relax_limit'] ='-';
                $ct_state = 1;
                $audit_all[$key]['is_expenese_num'] = 2;
            }

            //盈利扣除判斷
            if ($zh_state && $ct_state) {
                //全部稽核通過不扣除
                $audit_all[$key]['de_wind'] = 0;
            }else{
                if ($win_money > 0 && $base_money > 0) {
                    //盈利了扣除
                    $audit_all[$key]['de_wind'] = $win_money*($dis/$base_money);
                    $audit_all[$key]['deduction_e'] += $audit_all[$key]['de_wind'];//每筆稽核扣除金額
                }else{
                    $audit_all[$key]['de_wind'] = 0;
                }
            }
            //起始稽核到當前時間所有有效打碼
            $audit_all['bet_all'] += $at_allbet;
            $cdis += $audit_all[$key]['de_wind'];
        }
        $audit_all['count_dis'] = $cdis;//扣除所有優惠
        $audit_all['out_fee'] = $this->out_user_fee($uid,$pay_data);//出款手續費

        return $audit_all;
    }

    //綜合稽核判斷
    public function zh_audit($tc,$ab,$dis){
        $dataA = array();
        if ($tc > $ab) {
            //總有效打碼 小於 綜合打碼
            $dataA['dis'] = $dis;//扣除所有優惠
            $dataA['bet'] = $ab;//當筆有效打碼 傳遞到下筆
            $dataA['state'] = 0;//綜合稽核未通過
        }else{
            $dataA['dis'] = 0;
            $dataA['bet'] = $ab - $tc;
            $dataA['state'] = 1;//綜合稽核通過
        }
        return $dataA;
    }
    //常態稽核
    public function ct_audit($ak,$ac,$az,$total,$ct){
        //判斷存款金額 是否大於最低放寬額度
        if ($total < $ct) {
            //總打碼 小於常態稽核
            $ctdata['state'] = 0;//沒有通過常態稽核
            $ctdata['money'] = $ak*$az*0.01;//扣除行政費用
        }else{
            $ctdata['state'] = 1;//通過常態稽核
        }
        return $ctdata;
    }

    //稽核日誌記錄
    public function get_audit_log($map,$limit){
        $db_model['tab'] = 'k_user_audit_log';
        $db_model['type'] = 1;
        return $this->M($db_model)->where($map)->limit($limit)->order("id DESC")->select();
    }

    //彩票
    public function fc_user_bet($arr,$e_time,$uid){
        $cstr = '';
        $icount = count($arr);
        foreach ($arr as $key => $val) {
            //結束時間為上壹次開始時間
            if ($key) {
                $i = $key-1;
                $val['end_date'] = $arr[$i]['begin_date'];
            }else{
                $val['end_date'] = $e_time;
            }
            $cstr .= "sum(case when update_time >= '".$val['begin_date']."' and update_time <= '".$val['end_date']."' then money end) as bet".$key.',';
            //取出最大區間起始時間
            if ($icount == ($key + 1)) {
                $s_time = $val['begin_date'];
            }
        }
        $cstr .= 'uid';

        $this->db->from('c_bet');
        $this->db->where('uid',$uid);
        $this->db->where('site_id',SITEID);
        $this->db->where_in('status',array(1,2));
        //啟用自動分表
        $beginDate = date("Y-m-d H:i:s", strtotime("$s_time -48 hours"));
        $this->db->where('addtime >=',$beginDate);
        $this->db->where('addtime <=',$e_time);
        $this->db->select($cstr);
        return $this->db->get()->row_array();
    }

    //體育
    public function sp_user_bet($arr,$e_time,$uid){
        $cstr = $cstr_cg = '';
        foreach ($arr as $key => $val) {
            // if (empty($val['end_date'])) {
            //     $val['end_date'] = $e_time;
            // }
            //結束時間為上壹次開始時間
            if ($key) {
                $i = $key-1;
                $val['end_date'] = $arr[$i]['begin_date'];
            }else{
                $val['end_date'] = $e_time;
            }
            $cstr .= "sum(case when update_time >= '".$val['begin_date']."' and update_time <= '".$val['end_date']."' then bet_money end) as bet".$key.',';
        }
        $cstr .= 'uid';
        $this->db->from('k_bet');
        $this->db->where('uid',$uid);
        $this->db->where('site_id',SITEID);
        $this->db->where_in('status',array(1,2,4,5));
        $this->db->select($cstr);
        $sp_arr[0] = $this->db->get()->row_array();

        $this->db->from('k_bet_cg_group');
        $this->db->where('uid',$uid);
        $this->db->where('site_id',SITEID);
        $this->db->where_in('status',array(1,2));
        $this->db->select($cstr);
        $sp_arr[1] = $this->db->get()->row_array();
        return $sp_arr;
    }

    //視訊
    public function video_user_bet($arr,$e_time,$username){
        //視訊字段匹配
        //註單時間 有效下註 盈利
        $vtype = array('ag'=>array('bet_time','valid_betamount'),
            'agter'=>array('scene_endtime','cost'),
            'og'=>array('add_time','valid_amount'),
            'mg'=>array('date','income','payout'),
            'ct'=>array('transaction_date_time','availablebet'),
            'lebo'=>array('betstart_time','valid_betamount','payout'),
            'eg'=>array('betstart_time','valid_betamount','payout'),
            'lmg'=>array('betstart_time','valid_betamount'),
            'gpi'=>array('betstart_time','valid_betamount'),
            'gd'=>array('betstart_time','valid_betamount'),
            'sa'=>array('betstart_time','valid_betamount'),
            'ab'=>array('betstart_time','valid_betamount'),
            'gg'=>array('betstart_time','valid_betamount'),
            'hb'=>array('betstart_time','valid_betamount'),
            'im'=>array('update_time','bet_amt'),
            'bbin'=>array('wagers_date','commissionable','payoff'),
            'pt'=>array('GameDate','Bet','Bet','Win'));
        $icount = count($arr);
        //獲取視訊類型
        $vtypes = $this->get_video_config();
        if(in_array('ag', $vtypes)){
            $vtypes[] = 'agter';
        }
        //視訊數據讀取
        foreach ($vtypes as $ki => $vi) {
            $k = $vi;
            $v = $vtype[$k];
            if($k == 'agter'){
                $table = 'ag_cash_recordh';
            }else{
                $table = $k.'_bet_record';
            }
            $cstr = '';
            foreach ($arr as $key => $val) {
                //結束時間為上壹次開始時間
                if ($key) {
                    $i = $key-1;
                    $val['end_date'] = $arr[$i]['begin_date'];
                }else{
                    $val['end_date'] = $e_time;
                }

                //時間轉換
                if ($k === 'og' || $k === 'pt') {
                    $val['begin_date'] = date("Y-m-d H:i:s", strtotime("$val[begin_date] +12 hours"));
                    $val['end_date'] = date("Y-m-d H:i:s", strtotime("$val[end_date] +12 hours"));
                }elseif($k === 'ct'){
                    $val['begin_date'] = date("Y-m-d H:i:s", strtotime("$val[begin_date] +11 hours"));
                    $val['end_date'] = date("Y-m-d H:i:s", strtotime("$val[end_date] +11 hours"));
                }
                //區間起始時間
                if ($icount == ($key + 1)) {
                    $s_time = $val['begin_date'];
                }
                //條件匹配
                if ($key) {
                }else{
                    $e_time = $val['end_date'];
                }

                $cstr .= 'sum(case when '.$v[0]." >= '".$val['begin_date']."' and ".$v[0]." <= '".$val['end_date']."' then ".$v[1]." end) as bet".$key.',';
            }
            $cstr .= 'pkusername';

            $this->video_db->from($table);
            $this->video_db->select(' /* parallel */ '.$cstr);
            $mapv = array();
            $mapv["$v[0] >="] = $s_time;
            $mapv["$v[0] <="] = $e_time;
            $mapv["pkusername"] = $username;
            $mapv["site_id"] = SITEID;

            //$this->video_db->where('pkusername',$username);
            //$this->video_db->where('site_id',SITEID);
            //單個視訊條件判斷
            switch ($k) {
                case 'bbin':
                    // $this->video_db->where('result_type <>','-1');
                    // $this->video_db->where('result <>','D');
                    // $this->video_db->where('result <>','-1');
                    // $this->video_db->where('commissionable >',0);
                    $this->video_db->where('isvalid',1);
                    break;
                case 'og':
                    $this->video_db->where_in('result_type',array(1,2));
                    break;
                case 'mg':
                    break;
                case 'ag':
                    $this->video_db->where('flag',1);
                    break;
                case 'im'://IM體育
                    $this->video_db->where('settled',1);//已結算
                    $this->video_db->where('bt_status',0);//無兌現
                    $this->video_db->where('bet_cancelled',0);//無取消註單
                    $this->video_db->where('result <>',0);//去掉和局
                    break;
                case 'ct':
                    $this->video_db->where('is_revocation',1);
                    break;
                case 'lebo':
                    break;
            }
            $video_arr[$k] = $this->video_db->where($mapv)->get()->row_array();
        }
        return $video_arr;
    }

    //獲取會員手續費
    public function out_user_fee($uid,$pay_data){
        //開啟免手續費次數
        if($pay_data['is_fee_free']==1){
            //獲取當日累計出款次數
            //$endTime = date('Y-m-d H:i:s',(time()-$pay_data['repeat_hour_num']*60*60));
            $endTime = date("Y-m-d H:i:s", strtotime("-24 hours"));
            $this->db->from('k_user_bank_out_record');
            $this->db->where('uid',$uid);
            $this->db->where('site_id',SITEID);
            $this->db->where('out_status',1);
            $this->db->where('out_time >=',$endTime);
            $count_out = $this->db->count_all_results();
            if($pay_data['fee_free_num'] <= $count_out){
                $out_fee=$pay_data['out_fee'];
            }else{
                $out_fee=0;
            }
        }else{
            $out_fee=$pay_data['out_fee'];
        }
        return $out_fee;
    }

}