<?php
// Application: Christian Missions Connector
// File: 'trips.php'
//  shows all trips the user is a member of
//
//require_once 'facebook.php';

include_once 'common.php';
ob_start();

$fb = cmc_startup($appapikey, $appsecret,0);
$fbid = get_user_id($fb);
//$fbid = $fb->require_login("publish_stream");

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
//if (isset($_GET) && empty($_SESSION)) {
if (isset($_REQUEST['ids'])) {
 $selectedids = $_REQUEST['ids'];
 session_start();
 $tripid = $_SESSION['mytripid'];

 $sql = 'select creatorid,tripname,tripdesc,phone,email,departure,returning,zipcode from trips where id="'.$tripid.'"';
 
 $result = mysql_query($sql);
 $row = mysql_fetch_array($result,MYSQL_ASSOC);
 
 $info = $fb->api_client->users_getInfo($row['creatorid'], 'name,email');
 $record = $info[0];
 $name = $record['name'];

 $message = $name." is making a trip with following characteristics: ";
 
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
	//$depart = date('Y-m-d', $departn);
	//$depdate = explode(' ',$row['departure']);
	$message = $message.'Date of Departure: '.$newdp[1].'-'.$newdp[2].'-'.$newdp[0].' ';
}
if (!empty($row['returning'])) {
	//$return = date('m-d-Y', $row['returning']);
	$returnn = explode(' ',$row['returning']);
	$newret = explode('-',$returnn[0]);
	//$retdate = explode(' ',$row['returning']);
	$message = $message.'Date Returning: '.$newret[1].'-'.$newret[2].'-'.$newret[0].' ';
	//$message = $message.'Returning: '.$retdate[0].' ';
}
if (!empty($row['zipcode']))
	$message = $message.'Destination Zipcode: '.$row['zipcode'].' ';


 $res = $fb->api_client->users_hasAppPermission('publish_stream',null);
 if (!$res) {
?>

  <script type="text/javascript">
  Facebook.showPermissionDialog("read_stream,publish_stream,manage_pages,offline_access");
  </script>
<?php
}

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
	$result2 = mysql_query($sql2);
	$numrows = mysql_num_rows($result2);
	if ($numrows == 0) {
		$sql2 = 'insert into notifications (id,starttime,notifications) VALUES ("'.$fbid.'","'.$today.'","'.count($selectedids).'")';
		$result2 = mysql_query($sql2);
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

		$result2 = mysql_query($sql2);
	}  
  
     foreach ($selectedids as $currentfriend) {
        echo '<script type="text/javascript">';
	echo "Facebook.streamPublish('".$message."', null, null,'".$currentfriend."','',null,'true')";
	echo '</script>';
     }

	// update the database tables to reflect that these guys have been invited
	foreach($selectedids as $selected) {
		$sql = 'select * from tripwallinvites where userid="'.$selected.'" and tripid="'.$tripid.'"';
		$result = mysql_query($sql);
		$numrows = mysql_num_rows($result);
		if ($numrows == 0) {
		  $sql = 'INSERT into tripwallinvites (userid, tripid) VALUES ("'.$selected.'","'.$tripid.'")';
		  $result = mysql_query($sql);
		}
	}  
  }
  echo '<a href="welcome.php">Go back to Application home</a><br />';

}
/*
else {
        session_start();
	if (isset($_SESSION['fselect'])) {
	        echo 'SESSION = '.$_SESSION['fselect'].'<br />';
		unset($_SESSION['fselect']);
		//echo "<fb:redirect url='profile.php' />";
	}
}
*/

if (isset($_GET['tripid'])) {

// This is the tripid selected
$tripid = $_GET['tripid'];
session_start();
$_SESSION['mytripid'] = $tripid;

/*
$sql = 'select isadmin from tripmembers where userid="'.$fbid.'" and tripid="'.$tripid.'"';
if ($result = mysql_query($sql)) {
	$row = mysql_fetch_array($result);
	$isadmin = $row['isadmin'];
}

if (!$isadmin) {
 echo 'You are not a trip administrator; therefore you cannot share this trip with others <br />';
 echo '<a href="welcome.php">Go back to Application home</a><br />';
}
else {
*/

// first determine if the user is an admin of this trip of not
// If not, they cannot invite and the page should display this error and redirect the user

$app_url="missionsconnector/profile.php?type=trip";
$app_name="Christian Missions Connector Trip Messages";

   $sql = 'select * from notifications where id="'.$fbid.'"';
   if ($result = mysql_query($sql)) {
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
	echo '<br/><br/>You have already sent more than 100 invitations for today, you cannot send any more <br /><br />';
	echo '<a href="welcome.php">Go back to Application home</a><br />';
   }
   else {

   // Retrieve array of friends who've already authorized the app.
   $fql = 'SELECT uid FROM user WHERE uid IN (SELECT uid2 FROM friend WHERE uid1='.$fbid.') AND is_app_user = 1';
   $_friends = $fb->api_client->fql_query($fql);
   // Extract the user ID's returned in the FQL request into a new array.
   $friends = array();
   if (is_array($_friends) && count($_friends)) {
	foreach ($_friends as $friend) {
	if ($friend['uid'] != 0)
		$friends[] = $friend['uid'];
	}
   }
   
   $sql = 'select userid from tripwallinvites where tripid="'.$tripid.'"';
   $myfriends=array();
   if ($result = mysql_query($sql)) {
	while ($invitedfriends = mysql_fetch_array($result,MYSQL_ASSOC)) {
		$myfriends[] = $invitedfriends['userid'];
	}
   }   
   
   $myfriends = implode(',', $myfriends);
   
   // Get some information about the trip so that this information can be passed on in the invite
   $sql = 'select tripname,tripdesc,destination,departure,returning,religion from trips where id="'.$tripid.'"';
   if ($result = mysql_query($sql)) {
	$row = mysql_fetch_array($result);
   }
   // Convert the array of friends into a comma-delimeted string.
   $friends = implode(',', $friends);

   // Prepare the invitation text that all invited users will receive.
   $content = "<fb:name uid=\"".$fbid."\" firstnameonly=\"true\" shownetwork=\"false\"/> has created a trip and would like to share a trip related message"."\n";

   //$invite_href = "invitetotrip.php?tripid=".$tripid;
   $invite_href = "share.php";
   session_start();
   $_SESSION['fselect'] = 1;

   /*
   if ($value==0)
   	$invite_href = "inviteupdate.php?tripid=".$tripid."&admin=1";
   else
        $invite_href = "inviteupdate.php?tripid=".$tripid."&admin=0";
   */


?>

<fb:request-form action="<?php echo $invite_href; ?>" method="post" type="<?php echo $app_name; ?>" content="<?php echo htmlentities($content,ENT_COMPAT,'UTF-8'); ?>"
<fb:multi-friend-selector actiontext="Here are your friends with whom you can share trip related messages" exclude_ids="<?php echo $myfriends; ?>" />
</fb:request-form>


<?php
//}
}
}

?>

