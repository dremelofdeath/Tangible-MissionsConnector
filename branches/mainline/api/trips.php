<?php
// Application: Christian Missions Connector
// File: 'trips.php' 
//  shows all trips the user is a member of
// 
//require_once 'facebook.php';

include_once 'common.php';

$fb = cmc_startup($appapikey, $appsecret);
$fbid = get_user_id($fb);
//$fbid = $fb->require_login();

?>
<br/><br/>


<h1><a href="makeProfileT.php"> Create a New Trip </a></h1>
<br/><br/>


<?PHP 
 $app_name="Christian Missions Connector"; $app_url="http://tangiblesoft.net/missionsconnector"; 

$con = mysql_connect(localhost,"arena", "***arena!password!getmoney!getpaid***");
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
?>
