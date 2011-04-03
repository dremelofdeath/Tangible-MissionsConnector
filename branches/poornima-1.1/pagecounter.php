<?php

include_once 'facebook/facebook.php';
include_once 'common.php';

ob_start();

//session_start();
//session_unset();


//$fb = cmc_startup($appapikey, $appsecret,2);
//$fbid = $fb->require_login($required_permissions = 'publish_stream');

$con = arena_connect();

// Originally I thought of using MYSQL to store "hits" counters, but somehow my CREATE SQL query
// did not work. So using simple files to hold the total and unique hits to the welcome page - PB


// This can be placed in index - CREATE hits table only if it does not exist - some logic needed there
//mysql_query("CREATE TABLE hits (unique int(6), total int(7))");

// Assumes: you are already connected to MySQL,
// and have selected the database you will be using;
// That the visitor is cookie-compatible;
// That the visitor has not cleared their cookies.


// Also note that this script is very optimizable.
// It is intended only to work and to be easy to understand,
// not to win any awards.

$facebook = new Facebook($appapikey, $appsecret);

$mysession = $facebook->getSession();
if ($mysession) {
     echo '<a href="' . $facebook->getLogoutUrl() . '">Logout</a>';
}
else {
     echo '<a href="' . $facebook->getLoginUrl(array('req_perms' => 'read_stream,publish_stream,email,user_photos', 'display'=>'popup')) . '">Login</a>';
}
$fbid = $facebook->getUser();


//$fbid = $facebook->require_login($required_permissions = 'publish_stream');

//$uniquepage = "uniquehitcounter.txt";

//$totalpage = "totalhits.txt";


// The total number of hits is going to be incremented
// regardless of the situation, so this can be done first
// to get it out of the way

  // Now, we set the cookie's value. Here, it is set to
  // expire in 3600 seconds (1 hours) - you can
  // change this is if you like, but it ensures that the
  // same person won't be counted as a unique visitor
  // more than once in a 1-hour period


if(isset($_COOKIE["visited"])) {
  $cookie = $_COOKIE["visited"];
//  echo 'cookie value='.$cookie;
  // Our global variable index will be called 'visited',
  // to make it easy to remember
} else {
  $cookie = false;
}

//$unique_hits = file($uniquepage);
//$unique_hits[0] = $unique_hits[0] + 0;

//$ip = $_SERVER['REMOTE_ADDR'];

if (!$cookie) {
  
  $sql = 'select * from hits where userid="'.$fbid.'"';
  $result = mysql_query($sql);
  $numrows = mysql_num_rows($result);
  if ($numrows==0) {
	$sql = 'insert into hits (userid,count) VALUES ("'.$fbid.'","1")';
	$result = mysql_query($sql);
  }
  else {
  $row = mysql_fetch_array($result);
  $unique_hits = $row['count'] + 1;
  $sql = 'update hits set count="'.$unique_hits.'" where userid="'.$fbid.'"';
  $result = mysql_query($sql);
  }

  /*
  $unique_hits[0]++;
  $fp = fopen($uniquepage,"w");
  fputs($fp,"$unique_hits[0]");
  fclose($fp);
  */

  //$fd = fopen ($uniquepage , "w"); 
  //$fcounted = $fstring."\n".getenv("REMOTE_ADDR");
  //$fout= fwrite ($fd , $fcounted );
  //fclose($fd); 

  //mysql_query("UPDATE hits SET unique = '" . $unique_hits . "'");
  // The value of $cookie is NOT true, meaning that the user
  // has not yet been counted
  //setcookie("visited","1",time()+3600);
  setcookie("visited","1",time()+600);
}

//print "<b>PERFORMANCE STATISTICS:"."</b><br />";
//print "<b>Total number of page hits=".$total_hits[0]."</b><br />";
//print "<b>This application has been accessed ".$unique_hits[0]." times</b><br />";

?>
