<?php
date_default_timezone_set("Singapore");
// if (!isset($_SESSION))
// {
//     session_start();
// }


class Core_Controller extends CI_Controller
{

    function __construct() {
        parent::__construct();
        $this->load->library("session");
        $this->load->database();
        $this->load->helper('url');
        
    }

    function index() {

    }
    function getSessionData(){
        $userData = $this->session->userdata(AP_SESSION); // Get user data stored in session
        if ($userData) {
            return $userData;
        } else {
            return "";
        }
    }
    /**
     * Check user privilege, if they already logged in, redirect them to appropriate controller, otherwise, redirect them
     * to Dashboard login page.
     */
    function checkPrivilege()
    {
        $userData = $this->session->userdata(AP_SESSION); // Get user data stored in session
        if ($userData) {
            // Session still alive
        } else {
            redirect('login');
        }
    }
    function getLevelList(){
        $this->checkPrivilege();
        $this->load->model("portal_analytics");
        $levels = $this->portal_analytics->getLevelList();
        return $levels;
    }

    function getModuleList(){
        $this->checkPrivilege();
        $this->load->model("portal_analytics");
        $modules = $this->portal_analytics->getNavModuleList();
        return $modules;
    }

    function getSectionList(){
        $this->checkPrivilege();
        $lvs = $this->input->post('levels');

        $levels = $this->getLevelList();
        $sections = array();

        for($i = 0; $i < count($lvs); $i++) {
            foreach ($levels as $level) {
                if($level->value == $lvs[$i]){
                    // $sects = $level->sections;
                    // for($i = 0; $i< count($sects); $i++){
                        // $section_name = $level->prefix . $sects[$i];
                        array_push($sections, $level);
                    // }
                }
            }
        }
        echo json_encode($sections);
    }
}

