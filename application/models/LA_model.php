<?php
include('Data_model.php');
class LA_Model extends Data_model{

	function getChartData($start_date, $locations, $year, $gender, $nationality, $building_prefix){

		//Build constraints string
		$result = array();
		$constraints = "";
		$current_year = date('Y');	
		if($year == -1)
		{
			$constraints .= " and matric_year<$current_year-3";
		}
		else if($year != 0)
		{
			$constraints .= "  and matric_year=$year";
		}

		if($gender != 0)
		{
			$constraints .= "  and gender='$gender'";
		}
		
		if($nationality != 0)
		{
			$constraints .= "  and nationality='$nationality'";
		}

		//Build filter string
		$json1 = "[['Day'";
		$str_schools_filters = "(";
		for($i = 0; $i < count($locations); $i++)
		{
			if($i == (count($locations)-1))
				$str_schools_filters .= "'".$locations[$i]."')";
			else
				$str_schools_filters .= "'".$locations[$i]."',";

			$name = $locations[$i];
			$json1 = $json1 .",'".$name."'";
		}
		$json1 = $json1."],";


		$query1 =  "select day,hour,school,count(distinct(A.id)) from $this->ana_schemas.$this->hourly_table as A, 
		$this->ana_schemas.$this->user_info_table as B where A.id=B.mac and mapped_location like "."'".$building_prefix ."%'" .
		" and day='" . $start_date . "' and school in $str_schools_filters $constraints group by day,hour,school order by hour;";

		$result1 = $this->db->query($query1);

		$arrValues = [];
		$iValue = 0;
		foreach ($result1->result_array() as $row) {
			//Build return result
			$timeStr = $row['day'] . ' ' . $row['hour'];
			for ($i = 0; $i < count($locations); $i++) {
				if ($row['school'] == $locations[$i]) {
					$arrValues[$timeStr][$i] = intval($row['count']);
				}
			}

			for ($i = 0; $i < count($locations); $i++) {
				if (!isset($arrValues[$timeStr][$i])) {
					$arrValues[$timeStr][$i] = 0;
				}
			}
		}

		foreach ($arrValues as $date => $values) {
			$json1 = $json1 . "[new Date(" . substr($date, 0, 4) . "," . (intval(substr($date, 5, 2))-1) . "," . substr($date, 8, 2) . 
					"," . substr($date, 11, strlen($date) - 11) . ",0,0) ";
			for($i = 0; $i < count($values); $i++)
			{
				$json1 = $json1 .",".$values[$i];
			}
			$json1 = $json1 ."],";
		}

		if(count($arrValues) > 0)
		{
			$json1 = rtrim ( $json1, "," );
			$json1 = $json1 . "]";
		}
		else
		{
			$json1 = "";
		}


		//Get LKS Library anonymous users 
		$json2 = "[['Day'";
		$json2 = $json2 .",'$building_prefix - Anonymous users'";
		$json2 = $json2."],";
		$likeString = "'$building_prefix%'";
		$query2 = "select day,hour,count(distinct(id)) from $this->ana_schemas.$this->hourly_table 
					where mapped_location like $likeString and day='" . $start_date . "' group by day,hour order by hour;";

		$result2 = $this->db->query($query2);

		$arrValues = [];
		$iValue = 0;
		foreach ($result2->result_array() as $row) {
			//Build return result
			$timeStr = $row['day'] . ' ' . $row['hour'];
			$arrValues[$timeStr][0] = intval($row['count']);
		}

		foreach ($arrValues as $date => $values) {
			$json2 = $json2 . "[new Date(" . substr($date, 0, 4) . "," . (intval(substr($date, 5, 2))-1) . "," . substr($date, 8, 2) . 
					"," . substr($date, 11, strlen($date) - 11) . ",0,0) ";
			for($i = 0; $i < count($values); $i++)
			{
				$json2 = $json2 .",".$values[$i];
			}
			$json2 = $json2 ."],";
		}
		if(count($arrValues)>0)
		{
			$json2 = rtrim ( $json2, "," );
			$json2 = $json2 . "]";

		}
		else
		{
			$json2 = "";
		}


		return $result = array('locations' =>$locations , 'livelabs_users' => $json1, 'anonymous_users' => $json2);
	}
}

?>