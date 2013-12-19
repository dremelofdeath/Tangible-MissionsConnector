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

if (array_key_exists('tripid', $saferequest) && array_key_exists('fbid', $saferequest) && array_key_exists('isadmin', $saferequest) && array_key_exists('type', $saferequest)) {
  // invitation ids, tripid and facebook userid should be provided
  $isadmin = $saferequest['isadmin'];
  $fbid = $saferequest['fbid'];
  $tripid = $saferequest['tripid'];
  $membertype = $saferequest['type'];
  if (($membertype < 1) || ($membertype > 3)) {
    $has_error = TRUE;
    $err_msg = "Member type can only be 1 or 2 or 3";
  }
} 
else {
  // error case: all needed variables are not defined
  $has_error = TRUE;
  $err_msg = "Required parameters not defined.";
}

$json = array();

if (!$has_error) {

$today = date("F j, Y");

$sql = 'select userid from tripmembers where userid="'.$fbid.'" and tripid="'.$tripid.'"';
$result = $con->query($sql);

if (!$result) {
 	setjsonmysqlerror($has_error,$err_msg,$sql,$con);
}
else {
	if ($result->num_rows > 0) {
 	$sql = 'update tripmembers set accepted="1", type="'.$membertype.'", datejoined="'.$today.'", isadmin="'.$isadmin.'" where userid="'.$fbid.'" and tripid="'.$tripid.'"';
 		$result = $con->query($sql);
 		if (!$result) {
 			setjsonmysqlerror($has_error,$err_msg,$sql,$con);
		}

		if (!$has_error) {
		// now update number of people in trips table
 		$sql = 'select numpeople from trips where id="'.$tripid.'"';
		$result = $con->query($sql);
 		if ($result) {
 			$row = $result->fetch_array();
			$numpeople = $row['numpeople']+0;

 			// increment the number of people in this trip
 			$numpeople++;
 			$sql = 'update trips set numpeople="'.$numpeople.'" where id="'.$tripid.'"';
 			$result = $con->query($sql);
			if (!$result) {
 				setjsonmysqlerror($has_error,$err_msg,$sql,$con);
			}
 		}
		else 
			setjsonmysqlerror($has_error,$err_msg,$sql,$con);

		} // has_error

	}
	else {

   		// first check that the user has a CMC profile - otherwise let the user know that he/she needs to create a CMC profile first
   		$sql = 'select * from users where userid="'.$fbid.'"';
   		$result = $con->query($sql);
		if (!$result) {
			setjsonmysqlerror($has_error,$err_msg,$sql,$con);
		}

		if (!$has_error) {
   		if ($result->num_rows==0) {
			// This means user does not have a CMC profile
			$has_error = TRUE;
			$err_msg = "User does not have a CMC profile";
   		}
   		else {


			$sql = 'insert into tripmembers (userid, tripid, isadmin, invited, accepted, type, datejoined) VALUES ("'.$fbid.'","'.$tripid.'","'.$isadmin.'","1","1","'.$membertype.'","'.$today.'")';

			$result = $con->query($sql);
			if ($result) {
			// now update number of people in trips table

 			$sql = 'select numpeople from trips where id="'.$tripid.'"';
			$result = $con->query($sql);
  			if ($result) {
  				$row = $result->fetch_array();
				$numpeople = $row['numpeople']+0;

  				// increment the number of people in this trip
  				$numpeople++;
  				$sql = 'update trips set numpeople="'.$numpeople.'" where id="'.$tripid.'"';
  				$result = $con->query($sql);
				if (!$result) {
 					setjsonmysqlerror($has_error,$err_msg,$sql,$con);
				}
  			}
			else {
				setjsonmysqlerror($has_error,$err_msg,$sql,$con);
			}
			}
			else {
				setjsonmysqlerror($has_error,$err_msg,$sql,$con);
			}

		}
		} // has_error
	}
}

}

$json['has_error'] = $has_error;

if ($has_error) {
  $json['err_msg'] = $err_msg;
}

echo json_encode($json);

?>
