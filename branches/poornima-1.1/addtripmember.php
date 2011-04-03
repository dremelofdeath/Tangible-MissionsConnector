<?php
// Application: Christian Missions Connector
// File: 'addtripmember.php' 
//  add user to trip
// 
//require_once 'facebook.php';

include_once 'common.php';
ob_start();

$fb = cmc_startup($appapikey, $appsecret,0);
$response = array('response' => array('hasError' => false, 'addtripmembermsg' => 'Add Trip Member', 'uid' => 100000022664372));
$somejson = json_encode($response);

// During actual implementation, $somejson will come from frontend
// through either get or post
$mydataobj = json_decode($somejson);

// Now process the json object
if ($mydataobj->{'response'}->{'hasError'}) {
     // have appropriate response if there is an error
  echo 'Error <br />';
}
else {
     $fbid = $mydataobj->{'response'}->{'uid'};
}

//$fbid = $fb->require_login("publish_stream");

//$fbid=$user;
$tid=$_REQUEST['tripid'];

$con=mysql_connect(localhost,"arena","***arena!password!getmoney!getpaid***");
//$con=mysql_connect(localhost,"poornima","MYdata@1");
	if(!$con)
	{
		die('Could not connect: ' .  mysql_error());
	}
	
	mysql_select_db("missionsconnector", $con);

	// first check that the user has a CMC profile - otherwise redirect user to create a profile
	$sql = 'select * from users where userid="'.$fbid.'"';
	$result = mysql_query($sql);
	$numrows = mysql_num_rows($result);

	if ($numrows==0) {
	// This means user does not have a CMC profile
	echo '<br /><br /> You do not have a Christian Missions Profile Yet!! <br /><br />';
	echo"<b>Getting started</b> is simple and takes about 2 minutes. The first step is to create a profile for yourself or your organization by clicking the blue highlighted link below <br/><br /><center><a href='http://apps.facebook.com/missionsconnector/new.php'>Create your profile</a></center><br /><br />";

	}

	else {


	$sql = 'INSERT INTO tripmembers (userid,tripid,isadmin,invited,accepted) VALUES ("'.$fbid.'","'.$tid.'","0","1","1")';
	if($result = mysql_query($sql)){
	
	$sql2 = 'select * from trips where id="'.$tid.'"';
	$result2 = mysql_query($sql2);
	$row2 = mysql_fetch_array($result2,MYSQL_ASSOC);

  // now update recent activity
  //$res = $fb->api_client->users_hasAppPermission('publish_stream',null);
  //if (!$res) {
  ?>

  <script type="text/javascript">

  function callback (perms) {
       if (!perms) {
               message('You did not grant the special permission to post to friends wall without being prompted.');
                 } else {
                           message('You can now publish to walls without being prompted.');
                             }
         }

    Facebook.showPermissionDialog("read_stream,publish_stream,manage_pages,offline_access",callback);
  </script>

<?php
//}	
	
	  /*
 	  $info = $fb->api_client->users_getInfo($fbid, 'name', 'email');
  	$record = $info[0];
   	$name = $record['name'];
    */

      // Get the name information directly from the facebook profile pages
      $name = get_name_from_fb_using_curl($fbid);

    	$message = 'Tripmember: '.$name.' has been added to the trip: '.$row2['tripname'];

       	session_start();
  	
	// get a list of friends who are using the CMC app
    	//$friends=$fb->api_client->friends_getAppUsers();


        if (!isset($_SESSION['apmsg'])) {
        ?>

              <script type="text/javascript">
                  Facebook.streamPublish(<?PHP $message ?>,null,null,<?php $appid ?>,' ',null,true,<?php $appid ?>);
            </script>

        <?php

	   //$fb->api_client->stream_publish($message,null,null,$appid,$appid);
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
      ?>
    
        <script type="text/javascript">
        Facebook.streamPublish(<?PHP $message ?>,null,null,<?php $appid ?>,' ',null,true,<?php $appid ?>);
        </script>

    <?php
		//$fb->api_client->stream_publish($message,null,null,$appid,$appid);

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
	
		
   header('"Location: /profile.php?id='.$fbid.'"');

   //echo "<fb:redirect url='http://apps.facebook.com/missionsconnector/profile.php?id=".$fbid."' />";
	}	  
	//	$nexturl="http://apps.facebook.com/missionsconnector//trips.php";
//  header("Location:".$nexturl);
?>
