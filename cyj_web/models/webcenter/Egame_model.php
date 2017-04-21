<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Egame_model extends MY_Model {

    function __construct() {
        parent::__construct();
    }

    //获取电子配置
    public function get_gameconf(){
        $redis_key = SITEID.'_'.INDEX_ID.'_web_config';
        $result = $this->get_redis_key($redis_key);
        $egame_config = explode(',',$result['dz_module']);
        foreach ($egame_config as $key => $value){
            if($value == 'bbdz'){
                $egame_config[$key] = 'BBIN';
            }elseif (substr($value, -2)=='dz'){
                $egame_config[$key] = strtoupper(substr($value ,0,-2));
            }else{
                $egame_config[$key] = strtoupper($value);
            }
        }
        return $egame_config;
    }

    //获取电子数据
    public function get_games_data($type) {
        $gameids = $this->get_game_reject($type);

        $map['type'] = $type;
        $map['status'] = 1;
        $this->public_db->where($map);
        //剔除对应游戏
        if ($gameids) {
            $this->public_db->where_not_in('id',$gameids);
        }
        $this->public_db->order_by('recommend DESC,id ASC');
        return $this->public_db->get('mg_game')->result_array();
    }

        //获取站点剔除电子条件
    public function get_game_reject($type){
        $gameids = $game_ids = array();
        $this->db->select("game_id");
        $this->db->where("site_id",SITEID);
        $this->db->where("index_id",INDEX_ID);
        $this->db->where("game_type",$type);
        $game_ids = $this->db->get('site_games_reject_record')->result_array();
        if ($game_ids) {
            foreach ($game_ids as $key => $val) {
                $gameids[] = $val['game_id'];
            }
        }
        return $gameids;
    }

    //获取电子内页主题颜色
    public function get_game_color(){
        //电子内页主题颜色
        $redis_key = SITEID . '_' . INDEX_ID . '_game_color';
        $color = $this->get_redis_key($redis_key);
        if(empty($color)){
            $this->db->from('info_activity_promotion_set');
            $this->db->where("index_id= '". INDEX_ID."' and site_id = '".SITEID."'");
            $this->db->select('bcolor');
            $colorstr=$this->db->get()->row_array();
            $bcolor = implode($colorstr);
            $color = empty($bcolor)?'':$bcolor;
            $this->set_redis_key($redis_key,$bcolor);
        }
        $color = trim($color,'"');
        $colordate = explode(",",$color);
        return $this->egame_html_color($colordate);
    }

    //控制电子内页颜色
    public function egame_html_color($colordate){
        $color_css ='';
        $color_css .= '<style type="text/css">';
        if (!empty($colordate[0])) {
            $color_css .= '.tab1{background:'.$colordate[0].'}';
        }
        if(!empty($colordate[1])){
            $color_css .= '.tab1 .divgmenu .ul_ul li.zhu_gameClass.off .bg_col{background:'.$colordate[1].'}';
            $color_css .= '.tab1 .divgmenu .ul_ul li.zhu_gameClass.off .act-img{border-top:13px solid '.$colordate[1].'}';
        }
        $color_css .= '.tab1 ul.game_category li a, .tab1 .search a.serch_but{';
        if (!empty($colordate[2])) {
            $color_css .= 'background:'.$colordate[2].';';
        }
        if (!empty($colordate[4])) {
            $color_css .= 'border: 1px solid '.$colordate[4].';';
        }
        $color_css .= '}';
        if (!empty($colordate[3])) {
            $color_css .= '.tab1 ul.game_category li a.active, .tab1 ul.game_category li a:hover{background:'.$colordate[3].'}';
            $color_css .= '.tab1 .search a.serch_but:hover{background:'.$colordate[3].'}';
        }
        if (!empty($colordate[5])) {
            $color_css .= '.tab1 .menudiv{background:'.$colordate[5].'}';
        }
        $color_css .= '</style>';
        return $color_css;
    }

}