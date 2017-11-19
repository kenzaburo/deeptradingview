<?php
/**
 * Created by PhpStorm.
 * User: trunghuynh
 * Date: 26/10/16
 * Time: 11:33 AM
 */
class Module_Model extends CI_Model {

    public $ana_schemas = ANALYTICS_SCHEMAS;
    public $module_tbl = MODULES;
    public $login_history_btl = LOGIN_HISTORY_TABLE;
    // Login function


    function get_module_by_id($module_id){
        $queryStr ="select * from $this->ana_schemas.$this->module_tbl where \"id\" = '".$module_id."'" ;
        $query = $this->db->query($queryStr);
        if ($query) {
            return $query->result();
        }
        return NULL;
    }


    function get_all_module(){
        $queryStr ="select * from $this->ana_schemas.$this->module_tbl" ;
        $query = $this->db->query($queryStr);
        if ($query) {
            return $query->result();
        }
        return NULL;
    }

    function add_module($data){
        $verifyB = $this->get_module_by_url($data['url']);
        if($verifyB != NULL){
            // User existed
            return EXISTED;
        }

        //
        $query = $this->db->insert(ANALYTICS_SCHEMAS . "." . MODULES,$data);
        if($query) {
            $module = $this->get_module_by_url($data['url']);
            return $module;
        } else {
            return NULL;
        }
    }

    function get_module_by_url($url){
        $queryStr ="select * from $this->ana_schemas.$this->module_tbl where \"url\" = '".$url."'" ;
        $query = $this->db->query($queryStr);
        if ($query) {
            return $query->result();
        }
        return NULL;
    }

    function delete_module_by_id($id){
        $verifyB = $this->get_module_by_id($id);
        if($verifyB != NULL){
            $this->db->delete(ANALYTICS_SCHEMAS . "." . MODULES, array('id' => $id));
            $this->db->delete(ANALYTICS_SCHEMAS . "." . BUILDING_MODULE_MAPPING, array('module_id' => $id));
            return OK;
        } else {
            return NON_EXISTED;
        }
    }
}

