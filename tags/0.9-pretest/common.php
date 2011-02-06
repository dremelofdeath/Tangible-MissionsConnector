<?php

include_once '../facebook/facebook.php';
include_once 'config.php';

function echo_dashboard($fbid,$appadminids) {
  echo '<fb:dashboard>';
  echo '  <fb:action href="index.php">Home</fb:action>';
  $adminlink = 0;
  for ($i=0;$i<count($appadminids);$i++) {
	if ($appadminids[$i] == $fbid) {
		$adminlink = 1;
		continue 1;
	}
  }
  if ($adminlink) {
	echo '  <fb:action href="admin.php"><b>Admin</b></fb:action>';
  }

  echo '  <fb:help href="about.php">Contact Us</fb:action>';
  echo '  <fb:help href="help.php">Help & FAQ</fb:action>';
  echo ' <fb:help href="donate.php">Donate</fb:action>';
  
//check if a profile already exists
$sql = 'select * from users where userid="'.$fbid.'"';
if ($result = mysql_query($sql)) {
$numrows = mysql_num_rows($result);
if ($numrows > 0) {
while ($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
$isreceiver = $row['isreceiver'];
if ($isreceiver) {
  echo '  <fb:create-button href="makeprofile.php?type=mission&update=1&edit=1">';
  echo '    Edit your Christian Missions Connector Profile';
  echo '  </fb:create-button>';
} 
else {
  echo '  <fb:create-button href="makeprofile.php?type=volunteer&update=1&edit=1">';
  echo '    Edit your Christian Missions Connector Profile';
  echo '  </fb:create-button>';
}
}
}
else {
  echo '  <fb:create-button href="new.php">';
  echo '    Create your Christian Missions Connector Profile';
  echo '  </fb:create-button>';
}
}
else
	echo "SQL Error ".mysql_error()." ";
	
  echo '</fb:dashboard>';
}

// function to display profile picture
function displayprofilepic($facebook,$fbid) {

    if ($fb_uid == false) { return array(null, 2, 'No valid FB user in Session'); }
           
    $albums=$facebook->api_client->photos_getAlbums($fb_uid,NULL);
    $aid_profile = null;
    foreach($albums as $album){
        if($album['type']=='profile'){
            $aid_profile = $album['aid'];
            break;
        }
        return null;
    }
    $photos_profile = null;
    //It s a bit tricky but if you use also the uid like params then you will get a empty string ... Mistery
    if(!empty($aid_profile))$photos_profile = $facebook->api_client->photos_get('',$aid_profile,'');
    
    return $photos_profile;
	
//you  can test it in the tool box... it s works but it s not optimized...
//link to the orignal picture in  the array (key = src_big)

}


// here for reference because I have no idea what it does --zack <3
//$tabstring="<fb:tabs><fb:tab-item href='http://apps.facebook.com/missionsconnector/index.php' title='Invite'/><fb:tab-item href='http://apps.facebook.com/missionsconnector/searchform.php' title='Find New Connections'/><fb:tab-item href='http://apps.facebook.com/missionsconnector//profile.php?id='".$profileid."' title='My Profile'/><fb:tab-item href='http://apps.facebook.com/missionsconnector//mynetwork.php' title='People in My Network'/><fb:tab-item href='http://apps.facebook.com/missionsconnector//trips.php' title='My Trips'/></fb:tabs>";

function echo_tabbar($appadminids,$fbid) {
  echo '<fb:tabs>';
  /*
  $adminlink = 0;
  for ($i=0;$i<count($appadminids);$i++) {
	if ($appadminids[$i] == $fbid) {
		$adminlink = 1;
		continue 1;
	}
  }
  if ($adminlink) {
	echo '  <fb:tab-item href="admin.php" title="Admin"/>';
  }
  */
  echo '  <fb:tab-item href="welcome.php" title="Welcome!"/>';
  echo '  <fb:tab-item href="profile.php" title="My Profile"/>';
  echo '  <fb:tab-item href="searchform.php" title="Find New Connections"/>';
  echo '  <fb:tab-item href="searchtrips.php" title="View Upcoming Trips"/>';
  echo '  <fb:tab-item href="mynetwork.php" title="People in My Network"/>';  
  echo '  <fb:tab-item href="invite.php" title="Invite"/>';
  //echo '  <fb:tab-item href="help.php" title="Help & FAQ"/>';
  //echo '  <fb:tab-item href="donate.php" title="Donate"/>';
  echo ' <fb:tab-item href="tripoptions.php" title = "Manage Trips"/>';
  echo '</fb:tabs>';
}

function arena_connect() {
  $host = "localhost";
  $user = "poornima";
  //$con = mysql_connect($host, $user, "***arena!password!getmoney!getpaid***");
  $con = mysql_connect($host, $user, "MYdata@1");
  if(!$con) die('Could not connect: ' . mysql_error());
  mysql_select_db("missionsconnector", $con);
  return $con;
}

// Checks to see if the user has ever used this app before. If not, then a 
// placeholder is inserted into the users database for tracking purposes. 
// Returns true if this user has used before; false otherwise.
function db_check_user($fbid) {
  $has_used_before = false;

  $sql = "SELECT userid FROM users WHERE userid='".$fbid."'";
  $result = mysql_query($sql) or die(mysql_error());

  $num_userids = mysql_num_rows($result);

  if($num_userids > 0){
    $sql = "UPDATE users SET lastviewed=NOW() WHERE userid =".$fbid;
    $has_used_before = true;
  } else if($num_userids == 0) {
    $sql = "INSERT INTO users (userid, dateadded, lastviewed) ".
      "VALUES ('".$fbid."',NOW(),NOW())";
  } else {
    die("Negative number of results, run for the hills! " . mysql_error());
  }
  mysql_query($sql) or die(mysql_error());
  return $has_used_before;
}

// Make sure to call this function at the top of every CMC page. It will return 
// the application's Facebook object.
function cmc_startup($appapikey, $appsecret,$val) {
  $facebook = new Facebook($appapikey, $appsecret);
  $params = array(
          'canvas'     => 1,
	  'fbconnect'  => 0,
	  'next'       => URL_CANVAS,
	  'cancel_url' => 'http://www.facebook.com/',
	  'req_perms'  => 'publish_stream, status_update, offline_acces'
  );
  //$login_url = $facebook->getLoginUrl($params);

  $fbid = $facebook->require_login("publish_stream,read_stream");

  // create app admin ids - facebook ids of people who have admin rights for this application
  $appadminids = array();
  $appadminids[] = 100000022664372;
  $appadminids[] = 707283972;
  $appadminids[] = 25826994;

  arena_connect();
  //db_check_user($fbid);
  if ($val != 2) {
  echo_dashboard($fbid,$appadminids);
  echo_tabbar($appadminids,$fbid);
  if ($val==1) {
	echo "<fb:profile-pic uid=".$fbid." linked='true' /> <br /><fb:name uid=".$fbid." linked='true' shownetwork='true' /><br/><br/>";  
  //$profile_pic =  "http://graph.facebook.com/".$fbid."/picture";
  //echo "<img src=\"" . $profile_pic . "\" /><br />";
  }
  }
  //displayprofilepic($facebook,$fbid);

  return $facebook;
}

?>
