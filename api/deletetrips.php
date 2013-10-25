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

if (array_key_exists('tripid', $saferequest)) {
  // tripid should be provided
  $tripid = $saferequest['tripid'];
} 
else {
  // error case: neither are defined
  $has_error = TRUE;
  $err_msg = "tripid was not defined.";
}

$json = array();


if (!$has_error) {

  $sql = 'select * from trips where id="'.$tripid.'"';
  $result = $con->query($sql);

  if (!$result) {
    setjsonmysqlerror($has_error,$err_msg,$sql);
  } else {
    if ($result->num_rows == 0) {
      $has_error = TRUE;
      $err_msg = "No Trips to match the tripid";
    } else {
      $row = $result->fetch_array();
      $tripname = $row['tripname'];

      $result->free();

      // first we need to delete the tripmembers for this trip
      $sql = 'delete from tripmembers where tripid="'.$tripid.'"';
      $result = $con->query($sql);

      if ($result) {
        // Then delete skills associated with the trip
        $sql1 = 'delete from skillsselectedtrips where tripid="'.$tripid.'"';
        $result1 = $con->query($sql1);

        if ($result1) {

          // Then delete the row from trips table
          $sql = 'delete from trips where id="'.$tripid.'"';
          $result = $con->query($sql);
          if ($result) {
            $json['tripname'] = $tripname;
          } else {
            setjsonmysqlerror($has_error,$err_msg,$sql);
          }
        } else {
          setjsonmysqlerror($has_error,$err_msg,$sql);
        }
      } else {
        setjsonmysqlerror($has_error,$err_msg,$sql);
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



