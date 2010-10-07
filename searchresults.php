<?php
// Application: Christian Missions Connector
// File: 'searchresults.php' 
//  search results retrieved and displayed (sort by distance**)
// 
//require_once 'facebook.php';

include_once 'common.php';

echo "test if any echo works at the top";

$fb = cmc_startup($appapikey, $appsecret);

function haversine($lat, $lng, $lat2, $lng2) {
  $radius = 6378100; // radius of earth in meters
  $latDist = $lat - $lat2;
  $lngDist = $lng - $lng2;
  $latDistRad = deg2rad($latDist);
  $lngDistRad = deg2rad($lngDist);
  $sinLatD = sin($latDistRad);
  $sinLngD = sin($lngDistRad);
  $cosLat1 = cos(deg2rad($lat));
  $cosLat2 = cos(deg2rad($lat2));
  $a = $sinLatD*$sinLatD + $cosLat1*$cosLat2*$sinLngD*$sinLngD*$sinLngD;
  if($a<0) $a = -1*$a;
  $c = 2*atan2(sqrt($a), sqrt(1-$a));
  $distance = $radius*$c;
  $distance = $distance*3.2808399;

  return $distance;
}

?>
<br/><br/>




<?php
$profileid=$_Request['id'];


$con = mysql_connect(localhost,"arena", "***arena!password!getmoney!getpaid***");
if(!$con)
{
  die('Could not connect: ' .  mysql_error());
}

mysql_select_db("missionsconnector", $con);

if($_REQUEST['type']="Active Mission Organizers"){
  $friends=array();
  $sql="select users.userid from users,skills,skillsselected,countries,countriesselected,regions,regionsselected,durations,durationsselected where users.userid='".$profileid."' and isreceiver=1 and users.religion='".$_REQUEST['relg']."' and skills.skilldesc='".$_REQUEST['medskills']."' and skills.skilldesc='".$_REQUEST['otherskills']."' and skills.skilldesc='".$_REQUEST['spiritserv']."' and countries.longname='".$_REQUEST['country']."' and regions.name='".$_REQUEST['region']."' and durations.name='".$_REQUEST['dur']."' and durations.id=durationsselected.id and regionsselected.id=regions.id and countriesselected.id=countries.id and skillsselected.id=skills.id and users.userid=skillsselected.userid and users.userid=countriesselected.userid and users.userid=regionsselected.userid and users.userid=durationsselected.userid";
  if($result = mysql_query($sql)){
    $num_rows = mysql_num_rows($result);
    while($row= mysql_fetch_array($result)){
      $id = $row['users.userid'];
      $friends[$id];  
    }}else {
      echo "SQL Error ".mysql_error()." ";
    }
    if($num_rows=0){
      echo "We're sorry, there were no matches for your search criteria. This version of Christian Missions Connector does not support partial matches, so please try again with fewer fields selected or use the 'Any' option for fileds that you do not have a strong preference for.";
    }
    foreach ($friends as $currentfriend){
      echo "<fb:profile-pic uid=".$currentfriend." linked='false' /> <fb:name uid=".$currentfriend." linked='true' shownetwork='true' /><br/><br/>";
      //echo "<a href='http://apps.facebook.com/missionsconnector/profile.php?userid=".$currentfriend.">See Profile</a><br/><br/>";
echo "successful active misssions call"; 

}}


      if($_REQUEST['type']="Volunteers"){
        $friends=array();
        $sql = "select users.userid from users,skills,skillsselected,countries,countriesselected,regions,regionsselected,durations,durationsselected where users.userid='".$profileid."' and isreceiver=0 and users.religion='".$_REQUEST['relg']."' and skills.skilldesc='".$_REQUEST['medskills']."' and skills.skilldesc='".$_REQUEST['otherskills']."' and skills.skilldesc='".$_REQUEST['spiritserv']."' and countries.longname='".$_REQUEST['country']."' and regions.name='".$_REQUEST['region']."' and durations.name='".$_REQUEST['dur']."' and durations.id=durationsselected.id and regionsselected.id=regions.id and countriesselected.id=countries.id and skillsselected.id=skills.id and users.userid=skillsselected.userid and users.userid=countriesselected.userid and users.userid=regionsselected.userid and users.userid=durationsselected.userid";
        if($result = mysql_query($sql)){
          while($row= mysql_fetch_array($result)){
            $id = $row['users.userid'];
            $friends[$id];  
          }}else {
            echo "SQL Error ".mysql_error()." ";
          }

          foreach ($friends as $currentfriend){
            echo "<fb:profile-pic uid=".$currentfriend." linked='false' /> <fb:name uid=".$currentfriend." linked='true' shownetwork='true' /><br/><br/>";
            //echo "<a href='http://apps.facebook.com/missionsconnector/profile.php?userid=".$currentfriend.">See Profile</a><br/><br/>";
          }}


            if($_REQUEST['type']="Upcoming Mission Trips"){
              $triparray=array();
              $sql = "select trips.tripname,trips.id from trips,tripmembers,users,skills,skillsselected,countries,countriesselected,regions,regionsselected,durations,durationsselected where trips.id=tripmembers.tripid and tripmembers.userid=users.userid and tripmembers.isadmin='1' and users.religion='".$_REQUEST['relg']."' and skills.skilldesc='".$_REQUEST['medskills']."' and skills.skilldesc='".$_REQUEST['otherskills']."' and skills.skilldesc='".$_REQUEST['spiritserv']."' and countries.longname='".$_REQUEST['country']."' and regions.name='".$_REQUEST['region']."' and durations.name='".$_REQUEST['dur']."' and durations.id=durationsselected.id and regionsselected.id=regions.id and countriesselected.id=countries.id and skillsselected.id=skills.id and users.userid=skillsselected.userid and users.userid=countriesselected.userid and users.userid=regionsselected.userid and users.userid=durationsselected.userid";
              if($result = mysql_query($sql)){
                while($row= mysql_fetch_array($result)){
                  $id = $row['trips.id'];
                  $name=$row['trips.tripname'];
                  $triparray[$id]=$name;
                  foreach ($trips as $currenttrip){
                    echo "<a href='profileT.php?tripid=".$tripid."'>".$tripname."</a><br/><br/>";
                    }}}else {
                      echo "SQL Error ".mysql_error()." ";
                    }

}
echo$_REQUEST['type'];

	     
?>
