<?php
//newtrip.php/////////////////

include_once 'common.php';

$fb = cmc_startup($appapikey, $appsecret);

$tripname=$_REQUEST['tripname'];
$desc=$_REQUEST['desc'];
$phone=$_REQUEST['phone'];
$email=$_REQUEST['email'];
$orgweb=$_REQUEST['orgweb'];
$dur=$_REQUEST['dur'];
$exec=$_REQUEST['exec'];
$depart=$_REQUEST['depart'];
$return=$_REQUEST['return'];
$zip=$_REQUEST['zip'];
$relg=$_REQUEST['relg'];

$con=mysql_connect(localhost,"arena","***arena!password!getmoney!getpaid***");
	if(!$con)
	{
		die('Could not connect: ' .  mysql_error());
	}
	
	mysql_select_db("missionsconnector", $con);
	$sql = "INSERT INTO trips (creatorid,tripname,tripdesc,phone,email,website,isinexecutionstage,departure,returning,zipcode,religion)VALUES ('".$fbid."','".$tripname."','".$desc."','".$phone."','".$email."','".$orgweb."','".$exec."','".$depart."','".$return."','".$zip."','".$relg."')";

if($result = mysql_query($sql)){
	$sql = "SELECT MAX tripid FROM trips";
	if($result = mysql_query($sql)){
	while($row= mysql_fetch_array($result)){
		$tripid = $row['tripid'];}
	$sql = "INSERT INTO tripmembers VALUES ($fbid,$tripid,1)";
	if($result=mysqlquery($sql)){
	}
	}
	}else {
		echo "SQL Error ".mysql_error()." ";
		 }/*else {
		echo "SQL Error ".mysql_error()." ";
	}*/
$nexturl="http://apps.facebook.com/missionsconnector/trips.php";
    
echo "<fb:redirect url='http://apps.facebook.com/missionsconnector//profile.php?id=".$fbid."' />";

?>
