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

?>

<h1><a href="welcome.php> Go back to the Welcome Page </a></h1><br /><br />

<?php

  echo "<fb:redirect url='http://www.facebook.com/apps/application.php?id=305928355832&v=app_2373072738#!/apps/application.php?id=305928355832&v=wall' />";

  $fql = 'SELECT actor_id, message, created_time FROM stream WHERE source_id='.$appid.' AND is_hidden = 0';
  $result = $fb->api_client->fql_query($fql);

  $messages = array();
  $actor_id = array();
  $created_times = array();
  $names = array();
  $emails = array();
  $j=0;
  if (is_array($result) && count($result)) {
  	foreach ($result as $msgpost) {
		$messages[$j] = $msgpost['message'];
		$actor_id[$j] = $msgpost['actor_id'];
		//echo $j.' '.$actor_id[$j].'<br />';
		/*
		$info = $fb->api_client->users_getInfo($actor_id[$j], 'name', 'email', 'current_location');
		$record = $info[0];

		$names[$j] = $record['name'];
		$emails[$j] = $record['email'];
		*/

		$created_times[$j] = $msgpost['created_time'];
		$j++;
	}
  }
  // Now display the stream
  echo '<b> Recent Activity on the Missions Connector Trips Wall <b/> <br /><br />';
  for ($i=0;$i<count($messages);$i++) {
        $mytime = date('m-d-Y', $created_times[$i]);
	//echo $names[$i].' '.$emails[$i].' created on '.$created_times[$i].' the following message: <br />';
	echo 'The following message was created on '.$mytime.': <br />';
	//echo '<fb:name uid="'.$actor_id[$i].'" /></fb:name>'.' '.' created the following message on '.$mytime.': <br />';
	//echo "<script language=javascript>alert('".$messages[$i]."');</script>";
	echo '               '.$messages[$i].'<br />';
	echo '<br /><br />';
  }
?>

