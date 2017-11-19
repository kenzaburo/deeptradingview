<?php

class User_Model extends CI_Model
{

    public $ana_schemas = ANALYTICS_SCHEMAS;
    public $user_tbl = USER_TABLE;
    public $user_building = USER_BUILDING_MAPPING;
    public $login_history_btl = LOGIN_HISTORY_TABLE;

    // Login function

    function delete_all_building_for_user($data)
    {
        $query = $this->db->delete(ANALYTICS_SCHEMAS . "." . USER_BUILDING_MAPPING, $data);
        if ($query) {
            return OK;
        } else {
            return NULL;
        }
    }

    function add_building_for_user($data)
    {
        $query = $this->db->insert(ANALYTICS_SCHEMAS . "." . USER_BUILDING_MAPPING, $data);
        if ($query) {
            return OK;
        } else {
            return NULL;
        }
    }

    /**
    this function will get all infomation of buildings which user subscribe
    including modules of buildings
    */
    function get_building_for_user($id)
    {
        $out = array();
        $this->load->model('building_model');
        $queryStr = "select * from $this->ana_schemas.$this->user_building where \"user_id\" = '" . $id . "'";
        $query = $this->db->query($queryStr);

        if ($query) {
            $result = $query->result();
            $user = $this->get_user_by_id($id)[0];
            $user->password = NULL;
            $user->sso_id = NULL;
            $buildings = array();
            for ($i = 0; $i < count($result); $i++) {
                $building = $this->building_model->get_building_by_id($result[$i]->building_id);
                array_push($buildings, $building);
            }
            array_push($out, array("user" => $user,
                "buildings" => $buildings));
            return $out;
        }
        return NULL;
    }

    /**
     * @param $user_id
     * @return null
     */
    function get_user_by_id($user_id)
    {
        $queryStr = "select * from $this->ana_schemas.$this->user_tbl where \"id\" = '" . $user_id . "'";
        $query = $this->db->query($queryStr);
        if ($query) {
            return $query->result();
        }
        return NULL;
    }

    /**
     * @param $user_id
     * @return null
     */
    function get_building_for_user_id($user_id)
    {
        $this->db->select('*');
        $this->db->where('user_id', $user_id);
        $this->db->from(ANALYTICS_SCHEMAS . "." . USER_BUILDING_MAPPING);
        $query = $this->db->get();
        if ($query) {
            return $query->result();
        }
        return NULL;
    }

    /**
     * @return null
     */
    function get_all_users()
    {
        $queryStr = "select * from $this->ana_schemas.$this->user_tbl";
        $query = $this->db->query($queryStr);
        if ($query) {
            return $query->result();
        }
        return NULL;
    }

    /**
     * Update user profile of system
     * @param $data
     * @return null
     */
    function update_user($data)
    {
        $verifyEmail = $this->get_user_by_email($data['email']);
        if ($verifyEmail != NULL) {
            $this->db->where("email", $data['email']);
            $query = $this->db->update(ANALYTICS_SCHEMAS . "." . USER_TABLE, $data);
            if ($query) {
                $user = $this->get_user_by_email($data['email']);
                return $user;
            } else {
                return NULL;
            }
        } else {
            return NON_EXISTED;
        }
    }

    function get_user_by_email($email)
    {
        $queryStr = "select * from $this->ana_schemas.$this->user_tbl where \"email\" = '" . $email . "'";
        $query = $this->db->query($queryStr);
        if ($query) {
            return $query->result();
        }
        return NULL;
    }

    function delete_user_by_id($id)
    {
        $verifyEmail = $this->get_user_by_id($id);
        if ($verifyEmail != NULL) {
            $this->db->delete(ANALYTICS_SCHEMAS . "." . USER_TABLE, array('id' => $id));
            $this->db->delete(ANALYTICS_SCHEMAS . "." . USER_BUILDING_MAPPING, array('user_id' => $id));
            return OK;
        } else {
            return NON_EXISTED;
        }
    }

    function logUserLogin($username)
    {
        $date = date('Y-m-d H:i:s');
        $site = "smulib";
        $queryStr = "insert into $this->ana_schemas.$this->login_history_btl values('$date', '$username','$site');";
        $query = $this->db->query($queryStr);
        if ($query) {
            return true;
        } else {
            return false;
        }
    }

    function login($user)
    {
        $queryStr = "select id,first_name,last_name,email,phone,role,sso_id from " . ANALYTICS_SCHEMAS . "." . USER_TABLE . " where \"email\" = '" . $user['email'] . "' and \"password\" = '" . $user['password'] . "'";
        $query = $this->db->query($queryStr);
        if ($query) {
            return $query->result();
        }
        return NON_EXISTED;
    }

    function sso_login($user)
    {
        $this->db->where('sso_id', $user['sso_id']);
        $this->db->from(ANALYTICS_SCHEMAS . "." . USER_TABLE);
        $count = $this->db->count_all_results();
        if ($count == 0) {
            //Add new sso_login
            $this->add_user($user);
            $user = $this->get_user_by_email($user['email']);
            return $user;
        } else if ($count == 1) {
            $queryStr = "select id,first_name,last_name,email,phone,role,sso_id from " . ANALYTICS_SCHEMAS . "." . USER_TABLE . " where \"email\" = '" . $user['email'] . "' and \"sso_id\" = '" . $user['sso_id'] . "'";
            $query = $this->db->query($queryStr);
            if ($query) {
                $user_tmp =  $query->result();
//                var_dump($user_tmp);
                if($user_tmp[0]->sso_id == ""){
                    //update user
                    return $this->add_user($user);
                } else {
                    return $user_tmp;
                }

            } else {
                return BAD_REQUEST;
            }
        }
    }

    /**
     * Add new user of system
     * @param $data
     * @return null
     */
    function add_user($data)
    {
        $verifyEmail = $this->get_user_by_email($data['email']);
        if ($verifyEmail != NULL) {
            // update profile
            $this->db->where('email',$data['email']);
            $this->db->update(ANALYTICS_SCHEMAS . "." . USER_TABLE, $data);
            $user = $this->get_user_by_email($data['email']);
            return $user;
        }

        //
        $query = $this->db->insert(ANALYTICS_SCHEMAS . "." . USER_TABLE, $data);
        if ($query) {
            $user = $this->get_user_by_email($data['email']);
            return $user;
        } else {
            return NULL;
        }
    }


}
