<?php
include_once 'common.php';
include_once 'pagecounter.php';
ob_start();

session_start();
session_unset();

/*
function recordhits() {
$uniquepage = "uniquehitcounter.txt";

session_start();
//echo 'session='.$_SESSION['hits'];
if (!isset($_SESSION['hits'])) {
  //echo 'SESSION not set';
  $unique_hits = file($uniquepage);
  $unique_hits[0] = $unique_hits[0] + 0;
  $unique_hits[0]++;
  $fp = fopen($uniquepage,"w");
  fputs($fp,"$unique_hits[0]");
  fclose($fp);
  $_SESSION['hits'] = 1;
}		

}
*/

$fb = cmc_startup($appapikey, $appsecret,0);
$fbid = $fb->require_login($required_permissions = 'publish_stream');


if (isset($_GET['error'])) {
 	echo '<br /><b>Sorry, you are not authorized to view the Administrative Pages </b><br /><br/>';
}

echo "

Hi Welcome to Christian Missions Connector!
<br><br>

Are you interested in missions work? Do you want to connect with people and organizations who share your passion for missions? Whether you want to find a missions organization, start a mission team, join a mission team or just connect with others who have a passion for missions, Christian Missions Connectors can help. To explore the site, you can use the tabs above or the step by step instructions provided below.

<br><br>

<br><br>

";

//recordhits();

//check if a profile already exists
$sql = 'select * from users where userid="'.$fbid.'"';
if ($result = mysql_query($sql)) {
$numrows = mysql_num_rows($result);
if ($numrows > 0) {
while ($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
$isreceiver = $row['isreceiver'];
if ($isreceiver)
echo "You can update your profile quickly. To do so, please click the blue highlighted link below <br /> <br /><center><a href='http://apps.facebook.com/missionsconnector/makeprofile.php?type=mission&update=1&edit=1'>Edit your profile</a></center><br /><br />"; 
else
echo"You can update your profile quickly. To do so, please click the blue highlighted link below <br /> <br /><center><a href='http://apps.facebook.com/missionsconnector/makeprofile.php?type=volunteer&update=1&edit=1'>Edit your profile</a></center><br /><br />"; 
}
}
else
echo"<b>Getting started</b> is simple and takes about 2 minutes. The first step is to create a profile for yourself or your organization by clicking the blue highlighted link below <br/><br /><center><a href='http://apps.facebook.com/missionsconnector/new.php'>Create your profile</a></center><br /><br />"; 

}
else
	echo "SQL Error ".mysql_error()." ";

echo"If you are a missions organization, missions team leader or simply a volunteer who wants to <b>start a new trip</b> click on the blue highlighted link below<br/><br/><center><a href='http://apps.facebook.com/missionsconnector/makeprofile.php?type=trip'>Create a new trip</a></center>";

echo".  <br><br> ";

echo"If you are a missions organization, missions team leader or a volunteer who wants to <b>find more team members</b> click on the blue highlighted link below<br/><br/><center><a href='http://apps.facebook.com/missionsconnector/searchform.php'>Find new connections</a></center>";

echo" <br><br>";

echo "After you get familiar with Christian Missions Connector, we hope you'll <b>share it with others</b> by clicking on any of these links: <br/><br/>";

echo "1) ";
echo"<a href='http://apps.facebook.com/missionsconnector/post.php'>Post something about Missions Connector to our application wall</a>";

echo".  <br><br> ";

echo "2) ";
echo"<a href='http://apps.facebook.com/missionsconnector/invite.php'>Invite others to Christian Missions Connector.</a>

<br><br>";

echo "3) ";
echo"<a href='http://apps.facebook.com/missionsconnector/donate.php'>Donate to Christian Missions Connector</a>";

echo".  <br><br> ";
?>


<?php

  //$fql = 'SELECT post_id, actor_id, target_id, message FROM stream WHERE source_id in (SELECT target_id FROM connection WHERE source_id='.$appid.') AND is_hidden = 0';


/*
echo "application ID:".$appid;


//$fb->api_client->stream_publish($message, $attachment, $action_links, APP_ID, $facebook_uid);

$res = $fb->api_client->users_hasAppPermission('publish_stream',null);
//echo 'permission='.$res;

if (!$res) {

//echo '<p> Prompt to grant permissions: <form prompt-permission="publish_stream" method="post" </form></p>';

}

//?>

<script type="text/javascript">
Facebook.showPermissionDialog("read_stream,publish_stream");
</script>

//<?php
//<fb:prompt-permission perms="read_stream,publish_stream">Grant Permission for posting messages</fb:prompt-permission>

$res = $fb->api_client->users_hasAppPermission('publish_stream',null);

print '<br />';
$message = "Christian Missions Connector";
$ans = $fb->api_client->stream_publish($message, null, null, $appid,null);
*/

?>

