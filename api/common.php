<?php
require 'facebook/facebook.php';

function arena_connect() {
  $host = "localhost";
  $user = "arena";
  $con = new mysqli("localhost", "arena", "***arena!password!getmoney!getpaid***", "missionsconnector");
  if($con->connect_errno) {
    die('Could not connect: ' . $con->connect_error);
  }
  return $con;
}

// Checks to see if the user has ever used this app before. If not, then a 
// placeholder is inserted into the users database for tracking purposes. 
// Returns true if this user has used before; false otherwise.
function db_check_user($con, $fbid) {
  $has_used_before = false;

  $sql = "SELECT userid FROM users WHERE userid='".$fbid."'";
  $result = $con->query($sql) or die($con->error);

  if($result->num_rows > 0) {
    $sql = "UPDATE users SET lastviewed=NOW() WHERE userid =".$fbid;
    $has_used_before = true;
  } else if($result->num_rows == 0) {
    // I'm not sure that we want to do this anymore. If we do decide we want to 
    // track users, we could make a tracking table and use that instead.
    //$sql = "INSERT INTO users (userid, dateadded, lastviewed) ".
      //"VALUES ('".$fbid."',NOW(),NOW())";
  } else {
    die("Negative number of results, run for the hills! " . $con->error);
  }
  $con->query($sql) or die($con->error);
  return $has_used_before;
}

function setjsonmysqlerror(&$has_error,&$err_msg,$query,$con) {
 	$has_error = TRUE;
  $this_error = "Can't query (query was '$query'): " . $con->error;
  if (!$err_msg) {
    $err_msg = $this_error;
  } else {
    $err_msg .= "<br/>" . $this_error;
  }
}

function db_purge_all_searches($con) {
  $query = "DELETE FROM searchterms";
  $result = $con->query($query);
  $query = "DELETE FROM searches";
  $result = $con->query($query);
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
  $result = $con->query($query);

  if (!$result) {
    setjsonmysqlerror($has_error,$err_msg,$query,$con);
    return -1;
  } else {
    $ret = -1;

    if($result->num_rows == 0) {
      // error case; user not found, delete nothing and return an error code
      $ret = $num_userids;
    } else if($result->num_rows > 1) {
      // error case; more than one deletion would occur.
      $ret = $num_userids;
    } else if($result->num_rows == 1) {
      // only one result! burn him!
      // note: it must happen in this order to satisfy foreign key contraints
      $query = "DELETE FROM countriesselected WHERE userid='".$userid."'";
      $result = $con->query($query);
      if (!$result) {
        setjsonmysqlerror($has_error,$err_msg,$query,$con);
      }
      $query = "DELETE FROM durationsselected WHERE userid='".$userid."'";
      $result = $con->query($query);
      if (!$result) {
        setjsonmysqlerror($has_error,$err_msg,$query,$con);
      }
      $query = "DELETE FROM hits WHERE userid='".$userid."'";
      $result = $con->query($query);
      if (!$result) {
        setjsonmysqlerror($has_error,$err_msg,$query,$con);
      }
      $query = "DELETE FROM languagesselected WHERE userid='".$userid."'";
      $result = $con->query($query);
      if (!$result) {
        setjsonmysqlerror($has_error,$err_msg);
      }
      $query = "DELETE FROM notifications WHERE id='".$userid."'";
      $result = $con->query($query);
      if (!$result) {
        setjsonmysqlerror($has_error,$err_msg);
      }
      $query = "DELETE FROM regionsselected WHERE userid='".$userid."'";
      $result = $con->query($query);
      if (!$result) {
        setjsonmysqlerror($has_error,$err_msg,$query,$con);
      }
      $query = "DELETE skillsselectedtrips FROM skillsselectedtrips "
        . "JOIN trips ON trips.id=skillsselectedtrips.tripid "
        . "INNER JOIN users ON users.userid='".$userid."' AND users.userid=trips.creatorid";
      $result = $con->query($query);
      if (!$result) {
        setjsonmysqlerror($has_error,$err_msg,$query,$con);
      }
      $query = "DELETE searches, searchterms FROM searches "
        . "JOIN searchterms ON searches.searchid=searchterms.searchid "
        . "WHERE searches.userid='".$userid."'";
      $result = $con->query($query);
      if (!$result) {
        setjsonmysqlerror($has_error,$err_msg,$query,$con);
      }
      $query = "DELETE FROM skillsselected WHERE userid='".$userid."'";
      $result = $con->query($query);
      if (!$result) {
        setjsonmysqlerror($has_error,$err_msg,$query,$con);
      }
      $query = "DELETE FROM tripmembers WHERE userid='".$userid."'";
      $result = $con->query($query);
      if (!$result) {
        setjsonmysqlerror($has_error,$err_msg,$query,$con);
      }
      $query = "DELETE FROM trips WHERE creatorid='".$userid."'";
      $result = $con->query($query);
      if (!$result) {
        setjsonmysqlerror($has_error,$err_msg,$query,$con);
      }
      $query = "DELETE FROM tripwallinvites WHERE userid='".$userid."'";
      $result = $con->query($query);
      if (!$result) {
        setjsonmysqlerror($has_error,$err_msg,$query,$con);
      }
      $query = "DELETE FROM usstatesselected WHERE userid='".$userid."'";
      $result = $con->query($query);
      if (!$result) {
        setjsonmysqlerror($has_error,$err_msg,$query,$con);
      }
      $query = "DELETE FROM users WHERE userid='".$userid."'";
      $result = $con->query($query);
      if (!$result) {
        setjsonmysqlerror($has_error,$err_msg,$query,$con);
      }
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
  $result = $con->query($query);

  if (!$result) {
    setjsonmysqlerror($has_error,$err_msg,$query,$con);
    return -1;
  } else {
    $ret = -1;
    if($result->num_rows == 0) {
      // error case; user not found, delete nothing and return an error code
      $ret = $num_userids;
    } else if($result->num_rows > 1) {
      // error case; more than one deletion would occur.
      $ret = $num_userids;
    } else if($result->num_rows == 1) {
      // only one result! burn him!
      $query = "DELETE FROM countriesselected WHERE userid='".$userid."'";
      $result = $con->query($query);
      if (!$result) {
        setjsonmysqlerror($has_error,$err_msg,$query,$con);
      }
      $query = "DELETE FROM durationsselected WHERE userid='".$userid."'";
      $result = $con->query($query);
      if (!$result) {
        setjsonmysqlerror($has_error,$err_msg,$query,$con);
      }
      $query = "DELETE FROM regionsselected WHERE userid='".$userid."'";
      $result = $con->query($query);
      if (!$result) {
        setjsonmysqlerror($has_error,$err_msg,$query,$con);
      }
      $query = "DELETE FROM skillsselected WHERE userid='".$userid."'";
      $result = $con->query($query);
      if (!$result) {
        setjsonmysqlerror($has_error,$err_msg,$query,$con);
      }
      $query = "DELETE FROM usstatesselected WHERE userid='".$userid."'";
      $result = $con->query($query);
      if (!$result) {
        setjsonmysqlerror($has_error,$err_msg,$query,$con);
      }
      $query = "UPDATE LOW_PRIORITY users ".
        "SET name=NULL, organization=NULL, isreceiver=NULL, state=NULL, city=NULL, zipcode=NULL, ".
        "phone=NULL, fbphone=NULL, email=NULL, missionsexperience=NULL, religion=NULL,".
        "aboutme=NULL, website=NULL, partnersite=NULL WHERE userid='".$userid."' LIMIT 1";
      $result = $con->query($query);
      if (!$result) {
        setjsonmysqlerror($has_error,$err_msg,$query,$con);
      }
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
function cmc_safe_request_strip($con) {
	$returnArrayMap = array();
	foreach($_REQUEST as $key => $value) {
		// If the value is an array, then we need to rip out the array's bits, and 
		// not accidentally convert the word 'Array' to a string. -zack
		if(is_array($value)) {
			$localValue = array();
			foreach($value as $nested_key => $nested_value) {
        $localValue[$con->real_escape_string($nested_key)] =
            $con->real_escape_string(htmlspecialchars(strip_tags($nested_value)));
			}
			$returnArrayMap[$con->real_escape_string($key)] = $localValue;
		} else {
      $returnArrayMap[$con->real_escape_string($key)] =
          $con->real_escape_string(htmlspecialchars(strip_tags($value)));
		}
	}
  date_default_timezone_set('America/New_York');
	return $returnArrayMap;
}

function cmc_safe_object_strip($con, $obj) {
  $safeobj = array();
  foreach ($obj as $key => $value) {
    if (is_array($value)) {
      $nested_array = array();
      foreach ($value as $nested_key => $nested_value) {
        $nested_array[$con->real_escape_string($nested_key)] =
            $con->real_escape_string(htmlspecialchars($nested_value));
      }
      $safeobj[$con->real_escape_string($key)] = $nested_array;
    } else {
      $safeobj[$con->real_escape_string($key)] =
          $con->real_escape_string(htmlspecialchars($value));
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
