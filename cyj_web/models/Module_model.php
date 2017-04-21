<?php if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

//站点模块配置
class Module_model extends MY_Model {

	function __construct() {
		parent::__construct();
	}

    public  function module_config($site_id,$index_id){
        //获取配置
        $v_config = $vd_module = $video_types = $m_module = array();
        $modules = $this->module_site($site_id,$index_id);

        //视讯配置
        if ($modules && isset($modules['video_module'])) {
            $vd_module = explode(',',$modules['video_module']);
        }
        //彩票体育配置
        if ($modules && isset($modules['module'])) {
            $m_module = explode(',',$modules['module']);
        }
        //视讯电子组合
        if ($vd_module) {
            foreach ($vd_module as $k => $v) {
                $video_types[] = $v;
                if ($v == 'mg') {
                    $video_types[] = 'mgdz';
                }elseif($v == 'bbin'){
                    $video_types[] = 'bbdz';
                    $video_types[] = 'bbsp';
                    $video_types[] = 'bbfc';
                }elseif ($v == 'ag') {
                    $video_types[] = 'agdz';
                    $video_types[] = 'agter';
                }
            }
        }

        //配置合并
        if ($m_module && $video_types) {
            $video_types = array_merge($m_module, $video_types);
        }elseif($module_type){
            $video_types = $module_type;
        }

        if ($video_types) {
            foreach ($video_types as $key => $val) {
                if ($val == 'sp') {
                    $v_config[$key]['vtitle'] = '体育';
                }elseif($val == 'fc'){
                    $v_config[$key]['vtitle'] = '彩票';
                }elseif($val == 'bbsp'){
                    $v_config[$key]['vtitle'] = 'BB体育';
                }elseif($val == 'bbfc'){
                    $v_config[$key]['vtitle'] = 'BB彩票';
                }else{
                    if (strpos($val, 'dz') || $val == 'eg' || $val == 'pt') {
                        $v_config[$key]['vtitle'] =  strtoupper(str_replace('dz', '', $val)).'电子';
                    }elseif($val == 'im'){
                        $v_config[$key]['vtitle'] =  strtoupper($val).'体育';
                    }elseif($val == 'agter'){
                        $v_config[$key]['vtitle'] =  'AG捕鱼';
                    }else{
                        $v_config[$key]['vtitle'] =  strtoupper($val).'视讯';
                    }
                }
                $v_config[$key]['type'] = $val;
                $v_config[$key]['vtype'] = $val.'_discount';
            }
        }

        return $v_config;
    }

    //获取配置一维数组
    public function module_array($site_id,$index_id){
        $m_module = $modules = $video_types = array();
        $modules = $this->module_site($site_id,$index_id);

        if ($modules) {
            //视讯电子模块
            if ($modules['video_module']) {
                $vd_module = explode(',',$modules['video_module']);
                foreach ($vd_module as $k => $v) {
                    $video_types[] = $v;
                    if ($v == 'mg') {
                        $video_types[] = 'mgdz';
                    }elseif($v == 'bbin'){
                        $video_types[] = 'bbdz';
                        $video_types[] = 'bbsp';
                        $video_types[] = 'bbfc';
                    }elseif ($v == 'ag') {
                        $video_types[] = 'agdz';
                        $video_types[] = 'agter';
                    }elseif ($v == 'gd') {
                        $video_types[] = 'gddz';
                    }elseif ($v == 'gpi') {
                        $video_types[] = 'gpidz';
                    }
                }
            }
            //普通模块
            if ($modules['module']) {
                $m_module = explode(',',$modules['module']);
            }
            return array_merge($m_module, $video_types);
        }
        return false;
    }

    //获取站点模块配置
    public function module_site($site_id,$index_id){
        $db_model = array();
        $db_model['tab'] = 'web_config';
        $db_model['type'] = 1;

        $map = array();
        $map['site_id'] = $site_id;
        $map['index_id'] = 'a';

        return $this->M($db_model)->field("module,fc_module,dz_module,video_module")->where($map)->find();
    }
    //获取配置标题
    public function module_title(){
        $db_model = array();
        $db_model['tab'] = 'site_module';
        $db_model['type'] = 4;

        $map = array();
        $map['type'] = array('in','(1,3,4,5)');
        $data = $this->M($db_model)->where($map)->order("id asc")->select();
        //数据格式处理
        if ($data) {
            foreach ($data as $key => $val) {
                $t_data[$val['cate_type']] = $val['name'];
                if ($val['cate_type'] == 'ag') {
                    $t_data['agter'] = 'AG捕鱼';
                }
            }
            return $t_data;
        }
        return false;
    }
}