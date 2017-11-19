<?php
include('Data_model.php');

class Dwell_time extends Data_model
{
    function getDwellTimeData($levels, $start_date, $end_date)
    {
        $this->load->model("building_model");
        $json = "[['Hours'";
        $json_total = "[['Hours','Total'],";
        $json_percent = "[['Hours'";
        $json_total_percent = "[['Hours','Total percentage'],";
        $json_data_avg = "[";
        $json_data_avg_total = "[";
        $str_level_filter = "(";
        $arr_levels = array();
        $arr_name_levels = array();

        for ($i = 0; $i < count($levels); $i++) {
            $level_obj = $this->building_model->get_level_by_id($levels[$i]);
            if($level_obj == NULL){
                return NULL;
            }
            array_push($arr_levels, $level_obj->prefix);
            array_push($arr_name_levels, $level_obj->name);

        }

        for ($i = 0; $i < count($arr_levels); $i++) {
            if ($i == (count($arr_levels) - 1)) {
                $str_level_filter .= "'" . $arr_levels[$i] . "'";
            } else {
                $str_level_filter .= "'" . $arr_levels[$i] . "',";
            }
            $json = $json . ",\"" . $arr_name_levels[$i] . "\"";
            $json_percent = $json_percent . ",\"" . $arr_name_levels[$i] . "\"";
        }

        $str_level_filter .= ")";
        $json = $json . "],";
        $json_percent = $json_percent . "],";

        $strSelect = "select num_hour, level, count(id) 
                        from $this->ana_schemas.$this->dwell_time_tbl 
						where 
							from_day>='$start_date' 
							and to_day<='$end_date' 
							and level in $str_level_filter 
							group by level, num_hour
							order by num_hour";

        // var_dump($strSelect);
        $result = $this->db->query($strSelect);

        $arrValues = [];
        $iValue = 0;
        $arr_total = [];
        $total_all = 0;
        $total_num_count = 0;
        $total_percent_list = [];
        foreach ($result->result_array() as $row) {
            $num_hour = $row['num_hour'];
            //
            for ($i = 0; $i < count($arr_levels); $i++) {
                if ($row['level'] == $arr_levels[$i]) {
                    $arrValues[$num_hour][$i] = intval($row['count']);
                    if (!isset($arr_total[$i])) {
                        $arr_total[$i] = intval($row['count']);
                    } else {
                        $arr_total[$i] += intval($row['count']);
                    }
                    $total_all += intval($row['count']);
                }
            }
            //
            for ($i = 0; $i < count($arr_levels); $i++) {
                if (!isset($arrValues[$num_hour][$i])) {
                    $arrValues[$num_hour][$i] = 0;
                }
            }
        }

        $data_avg = []; // data_avg[1] - Level 1, data_avg[2] - Level 2
        $sum_count = 0; // p1+p2+p3
        $sum_total_count = 0; // p1*s1 + p2*s2...
        // var_dump($arrValues);
        foreach ($arrValues as $num_hour => $values) {
            $json = $json . "[ $num_hour";
            $json_percent = $json_percent . "[ $num_hour";
            $json_total = $json_total . "[ $num_hour";
            $json_total_percent = $json_total_percent . "[ $num_hour";
            $total = 0;
            for ($i = 0; $i < count($values); $i++) {
                $tmp = round($values[$i] / $arr_total[$i], 2);
                $json = $json . "," . $values[$i];
                $json_percent = $json_percent . "," . $tmp;
                $total += $values[$i];
                if (!isset($data_avg[$i])) {
                    $data_avg[$i] = $values[$i] * $num_hour;
                } else {
                    $data_avg[$i] += $values[$i] * $num_hour;
                }
            }
            $sum_count += $total;
            $sum_total_count += $total * $num_hour;
            $tmp = round($total / $total_all, 2);
            $json_total_percent = $json_total_percent . ",$tmp],";
            $json_total = $json_total . ",$total],";
            $json = $json . "],";
            $json_percent = $json_percent . "],";

        }

        ## Build average dwell time table
        foreach ($data_avg as $key => $value) {
            $json_data_avg = $json_data_avg . "[\"" . $arr_name_levels[$key] . "\",\"" . round($value / $arr_total[$key], 2) . "\"";
            $json_data_avg = $json_data_avg . "],";
        }
        ## Build for average total dwell time table
        $json_data_avg = $json_data_avg . "[\"Total\",\"" . round($sum_total_count / $sum_count, 2) . "\"]]";


        if (count($arrValues) > 0) {
            $json = rtrim($json, ",");
            $json = $json . "]";
            $json_percent = rtrim($json_percent, ",");
            $json_percent = $json_percent . "]";
            $json_total = rtrim($json_total, ",");
            $json_total = $json_total . "]";
            $json_total_percent = rtrim($json_total_percent, ",");
            $json_total_percent = $json_total_percent . "]";


            return array('json' => $json,
                'json_total' => $json_total,
                'json_percent' => $json_percent,
                "json_total_percent" => $json_total_percent,
                "json_data_avg" => $json_data_avg);
        } else {
            return "";
        }
    }
}

?>