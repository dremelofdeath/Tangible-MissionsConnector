<?php
// Application: Christian Missions Connector
// File: 'trips.php'
//  shows all trips the user is a member of
//

include_once 'common.php';

$con = arena_connect();

$saferequest = cmc_safe_request_strip();
$has_error = FALSE;
$err_msg = '';

if (array_key_exists('tripid', $saferequest) && array_key_exists('fbid', $saferequest)) {
  // both tripid and facebook userid should be provided
  $tid = $saferequest['tripid'];
  $fbid = $saferequest['fbid'];
} 
else if (array_key_exists('Tripmembers', $saferequest) && array_key_exists('tripid', $saferequest)) {
  // both tripid and facebook userid should be provided
  $tid = $saferequest['tripid'];
  $tripmembers = $saferequest['Tripmembers'];
}
else {
  // error case: neither are defined
  $has_error = TRUE;
  $err_msg = "Required parameters was defined.";
}

$json = array();

if (!$has_error) {

// check if there is a trip corresponding to $tid
$sql = 'select * from trips where id="'.$tid.'"';
$result = mysql_query($sql,$con);
if (!$result) {
	setjsonmysqlerror($has_error,$err_msg,$sql);
}
else {

$numrows = mysql_num_rows($result);
if ($numrows ==0) {
  $has_error = TRUE;
  $err_msg = "No Trip exists with the specified ID";
}
else {
if (isset($fbid)) {

$sql = 'select userid from tripmembers where userid !="'.$fbid.'" and accepted="1" and tripid="'.$tid.'"';
$result = mysql_query($sql,$con);
if (!$result) {
    setjsonmysqlerror($has_error,$err_msg,$sql);
}
else {
	$numrows = mysql_num_rows($result);
	if ($numrows==0) {
		$sql2 = 'select * from tripmembers where userid="'.$fbid.'" and accepted="1" and tripid="'.$tid.'"';
		$result2 = mysql_query($sql2,$con);
		if (!$result2) {
		    setjsonmysqlerror($has_error,$err_msg,$sql2);
		}
		else {
		$numrows2 = mysql_num_rows($result2);
		if ($numrows2==1) {
			$has_error =  TRUE;
			$err_msg = 'You are the only person in this trip'; 
		}
		}
	}
}
}
else if (isset($tripmembers)) {

// Now we can delete members from the trip - which means updating the tripmembers table in the database

if (is_array($tripmembers)) {
	while ($mytripmember = current($tripmembers)) {
			$sql = 'delete from tripmembers where userid="'.$mytripmember.'" and tripid="'.$tid.'"';
			$result = mysql_query($sql,$con);
			if (!result) {
				setjsonmysqlerror($has_error,$err_msg,$sql);
				continue 1;
			}
			next($tripmembers);
	}
}
else {

$sql = 'delete from tripmembers where userid="'.$tripmembers.'" and tripid="'.$tid.'"';
$result = mysql_query($sql,$con);

if (!result) {
	setjsonmysqlerror($has_error,$err_msg,$sql);
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



