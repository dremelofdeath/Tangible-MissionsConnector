<?php
// Application: Christian Missions Connector
// File: 'addtripmember.php' 
//  add user to trip
// 
//require_once 'facebook.php';

include_once 'common.php';

$con = arena_connect();

$saferequest = cmc_safe_request_strip();
$has_error = FALSE;
$err_msg = '';

if (array_key_exists('tid', $saferequest) && array_key_exists('fbid', $saferequest)) {
  // both tripid and facebook userid should be provided
  $tid = $saferequest['tid'];
  $fbid = $saferequest['fbid'];
} else {
  // error case: neither are defined
  $has_error = TRUE;
  $err_msg = "Neither required parameters was defined.";
}

$json = array();

if (!$has_error) {

	// first check that the user has a CMC profile - otherwise redirect user to create a profile
	$sql = 'select * from users where userid="'.$fbid.'"';
	$result = mysql_query($sql,$con);
	if (!$result) {
    $has_error = TRUE;
    $err_msg = "Can't query (query was '$query'): " . mysql_error();
    } else {
	
	$numrows = mysql_num_rows($result);

	if ($numrows==0) {
	// This means user does not have a CMC profile
	
	$has_error = TRUE;
    $err_msg = "No CMC Profile";
	/*
	echo '<br /><br /> You do not have a Christian Missions Profile Yet!! <br /><br />';
	echo"<b>Getting started</b> is simple and takes about 2 minutes. The first step is to create a profile for yourself or your organization by clicking the blue highlighted link below <br/><br /><center><a href='http://apps.facebook.com/missionsconnector/new.php'>Create your profile</a></center><br /><br />";
	*/
	}

	else {


	$sql = 'INSERT INTO tripmembers (userid,tripid,isadmin,invited,accepted) VALUES ("'.$fbid.'","'.$tid.'","0","1","1")';
	$result = mysql_query($sql,$con);
	
	if(!result){	
	    $has_error = TRUE;
		$err_msg = "Can't query (query was '$query'): " . mysql_error();
	}
	else {
	// update number of people in trips table
	$sql = 'select numpeople from trips where id="'.$tid.'"';
	$result = mysql_query($sql,$con);
	if (!$result) {
	    $has_error = TRUE;
		$err_msg = "Can't query (query was '$query'): " . mysql_error();
	}
	else {
		$row = mysql_fetch_array($result);
		$numpeople = $row['numpeople'];
		$numpeople++;
		$sql2 = 'update trips set numpeople="'.$numpeople.'" where id="'.$tid.'"';
		$result2 = mysql_query($sql2,$con);
		if (!$result2) {
			$has_error = TRUE;
			$err_msg = "Can't query (query was '$query'): " . mysql_error();
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
