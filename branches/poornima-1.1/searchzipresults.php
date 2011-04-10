<?php
// Application: Christian Missions Connector
// File: 'searchzipresults.php' 
//  search results retrieved and displayed (sort by distance**)
// 
//require_once 'facebook.php';

include_once 'common.php';

// create a results object
class resultsObj{
    var $name;
    var $state;
    var $city;
	var $phone;
	var $email;
	var $religion;
} 

$con = arena_connect();

$saferequest = cmc_safe_request_strip();
$has_error = FALSE;
$err_msg = '';

// need value, fbid, zip, searchradius or value, fbid, country, state, city

if (array_key_exists('value', $saferequest) && array_key_exists('fbid', $saferequest) && array_key_exists('zip', $saferequest) && array_key_exists('searchradius', $saferequest)) {
  // invitation ids, tripid and facebook userid should be provided
  $value = $saferequest['value'];
  $fbid = $saferequest['fbid'];
  $zipcode = $saferequest['zip'];
  $searchradius = $saferequest['searchradius'];
} 
else if (array_key_exists('value', $saferequest) && array_key_exists('fbid', $saferequest) && array_key_exists('country', $saferequest) && array_key_exists('state', $saferequest) && array_key_exists('city', $saferequest)) {
  $value = $saferequest['value'];
  $fbid = $saferequest['fbid'];
  $loc1country = $saferequest['country'];
  $loc1state = $saferequest['state'];
  $loc1city = $saferequest['city'];
  
}
else {
  // error case: all needed variables are not defined
  $has_error = TRUE;
  $err_msg = "Required parameters not defined.";
}

$json = array();

// function to claculate the distance between two locations
function haversine($lat, $lng, $lat2, $lng2) {
  $radius = 6378100; // radius of earth in meters
  $latDist = $lat - $lat2;
  $lngDist = $lng - $lng2;
  $latDistRad = deg2rad($latDist);
  $lngDistRad = deg2rad($lngDist);
  $sinLatD = sin($latDistRad/2.0);
  $sinLngD = sin($lngDistRad/2.0);
  $cosLat1 = cos(deg2rad($lat));
  $cosLat2 = cos(deg2rad($lat2));
  $a = $sinLatD*$sinLatD + $cosLat1*$cosLat2*$sinLngD*$sinLngD;
  if($a<0) $a = -1*$a;
  $c = 2*atan2(sqrt($a), sqrt(1-$a));
  $distance = $radius*$c;
  // convert distance to miles
  $distance = $distance/1609.0;

  return $distance;
}

// function to get zip info
function getZipInfo($zip,&$has_error,&$err_msg,$con) {
$sql  = "SELECT * FROM zipcodes WHERE zipcode='" . $zip . "'";
$query = mysql_query($sql,$con);
if (!$query) {
	$has_error = TRUE;
	$err_msg = "Can't query (query was '$query'): " . mysql_error();
	return FALSE;
}
else {
if(mysql_num_rows($query) < 1)
	return FALSE;
		        
$zipInfo = mysql_fetch_object($query);    
return $zipInfo;  
}

} //end getZipInfo

function getZipsWithin($zip,$miles,&$dists,&$has_error,&$err_msg,$con) {
if(($zipInfo = getZipInfo($zip,$has_error,$err_msg,$con)) === FALSE)
	return FALSE;
	        
$sql = "SELECT zipcode, latitude, longitude from zipcodes";

$query = mysql_query($sql,$con);
if (!$query) {
	$has_error = TRUE;
	$err_msg = "Can't query (query was '$query'): " . mysql_error();
	return FALSE;
}
else {
$dists = array();
$retval = array();
$i=0;
while ($res = mysql_fetch_array($query,MYSQL_ASSOC)) {
	$distance = haversine($zipInfo->latitude,$zipInfo->longitude,$res["latitude"],$res["longitude"]);
	if ($distance <= $miles) {
		$dists[$i] = $distance;
		$retval[$i] = $res["zipcode"];
		$i++;
	}
}
											    
return $retval;
}

} //end zipsWithin


if (!$has_error) {
$profileid = $fbid;

if ($value==1) {

if ((empty($zipcode)) || (empty($searchradius))) {
  $has_error = TRUE;
  $err_msg = "Empty Zipcode or searchradius";
}
else { 

	$result = getZipsWithin($zipcode,$searchradius,$dists,$has_error,$err_msg,$con);
	//sort according to increasing distances
	if (!$result) {
	    $has_error = TRUE;
		$err_msg = "Entered zipcode is not valid";
	}
	else {
		array_multisort($dists, SORT_ASC,SORT_NUMERIC, $result);
		getsearchresults($fbid,$result,$dists,$con,$has_error,$err_msg,$json);
	}
}

}
else if ($value==2) {
// This part is for non-USA locations not based on zip code

if ((empty($loc1country)) || (empty($loc1state)) || (empty($loc1city))) {
  $has_error = TRUE;
  $err_msg = "Empty country, state or city";
}	
else {
// specific location of the user
$str1 = "'http://maps.google.com/maps/api/geocode/json?address=".$loc1country."+".$loc1state."+".$loc1city."&sensor=false'";
$geocode1 = file_get_contents($str1);
$output1= json_decode($geocode1);
$userlatitude = $output1->results[0]->geometry->location->lat;
$userlongitude = $output1->results[0]->geometry->location->lng;

$sql='select users.userid,users.name,users.country,users.state,users.city,users.religion,users.phone,users.emailid from users,skills,skillsselected,countries,countriesselected,regions,regionsselected,durations,durationsselected where users.userid="'.$profileid.'" and users.country="'.$loc1country.'"';

$result = mysql_query($sql,$con);

if (!$result) {
	$has_error = TRUE;
	$err_msg = "Can't query (query was '$query'): " . mysql_error();
}
else {
    $num_rows = mysql_num_rows($result);
    if ($num_rows == 0) {
	echo '<b> There are no other Christian Missions registered in your country. You are the First one. <b/> <br />';
    }
    else {
    	$i=0;
		while($row= mysql_fetch_array($result)){
			$loc2country[$i] = $row['users.country'];
			$loc2state[$i] = $row['users.state'];
			$loc2city[$i] = $row['users.city'];
			$loc2name[$i] = $row['users.name'];
			$loc2id[$i] = $row['users.userid'];
			$loc2relg[$i] = $row['users.religion'];
			$loc2email[$i] = $row['users.emailid'];
			$loc2phone[$i] = $row['users.phone'];
			$str2 = "'http://maps.google.com/maps/api/geocode/json?address=".$loc2country."+".$loc2state."+".$loc2city."&sensor=false'";
			$geocode2 = file_get_contents($str2);
			$output2= json_decode($geocode2);
			$user2latitude = $output2->results[0]->geometry->location->lat;
			$user2longitude = $output2->results[0]->geometry->location->lng;
			// now compute haversine distance and store
			$dists[$i] = haversine($userlatitude,$userlongitude,$user2latitude,$user2longitude);
			$loc[$i] = $i;
			$i++;
		}
    }

	// now sort the dists in ascending order
	array_multisort($dists, SORT_ASC, SORT_NUMERIC, $loc);

	// now obtain the top 5 nearest mission locations or volunteers
	$json['results'] = array();
	
	for ($j=0;$j<count($dists);$j++) {
		// do not show own profile
		if ($loc2id[$loc[$j]] != $fbid) {
			$resobj = new resultsObj;
			if (!isempty($loc2name[$loc[$j]]))
				$resobj->name = $loc2name[$loc[$j]];
			else
				$resobj->name = '';
			if (!isempty($loc2state[$loc[$j]]))
				$resobj->state = $loc2state[$loc[$j]];
			else
				$resobj->state = '';
				
			if (!isempty($loc2city[$loc[$j]]))
				$resobj->city = $loc2city[$loc[$j]];
			else
				$resobj->city = '';
				
			if (!isempty($loc2phone[$loc[$j]]))
				$resobj->phone = $loc2phone[$loc[$j]];
			else
				$resobj->phone = '';
				
			if (!isempty($loc2email[$loc[$j]]))
				$resobj->email = $loc2email[$loc[$j]];
			else
				$resobj->email = '';
				
			if (!isempty($loc2relg[$loc[$j]]))
				$resobj->religion = $loc2relg[$loc[$j]];
			else
				$resobj->religion = '';
				
			$json['results'][] = clone $resobj;
			
			$rebobj = NULL;
		
		}						
	}

}

}
}

}

function getsearchresults($fbid,$result,$dists,$con,&$has_error,&$err_msg,&$json) {

$j=0;
$json['results'] = array();

for ($i=0;$i<count($dists);$i++) {
	$sql = 'SELECT * from users WHERE zipcode="'.$result[$i].'"';
	$res = mysql_query($sql,$con);
	if (!$res) {
		$has_error = TRUE;
		$err_msg = "Can't query (query was '$query'): " . mysql_error();
	}
	else {
		$numrows = mysql_num_rows($res);
		if ($numrows != 0) {
			while ($r = mysql_fetch_object($res)) {
				$checkid = $r->userid;
				// do not include own information
				if ($checkid != $fbid) {
					$json['results'][] = $r;
				}
				$j++;
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
