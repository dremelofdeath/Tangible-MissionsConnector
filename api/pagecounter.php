<?php

include_once 'common.php';

$con = arena_connect();

$saferequest = cmc_safe_request_strip();
$has_error = FALSE;
$err_msg = '';

if (array_key_exists('fbid', $saferequest)) {
  $fbid = $saferequest['fbid'];
}
else {
  // error case: all needed variables are not defined
  $has_error = TRUE;
  $err_msg = "Facebook uid is a required parameter";
}

$json = array();

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




// The total number of hits is going to be incremented
// regardless of the situation, so this can be done first
// to get it out of the way

  // Now, we set the cookie's value. Here, it is set to
  // expire in 3600 seconds (1 hours) - you can
  // change this is if you like, but it ensures that the
  // same person won't be counted as a unique visitor
  // more than once in a 1-hour period

if (!$has_error) {

if(isset($_COOKIE["visited"])) {
  $cookie = $_COOKIE["visited"];
} else {
  $cookie = false;
}

if (!$cookie) {
  
  $sql = 'select * from hits where userid="'.$fbid.'"';
  $result = mysql_query($sql,$con);
  if (!$result)
	setjsonmysqlerror($has_error,$err_msg,$sql);
  else {
  $numrows = mysql_num_rows($result);
  if ($numrows==0) {
	$sql = 'insert into hits (userid,count) VALUES ("'.$fbid.'","1")';
	$result = mysql_query($sql,$con);
	if (!$result)
		setjsonmysqlerror($has_error,$err_msg,$sql);
  }
  else {
  $row = mysql_fetch_array($result);
  $unique_hits = $row['count'] + 1;
  $sql = 'update hits set count="'.$unique_hits.'" where userid="'.$fbid.'"';
  $result = mysql_query($sql,$con);
  if (!$result)
	setjsonmysqlerror($has_error,$err_msg,$sql);
  }

  setcookie("visited","1",time()+600);
  }
}

}

$json['has_error'] = $has_error;

if ($has_error) {
  $json['err_msg'] = $err_msg;
}

echo json_encode($json);


?>
