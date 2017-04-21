<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Common_model extends MY_Model {

    function __construct() {
        parent::__construct();
        $this->init_db();
    }

    //根據uid 獲取用戶基本信息
    public function get_user_info($uid){
        if(!empty($uid)){
            $this->db->from('k_user');
            $this->db->where('uid',$uid);
            $this->db->where('site_id',SITEID);
            //$this->db->where('index_id',INDEX_ID);
            $userinfo = $this->db->get()->row_array();
            return $userinfo;
        }
    }

    //根據用戶的層級ID 獲取相應的支付參數如果當前層級未設置支付參數，則讀取網站的默認支付參數(根據SITEID查ID最小)
    public function get_user_level_info($level){
        if(!empty($level)){
            $this->db->from('k_user_level');
            $this->db->where('id',$_SESSION['level_id']);
            $this->db->where('site_id',SITEID);
            $this->db->where('index_id',INDEX_ID);
            $userinfo = $this->db->get()->row_array();
            if(!empty($userinfo['RMB_pay_set'])){
                $this->db->from('k_cash_config_view');
                $this->db->where('site_id',SITEID);
                $this->db->where('id',$userinfo['RMB_pay_set']);
                $levelinfo = $this->db->get()->row_array();
            }else{
                $this->db->from('k_cash_config_view');
                $this->db->where('site_id',SITEID);
                $this->db->order_by('id','asc');
                $levelinfo = $this->db->get()->row_array();
            }
            return $levelinfo;
        }
    }

    //獲取網站的版權信息
    public function get_copyright(){
        $redis = RedisConPool::getInstace();
        $redis_key = SITEID.'_'.INDEX_ID.'_web_config';
        $copyright = $redis->get($redis_key);
        if(!empty($copyright)){
            $con_data = json_decode($copyright,TRUE);
        }else{
            $map = array();
            $map['table'] = 'web_config';
            $map['where']['site_id'] = SITEID;
            $map['where']['index_id'] = INDEX_ID;
            $con_data = $this->rfind($map);

            if (!empty($con_data)) {
                if ($con_data['tel']) {
                    $tel = explode(',',$con_data['tel']);
                }
                if ($con_data['qq']) {
                    $qq = explode(',',$con_data['qq']);
                }
                if ($con_data['email']) {
                    $email = explode(',',$con_data['email']);
                }
            }

            $con_data['tel'] = $tel;
            $con_data['qq'] = $qq;
            $con_data['email'] = $email;
            $con_data_redis = json_encode($con_data,JSON_UNESCAPED_UNICODE);
            $redis->set($redis_key,$con_data_redis);
        }
        return $con_data;
    }


    public function cash_type_r($cash_type){
        switch ($cash_type) {
            case '1':
                return '額度轉換';
                break;
            case '2':
                return '體育下註';
                break;
            case '3':
                return '彩票下註';
                break;
            case '4':
                return '視訊下註';
                break;
            case '5':
                return '彩票派彩';
                break;
            case '6':
                return '活動優惠';
                break;
            case '7':
                return '系統拒絕出款';
                break;
            case '8':
                return '系統取消出款';
                break;
            case '9':
                return '優惠退水';
                break;
            case '10':
                return '在線存款';
                break;
            case '11':
                return '公司入款';
                break;
            case '12':
                return '存入取出';
                break;
            case '13':
                return '優惠沖銷';
                break;
            case '14':
                return '彩票派彩';
                break;
            case '15':
                return '體育派彩';
                break;
            case '19':
                return '線上取款';
                break;
            case '20':
                return '和局返本金';
            case '22':
                return '體育無效註單';
            case '23':
                return '系統取消出款';
            case '24':
                return '系統拒絕出款';
            case '25':
                return '彩票無效註單';
            case '26':
                return '彩票無效註單(扣本金)';
            case '27':
                return '註單取消(彩票)';
            case '28':
                return '註單取消(體育)';
            case '33':
                return '自助返水';
            case '34':
                return 'EG電子下註';
            case '35':
                return 'EG電子派彩';
            case '36':
                return '註單取消(EG電子)';
        }
    }
    //返回交易類別
    public function cash_do_type_r($do_type){
        $DoTarr = [ '1'=>'存入',
            '2'=>'取出',
            '3'=>'人工存入',
            '4'=>'人工取出',
            '5'=>'扣除派彩',
            '6'=>'返回本金'
        ];
        return $DoTarr[$do_type];
    }

    //銀行類別區分
    public function bank_type($type) {
        $this->db->from('k_bank_cate');
        $this->db->where('id',$type);
        $this->db->select('bank_name');
        $result = $this->db->get()->row_array();
        return $result['bank_name'];
    }

    //線下入款方式
    public function in_type($type) {
        $typeArr = ['1'=>'網銀轉帳',
            '2'=>'ATM自動櫃員機',
            '3'=>'ATM現金入款',
            '4'=>'銀行櫃臺',
            '5'=>'手機轉帳',
            '6'=>'支付寶轉賬',
            '7'=>'財付通',
            '8'=>'微信支付'
        ];
        return $typeArr[$type];
    }

    public function str_cut($str){
        $arr = array();
        if(strstr($str, ',操作者') || strstr($str, ',返水操作者')){
            if(strstr($str, ',操作者')){
                $arr = explode(',操作者', $str);
                return $arr[0];
            }elseif(strstr($str, ',返水操作者')){
                $arr = explode(',返水操作者', $str);
                return $arr[0];
            }

        }else{
            return $str;
        }
    }

    public function get_video_dz_config(){
        $data = $this->get_copyright();
        $data = array_merge(explode(',',$data['video_module']),explode(',',$data['dz_module']));
        foreach ($data as $key => $val) {
            if ($val=="bbdz") {
                $data[] = 'bbin';
                unset($data[$key]);
            } elseif (substr($val, -2) == 'dz') {
                $data[] = substr($val, 0, -2);
                unset($data[$key]);
            }
        }
        $data = array_unique($data);
        return $data;
    }

    //獲取模塊
    public function get_video_dz_mem(){
        $title = [
            'module'=>['sp'=>'體育','fc'=>'彩票'],
            'video_module'=>'視訊',
            'dz_module'=>'電子'
        ];
        $config = $this->get_copyright();
        if (!empty($config['module'])) { //常規模塊
            $d['module'] = explode(',',$config['module']);
        } else {$d['module'] = ['sp','fc'];}

        if (!empty($config['video_module'])) { //視訊模塊
            $d['video_module'] = explode(',',$config['video_module']);
        }

        if (!empty($config['dz_module'])) { //電子模塊
            $d['dz_module'] = explode(',',$config['dz_module']);
        }
        foreach ($d as $k => $v) {
            foreach ($v as $key => $val) {
                if ($k == 'module') {
                    $MOD[$val]['name']= $title[$k][$val];
                }else{
                    if ($val == 'im') {
                        $MOD[$val]['name'] = strtoupper($val).$title['module']['sp'];
                    }elseif (strstr($val ,'dz')) {
                        $MOD[$val]['name'] = strtoupper(strstr($val ,'dz',true)).$title[$k];
                        if($val == 'agdz'){
                            $MOD['agter']['name'] = "AG捕魚";
                        }
                    }else{
                        if($val=='dg99'){
                            $MOD[$val]['name'] = '818彩票';
                        }else{
                            $MOD[$val]['name'] = strtoupper($val).$title[$k];
                        }
                    }
                }
            }
        }
        return $MOD;
    }


    //系統維護
    // public function GetSiteStatus($d,$type=0,$cate_type,$site_type=1){
    //     $echo['status']=0;
    //     $echo['url']=null;
    //     if($d['Module'] || $d['Relation']){
    //         $data=array_merge($d['Module'],$d['Relation']);
    //         $wh=false;
    //         $r=array();
    //         if($cate_type){
    //             foreach($data as $v){
    //                 $msg=($v['message']);
    //                     if($v['site_id']==SITEID && $v['cate_type']==$cate_type){
    //                         $wh=true;
    //                         $url_[$v['cate_type']]=1;
    //                         $url_[$v['cate_type'].'_msg']=$msg;
    //                     }
    //             }
    //             foreach($data as $v){
    //                 $msg=($v['message']);

    //                 if(!$v['site_id'] && $v['cate_type']==$cate_type){
    //                     $wh=true;
    //                     $url_[$v['cate_type']]=1;
    //                     $url_[$v['cate_type'].'_msg']=$msg;
    //                 }
    //             }
    //         }else{
    //             foreach($data as $v){
    //                 $msg=($v['message']);
    //                 if($v['site_id']==SITEID){
    //                     $wh=true;
    //                     $url_[$v['cate_type']]=1;
    //                     $url_[$v['cate_type'].'_msg']=$msg;
    //                 }
    //             }
    //             foreach($data as $v){
    //                 $msg=($v['message']);

    //                 if(!$v['site_id'] ){
    //                     $wh=true;
    //                     $url_[$v['cate_type']]=1;
    //                     $url_[$v['cate_type'].'_msg']=$msg;
    //                 }
    //             }
    //         }

    //         if($wh==true){
    //             $echo['status']=1;
    //             $url='http://'.$_SERVER['HTTP_HOST'].'/wh/';
    //             if($type==1){
    //                 $url="<script>window.top.frames.location.href='$url'</script>";
    //                 $echo['url']=$url;
    //             }elseif($type==2){//會員中心額度轉換以及刷新額度使用
    //                 return $url_;
    //             }elseif($type==100){
    //                 return $url_;
    //             }else{
    //                 $echo['url']=$url;
    //             }
    //         }
    //     }
    //     if ($echo['status']==1){
    //         echo $echo['url'];
    //         exit;
    //     }
    // }

    function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
    {
        static $recursive_counter = 0;
        if (++$recursive_counter > 1000) {
            die('possible deep recursion attack');
        }
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->arrayRecursive($array[$key], $function, $apply_to_keys_also);
            } else {
                $array[$key] = $function($value);
            }
            if ($apply_to_keys_also && is_string($key)) {
                $new_key = $function($key);
                if ($new_key != $key) {
                    $array[$new_key] = $array[$key];
                    unset($array[$key]);
                }
            }
        }
        $recursive_counter--;
    }
    function JSON($array) {
        $this->arrayRecursive($array, 'urlencode', true);
        $json = json_encode($array);
        return urldecode($json);
    }


    //新會員中心樣式選擇
    public function get_membercolor(){
        $data = $this->get_copyright();
        switch ($data['membercolor']) {
            case '1':
                $color = "#875e5a";
                break;
            case 'style_red':
                $color = "#B60D3C";
                break;
            case 'style_yellow':
                $color = "#DAD643";
                break;
            case 'style_pur':
                $color = "#A636C9";
                break;
            case 'style_blue':
                $color = "#367DC9";
                break;
            default:
                $color = "#875e5a";
                break;
        }
        return $color;
    }
}