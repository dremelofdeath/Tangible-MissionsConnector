<?php
// Application: Christian Missions Connector
// File: 'addtripmember.php' 
//  add user to trip
// 
//require_once 'facebook.php';

include_once 'common.php'

$fb = cmc_startup($appapikey, $appsecret);

//$fbid=$user;
$tid=$_Request['tripid'];

$con=mysql_connect(localhost,"arena","***arena!password!getmoney!getpaid***");
	if(!$con)
	{
		die('Could not connect: ' .  mysql_error());
	}
	
	mysql_select_db("missionsconnector", $con);
	$sql = "INSERT INTO tripmembers (userid,tripid,isadmin) VALUES ('".$fbid."','".$tid."','0')";
	if($result = mysql_query($sql)){
	}
	else {echo "SQL Error ".mysql_error()." ";
		 }
		

echo "<fb:redirect url='http://apps.facebook.com/missionsconnector//profile.php?id=".$fbid."' />";
		  
	//	$nexturl="http://apps.facebook.com/missionsconnector//trips.php";
//  header("Location:".$nexturl);
?>
