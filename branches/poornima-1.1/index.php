
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>

<script type="text/javascript">
<!--
function redirect_welcome(){
      window.location = "welcome.php"
}
function redirect_profile(){
      window.location = "profile.php"
}

//-->
</script>

<?php
// Application: Christian Missions Connector
// File: 'profilein.php' 
//  add user profile to db
// 
//require_once 'facebook.php';

include_once 'common.php';

$fb = new Facebook($appapikey, $appsecret);
//$fb->require_frame(); 
//$fbid = $fb->require_login();
$fbid = get_user_id($fb);
arena_connect();
//if(db_check_user($fbid)) {
if (false) {
?>
  <body onLoad="setTimeout('redirect_profile()', 0)">

<?php
} else {
?>
?>
 <body onLoad="setTimeout('redirect_welcome()', 0)">

<?php
}

?>
</head>
</html>
