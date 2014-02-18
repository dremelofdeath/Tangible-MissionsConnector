<?php
// Application: Christian Missions Connector
// File: 'trips.php'
//  shows all trips the user is a member of
//
//require_once 'facebook.php';

include_once 'common.php';

$con = arena_connect();

$saferequest = cmc_safe_request_strip($con);
$has_error = FALSE;
$err_msg = '';

if (array_key_exists('tripid', $saferequest)
    && array_key_exists('fbid', $saferequest)
    && array_key_exists('isadmin', $saferequest)
    && array_key_exists('type', $saferequest)) {
  // invitation ids, tripid and facebook userid should be provided
  $isadmin = $saferequest['isadmin'];
  $fbid = $saferequest['fbid'];
  $tripid = $saferequest['tripid'];
  $membertype = $saferequest['type'];
} else {
  // error case: all needed variables are not defined
  $has_error = TRUE;
  $err_msg = "Required parameters not defined.";
}

$json = array();

if (!$has_error) {
  $sql = 'INSERT INTO tripmembers '
    . '(userid, tripid, isadmin, invited, accepted, type, datejoined) '
    . 'VALUES ("'.$fbid.'","'.$tripid.'","'.$isadmin.'","1","1","'.$membertype.'","'.$today.'")';

  $result = $con->query($sql);

  if (!$result) {
    setjsonmysqlerror($has_error,$err_msg,$sql,$con);
  }
}
