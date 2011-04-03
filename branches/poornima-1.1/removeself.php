<?php
// Application: Christian Missions Connector
// File: 'trips.php'
//  shows all trips the user is a member of
//
//require_once 'facebook.php';

include_once 'common.php';
ob_start();

$fb = cmc_startup($appapikey, $appsecret,0);
$fbid = get_user_id($fb);
//$fbid = $fb->require_login("publish_stream,read_stream");

?>

<?php

if (isset($_GET)) {
$tripid = $_GET['tripid'];

$sql = 'select * from tripmembers where tripid="'.$tripid.'"';
$result = mysql_query($sql);
$numrows = mysql_num_rows($result);
if ($numrows==1) {
 echo '<br />You are the only person on this trip <br />';
 echo "<br/><a href='deletetrips.php?tripid=".$tripid."'>Delete this trip instead?</a><br/><br/>";
}
else {
$sql = 'delete from tripmembers where tripid="'.$tripid.'" and userid="'.$fbid.'"';
$result = mysql_query($sql);

// Now decrement the number of people on this trip
$sql = 'select * from trips where id="'.$tripid.'"';
$result = mysql_query($sql);
$row = mysql_fetch_array($result);
$numpeople = $row['numpeople'] + 0;
$numpeople--;

$sql = 'update trips set numpeople="'.$numpeople.' where id="'.$tripid.'"';
$result = mysql_query($sql);

echo '<br /><br />You have been removed from this trip <br />';
echo "<br /><a href='tripoptions.php'> Go back to trip options</a><br /><br />";

}

}

?>



