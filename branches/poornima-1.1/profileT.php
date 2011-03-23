<?php
// Application: Christian Missions Connector
// File: 'profileT.php' 
//  mission trip profile as seen by public- no edits from this page
// 
//require_once 'facebook.php';
include_once 'common.php';
ob_start();

$fb = cmc_startup($appapikey, $appsecret,0);
$fbid = $fb->require_login("publish_stream,read_stream");

arena_connect();

/*

<fb:dashboard>
	<fb:action href="index.php">Home</fb:action>
	<fb:help href="about.php">About</fb:action>
	<fb:help href="help.php">Help</fb:action>
	<fb:create-button href="new.php"> Create or Edit your Christian Missions Connector Profile </fb:create-button>
</fb:dashboard>	


<?php 
$tabstring="<fb:tabs><fb:tab-item href='http://apps.facebook.com/missionsconnector/index.php' title='Invite'/><fb:tab-item href='http://apps.facebook.com/missionsconnector/searchform.php' title='Find New Connections'/><fb:tab-item href='http://apps.facebook.com/missionsconnector//profile.php?id='".$user_id."' title='My Profile'/><fb:tab-item href='http://apps.facebook.com/missionsconnector//mynetwork.php' title='People in My Network'/><fb:tab-item href='http://apps.facebook.com/missionsconnector//trips.php' title='My Trips'/></fb:tabs>";
$tabstring="<fb:tabs><fb:tab-item href='http://apps.facebook.com/missionsconnector/index.php' title='Invite'/><fb:tab-item href='http://apps.facebook.com/missionsconnector/searchform.php' title='Find New Connections'/><fb:tab-item href='http://apps.facebook.com/missionsconnector//profile.php?id=".$user_id."' title='My Profile'/><fb:tab-item href='http://apps.facebook.com/missionsconnector//mynetwork.php' title='People in My Network'/><fb:tab-item href='http://apps.facebook.com/missionsconnector//help.php' title='Help & FAQ'/></fb:tabs>";
echo $tabstring;
*/

?>
<br/><br/>


<?php

if (!isset($_GET['tripid'])) {


}
else
	$tid=$_REQUEST['tripid'];


$con = mysql_connect(localhost,"arena", "***arena!password!getmoney!getpaid***");
//$con = mysql_connect(localhost,"poornima", "MYdata@1");
	if(!$con)
	{
		die('Could not connect: ' .  mysql_error());
	}
	
	mysql_select_db("missionsconnector", $con);


//$sql = "select * from trips,tripmembers,users,skills,skillsselected,countries,countriesselected,regions,regionsselected,durations,durationsselected where trips.id=tripmembers.tripid and tripmembers.userid=users.userid and tripmembers.isadmin='1' and durations.id=durationsselected.id and regionsselected.id=regions.id and countriesselected.id=countries.id and skillsselected.id=skills.id and users.userid=skillsselected.userid and users.userid=countriesselected.userid and users.userid=regionsselected.userid and users.userid=durationsselected.userid and trips.id='".$tid."'";

$sql = 'select * from trips where id="'.$tid.'"';

if($result = mysql_query($sql)) {
  $num_rows = mysql_num_rows($result);
  while($row= mysql_fetch_array($result)) {
    $name = $row['tripname'];
    $creatorid = $row['creatorid'];
    $sql2 = 'select * from users where userid="'.$creatorid.'"';
    $result2 = mysql_query($sql2);
    $row2 = mysql_fetch_array($result2);
    $towner=$row2['name'];
    $tripdesc=$row['tripdesc'];
    $phone=$row['phone'];
    $email=$row['email'];
    $web=$row['website'];
    $dur=$row['duration'];
    $stage=$row['isinexecutionstage'];
    $destination=$row['destination'];
    $destinationcountry=$row['country'];
    //$departn=$row['departure'];
    $departn = explode(' ',$row['departure']);
    $depart = explode('-',$departn[0]);
    //$depart = date('m-d-Y', $departn);
    //$returnn=$row['returning'];
    //$return = date('m-d-Y',$returnn);
    $returnn = explode(' ',$row['returning']);
    $return = explode('-',$returnn[0]);
    $zip=$row['zipcode'];	
    $relg=$row['religion'];
    $numpeople = $row['numpeople'];
    $website = $row['website'];

  }
} else {
  echo "SQL Error ".mysql_error()." ";
}	

echo "<h1>".$name." is led by ".$towner."</h1><br/><br/><br/>";
		if (!empty($tripdesc))
		echo "<h2> Description: </h2>".$tripdesc."<br/><br/>"; 
		if (!empty($website))
		echo '<h2> Trip Website: </h2><a href="'.$website.'">'.$website.'</a><br/><br/>'; 
		if (!empty($destination))
		echo "<h2> Trip Destination: </h2>".$destination."<br/><br/>"; 		
		if (!empty($destinationcountry))
		echo "<h2> Destination Country: </h2>".$destinationcountry."<br/><br/>"; 		
		if (!empty($phone))
		echo "<h2> Contact Phone: </h2>".$phone."<br/><br/>"; 
		if (!empty($email))
		echo "<h2> Contact Email: </h2>".$email."<br/><br/>"; 
		if (!empty($website))
		echo "<h2> Website: </h2>".$website."<br/><br/>"; 
		if (!empty($dur))
		echo "<h2> Trip Duration: </h2>".$dur."<br/><br/>";
		if (!empty($numpeople))
		echo "<h2> Anticipated Number of People: </h2>".$numpeople."<br/><br/>";
		if (!empty($stage)) {
		if ($stage==0)
		echo "<h2> Stage: </h2>Planning Phase<br/><br/>";  
		else if ($stage==1)
		echo "<h2> Stage: </h2>In Execution<br/><br/>";  
		}
		if (!empty($depart))
		echo "<h2> Departure: </h2>".$depart[1]."-".$depart[2]."-".$depart[0]."<br/><br/>"; 
		if (!empty($return))
		echo "<h2> Return: </h2>".$return[1]."-".$return[2]."-".$return[0]."<br/><br/>"; 
		if (!empty($zip))
		echo "<h2> Destination Zip Code: </h2>".$zip."<br/><br/>"; 
		if (!empty($relg))
		echo "<h2> Religious Affiliation: </h2>".$relg."<br/><br/>"; 



// see if the user is already part of this trip

$sql = 'select * from tripmembers where userid="'.$fbid.'" and tripid="'.$tid.'"';
if ($result = mysql_query($sql)) {

$numrows = mysql_num_rows($result);
if ($numrows>0) {
	echo '<b> You are already part of this trip <br /> <br />';
  
echo "<fb:editor action='http://apps.facebook.com/missionsconnector/removeself.php?tripid=".$tid."' method='get'>";
echo "<fb:editor-button value='Remove Yourself from this Trip' name='remove'/>";
echo "</fb:editor>";

  echo "<br/><a href='invitetotrip.php?value=1'>Invite others to trips</a><br/><br/>";
    echo "<br/><a href='invitetotrip.php?value=0'>Invite others to trips as Trip Administrators</a><br/><br/>";


}
else {
echo "<fb:editor action='http://apps.facebook.com/missionsconnector/addtripmember.php?tripid=".$tid."' method='get'>";
echo "<fb:editor-button value='Join this Trip' name='join'/>";
echo "</fb:editor>";
}
}
else {
	echo 'Could not connect to MYSQL SERVER <br />';
}

?>
