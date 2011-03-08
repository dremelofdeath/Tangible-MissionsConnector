<?php
// Application: Christian Missions Connector
// File: 'trips.php'
//  shows all trips the user is a member of
//
//require_once 'facebook.php';

include_once 'common.php';

$fb = cmc_startup($appapikey, $appsecret,0);
$fbid = $fb->require_login("publish_stream,read_stream");

?>

<?php

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



//$today = date("F j,Y");

$todayy = date("Y");
$todaym = date("m");
$todayd = date("d");
$today = getdatestring($todayy,$todaym,$todayd);

// get all trips that are in the future
$sql = 'select * from trips where departure >="'.$today.'"';
//echo $sql.'<br />';

if ($result = mysql_query($sql)) {

   $numrows = mysql_num_rows($result);

   if ($numrows==0) {
	echo "There are no upcoming trips <br/>";
	echo "<a href='welcome.php'>Go back to welcome page</a><br /><br />";
   }
   else {
   echo "<br/>If you would like to search within upcoming trips, <a href='advancedsearch.php'><b>click here</b></a><br/><br/>";
  echo '<br /><b>These are the upcoming trips: <b/> <br /><br />';
  while ($row = mysql_fetch_array($result)) {
	echo "<br/><a href='profileT.php?tripid=".$row['id']."'>".$row['tripname']."</a><br/><br/>";

  }
  }
 
}
else {
	echo '<b>MYSQL Error </b><br />';
	//echo "<fb:redirect url='tripoptions.php' />";
}

?>



