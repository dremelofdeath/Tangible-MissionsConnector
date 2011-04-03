<?php
// Application: Christian Missions Connector
// File: 'trips.php' 
//  shows all trips the user is a member of
// 
//require_once 'facebook.php';

include_once 'common.php';

$fb = cmc_startup($appapikey, $appsecret,0);
$fbid = get_user_id($fb);
//$fbid = $fb->require_login("publish_stream");

echo '<b>You have the following trip options: </b><br /><br />';

?>
<br/><br/>

<h1><a href="makeprofile.php?type=trip"> Create a Missions Trip </a></h1><br /><b
r /><br/>

<h1><a href="invitetotrip.php?value=1"> Invite Others to Trips </a></h1><br /><br />
<h1><a href="invitetotrip.php?value=0"> Invite Others to Trips as Trip Administrators </a></h1><br /><br />

<h1><a href="invitetotrip.php?value=4"> Update Trips </a></h1><br /><br />

<h1><a href="invitetotrip.php?value=2"> Remove Others from Trips </a></h1><br /><br />
<h1><a href="invitetotrip.php?value=3"> Delete Trips </a></h1><br /><br />

<h1><a href="invitetotrip.php?value=5"> Remove yourself from a trip </a></h1><br /><br />

<h1><a href="searchtrips.php"> Search Upcoming Trips </a></h1><br /><br />
<h1><a href="tripactivity.php"> View Recent Trip Activity </a></h1><br /><br />

<br/><br/>



<?php 
//<fb:feed title="Trips News Feed" max="10"/>
/*
 $app_name="Christian Missions Connector"; $app_url="http://tangiblesoft.net/missionsconnector"; 

//$con = mysql_connect(localhost,"arena", "***arena!password!getmoney!getpaid***");
$con = mysql_connect(localhost,"poornima", "MYdata@1");
	if(!$con)
	{
		die('Could not connect: ' .  mysql_error());
	}
	$trips=array();
	mysql_select_db("missionsconnector", $con);
	$sql = "select tripid from tripmembers where userid='".$fbid."'";
if($result = mysql_query($sql)){
	while($row= mysql_fetch_array($result)){
		$trips = $row['tripid'];
	}}
 foreach ($trips as $currenttrip){
	 $sql = "select tripname,tripid from trips where tripid='".$tripid."'";
if($result = mysql_query($sql)){
	while($row= mysql_fetch_array($result)){
		$trips = $row['tripid']['tripname'];
	}}
echo "A";
	echo "<a href='profileT.php?tripid=".$tripid."'>".$tripname."</a><br/><br/>";
	}
*/

?>
