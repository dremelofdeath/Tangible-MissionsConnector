<?php
// Application: Christian Missions Connector
// File: 'profileT.php' 
//  mission trip profile as seen by public- no edits from this page
// 
//require_once 'facebook.php';
include_once 'common.php';
$con = arena_connect();

$saferequest = cmc_safe_request_strip();
$has_error = FALSE;
$err_msg = '';

if (array_key_exists('fbid', $saferequest) && array_key_exists('tripid', $saferequest)) {
  $fbid = $saferequest['fbid'];
  $tid = $saferequest['tripid'];
}
else {
  // error case: all needed variables are not defined
  $has_error = TRUE;
  $err_msg = "Required parameters not defined.";
}

$json = array();

if (!$has_error) {

$sql = 'select * from trips where id="'.$tid.'"';
$result = mysql_query($sql,$con);
if (!$result) {
	setjsonmysqlerror($has_error,$err_msg,$sql);
}
else {

  $num_rows = mysql_num_rows($result);

  if ($num_rows > 0) {
  while($row= mysql_fetch_array($result)) {
    $name = $row['tripname'];
	if (!empty($name))
		$json['tripname'] = $name;
    $creatorid = $row['creatorid'];
	$json['creatorid'] = $creatorid;
    $sql2 = 'select * from users where userid="'.$creatorid.'"';
    $result2 = mysql_query($sql2,$con);
	if (!$result2) {
		setjsonmysqlerror($has_error,$err_msg);
		continue 1;
	}
	else {
		$row2 = mysql_fetch_array($result2);
		$towner=$row2['name'];
		if (!empty($towner))
			$json['tripowner'] = $towner;
		$tripdesc=$row['tripdesc'];
		if (!empty($tripdesc))
			$json['tripdesc'] = $tripdesc;
		$phone=$row['phone'];
		if (!empty($phone))
			$json['phone'] = $phone;
		$email=$row['email'];
		if (!empty($email))
			$json['email'] = $email;
		$web=$row['website'];		
		$dur=$row['durationid'];
		if (!empty($dur))
			$json['duration'] = $dur;
		$stage=$row['isinexecutionstage'];
		if (!empty($stage)) {
		if ($stage==0)
			$json['tripstage'] = "Planning Phase";  
		else if ($stage==1)
			$json['tripstage'] = "In Execution";  
		}
		$destination=$row['destination'];
		if (!empty($destination))
			$json['destination'] = $destination;
		$destinationcountry=$row['country'];
		if (!empty($destinationcountry))
			$json['destinationcountry'] = $destinationcountry;
		$departn = explode(' ',$row['departure']);
		$depart = explode('-',$departn[0]);
		if (!empty($depart)) {
			$json['departyear'] = $depart[0];
			$json['departmonth'] = $depart[1];
			$json['departday'] = $depart[2];
		}
		$returnn = explode(' ',$row['returning']);
		$return = explode('-',$returnn[0]);
		if (!empty($return)) {
			$json['returnyear'] = $return[0];
			$json['returnmonth'] = $return[1];
			$json['returnday'] = $return[2];
		}
		$zip=$row['zipcode'];	
		if (!empty($zip))
			$json['zip'] = $zip;
		$relg=$row['religion'];
		if (!empty($relg))
			$json['religion'] = $relg;
		$numpeople = $row['numpeople'];
		if (!empty($numpeople))
			$json['numpeople'] = $numpeople;
		$website = $row['website'];
		if (!empty($website))
			$json['website'] = $web;
	}
  }

// see if the user is already part of this trip

$sql = 'select * from tripmembers where userid="'.$fbid.'" and tripid="'.$tid.'"';
$result = mysql_query($sql,$con);

if (!$result) {
	setjsonmysqlerror($has_error,$err_msg,$sql);
}
else {

$numrows = mysql_num_rows($result);
if ($numrows>0) {
	$json['member'] = true;
}
else {
	$json['member'] = false;
}
}
}
else {
  $has_error = TRUE;
  $err_msg = "No trip identified";
}

}

}

$json['has_error'] = $has_error;

if ($has_error) {
  $json['err_msg'] = $err_msg;
}

echo json_encode($json);

?>
