<?php
// Application: Christian Missions Connector
// File: 'searchresults.php' 
//  search results retrieved and displayed (sort by distance**)
// 
//require_once 'facebook.php';

include_once 'common.php';
ob_start();

$fb = cmc_startup($appapikey, $appsecret,0);
$fbid = $fb->require_login("publish_stream,read_stream");


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
function getZipInfo($zip) {
$sql  = "SELECT * FROM zipcodes WHERE zipcode='" . $zip . "'";
$query = mysql_query($sql);
if(mysql_num_rows($query) < 1)
        return FALSE;

	$zipInfo = mysql_fetch_object($query);
	return $zipInfo;
} //end getZipInfo


?>
<br/><br/>

<?php
//$profileid=$_Request['id'];
$profileid = $fbid;

function get_rest_of_string(&$sql3,&$sql1,&$sql2,$val,$saferequest) {

$skills = 0;
$sql2 = '';
$sql1 = ' ';
$sql3='';
$usersinc=0;

if (isset($saferequest['relg'])) {
  if (strcmp($saferequest['relg'],"Any")) {
  if ($val ==1) {
        $usersinc = 1;
  	$sql1 = ',users';
  	$sql3 = $sql3.' and users.religion="'.$saferequest['relg'].'"';
  }
  else
  	$sql3 = $sql3.' and users.religion="'.$saferequest['relg'].'"';
  }
}
if (isset($saferequest['medskills'])) {
  if (strcmp($saferequest['medskills'],"Any")) {
  $sql3 = $sql3.' and skills.skilldesc="'.$saferequest['medskills'].'"';
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
if (isset($saferequest['otherskills'])) {
  if (strcmp($saferequest['otherskills'],"Any")) {
  $sql3 = $sql3.' and skills.skilldesc="'.$saferequest['otherskills'].'"';
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
if (isset($saferequest['spiritserv'])) {
  if (strcmp($saferequest['spiritserv'],"Any")) {
  $sql3 = $sql3.' and skills.skilldesc="'.$saferequest['spiritserv'].'"';
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
if (isset($saferequest['country'])) {
  if (strcmp($saferequest['country'],"Any")) {
  $sql3 = $sql3.' and countries.longname="'.$saferequest['country'].'"';
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
if (isset($saferequest['region'])) {
  if (strcmp($saferequest['region'],"Any")) {
  $sql3 = $sql3.' and regions.name="'.$saferequest['region'].'"';
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
if (isset($saferequest['dur'])) {
  if (strcmp($saferequest['dur'],"Any")) {
  $sql3 = $sql3.' and durations.name="'.$saferequest['dur'].'"';
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

$con = mysql_connect('localhost',"arena", "***arena!password!getmoney!getpaid***");
//$con = mysql_connect(localhost,"poornima", "MYdata@1");
if(!$con)
{
  die('Could not connect: ' .  mysql_error());
}

mysql_select_db("missionsconnector", $con);

// Store the adv GET parameter
$adv = $_GET['adv'];


// get the zipcode of the current user
$sql = 'select zipcode from users where userid="'.$fbid.'"';
$result = mysql_query($sql);
$numrows = mysql_num_rows($result);
if ($numrows == 0)
  echo 'User zipcode is not specified <br />';
else {
$row = mysql_fetch_array($result,MYSQL_ASSOC);
$myzipcode = $row['zipcode'];
if (empty($myzipcode)) {
	//echo 'WARNING: User zip code unknown, results below are not sorted according to distance <br /><br />';
echo '<br /><b>Your Search Results (Results are not Sorted):<b/><br/><br />';
}
else
echo '<br /><b>Your Search Results (Sorted according to nearest from you):<b/><br/><br />';

}

$saferequest = cmc_safe_request_strip();

// This is for basic search
if ($adv==0) {

//if (isset($_REQUEST['keys'])) {
if (isset($saferequest['keys'])) {
session_start();
$_SESSION['storeid'] = '';
$_SESSION['storeida'] = '';
}


//$keywords = $_REQUEST['keys'];
$keywords = $saferequest['keys'];
//echo $keywords.'<br />';
if (!empty($keywords)) {

if (strstr($keywords,',')) {
 $keys = explode(",",$keywords);
}
else if (strstr($keywords,' ')) {
 $keys = explode(" ",$keywords);
}
else 
 $keys = explode(" ",$keywords);

/*
for ($i=0;$i<count($keys);$i++) {
	echo '<br />name:'.$keys[$i].'<br />';
}
*/

//$terms=explode(',', $_GET['keywords']);
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

session_start();
		
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

	$result = mysql_query($sql);
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
		$myzipinfo = getZipInfo($myzipcode);
		for ($i=0;$i<count($zipnowa);$i++) {
			if ($zipnowa[$i] == 0)
				$zipnowa[$i] = $myzipcode;

		        //echo 'Zipcode:'.$zipnow[$i].'<br />';
			$otherzipinfo = getZipInfo($zipnowa[$i]);
			$distsa[$i] = haversine($myzipinfo->latitude,$myzipinfo->longitude,$otherzipinfo->latitude,$otherzipinfo->longitude);
		}

	        // now sort the results in ascending order according to distance	
		array_multisort($distsa, SORT_ASC, SORT_NUMERIC,$storerowa,$zipnowa,$storeida,$storenamea);
		}
		
		// now print the results
		$sql4 = '';

		for ($j=0;$j<count($storerowa);$j++) {
		        if ($storeida[$j]!=0) {
      			echo "<fb:profile-pic uid=".$storeida[$j]." linked='true' /> <br /><fb:name uid=".$storeida[$j]." linked='true' shownetwork='true' /><a href='http://apps.facebook.com/missionsconnector/profile.php?userid=".$storeida[$j]."'><br/>  See CMC Profile</a><br/><br/>";
				if ($j==0)
				$sql4 = $sql4.strval($storeida[$j]);
				else
				$sql4 = $sql4.','.strval($storeida[$j]);
			}

		}
		$_SESSION['storeida'] = $storeida;
		mysql_free_result($result);			
		
	}
        }


	if (empty($sql4) && (!strcmp($filter,'()'))) {
		echo 'No Results to display matching your keywords <br />';
	    	echo '<a href="searchform.php">Search Again</a><br />';
		echo '<a href="welcome.php">Go back to Application home</a><br />';
	}
	else {

	if (!empty($sql4))
	$sql='select * from users where userid!="'.$fbid.'" and userid NOT IN('.$sql4.') and '.$filter;
	else 
	$sql='select * from users where userid!="'.$fbid.'" and '.$filter;

/*
// first get the complete data from the users table - ignore the current user
$sql = 'select * from users where userid !="'.$fbid.'"';
*/

if ($result = mysql_query($sql)) {
	$numrows = mysql_num_rows($result);
	if (($numrows ==0) && (empty($sql4))) {
		echo 'No Results to display matching your keywords <br />';
	        echo '<a href="searchform.php">Search Again</a><br />';
		echo '<a href="welcome.php">Go back to Application home</a><br />';
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
		$myzipinfo = getZipInfo($myzipcode);
		for ($i=0;$i<count($zipnow);$i++) {
			if ($zipnow[$i] == 0)
				$zipnow[$i] = $myzipcode;

		        //echo 'Zipcode:'.$zipnow[$i].'<br />';
			$otherzipinfo = getZipInfo($zipnow[$i]);
			$dists[$i] = haversine($myzipinfo->latitude,$myzipinfo->longitude,$otherzipinfo->latitude,$otherzipinfo->longitude);
		}

	        // now sort the results in ascending order according to distance	
		array_multisort($dists, SORT_ASC, SORT_NUMERIC,$storerow,$zipnow,$storeid,$storename);
		}
		
		// now print the results
		for ($j=0;$j<count($storerow);$j++) {
		        if ($storeid[$j]!=0) {
      			echo "<fb:profile-pic uid=".$storeid[$j]." linked='true' /> <br /><fb:name uid=".$storeid[$j]." linked='true' shownetwork='true' /><a href='http://apps.facebook.com/missionsconnector/profile.php?userid=".$storeid[$j]."'><br/>  See CMC Profile</a><br/><br/>";
			}

		}
		$_SESSION['storeid'] = $storeid;
		mysql_free_result($result);
	}
}
else {
	echo 'MYSQL Error <br />';
	echo '<a href="welcome.php">Go back to Application home</a><br />';
}
}
}
else {

	session_start();
	if ((empty($_SESSION['storeid'])) && (empty($_SESSION['storeida']))) {
	echo '<b>You Entered an empty string - Nothing to search <b/><br />';
	echo '<a href="searchform.php">Search Again</a><br />';
	echo '<a href="welcome.php">Go back to Application home</a><br />';
	}
	else {
		if (!empty($_SESSION['storeida'])) {
		foreach ($_SESSION['storeida'] as $value) {
			if ($value != 0) {
			echo "<fb:profile-pic uid=".$value." linked='true' /> <br /><fb:name uid=".$value." linked='true' shownetwork='true' /><a href='http://apps.facebook.com/missionsconnector/profile.php?userid=".$value."'><br/>  See CMC Profile</a><br/><br/>";
			}
		}
		}
		if (!empty($_SESSION['storeid'])) {
		foreach ($_SESSION['storeid'] as $value) {
			if ($value != 0) {
			echo "<fb:profile-pic uid=".$value." linked='true' /> <br /><fb:name uid=".$value." linked='true' shownetwork='true' /><a href='http://apps.facebook.com/missionsconnector/profile.php?userid=".$value."'><br/>  See CMC Profile</a><br/><br/>";
			}
		}
		}
	}
	
}

}
// This is for advanced search
else {

//if (!isset($_REQUEST['type'])) {
if (!isset($saferequest['type'])) {

if (!empty($_SESSION['vstoreid'])) {
		$friends = $_SESSION['vstoreid'];
          foreach ($friends as $currentfriend){
	    if (($currentfriend > 0) && ($currentfriend!=$fbid)) {
			$vstoreid[] = $currentfriend;
            echo "<fb:profile-pic uid=".$currentfriend." linked='true' /> <br /><fb:name uid=".$currentfriend." linked='true' shownetwork='true' /><a href='http://apps.facebook.com/missionsconnector/profile.php?userid=".$currentfriend."'><br/>  See CMC Profile</a><br/><br/>";
	    }
		}

}
else if (!empty($_SESSION['nstoreid'])) {
		$friends = $_SESSION['nstoreid'];
          foreach ($friends as $currentfriend){
	    if (($currentfriend > 0) && ($currentfriend!=$fbid)) {
			$vstoreid[] = $currentfriend;
                  echo "<fb:profile-pic uid=".$currentfriend." linked='true' /> <br /><fb:name uid=".$currentfriend." linked='true' shownetwork='true' /><a href='http://apps.facebook.com/missionsconnector/profile.php?userid=".$currentfriend."'><br/>  See CMC Profile</a><br/><br/>";
	    }
		}
}
else if (!empty($_SESSION['tstoreid'])) {
$tstoreid = $_SESSION['tstoreid'];
$tstorename = $_SESSION['tstorename'];
for ($i=0;$i<count($tstoreid);$i++) {
echo "<a href='profileT.php?tripid=".$tstoreid[$i]."'>".$tstorename[$i]."</a><br/><br/>";
}
}
else {
 //echo '<b>The type parameter must be specified for the search to work <br /></b>';
 $str = '';
//if (!empty($_REQUEST['medskills']))
if (!empty($saferequest['medskills']))
        $str = $str.'&ms='.$saferequest['medskills'];
	//$_SESSION['medskills'] = $_REQUEST['medskiils'];
//if (!empty($_REQUEST['otherskills']))
if (!empty($saferequest['otherskills']))
        $str = $str.'&os='.$saferequest['otherskills'];
	//$_SESSION["otherskills"] = $_REQUEST['otherskiils'];
//if (!empty($_REQUEST['spiritserv']))
if (!empty($saferequest['spiritserv']))
        $str = $str.'&ss='.$saferequest['spiritserv'];
	//$_SESSION["spiritserv"] = $_REQUEST['spiritserv'];
//if (!empty($_REQUEST['relg']))
if (!empty($saferequest['relg']))
        $str = $str.'&rg='.$saferequest['relg'];
	//$_SESSION["relg"] = $_REQUEST['relg'];
//if (!empty($_REQUEST['partner'])) {
if (!empty($saferequest['partner'])) {
        //$str = $str.'&p='.$_REQUEST['partner'];
	if (!strcmp($saferequest['partner'],"false"))
		$str = $str.'&p=2';
	else
		$str = $str.'&p=1';
}
	//$_SESSION["partner"] = $_REQUEST['partner'];
//if (!empty($_REQUEST['zip']))
if (!empty($saferequest['zip']))
        $str = $str.'&zip='.$saferequest['zip'];
	//$_SESSION["zip"] = $_REQUEST['zip'];
//if (!empty($_REQUEST['region']))
if (!empty($saferequest['region']))
        $str = $str.'&region='.$saferequest['region'];
	//$_SESSION["region"] = $saferequest['region'];
//if (!empty($_REQUEST['country']))
if (!empty($saferequest['country']))
        $str = $str.'&ct='.$saferequest['country'];
	//$_SESSION["country"] = $_REQUEST['country'];
//if (!empty($_REQUEST['name']))
if (!empty($saferequest['name']))
        $str = $str.'&nm='.$saferequest['name'];
	//$_SESSION["name"] = $_REQUEST['name'];
//if (!empty($_REQUEST['dur']))
if (!empty($saferequest['dur']))
        $str = $str.'&dur='.$saferequest['dur'];
	//$_SESSION["dur"] = $_REQUEST['dur'];
//if (!empty($_REQUEST['DepartYear']))
if (!empty($saferequest['DepartYear']))
        $str = $str.'&dy='.$saferequest['DepartYear'];
	//$_SESSION["DepartYear"] = $_REQUEST['DepartYear'];
//if (!empty($_REQUEST['DepartMonth']))
if (!empty($saferequest['DepartMonth']))
        $str = $str.'&dm='.$saferequest['DepartMonth'];
	//$_SESSION["DepartMonth"] = $_REQUEST['DepartMonth'];
//if (!empty($_REQUEST['DepartDay']))
if (!empty($saferequest['DepartDay']))
        $str = $str.'&dd='.$saferequest['DepartDay'];
	//$_SESSION["DepartDay"] = $_REQUEST['DepartDay'];
//if (!empty($_REQUEST['ReturnYear']))
if (!empty($saferequest['ReturnYear']))
        $str = $str.'&ry='.$saferequest['ReturnYear'];
	//$_SESSION["ReturnYear"] = $_REQUEST['ReturnYear'];
//if (!empty($_REQUEST['ReturnMonth']))
if (!empty($saferequest['ReturnMonth']))
        $str = $str.'&rm='.$saferequest['ReturnMonth'];
	//$_SESSION["ReturnMonth"] = $_REQUEST['ReturnMonth'];
//if (!empty($_REQUEST['ReturnDay']))
if (!empty($saferequest['ReturnDay']))
        $str = $str.'&rd='.$saferequest['ReturnDay'];
	//$_SESSION["ReturnDay"] = $_REQUEST['ReturnDay'];

 echo "<fb:redirect url='http://apps.facebook.com/missionsconnector/advancedsearch.php?fill=1".$str."' />";
 }
}
else {
//if($_REQUEST['type']=="Active Mission Organizers"){
if($saferequest['type']==1){
  $friends=array();

  $sql='select users.userid,users.zipcode from users';

  get_rest_of_string($sql3,$sql1,$sql2,0,$saferequest);
  $sql = $sql.$sql1.' where isreceiver="1"'.$sql3.$sql2;

if($result = mysql_query($sql)){
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
      echo "SQL Error ".mysql_error()." ";
}

    if($num_rows==0){
      echo "We're sorry, there were no matches for your search criteria. This version of Christian Missions Connector does not support partial matches, so please try again with fewer fields selected or use the 'Any' option for fileds that you do not have a strong preference for.<br /><br />";
      echo"<a href='http://apps.facebook.com/missionsconnector/searchform.php'>Go back to search options</a>";
    }
	else {
	
	// the friends list needs to be sorted according to distance -- new version
		// We now have the user zip code and the list of search zip codes
		// Find the distances and order them in ascending order
		if (isset($myzipcode)) {
		$myzipinfo = getZipInfo($myzipcode);
		for ($i=0;$i<count($zipnow);$i++) {
		    // if zipcode is not known, make the distance very big
			if ($zipnow[$i] == 0)
				$dists[$i] = 1000000000;
			else {
				//$zipnow[$i] = $myzipcode;

		        //echo 'Zipcode:'.$zipnow[$i].'<br />';
			$otherzipinfo = getZipInfo($zipnow[$i]);
			$dists[$i] = haversine($myzipinfo->latitude,$myzipinfo->longitude,$otherzipinfo->latitude,$otherzipinfo->longitude);
			}
		}	

	        // now sort the results in ascending order according to distance	
		if (!empty($dists))
			array_multisort($dists, SORT_ASC, SORT_NUMERIC,$friends);
		}	
	
	session_start();
	$nstoreid = array();
    foreach ($friends as $currentfriend){
      if (($currentfriend > 0) && ($currentfriend != $fbid)) {
	  $nstoreid[] = $currentfriend;
      echo "<fb:profile-pic uid=".$currentfriend." linked='true' /> <br /><fb:name uid=".$currentfriend." linked='true' shownetwork='true' /><a href='http://apps.facebook.com/missionsconnector/profile.php?userid=".$currentfriend."'><br/>  See CMC Profile</a><br/><br/>";
      //echo "<a href='http://apps.facebook.com/missionsconnector/profile.php?userid=".$currentfriend.">See Profile</a><br/><br/>";
//echo "successful active misssions call"; 
     }

}

	$_SESSION['nstoreid'] = $nstoreid;
	$_SESSION['vstoreid'] = '';
	$_SESSION['tstoreid'] = '';
	
}

}

      //if($_REQUEST['type']=="Volunteers"){
      if($saferequest['type']==2){
        $friends=array();
  	
  	$sql='select users.userid,users.zipcode from users';
	get_rest_of_string($sql3,$sql1,$sql2,0,$saferequest);
  	$sql = $sql.$sql1.' where isreceiver="0"'.$sql3.$sql2;
       if($result = mysql_query($sql)){
         $num_rows = mysql_num_rows($result);
	 $j=0;
          while($row= mysql_fetch_array($result,MYSQL_ASSOC)){
            $id = $row['userid'];
            $friends[$j] = $id;  
	     if (empty($row['zipcode']))
		$zipnow[$j] = 0;
		else
		$zipnow[$j] = $row['zipcode'];
	    $j++;
          }
		  }else {
            echo "SQL Error ".mysql_error()." ";
          }

    if($num_rows==0){
      echo "We're sorry, there were no matches for your search criteria. This version of Christian Missions Connector does not support partial matches, so please try again with fewer fields selected or use the 'Any' option for fields that you do not have a strong preference for.<br /><br />";
      echo"<a href='http://apps.facebook.com/missionsconnector/searchform.php'>Go back to search options</a>";
    }
	else {		  
		  
	// the friends list needs to be sorted according to distance -- new version
		// We now have the user zip code and the list of search zip codes
		// Find the distances and order them in ascending order
		if (isset($myzipcode)) {
		$myzipinfo = getZipInfo($myzipcode);
		for ($i=0;$i<count($zipnow);$i++) {
		    // if zipcode is not known, make the distance very big
			if ($zipnow[$i] == 0)
				$dists[$i] = 1000000000;
			else {
				//$zipnow[$i] = $myzipcode;

		        //echo 'Zipcode:'.$zipnow[$i].'<br />';
			$otherzipinfo = getZipInfo($zipnow[$i]);
			$dists[$i] = haversine($myzipinfo->latitude,$myzipinfo->longitude,$otherzipinfo->latitude,$otherzipinfo->longitude);
			}
		}
	        // now sort the results in ascending order according to distance	
		if (!empty($dists))
			array_multisort($dists, SORT_ASC, SORT_NUMERIC,$friends);
		}	
		
			session_start();
			$vstoreid = array();
          foreach ($friends as $currentfriend){
	    if (($currentfriend > 0) && ($currentfriend!=$fbid)) {
			$vstoreid[] = $currentfriend;
            echo "<fb:profile-pic uid=".$currentfriend." linked='true' /> <br /><fb:name uid=".$currentfriend." linked='true' shownetwork='true' /><a href='http://apps.facebook.com/missionsconnector/profile.php?userid=".$currentfriend."'><br/>  See CMC Profile</a><br/><br/>";
	    }
          }
		$_SESSION['vstoreid'] = $vstoreid;
		$_SESSION['tstoreid'] = '';
		$_SESSION['nstoreid'] = '';
    }

	  }


            //if($_REQUEST['type']=="Upcoming Mission Trips"){
            if($saferequest['type']==3){

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
	get_rest_of_string($sql3,$sql1,$sql2,1,$saferequest);
  	//$sql = $sql.$sql1.' where trips.id=tripmembers.tripid and tripmembers.userid=users.userid and tripmembers.isadmin="1"'.$sql3.$sql2;
	//if (empty($sql2) && empty($sql3))
  	//	$sql = $sql.$sql1.$sql4.$sql3.$sql2;
	//else
  		$sql = $sql.$sql1.$sql4.$sql3.$sql2;
	 
	//  echo $sql.'<br />';

	  if($result = mysql_query($sql)){
		$numrows = mysql_num_rows($result);
		if ($numrows == 0) {
			echo 'No search results to display <br />';
			echo"<a href='http://apps.facebook.com/missionsconnector/searchform.php'>Go back to search options</a>";
		}
		else {
				session_start();
				$tstoreid = array();
				$tstorename = array();
                while($row= mysql_fetch_array($result,MYSQL_NUM)){
                  $id = $row[1];
                  $name=$row[0];
                  //$triparray[$id]=$name;
					$tstoreid[] = $id;
					$tstorename[] = $name;
                  //foreach ($trips as $currenttrip){
                    echo "<a href='profileT.php?tripid=".$id."'>".$name."</a><br/><br/>";
                    //}
		    }
			$_SESSION['tstoreid'] = $tstoreid;
			$_SESSION['tstorename'] = $tstorename;
			$_SESSION['vstoreid'] = '';
			$_SESSION['nstoreid'] = '';			
		 }
		 }
		 else {
                      echo "SQL Error ".mysql_error()." ";
                 }
		    
	  

}
}
}	     
?>
