<?php
// Application: Christian Missions Connector
//

include_once 'common.php';

$con = arena_connect();

$saferequest = cmc_safe_request_strip($con);
$has_error = FALSE;
$err_msg = '';

if (array_key_exists('tripid', $saferequest) && array_key_exists('fbid', $saferequest)) {
  // both tripid and facebook userid should be provided
  $tid = $saferequest['tripid'];
  $fbid = $saferequest['fbid'];
} else if (array_key_exists('Tripmembers', $saferequest) && array_key_exists('tripid', $saferequest)) {
  // both tripid and facebook userid should be provided
  $tid = $saferequest['tripid'];
  $tripmembers = $saferequest['Tripmembers'];
} else {
  // error case: neither are defined
  $has_error = TRUE;
  $err_msg = "Required parameters was defined.";
}

$json = array();

if (!$has_error) {

  // check if there is a trip corresponding to $tid
  $sql = 'select * from trips where id="'.$tid.'"';
  $result = $con->query($sql);
  if (!$result) {
    setjsonmysqlerror($has_error,$err_msg,$sql);
  } else {
    if ($result->num_rows ==0) {
      $has_error = TRUE;
      $err_msg = "No Trip exists with the specified ID";
    } else {
      if (isset($fbid)) {
        $sql = 'select * from tripmembers where userid="'.$fbid.'" and accepted="1" and tripid="'.$tid.'"';
        $result = $con->query($sql);
        if (!$result) {
          setjsonmysqlerror($has_error,$err_msg,$sql);
        } else {
          if ($result->num_rows == 0) {
            $has_error = TRUE;
            $err_msg = 'User is not a member of that trip';
          } else {
            $sql = 'select COUNT(userid) from tripmembers where accepted="1" and tripid="'.$tid.'"';
            $result = $con->query($sql);
            if (!$result) {
              setjsonmysqlerror($has_error,$err_msg,$sql);
            } else {
              $countrow = $result->fetch_array();
              $membercount = $countrow['COUNT(userid)'];
              if ($membercount == 1) {
                $has_error = TRUE;
                $json['membercount'] = $membercount;
                $err_msg = 'You are the only person in this trip'; 
              } else {
                $sql = 'DELETE FROM tripmembers WHERE userid="'.$fbid.'" AND tripid="'.$tid.'"';
                $result = $con->query($sql);
                if (!$result) {
                  setjsonmysqlerror($has_error, $err_msg, $sql);
                }
              }
            }
          }
        }
      } else if (isset($tripmembers)) {

        // Now we can delete members from the trip - which means updating the tripmembers table in the database

        if (is_array($tripmembers)) {
          while ($mytripmember = current($tripmembers)) {
            $sql = 'delete from tripmembers where userid="'.$mytripmember.'" and tripid="'.$tid.'"';
            $result = $con->query($sql);
            if (!result) {
              setjsonmysqlerror($has_error,$err_msg,$sql);
              continue 1;
            }
            next($tripmembers);
          }
        } else {

          $sql = 'delete from tripmembers where userid="'.$tripmembers.'" and tripid="'.$tid.'"';
          $result = $con->query($sql);

          if (!result) {
            setjsonmysqlerror($has_error,$err_msg,$sql);
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



