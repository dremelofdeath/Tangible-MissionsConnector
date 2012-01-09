<?php
// Application: Christian Missions Connector
// File: 'trips.php'
//  shows all trips the user is a member of
//
//require_once 'facebook.php';

include_once 'common.php';

$con = arena_connect();

$saferequest = cmc_safe_request_strip();
$has_error = FALSE;
$err_msg = '';

$json = array();

function getdatestring($year,$month,$date) {

if ($month<10)
	$smonth = strval($month);
else
	$smonth = strval($month);

if ($date<10)
	$sdate = strval($date);
else
	$sdate = strval($date);

$res = $year.'-'.$smonth.'-'.$sdate.' '.'00:00:00';
return $res;
}

$todayy = date("Y");
$todaym = date("m");
$todayd = date("d");
$today = getdatestring($todayy,$todaym,$todayd);

// get all trips that are in the future
$sql = 'select * from trips where departure >="'.$today.'"';
$result = mysql_query($sql,$con);

if ($result) {

   $numrows = mysql_num_rows($result);
	$json['tripnames'] = array();
	$json['tripids'] = array();

   if ($numrows!=0) {
  	while ($row = mysql_fetch_array($result)) {
		$json['tripnames'][] = $row['tripname'];
		$json['tripids'][] = $row['id'];
  	}
   }
 
}
else {
 	setjsonmysqlerror($has_error,$err_msg,$sql);
}


$json['has_error'] = $has_error;

if ($has_error) {
  $json['err_msg'] = $err_msg;
}

echo json_encode($json);

?>



