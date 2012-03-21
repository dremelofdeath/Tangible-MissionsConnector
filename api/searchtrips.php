<?php
include_once 'common.php';

$con = arena_connect();

$saferequest = cmc_safe_request_strip();
$has_error = FALSE;
$err_msg = '';

$json = array();

// get all trips that are in the future
$sql = null;
if (array_key_exists('fbid', $saferequest) && $saferequest['fbid'] != '') {
  $sql =
    'SELECT t.*, tm.isadmin '.
    'FROM trips AS t '.
    'LEFT JOIN tripmembers AS tm '.
    'ON t.id=tm.tripid AND tm.userid = "'.$saferequest['fbid'].'" '.
    'WHERE t.departure >= NOW()';
} else {
  $sql = 'SELECT * FROM trips WHERE departure >= NOW()';
}
$result = mysql_query($sql,$con);

if ($result) {

   $numrows = mysql_num_rows($result);
	$json['trips'] = array();

   if ($numrows!=0) {
  	while ($row = mysql_fetch_array($result)) {
		$json['trips'][] = $row;
  	}
   }
 
}
else {
 	setjsonmysqlerror($has_error,$err_msg,$sql);
}


$json['has_error'] = $has_error;

if ($has_error) {
  $json['err_msg'] = $err_msg;
}

echo json_encode($json);
