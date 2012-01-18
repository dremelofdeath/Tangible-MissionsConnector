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

if (array_key_exists('tripid', $saferequest)) {
  // tripid should be provided
  $tripid = $saferequest['tripid'];
} 
else {
  // error case: neither are defined
  $has_error = TRUE;
  $err_msg = "tripid was not defined.";
}

$json = array();


if (!$has_error) {

$sql = 'select * from trips where id="'.$tripid.'"';
$result = mysql_query($sql,$con);
if (!$result) {
    setjsonmysqlerror($has_error,$err_msg,$sql);
}
else {

  $numrows = mysql_num_rows($result);

  if ($numrows == 0) {
    $has_error = TRUE;
    $err_msg = "No Trips to match the tripid";
  }
  else {
	$row = mysql_fetch_array($result,MYSQL_ASSOC);
	$tripname = $row['tripname'];
	
	mysql_free_result($result);

	// first we need to delete the tripmembers for this trip
	$sql = 'delete from tripmembers where tripid="'.$tripid.'"';
	$result = mysql_query($sql,$con);

	if ($result) {
  
  		// Then delete the row from trips table
  		$sql = 'delete from trips where id="'.$tripid.'"';
  		$result = mysql_query($sql,$con);
  		if ($result) {
			$json['tripname'] = $tripname;
  		}
  		else {
    			setjsonmysqlerror($has_error,$err_msg,$sql);
  		}
	}
	else {
    		setjsonmysqlerror($has_error,$err_msg,$sql);
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



