<?php
// Application: Christian Missions Connector
// File: 'trips.php'
//  shows all trips the user is a member of
//
//require_once 'facebook.php';

include_once 'common.php';

$fb = cmc_startup($appapikey, $appsecret,0);
$fbid = get_user_id($fb);
//$fbid = $fb->require_login("publish_stream");

if (!empty($_GET)) {

$tripid = $_GET['id'];
$isadmin = $_GET['admin'];
$today = date("F j, Y");

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


$sql = 'insert into tripmembers (userid, tripid, isadmin, invited, accepted, datejoined) VALUES ("'.$fbid.'","'.$tripid.'","'.$isadmin.'","1","0","'.$today.'")';

//echo $sql.'<br >';
$result = mysql_query($sql);
}

echo "<fb:redirect url='welcome.php' />";

}
?>
