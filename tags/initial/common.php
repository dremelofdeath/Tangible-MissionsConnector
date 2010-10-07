<?php

include_once '../facebook/facebook.php';
include_once 'config.php';

function echo_dashboard() {
  echo '<fb:dashboard>';
  echo '  <fb:action href="index.php">Home</fb:action>';
  echo '  <fb:help href="about.php">About</fb:action>';
  echo '  <fb:help href="help.php">Help & FAQ</fb:action>';
  echo '  <fb:create-button href="new.php">';
  echo '    Create or Edit your Christian Missions Connector Profile';
  echo '  </fb:create-button>';
  echo '</fb:dashboard>';
}

// here for reference because I have no idea what it does --zack <3
//$tabstring="<fb:tabs><fb:tab-item href='http://apps.facebook.com/missionsconnector/index.php' title='Invite'/><fb:tab-item href='http://apps.facebook.com/missionsconnector/searchform.php' title='Find New Connections'/><fb:tab-item href='http://apps.facebook.com/missionsconnector//profile.php?id='".$profileid."' title='My Profile'/><fb:tab-item href='http://apps.facebook.com/missionsconnector//mynetwork.php' title='People in My Network'/><fb:tab-item href='http://apps.facebook.com/missionsconnector//trips.php' title='My Trips'/></fb:tabs>";

function echo_tabbar() {
  echo '<fb:tabs>';
  echo '  <fb:tab-item href="welcome.php" title="Welcome!"/>';
  echo '  <fb:tab-item href="profile.php" title="My Profile"/>';
  echo '  <fb:tab-item href="searchform.php" title="Find New Connections"/>';
  echo '  <fb:tab-item href="mynetwork.php" title="People in My Network"/>';  
  echo '  <fb:tab-item href="invite.php" title="Invite"/>';
  echo '  <fb:tab-item href="help.php" title="Help & FAQ"/>';
  echo '  <fb:tab-item href="donate.php" title="Donate"/>';
  echo '</fb:tabs>';
}

function arena_connect() {
  $host = "localhost";
  $user = "arena";
  $con = mysql_connect($host, $user, "***arena!password!getmoney!getpaid***");
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
function cmc_startup($appapikey, $appsecret) {
  $facebook = new Facebook($appapikey, $appsecret);
  $fbid = $facebook->require_login();
  echo_dashboard();
  echo_tabbar();
  arena_connect();
  db_check_user($fbid);
  return $facebook;
}

?>
