<?php
// Application: Christian Missions Connector
// File: 'profilein.php' 
//  add user profile to db
// 
//require_once 'facebook.php';

include_once 'common.php';

$facebook = new Facebook($appapikey, $appsecret);
$facebook->require_frame(); 
$fbid = $facebook->require_login();
arena_connect();
//if(db_check_user($fbid)) {
if(false) {
  echo "<fb:redirect url='profile.php' />";
} else {
  echo "<fb:redirect url='welcome.php' />";
}

?>
