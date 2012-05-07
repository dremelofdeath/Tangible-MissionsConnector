<?php
require 'facebook/facebook.php';

function arena_connect() {
  $host = "localhost";
  $user = "arena";
  $con = mysql_connect($host, $user, "***arena!password!getmoney!getpaid***");
  //$con = mysql_connect($host, $user, "MYdata@1");
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

function setjsonmysqlerror(&$has_error,&$err_msg,$query) {
 	$has_error = TRUE;
  $this_error = "Can't query (query was '$query'): " . mysql_error();
  if (!$err_msg) {
    $err_msg = $this_error;
  } else {
    $err_msg .= "<br/>" . $this_error;
  }
}

function db_purge_all_searches($con) {
  $query = "DELETE FROM searchterms";
  $result = mysql_query($query,$con);
  $query = "DELETE FROM searches";
  $result = mysql_query($query,$con);
}

// Removes every trace of a given user's information from the Tangible servers.
// param $userid: the Facebook user ID of the user to purge.
// Returns the number of users that were found. Only deletes user information if 
// the query is unambiguous (i.e., there is only one row found that matches the 
// user ID. It will not delete anything if there would be more than one 
// deletion.
function db_purge_user_by_id($userid,$con,&$has_error,&$err_msg) {
  // First, let's see if the user exists before we try anything at all.
  $query = "SELECT userid FROM users WHERE userid='".$userid."'";
  $result = mysql_query($query,$con);

  if (!$result) {
	setjsonmysqlerror($has_error,$err_msg,$query);
	return -1;
  }
  else {
  $num_userids = mysql_num_rows($result);

  $ret = -1;

  if($num_userids == 0) {
    // error case; user not found, delete nothing and return an error code
    $ret = $num_userids;
  } else if($num_userids > 1) {
    // error case; more than one deletion would occur.
    $ret = $num_userids;
  } else if($num_userids == 1) {
    // only one result! burn him!
    // note: it must happen in this order to satisfy foreign key contraints
    $query = "DELETE FROM countriesselected WHERE userid='".$userid."'";
    $result = mysql_query($query,$con);
    if (!$result)
	setjsonmysqlerror($has_error,$err_msg,$query);
    $query = "DELETE FROM durationsselected WHERE userid='".$userid."'";
    $result = mysql_query($query,$con);
    if (!$result)
	setjsonmysqlerror($has_error,$err_msg,$query);
    $query = "DELETE FROM hits WHERE userid='".$userid."'";
    $result = mysql_query($query,$con);
    if (!$result)
	setjsonmysqlerror($has_error,$err_msg,$query);
    $query = "DELETE FROM languagesselected WHERE userid='".$userid."'";
    $result = mysql_query($query,$con);
    if (!$result)
	setjsonmysqlerror($has_error,$err_msg);
    $query = "DELETE FROM notifications WHERE id='".$userid."'";
    $result = mysql_query($query,$con);
    if (!$result)
	setjsonmysqlerror($has_error,$err_msg);
    $query = "DELETE FROM regionsselected WHERE userid='".$userid."'";
    $result = mysql_query($query,$con);
    if (!$result)
	setjsonmysqlerror($has_error,$err_msg,$query);
    $query = "DELETE searches, searchterms FROM searches JOIN searchterms ON searches.searchid=searchterms.searchid WHERE searches.userid='".$userid."'";
    $result = mysql_query($query,$con);
    if (!$result)
	setjsonmysqlerror($has_error,$err_msg,$query);
    $query = "DELETE FROM skillsselected WHERE userid='".$userid."'";
    $result = mysql_query($query,$con);
    if (!$result)
	setjsonmysqlerror($has_error,$err_msg,$query);
    $query = "DELETE FROM tripmembers WHERE userid='".$userid."'";
    $result = mysql_query($query,$con);
    if (!$result)
	setjsonmysqlerror($has_error,$err_msg,$query);
    $query = "DELETE FROM trips WHERE creatorid='".$userid."'";
    $result = mysql_query($query,$con);
    if (!$result)
	setjsonmysqlerror($has_error,$err_msg,$query);
    $query = "DELETE FROM tripwallinvites WHERE userid='".$userid."'";
    $result = mysql_query($query,$con);
    if (!$result)
	setjsonmysqlerror($has_error,$err_msg,$query);
    $query = "DELETE FROM usstatesselected WHERE userid='".$userid."'";
    $result = mysql_query($query,$con);
    if (!$result)
	setjsonmysqlerror($has_error,$err_msg,$query);
    $query = "DELETE FROM users WHERE userid='".$userid."'";
    $result = mysql_query($query,$con);
    if (!$result)
	setjsonmysqlerror($has_error,$err_msg,$query);
    $ret = 1;
  } else {
	$has_error = TRUE;
	$err_msg = "Something horrible is happening. Negative number of rows returned";
  }
  return $ret;
   }
}

// Removes every trace of a given user's information from the Tangible servers.
// param $userid: the Facebook user ID of the user to purge.
// Returns the number of users that were found. Only deletes user information if 
// the query is unambiguous (i.e., there is only one row found that matches the 
// user ID. It will not delete anything if there would be more than one 
// deletion.
function db_scrub_user_by_id($userid,$con,&$has_error,&$err_msg) {
  // First, let's see if the user exists before we try anything at all.
  $query = "SELECT userid FROM users WHERE userid='".$userid."'";
  $result = mysql_query($query,$con);

  if (!$result) {
	setjsonmysqlerror($has_error,$err_msg,$query);
	return -1;
  }
  else {
  $num_userids = mysql_num_rows($result);

  $ret = -1;

  if($num_userids == 0) {
    // error case; user not found, delete nothing and return an error code
    $ret = $num_userids;
  } else if($num_userids > 1) {
    // error case; more than one deletion would occur.
    $ret = $num_userids;
  } else if($num_userids == 1) {
    // only one result! burn him!
    $query = "DELETE FROM countriesselected WHERE userid='".$userid."'";
    $result = mysql_query($query,$con);
    if (!$result)
	setjsonmysqlerror($has_error,$err_msg,$query);
    $query = "DELETE FROM durationsselected WHERE userid='".$userid."'";
    $result = mysql_query($query,$con);
    if (!$result)
	setjsonmysqlerror($has_error,$err_msg,$query);
    $query = "DELETE FROM regionsselected WHERE userid='".$userid."'";
    $result = mysql_query($query,$con);
    if (!$result)
	setjsonmysqlerror($has_error,$err_msg,$query);
    $query = "DELETE FROM skillsselected WHERE userid='".$userid."'";
    $result = mysql_query($query,$con);
    if (!$result)
	setjsonmysqlerror($has_error,$err_msg,$query);
    $query = "DELETE FROM usstatesselected WHERE userid='".$userid."'";
    $result = mysql_query($query,$con);
    if (!$result)
	setjsonmysqlerror($has_error,$err_msg,$query);
		$query = "UPDATE LOW_PRIORITY users ".
			       "SET name=NULL, organization=NULL, isreceiver=NULL, state=NULL, city=NULL, zipcode=NULL, ".
						 "phone=NULL, fbphone=NULL, email=NULL, missionsexperience=NULL, religion=NULL,".
						 "aboutme=NULL, website=NULL, partnersite=NULL WHERE userid='".$userid."' LIMIT 1";
    $result = mysql_query($query,$con);
    if (!$result)
	setjsonmysqlerror($has_error,$err_msg,$query);
    $ret = 1;
  } else {
	$has_error = TRUE;
	$err_msg = "Something horrible is happening. Negative number of rows returned";
  }
  return $ret;
  }
}

/**
 * Securely strip all of the $_REQUEST input to a given page of dangerous 
 * characters/code and dump it into another array map, which we will return.
 * @return An array containing exactly the same info as $_REQUEST, only safe
 */
function cmc_safe_request_strip() {
	$returnArrayMap = array();
	foreach($_REQUEST as $key => $value) {
		// If the value is an array, then we need to rip out the array's bits, and 
		// not accidentally convert the word 'Array' to a string. -zack
		if(is_array($value)) {
			$localValue = array();
			foreach($value as $nested_key => $nested_value) {
				$localValue[mysql_real_escape_string($nested_key)] = mysql_real_escape_string(htmlspecialchars(strip_tags($nested_value)));
			}
			$returnArrayMap[mysql_real_escape_string($key)] = $localValue;
		} else {
			$returnArrayMap[mysql_real_escape_string($key)] = mysql_real_escape_string(htmlspecialchars(strip_tags($value)));
		}
	}
  date_default_timezone_set('America/New_York');
	return $returnArrayMap;
}

function cmc_safe_object_strip($obj) {
  $safeobj = array();
  foreach ($obj as $key => $value) {
    if (is_array($value)) {
      $nested_array = array();
      foreach ($value as $nested_key => $nested_value) {
        $nested_array[mysql_real_escape_string($nested_key)] = mysql_real_escape_string(htmlspecialchars($nested_value));
      }
      $safeobj[mysql_real_escape_string($key)] = $nested_array;
    } else {
      $safeobj[mysql_real_escape_string($key)] = mysql_real_escape_string(htmlspecialchars($value));
    }
  }
  return (object)$safeobj;
}

function get_name_from_fb_using_curl($fbid) {
 
  $urlx = 'http://graph.facebook.com/'.$fbid.'/';
  $process = curl_init($urlx);
  curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
  $user = json_decode(curl_exec($process));
  curl_close($process);
  $name = $user->{'name'};

  return $name;

}
