<?php
include_once 'common.php';

$con = arena_connect();
$saferequest = cmc_safe_request_strip();

$has_error = FALSE;
$err_msg = '';

if (array_key_exists('fbid', $saferequest)) {
  // facebook userid should be provided in the least
  $fbid = $saferequest['fbid'];
} 
else if ((array_key_exists('fbid', $saferequest)) && (array_key_exists('userid', $saferequest)) && (array_key_exists('cmd', $saferequest))) {
  // both userid and facebook userid should be provided
  $fbid = $saferequest['fbid'];
  $usertopurge = $saferequest['userid'];
  $cmd = $saferequest['cmd'];
}
else {
  // error case
  $has_error = TRUE;
  $err_msg = "Facebook id was not defined.";
}

if (!$has_error) {

// get admins of CMC from the database - We need to create this table first
$appadminids = array();

$sql = 'select * from cmcadmins';
$result = mysql_query($sql,$con);
if (!$result) {
	$has_error = TRUE;
	$err_msg = "Can't query (query was '$query'): " . mysql_error();
}
else {
	$appadminids = array();
	while ($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
		$appadminids[] = $row['userid'];
	}
}

$allow=0;
for ($i=0;$i<count($appadminids);$i++) {
  if ($fbid==$appadminids[$i]) {
  $allow = 1;
  continue 1;
  }
}

if (!$allow) {
  $has_error = TRUE;
  $err_msg = "No Administrative privileges";
} else {

  process_admin_commands($cmd,$usertopurge,$con,$has_error,$err_msg);

  $numhits = numhits($con,$has_error,$err_msg);

  if (!$has_error)
  	$json['numhits'] = $numhits;
  
}

}

function numhits($con,&$has_error,&$err_msg) {
  // sum up all the user specific hits
  $sql = "select * from hits";
  $result = mysql_query($sql,$con);
  if (!$result) {
	setjsonmysqlerror($has_error,$err_msg,$sql);
	return -1;
  }
  else {
  $unique_hits = 0;
  while ($row = mysql_fetch_array($result)) {
    $unique_hits = $unique_hits + $row['count'];
  }
  return $unique_hits;
  }
}

function process_admin_commands($cmd,$usertopurge,$con,&$has_error,&$err_msg) {
  if(isset($cmd)) {
    switch($cmd) {
      case "purgeuser": process_admin_purgeuser($usertopurge,$con); break;
      case "purgeuser4real": process_admin_purgeuser4real($usertopurge,$con,$has_error,$err_msg); break;
      case "scrubuser": process_admin_scrubuser($usertopurge,$con); break;
      case "scrubuser4real": process_admin_scrubuser4real($usertopurge,$con,$has_error,$err_msg); break;
      default: break;
    }
  }
}

// This can be removed from the backend - no forms or any user interaction on the backend
/*
function process_admin_purgeuser($usertopurge,$con) {
  echo "<br />";
  echo "<center>";
  echo "  <font color='red'>";
  echo "    <b>WARNING: You are about to purge the user with ID:".$usertopurge." from ALL DATABASES!!</b><br/>";
  echo "  </font>";
  echo "  <b>This will delete everything the user has (profile, trips, etc.).";
  echo "  Are you <i>sure</i> you want to do this?</b><br/>";
  echo "  <form action='admin.php' method='POST'>";
  echo "    <input type='hidden' name='cmd' value='purgeuser4real' />";
  echo "    <input type='hidden' name='userid' value='".$usertopurge."' />";
  echo "    <input type='submit' value='I understand the consequences. Obliterate this poor user.' />";
  echo "  </form>";
  echo "  <br />";
  echo "</center>";
}
*/

function process_admin_purgeuser4real($usertopurge,$con,&$has_error,&$err_msg) {
  $resultcode = db_purge_user_by_id($usertopurge,$con,$has_error,$err_msg);
  if (!$has_error) {
  if($resultcode == 0) {
	$has_error = TRUE;
	$err_msg = "User ID:".$usertopurge." could not be found in the databases";
  } else if($resultcode > 1) {
	$has_error = TRUE;
	$err_msg = "Multiple results for ID::".$usertopurge.". Did not delete anything. Please check the DB.";
  } else if($resultcode == 1) {

  } else {
	$has_error = TRUE;
	$err_msg = "CATASTROPHIC FAILURE: Negative number of users. Something really horrible just happened";
  }
  }
}

// This can be removed from the backend as well
/*
function process_admin_scrubuser($usertopurge,$con) {
  echo "<br />";
  echo "<center>";
  echo "  <b>You are scrubbing the user ID:".$usertopurge.".<br/>";
  echo "  This will erase their profile information only. Are you sure?</b><br/>";
  echo "  <form action='admin.php' method='POST'>";
  echo "    <input type='hidden' name='cmd' value='scrubuser4real' />";
  echo "    <input type='hidden' name='userid' value='".$usertopurge."' />";
  echo "    <input type='submit' value=\"I'm sure. Scrub this user.\" />";
  echo "  </form>";
  echo "  <br />";
  echo "</center>";
}
*/

function process_admin_scrubuser4real($usertopurge,$con,&$has_error,&$err_msg) {
  $resultcode = db_scrub_user_by_id($usertopurge,$con,$has_error,$err_msg);
  if (!$has_error) {
  if($resultcode == 0) {
	$has_error = TRUE;
	$err_msg = "User ID:".$usertopurge." could not be found in the databases";
  } else if($resultcode > 1) {
	$has_error = TRUE;
	$err_msg = "Multiple results for ID::".$usertopurge.". Did not delete anything. Please check the DB.";
  } else if($resultcode == 1) {

  } else {
	$has_error = TRUE;
	$err_msg = "CATASTROPHIC FAILURE: Negative number of users. Something really horrible just happened";
  }
  }
}

$json['has_error'] = $has_error;

if ($has_error) {
  $json['err_msg'] = $err_msg;
}

echo json_encode($json);

?>
