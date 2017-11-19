<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Data_model extends CI_Model
{

    public $transFillColor = "rgba(220,220,220,1)";
    public $fillColor = array("rgba(205, 40, 135, 1)","rgba(28, 178, 34, 1)","rgba(178, 74, 15, 1)","rgba(229, 43, 80, 1)","rgba(4, 0, 178, 1)","rgba(176, 178, 12, 1)","rgba(28, 178, 34, 1)","rgba(205, 40, 135, 1)","rgba(172,194,132,0.4)", "rgba(172,10,100,0.4)", "rgba(172,100,10,0.4)", "rgba(172,50,150,0.4)", "rgba(172,150,50,0.4)", "rgba(172,200,200,0.4)", "rgba(172,10,100,0.4)",
        "rgba(172,100,10,0.4)", "rgba(172,50,150,0.4)", "rgba(172,150,50,0.4)", "rgba(172,200,200,0.4)", "rgba(172,194,132,0.4)", "rgba(172,10,100,0.4)",
        "rgba(172,100,10,0.4)", "rgba(172,50,150,0.4)", "rgba(172,150,50,0.4)", "rgba(172,200,200,0.4)", "rgba(172,10,100,0.4)", "rgba(172,100,10,0.4)", "rgba(172,50,150,0.4)",
        "rgba(172,150,50,0.4)", "rgba(172,200,200,0.4)", "rgba(172,194,132,0.4)", "rgba(172,10,100,0.4)", "rgba(172,100,10,0.4)", "rgba(172,50,150,0.4)", "rgba(172,150,50,0.4)",
        "rgba(172,200,200,0.4)", "rgba(172,10,100,0.4)", "rgba(172,100,10,0.4)", "rgba(172,50,150,0.4)", "rgba(172,150,50,0.4)", "rgba(172,200,200,0.4)");

    public $ana_schemas = ANALYTICS_SCHEMAS;
    public $daily_table = DAILY_TABLE;
    public $hourly_table = HOURLY_TABLE;
    public $dwell_time_tbl = DWELL_TIME_TABLE;
    public $repeat_clients_tbl = REPEAT_CLIENTS_TABLE;
    public $indoor_schema = INDOORLOC_SCHEMAS;
    public $section_mapping_table = LOCATION_SECTION_TABLE;
    public $occupancy_table = OCCUPANCY_TABLE;
    public $user_info_table = USER_INFO_TABLE;
    public $number_of_level = NUMBER_OF_LEVEL;
    public $heatmap_archival_table = HEATMAP_ARCHIVAL_TABLE;
    public $num_left_location_char = NUM_LEFT_LOCATION_CHAR;

    function get_volume_24h($tbl, $ex_code, $start_time, $end_time){

        $query = "select * from ".COINMASTER_SCHEMAS . "." . $tbl." where timestamp >= '$start_time' and timestamp <= '$end_time'";
        $resultset = $this->db->query($query);
        $data = $resultset->result_array();
        $buy_line = array();
        $sell_line = array();
        $time_line = array();
        foreach ($data as $value) {
            array_push($buy_line, floatval($value['buy_total']));
            array_push($sell_line, -floatval($value['sell_total']));
            array_push($time_line, $value['timestamp']);
        }
        $result = array('dates' => $time_line,'dataBuy'=>$buy_line,'dataSell'=>$sell_line);
        return $result;
    }



    function get_section_occupancy($level_prefix, $start_date,$end_date)
    {
        $loc = $level_prefix;
        $arr_occupancy = array();
        $query = "SELECT distinct 
                  $this->indoor_schema.$this->section_mapping_table.mapped_location,
                  $this->indoor_schema.$this->occupancy_table.time, 
                  $this->indoor_schema.$this->occupancy_table.occupancy,
                  $this->indoor_schema.$this->section_mapping_table.max_capacity
                  FROM $this->indoor_schema.$this->occupancy_table, $this->indoor_schema.$this->section_mapping_table
				  where time>='$start_date' and time <='$end_date' 
				    and $this->indoor_schema.$this->section_mapping_table.current_section= $this->indoor_schema.$this->occupancy_table.current_section
					and $this->indoor_schema.$this->section_mapping_table.mapped_location like'$loc%' 
					order by time, occupancy desc;";

        $resultset = $this->db->query($query);
        if ($resultset != FALSE) {
            $dataset = $resultset->result_array();
            if (count($dataset) > 0) {

                $result = array();
                foreach ($dataset as $row) {
                    $tmp = strtotime($row['time']);
                    $time = date('Y-m-d H:i', $tmp);

                    if(isset($result[$time])){
                        $result[$time][] = $row;
                    } else {
                        $result[$time] = array($row);
                    }
                }

                foreach ($result as $time=>$value) {
                    array_push($arr_occupancy,array("value"=>$value,"time"=>$time));
                }

            } else {
                $arr_occupancy = false;
            }
        }

        return $arr_occupancy;
    }

    function get_latest_occupancy_all_levels($building_id,$start_date,$end_date)
    {
        $limit = $this->number_of_level;
        $this->load->model("building_model");
        $levels = $this->building_model->get_building_levels($building_id);
        $result = array();
        for($i = 0; $i < count($levels); $i++){
            $occ = $this->get_section_occupancy($levels[$i]->prefix,$start_date,$end_date);
            $result[$levels[$i]->id] = $occ;
        }
        return $result;
    }


    function get_heatmap_history($location, $start_date, $end_date)
    {
        //Get heatmap data from heatmaps_archive table
        $query = "WITH summary AS (SELECT heatmapstring,last_update, ROW_NUMBER() OVER (PARTITION BY last_update) as rk 
                        FROM $this->indoor_schema.$this->heatmap_archival_table 
                            WHERE location =  $location AND last_update >= '$start_date' AND last_update <= '$end_date') 
                            SELECT heatmapstring,last_update 
                        FROM summary 
                        WHERE rk=1";
        $result = $this->db->query($query);

        /**
         * Try again in case a connection was not created.
         */
        if (!$result) {
            $result = $this->db->query($query);
        }

        if ($result != FALSE) {
            //Process result and return heatmapstring.
            $dataset = $result->result_array();
            if (count($dataset) > 0) {
                $arr_heatmap_string = $dataset;
            } else {
                $arr_heatmap_string = false;
            }
        }

        return $arr_heatmap_string;
    }


    function getDailyLevelData($prefix_arr, $start_date, $end_date)
    {
        $this->load->model("building_model");
        $arr_levels = $prefix_arr;
        $labels = array();
        $dataset = array();
        $l_arr = array();
        $totalData = array();
        //
        for ($i = 0; $i < count($arr_levels); $i++) {
            $labels = array();
            $daily_data = array();
            $line_name = "";
            $filter = "('" . $arr_levels[$i] . "')";
            $length = strlen($arr_levels[$i]);

            $queryStr = "select day,left(mapped_location,$length) as level,count(distinct(id)) 
                          from $this->ana_schemas.$this->daily_table 
                          where left(mapped_location,$length) in " . $filter . " and day>='" . $start_date . "' AND day<='" . $end_date . "' 
                          group by day,left(mapped_location,$length)
                          order by day;";
         
            $query = $this->db->query($queryStr);
            $k = 0;
            foreach ($query->result_array() as $row) {
                array_push($labels, $row['day']);
                array_push($daily_data, $row['count']);
            }

            // Get line name
            $result = $this->building_model->get_level_by_prefix($arr_levels[$i]);
            if ($result != NULL)
                $line_name = $result[0]->name;
            else {
                $line_name = "";
            }

            //This block of code makes sure chart will have "0" data instead of missing data
            $l_arr = array();
            $data_arr = array();
            //
            $begin = new DateTime($start_date);
            $end = new DateTime($end_date);
            date_add($end, date_interval_create_from_date_string('1 days')); //Add $end_date into last element of array of dates
            $interval = DateInterval::createFromDateString('1 Day');
            $period = new DatePeriod($begin, $interval, $end);
            foreach ($period as $dt) {
                $day = $dt->format('Y-m-d');
                array_push($l_arr,$day);
                $index = $this->isHaveDate($day,$labels);
                if($index == -1){ //no data for the date at $index of $daily_data
                    array_push($data_arr,0);
                } else {
                    array_push($data_arr,$daily_data[$index]);
                }
            }
            if(count($arr_levels) >1){ //try to build total line
                $k = 0;
                foreach ($data_arr as $value) {     
                    if (isset($totalData[$k])){
                        $totalData[$k] += $value;
                    } else {
                        $totalData[$k] = $value;
                    }
                    $k++;
                }
            }
            
            // assembly data
            $data = array('label' => $line_name,
                'data' => $data_arr,
                'fill'=> false,
                'pointRadius'=> 5,
                'pointHitRadius'=> 10,
                'borderColor' => $this->fillColor[$i],
                'backgroundColor' => $this->fillColor[$i], // transparent background
                'pointBorderColor' => $this->fillColor[$i],
                'pointBackgroundColor' => $this->fillColor[$i], // strokeColor(line color),strokeColor and pointColor should be the same
                "pointStrokeColor" => $this->fillColor[$i]);
            array_push($dataset, $data);
        }

        if(count($arr_levels) > 1){
            $temp_arr = array();
            foreach ($totalData as $key => $value) {
                array_push($temp_arr,strval($value));
            }
            //
            $total_arr = array('label'=>"Total",
                            'data'=>$totalData,
                            'fill'=> false,
                            'pointRadius'=> 5,
                            'pointHitRadius'=> 10,
                            'borderColor' => $this->transFillColor,
                            'backgroundColor' => $this->transFillColor, // transparent background
                            'pointBorderColor' => $this->transFillColor,
                            'pointBackgroundColor' => $this->transFillColor, // strokeColor(line color),strokeColor and pointColor should be the same
                            "pointStrokeColor" => $this->transFillColor);
                            
            array_push($dataset, $total_arr);   
        }

        $result = array('labels' => $l_arr, "datasets" => $dataset);
        return $result;
    }

    private function isHaveDate($k, $labels){
        $ret = -1;
        for($i = 0; $i < count($labels); $i++){
            if($labels[$i] == $k){
                return $i;
            }
        }
        return $ret;
    }

    function getDailySectionData($sections, $start_date, $end_date)
    {

        $labels = array(); //A temp label array
        $dataset = array();
        $totalData = array();
        $l_arr = array();
        for ($i = 0; $i < count($sections); $i++) {
            $labels = array();
            $daily_data = array();
            $line_name = "";
            $filter = "('" . $sections[$i] . "')";
            $queryStr = "select day,mapped_location,count(distinct(id)) from $this->ana_schemas.$this->daily_table 
                           where mapped_location in " . $filter . " and day>='" . $start_date . "' AND day<='" . $end_date . "'
                           group by day,mapped_location 
                           order by day;";

            $query = $this->db->query($queryStr);
            $k = 0;
            foreach ($query->result_array() as $row) {
                array_push($labels, $row['day']);
                array_push($daily_data, $row['count']);
            }

            //get line name
            $line_name = substr($sections[$i], 0);
            //
            $l_arr = array();
            $data_arr = array();
            //
            $begin = new DateTime($start_date);
            $end = new DateTime($end_date);
            date_add($end, date_interval_create_from_date_string('1 days'));
            $interval = DateInterval::createFromDateString('1 Day');
            $period = new DatePeriod($begin, $interval, $end);
            foreach ($period as $dt) {
                $day = $dt->format('Y-m-d');
                array_push($l_arr,$day);
                $index = $this->isHaveDate($day,$labels);
                if($index == -1){ //no data for the date at $index of $daily_data
                    array_push($data_arr,0);
                } else {
                    array_push($data_arr,$daily_data[$index]);
                }
            }

            if(count($sections) > 1){ //try to build total line
                $k = 0;
                foreach ($data_arr as $value) {     
                    if (isset($totalData[$k])){
                        $totalData[$k] += $value;
                    } else {
                        $totalData[$k] = $value;
                    }
                    $k++;
                }
            }

            // assembly data
            $data = array('label' => $line_name,
                'data' => $data_arr,
                'fill'=> false,
                'pointRadius'=> 5,
                'pointHitRadius'=> 10,
                'borderColor' => $this->fillColor[$i],
                'backgroundColor' => $this->fillColor[$i], // transparent background
                'pointBorderColor' => $this->fillColor[$i],
                'pointBackgroundColor' => $this->fillColor[$i], // strokeColor(line color),strokeColor and pointColor should be the same
                "pointStrokeColor" => $this->fillColor[$i]);
            array_push($dataset, $data);
        }

        if(count($sections) > 1){
            $temp_arr = array();
            foreach ($totalData as $key => $value) {
                array_push($temp_arr,strval($value));
            }

            $total_arr = array('label'=>"Total",
                            'data'=>$temp_arr,
                            'fill'=> false,
                            'pointRadius'=> 5,
                            'pointHitRadius'=> 10,
                            'borderColor' => $this->transFillColor,
                            'backgroundColor' => $this->transFillColor, // transparent background
                            'pointBorderColor' => $this->transFillColor,
                            'pointBackgroundColor' => $this->transFillColor, // strokeColor(line color),strokeColor and pointColor should be the same
                            "pointStrokeColor" => $this->transFillColor);
                            
            array_push($dataset, $total_arr);   
        }
        //
        $result = array('labels' => $l_arr, "datasets" => $dataset);
        return $result;
    }

    function isContain($k, $labels){
        $res = -1;
        for($i = 0; $i < count($labels); $i++){
            if(intval($labels[$i]) == $k){
                return $i;
            }
        }
        return $res;
    }

    function getHourlyLevelData($prefix_arr, $start_date)
    {
        // Get list of level information
        $this->load->model("building_model");
        $arr_levels = $prefix_arr;
        $totalData = array();
        $labels = array();
        $l_arr = array();
        $dataset = array();

        for ($i = 0; $i < count($arr_levels); $i++) {
            $labels = array();
            $daily_data = array();
            $line_name = "";
            $filter = "('" . $arr_levels[$i] . "')";
            $length = strlen($arr_levels[$i]);
            $queryStr = "select day,hour,left(mapped_location,$length) as level,count(distinct(id)) 
                          from $this->ana_schemas.$this->hourly_table 
                          where left(mapped_location,$length) in " . $filter . " and day='" . $start_date . "' 
                          group by day,hour,left(mapped_location,$length) 
                          order by hour;";

            $query = $this->db->query($queryStr);
            foreach ($query->result_array() as $row) {
                array_push($labels, $row['hour']);
                array_push($daily_data, $row['count']);
            }

            //get line name
            $result = $this->building_model->get_level_by_prefix($arr_levels[$i]);
            if ($result != NULL)
                $line_name = $result[0]->name;
            else {
                $line_name = "";
            }
            
            //if today is not start date do... 24 hours
            // else today is start_date do only current hour time
            $d1 = new DateTime($start_date);
            $now = new DateTime("now");

            //
            $l_arr = array();
            $data_arr = array();

            //
            if($d1->format("Y-m-d") == $now->format("Y-m-d")){
                //todo get time now
                $var = intval(date('H'));
                for($k = 0; $k < $var; $k++){
                    array_push($l_arr, $k.":00");
                    $index = $this->isContain($k,$labels);
                    if($index != -1){
                        array_push($data_arr,$daily_data[$index]);
                    } else {
                        array_push($data_arr,0); //add 0 if there is no data at $k hour
                    }
                }
            } else if($d1->format("Y-m-d") < $now->format("Y-m-d")){
                $h_max = 24;
                for($k = 0; $k < $h_max; $k++){
                    array_push($l_arr, $k.":00");
                    $index = $this->isContain($k,$labels);
                    if($index != -1){
                        array_push($data_arr,$daily_data[$index]);
                    } else {
                        array_push($data_arr,0); //add 0 if there is no data at $k hour
                    }
                }
            }

            if(count($arr_levels) > 1){ //try to build total line
                $k = 0;
                foreach ($data_arr as $value) {     
                    if (isset($totalData[$k])){
                        $totalData[$k] += $value;
                    } else {
                        $totalData[$k] = $value;
                    }
                    $k++;
                }
            }

            // assembly data
            $data = array('label' => $line_name,
                'data' => $data_arr,
                'fill'=> false,
                'pointRadius'=> 5,
                'pointHitRadius'=> 10,
                'borderColor' => $this->fillColor[$i],
                'backgroundColor' => $this->fillColor[$i], // transparent background
                'pointBorderColor' => $this->fillColor[$i],
                'pointBackgroundColor' => $this->fillColor[$i], // strokeColor(line color),strokeColor and pointColor should be the same
                "pointStrokeColor" => $this->fillColor[$i]);
            array_push($dataset, $data);
        }

        
        if(count($arr_levels) > 1){
            $temp_arr = array();
            foreach ($totalData as $key => $value) {
                array_push($temp_arr,strval($value));
            }
            //
            $total_arr = array('label'=>"Total",
                            'data'=>$totalData,
                            'fill'=> false,
                            'pointRadius'=> 5,
                            'pointHitRadius'=> 10,
                            'borderColor' => $this->transFillColor,
                            'backgroundColor' => $this->transFillColor, // transparent background
                            'pointBorderColor' => $this->transFillColor,
                            'pointBackgroundColor' => $this->transFillColor, // strokeColor(line color),strokeColor and pointColor should be the same
                            "pointStrokeColor" => $this->transFillColor);
                            
            array_push($dataset, $total_arr);   
        }
        //
        $result = array('labels' => $l_arr, "datasets" => $dataset);
        return $result;
    }

    function getHourlySectionData($sections, $start_date)
    {

        $labels = array();
        $l_arr = array();
        $totalData = array();
        $dataset = array();
        for ($i = 0; $i < count($sections); $i++) {
            $labels = array();
            $daily_data = array();

            $line_name = "";
            $filter = "('" . $sections[$i] . "')";
            $queryStr = "select day,hour,mapped_location,count(distinct(id)) from  $this->ana_schemas.$this->hourly_table 
                            where mapped_location in" . $filter . "and day='" .$start_date . "' 
                            group by day,hour,mapped_location order by hour;";
            $query = $this->db->query($queryStr);
            foreach ($query->result_array() as $row) {
                array_push($labels, $row['hour']);
                array_push($daily_data, $row['count']);
            }

            //get line name
            $line_name = substr($sections[$i], 0);

            //if today is not start date do... 24 hours
            // else today is start_date do only current hour time
            $d1 = new DateTime($start_date);
            $now = new DateTime("now");
            //
            $data_arr = array();
            $l_arr = array();
            if($d1->format("Y-m-d") == $now->format("Y-m-d")){
                //todo get time now
                $var = intval(date('H'));
                for($k = 0; $k < $var; $k++){
                    array_push($l_arr, $k.":00");

                    $index = $this->isContain($k,$labels);
                    if($index != -1){
                        array_push($data_arr,$daily_data[$index]);
                    } else {
                        array_push($data_arr,0); //add 0 if there is no data at $k hour
                    }
                }
            } else if($d1->format("Y-m-d") < $now->format("Y-m-d")){
                $h_max = 24;
                for($k = 0; $k < $h_max; $k++){
                    array_push($l_arr, $k.":00");
                    $index = $this->isContain($k,$labels);
                    if($index != -1){
                        array_push($data_arr,$daily_data[$index]);
                    } else {
                        array_push($data_arr,0); //add 0 if there is no data at $k hour
                    }
                }
            }

            if(count($sections) > 1){ //try to build total line
                $k = 0;
                foreach ($data_arr as $value) {     
                    if (isset($totalData[$k])){
                        $totalData[$k] += $value;
                    } else {
                        $totalData[$k] = $value;
                    }
                    $k++;
                }
            }

            // Assembly data
            $data = array('label' => $line_name,
                'data' => $data_arr,
                'fill'=> false,
                'pointRadius'=> 5,
                'pointHitRadius'=> 10,
                'borderColor' => $this->fillColor[$i],
                'backgroundColor' => $this->fillColor[$i], // transparent background
                'pointBorderColor' => $this->fillColor[$i],
                'pointBackgroundColor' => $this->fillColor[$i], // strokeColor(line color),strokeColor and pointColor should be the same
                "pointStrokeColor" => $this->fillColor[$i]);
            array_push($dataset, $data);
        }

        if(count($sections) > 1){
            $temp_arr = array();
            foreach ($totalData as $key => $value) {
                array_push($temp_arr,strval($value));
            }
            //
            $total_arr = array('label'=>"Total",
                            'data'=>$totalData,
                            'fill'=> false,
                            'pointRadius'=> 5,
                            'pointHitRadius'=> 10,
                            'borderColor' => $this->transFillColor,
                            'backgroundColor' => $this->transFillColor, // transparent background
                            'pointBorderColor' => $this->transFillColor,
                            'pointBackgroundColor' => $this->transFillColor, // strokeColor(line color),strokeColor and pointColor should be the same
                            "pointStrokeColor" => $this->transFillColor);
                            
            array_push($dataset, $total_arr);   
        }
        $result = array('labels' => $l_arr, "datasets" => $dataset);
        return $result;
    }

    function getVisitBuildingData($building_code, $start_date, $end_date)
    {
        return $this->getVisitSectionData($building_code, $start_date, $end_date);
    }

    function getVisitSectionData($building_code, $start_date, $end_date)
    {

        $labels = array();
        $dataset = array();
        $line_name = "";
        $daily_data = array();
        $queryStr = "select A.count as no_repeat, count(A.id) from (select  id, count(id) from 
        $this->ana_schemas.$this->repeat_clients_tbl where building=$building_code 
          and date>='" . $start_date . "' and date <= '" . $end_date . "' group by id) as A group by A.count order by no_repeat;";
        $query = $this->db->query($queryStr);
        foreach ($query->result_array() as $row) {
            array_push($labels, $row['no_repeat']);
            array_push($daily_data, $row['count']);
        }

        //get line name
        $line_name = "No. of repeat client";
        // assembly data
        $i = 0;
        $data = array('label' => $line_name,
            'data' => $daily_data,
             'borderWidt'=> 1,
            'backgroundColor' => $this->fillColor[$i],
            'borderColor' => $this->fillColor[$i],
            'pointBackgroundColor' => $this->fillColor[$i]);
        array_push($dataset, $data);

        $result = array('labels' => $labels, "datasets" => $dataset);
        return $result;
    }

    function getTransitionData($start_date, $end_date, $hours, $weekdays, $weekends)
    {
        $constraints = "";
        if (count($hours) > 0) {
            $constraints = "and hour in (";
            for ($i = 0; $i < count($hours); $i++) {
                if ($i == (count($hours) - 1))
                    $constraints .= $hours[$i] . ")";
                else
                    $constraints .= $hours[$i] . ",";
            }
        }

        $constraints_wk = "";
        if ($weekdays == "false") {
            $constraints_wk = "and EXTRACT(dow from day) IN (0,6)";
        } else if ($weekends == "false") {
            $constraints_wk = "and EXTRACT(dow from day) NOT IN (0,6)";
        }

        $tran_data_matrix = array();
        //TODO: get this array from database
        $place_array = array("SMULKS", "SMUSOA", "SMUSOB", "SMUSES", "SMUSIS", "SMULab");
        $arr_values = array();
        $arr_cul = array();
        $total = 0;
        $transition_tbl = "transition_buildings"; //TODO: move to constant
        foreach ($place_array as $i) {
            $temp_array = array();
            foreach ($place_array as $j) {
                if ($i == $j) {
                    array_push($temp_array, 0);
                } else {
                    $query = "select sum(count) from $this->ana_schemas.$transition_tbl where from_section='$i' 
                    and to_section='$j' and day>='$start_date' and day <='$end_date' $constraints $constraints_wk;";
                    $rs = $this->db->query($query);
                    foreach ($rs->result_array() as $row) {
                        $temp = $row['sum'];
                    }

                    $temp = ($temp == "") ? 0 : $temp;

                    // $tran_data_matrix = $tran_data_matrix . $temp . ",";
                    array_push($temp_array, intval($temp));

                    // Get from name for data table
                    // $from = $this->getSectionName($i);
                    $from = $i;

                    // Get "to" name for data table
                    // $to = $this->getSectionName($j);
                    $to = $j;
                    $arr_values[$from . "->" . $to] = $temp;
                    $total += intval($temp);
                }
            }
            array_push($tran_data_matrix, $temp_array);
        }

        arsort($arr_values);
        $currentTotal = 0;

        foreach ($arr_values as $place => $values) {
            $currentTotal += intval($values);
            if ($total > 0) {
                $arr_cul[$place] = round($currentTotal / $total * 100);
            } else {
                $arr_cul[$place] = 0;
            }
        }

        $return_arr = array('tran_data_matrix' => $tran_data_matrix,
            'arr_values' => $arr_values,
            'arr_cul' => $arr_cul);
        return $return_arr;
    }

}
