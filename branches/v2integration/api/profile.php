<?php
// Application: Christian Missions Connector
// File: 'profile.php' 
//   shows profile of given person
//   requires input userid
// 
// Application: Christian Missions Connector
// File: 'profileV.php' 
//   profile creation- volunteer


include_once 'common.php';

$con = arena_connect();

$saferequest = cmc_safe_request_strip();
$has_error = FALSE;
$err_msg = '';

if (array_key_exists('userid', $saferequest) && array_key_exists('fbid', $saferequest)) {
  // invitation ids, tripid and facebook userid should be provided
  $showuserid = $saferequest['userid'];
  $fbid = $saferequest['fbid'];
} 
else if (array_key_exists('fbid', $saferequest)) {
  $fbid = $saferequest['fbid'];
  $showuserid = $fbid;
}
else {
  // error case: all needed variables are not defined
  $has_error = TRUE;
  $err_msg = "Required parameters not defined.";
}

$json = array();

if (!$has_error) {

$sql = 'select * from users where userid="'.$showuserid.'"';
$result = mysql_query($sql,$con);

if(!$result) {
 	setjsonmysqlerror($has_error,$err_msg,$sql);
}
else {

function cmc_profile_render_id_join($title2,$title,$desc, $descdb, $selecteddb, $fbid, &$msg, &$k,&$has_error,&$err_msg,&$json,$con) {
  $sql = "SELECT * FROM ".$descdb.
     " JOIN ".$selecteddb." ON ".$descdb.".id = ".$selecteddb.".id".
     " WHERE ".$selecteddb.".userid='".$fbid."'";
  $result = mysql_query($sql,$con);
  if (!$result) {
    setjsonmysqlerror($has_error,$err_msg,$sql);
  }
  else {
    $i=0;
    while($row= mysql_fetch_array($result)) {

  if ($i==0) {
    if ($k==0) {
	  $json[str_replace (" ", "", $title2)] = array();
      $k++;
    }
	$json[str_replace (" ", "", $title2)][str_replace (" ", "", $title)] = array();
	$json[str_replace (" ", "", $title2)][str_replace (" ", "", $title)."id"] = array();
  }
  
  $i++;
	  
	  $json[str_replace (" ", "", $title2)][str_replace (" ", "", $title)][] = $row[$desc];
	  $json[str_replace (" ", "", $title2)][str_replace (" ", "", $title)."id"][] = $row["id"];
    }
  }
}

function cmc_profile_render_skills($title, $type, $fbid,&$has_error,&$err_msg,&$json,$con) {
  $sql = "SELECT * FROM skills".
       " JOIN skillsselected ON skills.id = skillsselected.id".
       " WHERE skills.type=".$type." AND skillsselected.userid='".$fbid."'";
  $result = mysql_query($sql,$con);
  if (!$result) {
  	setjsonmysqlerror($has_error,$err_msg,$sql);
  }
  else {
    $i=0;
    while($row= mysql_fetch_array($result)){
      if ($i==0) {
	  $json[str_replace (" ", "", $title)] = array();
	  $json[str_replace (" ", "", $title)."id"] = array();
      }
      $i++;
	  $json[str_replace (" ", "", $title)][] = $row['skilldesc'];
	  $json[str_replace (" ", "", $title)."id"][] = $row['id'];
    }
  }
}

if(mysql_num_rows($result) != 0) {

  $json['exists'] = 1;
  while($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
    $name = $row['name'];
    $organization = $row['organization'];
    $isleader = $row['isreceiver'];
	$json['isreceiver'] = $row['isreceiver'];
    $zip = $row['zipcode'];
    $email = $row['email'];
    $misexp = $row['missionsexperience'];
    $relg = $row['religion'];
    $aboutme = $row['aboutme'];
    $website = $row['website'];
    $partnersite = $row['partnersite']; 
    $phone = $row['phone'];
    $state = $row['state'];
    $city = $row['city'];
  }

  if (empty($name)) {

       // This call is no longer supported
       //$info = $fb->api_client->users_getInfo($showuserid, 'name,email');

    // Get the name information directly from the facebook profile pages
    $name = get_name_from_fb_using_curl($showuserid);

  $sql2 = 'update users set name="'.$name.'" where userid="'.$showuserid.'"';
  $result2 = mysql_query($sql2,$con);
  if (!$result2) {
  		setjsonmysqlerror($has_error,$err_msg,$sql2);
  }
  
  } 

  if (!empty($name))
	$json['name'] = $name;

  if ($isleader == 1) {
  if (!empty($organization))
	$json['AgencyName'] = $organization;
  if (!empty($website))
	$json['AgencyWebsite'] = $website;
  }
  
  if (!empty($aboutme))
	$json['about'] = $aboutme;

  if (!empty($zip))
	$json['zip'] = $zip;
  if (!empty($email))
	$json['email'] = $email;
  if (!empty($phone))
	$json['phone'] = $phone;

  if (!empty($misexp))
	$json['misexp'] = $misexp;
  
  if (!empty($relg))
	$json['relg'] = $relg;

  if ($isleader == 1) {
  cmc_profile_render_skills("Facility Medical Offerings", '4', $showuserid,$has_error,$err_msg,$json,$con);
  cmc_profile_render_skills("Facility Non_Medical Offerings", '5', $showuserid,$has_error,$err_msg,$json,$con);
  }
  
  cmc_profile_render_skills("Medical Skills", '1', $showuserid,$has_error,$err_msg,$json,$con);
  cmc_profile_render_skills("Non_Medical Skills", '2', $showuserid,$has_error,$err_msg,$json,$con);
  cmc_profile_render_skills("Spiritual Skills", '3', $showuserid,$has_error,$err_msg,$json,$con);
  
  $pp=-1;
  cmc_profile_render_id_join("States","State",'longname', 'usstates', 'usstatesselected', $showuserid, $message, $pp,$has_error,$err_msg,$json,$con);

  if (!empty($city)) {
	$json['city'] = $city;
  }
  
  $kk=0;
  cmc_profile_render_id_join("Geographic Areas of Interest","Regions",'name', 'regions', 'regionsselected', $showuserid, $message, $kk,$has_error,$err_msg,$json,$con);

  cmc_profile_render_id_join("Geographic Areas of Interest","Countries",'longname', 'countries', 'countriesselected', $showuserid, $message, $kk,$has_error,$err_msg,$json,$con);

  $pp=-1;
  cmc_profile_render_id_join("Durations","Preferred Duration of Mission Trips",'name', 'durations', 'durationsselected', $showuserid, $message, $pp,$has_error,$err_msg,$json,$con);

  $json['tripid'] = array();
  $json['trips'] = array();
  $trips = array();
  $sql = "select tripid from tripmembers where userid='".$showuserid."'";
  $result = mysql_query($sql,$con);
  if($result) {
    $ii=0;
    while($row= mysql_fetch_array($result)) {
      $tid=$row['tripid'];
	  $json['tripid'][] = $tid;
      $sql2 = 'select tripname from trips where id="'.$tid.'"';
      $result2 = mysql_query($sql2,$con);
	  if (!$result2) {
	  		setjsonmysqlerror($has_error,$err_msg,$sql2);
			continue 1;
	  }
	  else {
		$row2 = mysql_fetch_array($result2);
		$tname = $row2['tripname'];
		$trips[]=$tname;    
		$json['trips'][] = $trips[$ii];
		$ii++;
	  }
    }
  } else {
  		setjsonmysqlerror($has_error,$err_msg,$sql);
  }
  
} else {
		$json['exists'] = 0;		
  		$has_error = TRUE;
		$err_msg = "User does not have a CMC profile";
}

}

}

$json['has_error'] = $has_error;

if ($has_error) {
  $json['err_msg'] = $err_msg;
}

echo json_encode($json);
  
?>  
