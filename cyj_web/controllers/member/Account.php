<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Account extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('member/Account_model');
        $this->Account_model->login_check($_SESSION['uid']);
        $this->load->model('Common_model');
    }

    //我的賬戶
    public function userinfo()
    {
        $redis = RedisConPool::getInstace();
        $redis_key = SITEID . '_userinfo_' . $_SESSION['uid'];
        $userinfo = $redis->get($redis_key);
        if ($userinfo) {
            $userinfo = json_decode($userinfo, TRUE);
        } else {
            $userinfo = $this->Common_model->get_user_info($_SESSION['uid']);
            $redis->setex($redis_key, '5', json_encode($userinfo));
        }

        //$userinfo = $this->Common_model->get_user_info($_SESSION['uid']);
        //$copyright = $this->Common_model->get_copyright();
        //$video_config = explode(',',$copyright['video_module']);
        $video_config = $this->Common_model->get_video_dz_config();
        //余額總計計算
        $allmoney = $userinfo['money'];
        foreach ($video_config as $key => $val) {
            if ($val != 'eg') {
                $allmoney += $userinfo[$val . '_money'];
            } else {
                unset($video_config[$key]);
            }
        }

        $video_config1 = array_slice($video_config, 0, 3);
        $video_config2 = array_slice($video_config, 3, 4);
        $video_config3 = array_slice($video_config, 7, 4);

        $this->add('video_config1', $video_config1);
        $this->add('video_config2', $video_config2);
        $this->add('video_config3', $video_config3);

        //判斷是否開啟自助返水
        $is_self = $this->Account_model->is_self_user();
        $this->add('is_self', $is_self['is_self_fd']);

        //$cash_data = $this->Account_model->get_cash_limit();
        //最近10記錄redis緩存
        $redis_key = SITEID . '_cash_limit_' . $_SESSION['uid'];
        $cash_data = $redis->get($redis_key);
        if ($cash_data) {
            $cash_data = json_decode($cash_data, TRUE);
        } else {
            $cash_data = $this->Account_model->get_cash_limit();
            $redis->setex($redis_key, '5', json_encode($cash_data));
        }

        $this->add('cash_data', $cash_data);//最近十筆記錄
        $this->add('allmoney', $allmoney);//余額總計
        $this->add('userinfo', $userinfo);//用戶信息
        $this->add('video_config', $video_config);//站點視訊模塊
        $this->display('member/userinfo.html');
    }

    //體育投註咨詢
    public function advisory()
    {
        //判斷是否開啟自助返水
        $is_self = $this->Account_model->is_self_user();
        $this->add('is_self', $is_self['is_self_fd']);

        $spTitle = array('ft' => '足球', 'bk' => '籃球', 'vb' => '排球', 'bs' => '棒球', 'tn' => '網球');
        $spArr = array('ft', 'bk', 'vb', 'bs', 'tn');
        //$spType = $this->Account_model->get_sp_advisory($spArr);

        $redis = RedisConPool::getInstace();
        $redis_key = SITEID . '_sp_advisory_' . $_SESSION['uid'];
        $spType = $redis->get($redis_key);
        if ($spType) {
            $spType = json_decode($spType, TRUE);
        } else {
            $spType = $this->Account_model->get_sp_advisory($spArr);
            $redis->setex($redis_key, '5', json_encode($spType));
        }

        $this->add('spTitle', $spTitle);
        $this->add('spType', $spType);
        $this->display('member/advisory.html');
    }

    //彩票投註咨詢
    public function lottery_advisory()
    {
        //判斷是否開啟自助返水
        $is_self = $this->Account_model->is_self_user();
        $this->add('is_self', $is_self['is_self_fd']);
        $this->load->model('lottery/fc_com_model');
        //標題
        $fcTitle = array();

        $uid = $_SESSION['uid'];
        $aid = $_SESSION['agent_id'];
        $site_id = SITEID;
        //玩法容器
        $plays = [];
        ///獲取 type  =＞　name   彩種集合   渲染模板導航
        $games = $this->fc_com_model->fc_titles('');
        foreach ($games as $v) {
            $fcTitle[$v['type']] = $v['name'];
        }
        ///獲取代理限額
        $fcType = $this->fc_com_model->get_limit_agent($fcTitle, $aid, $site_id, $plays);
        ///獲取用戶個人限額
        $self_set = $this->fc_com_model->get_limit_user($uid);
        foreach ($fcType as $key => &$val) {
            foreach ($val as $k => &$v) {
                if (isset($self_set[$v['type_id']]))                 ///如果有用戶限額就覆蓋前者
                    $v = $self_set[$v['type_id']];

                $v += $plays[$v['type_id']];                    ///填充數據title
            }
        }
        $this->add('fcTitle', $fcTitle);
        $this->add('fcType', $fcType);
        $this->display('member/lottery_advisory.html');
    }

    //加載修改密碼頁面
    public function password()
    {
        $userinfo = $this->Common_model->get_user_info($_SESSION['uid']);
        if ($userinfo['change_pwd'] == 0) {
            message('系統管理員已禁止您自行修改登錄密碼。\n 請聯系系統管理員！');
        }

        $pwdtype = $this->input->get('pwdtype');
        $pwdtype = !empty($pwdtype) ? $pwdtype : 'login';

        //讀取視訊配置
        $conf['site_id'] = SITEID;
        $conf['index_id'] = INDEX_ID;
        $video_config = $this->db->from('web_config')->where($conf)->select('video_module')->get()->row_array();
        $video_config = explode(',', $video_config['video_module']);
        $video = array('mg', 'pt', 'bbin');
        $edvideo = array_intersect($video, $video_config);
        if (in_array($pwdtype, $edvideo)) {
            $video_user = $this->Account_model->get_video_username($pwdtype);
            $this->add('video_user', $video_user);
        }

        $this->add('edvideo', $edvideo);
        $this->add('pwdtype', $pwdtype);
        $this->display('member/edit_pwd.html');
    }

    //修改網站登錄密碼
    public function userpwd()
    {
        $oldpass = $this->input->post('oldpass');
        $newpass2 = $this->input->post('newpass2');
        if ($oldpass && $newpass2 && $oldpass != $newpass2) {
            //表單驗證
            $this->load->library('form_validation');
            $this->form_validation->set_rules('oldpass', 'oldpass', 'required|min_length[6]|max_length[12]');
            $this->form_validation->set_rules('newpass2', 'newpass2', 'required|min_length[6]|max_length[12]');

            if ($this->form_validation->run() == FALSE) {
                message('登錄密碼格式錯誤!');
            }

            $set['password'] = md5(md5($newpass2));
            $result = $this->Account_model->edit_password($set);
            if ($result) {
                echo "<script>alert('登陸密碼修改成功');</script>";
                session_destroy();
                echo "<script>top.location.href='/';</script>";
            } else {
                echo "<script>alert('登陸密碼修改失敗');</script>";
            }
        } else {
            message("原取款密碼不能與修改後的取款密碼壹致!");
        }
    }

    //修改取款密碼
    public function moneypwd()
    {
        $oldmoneypass = $this->input->post('oldmoneypass');
        $newmoneypass2 = $this->input->post('newmoneypass2');
        if ($oldmoneypass && $newmoneypass2 && $oldmoneypass != $newmoneypass2) {
            //表單驗證
            $this->load->library('form_validation');
            $this->form_validation->set_rules('oldmoneypass', 'oldmoneypass', 'required|min_length[4]|max_length[4]');
            $this->form_validation->set_rules('newmoneypass2', 'newmoneypass2', 'required|min_length[4]|max_length[4]');

            if ($this->form_validation->run() == FALSE) {
                message('取款密碼格式錯誤!');
            }

            if ($_SESSION['shiwan'] == 1) {
                exit("<script language=javascript>alert('試玩賬號沒有取款密碼，請使用正式賬號！');history.go(-1);</script>");
            }

            $data['qk_pwd'] = $newmoneypass2;
            $result = $this->Common_model->get_user_info($_SESSION['uid']);
            if ($result['qk_pwd'] == $oldmoneypass) {
                if ($oldmoneypass != newmoneypass2) {
                    $set['qk_pwd'] = $newmoneypass2;
                    $row = $this->Account_model->edit_password($set);
                    if ($row) {
                        message("取款密碼修改成功!");
                    } else {
                        message("取款密碼修改失敗!");
                    }
                } else {
                    message("原取款密碼不能與修改後的取款密碼壹致!");
                }
            } else {
                message("原取款密碼錯誤!");
            }
        } else if ($oldmoneypass == $newmoneypass2) {
            message("新密碼與原密碼壹樣，請重新輸入!");
        }
    }

    //修改mg登錄密碼
    public function mgpwd()
    {
        if ($_SESSION['shiwan'] == 1) {
            message("試玩賬號沒有MG密碼，請使用正式賬號！");
        }
        $omgpass = $this->input->post('omgpass');
        $newmgpass2 = $this->input->post('newmgpass2');
        if ($omgpass && $newmgpass2) {
            //表單驗證
            $this->load->library('form_validation');
            $this->form_validation->set_rules('omgpass', 'omgpass', 'required|min_length[6]|max_length[12]');
            $this->form_validation->set_rules('newmgpass2', 'newmgpass2', 'required|min_length[6]|max_length[12]');
            if ($this->form_validation->run() == FALSE) {
                message('登錄密碼格式錯誤!');
            }

            $this->load->library('Games');
            $games = new Games();
            $password = md5(md5($omgpass));
            $result = $this->Common_model->get_user_info($_SESSION['uid']);
            if ($result['password'] == $password) {
                $row = $games->MgEditAccountPwd($result['username'], $newmgpass2);
                $data = json_decode($row);
                if ($data->result) {
                    message("密碼修改成功!");
                } else {
                    message("修改密碼失敗!");
                }
            } else {
                message("原登錄密碼錯誤!");
            }
        }
    }

    //修改PT密碼
    public function ptpwd()
    {
        if ($_SESSION['shiwan'] == 1) {
            message("試玩賬號沒有PT密碼，請使用正式賬號！");
        }
        $optpass = $this->input->post('optpass');
        $newptpass2 = $this->input->post('newptpass2');
        if ($optpass && $newptpass2) {
            //表單驗證
            $this->load->library('form_validation');
            $this->form_validation->set_rules('optpass', 'optpass', 'required|min_length[6]|max_length[12]');
            $this->form_validation->set_rules('newptpass2', 'newptpass2', 'required|min_length[6]|max_length[12]');
            if ($this->form_validation->run() == FALSE) {
                message('登錄密碼格式錯誤!');
            }

            $this->load->library('Games');
            $games = new Games();
            $password = md5(md5($_POST[optpass]));
            $result = $this->Common_model->get_user_info($_SESSION['uid']);

            if ($result['password'] == $password) {
                $row = $games->PtEditAccountPwd($result['username'], $newptpass2);
                $data = json_decode($row);
                if ($data->result) {
                    message("密碼修改成功!");
                } else {
                    message("密碼修改失敗!");
                }
            } else {
                message("原登錄密碼錯誤!");
            }
        }
    }

    //修改BBIN密碼
    public function bbinpwd()
    {
        if ($_SESSION['shiwan'] == 1) {
            message("試玩賬號沒有PBBIN密碼，請使用正式賬號！");
        }
        $obbinpass = $this->input->post('obbinpass');
        $newbbinpass2 = $this->input->post('newbbinpass2');
        if ($obbinpass && $newbbinpass2) {
            //表單驗證
            $this->load->library('form_validation');
            $this->form_validation->set_rules('obbinpass', 'obbinpass', 'required|min_length[6]|max_length[12]');
            $this->form_validation->set_rules('newbbinpass2', 'newbbinpass2', 'required|min_length[6]|max_length[12]');
            if ($this->form_validation->run() == FALSE) {
                message('登錄密碼格式錯誤!');
            }

            $this->load->library('Games');
            $games = new Games();
            $password = md5(md5($_POST[obbinpass]));
            $result = $this->Common_model->get_user_info($_SESSION['uid']);

            if ($result['password'] == $password) {
                $row = $games->BbinEditAccountPwd($result['username'], $newbbinpass2);
                $data = json_decode($row);
                if ($data->result) {
                    message("密碼修改成功!");
                } else {
                    message("密碼修改失敗!");
                }
            } else {
                message("原登錄密碼錯誤!");
            }
        }
    }

    //會員自助返水
    public function user_self_fd()
    {
        $this->load->model('member/User_self_fd_model');
        //獲取視訊配置
        $vconfig = $this->User_self_fd_model->video_config();
        $title = array(
            'sp' => '體育',
            'ag' => 'AG視訊', 'agdz' => 'AG電子', 'agter' => 'AG捕魚',
            'og' => 'OG視訊',
            'mg' => 'MG視訊', 'mgdz' => 'MG電子',
            'ct' => 'CT視訊',
            'pt' => 'PT電子',
            'eg' => 'EG電子',
            'lebo' => 'LEBO視訊', 'dg99' => '818彩票',
            'bbin' => 'BB視訊', 'bbdz' => 'BB電子', 'bbsp' => 'BB體育', 'bbfc' => 'BB彩票',
            'lmg' => 'LMG視訊',
            'gpi' => 'GPI視訊',
            'gd' => 'GD視訊',
            'sa' => 'SA視訊',
            'ab' => 'AB視訊',
            'im' => 'IM體育');

        array_unshift($vconfig, "sp");

        if (in_array('bbin', $vconfig)) {
            array_push($vconfig, 'bbdz');
            array_push($vconfig, 'bbsp');
            array_push($vconfig, 'bbfc');
        }
        if (in_array('mg', $vconfig)) {
            array_push($vconfig, 'mgdz');
        }
        if (in_array('ag', $vconfig)) {
            array_push($vconfig, 'agdz');
            array_push($vconfig, 'agter');
        }

        $odata = $old_data = array();
        //今日累計自助返水
        $old_data = $this->User_self_fd_model->user_self_fd_olddata(date('Ymd'));

        foreach ($old_data as $key => $val) {
            if (false !== strpos($key, 'fd')) {
                $ki = str_replace('_fd', '', $key);
                $odata[$ki] = $val;
            }
        }
//p($vconfig);exit;
        $this->add('odata', $odata);
        $this->add('vconfig', $vconfig);
        $this->add('title', $title);
        $this->display('member/user_self_fd.html');
    }

    //請求返水數據
    public function user_self_fd_data()
    {
        $this->load->model('member/User_self_fd_model');
        //當前即時數據
        $data = $this->User_self_fd_model->user_self_fd_data();
        //前期累計返水數據
        $old_data = $this->User_self_fd_model->user_self_fd_olddata($data['orderIds']);
        // p($data);echo '<br>===<br>';p($old_data);
        $title = array(
            'sp_bet' => '體育',
            'ag_bet' => 'AG視訊', 'agdz_bet' => 'AG電子', 'agter_bet' => 'AG捕魚',
            'og_bet' => 'OG視訊',
            'mg_bet' => 'MG視訊', 'mgdz_bet' => 'MG電子',
            'ct_bet' => 'CT視訊',
            'pt_bet' => 'PT電子',
            'eg_bet' => 'EG電子',
            'lebo_bet' => 'LEBO視訊', 'dg99_bet' => '818彩票',
            'bbin_bet' => 'BB視訊', 'bbdz_bet' => 'BB電子', 'bbsp_bet' => 'BB體育', 'bbfc_bet' => 'BB彩票',
            'lmg_bet' => 'LMG視訊',
            'gpi_bet' => 'GPI視訊',
            'gd_bet' => 'GD視訊',
            'sa_bet' => 'SA視訊',
            'ab_bet' => 'AB視訊',
            'im_bet' => 'IM體育');

        if ($data['total_e_fd'] && ($data['total_e_fd'] - $old_data['total_e_fd']) > 0.01) {

            if ($_SESSION['self_fd_data']) {
                unset($_SESSION['self_fd_data']);
            }

            $_SESSION['self_fd_data'] = $data;
            foreach ($data as $key => $val) {
                if ($key != 'orderIds') {
                    $data[$key] = $val - $old_data[$key];
                }
            }

            $th_html = $td_html = $fd_html = '';
            foreach ($data as $k => $v) {
                if (FALSE !== strpos($k, 'bet')) {
                    if ($title[$k]) {
                        $th_html .= '<th>' . $title[$k] . '</th>';
                        $td_html .= '<td>' . $v . '</td>';
                    }

                } elseif (FALSE !== strpos($k, 'fd') && $k != 'total_e_fd') {
                    $fd_html .= '<td>' . $v . '</td>';
                    $old_fd_html .= '<td>' . $old_data[$k] . '</td>';
                }
            }

            $dhtml = '<tr><th colspan="28" style="background-color: #C5D9F1;">有效打碼</th></tr><tr>' . $th_html . '</tr><tr>' . $td_html . '</tr><tr><th colspan="28" style="background-color: #C5D9F1;">返水額度</th></tr><tr>' . $th_html . '</tr><tr>' . $fd_html . '</tr><tr><th colspan="28" style="background-color: #C5D9F1;">今日累計返水額度【不含當次】</th></tr><tr>' . $th_html . '</tr><tr>' . $old_fd_html . '</tr><tr><td colspan="28" align="center"><input type="button" value="自助返水寫入" id="fdbtndo" onclick="self_fd_data();"></tr>';

            $udata['state'] = 1;
            $udata['msg'] = '可獲返水金額：' . $data['total_e_fd'];
            $udata['data'] = $dhtml;

            exit(json_encode($udata));
        } else {
            $udata['state'] = 0;
            $udata['msg'] = '暫無可獲返水';
            $udata['data'] = 0;

            exit(json_encode($udata));
        }
    }

    //會員自助返水處理
    public function user_self_fd_data_do()
    {
        $this->load->model('member/User_self_fd_model');
        if (empty($_SESSION['self_fd_data']) || empty($_SESSION['self_fd_data']['total_e_fd'])) {
            exit('error');
        }

        //當前即時數據
        $log = $this->User_self_fd_model->user_self_fd_data_do();
        if ($log) {
            $udata['state'] = 1;
            $udata['msg'] = '自助返水操作成功!';
        } else {
            $udata['state'] = 0;
            $udata['msg'] = '自助返水操作失敗!';
        }
        exit(json_encode($udata));
    }
}