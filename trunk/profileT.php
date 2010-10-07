<?php
// Application: Christian Missions Connector
// File: 'profileT.php' 
//  mission trip profile as seen by public- no edits from this page
// 
//require_once 'facebook.php';



?>
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
?>
<br/><br/>


<?php
$tid=$_REQUEST['tripid'];


$con = mysql_connect(localhost,"arena", "***arena!password!getmoney!getpaid***");
	if(!$con)
	{
		die('Could not connect: ' .  mysql_error());
	}
	
	mysql_select_db("missionsconnector", $con);


$sql = "select * from trips,tripmembers,users,skills,skillsselected,countries,countriesselected,regions,regionsselected,durations,durationsselected where trips.id=tripmembers.tripid and tripmembers.userid=users.userid and tripmembers.isadmin='1' and durations.id=durationsselected.id and regionsselected.id=regions.id and countriesselected.id=countries.id and skillsselected.id=skills.id and users.userid=skillsselected.userid and users.userid=countriesselected.userid and users.userid=regionsselected.userid and users.userid=durationsselected.userid and trips.id='".$tid."'";
if($result = mysql_query($sql)){
		$num_rows = mysql_num_rows($result);
	while($row= mysql_fetch_array($result)){
		$name = $row['trips.tripname'];
		$towner=$row['users.name'];
		$tripdesc=$row['trips.tripdesc'];
		$phone=$row['trips.phone'];
		$email=$row['trips.email'];
		$web=$row['trips.website'];
		$dur=$row['trips.duration'];
		$stage=$row['trips.isinexecutionstage'];
		$depart=$row['trips.departure'];
		$return=$row['trips.returning'];
		$zip=$row['trips.zipcode'];	
		$relg=$row['trips.religion'];
		
		
	}}else {
		echo "SQL Error ".mysql_error()." ";
		 }	

echo "<h1>".$name." is led by ".$towner."</h1><br/><br/><br/>";
		echo "<h2> Description: </h2>".$tripdesc."<br/><br/>"; 
		echo "<h2> Phone: </h2>".$phone."<br/><br/>"; 
		echo "<h2> Email: </h2>".$email."<br/><br/>"; 
		echo "<h2> Website: </h2>".$website."<br/><br/>"; 
		echo "<h2> Duration: </h2>".$dur."<br/><br/>";
		echo "<h2> Stage: </h2>".$stage."<br/><br/>";  
		echo "<h2> Departure: </h2>".$depart."<br/><br/>"; 
		echo "<h2> Return: </h2>".$return."<br/><br/>"; 
		echo "<h2> From Zip Code: </h2>".$zip."<br/><br/>"; 
		echo "<h2> Religious Affiliation: </h2>".$relg."<br/><br/>"; 



echo "<fb:editor action='http://apps.facebook.com/missionsconnector/addtripmember.php?tripid=".$tid."' method='get'>";
echo "<fb:editor-button value='Join this Trip' name='join'/>";
echo "</fb:editor>";

?>