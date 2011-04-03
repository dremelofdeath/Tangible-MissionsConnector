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
ob_start();

$fb = cmc_startup($appapikey, $appsecret,0);

$response = array('response' => array('hasError' => false, 'profilemsg' => 'Welcome to your CMC Profile', 'uid' => 100000022664372));
$somejson = json_encode($response);

// During actual implementation, $somejson will come from frontend
// through either get or post
$mydataobj = json_decode($somejson);

// Now process the json object
if ($mydataobj->{'response'}->{'hasError'}) {
   // have appropriate response if there is an error
echo 'Error <br />';
}
else {
   $fbid = $mydataobj->{'response'}->{'uid'};
}

$con = arena_connect();

if (isset($_GET['userid'])) {
  $showuserid = $_GET['userid'];
  $sql = 'select * from users where userid="'.$showuserid.'"';
}
else {
//$fbid = $user_id;
$showuserid = $fbid;
$sql = "select * from users where userid=".$fbid;
}

$result = mysql_query($sql);

if(!$result) {
  echo "SQL Error: " . mysql_error() . " ";
}

$is_volunteer = false;
$is_mission = false;
$is_trip = false;


if(isset($_GET['type'])) {
  if($_GET['type'] == "volunteer") $is_volunteer = true;
  if($_GET['type'] == "mission") $is_mission = true;
  if($_GET['type'] == "trip") {
    $is_trip = true;
    if (isset($_GET['tripid']))
      $tripid = $_GET['tripid'];
    if (isset($_GET['update'])) {
      $update = $_GET['update'];
      $tripid = $update;
    }
  }
}

function cmc_profile_render_id_join($title2,$title,$desc, $descdb, $selecteddb, $fbid, &$msg, $is_trip,&$k) {
  $sql = "SELECT ".$desc." FROM ".$descdb.
     " JOIN ".$selecteddb." ON ".$descdb.".id = ".$selecteddb.".id".
     " WHERE ".$selecteddb.".userid='".$fbid."'";
  if($result = mysql_query($sql)) {
    $i=0;
    while($row= mysql_fetch_array($result)) {

  if ($i==0) {
    if ($k==0) {
      echo "<b>".$title2."</b>:<br/>";
      $k++;
    }
    echo "<b>".$title."</b>:<br/>";
  }
  
  $i++;
  
      if ($is_trip) {
  $msg = $msg.' '.$row[$desc];
      }
      echo $row[$desc];
    echo "<br/>";
    }
  } else {
    echo "SQL Error ".mysql_error()." ";
  }
}

function cmc_profile_render_skills($title, $type, $fbid) {
  $sql = "SELECT skilldesc FROM skills".
       " JOIN skillsselected ON skills.id = skillsselected.id".
       " WHERE skills.type=".$type." AND skillsselected.userid='".$fbid."'";
  if($result = mysql_query($sql)) {
    $i=0;
    while($row= mysql_fetch_array($result)){
      if ($i==0) {
      echo "<br/><br/><br/>";
      echo "<b>".$title."</b>:<br/>";
      }
      $i++;
      echo $row['skilldesc'];
    echo "<br/>";
    }
  } else {
    echo "SQL Error ".mysql_error()." ";
  }
}
?>

<script LANGUAGE="javascript">

function postMessage1(mUid, mTarget, mMsg, mCaption, mDesc, mLink) {
  FB.ui(
    {
      method: 'stream.publish',
      auto_publish: true,
      message: mMsg,
      uid: mUid,
      target_id: mTarget,
      attachment: {
        name: 'Test',
        caption: mCaption,
        description: (mDesc),
        href: mLink,
        action_links: [ { text: 'test', href: mLink } ]
      },
      function(response) {
        if (response && response.post_id) {
          alert('Post was published.');
        } else {
          alert('Post was not published.');
        }
      }
    }
  );
}

function postMessage2(mUid, mMsg, mCaption, mDesc, mLink, mName) {
  FB.api('/'+mUid+'/feed', 'post',
    {
      message: mMsg,
      name: mName,
      caption: mCaption,
      description: (mDesc),
      link: mLink
    },
    function(response) {
      if (!response || response.error) {
        alert('Error occured');
      } else {
        alert('Post ID: ' + response);
      }
    }
  ); 
}
</script>

<?php
if(mysql_num_rows($result) != 0) {
  while($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
    $name = $row['name'];
    $organization = $row['organization'];
    $isleader = $row['isreceiver'];
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
        //echo 'USERID:'.$showuserid.'<br />';

       // This call is no longer supported
       //$info = $fb->api_client->users_getInfo($showuserid, 'name,email');

    // Get the name information directly from the facebook profile pages
    $name = get_name_from_fb_using_curl($showuserid);
    /*
    $record = $info[0];
    $name = $record['name'];
    */
  echo '<center>';
  echo '<b> '.$name."'s name was not updated when their profile was created.<br />";
  echo 'Updating the name in the profiles database... ';
  $sql2 = 'update users set name="'.$name.'" where userid="'.$showuserid.'"';
  $result2 = mysql_query($sql2);
  echo 'done!</b><br/><br/></center>';
  } 
/*
if ($GLOBALS["trip"]) {
 $is_trip = 1;
 echo 'This is a trip';
}
else {
 $is_trip = 0;
 echo 'This is not a trip';
}
*/

  if ($isleader == 1) {
    if ($showuserid==$fbid)
    $volstring = " are leading missions";
    else
    $volstring = " is leading missions";

  } else {
    if ($showuserid==$fbid)
    $volstring = " are a volunteer";
    else
    $volstring = " is a volunteer";   
  }

  $partnerstring = "";
  if($partnersite) {
    $partnerstring = "(CMC Partner)";
  }

 // now put the trip information into a string for input into a news feed
 if ($is_trip) {
  if ($update)
  $message = $name.' updated the trip ';
  else
  $message=$name.' is making a trip ';  
 }

 if (empty($volstring)) {
  if ($showuserid==$fbid) {
    echo '<a href="http://www.facebook.com/profile.php?id='.$showuserid.'"><img src="http://graph.facebook.com/'.$showuserid.'/picture" /></a>';
    //echo "<fb:profile-pic uid=".$showuserid." linked='true' /> <br /><br />".$name."";
  }
 }
 else 
    echo '<a href="http://www.facebook.com/profile.php?id='.$showuserid.'"><img src="http://graph.facebook.com/'.$showuserid.'/picture" /></a>';
  //echo "<fb:profile-pic uid=".$showuserid." linked='true' /> <br /><br /><fb:name uid=".$showuserid." linked='true' shownetwork='true' />";

  echo $volstring.'<b/>'.$partnerstring.'<br/><br /><br />';

  if ($isleader == 1) {
  if (!empty($organization))
    echo "<b>Agency Name: </b> $organization<br/>"; 
  if (!empty($website))
    echo "<b>Agency Website: </b> $website<br/>";
  if (!empty($aboutme))
    echo "<b>About My Agency: </b> $aboutme<br/>";
  
  }
  if (!empty($zip))
    echo "<b>Zipcode: </b> $zip<br/>"; 
  if (!empty($email))
    echo "<b>Email: </b> $email<br/>";
  if (!empty($phone))
    echo "<b>Phone: </b> $phone<br/>";

  if (!empty($misexp))
  echo "<b>Missions Experience: </b> $misexp<br/>";
  if (!empty($relg))
  echo "<b>Religious Affiliation: </b> $relg<br/>";

  if ($isleader == 1) {
  cmc_profile_render_skills("Facility Medical Offerings", '4', $showuserid);
  cmc_profile_render_skills("Facility Non-Medical Offerings", '5', $showuserid);
  }
  
  cmc_profile_render_skills("Medical Skills", '1', $showuserid);
  cmc_profile_render_skills("Non-Medical Skills", '2', $showuserid);
  cmc_profile_render_skills("Spiritual Skills", '3', $showuserid);

  echo "<br/><br/><br/>";
  
  //if (!empty($state)) {
 //   echo "<b>State: </b> $state<br/>"; 
  //}
  $pp=-1;
  cmc_profile_render_id_join("","State",'longname', 'usstates', 'usstatesselected', $showuserid, $message, $is_trip,$pp);
  echo "<br/>";

  if (!empty($city)) {
    echo "<b>City: </b> $city<br/>"; 
  }

  
  if ($is_trip) {
    $message = $message.' with following parameters: ';
    // Update the string to have this trip information
  $sql = 'select tripname,tripdesc,phone,email,departure,returning,zipcode from trips where id="'.$tripid.'"';
  $result = mysql_query($sql);
  $row = mysql_fetch_array($result,MYSQL_ASSOC);
    if (!empty($row['tripname']))
    $message = $message.'Name: '.$row['tripname'].' ';
    if (!empty($row['tripdesc']))
    $message = $message.'Description: '.$row['tripdesc'].' ';
    if (!empty($row['phone']))
    $message = $message.'Phone: '.$row['phone'].' ';
    if (!empty($row['email']))
    $message = $message.'Email: '.$row['email'].' ';
    if (!empty($row['departure'])) {
    $departn = explode(' ',$row['departure']);
    $newdp = explode('-',$departn[0]);
    //$depart = date('Y-m-d', $departn);
          //echo 'depart '.$depart.'<br />';

    //$depdate = explode(' ',$row['departure']);
    $message = $message.'Departure: '.$newdp[1].'-'.$newdp[2].'-'.$newdp[0].' ';
  }
    if (!empty($row['returning'])) {
    //$return = date('m-d-Y', $row['returning']);
    $returnn = explode(' ',$row['returning']);
    $newret = explode('-',$returnn[0]);
    //$retdate = explode(' ',$row['returning']);
    $message = $message.'Returning: '.$newret[1].'-'.$newret[2].'-'.$newret[0].' ';
    //$message = $message.'Returning: '.$retdate[0].' ';
  }
    if (!empty($row['zipcode']))
    $message = $message.'Zipcode: '.$row['zipcode'].' ';

  }
  else
    $message = '';

  //echo "<b>Geographic Areas of Interest:</b><br/>"; 
  //echo "<b>Regions:</b><br/>";
  $kk=0;
  cmc_profile_render_id_join("Geographic Areas of Interest","Regions",'name', 'regions', 'regionsselected', $showuserid, $message, $is_trip,$kk);
  echo "<br/>";

  //echo "<b>Countries:</b><br/>";
  cmc_profile_render_id_join("Geographic Areas of Interest","Countries",'longname', 'countries', 'countriesselected', $showuserid, $message, $is_trip,$kk);
  echo "<br/>";

  //echo "<br/><br/><br/>";
  //echo "<b>Preferred Duration of Mission Trips:</b><br/>";
  $pp=-1;
  cmc_profile_render_id_join("","Preferred Duration of Mission Trips",'name', 'durations', 'durationsselected', $showuserid, $message, $is_trip,$pp);

  //echo "<br/><br/><br/>";
  $trips = array();
  $sql = "select tripid from tripmembers where userid='".$showuserid."'";
  if($result = mysql_query($sql)) {
    while($row= mysql_fetch_array($result)) {
      //$tname = $row['tripname'];
      $tid=$row['tripid'];
      $sql2 = 'select tripname from trips where id="'.$tid.'"';
      $result2 = mysql_query($sql2);
      $row2 = mysql_fetch_array($result2);
      $tname = $row2['tripname'];
      $trips[$tid]=$tname;    
    }
  } else {
    echo "SQL Error ".mysql_error()." ";
  }

  if (!empty($trips)) {
  echo "<b>Participating in Mission Trips:</b><br/>";
  foreach($trips as $key => $curtrip) {
    //$curtid=key($curtrip);
    echo "<br/><a href='profileT.php?tripid=".$key."'>".$curtrip."</a>";

    echo "<form action='share.php?tripid=".$key."' method='get'>";
    echo '<input type="button" name="share" value="Share">';
    echo '</form>';
    echo "<br /><br />";

    /*
    echo "<fb:editor action='share.php?tripid=".$key."' method='get'>";
    echo '<fb:editor-buttonset> ';
    echo "<fb:editor-button value='Share' name='share'/>";
    echo '</fb:editor-buttonset>';
    echo "</fb:editor>";
    echo "<br /><br />";
    */
  }
  }

  //echo "<br/><br/>";

  //echo 'MY MESSAGE='.$message.'<br />';

  if ($is_trip) {
  // Does the user have permission to publish their messages
  // If not, they should be prompted to allow access
  //$res = $fb->getApiUrl->users_hasAppPermission('publish_stream',null);

  //if (!$res) {
  ?>

  <script type="text/javascript">
  
  function callback (perms) {
   if (!perms) {
      message('You did not grant the special permission to post to friends wall without being prompted.');
  } else {
        message('You can now publish to walls without being prompted.');
  }
  }

  Facebook.showPermissionDialog("read_stream,publish_stream,manage_pages,offline_access",callback);



  </script>


<?php
//}
/*
   echo '<SCRIPT LANGUAGE="javascript"><!--n';
   echo "postmessage2(".$fbid.",".$appid.",".$message.",Trips News,Missions Connector Trips Wall,http://apps.facebook.com/missionsconnector/tripswall.php);n";
   echo "// --></SCRIPT>n";
*/
?>

<?php
/*
function callFb($url, $params) {
$ch = curl_init();
curl_setopt_array($ch, array(CURLOPT_URL => $url,CURLOPT_POSTFIELDS => http_build_query($params),CURLOPT_RETURNTRANSFER => true,CURLOPT_VERBOSE => true));
$result = curl_exec($ch);
curl_close($ch);
return $result;
}
*/

/*
<script type="text/javascript">
function callback (post_id, exception) {
 if(post_id) {
    post_to_my_server(post_id);
     }
     }

     Facebook.streamPublish('Test Message',null,null,305928355832,'',callback,true,100000022664372)

</script>
*/
?>

<?php

//  echo "<script type=\"text/javascript\"  Facebook.streamPublish('Test Message', null, null,'305928355832','',callback,true,'')>";
  //echo "<script type=\"text/javascript\"  Facebook.streamPublish(".$message.", null, null,'".$appid."',null,null,true,null)>";
  //echo '</script>';

  //$action_links = array( array('text' => 'App Feed', 'href' => 'http://graph.facebook.com/305928355832/feed'));

  session_start();

function stream_callback(){
}

  // get a list of friends who are using the CMC app
  //$friends=$fb->api_client->friends_getAppUsers();

  $_SESSION['tripid'] = $tripid;
  if (!isset($_SESSION['lmsg'])) {
  /*
    if (isset($_SESSION['paidstr']))
    $message = $message.$_SESSION['paidstr'];
    */
   ?>

    <script type="text/javascript">
    Facebook.streamPublish(<?PHP $message ?>,null,null,<?php $appid ?>,' ',null,true,<?php $appid ?>);
    </script>

    <?php
  //$fb->api_client->stream_publish($message,null,null,$appid,$appid);

  /*
  if (!empty($friends)) {
  foreach ($friends as $currentfriend) {
  echo '<script type="text/javascript">';
  echo "Facebook.streamPublish('".$message."', null, null,'".$currentfriend."','',null,'true')";
  echo '</script>';
  }
  }
  */

  //$fb->api_client->stream_publish($message,null,null,$sampleid,$fbid);
  $_SESSION['lmsg'] = $message;
  }
  else {
  /*
    if (isset($_SESSION['paidstr']))
    $message = $message.$_SESSION['paidstr'];
  */
    if (strcmp($message,$_SESSION['lmsg'])) {
      ?>

    <script type="text/javascript">
    Facebook.streamPublish(<?PHP $message ?>,null,null,<?php $appid ?>,' ',null,true,<?php $appid ?>);
    </script>

      <?php
    //$fb->api_client->stream_publish($message,null,null,$appid,$appid);

  /*
  if (!empty($friends)) {
  foreach ($friends as $currentfriend) {
  echo '<script type="text/javascript">';
  echo 'Facebook.streamPublish("'.$message.'", null, null,"'.$currentfriend.'","",null,true)';
  echo '</script>';
  }
  }
  */

    //$fb->api_client->stream_publish($message,null,null,$sampleid,$fbid);
    $_SESSION['lmsg'] = $message;  
    }
  }

  echo "<h1>Some Trip Actions for you:</h1><br/>";
  echo "<br/><a href='invitetotrip.php?value=1'>Invite others to trips</a><br/><br/>";
  echo "<br/><a href='invitetotrip.php?value=0'>Invite others to trips as Trip Administrators</a><br/><br/>";

  /*
  echo '<fb:comments xid="missionsconnectortrips" canpost="true" candelete="true" numposts="10" returnurl="http://apps.facebook.com/missionsconnector/tripswall.php">';
  echo '<fb:title>Missionsconnector trips wall</fb:title>';
 // echo '<fb:message>'.$message.'</fb:message>';
  echo '</fb:comments>';
  */
 

 }
  //echo '<fb:feed title="Trips News Feed" max="10"/>';

} else {
  echo "This person doesn't have a profile set up yet!<br/>";
}
  
?>  
