<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Spread_model extends MY_Model {

    //讀取會員返利比例
    private $title = [  'fc_discount'=>'彩票','sp_discount'=>'體育',
        'ag_discount'=>'AG視訊','og_discount'=>'OG視訊',
        'mg_discount'=>'MG視訊','mgdz_discount'=>'MG電子',
        'ct_discount'=>'CT視訊','pt_discount'=>'PT電子',
        'lebo_discount'=>'LEBO視訊','bbin_discount'=>'BB視訊',
        'bbdz_discount'=>'BB電子','bbsp_discount'=>'BB體育',
        'bbfc_discount'=>'BB彩票','eg_discount'=>'EG電子',
        'lmg_discount'=>'LMG視訊','gpi_discount'=>'GPI視訊',
        'gpidz_discount'=>'GPI電子','gd_discount'=>'GD視訊',
        'gddz_discount'=>'GD電子','sa_discount'=>'SA視訊',
        'ab_discount'=>'AB視訊','im_discount'=>'IM體育',
        'gg_discount'=>'GG捕魚','hb_discount'=>'HABA電子'
    ];

    function __construct() {
        parent::__construct();
        $this->init_db();
    }

    //讀取會員推廣設定
    public function get_spread_set(){
        $db_model = array();
        $db_model['tab'] = 'k_user_spread_set';
        $db_model['type'] = 1;
        $map = [
            'site_id'=>SITEID,
            'index_id'=>INDEX_ID,
            'state'=>1
        ];
        return $this->M($db_model)
            ->where($map)
            ->find();
    }

    //獲取推廣數據
    public function get_spread_data(){
        $db_model = array();
        $db_model['tab'] = 'k_user';
        $db_model['type'] = 1;
        $map = [
            'site_id'=>SITEID,
            'index_id'=>INDEX_ID,
            'uid'=>$_SESSION['uid']
        ];
        return $this->M($db_model)
            ->field("username,spread_num,spread_money")
            ->where($map)
            ->find();
    }

    //獲取視訊配置
    public function get_video_config(){
        $db_model = array();
        $db_model['tab'] = 'web_config';
        $db_model['type'] = 1;
        $config = $this->M($db_model)
            ->field("video_module,dz_module")
            ->where("site_id='".SITEID."' AND index_id='".INDEX_ID."'")
            ->find();
        $config['video_module'] = explode(',',$config['video_module']);
        $config['dz_module'] = explode(',',$config['dz_module']);
        $config = array_merge_recursive(array('sp','fc'),$config['video_module'],$config['dz_module']);

        $title_types['count_bet'] = '有效打碼';
        foreach ($config as $k => $v) {
            if ($v != 'agdz') {
                $title_types[$v.'_discount'] = $this->title[$v.'_discount'];
                if ($v == 'mg') {
                    $title_types['mgdz_discount'] = $this->title['mgdz_discount'];
                }elseif($v == 'bbin'){
                    $title_types['bbsp_discount'] = $this->title['bbsp_discount'];
                    $title_types['bbfc_discount'] = $this->title['bbfc_discount'];
                }
            }
        }
        return $title_types;
    }

    //前期累計返水數據
    public function get_spread_dis(){
        $db_model = array();
        $db_model['tab'] = 'k_user_discount_spread';
        $db_model['type'] = 1;
        $map = [
            'site_id'=>SITEID,
            'index_id'=>INDEX_ID,
            'state'=>1
        ];
        $data = $this->M($db_model)
            ->where($map)
            ->order('count_bet ASC')
            ->select();
        return $data;
    }

    //獲取排行數據
    public function get_spread_top(){
        $db_model = array();
        $db_model['tab'] = 'k_user';
        $db_model['type'] = 1;
        $obj = $this->M($db_model);
        $map = [
            'site_id'=>SITEID,
            'index_id'=>INDEX_ID,
            'shiwan'=>0
        ];
        $data['num'] = $obj->field('username,spread_num,spread_money')
            ->where($map)
            ->order('spread_num desc')
            ->limit('0,10')
            ->select();
        $data['money'] = $obj->field('username,spread_num,spread_money')
            ->where($map)
            ->order('spread_money desc')
            ->limit('0,10')
            ->select();
        return $data;
    }

    //獲取隨機系數
    public function get_spread_ratio(){
        $db_model = array();
        $db_model['tab'] = 'k_user_spread_set';
        $db_model['type'] = 1;
        $map = [
            'site_id'=>SITEID,
            'index_id'=>INDEX_ID
        ];
        $data = $this->M($db_model)->where($map)->find();
        return $data;
    }
}