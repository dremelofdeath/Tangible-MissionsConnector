<?php
// Application: Christian Missions Connector
// File: 'profileM.php' 
//  mission receiver profile as seen by public- no edits from this page
// 
//require_once 'facebook.php';

$con = mysql_connect("mysql://localhost/missionsconnector?autoReconnect=true","arena", "***arena!password!getmoney!getpaid***");
	if(!$con)
	{
		die('Could not connect: ' .  mysql_error());
	}
	
	mysql_select_db("missionsconnector", $con);
$fbid=fb.getuserid();
	$sql = "select * from users where userid=".$fbid;
if($result = mysql_query($sql)){
	while($row= mysql_fetch_array($result)){
		$name = $row['building_name'];
		$x2 = $row['latitude'];
		$y2 = $row['longitude'];
		$hav = haversine($x, $y, $x2, $y2);
		$buildings[$hav]=$name;
	}
	} else {
		echo "SQL Error ".mysql_error()." ";
	}


?>