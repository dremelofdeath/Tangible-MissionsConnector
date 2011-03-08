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

function pubmessage($fb,$fbid,$tripid,$appid) {
 
 $info = $fb->api_client->users_getInfo($fbid, 'name', 'email');
 $record = $info[0];
 $name = $record['name'];
 
 $sql2 = 'select * from trips where id="'.$tripid.'"';
 $result2 = mysql_query($sql2);
 $row2 = mysql_fetch_array($result2,MYSQL_ASSOC);
 $message = $name.' has joined the trip:'.$row2['tripname'];
 
  session_start();
  
  // get a list of friends who are using the CMC app
  //$friends=$fb->api_client->friends_getAppUsers();

  if (!isset($_SESSION['tamsg'])) {
  $fb->api_client->stream_publish($message,null,null,$appid,$appid);
        /*
	if (!empty($friends)) {
	foreach ($friends as $currentfriend) {
	echo '<script type="text/javascript">';
	echo 'Facebook.streamPublish("'.$message.'", null, null,"'.$currentfriend.'","",null,true)';
	echo '</script>';
	}
	}
	*/
  $_SESSION['tamsg'] = $message;
  }
  else {
  	if (strcmp($message,$_SESSION['tamsg'])) {
  	$fb->api_client->stream_publish($message,null,null,$appid,$appid);
        /*
	if (!empty($friends)) {
	foreach ($friends as $currentfriend) {
	echo '<script type="text/javascript">';
	echo 'Facebook.streamPublish("'.$message.'", null, null,"'.$currentfriend.'","",null,true)';
	echo '</script>';
	}
	}
	*/
  	$_SESSION['tamsg'] = $message;  
  	}
  } 
  
}

if (!empty($_GET)) {

$tripid = $_GET['id'];
$isadmin = $_GET['admin'];
$today = date("F j, Y");

$sql = 'select userid from tripmembers where userid="'.$fbid.'" and tripid="'.$tripid.'"';
$result = mysql_query($sql);
$numrows = mysql_num_rows($result);

if ($numrows > 0) {
 $sql = 'update tripmembers set accepted="1", datejoined="'.$today.'", isadmin="'.$isadmin.'" where userid="'.$fbid.'" and tripid="'.$tripid.'"';
 $result = mysql_query($sql);
 
	// Does the user have permission to publish their messages
	// If not, they should be prompted to allow access
	$res = $fb->api_client->users_hasAppPermission('publish_stream',null);

	if (!$res) {
	?>

	<script type="text/javascript">
	Facebook.showPermissionDialog("read_stream,publish_stream,manage_pages,offline_access");
	</script>


<?php
}
 
 pubmessage($fb,$fbid,$tripid,$appid);
 
// now update number of people in trips table

 $sql = 'select numpeople from trips where id="'.$tripid.'"';
 if ($result = mysql_query($sql)) {
 	$row = mysql_fetch_array($result,MYSQL_ASSOC);
	$numpeople = $row['numpeople']+0;
 }
 // increment the number of people in this trip
 $numpeople++;
 $sql = 'update trips set numpeople="'.$numpeople.'" where id="'.$tripid.'"';
 $result = mysql_query($sql);

 //mysql_free_result($result);

}
else {

$sql = 'insert into tripmembers (userid, tripid, isadmin, invited, accepted, datejoined) VALUES ("'.$fbid.'","'.$tripid.'","'.$isadmin.'","1","1","'.$today.'")';

//echo $sql.'<br >';

$result = mysql_query($sql);

// now update number of people in trips table

 $sql = 'select numpeople from trips where id="'.$tripid.'"';
  if ($result = mysql_query($sql)) {
  	$row = mysql_fetch_array($result,MYSQL_ASSOC);
	$numpeople = $row['numpeople']+0;
  }
  // increment the number of people in this trip
  $numpeople++;
  $sql = 'update trips set numpeople="'.$numpeople.'" where id="'.$tripid.'"';
  $result = mysql_query($sql);
  
	// Does the user have permission to publish their messages
	// If not, they should be prompted to allow access
	$res = $fb->api_client->users_hasAppPermission('publish_stream',null);

	if (!$res) {
	?>

	<script type="text/javascript">
	Facebook.showPermissionDialog("read_stream,publish_stream,manage_pages,offline_access");
	</script>


<?php
}

  pubmessage($fb,$fbid,$tripid,$appid);

}

echo "<fb:redirect url='welcome.php' />";

}
?>
