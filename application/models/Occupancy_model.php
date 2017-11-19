<?php
include('Data_model.php');
class Occupancy_Model extends Data_model {


	function getLevelData($level, $date){

		$this->load->model("building_model");
        $lvl_obj = $this->building_model->get_level_by_id($level);

		$locations = $lvl_obj->sections;
		$prefix = $lvl_obj->prefix;
		$result = array();
		$rowbuilder = "";
		for ($k = 0; $k < count($locations); $k++) {
			$section_name = $locations[$k]->name;
			$strtemp = "select count(distinct(id)),hour from $this->ana_schemas.$this->hourly_table 
						where 
							day='$date' 
							AND mapped_location='$section_name' 
							group by hour 
							order by hour;";

			$query = $this->db->query($strtemp);
			$counts = [];
			for ($i = 0; $i < 24; $i++) {
				$counts[$i] = 0;
			}
			

			foreach ($query->result_array() as $row) {
				$counts[intval($row['hour'])] = $row['count'];
			}
			
			for ($i = 0; $i < 24; $i++) {
				$rowbuilder = $rowbuilder . $counts[$i] . ",";
			}
		}

		$rowbuilder = rtrim ( $rowbuilder, "," );
		return $result = array('locations' =>$locations ,'data' => "[".$rowbuilder."]");
	}
}

?>