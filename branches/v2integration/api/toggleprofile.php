<?php
// Application: Christian Missions Connector
// File: 'toggleprofile.php' 
// 

include_once 'common.php';
header('Content-type: application/json');
$con = arena_connect();

$saferequest = cmc_safe_request_strip();
$has_error = FALSE;
$err_msg = '';

function stripslashes_deep($value)
{
    $value = is_array($value) ? array_map("stripslashes_deep", $value) : stripslashes($value);
      return $value;
}

$json = array();

// make sure all the required parameters are defined, else throw an error
// profiletype, fbid
if (array_key_exists('fbid', $saferequest) && array_key_exists('profileinfo',$saferequest)) {

  $fbid = $saferequest['fbid'];
 
  if (get_magic_quotes_gpc())
  {
  $myobj = json_decode(htmlspecialchars_decode(array_map("stripslashes_deep",$saferequest['profileinfo'])));
  }
  else {
  $myobj = json_decode(htmlspecialchars_decode($saferequest['profileinfo']));
  }

    switch(json_last_error())
    {
      case JSON_ERROR_DEPTH:
         $has_error = TRUE;
         $err_msg = "Maximum stack depth exceeded";
         break;
      case JSON_ERROR_CTRL_CHAR:
         $has_error = TRUE;
         $err_msg = "Unexpected control character found";
          break;
      case JSON_ERROR_SYNTAX:
         $has_error = TRUE;
         $err_msg = "Syntax error, malformed JSON";
          break;
      case JSON_ERROR_NONE:
          break;
      }

}
else {
  // error case: all needed variables are not defined
  $has_error = TRUE;
  $err_msg = "Required parameters not defined.";
}


if (!$has_error) {

if (isset($myobj->{'toggle'})) {

if ($myobj->{'toggle'} == 1) {
   $sql = 'select * from users where userid="'.$fbid.'"';
   $result = mysql_query($sql,$con);
   if (!$result) {
 	  setjsonmysqlerror($has_error,$err_msg,$sql);
   }
   else {
   $numrows = mysql_num_rows($result);
   if ($numrows > 0) {
   $row = mysql_fetch_array($result);
   $misreceiver = $row['isreceiver'];
   if ($misreceiver == 1) {
   	$newrecr = 0;
	  $sql2 = 'UPDATE users SET isreceiver="'.$newrecr.'" where userid="'.$fbid.'"';
	  $result = mysql_query($sql2,$con);
   	if (!$result) {
 		setjsonmysqlerror($has_error,$err_msg,$sql2);
   	}
	else
		$isreceiver = $newrecr;
   }
   else {
   	$newrecr = 1;
	$sql2 = 'UPDATE users SET isreceiver="'.$newrecr.'" where userid="'.$fbid.'"';
	$result = mysql_query($sql2,$con);
	if (!$result) {
 		setjsonmysqlerror($has_error,$err_msg,$sql2);
   	}
	else
		$isreceiver = $newrecr;
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