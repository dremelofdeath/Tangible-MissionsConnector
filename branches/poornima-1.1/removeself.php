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

if (array_key_exists('tripid', $saferequest) && array_key_exists('fbid', $saferequest)) {
  // both tripid and facebook userid should be provided
  $tripid = $saferequest['tripid'];
  $fbid = $saferequest['fbid'];
} 
else {
  // error case: neither required variable is defined
  $has_error = TRUE;
  $err_msg = "Neither required parameters was defined.";
}

$json = array();

if (!$has_error) {
if (isset($tripid)) {

$sql = 'select * from tripmembers where tripid="'.$tripid.'"';
$result = mysql_query($sql,$con);
if (!$result) {
	$has_error = TRUE;
	$err_msg = "Can't query (query was '$query'): " . mysql_error();
}
else {
	$numrows = mysql_num_rows($result);
	if ($numrows==1) {
		$has_error = TRUE;
		$err_msg = "You are the the only person on this trip, delete trip instead";
	}
	else {
		$sql = 'delete from tripmembers where tripid="'.$tripid.'" and userid="'.$fbid.'"';
		$result = mysql_query($sql,$con);
		
		if (!$result) {
			$has_error = TRUE;
			$err_msg = "Can't query (query was '$query'): " . mysql_error();
		}
		else {

		// Now decrement the number of people on this trip
		$sql = 'select * from trips where id="'.$tripid.'"';
		$result = mysql_query($sql,$con);
		if (!$result) {
			$has_error = TRUE;
			$err_msg = "Can't query (query was '$query'): " . mysql_error();
		}
		else {
			$row = mysql_fetch_array($result);
			$numpeople = $row['numpeople'] + 0;
			$numpeople--;

			$sql = 'update trips set numpeople="'.$numpeople.' where id="'.$tripid.'"';
			$result = mysql_query($sql,$con);
			if (!$result) {
				$has_error = TRUE;
				$err_msg = "Can't query (query was '$query'): " . mysql_error();
			}
		}
		}
	}
}

}
}

$json['has_error'] = $has_error;

if ($has_error) {
  $json['err_msg'] = $err_msg;
}

echo json_encode($json);


?>



