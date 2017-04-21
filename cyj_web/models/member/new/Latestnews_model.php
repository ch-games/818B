<?php 
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Latestnews_model extends MY_Model {
    function __construct() {
        parent::__construct();
        $this->load->model('Common_model');
        $this->init_db();
    }

}