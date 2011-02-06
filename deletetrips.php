<?php
// Application: Christian Missions Connector
// File: 'trips.php'
//  shows all trips the user is a member of
//
//require_once 'facebook.php';

include_once 'common.php';
ob_start();

$fb = cmc_startup($appapikey, $appsecret,0);
$fbid = $fb->require_login("publish_stream,read_stream");

?>

<?php

if (isset($_GET)) {
$tripid = $_GET['tripid'];

$sql = 'select * from trips where id="'.$tripid.'"';
$result = mysql_query($sql);
$row = mysql_fetch_array($result,MYSQL_ASSOC);
$tripname = $row['tripname'];
mysql_free_result($result);

// first we need to delete the tripmembers for this trip
$sql = 'delete from tripmembers where tripid="'.$tripid.'"';
//$result = mysql_query($sql);

// first delete the row from trips table
//echo $sql.'<br />';

if ($result = mysql_query($sql)) {
  //echo '<br /><b>Trip Members Deleted, Now deleting the trip itself <b/> <br /><br />';
  //$sql = 'delete from tripmembers where tripid="'.$tripid.'"';
  
  $sql = 'delete from trips where id="'.$tripid.'"';
  if ($result = mysql_query($sql)) {
	echo '<b>Your Trip has been successfully Deleted<b/><br /><br />';
	echo '<a href="advancedsearch.php">Please take time to search for other trips</a> <br /><br />';
	echo '<a href="makeprofile.php?type=trip">Would you like to create a new trip</a> <br /><br />';
	//echo '<a href="tripoptions.php">Go back to Trip Options</a><br />';
  }
  else {
	echo '<b>Could not delete the trip <b/><br />';
  	//echo "<fb:redirect url='tripoptions.php' />";
  }

  // now update recent activity
  $res = $fb->api_client->users_hasAppPermission('publish_stream',null);
  if (!$res) {
  ?>

  <script type="text/javascript">
	Facebook.showPermissionDialog("read_stream,publish_stream,manage_pages,offline_access");
  </script>
<?php
}
 
 $info = $fb->api_client->users_getInfo($fbid, 'name', 'email');
 $record = $info[0];
 $name = $record['name'];
 $message = 'The trip: '.$tripname.' has been deleted by: '.$name;

 //$fb->api_client->stream_publish($message,null,null,$appid,$appid);
 
  session_start();
  // get a list of friends who are using the CMC app
    //$friends=$fb->api_client->friends_getAppUsers();


  if (!isset($_SESSION['pmsg'])) {
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


  $_SESSION['pmsg'] = $message;
  }
  else {
  	if (strcmp($message,$_SESSION['pmsg'])) {
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
  	$_SESSION['pmsg'] = $message;  
  	}
  } 
 
 
}
else {
	echo '<b>Could not delete any trip members <b/><br />';
	//echo "<fb:redirect url='tripoptions.php' />";
}

}
/*
else {
 echo '<br/><b>TRIP not identified, please select trip so that trip members may be removed <b/><br />';
 echo '<a href="welcome.php">Go back to Application home</a><br />';
}
*/
?>



