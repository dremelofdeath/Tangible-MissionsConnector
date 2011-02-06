<?php
// Application: Christian Missions Connector
// File: 'profilein.php' 
//  add user profile to db
// 
//require_once 'facebook.php';

include_once 'common.php';

$fb = new Facebook($appapikey, $appsecret);
$fb->require_frame(); 
$fbid = $fb->require_login();
arena_connect();
//if(db_check_user($fbid)) {
if(false) {
  echo "<fb:redirect url='profile.php' />";
} else {
  echo "<fb:redirect url='welcome.php' />";
}

?>
