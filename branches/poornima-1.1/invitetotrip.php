<?php
// Application: Christian Missions Connector
// File: 'trips.php'
//  shows all trips the user is a member of
//
//require_once 'facebook.php';

include_once 'common.php';
ob_start();

$fb = cmc_startup($appapikey, $appsecret,0);
$fbid = $fb->require_login("publish_stream");

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
   if (isset($_REQUEST['ids'])) {
	$selectedids = $_REQUEST['ids'];
 	
	session_start();
  	$tripid = $_SESSION['imytripid'];

	//$tripid = $_GET['tripid'];
	
	//echo 'TRIPID ='.$tripid.'<br />';

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

	//print_r($selectedids);
	// update the database tables to reflect that these guys have been invited
	foreach($selectedids as $selected) {
		$sql = 'select userid from tripmembers where userid="'.$selected.'" and tripid="'.$tripid.'"';
		  //echo $sql.'<br />';
		
		$result = mysql_query($sql);
		$numrows = mysql_num_rows($result);
		if ($numrows > 0) {
		  $sql = 'UPDATE tripmembers set invited="1" where userid="'.$selected.'" and tripid="'.$tripid.'"';
		  $result = mysql_query($sql);
		}
		else {
		  $sql = 'INSERT into tripmembers (userid, tripid,invited) VALUES ("'.$selected.'","'.$tripid.'","1")';

		  //echo $sql.'<br />';
		  $result = mysql_query($sql);
		}
	}
	
	

   }

// obtain the get parameters
$value = $_GET['value'];

// default values
$asadmin = 0;
$deletetrip = 0;
$deleteothers = 0;
$inviteothers = 0;
$searchtrip = 0;

if ($value ==0)
  $asadmin = 1;
else if ($value == 1)
  $inviteothers = 1;
else if ($value == 2)
  $deleteothers = 1;
else if ($value == 3)
  $deletetrip = 1;
else if ($value == 4)
  $updatetrip = 1;
else if ($value == 5)
  $searchtrip = 1;

echo '<fb:editor
action="http://apps.facebook.com/missionsconnector/invitetotrip.php?value='.$value.'" method="post">'
?>

<?php
//<fb:editor-custom name="Trips" label="Trips"><br/>
?>

<?php
//if (isset($_GET) && empty($_SESSION)) {
if (isset($_GET)) {
// get which trip - list all trips that this user is admin of
// Then the trip that needs further action is selected.

$sql = 'select tripid from tripmembers where userid="'.$fbid.'"';

$result = mysql_query($sql);
$j=1;
$numrows = mysql_num_rows($result);
if ($numrows==0) {
  echo '<br /><b>You are not member or administrator of any trip. <b/> <br />';
  echo "If you want to create a trip,<a href='http://apps.facebook.com/missionsconnector/makeprofile.php?type=trip'>click here</a><br /><br />";
}
else {

echo '<fb:editor-custom name="Trips" label="Trips"><br/>';

while ($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
	$sql = 'select tripname,tripdesc,destination,departure,returning,zipcode from trips where id="'.$row['tripid'].'"';
	$result2 = mysql_query($sql);
	$row2 = mysql_fetch_array($result2,MYSQL_ASSOC);
	echo '<label for="Trips">Trip'.$j.':'.$row2['tripname'].'</label><input type="radio" name="Trips" id="Trips" value="'.$row['tripid'].'" selected /><br/><br/>';
	$j++;
}

//echo 'MYVALUE0:'.$value.'del:'.$deleteothers.'<br>';
echo '<fb:editor-buttonset>';
echo '<fb:editor-button value="Submit" name="submit"/>';
echo '</fb:editor-buttonset>';

//echo '<fb:editor-button value="Submit" name="submit"/>';
echo '</fb:editor>';
}
}
if (isset($_POST['Trips'])) {

// This is the tripid selected
$tripid = $_POST['Trips'];

	session_start();
	$_SESSION['imytripid'] = $tripid;

//echo 'MYVALUE:'.$value.'tripid:'.$tripid.'<br>';

//echo 'TRIP ID selected ='.$tripid.'<br />';

$sql = 'select isadmin from tripmembers where userid="'.$fbid.'" and tripid="'.$tripid.'"';
if ($result = mysql_query($sql)) {
	$row = mysql_fetch_array($result);
	$isadmin = $row['isadmin'];
}

if ((!$isadmin) && (($value==2) || ($value==3) || ($value==4))) {
 echo '<br /> You are not a trip administrator; therefore you cannot delete others on this trip <br />';
 echo '<a href="welcome.php">Go back to Application home</a><br />';
}
else {
if (($value==0) || ($value==1)) {

// first determine if the user is an admin of this trip of not
// If not, they cannot invite and the page should display this error and redirect the user

$app_url="missionsconnector/profile.php?type=trip";
$app_name="Christian Missions Connector Trips";

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

   }
   else {
   
   // Retrieve array of friends who've already authorized the app.
   $fql = 'SELECT uid FROM user WHERE uid IN (SELECT uid2 FROM friend WHERE uid1='.$fbid.') AND is_app_user = 1';
   $_friends = $fb->api_client->fql_query($fql);
   // Extract the user ID's returned in the FQL request into a new array.
   $friends = array();
   if (is_array($_friends) && count($_friends)) {
	foreach ($_friends as $friend) {
	$friends[] = $friend['uid'];
	}
   }
   
   // have some logic here to remove friends to whom invitations have already been sent
   //echo $fbid.'<br />';
   $sql = 'select userid from tripmembers where userid !="'.$fbid.'" and invited="1" and tripid="'.$tripid.'"';
   //$invitedfriends = array();
   $myfriends=array();
   if ($result = mysql_query($sql)) {
	while ($invitedfriends = mysql_fetch_array($result,MYSQL_ASSOC)) {
		$myfriends[] = $invitedfriends['userid'];
	}
   }
   //$myfriends = $invitedfriends['userid'];
   
   // Get some information about the trip so that this information can be passed on in the invite
   $sql = 'select tripname,tripdesc,destination,departure,returning,religion from trips where id="'.$tripid.'"';
   if ($result = mysql_query($sql)) {
	$row = mysql_fetch_array($result);
   }

   // Convert the array of friends into a comma-delimeted string.
   $friends = implode(',', $friends);

   //if (count($myfriends)>1)
   $myfriends = implode(',', $myfriends);

   //print_r($friends);


   // Prepare the invitation text that all invited users will receive.
   $content = "<fb:name uid=\"".$fbid."\" firstnameonly=\"true\" shownetwork=\"false\"/> has created a trip <a href=\"http://apps.facebook.com/".$app_url."/\">".$app_name."</a> and would like to invite you to a trip with following characteristics: <br /> Description:".$row['tripdesc']."<br /> Destination:".$row['destination']."<br /> Departure:".$row['departure']."<br /> Returning:".$row['returning']."<br /> Religion:".$row['religion']."\n". "<fb:req-choice url=\"http://apps.facebook.com/missionsconnector/tripaccept.php?id=".$tripid."&admin=".$asadmin."\" label=\"Add this trip on your profile\"/> <fb:req-choice url=\"http://apps.facebook.com/missionsconnector/tripreject.php?id=".$tripid."&admin=".$asadmin."\" label=\"Reject this trip invitation\"/>";

   //$invite_href = "invitetotrip.php?tripid=".$tripid;
   $invite_href = "invitetotrip.php";
   /*
   if ($value==0)
   	$invite_href = "inviteupdate.php?tripid=".$tripid."&admin=1";
   else
        $invite_href = "inviteupdate.php?tripid=".$tripid."&admin=0";
   */

?>

<fb:request-form action="<?php echo $invite_href; ?>" method="post" type="<?php echo $app_name; ?>" content="<?php echo htmlentities($content,ENT_COMPAT,'UTF-8'); ?>">
<fb:multi-friend-selector actiontext="Here are your friends whom you can invite to the trip" exclude_ids="<?php echo $myfriends; ?>" />
</fb:request-form>


<?php
}
}
else if ($value == 2) { // remove others from trip

// If the run comes here, that means the user is already an administrator

// Get all users who have accepted this trip 
   echo "<fb:redirect url='deletetripmembers.php?tripid=".$tripid."' />";
   /*
   $sql = 'select userid from tripmembers where userid !="'.$fbid.'" and accepted="1" and tripid="'.$tripid.'"';
   //$invitedfriends = array();
   if ($result = mysql_query($sql)) {
	$currenttripmembers = mysql_fetch_array($result,MYSQL_ASSOC);
   }
   $tripmembers = $currenttripmembers['userid'];
   */

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

}
}

 // get a list of users that are using this application
/*
 $friends=$fb->api_client->friends_getAppUsers();

 if (empty($friends)) {
  // In this case users should also be sent an invitation to join the Christian Mission Connector
  }
  else {
   foreach ($friends as $currentfriend){
           echo "<fb:profile-pic uid=".$currentfriend." linked='false' />  <fb:name uid=".$currentfriend." linked='false' shownetwork='true'/><a href='http://apps.facebook.com/missionsconnector/profile.php?userid=".$currentfriend."'><br/>  See Profile</a><br/><br/>";
}
*/


?>

