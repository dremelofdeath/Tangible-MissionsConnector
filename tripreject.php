<?php
// Application: Christian Missions Connector
// File: 'trips.php'
//  shows all trips the user is a member of
//
//require_once 'facebook.php';

include_once 'common.php';

$fb = cmc_startup($appapikey, $appsecret,0);
$fbid = $fb->require_login("publish_stream");

if (!empty($_GET)) {

$tripid = $_GET['id'];
$isadmin = $_GET['admin'];
$today = date("F j, Y");

$sql = 'insert into tripmembers (userid, tripid, isadmin, invited, accepted, datejoined) VALUES ("'.$fbid.'","'.$tripid.'","'.$isadmin.'","1","0","'.$today.'")';

//echo $sql.'<br >';
$result = mysql_query($sql);


echo "<fb:redirect url='welcome.php' />";

}
?>
