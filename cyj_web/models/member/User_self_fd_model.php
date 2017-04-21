<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class User_self_fd_model extends MY_Model {

    function __construct() {
        parent::__construct();
        $this->load->model('Common_model');
        $this->init_db();
    }

    //獲取最近十筆交易記錄
    public function user_self_fd_data(){
        $fdata = array();
        $map['begin_date'] = date('Y-m-d').' 00:00:00';
        $map['end_date'] = date('Y-m-d').' 23:59:59';
        $order = date('Ymd');

        //$fdata['fc'] = $this->user_fcbet($map);
        $fdata['sp'] = $this->user_spbet($map);

        //獲取視訊配置
        $type = $this->video_config();
        if(in_array('ag', $type)){
            $type[] = 'agter';
        }
        $vdata = $this->user_videobet($map,$type);
        //p($vdata);exit;
        $allbet = 0;
        $allbet =$fdata['sp']['bet'] ;

        foreach ($type as $k => $v) {
            //總打碼
            if (empty($vdata[$v]['bet'])) {
                $vdata[$v]['bet'] = 0;
            }
            $allbet += $vdata[$v]['bet'];
            if ($v == 'mg') {
                if (empty($vdata['mgdz']['bet'])) {
                    $vdata['mgdz']['bet'] = 0;
                }
                $allbet += $vdata['mgdz']['bet'];
            }elseif($v == 'bbin'){
                if (empty($vdata['bbdz']['bet'])) {
                    $vdata['bbdz']['bet'] = 0;
                }
                if (empty($vdata['bbsp']['bet'])) {
                    $vdata['bbsp']['bet'] = 0;
                }
                if (empty($vdata['bbfc']['bet'])) {
                    $vdata['bbfc']['bet'] = 0;
                }
                $allbet += $vdata['bbdz']['bet'] + $vdata['bbsp']['bet'] +$vdata['bbfc']['bet'];
            }elseif($v == 'ag'){
                if(empty($vdata['agdz']['bet'])){
                    $vdata['agdz']['bet'] = 0;
                }

                $allbet += $vdata['agdz']['bet'];
            }elseif($v == 'agter'){
                if(empty($vdata['agter']['bet'])){
                    $vdata['agter']['bet'] = 0;
                }
                $allbet += $vdata['agter']['bet'];
            }
        }

        //優惠信息
        $discount = $this->map_dis($index_id);
//p($discount);exit;
        //獲取返點優惠
        foreach ($discount as $dt => $dv) {
            //存在該層級優惠
            if ($dv['level_id'] == $_SESSION['level_id']) {
                if ($dv['count_bet'] <= $allbet) {
                    $re = $dv;
                    break;
                }
            }elseif(!$dv['level_id']){
                if ($dv['count_bet'] <= $allbet) {
                    $re = $dv;
                    break;
                }
            }
        }

        $userIds = array();
        $userIds['orderIds'] = $order;

        //$userIds['fc_bet'] = sprintf("%.2f",($fdata['fc']['bet']+0));
        //$userIds['dg99_bet'] = sprintf("%.2f",($fdata['dg99']['bet']+0));
        $userIds['sp_bet'] = sprintf("%.2f",($fdata['sp']['bet']+0));
        //$userIds['dg99_fd'] = sprintf("%.2f",$fdata['dg99']['bet']*$re['dg99_discount']*0.01);
        $userIds['sp_fd'] = sprintf("%.2f",$fdata['sp']['bet']*$re['sp_discount']*0.01);
        //$userIds['six_fd'] = sprintf("%.2f",$fdata['fc']['six_bet']*$re['six_discount']*0.01);

        $userIds['total_e_fd'] = $userIds['fc_fd'] + $userIds['sp_fd'] + $userIds['six_fd'] + 0;

        $userIds['all_bet'] = $allbet;
        foreach ($type as $k => $v) {
            $userIds[$v.'_bet'] = sprintf("%.2f",($vdata[$v]['bet']+0));
            $userIds[$v.'_fd'] = sprintf("%.2f",$vdata[$v]['bet']*$re[$v.'_discount']*0.01);
            $userIds['total_e_fd'] += $userIds[$v.'_fd'];//總計返點

            if ($v == 'mg') {
                $userIds['mgdz_bet'] = sprintf("%.2f",($vdata['mgdz']['bet']+0));
                $userIds['mgdz_fd'] = sprintf("%.2f",$vdata['mgdz']['bet']*$re['mgdz_discount']*0.01);
                $userIds['total_e_fd'] += $userIds['mgdz_fd'];

            }elseif($v == 'bbin'){
                $userIds['bbdz_bet'] = sprintf("%.2f",($vdata['bbdz']['bet']+0));
                $userIds['bbdz_fd'] = sprintf("%.2f",$vdata['bbdz']['bet']*$re['bbdz_discount']*0.01);
                $userIds['bbsp_bet'] = sprintf("%.2f",($vdata['bbsp']['bet']+0));
                $userIds['bbsp_fd'] = sprintf("%.2f",$vdata['bbsp']['bet']*$re['bbsp_discount']*0.01);
                $userIds['bbfc_bet'] = sprintf("%.2f",($vdata['bbfc']['bet']+0));
                $userIds['bbfc_fd'] = sprintf("%.2f",$vdata['bbfc']['bet']*$re['bbfc_discount']*0.01);
                $userIds['total_e_fd'] += $userIds['bbdz_fd'] + $userIds['bbsp_fd'] + $userIds['bbfc_fd'];

            }elseif($v == 'ag'){
                $userIds['agdz_bet'] = sprintf("%.2f",($vdata['agdz']['bet']+0));
                $userIds['agdz_fd'] = sprintf("%.2f",$vdata['agdz']['bet']*$re['agdz_discount']*0.01);
                $userIds['total_e_fd'] += $userIds['agdz_fd'];
            }elseif($v == 'agter'){
                $userIds['agter_fd'] = sprintf("%.2f",$vdata['agter']['bet']*$re['agter_discount']*0.01);
                $userIds['agter_bet'] = sprintf("%.2f",($vdata['agter']['bet']+0));
                $userIds['total_e_fd'] += $userIds['agter_fd'];
            }

            //返點上限判斷
            if ($userIds['total_e_fd'] > $re['max_discount']) {
                $userIds['total_e_fd'] = $re['max_discount'];
            }
        }
        //die();
        return $userIds;
    }

    //獲取會員當天彩票打碼
    public function user_fcbet($map =array()){
        $this->db->from('c_bet');
        $this->db->where('uid',$_SESSION['uid']);
        $this->db->where('site_id',SITEID);
        $this->db->where('update_time >=',$map['begin_date']);
        $this->db->where('update_time <=',$map['end_date']);
        $this->db->where('mingxi_1 <>','特碼');//六合彩特碼時時返水
        if($map['fc_type']) $this->db->where('fc_type',$map['fc_type']);//彩票類型
        $this->db->where_in('status',array(1,2));
        // $this->db->select("sum(money) as bet");
        $this->db->select("sum(case when fc_type <> 'liuhecai' then money else 0 end) as bet,sum(case when fc_type = 'liuhecai' and mingxi_1 <> '特碼' then money else 0 end) as six_bet");
        return $this->db->get()->row_array();
    }

    //體育
    public function user_spbet($map =array()){
        $this->db->from('k_bet');
        $this->db->where('uid',$_SESSION['uid']);
        $this->db->where('site_id',SITEID);
        $this->db->where('update_time >=',$map['begin_date']);
        $this->db->where('update_time <=',$map['end_date']);
        $this->db->where_in('status',array(1,2,4,5));
        $this->db->select("sum(bet_money) as bet");
        $spbet = $this->db->get()->row_array();

        //體育串關
        $this->db->from('k_bet_cg_group');
        $this->db->where('uid',$_SESSION['uid']);
        $this->db->where('site_id',SITEID);
        $this->db->where('update_time >=',$map['begin_date']);
        $this->db->where('update_time <=',$map['end_date']);
        $this->db->where_in('status',array(1,2));
        $this->db->select("sum(bet_money) as bet");
        $spcbet = $this->db->get()->row_array();

        $spbet['bet'] = $spbet['bet'] + $spcbet['bet'];
        return $spbet;
    }

    //視訊
    public function user_videobet($arr =array(),$type){
        $vtype = array('ag'=>array('bet_time','valid_betamount'),
            'agter'=>array('scene_endtime','cost'),
            'og'=>array('add_time','valid_amount'),
            'mg'=>array('date','income'),
            'ct'=>array('transaction_date_time','availablebet'),
            'lebo'=>array('betstart_time','valid_betamount'),
            'dg99'=>array('betstart_time','valid_betamount'),
            'ab'=>array('betstart_time','valid_betamount'),
            'im'=>array('update_time','bet_amt'),
            'gpi'=>array('betstart_time','valid_betamount'),
            'gd'=>array('betstart_time','valid_betamount'),
            'sa'=>array('betstart_time','valid_betamount'),
            'lmg'=>array('betstart_time','valid_betamount'),
            'eg'=>array('betstart_time','valid_betamount'),
            'bbin'=>array('wagers_date','commissionable'),
            'pt'=>array('GameDate','Bet','Bet'));
        $map = array();
        //視訊數據讀取
        foreach ($type as $k => $v) {
            $k = $v;
            $v = $vtype[$v];
            if($k == 'agter'){
                $this->video_db->from('ag_cash_recordh');
            }else{
                $this->video_db->from($k.'_bet_record');
            }

            $tmp_str = "/* parallel */ sum($v[1]) as bet";
            $this->video_db->select($tmp_str);
            $map['pkusername'] = $_SESSION['username'];
            $map['site_id'] = SITEID;
            $map["$v[0] >="] = $arr['begin_date'];
            $map["$v[0] <="] = $arr['end_date'];

            //單個視訊條件判斷
            switch ($k) {
                case 'bbin':
                    $this->video_db->where_in('result',array(1,200));
                    $this->video_db->where_in('gamekind',array(5,15));
                    $this->video_db->where('commissionable >',0);
                    //電子
                    $video_arr['bbdz'] = $this->video_db->where($map)->get()->row_array();
                    //unset($map['gamekind']);
                    //體育
                    $this->video_db->from($k.'_bet_record');
                    $this->video_db->select($tmp_str);
                    $this->video_db->where_in('result',array('L','W','LL','LW'));
                    $this->video_db->where('gamekind',1);
                    $this->video_db->where('commissionable >',0);
                    $video_arr['bbsp'] = $this->video_db->where($map)->get()->row_array();

                    //彩票
                    $this->video_db->from($k.'_bet_record');
                    $this->video_db->select($tmp_str);
                    $this->video_db->where_in('result',array('L','W'));
                    $this->video_db->where('gamekind',12);
                    $this->video_db->where('commissionable >',0);
                    $video_arr['bbfc'] = $this->video_db->where($map)->get()->row_array();

                    $this->video_db->from($k.'_bet_record');
                    $this->video_db->select($tmp_str);
                    $this->video_db->where('result >',0);
                    $this->video_db->where('commissionable >',0);
                    $this->video_db->where_not_in('gamekind',array(1,5,12,15));
                    //$this->video_db->where_in('gamekind',array(1,3,12,15));
                    break;
                case 'og':
                    $this->video_db->where_in('result_type',array(1,2));
                    break;
                case 'mg':
                    //$map['module_id <'] = 28;
                    $this->video_db->where_not_in('module_id',array(25,28,29,30,32));
                    //電子
                    $video_arr['mgdz'] = $this->video_db->where($map)->get()->row_array();
                    //unset($map['module_id <']);

                    $this->video_db->from($k.'_bet_record');
                    $this->video_db->select($tmp_str);
                    $this->video_db->where_in('module_id',array(25,28,29,30,32));
                    break;
                case 'ag':
                    //電子
                    $this->video_db->where('data_type','EBR');
                    $video_arr['agdz'] = $this->video_db->where($map)->get()->row_array();

                    $this->video_db->from($k.'_bet_record');
                    $this->video_db->select($tmp_str);
                    $this->video_db->where('data_type','BR');
                    break;
                case 'ct':
                    $this->video_db->where('is_revocation',1);
                    break;
                case 'im'://IM體育
                    $this->video_db->where('settled',1);//結算
                    $this->video_db->where('bt_status',0);//無兌現
                    $this->video_db->where('bet_cancelled',0);//無取消
                    $this->video_db->where('result <>',0);//去掉和局
                    break;
                case 'lebo':
                case 'dg99':
                    $this->video_db->where('is_revocation',0);
                    break;
            }
            $video_arr[$k] = $this->video_db->where($map)->get()->row_array();
            unset($map["$v[0] <="]);
            unset($map["$v[0] >="]);
        }
        return $video_arr;
    }

    //獲取返點優惠
    public function map_dis(){
        $this->db->from('k_user_discount_set');
        $this->db->where('index_id',INDEX_ID);
        $this->db->where('site_id',SITEID);
        $this->db->where('is_delete',0);
        $this->db->order_by("count_bet desc,level_id desc");
        $this->db->select();
        return $this->db->get()->result_array();
    }

    //獲取視訊配置
    public function video_config(){
        $this->db->from('web_config');
        $this->db->where('site_id',SITEID);
        $this->db->where('index_id',INDEX_ID);
        $this->db->select('video_module');
        $result = $this->db->get()->row_array();
        if ($result['video_module']) {
            return explode(',',$result['video_module']);
        }
    }

    //前期累計返水數據
    public function user_self_fd_olddata($order){
        $this->db->from('k_user_self_fd');
        $this->db->where('index_id',INDEX_ID);
        $this->db->where('site_id',SITEID);
        $this->db->where('uid',$_SESSION['uid']);
        $this->db->where('order',$order);
        $this->db->select();
        $data = $this->db->get()->row_array();
        if (!$data) {
            $data = array(
                'all_bet'=>0,'sp_bet'=>0,
                'ag_bet'=>0,'agdz_bet'=>0,'agter_bet'=>0,
                'mg_bet'=>0,'mgdz_bet'=>0,
                'og_bet'=>0,
                'ct_bet'=>0,
                'lebo_bet'=>0,
                'dg99_bet'=>0,
                'bbin_bet'=>0,'bbdz_bet'=>0,'bbsp_bet'=>0,'bbfc_bet'=>0,
                'pt_bet'=>0,
                'eg_bet'=>0,
                'lmg_bet'=>0,
                'gpi_bet'=>0,
                'gd_bet'=>0,
                'sa_bet'=>0,
                'ab_bet'=>0,
                'im_bet'=>0,
                'total_e_fd'=>0,'sp_fd'=>0,
                'ag_fd'=>0,'agdz_fd'=>0,'agter_fd'=>0,
                'mg_fd'=>0,'mgdz_fd'=>0,
                'og_fd'=>0,
                'lmg_fd'=>0,
                'gpi_fd'=>0,
                'gd_fd'=>0,
                'sa_fd'=>0,
                'ab_fd'=>0,
                'im_fd'=>0,
                'ct_fd'=>0,
                'pt_fd'=>0,
                'eg_fd'=>0,
                'lebo_fd'=>0,'dg99_fd'=>0,
                'bbin_fd'=>0,'bbdz_fd'=>0,'bbsp_fd'=>0,'bbfc_fd'=>0);
        }
        return $data;
    }

    //自助返水處理
    public function user_self_fd_data_do(){
        $fdata = $_SESSION['self_fd_data'];
        $old_data = $this->user_self_fd_olddata($fdata['orderIds']);
        $fmoney = 0;
        //存在累計返水 減去之前
        if ($old_data['total_e_fd']) {
            $fmoney = $fdata['total_e_fd'] - $old_data['total_e_fd'];
        }else{
            $fmoney = $fdata['total_e_fd'];
        }

        //屏蔽負數
        if ($fmoney < 0.01) {return false;}

        $order = $fdata['orderIds'];
        unset($fdata['orderIds']);
        $time = date('Y-m-d H:i:s');

        //事務開啟
        $this->db->trans_begin();
        $this->db->where('uid',$_SESSION['uid']);
        $this->db->where('site_id',SITEID);
        $this->db->set('money','money+'.$fmoney,FALSE);
        $this->db->update('k_user');
        if(!$this->db->affected_rows()){
            $this->db->trans_rollback();
            return false;
        }

        //獲取會員當前余額
        $this->db->from('k_user');
        $this->db->where('uid',$_SESSION['uid']);
        $this->db->where('site_id',SITEID);
        $this->db->select('money');
        $userinfo = $this->db->get()->row_array();

        //存在記錄 更新
        if ($old_data['total_e_fd']) {
            //更新當天返水總記錄
            $this->db->where('uid',$_SESSION['uid']);
            $this->db->where('site_id',SITEID);
            $this->db->where('order',$order);
            $this->db->update('k_user_self_fd',$fdata);
            if(!$this->db->affected_rows()){
                $this->db->trans_rollback();
                return false;
            }
        }else{
            //插入當天數據
            $data_fd = array();
            $data_fd = $fdata;
            $data_fd['uid'] = $_SESSION['uid'];
            $data_fd['username'] = $_SESSION['username'];
            $data_fd['agent_id'] = $_SESSION['agent_id'];
            $data_fd['site_id'] = SITEID;
            $data_fd['index_id'] = INDEX_ID;
            $data_fd['do_time'] = $time;
            $data_fd['order'] = $order;
            $this->db->insert('k_user_self_fd',$data_fd);
        }

        //寫入自助返水記錄
        $data_fdl = array();
        $data_fdl['all_bet'] = $fdata['all_bet'] - $old_data['all_bet'];
        $data_fdl['sp_bet'] = $fdata['sp_bet'] - $old_data['sp_bet'];
        $data_fdl['ag_bet'] = $fdata['ag_bet'] - $old_data['ag_bet'];
        $data_fdl['mg_bet'] = $fdata['mg_bet'] - $old_data['mg_bet'];
        $data_fdl['og_bet'] = $fdata['og_bet'] - $old_data['og_bet'];
        $data_fdl['ct_bet'] = $fdata['ct_bet'] - $old_data['ct_bet'];
        $data_fdl['pt_bet'] = $fdata['pt_bet'] - $old_data['pt_bet'];
        $data_fdl['eg_bet'] = $fdata['eg_bet'] - $old_data['eg_bet'];
        $data_fdl['im_bet'] = $fdata['im_bet'] - $old_data['im_bet'];
        $data_fdl['lebo_bet'] = $fdata['lebo_bet'] - $old_data['lebo_bet'];
        $data_fdl['dg99_bet'] = $fdata['dg99_bet'] - $old_data['dg99_bet'];
        $data_fdl['bbin_bet'] = $fdata['bbin_bet'] - $old_data['bbin_bet'];
        $data_fdl['bbdz_bet'] = $fdata['bbdz_bet'] - $old_data['bbdz_bet'];
        $data_fdl['bbsp_bet'] = $fdata['bbsp_bet'] - $old_data['bbsp_bet'];
        $data_fdl['bbfc_bet'] = $fdata['bbfc_bet'] - $old_data['bbfc_bet'];
        $data_fdl['mgdz_bet'] = $fdata['mgdz_bet'] - $old_data['mgdz_bet'];
        $data_fdl['agdz_bet'] = $fdata['agdz_bet'] - $old_data['agdz_bet'];
        $data_fdl['agter_bet'] = $fdata['agter_bet'] - $old_data['agter_bet'];

        $data_fdl['total_e_fd'] = $fdata['total_e_fd']-$old_data['total_e_fd'];
        $data_fdl['sp_fd'] = $fdata['sp_fd'] - $old_data['sp_fd'];
        $data_fdl['ag_fd'] = $fdata['ag_fd'] - $old_data['ag_fd'];
        $data_fdl['og_fd'] = $fdata['og_fd'] - $old_data['og_fd'];
        $data_fdl['mg_fd'] = $fdata['mg_fd'] - $old_data['mg_fd'];
        $data_fdl['ct_fd'] = $fdata['ct_fd'] - $old_data['ct_fd'];
        $data_fdl['pt_fd'] = $fdata['pt_fd'] - $old_data['pt_fd'];
        $data_fdl['eg_fd'] = $fdata['eg_fd'] - $old_data['eg_fd'];
        $data_fdl['im_fd'] = $fdata['im_fd'] - $old_data['im_fd'];
        $data_fdl['lebo_fd'] = $fdata['lebo_fd'] - $old_data['lebo_fd'];
        $data_fdl['dg99_fd'] = $fdata['dg99_fd'] - $old_data['dg99_fd'];
        $data_fdl['bbin_fd'] = $fdata['bbin_fd'] - $old_data['bbin_fd'];
        $data_fdl['bbdz_fd'] = $fdata['bbdz_fd'] - $old_data['bbdz_fd'];
        $data_fdl['bbsp_fd'] = $fdata['bbsp_fd'] - $old_data['bbsp_fd'];
        $data_fdl['bbfc_fd'] = $fdata['bbfc_fd'] - $old_data['bbfc_fd'];
        $data_fdl['mgdz_fd'] = $fdata['mgdz_fd'] - $old_data['mgdz_fd'];
        $data_fdl['agdz_fd'] = $fdata['agdz_fd'] - $old_data['agdz_fd'];
        $data_fdl['agter_fd'] = $fdata['agter_fd'] - $old_data['agter_fd'];

        $data_fdl['uid'] = $_SESSION['uid'];
        $data_fdl['username'] = $_SESSION['username'];
        $data_fdl['agent_id'] = $_SESSION['agent_id'];
        $data_fdl['site_id'] = SITEID;
        $data_fdl['index_id'] = INDEX_ID;
        $data_fdl['do_time'] = $time;
        $data_fdl['order'] = $order;
        $this->db->insert('k_user_self_fd_log',$data_fdl);

        //寫入現金記錄
        $data_c = array();
        $data_c['uid'] = $_SESSION['uid'];
        $data_c['agent_id'] = $_SESSION['agent_id'];
        $data_c['username'] = $_SESSION['username'];
        $data_c['site_id'] = SITEID;
        $data_c['index_id'] = INDEX_ID;
        $data_c['cash_type'] = 33;//自助返水
        $data_c['cash_do_type'] = 1; //表示存入
        $data_c['cash_num'] = 0;
        $data_c['discount_num'] = $fmoney;
        $data_c['cash_balance'] = $userinfo['money'];
        $data_c['cash_date'] = $time;
        $data_c['remark'] = '自助返水,單號:'.$order;
        $this->db->insert('k_user_cash_record',$data_c);

        if($this->db->trans_status() === FALSE){
            $this->db->trans_rollback();
            return false;
        }else{
            $this->db->trans_commit();
            return true;
        }
    }
}