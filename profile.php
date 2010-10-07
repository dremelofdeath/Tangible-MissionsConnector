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

$fb = cmc_startup($appapikey, $appsecret);
$fbid = $fb->require_login();

$con = arena_connect();

//$fbid = $user_id;
$sql = "select * from users where userid=".$fbid;

$result = mysql_query($sql);

if(!$result) {
  echo "SQL Error: " . mysql_error() . " ";
}

function cmc_profile_render_id_join($desc, $descdb, $selecteddb, $fbid) {
  $sql = "SELECT ".$desc." FROM ".$descdb.
		 " JOIN ".$selecteddb." ON ".$descdb.".id = ".$selecteddb.".id".
		 " WHERE ".$selecteddb.".userid='".$fbid."'";
  if($result = mysql_query($sql)) {
    while($row= mysql_fetch_array($result)) {
      echo $row[$desc];
	  echo "<br/>";
    }
  } else {
    echo "SQL Error ".mysql_error()." ";
  }
}

function cmc_profile_render_skills($title, $type, $fbid) {
  echo "<br/><br/><br/>";
  echo "<h1>".$title.":</h1><br/>";
  $sql = "SELECT skilldesc FROM skills".
	     " JOIN skillsselected ON skills.id = skillsselected.id".
	     " WHERE skills.type=".$type." AND skillsselected.userid='".$fbid."'";
  if($result = mysql_query($sql)) {
    while($row= mysql_fetch_array($result)){
      echo $row['skilldesc'];
	  echo "<br/>";
    }
  } else {
    echo "SQL Error ".mysql_error()." ";
  }
}

if(mysql_num_rows($result) != 0) {
  while($row = mysql_fetch_array($result)) {
    $name = $row['name'];
    $isleader = $row['isreceiver'];
    $zip = $row['zipcode'];
    $email = $row['email'];
    $misexp = $row['missionsexperience'];
    $relg = $row['religion'];
    $aboutme = $row['aboutme'];
    $website = $row['website'];
    $partnersite = $row['partnersite']; 
  }

  if ($isleader == 1) {
    $volstring = "is leading missions";
  } else {
    $volstring = "is a volunteer";   
  }

  $partnerstring = "";
  if($partnersite) {
    $partnerstring = "(CMC Partner)";
  }

  echo "<b>$name $volstring</b> $partnerstring<br/>";

  echo "<b>Zipcode: </b> $zip<br/>"; 
  echo "<b>Email: </b> $email<br/>";

  echo "<b>Missions Experience: </b> $misexp<br/>";
  echo "<b>Religious Affiliation: </b> $relg<br/>";
  echo "<b>About Me: </b> $aboutme<br/>";

  echo "<b>Website: </b> $website<br/>";

  cmc_profile_render_skills("Medical Skills", '1', $fbid);
  cmc_profile_render_skills("Non-Medical Skills", '2', $fbid);
  cmc_profile_render_skills("Spiritual Skills", '3', $fbid);

  echo "<br/><br/><br/>";
  echo "<h1>Geographic Areas of Interest:</h1><br/>";

  echo "<h2>Regions:</h2><br/>";
  cmc_profile_render_id_join('name', 'regions', 'regionsselected', $fbid);
  echo "<br/>";

  echo "<h2>Countries:</h2><br/>";
  cmc_profile_render_id_join('longname', 'countries', 'countriesselected', $fbid);
  echo "<br/>";

  echo "<br/><br/><br/>";
  echo "<h1>Preferred Duration of Mission Trips:</h1><br/>";
  cmc_profile_render_id_join('name', 'durations', 'durationsselected', $fbid);

  echo "<br/><br/><br/>";
  $trips = array();
  $sql = "select tripname,tripid from trips,tripmembers where userid='".$fbid."'";
  if($result = mysql_query($sql)) {
    while($row= mysql_fetch_array($result)) {
      $tname = $row['tripname'];
      $tid=$row['tripid'];
      $trips[$tid]=$tname;    
    }
  } else {
    echo "SQL Error ".mysql_error()." ";
  }

  echo "<h1>Participating in Mission Trips:</h1><br/>";
  foreach($trips as $curtrip) {
    $curtid=key($curtrip);
    echo $curtrip."<br/><a href='profileT.php?tripid=".$curtid."'>".$curtrip."</a><br/><br/>";
  }

  echo "<br/><br/>";
} else {
  echo "You don't have a profile yet!<br/>";
}
  
?>  
