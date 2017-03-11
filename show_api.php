<?php
	include("simple_html_dom.php");

	$arg_count = count($argv);
	if($arg_count>2){
		echo "Only one argument allowed\n";
		exit();
	}else{
		$arg = $argv[1];
		$arg_split = explode("=", $arg);
		$arg_key = strtolower($arg_split[0]);
		$arg_value = strtolower($arg_split[1]);

		switch ($arg_key) {
			case 'state':
				$state_str = ucwords($arg_value);
				break;
			case 'area':
				$area_str = ucwords($arg_value);
				break;
			default:
				echo "Argument invalid";
				exit();
		}
	}
	$data = array();
	date_default_timezone_set('Asia/Singapore');
	$date = date('Y-m-d h:i:s a', time());
	// $date = '2017-03-11 10:30:20 am';
	$hour = date('H', strtotime($date));

	$remainder = $hour%6;
	$time_index = $remainder-1;
	$hour_param = "";
	if($hour<5){
		$hour_param= "hour1";
	}elseif ($hour<11) {
		$hour_param= "hour2";
	}elseif ($hour<17) {
		$hour_param= "hour3";
	}else{
		$hour_param= "hour4";
	}

	if($remainder==0){
		$format_date = date('Y-m-d', strtotime("yesterday"));
		$time_index = 5; //last index
	}else{
		$format_date = date('Y-m-d', strtotime($date));
	}
	$endpoint = "http://apims.doe.gov.my/v2/";
	$query = $hour_param."_".$format_date.".html"; 
	$html = file_get_html($endpoint.$query);
	if($html=== FALSE){
		echo "Data is currently not available.";
	}
	foreach($html->find('tr') as $row) {
	    $state = $row->find('td',0)->plaintext;
	    $area = $row->find('td',1)->plaintext;
	    $time1 = $row->find('td',2)->plaintext;
	    $time2 = $row->find('td',3)->plaintext;
	    $time3 = $row->find('td',4)->plaintext;
	    $time4 = $row->find('td',5)->plaintext;
	    $time5 = $row->find('td',6)->plaintext;
	    $time6 = $row->find('td',7)->plaintext;

	    $data[$state][$area] = [$time1,$time2,$time3,$time4,$time5,$time6];
	}


	if(isset($state_str)){
		$datum = $data[$state_str];
		$area_result = array();
		foreach ($datum as $area => $value) {
			array_push($area_result, array($area => $value[$time_index]));
		}
		$result = array("state" => $state_str, "data" => $area_result, "datetime"=> $date);
		echo json_encode($result);
	}


	if (isset($area_str)) {
	foreach ($data as $datum) {
		if (array_key_exists($area_str, $datum)){
				$area_value = "";
				foreach ($datum as $area => $value) {

					if ($area == $area_str) {
						$area_value = $value[$time_index];
					}
				}
			$result = array("area" => $area_str, "data" => $area_value, "datetime"=> $date);
			echo json_encode($result);
		}
	}
	}


?>


