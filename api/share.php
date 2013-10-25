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

if (array_key_exists('ids', $saferequest) && array_key_exists('fbid', $saferequest) && array_key_exists('tripid', $saferequest)) {
  // invitation ids, tripid and facebook userid should be provided
  $tripid = $saferequest['tripid'];
  $fbid = $saferequest['fbid'];
  $selectedids = $saferequest['ids'];
} 
else if (array_key_exists('fbid', $saferequest) && array_key_exists('tripid', $saferequest)) {
  $tripid = $saferequest['tripid'];
  $fbid = $saferequest['fbid'];
}
else {
  // error case: all needed variables are not defined
  $has_error = TRUE;
  $err_msg = "Required parameters not defined.";
}

$json = array();


function getdatestring($year,$month,$date,$hour,$min,$sec) {

if ($month<10)
	$smonth = strval($month);
else
	$smonth = strval($month);

if ($date<10)
	$sdate = strval($date);
else
	$sdate = strval($date);

$res = $year.'-'.$smonth.'-'.$sdate.' '.strval($hour).':'.strval($min).':'.strval($sec);

return $res;
}

function timeDiff($firstTime,$lastTime)
{

// convert to unix timestamps
$firstTime=strtotime($firstTime);
$lastTime=strtotime($lastTime);

// perform subtraction to get the difference (in seconds) between times
$timeDiff=$lastTime-$firstTime;

// return the difference in number of days
return $timeDiff/86400;
}

?>

<?php

if (isset($selectedids)) {

 $sql = 'select creatorid,tripname,tripdesc,phone,email,departure,returning,zipcode from trips where id="'.$tripid.'"';
 
 $result = $con->query($sql);
 if (!result) {
 	setjsonmysqlerror($has_error,$err_msg,$sql);
 }
 else {
 $row = $result->fetch_array();

 $name = get_name_from_fb_using_curl($row['creatorid']);

 $message = $name." created a trip with following characteristics: ";
 
 if (!empty($row['tripname']))
	$message = $message.'Name: '.$row['tripname'].' ';
if (!empty($row['tripdesc']))
	$message = $message.'Trip Description: '.$row['tripdesc'].' ';
if (!empty($row['phone']))
	$message = $message.'Contact Phone: '.$row['phone'].' ';
if (!empty($row['email']))
	$message = $message.'Contact Email: '.$row['email'].' ';
if (!empty($row['departure'])) {
	$departn = explode(' ',$row['departure']);
	$newdp = explode('-',$departn[0]);
	$message = $message.'Date of Departure: '.$newdp[1].'-'.$newdp[2].'-'.$newdp[0].' ';
}
if (!empty($row['returning'])) {
	$returnn = explode(' ',$row['returning']);
	$newret = explode('-',$returnn[0]);
	$message = $message.'Date Returning: '.$newret[1].'-'.$newret[2].'-'.$newret[0].' ';
}
if (!empty($row['zipcode']))
	$message = $message.'Destination Zipcode: '.$row['zipcode'].' ';

  if (!empty($selectedids)) {
  
	// update notifications table
	$todayy = date("Y");
	$todaym = date("m");
	$todayd = date("d");
	$todayH = date("H");
	$todayi = date("i");
	$todays = date("s");

	$today = getdatestring($todayy,$todaym,$todayd,$todayH,$todayi,$todays);
	
	$sql2 = 'select * from notifications where id="'.$fbid.'"';
	$result2 = $con->query($sql2);
	if (!$result2) {
		setjsonmysqlerror($has_error,$err_msg,$sql2);
	}
	else {
		if ($result->num_rows == 0) {
			$sql2 = 'insert into notifications (id,starttime,notifications) VALUES ("'.$fbid.'","'.$today.'","'.count($selectedids).'")';
			$result2 = $con->query($sql2);
			if (!$result2) {
				setjsonmysqlerror($has_error,$err_msg,$sql2);
			}
		}
		else {
			$row = $result2->fetch_array();
			$notifications = $row['notifications'];
			$starttime = $row['starttime'];
	
			// If time difference is greater than 1 day, reset notifications and starttime
			if (timeDiff($starttime,$today) > 1) {
				$notifications = 0;
				$sql2 = 'update notifications set notifications="'.$notifications.'", starttime="'.$today.'" where id="'.$fbid.'"';
			}
			else {
				// now update notifications or reset depending on time
				$notifications = $notifications + count($selectedids);

				$sql2 = 'update notifications set notifications="'.$notifications.'" where id="'.$fbid.'"';
			}

			$result2 = $con->query($sql2);
			if (!$result2) {
				setjsonmysqlerror($has_error,$err_msg,$sql2);
			}
		}  
		if (!$has_error) {
			// update the database tables to reflect that these guys have been invited
			foreach($selectedids as $selected) {
				$sql = 'select * from tripwallinvites where userid="'.$selected.'" and tripid="'.$tripid.'"';
				$result = $con->query($sql);
				if (!$result) {
					setjsonmysqlerror($has_error,$err_msg,$sql);
				}
				else {
					if ($result->num_rows == 0) {
						$sql = 'INSERT into tripwallinvites (userid, tripid) VALUES ("'.$selected.'","'.$tripid.'")';
						$result = $con->query($sql);
						if (!$result) {
							setjsonmysqlerror($has_error,$err_msg,$sql);
						}
					}
				}
			}  
		}
	}
}
}

if (!$has_error) {
	$json['message'] = $message;
}
}

else {

   $sql = 'select * from notifications where id="'.$fbid.'"';
   $result = $con->query($sql);
   
	if (!$result) {
		setjsonmysqlerror($has_error,$err_msg,$sql);	
	}
	else {
		if ($result->num_rows > 0) {
			$row = $result->fetch_array();
			$notifications = $row['notifications'];
		}
		else {
			$notifications = 0;
		}
	}   
   
    if ($notifications > 100) {
		$has_error = TRUE;
		$err_msg = 'You have already sent more than 100 invitations for today, you cannot send any more';
	}
	else {

   		$sql = 'select userid from tripwallinvites where tripid="'.$tripid.'"';
		$myfriends=array();
		$result = $con->query($sql);
		if (!$result) {
			setjsonmysqlerror($has_error,$err_msg,$sql);
		}
		else {
			while ($invitedfriends = $result->fetch_array()) {
				$myfriends[] = $invitedfriends['userid'];
			}
			$json['invitedfriends'] = $myfriends;
		}
	}
}

$json['has_error'] = $has_error;

if ($has_error) {
  $json['err_msg'] = $err_msg;
}

echo json_encode($json);

?>

