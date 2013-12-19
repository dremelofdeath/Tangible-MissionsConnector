<?php
// Application: Christian Missions Connector
// File: 'trips.php'
//  shows all trips the user is a member of
//
//require_once 'facebook.php';

include_once 'common.php';

$con = arena_connect();

$saferequest = cmc_safe_request_strip($con);
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
  $err_msg = "Required parameters not defined.";
}

$json = array();

if (!$has_error) {

// check the existence of the trip
$sql = 'select * from trips where id="'.$tripid.'"';
$result = $con->query($sql);
if (!$result) {
  setjsonmysqlerror($has_error,$err_msg,$sql,$con);
}
else {

 if ($result->num_rows == 0) {
    $has_error = TRUE;
    $err_msg = "No Trip exists with the specified ID";
 }
 else {
if (isset($tripid)) {

$sql = 'select * from tripmembers where tripid="'.$tripid.'" and userid="'.$fbid.'"';
$result = $con->query($sql);
if (!$result) {
	setjsonmysqlerror($has_error,$err_msg,$sql,$con);
}
else {
  if ($result->num_rows==0) {
		$has_error = TRUE;
		$err_msg = "You are not a member of this trip";
  }
  else if ($numrows==1) {
		$has_error = TRUE;
		$err_msg = "You are the the only person on this trip, delete trip instead";
	}
	else {
		$sql = 'delete from tripmembers where tripid="'.$tripid.'" and userid="'.$fbid.'"';
		$result = $con->query($sql);
		
		if (!$result) {
			setjsonmysqlerror($has_error,$err_msg,$sql,$con);
		}
		else {

		// Now decrement the number of people on this trip
		$sql = 'select * from trips where id="'.$tripid.'"';
		$result = $con->query($sql);
		if (!$result) {
			setjsonmysqlerror($has_error,$err_msg,$sql,$con);
		}
		else {
			$row = $result->fetch_array();
			$numpeople = $row['numpeople'] + 0;
			$numpeople--;

			$sql = 'update trips set numpeople="'.$numpeople.' where id="'.$tripid.'"';
			$result = $con->query($sql);
			if (!$result) {
				setjsonmysqlerror($has_error,$err_msg,$sql,$con);
			}
		}
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



