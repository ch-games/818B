<?php
defined('BASEPATH') OR exit('No direct script access allowed');

//我要推廣
class Spread extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('member/Spread_model');
        $this->Spread_model->login_check($_SESSION['uid']);
        if($_SESSION['shiwan'])
        {
            message("試玩賬號不能使用此功能,請用正式賬號！");
        }
        //判斷是否開啟
        $is_log = $this->Spread_model->get_spread_set();
        if(empty($is_log))
        {
            message("會員推廣功能暫未開啟！");
        }
    }

    //推廣主頁
    public function spread(){
        //推廣鏈接
        $surl = $_SERVER['HTTP_HOST'].'/?u='.$_SESSION['uid'];
        //獲取推廣數據
        $sdata = $this->Spread_model->get_spread_data();

        //讀取會員返利比例
        $title = array('fc_discount'=>'彩票','sp_discount'=>'體育','ag_discount'=>'AG視訊',
            'og_discount'=>'OG視訊','mg_discount'=>'MG視訊',
            'mgdz_discount'=>'MG電子','ct_discount'=>'CT視訊',
            'pt_discount'=>'PT電子','lebo_discount'=>'LEBO視訊',
            'bbin_discount'=>'BB視訊','bbdz_discount'=>'BB電子',
            'bbsp_discount'=>'BB體育','bbfc_discount'=>'BB彩票',
            'eg_discount'=>'EG電子','lmg_discount'=>'LMG視訊','gpi_discount'=>'GPI視訊','gd_discount'=>'GD視訊','sa_discount'=>'SA視訊','ab_discount'=>'AB視訊','im_discount'=>'IM體育');

        $vconfig = $this->Spread_model->video_config();
        if ($vconfig) {
            $video_types = array('fc_discount','sp_discount');
            foreach ($vconfig as $k => $v) {
                $video_types[] = $v.'_discount';
                if ($v == 'mg') {
                    $video_types[] = 'mgdz_discount';
                }elseif($v == 'bbin'){
                    $video_types[] = 'bbdz_discount';
                    $video_types[] = 'bbsp_discount';
                    $video_types[] = 'bbfc_discount';
                }
            }
        }

        //獲取返利比例
        $disdata = $this->Spread_model->get_spread_dis();

        $this->add('surl',$surl);
        $this->add('sdata',$sdata);
        $this->add('title',$title);
        $this->add('disdata',$disdata);
        $this->add('video_types',$video_types);
        $this->display('member/spread_index.html');
    }

    //推廣
    public function spread_top(){
        //獲取人數排行榜
        $spread_num = $this->Spread_model->get_spread_top('1');
        //獲取獲利排行榜
        $spread_money = $this->Spread_model->get_spread_top('2');

        //獲取隨機系統
        $spread_ratio = $this->Spread_model->get_spread_ratio();
        $siteid = SITEID;
        $num = ord($siteid[0]) + ord($siteid[1]) + ord($siteid[2]);

        foreach ($spread_num as $key => $val) {
            $spread_num[$key]['order'] = $key + 1;
            $spread_num[$key]['username'] = substr_replace($val['username'],'****',2,-1);
            $spread_num[$key]['spread_num'] += intval(($num-$key)*$spread_ratio['num_ratio']);
        }

        foreach ($spread_money as $k => $v) {
            $spread_money[$k]['order'] = $k + 1;
            $spread_money[$k]['username'] = substr_replace($v['username'],'****',2,-1);
            $spread_money[$k]['spread_money'] += ($num-$k)*$spread_ratio['money_ratio']*0.1;
        }


        $this->add('spread_num',$spread_num);
        $this->add('spread_money',$spread_money);
        $this->display('member/spread_top.html');
    }
}