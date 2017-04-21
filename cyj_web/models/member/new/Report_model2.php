<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

//報表統計
class Report_model2 extends MY_Model {
    function __construct() {
        parent::__construct();
        $this->init_db();
    }
    //報表
    public function report_data($type,$username,$site_id){
        $date = self::report_time($type);//時間區間

        $map = array();
        $map['username'] = $username;
        $map['site_id'] = $site_id;
        $map['day_time'] = array(array('>=',$date[0]),array('<=',$date[1]));

        //彩票
        $fdata['fc'] = $this->report_data_fc($map);
        //體育
        $fdata['sp'] = $this->report_data_sp($map);
        //視訊
        $vdata = $this->report_data_vd($map);
        //p($vdata);die;
        if ($vdata) {
            foreach ($vdata as $key => $val) {
                $fdata[$val['vtype']] = $val;
            }
        }

        return $fdata;
    }

    //當天 昨天 本周 上周
    public static function report_time($type){
        $date = array();
        switch ($type) {
            case 'today'://當天
                $mdate = date("Y-m-d");
                $date = array($mdate.' 00:00:00',$mdate.' 23:59:59');
                break;
            case 'yesterday'://昨天
                $mdate = date("Y-m-d", strtotime("-1 day"));
                $date = array($mdate.' 00:00:00',$mdate.' 23:59:59');
                break;
            case 'theweek'://本周
                $n_week = date('Y-m-d',(time()-((date('w')==0?7:date('w'))-1)*24*3600));
                $date = array($n_week.' 00:00:00',date("Y-m-d").' 23:59:59');
                break;
            case 'lastweek'://上周
                $n_week = date('Y-m-d',(time()-((date('w')==0?7:date('w'))-1)*24*3600));
                $l_week_s = date('Y-m-d', strtotime($n_week.'-7 day'));
                $l_week_e = date("Y-m-d", strtotime("last Sunday"));
                $date = array($l_week_s.' 00:00:00',$l_week_e.' 23:59:59');
                break;
            default:
                $mdate = date("Y-m-d");
                $date = array($mdate.' 00:00:00',$mdate.' 23:59:59');
                break;
        }
        return $date;
    }

    //彩票
    public function report_data_fc($map =array()){
        $db_model = array();
        $db_model['tab'] = 'c_bet_report';
        $db_model['type'] = 1;

        $data = $this->M($db_model)
            ->field("username,sum(num) as num,sum(bet_all) as bet_all,sum(bet_yx) as bet_yx,sum(win-bet_yx) as win")
            ->where($map)->find();
        return $data;
    }

    //體育
    public function report_data_sp($map =array()){
        $db_model = array();
        $db_model['tab'] = 'k_bet_report';
        $db_model['type'] = 1;

        $data = $this->M($db_model)
            ->field("uid,username,sum(num) as num,sum(bet_all) as bet_all,sum(bet_yx) as bet_yx,sum(win-bet_yx) as win")
            ->where($map)->find();
        return $data;
    }

    //電子
    public function report_data_vd($map =array()){
        $db_model = array();
        $db_model['tab'] = 'd_bet_report';
        $db_model['type'] = 1;

        $ddata = $this->M($db_model)
            ->field("site_id,username,sum(num) as num,sum(bet_all) as bet_all,sum(bet_yx) as bet_yx,sum(win) as win,vtype")
            ->where($map)->group("vtype")->select();
        //視訊
        $db_model['tab'] = 'v_bet_report';
        $vdata = $this->M($db_model)
            ->field("site_id,username,sum(num) as num,sum(bet_all) as bet_all,sum(bet_yx) as bet_yx,sum(win) as win,vtype")
            ->where($map)->group("vtype")->select();

        return array_merge_recursive($ddata,$vdata);
    }
}