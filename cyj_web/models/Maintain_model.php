<?php if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

//维护处理
class Maintain_model extends MY_Model {

	function __construct() {
		parent::__construct();
	}
	
	//模块维护状态读取  ps:  fc=彩票，sp=体育，vd=视讯，dz=电子游戏
	public function maintain_state($toltype='fc',$siteid,$indexid,$type=''){
		$main = $this->maintain_check($toltype,$siteid,$indexid);
		if($toltype == 'fc'){
			$data['module'] = in_array($type, json_decode($main['module'],true));
			if($data['module']){
				$data['is_maintain'] = 1;
				$data['content'] = $main['content'];
			}
		}elseif($toltype == 'sp'){
			$data['is_maintain'] = $main['is_maintain'];
			$data['content'] = $main['content'];
		}elseif($toltype == 'vd'){
			$main['module'] = json_decode($main['module'],true);
			$data = $main;
		}elseif ($toltype == 'dz') {
            $main['module'] = json_decode($main['module'],true);
            $data = $main;
        }
		
		return $data;
	}
	
   //维护判断
    function maintain_check($type,$site_id,$index_id){
        $log = $redis = RedisConPool::getInstace();
        $is_maintain = 0;
        $www = $_SERVER['HTTP_HOST'];
        if ($log) {
            //维护KEY
            $key = array('maintain_'.$type.'_all_site_ids',
                         'maintain_'.$type.'_one_site_ids_'.$site_id.$index_id
                         );
            $maintain_data = $redis->mget($key); 

            //单站维护
            if ($maintain_data[1]) {
                $maintain_sites = json_decode($maintain_data[1],true);
                $content = $maintain_sites['content'];
                $online_service = $maintain_sites['online_service'];
                $module = $maintain_sites['module'];
                $is_maintain = 1;
            }
            //全网维护
            if ($maintain_data[0]) {
                $maintain_sites = json_decode($maintain_data[0],true);
                foreach ($maintain_sites as $key => $val) {
                    if ($index_id) {
                        if ($val['site'] == $site_id && $val['index'] == $index_id) {
                            $content = $val['content'];
                            $online_service = $val['online_service'];
                            $module = $val['module'];
                            $is_maintain = 1;
                            break;
                        }
                    }else{
                        if ($val['site'] == $site_id) {
                            $content = $val['content'];
                            $is_maintain = 1;
                            break;
                        }
                    }
                    
                }
            }

            //模块维护返回对应数组
            if($type == 'fc' || $type == 'vd' || $type == 'dz' || $type == 'sp'){
            	//$module = json_decode($module,true);
            	$mod_array['module'] = $module;
            	$mod_array['is_maintain'] = $is_maintain;
            	$mod_array['content'] = $content;
            	
            	return $mod_array;
            }
        }else{
            //$is_maintain = 1;
        }

        if (empty($content)) {
            $content = '全网维护|系统正在进行全面维护中|【 当前 -- 24:00】';
        }

        //维护模板页面输出
        if ($is_maintain) {
            if ($_SERVER['REQUEST_URI'] != '/') {
                echo "<script>top.parent.location.href='http://".$_SERVER['HTTP_HOST']."';</script>";
            }
            $www = 'http://'.$www;
            $content = explode('|', $content);
            $this->maintain_html($www,$content,$online_service);
        }
    }

    //维护页面模板
    function maintain_html($www,$content = '',$online_service = './'){
        if($content[2]){
            $show = $content[2];
        }else{
            $show = "我们将尽快完成维护，如有疑问请联系在线客服！";
        }
$html = <<<EOF
<!DOCTYPE html><html><head><meta charset="UTF-8"><meta http-equiv="X-UA-Compatible" content="IE=Edge，chrome=1"><meta name="screen-orientation" content="portrait"><meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no" /><title>$content[0]</title>
       <!--[if lt IE 9]>
         <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
         <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
      <![endif]-->
      <style>
        html{height: 100%;}
        body{height: 100%;}
body{color: #666;background: #f1f1f1; margin: 0px; padding: 0px;}
body{
    position: relative;
    background: -webkit-gradient(linear, 0 0, 0 100%, from(#a1a1a1), to(#212121));
    /** Chrome Safari **/
    background: -moz-linear-gradient(top, #a1a1a1, #212121);
    /** FireFox **/
    background: -o-linear-gradient(top, #a1a1a1, #212121);
    /** Opear **/
    background: -ms-linear-gradient(#a1a1a1 0%, #212121 100%);
    /** IE9 IE10 **/
    filter: progid: DXImageTransform.Microsoft.gradient(startColorstr='#a1a1a1 ', endColorstr='#212121', grandientType=1);
    /** IE7 **/
    -ms-filter: progid: DXImageTransform.Microsoft.gradient(startColorstr='#a1a1a1 ', endColorstr='#212121', grandientType=1);
    /** IE8 **/
}
.wei-mainImg{
    width: 700px;
    height: 80%;
    position: absolute;
    left: 50%;
    top: 50%;
    margin-top: -20%;
    margin-left: -350px;
    background-image: url($www/public/timages/weihu.png);
    background-repeat: no-repeat;
    background-position: center;
    background-size:contain;
}
.wei-main{
    position: relative;
    z-index: 5;
    margin-top: 150px;
}
.wei-main h2{
    text-align: center;
    font: 32px/60px "微软雅黑";
    color: #212121;
    font-weight:bold;
    margin:0;
}
.wei-main p{
    text-align: center;
    font: 16px/35px "微软雅黑";
    color: #212121;
    margin: 0;
    font-weight:bold;
}
.wei-bottomLink{
    font: 14px/35px arial !important;
    text-align: center;
    color: rgba(255,255,255,0.75) !important;
    margin-bottom: 10px !important;
    position: absolute;
    bottom: 10px;
    width: 100%;
}
.sp-red{color: #E32222;}
.margin-left{margin-left: 20px;}
.wh-btn{ text-decoration: none; display: inline-block; color: #fff; background-color: #4F4F4F; padding: 1px 10px; border-radius: 4px; line-height: 25px; height: 25px; margin-top: 10px; box-shadow: 2px 2px 5px #000000;}
@media screen and (max-width: 1000px) {
    body{background-color: #222222; font-size: 15px; width: 100%; height: 100%; background-repeat: no-repeat;}
    .wei-bg{width: 100%; height: 100%;}
    .wei-mainImg{ width: 96%; left: 2%; margin-top:-35%; margin-left: 0px; background-position:top;}
    .wei-bg2{width: 100%; height:50%; left: 0px; margin-left: 0px; margin-top: 150px; overflow: hidden;}
    .wei-main{width: 100%;}
    .btn{font-size: 0.85rem;}
@media only screen and (min-width: 320px) {
    .wei-main{width: 100%; margin-top: 70px;}
    .wei-main h2{ font-size:1.2rem; line-height:3rem;}
    .wei-main p{font-size: 0.5rem; line-height:1.5rem;}
    .wh-btn{font-size: 0.4rem; line-height: 25px; height: 25px;}
    .wei-mainImg{margin-top: -50%;}
}
@media only screen and (min-width: 360px) {
    .wei-main{width: 100%; margin-top: 90px;}
    .wei-main h2{ font-size:1.4rem; line-height:3.5rem;}
    .wei-main p{font-size: 0.8rem; line-height: 1.6rem;}
    .wh-btn{font-size: 0.6rem; line-height: 25px; height: 25px;}
    .wei-mainImg{margin-top: -50%;}
}
@media not screen and (orientation:portrait){
    body{min-height: 700px;}
 }
@media only screen and (min-width: 400px) {
    .wei-main{margin-top: 110px;}
    .wei-mainImg{margin-top: -50%;}
  }
@media only screen and (min-width:600px){
     .wei-main{margin-top: 160px;}
    }
}
@media (min-width:0px){
}
@media (min-width:700px){
     .wei-main{margin-top: 160px;}
    }
}
@media (min-width:800px){
}
@media (min-width:1000px){
}
@media (min-width:1100px){
}
@media (min-width:1260px) {
}
@media (min-width:1300px) {
    .wei-main{
        margin-top: 300px;
    }
}
@media (min-width:1400px) {
}
@media (min-width:1500px) {
}
@media (min-width:1600px) {
    .wei-main{
        margin-top: 375px;
    }
}
</style></head><body>
<div class="wei-mainImg block-center">
<div class="wei-main block-center"><h2>$content[0]</h2><p>$content[1]</p>
<p>维护时间：<span class="sp-red">$show</span>
</p><p><a href="./" class="wh-btn" style="display:none">首页</a><a target="_blank" href="$online_service" class="wh-btn margin-left">联系在线客服</a>
</p></div></div><div class="wei-bottomLink wei-mt100"></div></body></html>
EOF;
    exit($html);
    }

}