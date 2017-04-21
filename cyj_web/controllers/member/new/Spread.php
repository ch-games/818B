<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spread extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('member/new/Spread_model');
        $this->Spread_model->login_check($_SESSION['uid']);
        if($_SESSION['shiwan']){
            message("試玩賬號不能使用此功能,請用正式賬號！",'/index.php/Index/new_member?url=4');
        }
        //判斷是否開啟
        $is_log = $this->Spread_model->get_spread_set();
        if(empty($is_log)){
            message("會員推廣功能暫未開啟！",'/index.php/Index/new_member?url=4');
        }
    }

    //我要推廣
    public function spread_mem_index(){

        //獲取推廣數據
        $sdata = $this->Spread_model->get_spread_data();
        //推廣鏈接
        $sdata['surl'] = $_SERVER['HTTP_HOST'].'/?u='.$_SESSION['uid'];
        //獲取視訊配置
        $video_types = $this->Spread_model->get_video_config();
        //獲取返利比例
        $disdata = $this->Spread_model->get_spread_dis();

        $this->add('sdata',$sdata);
        $this->add('disdata',$disdata);
        $this->add('video_types',$video_types);
        $this->display('web_public/member/spread/spread.html');
    }
    //排行榜
    public function spread_ranking_index(){

        //獲取排行榜
        $_spread = $this->Spread_model->get_spread_top();
        //獲取隨機系統
        $spread_ratio = $this->Spread_model->get_spread_ratio();

        $data = $this->spread_ranking_do($_spread,$spread_ratio);
        $this->add('sdata',$data);
        $this->display('web_public/member/spread/ranking.html');
    }

    public function spread_ranking_do($_spread,$spread_ratio){
        $siteid = SITEID;
        if($siteid=='t'){
            $num = ord($siteid[0]);
        }else{
            $num = ord($siteid[0]) + ord($siteid[1]) + ord($siteid[2]);
        }

        //人數排名
        foreach ($_spread['num'] as $key => $val) {
            $spread_['num'][$key]['order'] = $key + 1;
            $spread_['num'][$key]['username'] = substr_replace($val['username'],'****',2,-1);
            $spread_['num'][$key]['spread_num'] += intval(($num-$key)*$spread_ratio['num_ratio']);
        }
        //獲利金額排名
        foreach ($_spread['money'] as $k => $v) {
            $spread_['money'][$k]['order'] = $k + 1;
            $spread_['money'][$k]['username'] = substr_replace($v['username'],'****',2,-1);
            $spread_['money'][$k]['spread_money'] += ($num-$k)*$spread_ratio['money_ratio']*0.1;
        }
        return $spread_;
    }

}