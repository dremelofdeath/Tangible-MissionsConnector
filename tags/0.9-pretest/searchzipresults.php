<?php
// Application: Christian Missions Connector
// File: 'searchzipresults.php' 
//  search results retrieved and displayed (sort by distance**)
// 
//require_once 'facebook.php';

include_once 'common.php';
ob_start();

$fb = cmc_startup($appapikey, $appsecret,0);
$fbid = $fb->require_login("publish_stream,read_stream");

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
function getZipInfo($zip) {
$sql  = "SELECT * FROM zipcodes WHERE zipcode='" . $zip . "'";
$query = mysql_query($sql);
if(mysql_num_rows($query) < 1)
	return FALSE;
		        
$zipInfo = mysql_fetch_object($query);    
return $zipInfo;  
} //end getZipInfo

function getZipsWithin($zip,$miles,&$dists) {
if(($zipInfo = getZipInfo($zip)) === FALSE)
	return FALSE;
	        
$sql = "SELECT zipcode, latitude, longitude from zipcodes";

$query = mysql_query($sql);
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
} //end zipsWithin

?>
<br/><br/>

<?php
$profileid = $fbid;

//$con = mysql_connect(localhost,"arena", "***arena!password!getmoney!getpaid***");
$con = mysql_connect(localhost,"poornima", "MYdata@1");
if(!$con) {
  die('Could not connect: ' .  mysql_error());
}

mysql_select_db("missionsconnector", $con);

session_start();
$value = $_SESSION['locvalue'];

if ($value==1) {
// get the zip and search radius from form
if (isset($_REQUEST['zip'])) {
	$zipcode = $_REQUEST['zip'];
	$_SESSION['zip'] = $zipcode;
}
if (isset($_REQUEST['searchradius'])) {
	$searchradius = $_REQUEST['searchradius'];
	$_SESSION['searchradius'] = $searchradius;	
}

if ((empty($zipcode)) || (empty($searchradius))) {
  echo "<fb:redirect url='searchbyzip.php?error=1' />";
}
else { 
//if (isset($_REQUEST['zip'])) {

	$result = getZipsWithin($zipcode,$searchradius,$dists);
	//sort according to increasing distances
	if (!$result) {
		echo 'Entered zipcode is not valid <br /><br />';
		echo"<a href='http://apps.facebook.com/missionsconnector/searchbyzip.php'>Go back to search by zipcode</a>";
	}
	else {
	array_multisort($dists, SORT_ASC,SORT_NUMERIC, $result);
	printsearchresults($result,$dists);
	}
}

}
else if ($value==2) {
// This part is for non-USA locations not based on zip code
if (isset($_REQUEST['country'])) {
	$loc1country = $_REQUEST['country'];
	$_SESSION['lcountry'] = $loc1country;
}
if (isset($_REQUEST['state'])){
	$loc1state = $_REQUEST['state'];
	$_SESSION['lstate'] = $loc1state;	
}
if (isset($_REQUEST['city'])) {
	$loc1city = $_REQUEST['city'];
	$_SESSION['lcity'] = $loc1city;
}

if ((empty($loc1country)) || (empty($loc1state)) || (empty($loc1city))) {
  echo "<fb:redirect url='searchbyzip.php?error=1' />";
}	
else {
// specific location of the user
$str1 = "'http://maps.google.com/maps/api/geocode/json?address=".$loc1country."+".$loc1state."+".$loc1city."&sensor=false'";
$geocode1 = file_get_contents($str1);
$output1= json_decode($geocode1);
$userlatitude = $output1->results[0]->geometry->location->lat;
$userlongitude = $output1->results[0]->geometry->location->lng;

$sql='select users.userid,users.name,users.country,users.state,users.city,users.religion,users.phone,users.emailid from users,skills,skillsselected,countries,countriesselected,regions,regionsselected,durations,durationsselected where users.userid="'.$profileid.'" and users.country="'.$loc1country.'"';

if($result = mysql_query($sql)){
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
}

// now sort the dists in ascending order
array_multisort($dists, SORT_ASC, SORT_NUMERIC, $loc);

// now obtain the top 5 nearest mission locations or volunteers
echo '<b> Search Results <b/> <br />';
for ($j=0;$j<count($dists);$j++) {

echo "<fb:profile-pic uid=".$loc2id[$loc[$j]]." linked='false' /> <fb:name uid=".$loc2id[$loc[$j]]." linked='false' shownetwork='true' /><a href='http://apps.facebook.com/missionsconnector/profile.php?userid=".$loc2id[$loc[$j]]."'><br/>  See Profile</a><br/><br/>";

  	//$profile_pic =  "http://graph.facebook.com/".$loc2id[$loc[$j]]."/picture";
  	//echo $j.")<img src=\"" . $profile_pic . "\" />";
  	//echo "<br />";
	if (!isempty($loc2name[$loc[$j]]))
		echo "Name: ".$loc2name[$loc[$j]]."<br />"; 
	if (!isempty($loc2state[$loc[$j]]))
		echo "State: ".$loc2state[$loc[$j]]."<br />";
	if (!isempty($loc2city[$loc[$j]]))
		echo "City: ".$loc2city[$loc[$j]]."<br />";
	if (!isempty($loc2phone[$loc[$j]]))
		echo "Phone: ".$loc2phone[$loc[$j]]."<br />";
	if (!isempty($loc2email[$loc[$j]]))
		echo "Email: ".$loc2email[$loc[$j]]."<br />";
	if (!isempty($loc2relg[$loc[$j]]))
		echo "Religion: ".$loc2relg[$loc[$j]]."<br />";
								
  	echo "<br /><br />";
}

}
}

function printsearchresults($result,$dists) {

echo "<b>Your Search Results: <br /><br /></b>";

$j=0;

//foreach($result as $r) {
for ($i=0;$i<count($dists);$i++) {
$sql = 'SELECT * from users WHERE zipcode="'.$result[$i].'"';
$res = mysql_query($sql);
$numrows = mysql_num_rows($res);
if ($numrows != 0) {
while ($r = mysql_fetch_array($res,MYSQL_ASSOC)) {
foreach($r as $key => $val) {
	//if (!strcmp($key,"name")) || (!strcmp($key,"zipcode")) || (!strcmp($key,"phone")) || (!strcmp
	if ((!strcmp($key,"userid")) || (!strcmp($key,"aboutme")) || (!strcmp($key,"lastupdate")) || (!strcmp($key,"lastviewed")) || (!strcmp($key,"dateadded")) || (!strcmp($key,"isreceiver"))) {
	        // display profile picture if available
		if (!strcmp($key,"userid")) {
			// don't show own profile
			if (($val != $fbid) && ($val!=0)) {
			echo "<fb:profile-pic uid=".$val." linked='true' /><br /> <fb:name uid=".$val." linked='true' shownetwork='true' /><a href='http://apps.facebook.com/missionsconnector/profile.php?userid=".$val."'><br/>  See Profile</a><br/><br/>";
			}

		    //$profile_pic =  "http://graph.facebook.com/".$val."/picture";
		    //echo $j.")<img src=\"" . $profile_pic . "\" />";
		    //echo "<br />";
		}
	}
	else {
		if (isset($val)) {
			if (empty($val)) {

			}
			else {
			echo "<b>".strtoupper($key).": <b/>".$val."<br />";
			//echo $val;
			//echo "<br />";
			}
		}
	}
}
$j++;
echo "<br />";
echo "<br />";
}
}
}
if ($j==0) {
	echo 'You do not have any results to display <br /><br />';
	echo"<a href='http://apps.facebook.com/missionsconnector/searchbyzip.php'>Go back to search by zipcode</a>";
}

}

?>
