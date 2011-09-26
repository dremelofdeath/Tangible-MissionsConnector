<?php
require 'facebook/facebook.php';

// This function is not needed in the new backend
/*
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
*/

// here for reference because I have no idea what it does --zack <3
//$tabstring="<fb:tabs><fb:tab-item href='http://apps.facebook.com/missionsconnector/index.php' title='Invite'/><fb:tab-item href='http://apps.facebook.com/missionsconnector/searchform.php' title='Find New Connections'/><fb:tab-item href='http://apps.facebook.com/missionsconnector//profile.php?id='".$profileid."' title='My Profile'/><fb:tab-item href='http://apps.facebook.com/missionsconnector//mynetwork.php' title='People in My Network'/><fb:tab-item href='http://apps.facebook.com/missionsconnector//trips.php' title='My Trips'/></fb:tabs>";

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
	$err_msg = "Can't query (query was '$query'): " . mysql_error();
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
    $query = "DELETE FROM notifications WHERE id='".$userid."'";
    $result = mysql_query($query,$con);
    if (!$result)
	setjsonmysqlerror($has_error,$err_msg);
    $query = "DELETE FROM regionsselected WHERE userid='".$userid."'";
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
				$localValue[$nested_key] = htmlspecialchars(strip_tags($nested_value));
			}
			$returnArrayMap[$key] = $localValue;
		} else {
			$returnArrayMap[$key] = htmlspecialchars(strip_tags($value));
		}
	}
  date_default_timezone_set('America/New_York');
	return $returnArrayMap;
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

// There is no need for this function in the new backend. 
/*
// Make sure to call this function at the top of every CMC page. It will return 
// the application's Facebook object.
function cmc_startup($appapikey, $appsecret,$val) {

  //$facebook = new Facebook($appapikey, $appsecret,true);
  
  $facebook = new Facebook(array( 'appId' => $appapikey, 'secret' => $appsecret, 'cookie' => true, ));

  //$facebook->setSession(null,true);
  $params = array(
    'canvas'     => 1,
    'fbconnect'  => 0,
    'next'       => URL_CANVAS,
    'cancel_url' => 'http://www.facebook.com/',
    'req_perms'  => 'publish_stream, status_update, offline_acces'
  );


  // The userid should come from the JAVASCRIPT SDK Facebook FrontEnd
  // This should come as a JSON Object, so we should first decode the JSON object and then get
  // facebook userid and other details
  
  $response = array('response' => array('hasError' => false, 'welcomemessage' => 'Welcome to CMC', 'uid' => 100000022664372));
  $somejson = json_encode($response);

  $fbid = get_user_id($somejson);

  
  // During actual implementation, $somejson will come from frontend
  // through either get or post
  //$mydataobj = json_decode($somejson);

  // Now process the json object
  //if ($mydataobj->{'response'}->{'hasError'}) {
    // have appropriate response if there is an error
  //  echo 'Error <br />';
  //}
  //else { 
  //  $fbid = $mydataobj->{'response'}->{'uid'};
 // }

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
      // display profile picture only if $val is 1 
      echo '<a href="http://www.facebook.com/profile.php?id='.$fbid.'"><img src="http://graph.facebook.com/'.$fbid.'/picture" /></a>';
    }
  }
  //displayprofilepic($facebook,$fbid);
  
  date_default_timezone_set('Europe/London');

  return $facebook;
}
*/

?>


