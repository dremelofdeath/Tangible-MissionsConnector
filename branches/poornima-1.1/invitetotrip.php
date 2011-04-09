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

// return the difference
return $timeDiff/86400;
}


   // Modify the trip members table based on user invitations
   if (isset($selectedids)) {
 	
	$todayy = date("Y");
	$todaym = date("m");
	$todayd = date("d");
	$todayH = date("H");
	$todayi = date("i");
	$todays = date("s");

	$today = getdatestring($todayy,$todaym,$todayd,$todayH,$todayi,$todays);
	
	$sql2 = 'select * from notifications where id="'.$fbid.'"';
	$result2 = mysql_query($sql2,$con);
	if (!$result2) {
	    $has_error = TRUE;
		$err_msg = "Can't query (query was '$query'): " . mysql_error();
	}
	else {
		$numrows = mysql_num_rows($result2);
		if ($numrows == 0) {
			$sql2 = 'insert into notifications (id,starttime,notifications) VALUES ("'.$fbid.'","'.$today.'","'.count($selectedids).'")';
			$result2 = mysql_query($sql2,$con);
			if (!$result2) {
				$has_error = TRUE;
				$err_msg = "Can't query (query was '$query'): " . mysql_error();
			}
		}
		else {
			$row = mysql_fetch_array($result2);
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

			$result2 = mysql_query($sql2,$con);
			if (!$result2) {
				$has_error = TRUE;
				$err_msg = "Can't query (query was '$query'): " . mysql_error();
			}
		}

		// update the database tables to reflect that these guys have been invited
		foreach($selectedids as $selected) {
			$sql = 'select userid from tripmembers where userid="'.$selected.'" and tripid="'.$tripid.'"';
			$result = mysql_query($sql,$con);
			
			if (!$result) {
				$has_error = TRUE;
				$err_msg = "Can't query (query was '$query'): " . mysql_error();
			}
			else {
				$numrows = mysql_num_rows($result);
				if ($numrows > 0) {
					$sql = 'UPDATE tripmembers set invited="1" where userid="'.$selected.'" and tripid="'.$tripid.'"';
					$result = mysql_query($sql,$con);
					if (!$result) {
						$has_error = TRUE;
						$err_msg = "Can't query (query was '$query'): " . mysql_error();
					}
				}
				else {
					$sql = 'INSERT into tripmembers (userid, tripid,invited) VALUES ("'.$selected.'","'.$tripid.'","1")';
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
   else {

	$sql = 'select * from notifications where id="'.$fbid.'"';
	$result = mysql_query($sql,$con);
	if (!$result) {
		$has_error = TRUE;
		$err_msg = "Can't query (query was '$query'): " . mysql_error();	
	}
	else {
		$numrows = mysql_num_rows($result);
		if ($numrows > 0) {
			$row = mysql_fetch_array($result,MYSQL_ASSOC);
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
   
		// Retrieve array of friends who've already authorized the app - this should come from the front-end
		/*
		$friends = array();
		if (is_array($appfriends) && count($appfriends)) {
			foreach ($appfriends as $friend) {
				$friends[] = $friend['uid'];
			}
		}
		*/
		
		$sql = 'select userid from tripmembers where userid !="'.$fbid.'" and invited="1" and tripid="'.$tripid.'"';
		$myfriends=array();
		$result = mysql_query($sql,$con);
		if (!$result) {
			$has_error = TRUE;
			$err_msg = "Can't query (query was '$query'): " . mysql_error();
		}
		else {
			while ($invitedfriends = mysql_fetch_array($result,MYSQL_ASSOC)) {
				$myfriends[] = $invitedfriends['userid'];
			}
			$json['invitedfriends'] = $myfriends;
		}
   }
   
   // Get some information about the trip so that this information can be passed on in the invite
   $sql = 'select tripname,tripdesc,destination,departure,returning,religion from trips where id="'.$tripid.'"';
   $result = mysql_query($sql,$con);
   if (!$result) {
		$has_error = TRUE;
		$err_msg = "Can't query (query was '$query'): " . mysql_error();
   }
   else {
	$row = mysql_fetch_array($result);
	$json['tripname'] = $row['tripname'];
	$json['tripdesc'] = $row['tripdesc'];
	$json['destination'] = $row['destination'];
	$json['departure'] = $row['departure'];
	$json['returning'] = $row['returning'];
	$json['religion'] = $row['religion'];
   }

}
/*
else if ($value == 2) { // remove others from trip
   echo "<fb:redirect url='deletetripmembers.php?tripid=".$tripid."' />";
}
else if ($value == 3) { // delete trip
  echo "<fb:redirect url='deletetrips.php?tripid=".$tripid."' />";
}
else if ($value == 4) { // update trip
  echo "<fb:redirect url='makeprofile.php?type=trip&edit=1&update=".$tripid."' />";
}
else if ($value == 5) { // remove self from a trip
  echo "<fb:redirect url='removeself.php?tripid=".$tripid."' />";
}
*/

$json['has_error'] = $has_error;

if ($has_error) {
  $json['err_msg'] = $err_msg;
}

echo json_encode($json);

?>

