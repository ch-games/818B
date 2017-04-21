<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Wh extends MY_Controller {
    public function __construct() {
        parent::__construct();
    }
    public function index(){
        $this->load->model('Index_model');
        $SiteStatus=$this->SiteStatus;
        if($SiteStatus['Siteinfo']){
            foreach ($SiteStatus['Siteinfo'] as $k => $v) {
                if($v['site_id']==SITEID && $v['index_id']==INDEX_ID){
                    $kefu=$v['online_service'];
                    $sitename=$v['copy_right'];
                    $video=explode(',',$v['video_module']);
                    foreach($video as $kk=>$vv){
                        $games[]=[$vv.'_game'];
                    }
                    $siteopen=['sport'=>['sport'],'lottery'=>['lottery'],'video'=>$video,'games'=>$games,'cp'=>explode(',',$v['fc_module'])];
                    break;
                }
            }
        }
        $data_b = $this->Index_model->get_lottery();
        $domain=$_SERVER['HTTP_HOST']; 
        $playurl['ag']=['type'=>1,      'url'=>"http://$domain/index.php/video/login?g_type=ag"];
        $playurl['mg']=['type'=>1,      'url'=>"http://$domain/index.php/video/login?g_type=mg"];
        $playurl['ct']=['type'=>1,      'url'=>"http://$domain/index.php/video/login?g_type=ct"];
        $playurl['lebo']=['type'=>1,    'url'=>"http://$domain/index.php/video/login?g_type=lebo"];
        $playurl['og']=['type'=>1,    'url'=>"http://$domain/index.php/video/login?g_type=og"];
        $playurl['bbin']=['type'=>1,    'url'=>"http://$domain/index.php/video/login?g_type=bbin"];
        $playurl['sport']=['type'=>2,   'url'=>"http://$domain/index.php/index/sports?type=m"];
        $playurl['lottery']=['type'=>2, 'url'=>"http://$domain/index.php/index/lottery"];
        foreach($data_b as $v){
            $playurl[$v] = array('type'=>2,'url'=>"http://$domain/index.php/index/lottery");
        }
        $playurl['mg_game']=['type'=>2, 'url'=>"http://$domain/index.php/index/egame?metype=MG"];
        $playurl['pt']=['type'=>2, 'url'=>"http://$domain/index.php/index/egame?metype=PT"];
        $playurl['im']=['type'=>2, 'url'=>"http://$domain/index.php/index/im_sports?type=m"];
        
        $playurl['ag_game']=['type'=>2, 'url'=>"http://$domain/index.php/index/egame?metype=AG"];
        $playurl['bbin_game']=['type'=>2, 'url'=>"http://$domain/index.php/index/egame?metype=BBIN"];
        $playurl['eg_game']=['type'=>2, 'url'=>"http://$domain/index.php/index/egame?metype=EG"];
        $status=$this->GetSiteStatus($SiteStatus,100,'',1);
       // print_r($status);
        if($status['webhome']==1){
            $this->add('webhome',1);
        }else $this->add('webhome',0);

        $data_c = $this->Index_model->get_livetop();
        foreach($data_c as $k=>$v){
            $data_c[$k] = $v;
            if($v == 'mg'){
                $data_c[] = 'mg_game';
            }
            if($v == 'ag'){
                $data_c[] = 'ag_game';
            }
            if($v == 'bbin'){
                $data_c[] = 'bbin_game';
            }
            if($v == 'eg'){
                $data_c[] = 'eg_game';
            }
            if($v == 'pt'){
                $data_c[] = 'pt_game';
                unset($data_c[$k]);
            }
        }
        $data_c[] = 'webhome';
        $data_c[] = 'webend';
        $data_c[] = 'lottery';
        $data_c[] = 'sport';
        $data = array_merge($data_c,$data_b);
        foreach($SiteStatus['ModuleAll'] as $k=>$v){
            if(!in_array($v['cate_type'], $data)){
                unset($SiteStatus['ModuleAll'][$k]);
            }
        }
        foreach($SiteStatus['ModuleAll'] as $k=>$v){
            $modules_[$k]['cate_type']=$v['cate_type'];
        }
        $this->add('siteopen',json_encode($siteopen));
        $this->add('kefu',$kefu);
        $this->add('playurl',json_encode($playurl));
        $this->add('domain',$domain);
        $this->add('status_json',json_encode($status));
        $this->add('status',($status));
        $this->add('sitename',$sitename);
        $this->add('modules',$SiteStatus['ModuleAll']);
        $this->add('modules_',json_encode($modules_));
        $this->display('wh.html');
    }
    

    
}
