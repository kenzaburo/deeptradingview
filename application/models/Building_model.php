<?php

/**
 * Created by PhpStorm.
 * User: trunghuynh
 * Date: 26/10/16
 * Time: 11:33 AM
 */
class Building_Model extends CI_Model
{

    public $ana_schemas = ANALYTICS_SCHEMAS;
    public $building_tbl = BUILDINGS;
    public $building_module = BUILDING_MODULE_MAPPING;
    public $login_history_btl = LOGIN_HISTORY_TABLE;

    // Login function

    function get_level_by_prefix($prefix)
    {
        $this->db->select('*');
        $this->db->where('prefix', $prefix);
        $this->db->from(ANALYTICS_SCHEMAS . "." . LEVELS);
        $query = $this->db->get();
        $sections = $query->result();
        return $sections;
    }

    function add_level_for_building($data)
    {
        $query = $this->db->insert(ANALYTICS_SCHEMAS . "." . LEVELS, $data);
        if ($query) {
            return OK;
        } else {
            return NULL;
        }
    }

    function add_section_for_building($data)
    {
        $query = $this->db->insert(ANALYTICS_SCHEMAS . "." . SECTIONS, $data);
        if ($query) {
            return OK;
        } else {
            return NULL;
        }
    }

    function delete_module_for_building($data)
    {
        $query = $this->db->delete(ANALYTICS_SCHEMAS . "." . BUILDING_MODULE_MAPPING, $data);
        if ($query) {
            return OK;
        } else {
            return NULL;
        }
    }
    
    function add_module_for_building($data)
    {
        $query = $this->db->insert(ANALYTICS_SCHEMAS . "." . BUILDING_MODULE_MAPPING, $data);
        if ($query) {
            return OK;
        } else {
            return NULL;
        }
    }

    function get_module_for_building($id)
    {
        $this->load->model('module_model');
        $queryStr = "select * from $this->ana_schemas.$this->building_module where \"building_id\" = '" . $id . "'";
        $query = $this->db->query($queryStr);
        $out = array();
        if ($query) {
            $result = $query->result();
            $building = $this->get_building_by_id($id);
            $modules = array();
            for ($i = 0; $i < count($result); $i++) {
                $module = $this->module_model->get_module_by_id($result[$i]->module_id)[0];
                array_push($modules, $module);
            }
            array_push($out, array("building" => $building,
                "modules" => $modules));
            return $out;
        }
        return NULL;
    }

    function get_level_by_id($level_id)
    {
        $this->db->select('*');
        $this->db->where('id', $level_id);
        $this->db->from(ANALYTICS_SCHEMAS . "." . LEVELS);
        $query = $this->db->get();
        $levels = $query->result();
        if (count($query->result()) == 0) {
            return NULL;
        }

        $sections = $this->get_section_by_level($levels[0]->building_id, $levels[0]->id);
        (array)$levels[0]->sections = $sections;
        return $levels[0];
    }

    function get_section_by_level($building_id, $level_id)
    {
        $this->db->select('*');
        $this->db->where('building_id', $building_id);
        $this->db->where('level_id', $level_id);
        $this->db->from(ANALYTICS_SCHEMAS . "." . SECTIONS);
        $this->db->order_by('name ASC');
        $query = $this->db->get();
        $sections = $query->result();
        return $sections;
    }

    function get_building_levels($building_id)
    {
        $this->db->select('*');
        $this->db->where('building_id', $building_id);
        $this->db->from(ANALYTICS_SCHEMAS . "." . LEVELS);
        $this->db->order_by('name ASC');
        $query = $this->db->get();
        $levels = $query->result();
        for ($i = 0; $i < count($levels); $i++) {
            $sections = $this->get_section_by_level($building_id, $levels[$i]->id);
            (array)$levels[$i]->sections = $sections;
        }
        return $levels;
    }

    function get_building_by_id($building_id)
    {
        $queryStr = "select * from $this->ana_schemas.$this->building_tbl where \"id\" = '" . $building_id . "'";
        $query = $this->db->query($queryStr);
        if ($query) {
            if (count($query->result()) == 0) {
                return NULL;
            }
            $building = (array)$query->result()[0];
            //get levels
            $levels = $this->get_building_levels($building_id);
            $building['levels'] = $levels;
            return (object)$building;
        }
        return NULL;
    }


    function get_all_building()
    {
        $queryStr = "select * from $this->ana_schemas.$this->building_tbl";
        $query = $this->db->query($queryStr);
        if ($query) {
            $b_arr = $query->result();
            for($i = 0; $i < count($b_arr); $i++){
                $modules = $this->get_module_for_building($b_arr[$i]->id);
                // var_dump($modules);
                $b_arr[$i]->modules = $modules[0]['modules']; //skip building attribute
            }
            return $b_arr;
        }
        return NULL;
    }

    function get_all_levels()
    {
        $queryStr = "select * from " . ANALYTICS_SCHEMAS . "." . LEVELS;
        $query = $this->db->query($queryStr);
        if ($query) {
            return $query->result();
        }
        return NULL;
    }

    function add_building($data)
    {
        $verifyB = $this->get_building_by_code($data['code']);
        if ($verifyB != NULL) {
            // User existed
            return EXISTED;
        }

        //
        $query = $this->db->insert(ANALYTICS_SCHEMAS . "." . BUILDINGS, $data);
        if ($query) {
            $user = $this->get_building_by_code($data['code']);
            return $user;
        } else {
            return NULL;
        }
    }

    function get_building_by_code($code)
    {
        $queryStr = "select * from $this->ana_schemas.$this->building_tbl where \"code\" = '" . $code . "'";
        $query = $this->db->query($queryStr);
        if ($query) {
            return $query->result();
        }
        return NULL;
    }


    function update_building($data)
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


    function delete_building_by_id($id)
    {
        $verifyB = $this->get_building_by_id($id);
        if ($verifyB != NULL) {
            $this->db->delete(ANALYTICS_SCHEMAS . "." . BUILDINGS, array('id' => $id));
            $this->db->delete(ANALYTICS_SCHEMAS . "." . BUILDING_MODULE_MAPPING, array('building_id' => $id));
            $this->db->delete(ANALYTICS_SCHEMAS . "." . LEVELS, array('building_id' => $id));
            $this->db->delete(ANALYTICS_SCHEMAS . "." . SECTIONS, array('building_id' => $id));
            return OK;
        } else {
            return NON_EXISTED;
        }
    }
}

