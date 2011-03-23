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

?>

<?php

if (isset($_GET)) {
$tripid = $_GET['tripid'];

$sql = 'select userid from tripmembers where userid !="'.$fbid.'" and accepted="1" and tripid="'.$tripid.'"';

//echo $sql.'<br />';

if ($result = mysql_query($sql)) {
	$numrows = mysql_num_rows($result);
	if ($numrows==0) {
		$sql2 = 'select * from tripmembers where userid="'.$fbid.'" and accepted="1" and tripid="'.$tripid.'"';
		$result2 = mysql_query($sql2);
		$numrows2 = mysql_num_rows($result2);
		if ($numrows2==1) {
			echo '<br />You are the only person in this trip<br />';
			echo "<br/><a href='deletetrips.php?tripid=".$tripid."'>Delete this trip instead?</a><br/><br/>"; 
		}
	}
	else {
?>

<fb:editor
action="http://apps.facebook.com/missionsconnector/deletetripmembers.php" method='post'>
<fb:editor-custom name="TripMembers" label="Current Trip Members you would like to delete"><br/><br />

<?php

	while ($currenttripmembers = mysql_fetch_array($result,MYSQL_ASSOC)) {
	$sql = 'select name from users where userid ="'.$currenttripmembers['userid'].'"';
        //echo $sql.'<br />';
	
	$result2 = mysql_query($sql);
	$row2 = mysql_fetch_array($result2,MYSQL_ASSOC);
	echo '<label for="TripMembers">Name:'.$row2['name'].'</label><input type="radio" name="TripMembers" id="TripMembers" value="'.$currenttripmembers['userid'].'" selected />';
        }

?>

<fb:editor-button value="Submit" name="submit"/>
</fb:editor>

<?php
}
}
session_start();
$_SESSION['dtripid'] = $tripid;

}
else {
 echo 'TRIP not identified, please select trip so that trip members may be removed <br />';
 echo '<a href="welcome.php">Go back to Application home</a><br />';
}

if (isset($_POST['TripMembers'])) {

// This is the tripid selected
$tripmembers = $_POST['TripMembers'];
session_start();
$dtripid = $_SESSION['dtripid'];
// Now we can delete this member from the trip - which means updating the tripmembers table in the database

//Get Person's name
$sql = 'select name from users where userid="'.$tripmembers.'"';
$result = mysql_query($sql);
$row = mysql_fetch_array($result,MYSQL_ASSOC);

if (!empty($dtripid)) {
$sql = 'delete from tripmembers where userid="'.$tripmembers.'" and tripid="'.$dtripid.'"';
if ($result = mysql_query($sql)) {
	echo $row['name'].' has been removed from the trip members of this trip <br />';

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

	$sql2 = 'select * from trips where id="'.$dtripid.'"';
	$result2 = mysql_query($sql2);
	$row2 = mysql_fetch_array($result2,MYSQL_ASSOC);
	$message = 'Trip member: '.$row['name'].' has been deleted from the trip: '.$row2['tripname'];

	session_start();
  	
	// get a list of friends who are using the CMC app
    	//$friends=$fb->api_client->friends_getAppUsers();


	if (!isset($_SESSION['dpmsg'])) {
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
		$_SESSION['dpmsg'] = $message;
	}
	else {
		if (strcmp($message,$_SESSION['dpmsg'])) {
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
		$_SESSION['dpmsg'] = $message;
		}
	}

	$_SESSION['dtripid'] = '';

	echo "<fb:redirect url='tripoptions.php' />";
}
else {
	echo "MYSQL Error <br/>";
}
}
else {
	echo "WARNING: No trip identified. Not deleting any trip members <br /><br/>";
	echo "<a href='tripoptions.php'> Go back to trip options</a><br /><br />";
}
}

?>



