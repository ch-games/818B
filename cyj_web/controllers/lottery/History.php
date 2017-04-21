<?php
defined('BASEPATH') or exit('No direct script access allowed');

class History extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('lottery/lottery_model', 'lottery');
        $config_css = $this->config->item('css');
        $this->add('config_css', $config_css);
    }

    public function index()
    {
        $lotteryId = $this->input->get('lotteryId');
        $date = $this->input->get('date')?$this->input->get('date'):date('Y-m-d');
        $map['table'] = $lotteryId . "_auto";
        if ($lotteryId == 'liuhecai' || $lotteryId == 'fc_3d' || $lotteryId == 'pl_3') {
            $map['limit'] = 20;
        } else {
            if (! empty($date)) {
                $map['where'] = "date_format(datetime,'%Y-%m-%d') ='" . $date . "'";
            }
        }
        $map['order'] = "qishu desc";
        $data = $this->lottery->get_auto($map);
        $data = func_get_arr_row_all($data, $lotteryId);
        $this->add('data', $data);
        
        $this->load->model('lottery/Fc_com_model');
        $fc_games = $this->Fc_com_model->fc_titles('');
        
        $fc_games = $this->Fc_com_model->sort_fc_play($fc_games);
        
        $this->add('fc_games', $fc_games);
        $this->display("lottery/history/history_" . $lotteryId . '.html');
    }

    function zh_type($v)
    {
        switch ($v) {
            case 'gd_ten':
                return 1;
                break;
            case 'cq_ssc':
                return 2;
                break;
            case 'bj_10':
                return 3;
                break;
            case 'cq_ten':
                return 4;
                break;
            case 'fc_3d':
                return 5;
                break;
            case 'pl_3':
                return 6;
                break;
            case 'liuhecai':
                return 7;
                break;
            case 'bj_8':
                return 8;
                break;
            case 'tj_ssc':
                return 10;
                break;
            case 'jx_ssc':
                return 11;
                break;
            case 'xj_ssc':
                return 12;
                break;
            case 'js_k3':
                return 13;
                break;
            case 'jl_k3':
                return 14;
                break;
        }
    }
}