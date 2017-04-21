<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Transaction_model extends MY_Model {

    function __construct() {
        parent::__construct();
        $this->load->model('Common_model');
        $this->init_db();
    }

    //獲取模塊
    public function transaction_module_data(){
        $title = [
            'module'=>['sp'=>' 體育','fc'=>' 彩票'],
            'video_module'=>' 視訊',
            'dz_module'=>' 電子',
            'fish'=>' 捕魚'
        ];
        $config = $this->Common_model->get_copyright();
        if (!empty($config['module'])) { //常規模塊
            $d['module'] = explode(',',$config['module']);
        } else {$d['module'] = ['sp','fc'];}

        if (!empty($config['video_module'])) { //視訊模塊
            $d['video_module'] = explode(',',$config['video_module']);
        }

        if (!empty($config['dz_module'])) { //電子模塊
            $d['dz_module'] = explode(',',$config['dz_module']);
        }

        if (in_array('gg', $d['dz_module'])) { //捕魚模塊
            $d['fish'][] = 'gg';
        }
        if (in_array('ag', $d['video_module']) || in_array('agdz', $d['dz_module'])) {
            $d['fish'][] = 'agter';
        }

        foreach ($d as $k => $v) {
            foreach ($v as $val) {
                if (is_array($title[$k])) {
                    $MOD[$val] = $title[$k][$val];
                }else{
                    if ($val == 'im') {
                        $MOD[$val] = strtoupper($val).$title['module']['sp'];
                    }else {
                        $temp = $val;
                        if (substr($val, -2) == 'dz'){
                            $temp = substr($val, 0, -2);
                        }elseif ($val == 'agter'){
                            $temp = 'ag';
                        }
                        $MOD[$val] = strtoupper($temp).$title[$k];
                    }
                }
            }
        }
        asort($MOD);
        return $MOD;
    }

    //獲取可用彩票種類
    public function get_fc_games(){
        $fdata = $this->M(['tab'=>'fc_games', 'type'=>2])->where('state=1')->select('type');
        $fc_types = $this->sort_fc_play($fdata);
        return $fc_types;
    }

    ///按照類別排序彩種
    public function sort_fc_play($fc_datas){
        $order = ['yb','gpc','ssc','sf','11','k3','xy'];
        $order_fc_datas = array();
        foreach($order as $val){
            foreach($fc_datas as $k => $v){
                if($v['l_type'] == $val){
                    $order_fc_datas[$k] = $v;
                }
            }
        }
        return $order_fc_datas;
    }

    //第壹次查詢將數據存入redis
    public function transaction_bet_record_data($get){
        $uid = $_SESSION['uid'];
        $page = isset($get['Page']) ? $get['Page'] : 1;
        unset($get['Page']);
        $redis = RedisConPool::getInstace();
        $redis_key = SITEID.'_'.$uid.'_'.$get['g_type'].'_'.md5(json_encode($get));
        if ($page == 1 || !($redis->exists($redis_key)) ) {
            if ($get['g_type'] == 'sp') {
                $data = $this->transaction_sport_record($get);
            }elseif ($get['g_type'] == 'fc') {
                $data = $this->transaction_lottery_record($get);
            }elseif ($get['g_type'] == 'im') {
                $data = $this->transaction_im_recored($get);
                $resData['type'] = $this->get_im_type();
            }else {
                if($_SESSION['shiwan'] == 1){
                    $data['errorLevel'] = 'prompt';
                    $data['error'] = '請申請正式賬號！祝您遊戲愉快';
                    return $data;exit();
                }
                $data = $this->transaction_video_record($get);
            }
            if (!empty($data)) {
                $BetData = json_encode($data,JSON_UNESCAPED_UNICODE);
                $redis->setex($redis_key,'180',$BetData);
            }
        }elseif ($page > 1 && $redis->exists($redis_key)) {
            $Rdata = $redis->get($redis_key);
            $data = json_decode($Rdata,true);
        }else{
            $data['errorLevel'] = '';
            $data['error'] = '參數錯誤！';
            return $data;exit();
        }
        $resData['AllCount'] = count($data);
        //分頁
        $Chdata = array_chunk($data, 20);//數組分頁切割
        $totalPage = count($Chdata);
        if($totalPage < $page){
            $page = 1;
        }
        $offset = $page - 1;
        $resData['data'] = $Chdata[$offset];
        $resData['totalPage'] = $totalPage;
        return $resData;
    }

    //會員專區，交易記錄，體育
    public function transaction_sport_record($get){
        $uid = $_SESSION['uid'];
        $start_date = $get['S_Time'];
        $end_date = $get['E_Time'];
        $order = $get['OrderId'];
        $gtype = $get['Sptype'];
        $gtype = empty($gtype)?1:$gtype;
        if($gtype == 1){
            $sql_union = "k_bet";
        }else if($gtype == 2){
            $map['site_id'] = SITEID;
            $map['uid'] = $uid;
            $obj = $this->M(['tab'=>'k_bet_cg', 'type'=>1]);
            $deposit = $obj->where($map)->select();
            $deposit2 = $obj->where($map)->group('gid')->select();
            $sql_union=array();
            foreach($deposit2 as $key=>$value){
                $sql_union[] = "(select * from k_bet_cg_group where gid = '".$value['gid']."') as bet";
            }
        }

        $smap['where'] = "uid='".$uid."' and site_id='".SITEID."'";

        //時間判斷
        if (!empty($start_date)) {
            $s_date = $start_date . ' 00:00:00';
        }else{
            $s_date  = date("Y-m-d",time()) . ' 00:00:00';
        }

        if (!empty($end_date)) {
            $e_date = $end_date . ' 23:59:59';
        }else{
            $e_date = date("Y-m-d",time()) . ' 23:59:59';
        }
        //訂單號查詢
        if(empty($order)){
            $smap['where'] .= " and bet_time > '".$s_date."' and bet_time < '".$e_date."'";
        }else{
            $smap['where'] .= " and number = '".$order."'";
        }
        $smap['order'] .= "bet_time desc";
        $data = array();
        if($gtype == 1){
            $data = $this->get_record_b($sql_union,$smap);
        }else if($gtype == 2){
            foreach($sql_union as $key =>$value){
                $data[] = $this->get_record_b($value,$smap);
            }
        }

        $array = array();
        foreach ($data as $k=>$val){
            if($gtype == 2 && !empty($val)){
                foreach ($val as $key => $value) {
                    $map_c = "gid in (".$value['gid'].")";
                    $data_cg = $this->get_record_cg($map_c);
                }
                $value['chuanlian'] = $data_cg;
                $value['ball_sort'] = '串關';
                $array[] = $value;

            }else if(!empty($val)){
                $array[] = $val;
            }else{
                $array = array();
            }
        }
        foreach ($array as $k => $v) {
            $array[$k]['status'] = return_status($v['status']);
            $array[$k]['Scolor'] = return_color($v['ball_sort']);
        }

        return $array;
    }

    //獲取體育投註記錄
    function get_record_b($sql,$map){
        $obj=$this->M(['tab'=>$sql,'type'=>1]);
        return $obj->where($map['where'])
            ->order($map['order'])
            ->select();
        //p($obj->sql);die();
    }

    //獲取體育串關記錄
    function get_record_cg($map){
        return $this->M(['tab'=>'k_bet_cg','type'=>1])
            ->where($map)
            ->order("bid desc")
            ->select();
    }

    //會員專區，交易記錄，彩票
    public function transaction_lottery_record($get){
        $uid=$_SESSION['uid'];
        $arrry['uid'] = $uid;
        $start_date = $get['S_Time'];
        $end_date = $get['E_Time'];
        $order = $get['OrderId'];
        $gtype = $get['Fctype'];
        $s_date = empty($start_date) ? date('Y-m-d') : $start_date;
        $e_date = empty($end_date) ? date('Y-m-d') : $end_date;

        //獲取彩票類型
        $fc_games = $this->get_fc_games();
        foreach ($fc_games as $key => $value) {
            $fc_types[$value['type']] = $value;
        }

        //彩票種類判斷
        if (!empty($gtype)) {
            $arrry['fc_type'] = $fc_types[$gtype]['type'];
        }

        //時間
        $arrry['addtime'] = [['>',$s_date." 00:00:00"],['<',$e_date." 23:59:59"]];

        //訂單號查詢
        if(!empty($order)){
            $arrry['did'] = $order;
        }

        $map['order'] = 'addtime desc';
        $map['where'] = $arrry;
        $data = $this->get_record_cp($arrry,$map);
        foreach ($data as $k => $v) {
            $data[$k]['mingx3'] = return_typec_cp($v['type'],$v['mingxi_3']);
            if (empty($data[$k]['mingx3'])) {
                $data[$k]['mingx3'] = '';
            }
            $data[$k]['jgres'] = return_result_cp($v);
        }

        return $data;
    }

    //獲取彩票投註記錄
    function get_record_cp($array,$map){
        return $this->M(['tab'=>'c_bet','type'=>1])
            ->where($array)
            ->order($map['order'])
            ->select();
    }

    //視訊記錄
    public function get_video_bet_record($type,$map,$date){
        $field_arr = $this->get_video_field($type); //統壹輸出字段
        $map[$field_arr['pkaccount']] = $_SESSION['username'];
        $order = $map['OrderId'];
        unset($map['OrderId']);
        //訂單查詢
        if (!empty($order)) {
            $map[$field_arr['bet_id']] = $order;
        }
        $sort = $field_arr['bet_id'].' DESC';
        $map['site_id'] = SITEID;
        $map[$field_arr['bet_time']] = array(array('>=',$date[0]),array('<=',$date[1]));

        $db_model['tab'] = $type.'_bet_record';
        $db_model['type'] = 3;
        if ($type == 'agter') {
            $db_model['tab'] = 'ag_cash_recordh';
        }
        $Obj = $this->M($db_model);
        $field = '/* parallel */ * ,site_id '; //默認查詢全部 
        foreach ($field_arr as $k => $v) {
            $field .= ',' . $v . ' AS ' . $k;
        }
        $data = $Obj->field($field)
            ->where($map)
            ->order($sort)
            ->select();

        $video_game_arr = $this->video_type($type);

        foreach ($data as $key => $value) {
            //遊戲類型字段
            $tmp_game = $value['bet_type'];
            if ($type == 'ag' && $value['data_type'] == 'EBR') {
                $data[$key]['game_zh'] = '電子遊戲';
            }else{
                if ($type == 'og' && $value['table_id'] == 10 && $tmp_game == 12) {
                    $data[$key]['game_zh'] = '新式龍虎';
                }else{
                    if ($type == 'bbin') {
                        $data[$key]['game_zh'] = $video_game_arr[$tmp_game]['name'];
                    }else{
                        $data[$key]['game_zh'] = $video_game_arr[$type][$tmp_game];
                        if($type == 'ag'){
                            if($value['play_type']=="22"||$value['play_type']=="21"){
                                $data[$key]['game_zh'] = "多臺龍虎";
                            }
                        }
                    }
                }
            }
            //其他數據轉義
            if($value['table_id']=='null' || empty($value['table_id'])){
                $data[$key]['table_id'] = '-'; //部分值存在字符串‘null’
            }
            //其他數據轉義
            if($value['BoardNumber']=='null' || empty($value['BoardNumber'])){
                $data[$key]['BoardNumber'] = '-'; //部分值存在字符串‘null’
            }
            if(empty($data[$key]['game_zh'])) $data[$key]['game_zh'] = $data[$key]['bet_type'];
        }
        return $data;
    }

    //獲取視訊所有中文對照
    public function video_type($vtype){
        $db_model = array();
        $db_model['tab'] = 'k_video_games';
        $db_model['type'] = 1;

        if($vtype == 'bbin'){
            return $this->M($db_model)->where("vtype = '".$vtype."'")->select('type');
        }else{
            return array(
                'ct'=>array('1'=>'百家樂','2'=>'輪盤','3'=>'股寶',
                    '4'=>'龍虎','5'=>'番攤','7'=>'保險百家樂',
                    '8'=>'波比輪盤','9'=>'股寶番攤','10'=>'波比百家樂',
                    '13'=>'色碟'),
                'og'=>array('11'=>'標準百家樂','12'=>'經典龍虎','13'=>'輪盤',
                    '14'=>'股寶','16'=>'番攤'),
                'ag'=>array('BAC'=>'百家樂','DT'=>'龍虎','SHB'=>'股寶',
                    'ROU'=>'輪盤','CBAC'=>'包桌百家樂','LINK'=>'連環百家樂',
                    'HUNTER'=>'捕魚'),
                'lebo'=>array('4'=>'龍虎','3'=>'股寶','1'=>'百家樂',
                    '2'=>'輪盤'),
                'bbin'=>array('3015'=>'番攤','3003'=>'龍虎鬥','3001'=>'百家樂',
                    '3002'=>'二八杠','3005'=>'三公','3006'=>'溫州牌九',
                    '3007'=>'輪盤','3008'=>'股寶','3010'=>'德州撲克',
                    '3011'=>'色碟','3011'=>'色碟','3011'=>'色碟',
                    '3011'=>'色碟','3012'=>'牛牛','3014'=>'無限21點')
            );
        }
    }

    public function transaction_video_record($get){
        $Company = $get['g_type'];
        $start_date = (empty($get['S_Time']) ? date("Y-m-d") : $get['S_Time'])." 00:00:00";
        $end_date = (empty($get['E_Time']) ? date("Y-m-d") : $get['E_Time'])." 23:59:59";
        $date = array($start_date,$end_date);

        $map = array();
        $map['OrderId'] = $get['OrderId'];
        if($get['g_type'] == 'mgdz'){//mg電子
            $Company = 'mg';
            $map['module_id'] =  array('<','28');
        }elseif ($get['g_type'] == 'mg') {//mg視訊
            $map['module_id'] =  array('in','(28,29,30,32)');

        }elseif ($get['g_type'] == 'agdz'){//ag電子
            $Company = 'ag';
            $map['data_type'] = 'EBR';
        }elseif ($get['g_type'] == 'ag') {//ag視訊
            $map['data_type'] = ['!=','EBR'];

        }elseif ($get['g_type'] == 'bbdz'){//bbin電子
            $Company = 'bbin';
            $map['gamekind'] = 5;//電子
        }elseif ($get['g_type'] == 'bbin') {//bbin視訊
            $map['gamekind'] = ['!=',5];

        }elseif ($get['g_type'] == 'pt'){
            $map['Bet + Win'] = ['>',0];
        }

        $resData = $this->get_video_bet_record($Company,$map,$date);
        return $resData;
    }

    //適配視訊字段
    private function get_video_field($type){
        switch ($type){
            case 'lebo':
            case 'lmg':
            case 'gpi':
            case 'sa':
            case 'ab':
            case 'gd':
            case 'eg':
                $field = [  'bet_time'   => 'betstart_time',     //註單時間
                    'bet_id'     => 'game_id',           //註單號
                    'bet_type'   => 'game_type',         //遊戲類型
                    //'BoardNumber'=> 'game_code',         //局號
                    'table_id'   => 'table_id',          //桌號
                    'pkaccount'  => 'pkusername',        //系統賬號
                    'bet_amount' => 'betamount',         //總投註
                    'bet_valid'  => 'valid_betamount',   //有效投註
                    'bet_payout' => 'payout'             //結果
                ];
                break;
            case 'bbin':
                $field = [  'bet_time'   => 'wagers_date',
                    'bet_id'     => 'wagers_id',
                    'bet_type'   => 'gametype',
                    'BoardNumber'=> 'round_no',
                    'table_id'   => 'game_code',
                    'pkaccount'  => 'pkusername',
                    'bet_amount' => 'betamount',
                    'bet_valid'  => 'commissionable',
                    'bet_payout' => 'payoff'
                ];
                break;
            case 'mg':
                $field = [  'bet_time'   => 'date',
                    'bet_id'     => 'bet_no',
                    'bet_type'   => 'game_type',
                    //'BoardNumber'=> 'game_code',
                    // 'table_id'   => 'table_id',
                    'pkaccount'  => 'pkusername',
                    'bet_amount' => 'income',
                    'bet_valid'  => 'income',
                    'bet_payout' => 'payout-income'
                ];
                break;
            case 'ag':
                $field = [  'bet_time'   => 'bet_time',
                    'bet_id'     => 'bill_no',
                    'bet_type'   => 'game_type',
                    'BoardNumber'=> 'game_code',
                    'table_id'   => 'table_code',
                    'pkaccount'  => 'pkusername',
                    'bet_amount' => 'bet_amount',
                    'bet_valid'  => 'valid_betamount',
                    'bet_payout' => 'netamount'
                ];
                break;
            case 'agter':
                $field = [  'bet_time'   => 'scene_endtime',
                    'scene_starttime'   => 'scene_starttime',
                    'bet_id'     => 'scene_id',
                    'pkaccount'  => 'pkusername',
                    'sxaccount'  => 'player_name',
                ];
                break;
            case 'ct':
                $field = [  'bet_time'   => 'transaction_date_time',
                    'bet_id'     => 'transaction_id',
                    'bet_type'   => 'game_type',
                    'BoardNumber'=> 'play_id',
                    'table_id'   => 'table_id',
                    'pkaccount'  => 'pkusername',
                    'bet_amount' => 'betpoint',
                    'bet_valid'  => 'availablebet',
                    'bet_payout' => 'win_or_loss-betpoint'
                ];
                break;
            case 'pt':
                $field = [  'bet_time'   => 'GameDate',
                    'bet_id'     => 'GameCode',
                    'bet_type'   => 'GameType',
                    //'BoardNumber'=> 'game_code',
                    //'table_id'   => 'WindowCode',
                    'pkaccount'  => 'pkusername',
                    'bet_amount' => 'Bet',
                    'bet_valid'  => 'Bet',
                    'bet_payout' => 'Win-Bet'
                ];
                break;
            case 'og':
                $field = [  'bet_time'   => 'add_time',
                    'bet_id'     => 'order_number',
                    'bet_type'   => 'game_name_id',
                    'BoardNumber'=> 'game_record_id',
                    'table_id'   => 'table_id',
                    'pkaccount'  => 'pkusername',
                    'bet_amount' => 'betting_amount',
                    'bet_valid'  => 'valid_amount',
                    'bet_payout' => 'win_lose_amount'
                ];
                break;
            default:
                $field = [  'bet_time'   => 'betstart_time',     //註單時間
                    'bet_id'     => 'game_id',           //註單號
                    'bet_type'   => 'game_type',         //遊戲類型
                    //'BoardNumber'=> 'game_code',         //局號
                    'table_id'   => 'table_id',          //桌號
                    'pkaccount'  => 'pkusername',        //系統賬號
                    'bet_amount' => 'betamount',         //投註
                    'bet_valid'  => 'valid_betamount',   //有效投註
                    'bet_payout' => 'payout'             //結果
                ];
        }
        return $field;
    }

    //視訊電子壹個種類的信息
    public function get_all_one($vtype) {
        return $this->M(['tab'=>'k_video_games','type'=>1])
            ->where(['vtype'=>$vtype,'gtype'=>0])
            ->order("id ASC")
            ->select();
    }

    //會員專區，交易記錄，IM體育
    public function transaction_im_recored($get) {
        $array['username'] = $_SESSION['username'];
        $array['Sptype'] = $get['Sptype'];
        $array['start_date'] = $get['S_Time'];
        $array['end_date'] = $get['E_Time'];
        $array['OrderId'] = $get['OrderId'];

        //時間判斷
        if (empty($array['start_date'])) {
            $array['start_date']  = date("Y-m-d");
        }
        if (empty($array['end_date'])) {
            $array['end_date'] = date("Y-m-d");
        }

        $ball_sort = $array['Sptype'];
        $db_module = ['tab'=>'im_bet_record','type'=>3];
        $map = [
            'site_id' => SITEID,
            'pkusername' => $array['username'],
            'bet_time' => [['>=',$array['start_date']." 00:00:00"],
                ['<=',$array['end_date']." 23:59:59"]],
        ];

        if ($ball_sort == 2) {
            $map['bet_type'] = 'PARLAYALL';
        }elseif($ball_sort == 1){
            $map['bt_status'] = '0';
        }else{
            $map['bt_status'] = 1;
        }
        if (!empty($array['OrderId'])) {
            $map['bet_id'] = $array['OrderId'];
        }
        $deposit = $this->M($db_module)->where($map)->select();
        $data = array();
        /*foreach ($deposit as $key => $val) {
            $data['count']['bet_amtAll'] += $val['bet_amt'] + 0;
            if ($val['bt_status'] == 0) {
                $data['count']['payoffAll'] += $val['payoff'] + 0;
                $data['count']['resultAll'] += $val['result'] + 0;
            }else{
                $data['count']['payoffAll'] += $val['bt_buyback'] + 0;
                $data['count']['resultAll'] += $val['bet_amt']-$val['bt_buyback'] + 0;
            }

        }*/

        $data['data'] = $deposit;
        foreach ($data['data'] as $key => $val) {

            //$data['count']['bet_amt'] += $val['bet_amt'] + 0;
            if ($val['bt_status'] == 0) {
                $data['data'][$key]['Canwin'] = $val['bet_amt'] * $val['odds'];
                //$data['count']['payoff'] += $val['payoff'] + 0;
                //$data['count']['result'] += $val['result'] + 0;
            }else{
                $data['data'][$key]['Canwin'] = $val['bt_buyback'];
                //$data['count']['payoff'] += $val['bt_buyback'] + 0;
                //$data['count']['result'] += $val['bet_amt']-$val['bt_buyback'] + 0;
            }

        }

        return $data['data'];
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

    //獲取交易記錄、往來記錄總計
    function get_correspondence_totl($map){
        $db_model = array();
        $db_model['tab'] = 'k_user_cash_record';
        $db_model['type'] = '1';
        $obj = $this->M($db_model);
        return  $obj->where($map['where'])
            ->field('SUM(discount_num) as discount_num,SUM(cash_num) as cash_num')
            ->select();
    }

    //獲取交易記錄、往來記錄總條數
    function get_correspondence_count($map){
        $db_model = array();
        $db_model['tab'] = 'k_user_cash_record';
        $db_model['type'] = '1';
        $obj = $this->M($db_model);
        return $obj->join('JOIN `k_user` ON k_user.uid = k_user_cash_record.uid')
            ->where($map['where'])
            ->count();;
    }

    //獲取交易記錄、往來記錄
    function get_correspondence_record($map){
        $db_model = array();
        $db_model['tab'] = 'k_user_cash_record';
        $db_model['type'] = '1';
        $obj = $this->M($db_model);
        return  $obj->where($map['where'])
            ->join('JOIN `k_user` ON k_user.uid = k_user_cash_record.uid')
            ->order('k_user_cash_record.id desc')
            ->limit($map['limit'])
            ->select();
    }

    //獲取監控總頁數
    public function get_monitor_count($map){
        $db_model = array();
        $db_model['tab'] = 'k_user_bank_in_record';
        $db_model['type'] = '1';
        $obj = $this->M($db_model);
        return $obj->where($map['where'])
            ->count();
    }


    //獲取正在入款記錄
    public function get_monitor_record($map){
        $db_model = array();
        $db_model['tab'] = 'k_user_bank_in_record';
        $db_model['type'] = '1';
        $obj = $this->M($db_model);
        return  $obj->where($map['where'])
            ->order('id desc')
            ->limit($map['limit'])
            ->select();
    }

    public function make_sure_zh($type){
        switch ($type) {
            case '0':
                $result = "<font style='color:blue'>正在處理</font>";
                break;
            case '1':
                $result = "<font style='color:green'>入款已確認</font>";
                break;
            case '2':
                $result = "<font style='color:red'>入款已取消</font>";
                break;
            default:
                $result = "<font style='color:blue'>等待處理</font>";
                break;
        }
        return $result;
    }

    public function into_style_zh($type){
        switch ($type) {
            case '1':
                $result = "公司入款";
                break;
            case '2':
                $result = "線上入款";
                break;
            default:
                $result = "未知來源";
                break;
        }
        return $result;
    }


}