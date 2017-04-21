<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Lottery extends MY_Controller
{
    public function __construct(){
        parent::__construct();
        $this->load->model('lottery/Fc_com_model');
        $this->load->model('lottery/Lottery_model','lottery');
        
        $config_css = $this->config->item('css');
        $this->add('type',$type);
        $this->add('config_css',$config_css);
    }

    public function pankou_ajax(){
        if($this->input->post('action') == 'pankou_ajax'){
            $pankou_get = $this->input->post("pankou");
            $type = $this->input->post("type");
            if(!empty($pankou_get) && $pankou_get != $_SESSION['pankou']){
                $_SESSION['pankou'] = $pankou_get;//盘口切换
            }
            echo $pankou_get;
        }
    }
    
    //彩票入口
    public function index(){
        if(empty($_SESSION['pankou'])){
            $_SESSION['pankou']= 'A';
        }
        if($this->input->get("gameid") != 222){
            $_SESSION['pankou'] = 'A'; //临时用
        }
    
        $type = $this->input->get('type');

        //获取维护状态 是否维护
        $main_data = $this->Maintain_model->maintain_state('fc',SITEID,INDEX_ID,$type);
        if($main_data['is_maintain']){
        	$html = '<div class="wh-bg"><p class="wh-p">'.$main_data['content'].'</p></div>';
        	$this->add('whhtml', $html);
        }
        
        //彩票type过滤
        $type_arr_n = $this->Fc_com_model->fc_titles('');
        foreach ($type_arr_n as $key => $val) {
            $fc_games[] = $val['type'];
        }
        if(!in_array($type, $fc_games)){
            $type = 'cq_ssc';
        }
        //获取彩票名称
        $type_name_c = $this->Fc_com_model->fc_titles($type);
        $this->add('type_name_c', $type_name_c);
        
        //彩票公共头部
        $head = $this->lottery->_get_head();
        foreach($head as $k=>$v){
            if(!isset($v['data']))
                unset($head[$k]);
        }
        
        $this->add('uid',$_SESSION['uid']);
        //彩票一级玩法读取
        $list = $this->Fc_com_model->fc_games_type($type);
        $this->add('list', $list);
        
        $gameid = $this->input->get('gameid');
        //彩票赔率读取
        if ($type != 'liuhecai') {
            $odds_arr = $this->Fc_com_model->get_fc_odds_one($type,0,'A');
            foreach ($odds_arr as $key => $val) {
                $odds_arr[$key]['fc_type'] = $val['type2'];
                unset($odds_arr[$key]['type2']);//去掉多余
            }
        }
    
        //时时彩转数组格式
        if($type == 'cq_ssc' || $type == 'tj_ssc' || $type == 'xj_ssc' || $type == 'jx_ssc' ){
            $odds_html_5 =  pl_odds_ssc5($odds_arr);
            $odds_html_4 =  pailie_odds_ssc_4($odds_arr);
            $this->add('list_5',$odds_html_5);
            $this->add('list_4',$odds_html_4);
        }elseif($type == 'xy_28' || $type == 'bj_28' || $type == 'pc_28'){
            $odds_xy_28 =  pailie_odds_xy_28($odds_arr);
            $this->add('odds_xy_28',$odds_xy_28);
        }elseif($type == 'cq_ten' ||$type == 'gd_ten' ){
            $odds_html_2mian =  pailie_odds_ten($odds_arr);
            $odds_html_dijiqiu =  pailie_odds_ten_dijiqiu($odds_arr);
            $odds_html_4 =  pailie_odds_ssc_4($odds_arr);
            $odds_html_zonghe =  pailie_odds_ten_zonghe($odds_arr);
    
            $this->add('list_4',$odds_html_4);
    
            $this->add('odds_html_zonghe',$odds_html_zonghe);
            $this->add('odds_html_2mian',$odds_html_2mian);
            $this->add('odds_html_dijiqiu',$odds_html_dijiqiu);
        }elseif ($type=='pl_3'||$type == 'fc_3d'){
            $arr1 = array('第一球','第二球','第三球');
            $odds_html_5 =  pailie_odds_5($odds_arr,$arr1);
            $arr2 = array('独胆','跨度','3连','總和,龍虎');
            $odds_html =  pailie_odds_5($odds_arr,$arr2);
            $odds_html =  pailie_odds_5_j($odds_html);
            $this->add('odds_html',$odds_html);
            $this->add('list_5',$odds_html_5);
        }elseif ($type == 'js_k3' ||$type =='jl_k3' || $type =='ah_k3' || $type =='gx_k3'){
            $odds_html_5 =  pl_odds_k3($odds_arr,$arr1);
            $this->add('list_5',$odds_html_5);
        }elseif ($type=='bj_10'){
            $odds_html_dijiqiu =  pl_odds_ten($odds_arr);
            $this->add('odds_html_dijiqiu',$odds_html_dijiqiu);
        }elseif($type == 'bj_8' ){
            $oddsall = array('202','203','204','205','206');
            $beijin=array('840','841','842','844','847');
            $lianma = array();
            foreach ($odds_arr as $k=>$v){
                if(in_array($v['id'], $beijin)){
                    $selected[$k] =selected_one($v);
                }
                if(in_array($v['type_id'], $oddsall)){
                    $lianma[$v['fc_type']]['odds'] = 0;
                    $lianma[$v['fc_type']]['id'] = $v['id'];
                    $lianma[$v['fc_type']]['count_arr'] = $v['count_arr'];
                }
            }
            foreach ($lianma as $k => $v) {
                $aa = rtrim($v['odds'],',');
                $lianma[$k]['odds'] = $aa;
            }
            $j = array();
            $h = array();
            for($i=1;$i<=4;$i++){
                $j[] = $i;
            }
            for($i=1;$i<=20;$i++){
                $h[] = $i;
            }
    
            $arr1 = array('选一');
            $odds_html_5 =  pailie_odds_k3_5($selected[0],$arr1,8);
            $this->add('list_5',$odds_html_5);
            $this->add('lianma',$lianma);
            $this->add('j',$j);
            $this->add('h',$h);
            $arr2 = array('和值','上中下','奇和偶');
            $odds_html =  pailie_odds_5($odds_arr,$arr2);
            $this->add('odds_html',$odds_html);
            $this->add('selected', $selected);
            $this->add('oddsall', $oddsall);
        }elseif ($type=='gd_11' ||$type=='sd_11' ||$type=='jx_11'){
            $odds_gd11 = pailie_odds_gd11($odds_arr);	//广东11 总序列
            for($i=1;$i<=11;$i++){
                $h[] = $i;
            }
            $j[0] = array('第一球','第二球');
            $j[1] = array('第一球','第二球','第三球');
            $this->add('h',$h);
            $this->add('j',$j);
            $this->add('odds_gd11',$odds_gd11);
        }
        
        $this->add('head',$head);
        $this->add('url',URL);
        if(!empty($type)&&$type!='liuhecai'){
            $this->add('type',$type);
            $result = $this->lottery->_get_luzhu($type);
            $this->add('is_login',$_SESSION['uid']);
            $this->display("lottery/".$type.'.html');
        }else{
            $this->liuhecai($type,$html);
        }
    }

    //六合彩玩法处理
    public function liuhecai($type,$html){
        $gameid = $this->input->get('gameid');
        $ganemid_arr_n = array('222','223','224','225','226','227','228','229','230','231','232','233','234');
        if(!in_array($gameid, $ganemid_arr_n)){
            $gameid = '222';
        }
    
        $config = $this->config->item('games')[$gameid];
        $gamename2 = $this->input->get('gamename2');
        $gamename2 = $gamename2?$gamename2:$config[0]['name'];
        
        //赔率缓存redis
        $odds_arr = $this->Fc_com_model->get_fc_odds_one($type,$gameid,$_SESSION['pankou']);
    
        $this->add('config', $config);
        $this->add('gamename2', $gamename2);
    
        if ($gameid == 224) {
            //正码特处理
            foreach ($odds_arr as $key => $val) {
                if ($val['type2'] != $gamename2) {
                    unset($odds_arr[$key]);
                }
            }
            $odds_arr = tamaodds($odds_arr);
        }elseif($gameid==225){
            $odds_arr = zhengma($odds_arr);
        }elseif($gameid==226){
            $odds_arr = guoguan($odds_arr);
        }elseif($gameid==227 ){
            //连码特殊处理
            $odds_data = array();
            foreach ($odds_arr as $key => $val) {
                if ($val['type2'] == $gamename2) {
                    $odds_data[] = $val;
                }
            }
            $odds_arr = lianma($odds_data);
        }elseif($gameid==228 ){
        }elseif($gameid==229 ||$gameid==230 ||$gameid==231 ||$gameid==232 ||$gameid==233){
            
            
            //小玩法赔率分离
            $odds_data = array();
            foreach ($odds_arr as $key => $val) {
                if ($val['type2'] == $gamename2) {
                    $odds_data[] = $val;
                }
            }
            $odds_arr = $odds_data;
    
            if (strstr($gamename2,'尾') == true){
                $qiu =  func_get_weishu();
            }else{
                $qiu = func_get_shenxiao($gameid);
            }
        }elseif($gameid==234){
            $odds_data = array();
            foreach ($odds_arr as $key => $val) {
                if ($val['type2'] == $gamename2) {
                    $odds_data[] = $val;
                }
            }
            $odds_arr = $odds_data;
        }else{
            $odds_arr = tamaodds($odds_arr);
        }
		if($gameid == 229 || $gameid == 230 || $gameid == 231 || $gameid == 232){
            
            //赋予sort属性
            foreach($odds_arr as $k=>$v){
                $odds_arr[$k]['sort'] = array_search($v['input_name'],func_get_shuxiang());
            }
            //让数组按sort从小到大排序
            usort($odds_arr, function($a, $b) { 
                $al = $a['sort']; 
                $bl = $b['sort']; 
                if ($al == $bl) 
                    return 0; 
                return ($al > $bl) ? 1 : -1; 
            });
        }

        $now_qishu = $this->Fc_com_model->get_fc_qishu($type);//当前期数
        $this->add('now_qishu', $now_qishu);
        $this->add('is_login',$_SESSION['uid']);
        $this->add('gamename2', $gamename2);
        $this->add('odds_arr', $odds_arr);
        $this->add('type', $type);
        $this->add('qiu', $qiu);
        $this->add("whhtml", $html);
        $this->display("lottery/".$type.'/'.$gameid.'.html');
    }

    //彩票规则
    public function rule(){
        $lotteryId = $this->input->get('lotteryId');
        $this->add('type', $lotteryId);
        $this->display("lottery/rule.html");
    }

    //即可注单
    public function notCount(){
        if (empty($_SESSION['uid'])) {
            exit('error 404');
        }
        $uid = $_SESSION['uid'];
        $lotteryId = $this->input->get('lotteryId');
    
        $starttime = date('Y-m-d')." 00:00:00";
        $endtime  = date('Y-m-d')." 23:59:59";
        if(empty($lotteryId)){
            //添加5秒缓存
            $fdata = $this->Fc_com_model->get_fc_count($_SESSION['uid'],$starttime,$endtime);
    
            $this->add('data', $fdata);
            $this->display("lottery/notCount.html");
        }else{
            $map['table'] ='c_bet';
            $map['select'] = "*";
            $map['where'] = "addtime BETWEEN '".$starttime."' and '".$endtime."' and uid=".$uid."  and `js`= 0 and type='".$lotteryId."'";
            $map['order'] = 'id desc';
            $map['limit'] = '50';
            $data = $this->lottery->get_table($map);
            foreach ($data as $k=>$v){
                $mun['count'] =+($k+1);
                $mun['money'] +=$v['money'];
                $mun['win'] +=$v['win'];
            }
            $this->add('data', $data);
            $this->add('mun', $mun);
            $this->display("lottery/notCount2.html");
        }
    }

    public function get_json(){
        $postarr =$this->input->file_get();
        header('Content-Type: application/json;charset=utf-8');
        $type = $postarr['lotteryId'];
        $json = $this->lottery->_get_json($type);
        
        for($i=0;$i<=20;$i++){
        $json = preg_replace('/,"p":"'.$i.'"}/',',"p":'.$i.'}',$json);
        }
        
        echo $json;
    }
    
    //彩票下注
    public function bet(){
        $this->lottery->login_check($_SESSION['uid']);
        $postarr =$this->input->file_get();
        header('Content-Type: application/json;charset=utf-8');
        
        $this->load->model('maintenance_model');
        $lot = $this->maintenance_model->getweihu('lottery')?0:1;
        $type_status = $this->maintenance_model->getweihu($lotteryId)?0:1;
        if($lot == 0 || $type_status == 0){
            //下注维护判断
            $data=array("result"=>5,'msg'=>'维护中','errId'=>1026);
            echo json_encode($data);exit;
        }

        //获取维护状态 是否维护
        $main_data = $this->Maintain_model->maintain_state('fc',SITEID,INDEX_ID,$postarr['lotteryId']);
        if($main_data['is_maintain'] == 1){
        	//下注维护判断
        	$data=array("result"=>5,'msg'=>'维护中','errId'=>1026);
        	echo json_encode($data);exit;
        }
        
        //优化过滤
        if($postarr){
            if(isset($postarr['betParameters'][0]['mingxi_1']) && $postarr['betParameters'][0]['mingxi_1'] == 222)
                $res = $this->Fc_com_model->check_vaild($postarr['lotteryId'],$postarr['betParameters'],1,$_SESSION['pankou']);
            else
                $res = $this->Fc_com_model->check_vaild($postarr['lotteryId'],$postarr['betParameters'],1);
                
            if(!is_array($res)){
                switch($res){
                    case 1:
                        exit(json_encode(array('result'=>5,'msg'=>'internet error 100','errId'=>1026)));		///明细问题 或封盘时间下注
                    break;
                    case 2:
                        exit(json_encode(array('result'=>5,'msg'=>'重复数','errId'=>1026)));		///玩法问题
                    break;
                    case 3:
                        exit(json_encode(array('result'=>5,'msg'=>'网络问题，刷新重试','errId'=>1026)));		///输入值有效性问题
                    break;
                        
                }
            }else{
                $postarr = $res;
            }
        }else{
            exit();
        }
    
        $rel = 1;
        if($rel){
            $data = $this->lottery->_addlottery_bet($postarr,0);
            echo $data;
        }else{
            $data=array("result"=>5,"msg"=>"下注失败","errId"=>1026);
            echo json_encode($data);
        }
    }

    //六合彩赔率获取
	public function liuhecaijson(){
	    $postarr =$this->input->file_get();
	    header('Content-Type: application/json;charset=utf-8');
	    //$list = $this->lottery->get_odds_one($postarr['lotteryPan']);

        $ldata = $this->Fc_com_model->get_fc_odds_one('liuhecai',$postarr['lotteryPan'],$_SESSION['pankou']);
        $str3 = $str4 = $str5 = '';
        foreach ($ldata as $k=>$v){
            if($v['id'] == 8503 || $v['id'] == 8504 || $v['id'] == 1634 || $v['id'] == 1635){//2中特
                if(empty($str3)){
                    $str3 .= $v['odds_value'].'/';
                }else{
                    $str3 .= $v['odds_value'];
                }
                $list[$v['id']] = $str3;
            }elseif($v['id'] == 8507 || $v['id'] == 8508 || $v['id'] == 1638 || $v['id'] == 1639){//3中2
                if(empty($str4)){
                    $str4 .= $v['odds_value'].'/';
                }else{
                    $str4 .= $v['odds_value'];
                }
                $list[$v['id']] = $str4;
    
            }else{
                $list[$v['id']]=$v['odds_value'];
            }
        }

	    $type = 'liuhecai';
	    if($_SESSION['username']){
	        $IsLogin = true;
	    }else{
	        $IsLogin = false;
	    }
	    $fengpan_time = $this->lottery->_get_fengpan_time($type);
        $json = array(
            'Success' => 1,
            'Msg' =>"",
            'Obj' => array(
                'IsLogin'=>$IsLogin,
                'Lines' => $list,
                "CloseCountdown" => $fengpan_time['f_t_stro']
            ),
            'ExtendObj'=>array('IsLogin'=>$IsLogin),
            'OK'=>false,
            'PageCount'=>0
        );
        echo json_encode($json);
  }

    ///设置屏幕高度  公有文件
    public function setiframe(){
        $this->display("lottery/setiframe.html");
    }

    //获取会员余额
    public function GetBalance(){
        $postarr =$this->input->file_get();
        header('Content-Type: application/json;charset=utf-8');
        $type = 'liuhecai';
        $fengpan_time = $this->lottery->_get_fengpan_time($type);
        $kaijiang = $this->Fc_com_model->get_fc_auto($type,1);//最近开奖结果
        // p($kaijiang);
        if($kaijiang){
            for ($i=0;$i<7;$i++){
                $balls[$i]['number'] = $kaijiang['ball_'.($i+1)];
                $balls[$i]['sx'] = func_shenxiao($kaijiang['ball_'.($i+1)]);
                $balls[$i]['color'] =func_set_style($kaijiang['ball_'.($i+1)]);
            }
        }
        
        if ($_SESSION['uid']) {
            $money = $this->lottery->_get_userinfo($_SESSION['uid']);
        }else{
            $money['money'] = 0;
        }

        $json= array('Success'=>1,
            'Msg'=>'',
            'Obj'=>array(
                "CurrentPeriod"=>$this->Fc_com_model->get_fc_qishu($type),
                "OpenCount"=>$fengpan_time['k_t_stro'],
                "Balance"=>$money['money'],
                "LotterNo"=>$kaijiang['qishu'],
                'WinLoss'=>0,
                'PreResult'=>$balls,
              'NotCountSum'=>$this->lottery->_beted_limit_1($type)
            ),
            "ExtendObj"=>null,"OK"=>false,"PageCount"=>0
        );
        echo json_encode($json);
    }

}
