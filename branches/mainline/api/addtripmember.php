<?php
// Application: Christian Missions Connector
// File: 'addtripmember.php' 
//  add user to trip
// 
//require_once 'facebook.php';

include_once 'common.php';

$con = arena_connect();

$saferequest = cmc_safe_request_strip($con);
$has_error = FALSE;
$err_msg = '';

if (array_key_exists('tripid', $saferequest) && array_key_exists('fbid', $saferequest) && array_key_exists('type', $saferequest)) {
  // both tripid and facebook userid should be provided
  $tid = $saferequest['tripid'];
  $fbid = $saferequest['fbid'];
  $membertype = $saferequest['type'];
  if (($membertype < 1) || ($membertype > 3)) {
    $has_error = TRUE;
    $err_msg = "Trip Member Type can only be 1 or 2 or 3";
  }
} else {
  // error case
  $has_error = TRUE;
  $err_msg = "The required parameters was defined.";
}

$json = array();

if (!$has_error) {
	// first check that the user has a CMC profile - otherwise redirect user to create a profile
	$sql = 'select * from users where userid="'.$fbid.'"';
	$result = $con->query($sql);
	if (!$result) {
    setjsonmysqlerror($has_error,$err_msg,$sql,$con);
  } else {
    if ($result->num_rows == 0) {
      // This means user does not have a CMC profile
      $has_error = TRUE;
      $err_msg = "No CMC Profile";
    } else {
      // check that a trip with tid exists, if not throw an error message
      $sql = 'select * from trips where id="'.$tid.'"';
      $result = $con->query($sql);
      if (!$result) {
        setjsonmysqlerror($has_error,$err_msg,$sql,$con);
      } else {
        if ($result->num_rows == 0) {
          $has_error = TRUE;
          $err_msg = "Trip with the specified ID does not exist";
        } else {
          //check that the user is not part of the trip already
          $sql = 'select * from tripmembers where tripid="'.$tid.'" and userid="'.$fbid.'"';
          $result = $con->query($sql);
          if (!$result) {
            setjsonmysqlerror($has_error,$err_msg,$sql,$con);
          } else {
            $numbs = $result->num_rows;
            if ($numbs > 0) {
              $has_error = TRUE;
              $err_msg = "User is already part of this trip";
            } else {
              $sql = 'INSERT INTO tripmembers '
                . '(userid,tripid,isadmin,invited,accepted,type) '
                . 'VALUES ("'.$fbid.'","'.$tid.'","0","1","1","'.$membertype.'")';
              $con->query($sql);
              if(!$result) {	
                setjsonmysqlerror($has_error,$err_msg,$sql,$con);
              } else {
                // update number of people in trips table
                $sql = 'select numpeople from trips where id="'.$tid.'"';
                $con->query($sql);
                if (!$result) {
                  setjsonmysqlerror($has_error,$err_msg,$sql,$con);
                } else {
                  $row = $result->fetch_array();
                  $numpeople = $row['numpeople'];
                  $numpeople++;
                  $sql2 = 'update trips set numpeople="'.$numpeople.'" where id="'.$tid.'"';
                  $result2 = $con->query($sql2);
                  if (!$result2) {
                    setjsonmysqlerror($has_error,$err_msg,$sql2);
                  }
                }
              }
            }
          }
        }
      }
    }
	}
}

$json['has_error'] = $has_error;

if ($has_error) {
  $json['err_msg'] = $err_msg;
}

echo json_encode($json);
	
?>
