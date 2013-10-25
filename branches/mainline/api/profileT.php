<?php
// Application: Christian Missions Connector
// File: 'profileT.php' 
//  mission trip profile as seen by public- no edits from this page
// 
//require_once 'facebook.php';
include_once 'common.php';
$con = arena_connect();

$saferequest = cmc_safe_request_strip($con);
$has_error = FALSE;
$err_msg = '';

if (array_key_exists('fbid', $saferequest) && array_key_exists('tripid', $saferequest)) {
  $fbid = $saferequest['fbid'];
  $tid = $saferequest['tripid'];
}
else {
  // error case: all needed variables are not defined
  $has_error = TRUE;
  $err_msg = "Required parameters not defined.";
}

$json = array();

if (!$has_error) {
$json['tripid'] = $tid;
$sql = 'select * from trips where id="'.$tid.'"';
$result = $con->query($sql);

if (!$result) {
	setjsonmysqlerror($has_error,$err_msg,$sql);
}
else {

function cmc_profile_render_skills($title, $type, $tid,&$has_error,&$err_msg,&$json,$con) {
  $sql = "SELECT * FROM skills".
       " JOIN skillsselectedtrips ON skills.id = skillsselectedtrips.id".
       " WHERE skills.type=".$type." AND skillsselectedtrips.tripid='".$tid."'";
  $result = $con->query($sql);
  if (!$result) {
  	setjsonmysqlerror($has_error,$err_msg,$sql);
  }
  else {
    $i=0;
    while($row= $result->fetch_array()){
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

  $num_rows = $result->num_rows;

  cmc_profile_render_skills("Medical Skills", '1', $tid,$has_error,$err_msg,$json,$con);
  cmc_profile_render_skills("Non_Medical Skills", '2', $tid,$has_error,$err_msg,$json,$con);
  cmc_profile_render_skills("Spiritual Skills", '3', $tid,$has_error,$err_msg,$json,$con);  
  
  if ($num_rows > 0) {
  while($row= $result->fetch_array()) {
    $name = $row['tripname'];
	if (!empty($name))
		$json['tripname'] = $name;
    $creatorid = $row['creatorid'];
	$json['creatorid'] = $creatorid;
    $sql2 = 'select * from users where userid="'.$creatorid.'"';
    $result2 = $con->query($sql2);
	if (!$result2) {
		setjsonmysqlerror($has_error,$err_msg);
		continue 1;
	}
	else {
		$row2 = $result2->fetch_array();
		$towner=$row2['name'];
		if (!empty($towner))
			$json['tripowner'] = $towner;
		$tripdesc=$row['tripdesc'];
		if (!empty($tripdesc))
			$json['tripdesc'] = $tripdesc;
		$phone=$row['phone'];
		if (!empty($phone))
			$json['phone'] = $phone;
		$languages=$row['Languages'];
		if (!empty($languages)) {
			$json['languages'] = $languages;
			$json['languageid'] = array();
      $test = strpos($languages,',');
      if ($test !== false) {
			$languages = explode(",", $languages); 
				foreach($languages as $lg) {
					$sqll = 'select * from languages where englishname="'.$lg.'"';
					$resultl = $con->query($sqll);	
					if (!$resultl) {
						setjsonmysqlerror($has_error,$err_msg);
						continue 1;
					}
					else {
						$rowl = $resultl->fetch_array();
						$json['languageid'][] = $rowl['id'];
					}
				}
			}
			else {
				$sqll = 'select * from languages where englishname="'.$languages.'"';
				$resultl = $con->query($sqll);	
				if (!$resultl) {
					setjsonmysqlerror($has_error,$err_msg);
				}
				else {
					$rowl = $resultl->fetch_array();
					$json['languageid'][] = $rowl['id'];
				}				
			}
		}
		$email=$row['email'];
		if (!empty($email))
			$json['email'] = $email;
		$web=$row['website'];		
    if (!empty($web))
      $json['website'] = $web;
		$acco=$row['accommodationlevel'];		
    if (!empty($acco))
      $json['acco'] = $acco;
		$dur=$row['durationid'];
		if (!empty($dur))
			$json['duration'] = $dur;
		$stage=$row['isinexecutionstage'];
		if (!empty($stage)) {
      //$json['tripstage'] = $stage;
      
		if ($stage==1)
			$json['tripstage'] = "Planning Phase";  
		else if ($stage==2)
			$json['tripstage'] = "Execution Phase";  
      
		}
		$onneeds=$row['ongoingneeds'];
      
		if ($onneeds==1)
			$json['onneeds'] = $onneeds;  
		else
			$json['onneeds'] = $onneeds;  
      
		$triptimeframe=$row['flextimeframe'];
      
		if ($triptimeframe==1)
			$json['timeframe'] = $triptimeframe;  
		else
			$json['timeframe'] = $triptimeframe;  
      
		$destination=$row['destination'];
		if (!empty($destination))
			$json['destination'] = $destination;
		$destinationcountry=$row['country'];
		if (!empty($destinationcountry)) {
	     //$json['countryid'] = array();
			 $json['destinationcountry'] = $destinationcountry;
				$sqlc = 'select * from countries where longname="'.$destinationcountry.'"';
				$resultc = $con->query($sqlc);	
				if (!$resultc) {
					setjsonmysqlerror($has_error,$err_msg);
				}
				else {
					$rowc = $resultc->fetch_array();
					$json['countryid'] = $rowc['id'];
				}				

    }
		$departn = explode(' ',$row['departure']);
		$depart = explode('-',$departn[0]);
		if (!empty($depart)) {
			$json['departyear'] = $depart[0];
			$json['departmonth'] = $depart[1];
			$json['departday'] = $depart[2];
		}
		$returnn = explode(' ',$row['returning']);
		$return = explode('-',$returnn[0]);
		if (!empty($return)) {
			$json['returnyear'] = $return[0];
			$json['returnmonth'] = $return[1];
			$json['returnday'] = $return[2];
		}
		$zip=$row['zipcode'];	
		if (!empty($zip))
			$json['zip'] = $zip;
		$relg=$row['religion'];
		if (!empty($relg))
			$json['religion'] = $relg;
		$numpeople = $row['numpeople'];
		if (!empty($numpeople))
			$json['numpeople'] = $numpeople;
		$website = $row['website'];
		if (!empty($website))
			$json['website'] = $web;
	}
  }

// see if the user is already part of this trip

$sql = 'select * from tripmembers where userid="'.$fbid.'" and tripid="'.$tid.'"';
$result = $con->query($sql);

if (!$result) {
	setjsonmysqlerror($has_error,$err_msg,$sql);
}
else {

if ($result->num_rows>0) {
	$json['member'] = true;
}
else {
	$json['member'] = false;
}
}

// now get all the tripmembers information to display on the trips page
$sql = 'select * from tripmembers where tripid="'.$tid.'"';
$result = $con->query($sql);

if (!$result) {
	setjsonmysqlerror($has_error,$err_msg,$sql);
}
else {

$json['memberids'] = array();

  if ($result->num_rows > 0) {
  while($row= $result->fetch_array()) {
    if ($creatorid != $row['userid']) {
      $json['memberids'][] = $row['userid'];
    }
  }
  }
}

}
else {
  $has_error = TRUE;
  $err_msg = "No trip identified";
}

}

}

$json['has_error'] = $has_error;

if ($has_error) {
  $json['err_msg'] = $err_msg;
}

echo json_encode($json);

?>
