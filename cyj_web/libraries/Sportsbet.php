<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sportsbet  {



    /*生成登录验证ＴＯＫＥＮ　ＫＥＹ*/
    public function make_token_key($uid){
        return 'token_'.md5('token_'.CLUSTER_ID.'_'.SITEID.'_'.$uid);
    }
    public function write_bet_info($ball_sort,$column,$master_guest,$bet_point,$match_showtype,$match_rgg,$match_dxgg,$match_nowscore,$tid=0){
        $dm         =   explode("VS",$master_guest); //队名
        $qcrq       =   array("Match_Ho","Match_Ao"); //全场让球盘口
        $qcdx       =   array("Match_DxDpl","Match_DxXpl"); //全场大小盘口
        $ds         =   array("Match_DsDpl","Match_DsSpl"); //单双
        $info       =   "";
        if(strrpos($ball_sort,"足球") === 0 || $ball_sort == 'FT' || $ball_sort == 'FTP'){
            $bcrq   =   array("Match_BHo","Match_BAo"); //半场让球盘口
            $bcdx   =   array("Match_Bdpl","Match_Bxpl"); //半场大小盘口
            $qcdy   =   array("Match_BzM","Match_BzG","Match_BzH"); //全场独赢
            $bcdy   =   array("Match_Bmdy","Match_Bgdy","Match_Bhdy"); //半场独赢
            $sbbdz  =   array("Match_Hr_Bd10","Match_Hr_Bd20","Match_Hr_Bd21","Match_Hr_Bd30","Match_Hr_Bd31","Match_Hr_Bd32","Match_Hr_Bd40","Match_Hr_Bd41","Match_Hr_Bd42","Match_Hr_Bd43"); //上半波胆主
            $sbbdk  =   array("Match_Hr_Bdg10","Match_Hr_Bdg20","Match_Hr_Bdg21","Match_Hr_Bdg30","Match_Hr_Bdg31","Match_Hr_Bdg32","Match_Hr_Bdg40","Match_Hr_Bdg41","Match_Hr_Bdg42","Match_Hr_Bdg43"); //上半波胆客
            $sbbdp  =   array("Match_Hr_Bd00","Match_Hr_Bd11","Match_Hr_Bd22","Match_Hr_Bd33","Match_Hr_Bd44","Match_Hr_Bdup5"); //上半波胆平
            $bdz    =   array("Match_Bd10","Match_Bd20","Match_Bd21","Match_Bd30","Match_Bd31","Match_Bd32","Match_Bd40","Match_Bd41","Match_Bd42","Match_Bd43"); //波胆主
            $bdk    =   array("Match_Bdg10","Match_Bdg20","Match_Bdg21","Match_Bdg30","Match_Bdg31","Match_Bdg32","Match_Bdg40","Match_Bdg41","Match_Bdg42","Match_Bdg43"); //波胆客
            $bdp    =   array("Match_Bd00","Match_Bd11","Match_Bd22","Match_Bd33","Match_Bd44","Match_Bdup5"); //波胆平
            $rqs    =   array("Match_Total01Pl","Match_Total23Pl","Match_Total46Pl","Match_Total7upPl"); //入球数
            $bqc    =   array("Match_BqMM","Match_BqMH","Match_BqMG","Match_BqHM","Match_BqHH","Match_BqHG","Match_BqGM","Match_BqGH","Match_BqGG"); //半全场

            if(in_array($column,$qcrq) || in_array($column,$bcrq)){ //让球
                if(in_array($column,$qcrq))     $info   .=  "让球-";
                else    $info   .=  "上半场让球-";

                if($match_showtype ==   "H")    $info   .=  "主让$match_rgg-";
                else    $info   .=  "客让$match_rgg-";

                if($column == "Match_Ho" || $column == "Match_BHo") $info .= $dm[0];
                else    $info   .=  $dm[1];

            }elseif(in_array($column,$qcdx) || in_array($column,$bcdx)){ //大小
                if(in_array($column,$qcdx)){
                    $info       .=  "大小-";
                    if($column  ==  "Match_DxDpl")  $info   .=  "O";
                    else $info  .=  "U";
                }else{
                    $info       .=  "上半场大小-";
                    if($column  ==  "Match_Bdpl")   $info   .=  "O";
                    else $info  .=  "U";
                }
                $info           .=  $match_dxgg;
            }elseif(in_array($column,$qcdy) || in_array($column,$bcdy)){ //独赢
                if(in_array($column,$qcdy))         $info   .=  "标准盘-";
                else    $info   .=  "上半场标准盘-";

                if(     $column == "Match_BzM" || $column == "Match_Bmdy") $info    .=  $dm[0]."-独赢";
                elseif( $column == "Match_BzG" || $column == "Match_Bgdy") $info    .=  $dm[1]."-独赢";
                else    $info   .=  "和局";
            }elseif(in_array($column,$ds)){ //单双
                $info           .=  "单双-";
                if($column      == "Match_DsDpl")   $info .= "单";
                else    $info   .=  "双";
            }elseif(in_array($column,$sbbdz) || in_array($column,$sbbdk) || in_array($column,$sbbdp) || in_array($column,$bdz) || in_array($column,$bdk) || in_array($column,$bdp)){ //波胆
                if(in_array($column,$sbbdz) || in_array($column,$sbbdk) || in_array($column,$sbbdp)) $info  .=  "上半波胆-";
                else    $info   .=  "波胆-";

                if(strrpos($column,"up5")){
                    $info       .=  "UP5";
                }else{
                    $z           =  substr($column,-2,1);
                    $k           =  substr($column,-1,1);
                    if(in_array($column,$sbbdz) || in_array($column,$bdz))  $info   .=  $z.":".$k;
                    else $info  .=  $k.":".$z;
                }
            }elseif(in_array($column,$rqs)){ //入球数
                $info           .=  "入球数-";
                if(strrpos($column,"7up")){
                    $info       .=  "7UP";
                }else{
                    $info       .=  substr($column,-4,1)."~".substr($column,-3,1);
                }
            }elseif(in_array($column,$bqc)){ //半全场
                $info           .=  "半全场-";
                $n1              = "".substr($column,-2,1);
                $n2              = "".substr($column,-1,1);
                $n1              = ($n1 === "H" ? "和" : ($n1 === "M" ? "主" : "客"));
                $n2              = ($n2 === "H" ? "和" : ($n2 === "M" ? "主" : "客"));
                $info           .=  $n1."/".$n2;
            }
            if($ball_sort       ==  "足球滚球" || $ball_sort=='FTP'){
                $info           .=  "(".$match_nowscore.")";
            }
            $info               .=  "@".$bet_point;

        }elseif(strrpos($ball_sort,"篮球") === 0){
            if(in_array($column,$qcrq)){
                $info           .=  "让分-";
                if($match_showtype ==   "H") $info  .=  "主让$match_rgg-";
                else    $info   .=  "客让$match_rgg-";

                if($column      == "Match_Ho")$info .= $dm[0];
                else    $info   .=  $dm[1];

            }elseif(in_array($column,$qcdx)){
                $info           .=  "大小-";
                if($column      ==  "Match_DxDpl")$info .=  "O$match_dxgg";
                else $info      .=  "U$match_dxgg";

            }elseif(in_array($column,$ds)){ //单双
                $info           .=  "单双-";
                if($column      == "Match_DsDpl")   $info .= "单";
                else    $info   .=  "双";
            }
            $info               .=  "@".$bet_point;
        }elseif(strrpos($ball_sort,"棒球") === 0 || strrpos($ball_sort,"网球") === 0 || strrpos($ball_sort,"排球") === 0){
            $qcdy   =   array("Match_BzM","Match_BzG","Match_BzH"); //全场独赢
            if(in_array($column,$qcrq)){
                $info           .=  "让球-";
                if($match_showtype ==   "H") $info  .=  "主让$match_rgg-";
                else    $info   .=  "客让$match_rgg-";

                if($column      == "Match_Ho")$info .= $dm[0];
                else    $info   .=  $dm[1];

            }elseif(in_array($column,$qcdx)){
                $info           .=  "大小-";
                if($column      ==  "Match_DxDpl")$info .=  "O$match_dxgg";
                else $info      .=  "U$match_dxgg";

            }elseif(in_array($column,$ds)){ //单双
                $info           .=  "单双-";
                if($column      == "Match_DsDpl")   $info .= "单";
                else    $info   .=  "双";
            }elseif(in_array($column,$qcdy)){ //独赢
                $info           .=  "标准盘-";

                if(     $column == "Match_BzM") $info   .=  $dm[0]."-独赢";
                elseif( $column == "Match_BzG") $info   .=  $dm[1]."-独赢";
            }
            $info               .=  "@".$bet_point;
        }elseif(strrpos($ball_sort,"金融") === 0 || strrpos($ball_sort,"冠军") === 0){
            global $mysqli;
            $query  =   $mysqli->query("SELECT team_name FROM t_guanjun_team where tid=$tid limit 1");

            $row    =   $query->fetch_array();//print_r($row);exit;
            if(strrpos($ball_sort,"金融") === 0) $row['team_name']=strtolower(str_replace(" ",'',$row['team_name']));
            $info   =   $row['team_name'].'@'.$bet_point;
        }

        return $info;
    }
    /**
     * 轉換賠率
     * @param odd_f
     * @param H_ratio
     * @param C_ratio
     * @param showior
     * @return
     */

    public function chg_ior($odd_f,$iorH,$iorC,$showior){

        $iorH = floor(($iorH*1000)+0.001) / 1000;

        $iorC = floor(($iorC*1000)+0.001) / 1000;

        $ior=Array();
        if($iorH < 11) $iorH *=1000;
        if($iorC < 11) $iorC *=1000;

        //iorH=parseFloat(iorH);
        //iorC=parseFloat(iorC);
        switch($odd_f){
            case "H":   //香港變盤(輸水盤)
                $ior = $this->get_HK_ior($iorH,$iorC);
                break;
            case "M":   //馬來盤
                $ior = $this->get_MA_ior($iorH,$iorC);
                break;
            case "I" :  //印尼盤
                $ior = $this->get_IND_ior($iorH,$iorC);
                break;
            case "E":   //歐洲盤
                $ior = $this->get_EU_ior($iorH,$iorC);
                break;
            default:    //香港盤
                $ior[0]=$iorH ;
                $ior[1]=$iorC ;
        }
        $ior[0]/=1000;
        $ior[1]/=1000;

        $ior[0]=$this->double_format($ior[0]);
        $ior[1]=$this->double_format($ior[1]);
        //alert("odd_f="+odd_f+",iorH="+iorH+",iorC="+iorC+",ouH="+ior[0]+",ouC="+ior[1]);
        return $ior;
    }
    /*
    去正負號做小數第幾位捨去
    進來的值是小數值
    */
    public function double_format($double_num){
        return $double_num>0 ? sprintf("%.2f",$double_num) : $double_num<0 ? sprintf("%.2f",$double_num) : 0;
    }

    public function Decimal_point($tmpior,$show){
        $sign="";
        $sign =(($tmpior < 0)?"Y":"N");
        $tmpior = (floor(abs($tmpior) * $show + 1 / $show )) / $show;
        return ($tmpior * (($sign =="Y")? -1:1)) ;
    }
    public function get_EU_ior($H_ratio, $C_ratio){
        $out_ior= Array();
        $out_ior=$this->get_HK_ior($H_ratio,$C_ratio);
        $H_ratio=$out_ior[0];
        $C_ratio=$out_ior[1];
        if($H_ratio==0 ) $out_ior[0]=0;
        else $out_ior[0]=$H_ratio+1000;
        if($C_ratio==0 ) $out_ior[1]=0;
        else $out_ior[1]=$C_ratio+1000;
        return $out_ior;
    }
    /**
     * 換算成印尼盤賠率
     * @param H_ratio
     * @param C_ratio
     * @return
     */
    public function get_IND_ior( $H_ratio, $C_ratio){
        $out_ior= Array();

        $out_ior = $this->get_HK_ior($H_ratio,$C_ratio);

        $H_ratio =$out_ior[0];
        $C_ratio =$out_ior[1];
        $H_ratio = $H_ratio/1000;
        $C_ratio = $C_ratio/1000;

        if($H_ratio < 1 && $H_ratio!=0){
            $H_ratio=(-1) / $H_ratio;

        }
        if($C_ratio < 1 && $C_ratio!=0){
            $C_ratio=(-1) / $C_ratio;
        }
        $out_ior[0]=$H_ratio*1000;
        $out_ior[1]=$C_ratio*1000;
        //echo $H_ratio.$C_ratio;exit;
        return $out_ior;
    }

    /**
     * 換算成輸水盤賠率
     * @param H_ratio
     * @param C_ratio
     * @return
     */

    public function get_HK_ior( $H_ratio, $C_ratio){
        $out_ior= Array();
        $line=$lowRatio=$nowRatio=$highRatio=null;
        $nowType="";
        if ($H_ratio <= 1000 && $C_ratio <= 1000){
            $out_ior[0]=$H_ratio;
            $out_ior[1]=$C_ratio;
            return $out_ior;
        }
        $line=2000 - ( $H_ratio + $C_ratio );

        if ($H_ratio > $C_ratio){
            $lowRatio=$C_ratio;
            $nowType = "C";
        }else{
            $lowRatio = $H_ratio;
            $nowType = "H";
        }
        if (((2000 - $line) - $lowRatio) > 1000){
            //對盤馬來盤
            $nowRatio = ($lowRatio + $line) * (-1);
        }else{
            //對盤香港盤
            $nowRatio=(2000 - $line) - $lowRatio;
        }

        if ($nowRatio < 0){
            $highRatio = floor(abs(1000 / $nowRatio) * 1000) ;
        }else{
            $highRatio = (2000 - $line - $nowRatio) ;
        }
        if ($nowType == "H"){
            $out_ior[0]=$lowRatio;
            $out_ior[1]=$highRatio;
        }else{
            $out_ior[0]=$highRatio;
            $out_ior[1]=$lowRatio;
        }
        return $out_ior;
    }
    /**
     * 換算成馬來盤賠率
     * @param $H_ratio
     * @param $C_ratio
     * @return
     */
    public function get_MA_ior( $H_ratio, $C_ratio){
        $out_ior= Array();
        $line=$lowRatio=$highRatio=null;
        $nowType="";
        if (($H_ratio <= 1000 && $C_ratio <= 1000)){
            $out_ior[0]=$H_ratio;
            $out_ior[1]=$C_ratio;
            return $out_ior;
        }
        $line=2000 - ( $H_ratio + $C_ratio );
        if ($H_ratio > $C_ratio){
            $lowRatio = $C_ratio;
            $nowType = "C";
        }else{
            $lowRatio = $H_ratio;
            $nowType = "H";
        }
        $highRatio = ($lowRatio + $line) * (-1);
        if ($nowType == "H"){
            $out_ior[0]=$lowRatio;
            $out_ior[1]=$highRatio;
        }else{
            $out_ior[0]=$highRatio;
            $out_ior[1]=$lowRatio;
        }
        return $out_ior;
    }

    public function checkxe($set,$ball_sort,$tzx){
        if($ball_sort=='FTP') {
            $ball_sort='FT';
            $tzx='gq_'.$tzx;
        }elseif($ball_sort=='BKP') {
            $ball_sort='BK';
            $tzx='gq_'.$tzx;
        }

        $data =array();


        if(!$set){
            $data[0]=$set[strtolower($ball_sort)][$tzx]['single_field_max']=0; //单场
            $data[1]=$set[strtolower($ball_sort)][$tzx]['single_note_max']=0;//单注
            $data[2]=$set[strtolower($ball_sort)][$tzx]['min']=0;// 最小
        }else{
            $data[0]=$set[strtolower($ball_sort)][$tzx]['single_field_max']; //单场
            $data[1]=$set[strtolower($ball_sort)][$tzx]['single_note_max'];//单注
            $data[2]=$set[strtolower($ball_sort)][$tzx]['min'];// 最小
        }
        //print_r($data);
        if(!$data[0] || !$data[1]){//未定义
            $data[0]=$set[strtolower($ball_sort)][$tzx]['single_field_max']=100000; //单场
            $data[1]=$set[strtolower($ball_sort)][$tzx]['single_note_max']=100000;//单注
            $data[2]=$set[strtolower($ball_sort)][$tzx]['min']=1;// 最小
        }

        return ($data);
    }
    public function SMM($SMM,$rows,$sport_type,$sprotsorderpktype){

        $check=['QCRQ','SBRQ','QCDX','SBDX','QCDS','QCDY','SBDY'];
        foreach($check as $k=>$v){
            $REDISKEY="SMManage_".SITEID."_".$sport_type."_".$rows['Match_ID'].'_'.$v;
            $sport_type_value=null;
            if(in_array($REDISKEY,$SMM)) {
                foreach($sprotsorderpktype as $key=>$value){
                    if($key==$v){
                        $sport_type_value=$value;
                        break;
                    }
                }
                if(is_array($sport_type_value) && $sport_type_value){
                    foreach($sport_type_value as $kk=> $vv){
                        $rows[$kk]=0;
                        $rows['SMM'][]=$kk;
                    }
                }

            }

        }
        $NUMS=count($rows['SMM']);
        // ECHO $NUMS.'-';
        if(($NUMS>=8 && $sport_type=='FT') || ($NUMS>=6 && $sport_type=='BK')) {
            //echo count($rows['SMM']).'|';
            $docroot=$_SERVER['DOCUMENT_ROOT'];
            if(strpos($docroot,'A_admin/P_admin')===false) $rows=null;
        }
        return $rows;
    }
}

