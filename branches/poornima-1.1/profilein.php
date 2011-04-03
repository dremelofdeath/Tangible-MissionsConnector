<?php
// Application: Christian Missions Connector
// File: 'profilein.php' 
//  add user profile to db
// 
//require_once 'facebook.php';

include_once 'common.php';
ob_start();

$fb = cmc_startup($appapikey, $appsecret,0);
$fbid = get_user_id($fb);
//$fbid = $fb->require_login("publish_stream,read_stream");

arena_connect();

/*
if($_GET['type'] == "volunteer") $is_volunteer = true;
if($_GET['type'] == "mission") $is_mission = true;
if($_GET['type'] == "trip") $is_trip = true;
*/


session_start();

if ($_SESSION["mytype"]==1) {
 $isreceiver = 0;
 //echo 'Mission:'.$isreceiver.'<br />';
}
else if ($_SESSION["mytype"] == 2) {
 $is_trip = 1;
 //echo 'Trip:'.$is_trip.'<br />';
}
else {
 $isreceiver = 1;
 //echo 'Volunteer:'.$isreceiver.'<br />';
}

if (isset($_GET['update']))
	$update = $_GET['update'];

//$update = $_SESSION["update"];

// BUG #81010922: Fixing the security vulnerability where a user can inject HTML 
// code into their profile
$saferequest = cmc_safe_request_strip();


// Zip code is a required field - return to makeprofile and prompt for the zipcode if the country is USA
if (empty($saferequest['zip']) && (!strcmp($saferequest['country'],'United States'))) {
if ($isreceiver==0)
	echo "<fb:redirect url='makeprofile.php?type=mission&error=1' />";
else if ($isreceiver==1)
	echo "<fb:redirect url='makeprofile.php?type=volunteer&error=1' />";
}

$info = $fb->api_client->users_getInfo($fbid, 'name', 'email', 'about_me');

//echo count($info);

for($i=0; $i < count($info); $i++) {
  $record = $info[$i];
  $name = $record['name']; 
  $email = $record['email'].", ".$saferequest['email']; 
  //$phone = $info[0]['phone']; 
  $aboutme = $record['about_me'].", ".$saferequest['aboutme']; 
}

//$sql = "SELECT name, email, about_me FROM user WHERE uid ='".$fbid."'";
//echo $facebook->api_client->fql_query($sql);
if (!$is_trip) {

if (!empty($saferequest["toggle"])) {

if ($saferequest["toggle"] == 1) {
   $sql = 'select * from users where userid="'.$fbid.'"';
   $result = mysql_query($sql);
   $row = mysql_fetch_array($result);
   $misreceiver = $row['isreceiver'];
   if ($misreceiver == 1) {
   	$newrecr = 0;
	$sql2 = 'UPDATE users SET isreceiver="'.$newrecr.'" where userid="'.$fbid.'"';
	mysql_query($sql2);
	$isreceiver = $newrecr;
   }
   else {
   	$newrecr = 1;
	$sql2 = 'UPDATE users SET isreceiver="'.$newrecr.'" where userid="'.$fbid.'"';
	mysql_query($sql2);
	$isreceiver = $newrecr;
   }

}
}

// function to validate user input
function validateString($num)
{
if (strlen($num==0)) 
return -1;
else {
//if ((preg_match("<iframe",trim($num))) || (stristr($num,"iframe")))
if (preg_match("#^[a-zA-Z0-9 ]+$#i",trim($num)))
return 1;
else
return 0;
}
}

$sql = "SELECT userid FROM users WHERE userid='".$fbid."'";
$result = mysql_query($sql) or die(mysql_error());

$num_userids = mysql_num_rows($result);

if($num_userids > 0){
  
  $sql = 'UPDATE users SET name="'.$name.'"';
  
  if (!empty($saferequest['name']))
   $sql = $sql.', organization="'.$saferequest['name'].'"';
 
   $sql = $sql.', isreceiver="'.$isreceiver.'"';
   if ($update) {
   
   if (empty($saferequest['zip']))
	$sql = $sql.', zipcode=NULL';
   else
    $sql = $sql.', zipcode="'.$saferequest["zip"].'"';
	
   if (empty($saferequest['phone']))
	$sql = $sql.', phone=NULL';
   else
    $sql = $sql.', phone="'.$saferequest["phone"].'"';
	
   if (empty($saferequest['email']))
	$sql = $sql.', email=NULL';
   else
    $sql = $sql.', email="'.$saferequest["email"].'"';	
	
   if (empty($saferequest['misexp']))
	$sql = $sql.', missionsexperience=NULL';
   else
    $sql = $sql.', missionsexperience="'.$saferequest["misexp"].'"';
	
   if (empty($saferequest['relg']))
	$sql = $sql.', religion=NULL';
   else
    $sql = $sql.', religion="'.$saferequest["relg"].'"';
	
   if (empty($saferequest['about']))
	$sql = $sql.', aboutme=NULL';
   else
    $sql = $sql.', aboutme="'.$saferequest["about"].'"';
	
   if (empty($saferequest['state']))
	$sql = $sql.', state=NULL';
   else
    $sql = $sql.', state="'.$saferequest["state"].'"';


   $mycity = strip_tags($saferequest['city']);
   if (empty($mycity))
	$sql = $sql.', city=NULL';
   else {
      if (validateString($mycity)==0) {
      //if (validateString(mysql_real_escape_string($saferequest['city']))==0) {
	if ($isreceiver==0)
		echo "<fb:redirect url='makeprofile.php?type=mission&edit=1&error=8' />";
	else if ($isreceiver==1)
		echo "<fb:redirect url='makeprofile.php?type=volunteer&edit=1&error=8' />";
       }
    $sql = $sql.', city="'.$saferequest["city"].'"';
   }

   if (empty($saferequest['url']))
	$sql = $sql.', website=NULL';
   else
    $sql = $sql.', website="'.$saferequest["url"].'"';
	
   }
   else {
   if (!empty($saferequest['zip']))
   $sql = $sql.', zipcode="'.$saferequest["zip"].'"';
   if (!empty($saferequest['phone']))
   $sql = $sql.', phone = "'.$saferequest["phone"].'"';
   if (!empty($saferequest['email']))
   $sql = $sql.', email = "'.$saferequest["email"].'"';
   if (!empty($saferequest['misexp']))
   $sql = $sql.', missionsexperience = "'.$saferequest["misexp"].'"';
   if (!empty($saferequest['relg']))
    $sql = $sql.', religion = "'.$saferequest["relg"].'"';
    if (!empty($saferequest['about']))
    $sql = $sql.', aboutme = "'.$saferequest["about"].'"';
    if (!empty($saferequest['state']))
    $sql = $sql.',state ="'.$saferequest['state'].'"';
    //$mycity = mysql_real_escape_string($saferequest['city']);
    $mycity = strip_tags($saferequest['city']);

   if (!empty($mycity)) {
   //if (!empty(mysql_real_escape_string($saferequest['city']))) {
      //if (validateString(mysql_real_escape_string($saferequest['city']))==0) {
      if (validateString($mycity)==0) {
	if ($isreceiver==0)
		echo "<fb:redirect url='makeprofile.php?type=mission&error=8' />";
	else if ($isreceiver==1)
		echo "<fb:redirect url='makeprofile.php?type=volunteer&error=8' />";
       }
    else
    $sql = $sql.', city ="'.$saferequest['city'].'"';
    }
   if (!empty($saferequest['url']))
    $sql = $sql.', website = "'.$saferequest['url'].'"';

	}
	
   $sql = $sql.', partnersite = "0" WHERE userid ='.$fbid;
} else if($num_userids == 0) {

  $sql = 'INSERT INTO users '.
    '(userid, name, organization, isreceiver, state, city, zipcode, phone, email, missionsexperience,'.
    ' religion, aboutme, website, partnersite) '.
    'VALUES ("'.$fbid.'","'.$name.'","'.$saferequest['name'].'","'.$isreceiver.'","'.$saferequest['state'].'","'.strip_tags($saferequest['city']).'","'.strip_tags($saferequest["zip"]).'","'.
    strip_tags($saferequest["phone"]).'","'.strip_tags($saferequest["email"]).'","'.strip_tags($saferequest["misexp"]).'","'.
    $saferequest["relg"].'","'.$saferequest['about'].'","'.$saferequest['url'].'","0")';
} else {
  die("Run for the hills! " . mysql_error());
}

//  $sql = $sql."(userid, name, isreceiver, zipcode, phone, email, missionsexperience, religion, aboutme, website, partnersite) VALUES ('".$fbid."','".$tid."','0','".$saferequest['zip']."','".$saferequest['phone']."','".$saferequest['email']."','".$saferequest['misexp']."','".$saferequest['relg']."','".$saferequest['aboutme']."','".$saferequest['website']."','0')";
//  if($idcount<>0){
//    $sql=$sql."WHERE userid='".$fbid."'";}
   //mysql_fetch_array($result) or die(mysql_error());

mysql_query($sql) or die(mysql_error());
}

if ($is_trip) {

//$sql = 'SELECT id from trips';
//$result = mysql_query($sql);
//$num_rows = mysql_num_rows($result);

// check to see if any trip exists within the same creator, description or destination
// if so, set to update

if (empty($update)) {
$changed=0;
$sql = 'select * from trips where creatorid="'.$fbid.'"';
if (!empty($saferequest['name'])) {
  $sql = $sql.' and tripname="'.$saferequest['name'].'"';
  //$changed = 1;
}
//if (!empty($saferequest['about'])){
//  $sql = $sql.' and tripdesc="'.$saferequest['about'].'"';
  //if ($changed==0)
  //	$changed = 1;
//}
//if (!empty($saferequest['destination'])) {
//  $sql = $sql.' and destination="'.$saferequest['destination'].'"';
  //if ($changed==0)
  //	$changed = 1;
//}

//echo 'TESTING SQL: '.$sql.'<br />';

//if ($changed) {
//echo 'A trip with the same description as entered has been found, so updating that trip <br />';
$result = mysql_query($sql);
$numrows = mysql_num_rows($result);
if ($numrows>0) {
    $changed = 1;
	$row = mysql_fetch_array($result,MYSQL_ASSOC);
	$update = $row['id'];
}
}

if ($update) {
$sql = 'update trips set ';
$sql2 = ' where id="'.$update.'"';
$sql1 = '';
}
else {
$sql = 'INSERT INTO trips (creatorid';
$sql2 = 'VALUES ("'.$fbid.'"';
$sql1 = '';
}

$namemod = 0;

$tripname = strip_tags($saferequest['name']);
//if (!empty($saferequest['name'])){
if (!empty($tripname)){
	//$tripname = $saferequest['name'];
	if ($update) {
	$namemod = 1;
	$sql1 = $sql1.'tripname="'.$tripname.'"';
	}
	else {
	$sql1 = $sql1.',tripname';
	$sql2 = $sql2.',"'.$tripname.'"';
	}
}
else {
	if ($update)
	echo '<fb:redirect url="makeprofile.php?type=trip&edit=1&update='.$update.'&error=5" />';
	else
	echo '<fb:redirect url="makeprofile.php?type=trip&error=5" />';
}

$tripdesc = strip_tags($saferequest['about']);
//if (!empty($saferequest['about'])) {
if (!empty($tripdesc)) {
	//$tripdesc = $saferequest['about'];
	if ($update) {
	if ($namemod)
	$sql1 = $sql1.',tripdesc="'.$tripdesc.'"';
	else {
	$sql1 = $sql1.'tripdesc="'.$tripdesc.'"';
	$namemod=1;
	}
	}
	else {
	$sql1 = $sql1.',tripdesc';
	$sql2 = $sql2.',"'.$tripdesc.'"';
	}
}
else {
	if ($update) {
	if ($namemod)
	$sql1 = $sql1.',tripdesc=NULL';
	else {
	$sql1 = $sql1.'tripdesc=NULL';
	$namemod=1;
	}	
	}
}

function validatephone($phone) {
if( !preg_match("/^([1]-)?[0-9]{3}-[0-9]{3}-[0-9]{4}$/i", $phone) ) {
	    return false;
}
else
	return true;
}

$tripphone = strip_tags($saferequest['phone']);
//if (!empty($saferequest['phone'])) {
if (!empty($tripphone)) {
	//$tripphone = $saferequest['phone'];
	if ($update) {
	if ($namemod)
	$sql1 = $sql1.',phone="'.$tripphone.'"';
	else {
	$sql1 = $sql1.'phone="'.$tripphone.'"';
	$namemod = 1;
	}
	}
	else {
	$sql1 = $sql1.',phone';
	$sql2 = $sql2.',"'.$tripphone.'"';
	}
}
else {
	if ($update) {
	if ($namemod)
	$sql1 = $sql1.',phone=NULL';
	else {
	$sql1 = $sql1.'phone=NULL';
	$namemod=1;
	}	
	}
}


function validateemailid($email) {
if (strlen($email)==0) {
return -1;
}
else {
if (preg_match("/^[a-zA-Z0-9_.-]+@$/",trim($email)))
return 1;
else
return 0;
}
}

function check_email_address($email) {
// First, we check that there's one @ symbol, 
// and that the lengths are right.
if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) {
   // Email invalid because wrong number of characters 
   // in one section or wrong number of @ symbols.
return false;
}
// Split it into sections to make life easier
$email_array = explode("@", $email);
$local_array = explode(".", $email_array[0]);
for ($i = 0; $i < sizeof($local_array); $i++) {
if (!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&.'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$",$local_array[$i])) {
	return false;
}
}
// Check if domain is IP. If not, 
// it should be valid domain name
if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) {
	$domain_array = explode(".", $email_array[1]);
	if (sizeof($domain_array) < 2) {
		return false; // Not enough parts to domain
	}
	for ($i = 0; $i < sizeof($domain_array); $i++) {
		if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|.([A-Za-z0-9]+))$",$domain_array[$i])) {
			return false;
		}
	}
}
return true;
}


$tripemail = strip_tags($saferequest['email']);
//if (!empty($saferequest['email'])) {
if (!empty($tripemail)) {
	//$tripemail = $saferequest['email'];
	if (!check_email_address($tripemail)) {
		if ($update)
		echo '<fb:redirect url="makeprofile.php?type=trip&edit=1&update='.$update.'&error=7" />';
		else
		echo '<fb:redirect url="makeprofile.php?type=trip&error=7" />';
	}
	else {
	if ($update) {
	if ($namemod)
	$sql1 = $sql1.',email="'.$tripemail.'"';
	else {
	$sql1 = $sql1.'email="'.$tripemail.'"';
	$namemod=1;
	}
	}
	else {
	$sql1 = $sql1.',email';
	$sql2 = $sql2.',"'.$tripemail.'"';
	}
	}
}
else {
	if ($update) {
	if ($namemod)
	$sql1 = $sql1.',email=NULL';
	else {
	$sql1 = $sql1.'email=NULL';
	$namemod=1;
	}	
	}
}

// function to validate a url
function isValidURL($url)
{
return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
}

$tripurl = strip_tags($saferequest['url']);
//if (!empty($saferequest['url'])) {
if (!empty($tripurl)) {
	//$tripurl = $saferequest['url'];
	if (!isValidURL($tripurl)) {
		if ($update)
		echo '<fb:redirect url="makeprofile.php?type=trip&edit=1&update='.$update.'&error=6" />';
		else
		echo "<fb:redirect url='makeprofile.php?type=trip&error=6' />";
	}
	else {
	if ($update) {
	if ($namemod)
	$sql1 = $sql1.',website="'.$tripurl.'"';
	else {
	$sql1 = $sql1.'website="'.$tripurl.'"';
	$namemod=1;
	}

	}
	else {
	$sql1 = $sql1.',website';
	$sql2 = $sql2.',"'.$tripurl.'"';
	}
	}
}
else {
	if ($update) {
	if ($namemod)
	$sql1 = $sql1.',website=NULL';
	else {
	$sql1 = $sql1.'website=NULL';
	$namemod=1;
	}	
	}
}
if (!empty($saferequest['dur'])) {
	$tripdurid = $saferequest['dur'];
	if ($update) {
	if ($namemod)
	$sql1 = $sql1.',durationid="'.$tripdurid[0].'"';
	else {
	$sql1 = $sql1.'durationid="'.$tripdurid[0].'"';
	$namemod=1;
	}
	}
	else {
	$sql1 = $sql1.',durationid';
	$sql2 = $sql2.',"'.$tripdurid[0].'"';
	}
}
else {
	if ($update) {
	if ($namemod)
	$sql1 = $sql1.',durationid=NULL';
	else {
	$sql1 = $sql1.'durationid=NULL';
	$namemod=1;
	}	
	}
}


if (isset($saferequest['stage'])) {
	$tripstage = $saferequest['stage'];
	if ($update) {
	if ($namemod)
	$sql1 = $sql1.',isinexecutionstage="'.$tripstage.'"';
	else {
	$sql1 = $sql1.'isinexecutionstage="'.$tripstage.'"';
	$namemod=1;
	}
	}
	else {
	$sql1 = $sql1.',isinexecutionstage';
	$sql2 = $sql2.',"'.$tripstage.'"';
	}
}
else {
	if ($update) {
	if ($namemod)
	$sql1 = $sql1.',isinexecutionstage=NULL';
	else {
	$sql1 = $sql1.'isinexecutionstage=NULL';
	$namemod=1;
	}	
	}
}
/*
if (!empty($saferequest['fintype'])) {
  $fintype = $saferequest['fintype'];
  if ($fintype == 1) { 
  	$paidstr = ' This is a self-paid trip.';
	if ($update) {
	if ($namemod)
	$sql1 = $sql1.',notes="This is a self-paid trip"';
	else {
	$sql1 = $sql1.'notes="This is a self-paid trip"';
	$namemod=1;
	}
	}
	else {
	$sql1 = $sql1.'notes';
	$sql2 = $sql2.',"This is a self-paid trip"';
	}
  }
  else {
	if (!empty($saferequest['financing'])) {
		$financingby = $saferequest['financing'];
	if ($update) {
	if ($namemod)
	$sql1 = $sql1.',notes="'.$financingby.'"';
	else {
	$sql1 = $sql1.'notes="'.$financingby.'"';
	$namemod=1;
	}
	}
	else {
	$sql1 = $sql1.',notes';
	$sql2 = $sql2.',"'.$financingby.'"';
	}

		$paidstr = ' This trip is being paid. Notes: '.$financingby;
	}
	else {
	if ($update) {
	if ($namemod)
	$sql1 = $sql1.',notes="This is a paid trip, but notes given"';
	else {
	$sql1 = $sql1.'notes="This is a paid trip, but no notes given"';
	$namemod=1;
	}
	}
	else {
	$sql1 = $sql1.'notes';
	$sql2 = $sql2.',"This is a paid trip, but notes given"';
	}
		$paidstr = ' This is a paid trip. No notes given';

	}
  }
}

$_SESSION['paidstr'] = $paidstr;
*/

//echo 'DEPARTING: '.$saferequest['DepartMonth'].' '.$saferequest['DepartDay'].'<br />';

function getdatestring($year,$month,$date) {

if ($month<10)
	$smonth = strval($month);
else
	$smonth = strval($month);

if ($date<10)
	$sdate = strval($date);
else
	$sdate = strval($date);


  $res = $year.'-'.$smonth.'-'.$sdate.' '.'00:00:00';

  return $res;
}

function validate_date($val,$year,$month,$day,$update) {

if ($month%2==0) {
	// special case for february
	if ($month==2) {
		if ($year%4 ==0) {
			if (($year%100 == 0) && ($year%400 !=0)) {
				if ($day > 28) {
					//echo 'Day should be less than or equal to 28 <br />';
					if ($val==1) {
					if ($update)
					echo '<fb:redirect url="makeprofile.php?type=trip&edit=1&update='.$update.'&error=2" />';
					else
					echo "<fb:redirect url='makeprofile.php?type=trip&error=2' />";
					}
					else {
					if ($update)
					echo '<fb:redirect url="makeprofile.php?type=trip&edit=1&update='.$update.'&error=3" />';
					else
					echo "<fb:redirect url='makeprofile.php?type=trip&error=3' />";
					}
				}
			}
			else {
				if ($day > 29) {
					//echo 'Day should be less than 29 <br />';
					if ($val==1) {
					if ($update)
					echo '<fb:redirect url="makeprofile.php?type=trip&edit=1&update='.$update.'&error=2" />';
					else
					echo "<fb:redirect url='makeprofile.php?type=trip&error=2' />";
					}
					else {
					if ($update)
					echo '<fb:redirect url="makeprofile.php?type=trip&edit=1&update='.$update.'&error=3" />';
					else
					echo "<fb:redirect url='makeprofile.php?type=trip&error=3' />";
					}
				}
			}
		}
		else {
			if ($day > 28) {
				//echo 'Day must be less than or equal to 28 <br />';
				if ($val==1) {
					if ($update)
					echo '<fb:redirect url="makeprofile.php?type=trip&edit=1&update='.$update.'&error=2" />';
					else
				echo "<fb:redirect url='makeprofile.php?type=trip&error=2' />";
				}
				else {
					if ($update)
					echo '<fb:redirect url="makeprofile.php?type=trip&edit=1&update='.$update.'&error=3" />';
					else
				echo "<fb:redirect url='makeprofile.php?type=trip&error=3' />";
				}
			}
		}
	}
	else if ($month != 8) {
	 	if ($day > 30) {
		//echo 'Day must not be greater than 30 <br />';
		if ($val==1) {
					if ($update)
					echo '<fb:redirect url="makeprofile.php?type=trip&edit=1&update='.$update.'&error=2" />';
					else
		echo "<fb:redirect url='makeprofile.php?type=trip&error=2' />";
		}
		else {
					if ($update)
					echo '<fb:redirect url="makeprofile.php?type=trip&edit=1&update='.$update.'&error=3" />';
					else
		echo "<fb:redirect url='makeprofile.php?type=trip&error=3' />";
		}
		}
	}
}

}

function validate_return($year1,$month1,$day1,$year2,$month2,$day2,$update) {

if ($year2 < $year1) {
		if ($update)
		echo '<fb:redirect url="makeprofile.php?type=trip&edit=1&update='.$update.'&error=4" />';
		else
	echo "<fb:redirect url='makeprofile.php?type=trip&error=4' />";
}
else if ($year2 == $year1) {
	if ($month2 < $month1) {
		if ($update)
		echo '<fb:redirect url="makeprofile.php?type=trip&edit=1&update='.$update.'&error=4" />';
		else
		echo "<fb:redirect url='makeprofile.php?type=trip&error=4' />";
	}
	else if ($month2==$month1) {
		if ($day2 < $day1) {
		if ($update)
		echo '<fb:redirect url="makeprofile.php?type=trip&edit=1&update='.$update.'&error=4" />';
		else
			echo "<fb:redirect url='makeprofile.php?type=trip&error=4' />";
		}
	}
}

}

if ((!empty($saferequest['DepartYear'])) && (!empty($saferequest['DepartMonth'])) && (!empty($saferequest['DepartDay']))) {
        //$thisyear = date("Y");

	validate_date(1,$saferequest['DepartYear'],$saferequest['DepartMonth'],$saferequest['DepartDay'],$update);

	$tripdpt = getdatestring($saferequest['DepartYear'],$saferequest['DepartMonth'],$saferequest['DepartDay']);

	if ($update) {
	if ($namemod)
	$sql1 = $sql1.',departure="'.$tripdpt.'"';
	else {
	$sql1 = $sql1.'departure="'.$tripdpt.'"';
	$namemod=1;
	}
	}
	else {
	//echo 'DEPARTING: '.$tripdpt;
	$sql1 = $sql1.',departure';
	$sql2 = $sql2.',"'.$tripdpt.'"';
	}
}
if ((!empty($saferequest['ReturnYear'])) && (!empty($saferequest['ReturnMonth'])) && (!empty($saferequest['ReturnDay']))) {
	validate_date(2,$saferequest['ReturnYear'],$saferequest['ReturnMonth'],$saferequest['ReturnDay'],$update);

	validate_return($saferequest['DepartYear'],$saferequest['DepartMonth'],$saferequest['DepartDay'],$saferequest['ReturnYear'],$saferequest['ReturnMonth'],$saferequest['ReturnDay'],$update);

	$tripret = getdatestring($saferequest['ReturnYear'],$saferequest['ReturnMonth'],$saferequest['ReturnDay']);

	if ($update) {
	if ($namemod)
	$sql1 = $sql1.',returning="'.$tripret.'"';
	else {
	$sql1 = $sql1.'returning="'.$tripret.'"';
	$namemod=1;
	}
	}
	else {
	$sql1 = $sql1.',returning';
	$sql2 = $sql2.',"'.$tripret.'"';
	}
}

$tripdest = strip_tags($saferequest['destination']);
//if (!empty($saferequest['destination'])) {
if (!empty($tripdest)) {
	//$tripdest = $saferequest['destination'];
	if ($update) {
	if ($namemod) 
	$sql1 = $sql1.',destination="'.$tripdest.'"';
	else {
	$sql1 = $sql1.'destination="'.$tripdest.'"';
	$namemod=1;
	}
	}
	else {
	$sql1 = $sql1.',destination';
	$sql2 = $sql2.',"'.$tripdest.'"';
	}
}
else {
	if ($update) {
	if ($namemod)
	$sql1 = $sql1.',destination=NULL';
	else {
	$sql1 = $sql1.'destination=NULL';
	$namemod=1;
	}	
	}
}

if (!empty($saferequest['country'])) {
	$tripcountry = $saferequest['country'];
	$sql5 = 'select * from countries where id="'.$tripcountry[0].'"';
	$result5 = mysql_query($sql5);
	$row5 = mysql_fetch_array($result5);
	if ($update) {
	if ($namemod) 
	$sql1 = $sql1.',country="'.$row5['longname'].'"';
	else {
	$sql1 = $sql1.'country="'.$row5['longname'].'"';
	$namemod=1;
	}
	}
	else {
	$sql1 = $sql1.',country';
	$sql2 = $sql2.',"'.$row5['longname'].'"';
	}
}
else {
	if ($update) {
	if ($namemod)
	$sql1 = $sql1.',country=NULL';
	else {
	$sql1 = $sql1.'country=NULL';
	$namemod=1;
	}	
	}
}

$tripnump = strip_tags($saferequest['numpeople']);
//if (!empty($saferequest['numpeople'])) {
if (!empty($tripnump)) {
	//$tripnump = $saferequest['numpeople'];
	if ($update) {
	if ($namemod)
	$sql1 = $sql1.',numpeople="'.$tripnump.'"';
	else {
	$sql1 = $sql1.'numpeople="'.$tripnump.'"';
	$namemod=1;
	}
	}
	else {
	$sql1 = $sql1.',numpeople';
	$sql2 = $sql2.',"'.$tripnump.'"';
	}
}
else {
	if ($update) {
	if ($namemod)
	$sql1 = $sql1.',numpeople=NULL';
	else {
	$sql1 = $sql1.'numpeople=NULL';
	$namemod=1;
	}	
	}
}

$tripzip = strip_tags($saferequest['zip']);
//if (!empty($saferequest['zip'])) {
if (!empty($tripzip)) {
	//$tripzip = $saferequest['zip'];
	if ($update) {
	if ($namemod)
	$sql1 = $sql1.',zipcode="'.$tripzip.'"';
	else {
	$sql1 = $sql1.'zipcode="'.$tripzip.'"';
	$namemod=1;
	}
	}
	else {
	$sql1 = $sql1.',zipcode';
	$sql2 = $sql2.',"'.$tripzip.'"';
	}
}
else {
	if ($update) {
	if ($namemod)
	$sql1 = $sql1.',zipcode=NULL';
	else {
	$sql1 = $sql1.'zipcode=NULL';
	$namemod=1;
	}	
	}
}

if (!empty($saferequest['relg'])) {
	$triprelg = $saferequest['relg'];
	if ($update) {
	if ($namemod)
	$sql1 = $sql1.',religion="'.$triprelg.'"';
	else {
	$sql1 = $sql1.'religion="'.$triprelg.'"';
	$namemod=1;
	}
	}
	else {
	$sql1 = $sql1.',religion';
	$sql2 = $sql2.',"'.$triprelg.'"';
	}
}
else {
	if ($update) {
	if ($namemod)
	$sql1 = $sql1.',religion=NULL';
	else {
	$sql1 = $sql1.'religion=NULL';
	$namemod=1;
	}	
	}
}

if ($update) {
$sql = $sql.$sql1.$sql2;
}
else {
$todayy = date("Y");
$todaym = date("m");
$todayd = date("d");
$today = getdatestring($todayy,$todaym,$todayd);
$sql1 = $sql1.',dateadded';
$sql2 = $sql2.',"'.$today.'"';

$sql = $sql.$sql1.') '.$sql2.')';
}

echo 'Main SQL string: '.$sql.'<br />';


$result = mysql_query($sql);
if (!$result) {
	echo "SQL Error: ".mysql_error()." <br />";
}

if ($update) {
  $tripid = $update;
}
else {

$sql = 'select max(id) as tripid from trips where creatorid="'.$fbid.'"';
//if (!empty($saferequest['destination']))
//	$sql = $sql.' and destination="'.$saferequest['destination'].'"';
//if (!empty($saferequest['about']))
//	$sql = $sql.' and tripdesc="'.$saferequest['about'].'"';

//$sql = 'select id from trips where creatorid="'.$fbid.'" and destination="'.$saferequest['destination'].'" and tripdesc="'.$saferequest['about'].'"';
//echo $sql.'<br />';
$result = mysql_query($sql);
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $tripid = $row['tripid'] + 0;  
    break;
}

//echo 'TRIPID = '.$tripid.'<br />';

// now update the trip members table
$sql = 'INSERT into tripmembers (userid, tripid, isadmin, invited, accepted, datejoined) VALUES ("'.$fbid.'","'.$tripid.'","1","1","1","'.$today.'")';

//echo 'tripmembers SQL: '.$sql.'<br />';

$result = mysql_query($sql);
if (!$result) {
        echo "SQL Error: ".mysql_error()." <br />";
}	
}

}

  //else {echo "SQL Error ".mysql_error()." ";
  //   }

// clear out the old entries so that we start fresh
mysql_query("DELETE FROM skillsselected WHERE userid='".$fbid."'") or die(mysql_error());
mysql_query("DELETE FROM regionsselected WHERE userid='".$fbid."'") or die(mysql_error());
mysql_query("DELETE FROM countriesselected WHERE userid='".$fbid."'") or die(mysql_error());
mysql_query("DELETE FROM usstatesselected WHERE userid='".$fbid."'") or die(mysql_error());
mysql_query("DELETE FROM durationsselected WHERE userid='".$fbid."'") or die(mysql_error());

if (isset($saferequest['medfacil'])) {
$medfacil = $saferequest['medfacil'];
foreach($medfacil as $ms) {
  $sql = "INSERT INTO skillsselected VALUES ('".$fbid."','".$ms."')";
  mysql_query($sql);
  if(!$result){
    echo "<b><br/>==SQL Error==</b><br/>".mysql_error()."<br/><br/>Query was:<br/>".$sql;
  }
}
}

if (isset($saferequest['nonmedfacil'])) {
$nonmedfacil = $saferequest['nonmedfacil'];
foreach($nonmedfacil as $ms) {
  $sql = "INSERT INTO skillsselected VALUES ('".$fbid."','".$ms."')";
  mysql_query($sql);
  if(!$result){
    echo "<b><br/>==SQL Error==</b><br/>".mysql_error()."<br/><br/>Query was:<br/>".$sql;
  }
}
}

if (isset($saferequest['medskills'])) {
$medskills = $saferequest['medskills'];
foreach($medskills as $ms) {
  $sql = "INSERT INTO skillsselected VALUES ('".$fbid."','".$ms."')";
  mysql_query($sql);
  if(!$result){
    echo "<b><br/>==SQL Error==</b><br/>".mysql_error()."<br/><br/>Query was:<br/>".$sql;
  }
}
}

if (isset($saferequest['otherskills'])) {
$otherskills=$saferequest['otherskills'];
foreach($otherskills as $ms) {
  $sql = "INSERT INTO skillsselected VALUES ('".$fbid."','".$ms."')";
  $result = mysql_query($sql);
  if(!$result) {
    echo "<b><br/>==SQL Error==</b><br/>".mysql_error()."<br/><br/>Query was:<br/>".$sql;
  }
}
}

if (isset($saferequest['spiritserv'])) {
$relgskills=$saferequest['spiritserv'];
foreach($relgskills as $ms) {
  $sql = "INSERT INTO skillsselected VALUES ('".$fbid."','".$ms."')";
  $result = mysql_query($sql);
  if(!$result) {
    echo "<b><br/>==SQL Error==</b><br/>".mysql_error()."<br/><br/>Query was:<br/>".$sql;
  }
}
}

if (isset($saferequest['region'])) {
$region=$saferequest['region'];
foreach($region as $ms) {
  $sql = "INSERT INTO regionsselected VALUES ('".$fbid."','".$ms."')";
  $result = mysql_query($sql);
  if(!$result) {
    echo "<b><br/>==SQL Error==</b><br/>".mysql_error()."<br/><br/>Query was:<br/>".$sql;
  }
}
}

if (isset($saferequest['state'])) {
$mystate = $saferequest['state'];
//print_r($mystate);
//foreach($mystate as $ms) {
  $sql = "INSERT INTO usstatesselected VALUES ('".$fbid."','".$mystate."')";
  $result = mysql_query($sql);
  if(!$result) {
    echo "<b><br/>==SQL Error==</b><br/>".mysql_error()."<br/><br/>Query was:<br/>".$sql;
  }
//}
}

if (isset($saferequest['country'])) {
$country = $saferequest['country'];
foreach($country as $ms) {
  $sql = "INSERT INTO countriesselected VALUES ('".$fbid."','".$ms."')";
  $result = mysql_query($sql);
  if(!$result) {
    echo "<b><br/>==SQL Error==</b><br/>".mysql_error()."<br/><br/>Query was:<br/>".$sql;
  }
}
}

if (isset($saferequest['dur'])) {
$dur = $saferequest['dur'];
foreach($dur as $ms) {
  $sql = "INSERT INTO durationsselected VALUES ('".$fbid."','".$ms."')";
  $result = mysql_query($sql);
  if(!$result) {
    echo "<b><br/>==SQL Error==</b><br/>".mysql_error()."<br/><br/>Query was:<br/>".$sql;
  }
}
}

if ($is_trip) {
	if ($update)
		echo "<fb:redirect url='profile.php?type=trip&update=".$update." />";
	else
		echo "<fb:redirect url='profile.php?type=trip&tripid=".$tripid." />";
}
else if ($isreceiver==0) {
	echo "<fb:redirect url='profile.php?type=volunteer' />";
}
else if ($isreceiver) {
	echo "<fb:redirect url='profile.php?type=mission' />";
}


?>
