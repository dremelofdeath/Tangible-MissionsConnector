<?php
// Copyright 2007 Facebook Corp.  All Rights Reserved. 
// 
// Application: Christian Missions Connector
// File: 'mynetwork.php' 
//   Shows people user has added to network on CMC
// 

//require_once 'facebook.php';

include_once 'common.php';

$fb = cmc_startup($appapikey, $appsecret);

?>
<br/><br/>


<?PHP 
 $app_name="Christian Missions Connector"; $app_url="http://tangiblesoft.net/missionsconnector"; 

$friends=$facebook->api_client->friends_getAppUsers();
 foreach ($friends as $currentfriend){
	echo "<fb:profile-pic uid=".$currentfriend." linked='false' />  <fb:name uid=".$currentfriend." linked='false' shownetwork='true'/><a href='http://apps.facebook.com/missionsconnector/profile.php?userid=".$currentfriend."'><br/>  See Profile</a><br/><br/>";
	}
?>
