<?php
// Application: Christian Missions Connector
// File: 'searchresults.php' 
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

if (array_key_exists('fbid', $saferequest) && array_key_exists('adv',$saferequest)) {
  $fbid = $saferequest['fbid'];
  $adv = $saferequest['adv'];
  // basic search
  if ($adv == 0) {
	if (array_key_exists('keys',$saferequest))
		$keywords = $saferequest['keys'];
	else {
  		$has_error = TRUE;
  		$err_msg = "Keys not defined for basic search";
	}
  }
  //advanced search
  else {
	if (array_key_exists('type',$saferequest) && array_key_exists('searchkeys',$saferequest)) {
		$type = $saferequest['type'];
		// The advanced search fields are assumed to be sent from front-end to bank-end in a json-encoded object
		// It is first json_decoded here, and then used by the code here
		$searchkeys = json_decode($saferequest['searchkeys']);
	}
	else {
  		$has_error = TRUE;
  		$err_msg = "Search type and/or search fields not defined for advanced search";
	}

  }

}
else {
  // error case: all needed variables are not defined
  $has_error = TRUE;
  $err_msg = "Required parameters not defined.";
}

$json = array();

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
  $distance = $distance/1609.0;

  return $distance;
}

// function to get zip info
function getZipInfo($zip,&$has_error,&$err_msg,$con) {
$sql  = "SELECT * FROM zipcodes WHERE zipcode='" . $zip . "'";
$query = mysql_query($sql,$con);
if (!$query) {
	setjsonmysqlerror($has_error,$err_msg,$sql);
	return FALSE;
}
else {
if(mysql_num_rows($query) < 1)
        return FALSE;

	$zipInfo = mysql_fetch_object($query);
	return $zipInfo;
}
} //end getZipInfo

function get_rest_of_string(&$sql3,&$sql1,&$sql2,$val,$searchkeys) {

$skills = 0;
$sql2 = '';
$sql1 = ' ';
$sql3='';
$usersinc=0;

if (isset($searchkeys['relg'])) {
  if (strcmp($searchkeys['relg'],"Any")) {
  if ($val ==1) {
        $usersinc = 1;
  	$sql1 = ',users';
  	$sql3 = $sql3.' and users.religion="'.$searchkeys['relg'].'"';
  }
  else
  	$sql3 = $sql3.' and users.religion="'.$searchkeys['relg'].'"';
  }
}
if (isset($searchkeys['medskills'])) {
  if (strcmp($searchkeys['medskills'],"Any")) {
  $sql3 = $sql3.' and skills.skilldesc="'.$searchkeys['medskills'].'"';
  $sql2 = $sql2.' and skills.id=skillsselected.id and users.userid=skillsselected.userid';
  	if ($val==1) {
  if ($usersinc == 0) {
  	$sql1 = $sql1.',users,skills,skillsselected';
	$usersinc = 1;
	}
  }
  else
  	$sql1 = $sql1.',skills,skillsselected';
  $skills = 1;
  }
}
if (isset($searchkeys['otherskills'])) {
  if (strcmp($searchkeys['otherskills'],"Any")) {
  $sql3 = $sql3.' and skills.skilldesc="'.$searchkeys['otherskills'].'"';
  if ($skills==0) {
  	$sql2 = $sql2.' and skills.id=skillsselected.id and users.userid=skillsselected.userid';

  	if ($val==1) {
  if ($usersinc == 0) {
  	$sql1 = $sql1.',users,skills,skillsselected';
	$usersinc = 1;
	}
  }
  else
        $sql1 = $sql1.',skills,skillsselected';

  	$skills = 1;
  }
  }
}
if (isset($searchkeys['spiritserv'])) {
  if (strcmp($searchkeys['spiritserv'],"Any")) {
  $sql3 = $sql3.' and skills.skilldesc="'.$searchkeys['spiritserv'].'"';
  if ($skills==0) {
  	$sql2 = $sql2.' and skills.id=skillsselected.id and users.userid=skillsselected.userid';
  	if ($val==1) {
  if ($usersinc == 0) {
  	$sql1 = $sql1.',users,skills,skillsselected';
	$usersinc = 1;
	}
  }
  else
        $sql1 = $sql1.',skills,skillsselected';

  	$skills = 1;
  }
  }
}
if (isset($searchkeys['country'])) {
  if (strcmp($searchkeys['country'],"Any")) {
  $sql3 = $sql3.' and countries.longname="'.$searchkeys['country'].'"';
  	if ($val==1) {
  if ($usersinc == 0) {
  	$sql1 = $sql1.',users,countries,countriesselected';
	$usersinc = 1;
	}
  }
  else
  $sql1 = $sql1.',countries,countriesselected';

  $sql2 = $sql2.' and countries.id=countriesselected.id and users.userid=countriesselected.userid';
  }
}
if (isset($searchkeys['region'])) {
  if (strcmp($searchkeys['region'],"Any")) {
  $sql3 = $sql3.' and regions.name="'.$searchkeys['region'].'"';
  if ($val==1) {
  if ($usersinc == 0) {
  	$sql1 = $sql1.',users,regions,regionsselected';
	$usersinc = 1;
  }
  }
  else
  $sql1 = $sql1.',regions,regionsselected';

  $sql2 = $sql2.' and regions.id=regionsselected.id and users.userid=regionsselected.userid';
  }
}
if (isset($searchkeys['dur'])) {
  if (strcmp($searchkeys['dur'],"Any")) {
  $sql3 = $sql3.' and durations.name="'.$searchkeys['dur'].'"';
  	if ($val==1) {
  if ($usersinc == 0) {
  	$sql1 = $sql1.',users,durations,durationsselected';
	$usersinc = 1;
  }
  }
  else
  $sql1 = $sql1.',durations,durationsselected';

  $sql2 = $sql2.' and durations.id=durationsselected.id and users.userid=durationsselected.userid';
  }
}


}

if (!$has_error) {
$profileid = $fbid;

// get the zipcode of the current user
$sql = 'select zipcode from users where userid="'.$fbid.'"';
$result = mysql_query($sql,$con);
if (!$result) {
	setjsonmysqlerror($has_error,$err_msg,$sql);
}
else {
$numrows = mysql_num_rows($result);
if ($numrows != 0) {
$row = mysql_fetch_array($result,MYSQL_ASSOC);
$myzipcode = $row['zipcode'];
}

}

$json['results'] = array();

// This is for basic search
if ($adv==0) {

if (!empty($keywords)) {

if (strstr($keywords,',')) {
 $keys = explode(",",$keywords);
}
else if (strstr($keywords,' ')) {
 $keys = explode(" ",$keywords);
}
else 
 $keys = explode(" ",$keywords);

$clauses1=array();
$clauses2=array();
$clauses3=array();
$clauses4=array();
$clauses5=array();
$clauses6=array();
$clauses7=array();
$clauses8=array();
$clauses9=array();
foreach($keys as $term)
{
  //remove any chars you don't want to be searching - adjust to suit your requirements
  $clean=trim(preg_replace('/[^a-z0-9]/i', '', $term));   
  if (!empty($clean)) {
	//note use of mysql_escape_string - while not strictly required
	//in this example due to the preg_replace earlier, it's good
	//practice to sanitize your DB inputs in case you modify that filter...
	$clauses1[]="name like '%".mysql_real_escape_string($clean)."%'";
	$clauses2[]="state like '%".mysql_real_escape_string($clean)."%'";
	$clauses3[]="city like '%".mysql_real_escape_string($clean)."%'";
	$clauses4[]="zipcode like '%".mysql_real_escape_string($clean)."%'";
	$clauses5[]="phone like '%".mysql_real_escape_string($clean)."%'";
	$clauses6[]="email like '%".mysql_real_escape_string($clean)."%'";
	$clauses7[]="religion like '%".mysql_real_escape_string($clean)."%'";
	$clauses8[]="website like '%".mysql_real_escape_string($clean)."%'";
	$clauses9[]="organization like '%".mysql_real_escape_string($clean)."%'";
  }
}
		
$filter1 = '(';
if (!empty($clauses1))
	$filter1 = $filter1.implode(' AND ',$clauses1);
if (!empty($clauses2))
	$filter1 = $filter1.' OR '.implode(' AND ',$clauses2);
if (!empty($clauses3))
	$filter1 = $filter1.' OR '.implode(' AND ',$clauses3);
if (!empty($clauses4))
	$filter1 = $filter1.' OR '.implode(' AND ',$clauses4);
if (!empty($clauses5))
	$filter1 = $filter1.' OR '.implode(' AND ',$clauses5);
if (!empty($clauses6))
	$filter1 = $filter1.' OR '.implode(' AND ',$clauses6);
if (!empty($clauses7))
	$filter1 = $filter1.' OR '.implode(' AND ',$clauses7);
if (!empty($clauses8))
	$filter1 = $filter1.' OR '.implode(' AND ',$clauses8);
if (!empty($clauses9))
	$filter1 = $filter1.' OR '.implode(' AND ',$clauses9);

$filter1 = $filter1.')';

$filter = '(';
if (!empty($clauses1))
	$filter = $filter.implode(' OR ',$clauses1);
if (!empty($clauses2))
	$filter = $filter.' OR '.implode(' OR ',$clauses2);
if (!empty($clauses3))
	$filter = $filter.' OR '.implode(' OR ',$clauses3);
if (!empty($clauses4))
	$filter = $filter.' OR '.implode(' OR ',$clauses4);
if (!empty($clauses5))
	$filter = $filter.' OR '.implode(' OR ',$clauses5);
if (!empty($clauses6))
	$filter = $filter.' OR '.implode(' OR ',$clauses6);
if (!empty($clauses7))
	$filter = $filter.' OR '.implode(' OR ',$clauses7);
if (!empty($clauses8))
	$filter = $filter.' OR '.implode(' OR ',$clauses8);
if (!empty($clauses9))
	$filter = $filter.' OR '.implode(' OR ',$clauses9);

$filter = $filter.')';

	//build and execute the required SQL
	if (!strcmp($filter1,'()')) {
	$sql4 = '';
	}
	else {
	
	// get the matches with an 'AND' operator first
	$sql='select * from users where userid!="'.$fbid.'" and '.$filter1;

	$result = mysql_query($sql,$con);
	if (!$result) {
		setjsonmysqlerror($has_error,$err_msg,$sql);
	}
	else {
	$numrows1 = mysql_num_rows($result);
	if ($numrows1>0) {
	        // store zipcodes and row numbers, then sort
		$k=0;
		while ($row = mysql_fetch_array($result,MYSQL_NUM)) {
			$storerowa[$k] = $k;
			$storeida[$k] = $row[0];
			$storenamea[$k] = $row[1];
			$zipnowa[$k] = $row[6];
			$k++;
		}

		// We now have the user zip code and the list of search zip codes
		// Find the distances and order them in ascending order
		if (isset($myzipcode)) {
		$myzipinfo = getZipInfo($myzipcode,$has_error,$err_msg,$con);
		for ($i=0;$i<count($zipnowa);$i++) {
			if ($zipnowa[$i] == 0)
				$zipnowa[$i] = $myzipcode;

			$otherzipinfo = getZipInfo($zipnowa[$i],$has_error,$err_msg,$con);
			$distsa[$i] = haversine($myzipinfo->latitude,$myzipinfo->longitude,$otherzipinfo->latitude,$otherzipinfo->longitude);
		}

	        // now sort the results in ascending order according to distance	
		array_multisort($distsa, SORT_ASC, SORT_NUMERIC,$storerowa,$zipnowa,$storeida,$storenamea);
		}
		
		// now print the results
		$sql4 = '';

		for ($j=0;$j<count($storerowa);$j++) {
		        if ($storeida[$j]!=0) {
				// store the results into a json array
				$json['results'][] = $storeida[$j];

				if ($j==0)
				$sql4 = $sql4.strval($storeida[$j]);
				else
				$sql4 = $sql4.','.strval($storeida[$j]);
			}

		}
		mysql_free_result($result);			
		
	}
	}
        }


	if (empty($sql4) && (!strcmp($filter,'()'))) {
		
	}
	else {

	if (!empty($sql4))
	$sql='select * from users where userid!="'.$fbid.'" and userid NOT IN('.$sql4.') and '.$filter;
	else 
	$sql='select * from users where userid!="'.$fbid.'" and '.$filter;
		
	$result = mysql_query($sql,$con);

	if ($result) {
	$numrows = mysql_num_rows($result);
	if (($numrows ==0) && (empty($sql4))) {

	}
	else if ($numrows==0) {
	
	}
	else {

	    // store zipcodes and row numbers, then sort
		$k=0;
		while ($row = mysql_fetch_array($result,MYSQL_NUM)) {
			$storerow[$k] = $k;
			$storeid[$k] = $row[0];
			$storename[$k] = $row[1];
			$zipnow[$k] = $row[6];
			$k++;
		}

		// We now have the user zip code and the list of search zip codes
		// Find the distances and order them in ascending order
		if (isset($myzipcode)) {
		$myzipinfo = getZipInfo($myzipcode,$has_error,$err_msg,$con);
		for ($i=0;$i<count($zipnow);$i++) {
			if ($zipnow[$i] == 0)
				$zipnow[$i] = $myzipcode;

			$otherzipinfo = getZipInfo($zipnow[$i],$has_error,$err_msg,$con);
			$dists[$i] = haversine($myzipinfo->latitude,$myzipinfo->longitude,$otherzipinfo->latitude,$otherzipinfo->longitude);
		}

	        // now sort the results in ascending order according to distance	
		array_multisort($dists, SORT_ASC, SORT_NUMERIC,$storerow,$zipnow,$storeid,$storename);
		}
		
		// now print the results
		for ($j=0;$j<count($storerow);$j++) {
		        if ($storeid[$j]!=0) {
				$json['results'][] = $storeid[$j];
			}

		}
		mysql_free_result($result);
	}
}
else {
	setjsonmysqlerror($has_error,$err_msg,$sql);
}

}
}

}
// This is for advanced search
else {

if($type==1){
  $friends=array();

  $sql='select users.userid,users.zipcode from users';

  get_rest_of_string($sql3,$sql1,$sql2,0,$searchkeys);
  $sql = $sql.$sql1.' where isreceiver="1"'.$sql3.$sql2;
  $result = mysql_query($sql,$con);

if($result) {
    $num_rows = mysql_num_rows($result);
    $j = 0;
    while($row= mysql_fetch_array($result,MYSQL_ASSOC)){
      $id = $row['userid'];
      $friends[$j] = $id;
	  if (empty($row['zipcode']))
		$zipnow[$j] = 0;
		else
		$zipnow[$j] = $row['zipcode'];
		
      $j++;
     }
}
else {
      setjsonmysqlerror($has_error,$err_msg,$sql);
}

    if($num_rows==0){
	// Nothing to display or return
    }
	else {
	
	// the friends list needs to be sorted according to distance -- new version
		// We now have the user zip code and the list of search zip codes
		// Find the distances and order them in ascending order
		if (isset($myzipcode)) {
		$myzipinfo = getZipInfo($myzipcode,$has_error,$err_msg,$con);
		for ($i=0;$i<count($zipnow);$i++) {
		    // if zipcode is not known, make the distance very big
			if ($zipnow[$i] == 0)
				$dists[$i] = 1000000000;
			else {

			$otherzipinfo = getZipInfo($zipnow[$i],$has_error,$err_msg,$con);
			$dists[$i] = haversine($myzipinfo->latitude,$myzipinfo->longitude,$otherzipinfo->latitude,$otherzipinfo->longitude);
			}
		}	

	        // now sort the results in ascending order according to distance	
		if (!empty($dists))
			array_multisort($dists, SORT_ASC, SORT_NUMERIC,$friends);

		}	
	
	
		$json['searchids'] = array();
    		foreach ($friends as $currentfriend){
      			if (($currentfriend > 0) && ($currentfriend != $fbid)) {
	  			$json['searchids'][] = $currentfriend;
     			}
		}
	
	}

      }

      //Volunteers
      if($type==2){
        $friends=array();
  	
  	$sql='select users.userid,users.zipcode from users';
	get_rest_of_string($sql3,$sql1,$sql2,0,$searchkeys);
  	$sql = $sql.$sql1.' where isreceiver="0"'.$sql3.$sql2;
	$result = mysql_query($sql,$con);
        if($result){
        	$num_rows = mysql_num_rows($result);
	 	$j=0;
          	while($row= mysql_fetch_array($result,MYSQL_ASSOC)) {
            		$id = $row['userid'];
            		$friends[$j] = $id;  
	     		if (empty($row['zipcode']))
				$zipnow[$j] = 0;
			else
				$zipnow[$j] = $row['zipcode'];
	    		$j++;
          	}
	  }
	  else {
          	setjsonmysqlerror($has_error,$err_msg,$sql);
          }

    	if($num_rows==0){
		// nothing to display
    	}
	else {		  
		  
		// the friends list needs to be sorted according to distance -- new version
		// We now have the user zip code and the list of search zip codes
		// Find the distances and order them in ascending order
		if (isset($myzipcode)) {
		$myzipinfo = getZipInfo($myzipcode,$has_error,$err_msg,$con);
		for ($i=0;$i<count($zipnow);$i++) {
		    // if zipcode is not known, make the distance very big
			if ($zipnow[$i] == 0)
				$dists[$i] = 1000000000;
			else {

			$otherzipinfo = getZipInfo($zipnow[$i],$has_error,$err_msg,$con);
			$dists[$i] = haversine($myzipinfo->latitude,$myzipinfo->longitude,$otherzipinfo->latitude,$otherzipinfo->longitude);
			}
		}
	        // now sort the results in ascending order according to distance	
		if (!empty($dists))
			array_multisort($dists, SORT_ASC, SORT_NUMERIC,$friends);

		}	
		
		$json['searchids'] = array();
          	foreach ($friends as $currentfriend) {
	    		if (($currentfriend > 0) && ($currentfriend!=$fbid)) {
				$json['searchids'][] = $currentfriend;
	    		}
          	}

    	}

	}

	// This mean trip search results
            if($type==3){

	function getdatestring($year,$month,$date,$hour,$min,$sec) {

	if ($month<10)
        	$smonth = strval($month);
	else
	        $smonth = strval($month);

	if ($date<10)
		$sdate = strval($date);
	else
		$sdate = strval($date);

	$res = $year.'-'.$smonth.'-'.$sdate.' '.strval($hour).':'.strval($min).':'.strval($sec);

	return $res;
	}

        $todayy = date("Y");
	$todaym = date("m");
	$todayd = date("d");
	$todayH = date("H");
	$todayi = date("i");
	$todays = date("s");

	$today = getdatestring($todayy,$todaym,$todayd,$todayH,$todayi,$todays);
	$unixtime = strtotime($today);

	$triparray=array();

	$sql='select distinct trips.tripname, trips.id from trips';
	$sql4 = ' where trips.departure >="'.$today.'"';
	get_rest_of_string($sql3,$sql1,$sql2,1,$searchkeys);
  		$sql = $sql.$sql1.$sql4.$sql3.$sql2;
	 
	  $result = mysql_query($sql,$con);

	  if($result){
		$numrows = mysql_num_rows($result);
		if ($numrows == 0) {

		}
		else {
		$tstoreid = array();
		$tstorename = array();
		$json['tripids'] = array();
		$json['tripnames'] = array();
                while($row= mysql_fetch_array($result,MYSQL_NUM)){
                  $id = $row[1];
                  $name=$row[0];
		  $tstoreid[] = $id;
		  $tstorename[] = $name;
		  $json['tripids'][] = $id;
		  $json['tripnames'][] = $name;
		    }		
		 }
	 }
	else {
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
