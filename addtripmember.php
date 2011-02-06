<?php
// Application: Christian Missions Connector
// File: 'addtripmember.php' 
//  add user to trip
// 
//require_once 'facebook.php';

include_once 'common.php';
ob_start();

$fb = cmc_startup($appapikey, $appsecret,0);
$fbid = $fb->require_login("publish_stream");

//$fbid=$user;
$tid=$_REQUEST['tripid'];

//$con=mysql_connect(localhost,"arena","***arena!password!getmoney!getpaid***");
$con=mysql_connect(localhost,"poornima","MYdata@1");
	if(!$con)
	{
		die('Could not connect: ' .  mysql_error());
	}
	
	mysql_select_db("missionsconnector", $con);

	$sql = 'INSERT INTO tripmembers (userid,tripid,isadmin,invited,accepted) VALUES ("'.$fbid.'","'.$tid.'","0","1","1")';
	if($result = mysql_query($sql)){
	
	$sql2 = 'select * from trips where id="'.$tid.'"';
	$result2 = mysql_query($sql2);
	$row2 = mysql_fetch_array($result2,MYSQL_ASSOC);

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
    	$message = 'Tripmember: '.$name.' has been added to the trip: '.$row2['tripname'];

       	session_start();
  	
	// get a list of friends who are using the CMC app
    	//$friends=$fb->api_client->friends_getAppUsers();


        if (!isset($_SESSION['apmsg'])) {
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
	   $_SESSION['apmsg'] = $message;
	}
	else {
		if (strcmp($message,$_SESSION['apmsg'])) {
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
		$_SESSION['apmsg'] = $message;
		}
	}


	}
	else {echo "SQL Error ".mysql_error()." ";
	}
	
	// update number of people in trips table
	$sql = 'select numpeople from trips where id="'.$tid.'"';
	if($result = mysql_query($sql)){
		$row = mysql_fetch_array($result);
		$numpeople = $row['numpeople'];
		$numpeople++;
		$sql2 = 'update trips set numpeople="'.$numpeople.'" where id="'.$tid.'"';
		$result2 = mysql_query($sql2);
	}
	else {echo "SQL Error ".mysql_error()." ";
		 }
	
		

echo "<fb:redirect url='http://apps.facebook.com/missionsconnector/profile.php?id=".$fbid."' />";
		  
	//	$nexturl="http://apps.facebook.com/missionsconnector//trips.php";
//  header("Location:".$nexturl);
?>
