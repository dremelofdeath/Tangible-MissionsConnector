<?php
// Application: Christian Missions Connector
// File: 'profilein.php' 
//  add user profile to db
// 
//require_once 'facebook.php';

include_once 'common.php';

$fb = cmc_startup($appapikey, $appsecret);
$fbid = $fb->require_login();

arena_connect();

$info = $fb->api_client->users_getInfo($fbid, 'name', 'email', 'about_me');

for($i=0; $i < count($info); $i++) {
  $record = $info[$i];
  $name = $record['name']; 
  $email = $record['email'].", ".$_REQUEST['email']; 
  //$phone = $info[0]['phone']; 
  $aboutme = $record['about_me'].", ".$_REQUEST['aboutme']; 
}

//$sql = "SELECT name, email, about_me FROM user WHERE uid ='".$fbid."'";
//echo $facebook->api_client->fql_query($sql);


$sql = "SELECT userid FROM users WHERE userid='".$fbid."'";
$result = mysql_query($sql) or die(mysql_error());

$num_userids = mysql_num_rows($result);

if($num_userids > 0){
  $sql = "UPDATE users SET name='".$name."', isreceiver='0', zipcode='".
    $_REQUEST['zip']."', phone = '".$_REQUEST['phone']."', email = '".
    $_REQUEST['email']."', missionsexperience = '".$_REQUEST['misexp'].
    "', religion = '".$_REQUEST['relg']."', aboutme = '".$_REQUEST['aboutme'].
    "', website = '".$_REQUEST['website']."', partnersite = '0' WHERE userid =".
    $fbid;
} else if($num_userids == 0) {
  $sql = "INSERT INTO users ".
    "(userid, name, isreceiver, zipcode, phone, email, missionsexperience,".
    " religion, aboutme, website, partnersite) ".
    "VALUES ('".$fbid."','".$name."','0','".$_REQUEST['zip']."','".
    $_REQUEST['phone']."','".$email."','".$_REQUEST['misexp']."','".
    $_REQUEST['relg']."','".$aboutme."','".$_REQUEST['website']."','0')";
} else {
  die("Run for the hills! " . mysql_error());
}

//  $sql = $sql."(userid, name, isreceiver, zipcode, phone, email, missionsexperience, religion, aboutme, website, partnersite) VALUES ('".$fbid."','".$tid."','0','".$_REQUEST['zip']."','".$_REQUEST['phone']."','".$_REQUEST['email']."','".$_REQUEST['misexp']."','".$_REQUEST['relg']."','".$_REQUEST['aboutme']."','".$_REQUEST['website']."','0')";
//  if($idcount<>0){
//    $sql=$sql."WHERE userid='".$fbid."'";}
   //mysql_fetch_array($result) or die(mysql_error());

mysql_query($sql) or die(mysql_error());

  //else {echo "SQL Error ".mysql_error()." ";
  //   }

// clear out the old entries so that we start fresh
mysql_query("DELETE FROM skillsselected WHERE userid='".$fbid."'") or die(mysql_error());
mysql_query("DELETE FROM regionsselected WHERE userid='".$fbid."'") or die(mysql_error());
mysql_query("DELETE FROM countriesselected WHERE userid='".$fbid."'") or die(mysql_error());
mysql_query("DELETE FROM durationsselected WHERE userid='".$fbid."'") or die(mysql_error());

$medskills = $_REQUEST['medskills'];
foreach($medskills as $ms) {
  $sql = "INSERT INTO skillsselected VALUES ('".$fbid."','".$ms."')";
  mysql_query($sql);
  if(!$result){
    echo "SQL Error: ".mysql_error()." ";
  }
}

$otherskills=$_REQUEST['otherskills'];
foreach($otherskills as $ms) {
  $sql = "INSERT INTO skillsselected VALUES ('".$fbid."','".$ms."')";
  $result = mysql_query($sql);
  if(!$result) {
    echo "SQL Error: ".mysql_error()." ";
  }
}

$relgskills=$_REQUEST['spiritserv'];
foreach($relgskills as $ms) {
  $sql = "INSERT INTO skillsselected VALUES ('".$fbid."','".$ms."')";
  $result = mysql_query($sql);
  if(!$result) {
    echo "SQL Error: ".mysql_error()." ";
  }
}

$region=$_REQUEST['region'];
foreach($region as $ms) {
  $sql = "INSERT INTO regionsselected VALUES ('".$fbid."','".$ms."')";
  $result = mysql_query($sql);
  if(!$result) {
    echo "SQL Error: ".mysql_error()." ";
  }
}

$country = $_REQUEST['country'];
foreach($country as $ms) {
  $sql = "INSERT INTO countriesselected VALUES ('".$fbid."','".$ms."')";
  $result = mysql_query($sql);
  if(!$result) {
    echo "SQL Error: ".mysql_error()." ";
  }
}

$dur = $_REQUEST['dur'];
foreach($dur as $ms) {
  $sql = "INSERT INTO durationsselected VALUES ('".$fbid."','".$ms."')";
  $result = mysql_query($sql);
  if(!$result) {
    echo "SQL Error: ".mysql_error()." ";
  }
}

echo "<fb:redirect url='profile.php' />";

?>
