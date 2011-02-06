<?php
// Copyright 2007 Facebook Corp.  All Rights Reserved. 
// 
// Application: Christian Missions Connector
// File: 'mynetwork.php' 
//   Shows people user has added to network on CMC
// 

//require_once 'facebook.php';

include_once 'common.php';

$fb = cmc_startup($appapikey, $appsecret,0);

?>
<br/><br/>


<?PHP 
 $app_name="Christian Missions Connector"; $app_url="http://tangiblesoft.net/missionsconnector"; 

$friends=$fb->api_client->friends_getAppUsers();

if (empty($friends)) {
 echo "You do not have any friends in your network <br />";
}
else {
 foreach ($friends as $currentfriend){
	echo "<fb:profile-pic uid=".$currentfriend." linked='true' /> <br /> <fb:name uid=".$currentfriend." linked='true' shownetwork='true'/><a href='http://apps.facebook.com/missionsconnector/profile.php?userid=".$currentfriend."'><br/>  See CMC Profile</a><br/><br/>";
	}
}
?>
