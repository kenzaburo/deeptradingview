<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include('Core_controller.php');
class Login extends Core_Controller {

    public $ana_schemas = ANALYTICS_SCHEMAS;
    public $user_tbl = USER_TABLE;
	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
    public function __construct(){
        parent::__construct();
        $this->load->helper("url");
        $this->load->library("session");
    }

    /**
     * Check user privilege, if they already logged in, redirect them to appropriate controller
     */
    function checkPrivilege()
    {
        $userData = $this->session->userdata(AP_SESSION); // Get user data stored in session
        if ($userData) {
             redirect('heatmap');
            // }
            // Only admin and experimenter can use this page
            // if ($userData['type'] == 1) {
            //     redirect('experiment_list');
            // } else if ($userData['type'] == 2) {
            //     redirect('experiment_creation');
            // } else  if ($userData['type'] == 3) {
            //     redirect('participant_page');
            // }
        }
    }

	public function index()
	{
	    $this->checkPrivilege();
        $data['pageTitle'] = 'Analytics Portal login';
    	$this->load->view('login/login_view');
	}

	public function signin()
	{
        $this->checkPrivilege();
		$username = $this->input->post('username');        // Get the user name
        $password = $this->input->post('password');
        $this->verifyLogin($username, $password);
	}

    public function change_password(){
        // No need to verify privelege
        $oldPassword = $this->input->post('oldPassword'); 
        $newPassword = $this->input->post('newPassword');
        $confirmPassword = $this->input->post('confirmPassword');
        $userData = $this->session->userdata(AP_SESSION); // Get user data stored in session
        $result['status'] = 2; // Asume that there is a post parameter error
        // echo var_dump($userData);

        if ($userData) {
            $username = $userData['username'];
            $password = $userData['password'];

            if (sha1($oldPassword) != $password) {
                $result['message'] = "The old password is incorrect";
            } else if (strlen($newPassword) < 8) {
                $result['message'] = "Please enter at least 8 characters for the new password";
            } else if ($newPassword != $confirmPassword) {
                $result['message'] = "The password confirmation does not match";
            } else {
                $this->load->model('user_model');
                $user_info = $this->user_model->getUser($username);
                if($user_info->previlege != 3){// 3 is internal user, 6 is LDAP user, 2 is admin
                     $result['message'] = "Failed to change external user password ";
                } else{
                    $loginResult = $this->user_model->changePassword($username, $newPassword);                
                    $result['status'] = $loginResult;
                }

            }
            echo json_encode($result);
        } else {
            redirect("login");
        }

    }


    public function verifyLDAP($username, $password){
        $adServer = "ldap://DC01.staff.smu.edu.sg";
        $ldap = ldap_connect($adServer);
        // $username = $_POST['username'];
        // $password = $_POST['password'];

        $ldaprdn = 'SMUSTF' . "\\" . $username;

        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

        $bind = @ldap_bind($ldap, $ldaprdn, $password);

        if ($bind) {
            $filter="(sAMAccountName=$username)";
            $result = ldap_search($ldap,"dc=staff,dc=smu,dc=edu,dc=sg",$filter);
            ldap_sort($ldap,$result,"sn");
            $info = ldap_get_entries($ldap, $result);
            for ($i = 0; $i < $info["count"]; $i++)
            {
                if($info['count'] > 1)
                    break;
                // echo "<p>You are accessing <strong> ". $info[$i]["sn"][0] .", " . $info[$i]["givenname"][0] ."</strong><br /> (" . $info[$i]["samaccountname"][0] .")</p>\n";
                // echo '<pre>';
                // var_dump($info);
                // echo '</pre>';
                $userDn = $info[$i]["distinguishedname"][0];
            }
            @ldap_close($ldap);
            return true;
        
        } else {
            return false;
        }
    }


	public function verifyLogin($username, $password)
	{
        $this->load->library('form_validation');
        //Check username in database
        $this->load->model('user_model');

        $this->form_validation->set_rules('username', 'Username', 'trim|required|min_length[1]|max_length[12]|regex_match[/^^[a-zA-Z0-9]*$/]');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[8]');

        $this->form_validation->set_error_delimiters('<p class="error">', '</p>');
        $this->form_validation->set_message('required', 'Please enter %s');

        // check the user input data is valid or not
        $data['pageTitle'] = 'Portal Analytics Login';
        if ($this->form_validation->run() == FALSE) {
            $data["errorMessage"] = "Invalid username or password";
            $this->load->view('login/login_view', $data);
        } else {
            // Verify user's registration in local database
            $user_info = $this->user_model->getUser($username);
            if($user_info == NULL){
                $data['errorMessage'] = "Invalid username or password"; // Set the error message for login failed
                $this->load->view('login/login_view', $data);
            }else{
                //Verify user by SMU LDAP server
                $ldap_check = $this->verifyLDAP($username, $password);

                if($ldap_check){
                    $sess_array = array (
                        // 'id' => $row->id,
                        'username' => $username,
                        'password' => $password,
                        'email' => $user_info->email,
                        'name' => $user_info->name,
                        'privilege' => $user_info->previlege,
                        'phone' => $user_info->phone
                    );
                    $this->session->sess_expiration = 7*24*60*60;
                    $this->session->set_userdata(AP_SESSION, $sess_array);
                    $this->user_model->logUserLogin($username);
                    redirect('heatmap');
                } else{

                    $loginResult = $this->user_model->userLogin($username, $password);

                    if (count($loginResult) > 0) {
                        $row = $loginResult[0]; // Get user data from sql query result
                            $sess_array = array (
                                'id' => $row->id,
                                'username' => $row->username,
                                'password' => $row->password,
                                'email' => $row->email,
                                'name' => $row->name,
                                'privilege' => $row->previlege,
                                'phone' => $row->phone
                            );
                        $this->session->sess_expiration = 7*24*60*60;
                        $this->session->set_userdata(AP_SESSION, $sess_array);
                        $this->user_model->logUserLogin($username);
                        redirect('heatmap');
                    } else {
                        $data['errorMessage'] = "Invalid username or password"; // Set the error message for login failed
                        $this->load->view('login/login_view', $data);
                    }
                }               
            }

        }
    }
}
