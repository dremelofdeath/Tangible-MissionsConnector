<?php

include "common.php";

header('Content-type: application/json');

$con = arena_connect();

$saferequest = cmc_safe_request_strip($con);

$has_error = FALSE;
$err_msg = '';
$criteria = '';

if (array_key_exists('z', $saferequest) && !array_key_exists('c', $saferequest)) {
  // zipcode is provided, city is absent
  $criteria = "zipcode='".$saferequest['z']."'";
} else if (in_array('c', $saferequest) && !array_key_exists('z', $saferequest)) {
  // city is provided, zipcode is absent
  $criteria = "city='".$saferequest['c']."'";
} else if (array_key_exists('c', $saferequest) && array_key_exists('z', $saferequest)) {
  // error case: both are defined
  $has_error = TRUE;
  $err_msg = "Both city and zipcode are defined, ambiguous request.";
} else {
  // error case: neither are defined
  $has_error = TRUE;
  $err_msg = "Neither required parameter was defined.";
}

$json = array();

if (!$has_error) {
  $query = "SELECT zipcode, city, state FROM zipcodes WHERE ".$criteria.";";
  $result = $con->query($query);
  if (!$result) {
    $has_error = TRUE;
    $err_msg = "Can't query (query was '$query'): " . $con->error;
  } else {
    if ($result->num_rows == 1) {
      $row = $result->fetch_array();
      $json['zipcode'] = $row['zipcode'];
      $json['city'] = $row['city'].", ".$row['state'];
    } else if ($result->num_rows == 0) {
      $has_error = TRUE;
      $err_msg = "No results.";
    } else if ($result->num_rows < 0) {
      $has_error = TRUE;
      $err_msg = "Catastropic failure: negative results.";
    } else if ($result->num_rows > 1) {
      $has_error = TRUE;
      $err_msg = "Ambiguous request; more than one result.";
    }
  }
}

$json['has_error'] = $has_error;

if ($has_error) {
  $json['err_msg'] = $err_msg;
}

echo json_encode($json);

