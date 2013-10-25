<?php
// Application: Christian Missions Connector
// File: 'trips.php'
//  shows all trips the user is a member of
//
//require_once 'facebook.php';

include_once 'common.php';

$con = arena_connect();

$saferequest = cmc_safe_request_strip($con);
$has_error = FALSE;
$err_msg = '';

if (array_key_exists('ids', $saferequest) && array_key_exists('fbid', $saferequest) && array_key_exists('tripid', $saferequest) && array_key_exists('type', $saferequest)) {
  // invitation ids, tripid and facebook userid should be provided
  $tripid = $saferequest['tripid'];
  $fbid = $saferequest['fbid'];
  $selectedids = $saferequest['ids'];
  $membertype = $saferequest['type'];
} 
else if (array_key_exists('fbid', $saferequest) && array_key_exists('tripid', $saferequest)) {
  $tripid = $saferequest['tripid'];
  $fbid = $saferequest['fbid'];
}
else {
  // error case: all needed variables are not defined
  $has_error = TRUE;
  $err_msg = "Required parameters not defined.";
}

$json = array();


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

function timeDiff($firstTime,$lastTime) {
  // convert to unix timestamps
  $firstTime=strtotime($firstTime);
  $lastTime=strtotime($lastTime);

  // perform subtraction to get the difference (in seconds) between times
  $timeDiff=$lastTime-$firstTime;

  // return the difference
  return $timeDiff/86400;
}


if (!$has_error) {
  // Modify the trip members table based on user invitations
  if (isset($selectedids)) {

    $todayy = date("Y");
    $todaym = date("m");
    $todayd = date("d");
    $todayH = date("H");
    $todayi = date("i");
    $todays = date("s");

    $today = getdatestring($todayy,$todaym,$todayd,$todayH,$todayi,$todays);

    $sql2 = 'select * from notifications where id="'.$fbid.'"';
    $result2 = $con->query($sql2);
    if (!$result2) {
      setjsonmysqlerror($has_error,$err_msg,$sql2);
    } else {
      if ($result->num_rows == 0) {
        $sql2 = 'insert into notifications (id,starttime,notifications) VALUES ("'.$fbid.'","'.$today.'","'.count($selectedids).'")';
        $result2 = $con->query($sql2);
        if (!$result2) {
          setjsonmysqlerror($has_error,$err_msg,$sql2);
        }
      } else {
        $row = $result2->fetch_array();
        $notifications = $row['notifications'];
        $starttime = $row['starttime'];

        // If time difference is greater than 1 day, reset notifications and starttime
        if (timeDiff($starttime,$today) > 1) {
          $notifications = 0;
          $sql2 = 'update notifications set notifications="'.$notifications.'", starttime="'.$today.'" where id="'.$fbid.'"';
        } else {
          // now update notifications or reset depending on time
          $notifications = $notifications + count($selectedids);
          $sql2 = 'update notifications set notifications="'.$notifications.'" where id="'.$fbid.'"';
        }

        $result2 = $con->query($sql2);
        if (!$result2) {
          setjsonmysqlerror($has_error,$err_msg,$sql2);
        }
      }

      // update the database tables to reflect that these guys have been invited
      foreach($selectedids as $selected) {
        $sql = 'select userid from tripmembers where userid="'.$selected.'" and tripid="'.$tripid.'"';
        $result = $con->query($sql);

        if (!$result) {
          setjsonmysqlerror($has_error,$err_msg,$sql);
        } else {
          if ($result->num_rows > 0) {
            $sql = 'UPDATE tripmembers set invited="1" where userid="'.$selected.'" and tripid="'.$tripid.'" and type="'.$membertype.'"';
            $result = $con->query($sql);
            if (!$result) {
              setjsonmysqlerror($has_error,$err_msg,$sql);
            }
          } else {
            $sql = 'INSERT into tripmembers (userid, tripid,invited,type) VALUES ("'.$selected.'","'.$tripid.'","1","'.$membertype.'")';
            $result = $con->query($sql);
            if (!$result) {
              setjsonmysqlerror($has_error,$err_msg,$sql);
            }
          }
        }
      }
    }

  } else {

    $sql = 'select * from notifications where id="'.$fbid.'"';
    $result = $con->query($sql);
    if (!$result) {
      setjsonmysqlerror($has_error,$err_msg,$sql);	
    } else {
      if ($result->num_rows > 0) {
        $row = $result->fetch_array();
        $notifications = $row['notifications'];
      } else {
        $notifications = 0;
      }
    }

    if ($notifications > 100) {
      $has_error = TRUE;
      $err_msg = 'You have already sent more than 100 invitations for today, you cannot send any more';
    } else {

      // Retrieve array of friends who've already authorized the app - this should come from the front-end
    /*
    $friends = array();
    if (is_array($appfriends) && count($appfriends)) {
      foreach ($appfriends as $friend) {
        $friends[] = $friend['uid'];
      }
    }
     */

      $sql = 'select userid from tripmembers where userid !="'.$fbid.'" and invited="1" and tripid="'.$tripid.'"';
      $myfriends=array();
      $result = $con->query($sql);
      if (!$result) {
        setjsonmysqlerror($has_error,$err_msg,$sql);
      }
      else {
        while ($invitedfriends = $result->fetch_array()) {
          $myfriends[] = $invitedfriends['userid'];
        }
        $json['invitedfriends'] = $myfriends;
      }
    }

    // Get some information about the trip so that this information can be passed on in the invite
    $sql = 'select tripname,tripdesc,destination,departure,returning,religion from trips where id="'.$tripid.'"';
    $result = $con->query($sql);
    if (!$result) {
      setjsonmysqlerror($has_error,$err_msg,$sql);
    } else {
      $row = $result->fetch_array();
      if (!empty($row['tripname']))
        $json['tripname'] = $row['tripname'];
      if (!empty($row['tripdesc']))
        $json['tripdesc'] = $row['tripdesc'];
      if (!empty($row['destination']))
        $json['destination'] = $row['destination'];
      if (!empty($row['departure']))
        $json['departure'] = $row['departure'];
      if (!empty($row['returning']))
        $json['returning'] = $row['returning'];
      if (!empty($row['religion']))
        $json['religion'] = $row['religion'];
    }

  }
/*
else if ($value == 2) { // remove others from trip
   echo "<fb:redirect url='deletetripmembers.php?tripid=".$tripid."' />";
}
else if ($value == 3) { // delete trip
  echo "<fb:redirect url='deletetrips.php?tripid=".$tripid."' />";
}
else if ($value == 4) { // update trip
  echo "<fb:redirect url='makeprofile.php?type=trip&edit=1&update=".$tripid."' />";
}
else if ($value == 5) { // remove self from a trip
  echo "<fb:redirect url='removeself.php?tripid=".$tripid."' />";
}
 */
}

$json['has_error'] = $has_error;

if ($has_error) {
  $json['err_msg'] = $err_msg;
}

echo json_encode($json);

