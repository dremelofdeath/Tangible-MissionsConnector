<?php
include_once 'common.php';
//include_once 'pagecounter.php';

$fb = cmc_startup($appapikey, $appsecret,0);
$fbid = get_user_id($fb);
//$fbid = $fb->require_login($required_permissions = 'publish_stream');

// create app admin ids - facebook ids of people who have admin rights for this application
$appadminids = array();
$appadminids[] = 100000022664372;
$appadminids[] = 707283972;
$appadminids[] = 25826994;

$allow=0;
for ($i=0;$i<count($appadminids);$i++) {
  if ($fbid==$appadminids[$i]) {
  $allow = 1;
  continue 1;
  }
}

if (!$allow) {
  echo "<fb:redirect url='welcome.php?error=1' />"; 
} else {

  process_admin_commands();

  echo "Welcome to the Christian Missions Connector's Administrative Area <br /><br />";
  numhits();
  echo "<br />";

  echo "<form action='admin.php' method='POST'>";
  echo "  <input type='hidden' name='cmd' value='purgeuser' />";
  echo "  <label for='admin_purge_userid'>";
  echo "    Purge a user from the database: ";
  echo "  </label>";
  echo "  <input type='text' id='admin_purge_userid' name='userid' />";
  echo "  <input type='submit' value='Purge this user' />";
  echo "</form>";
  echo "<br />";

  echo "<form action='admin.php' method='POST'>";
  echo "  <input type='hidden' name='cmd' value='scrubuser' />";
  echo "  <label for='admin_scrub_userid'>";
  echo "    Scrub a user's profile data: ";
  echo "  </label>";
  echo "  <input type='text' id='admin_scrub_userid' name='userid' />";
  echo "  <input type='submit' value='Scrub this user' />";
  echo "</form>";
}

function numhits() {
  // sum up all the user specific hits
  $sql = "select * from hits";
  $result = mysql_query($sql);
  $unique_hits = 0;
  while ($row = mysql_fetch_array($result)) {
    $unique_hits = $unique_hits + $row['count'];
  }

  //$uniquepage = "uniquehitcounter.txt";
  //$unique_hits = file($uniquepage);
  //$unique_hits[0] = $unique_hits[0] + 0;
  print "<b>This application has been accessed ".$unique_hits." times</b><br />";
}

function process_admin_commands() {
  if(isset($_REQUEST["cmd"])) {
    switch($_REQUEST["cmd"]) {
      case "purgeuser": process_admin_purgeuser(); break;
      case "purgeuser4real": process_admin_purgeuser4real(); break;
      case "scrubuser": process_admin_scrubuser(); break;
      case "scrubuser4real": process_admin_scrubuser4real(); break;
      default: break;
    }
  }
}

function process_admin_purgeuser() {
  $usertopurge = $_REQUEST["userid"];
  echo "<br />";
  echo "<center>";
  echo "  <font color='red'>";
  echo "    <b>WARNING: You are about to purge the user with ID:".$usertopurge." from ALL DATABASES!!</b><br/>";
  echo "  </font>";
  echo "  <b>This will delete everything the user has (profile, trips, etc.).";
  echo "  Are you <i>sure</i> you want to do this?</b><br/>";
  echo "  <form action='admin.php' method='POST'>";
  echo "    <input type='hidden' name='cmd' value='purgeuser4real' />";
  echo "    <input type='hidden' name='userid' value='".$usertopurge."' />";
  echo "    <input type='submit' value='I understand the consequences. Obliterate this poor user.' />";
  echo "  </form>";
  echo "  <br />";
  echo "</center>";
}

function process_admin_purgeuser4real() {
  $usertopurge = $_REQUEST["userid"];
  $resultcode = db_purge_user_by_id($usertopurge);
  echo "<br />";
  echo "<center>";
  if($resultcode == 0) {
    echo "  <b>Warning: User ID:".$usertopurge." could not be found in the databases.</b><br/>";
  } else if($resultcode > 1) {
    echo "  <b>Warning: Multiple results for ID:".$usertopurge.". Did not delete anything. Please check the DB.</b><br/>";
  } else if($resultcode == 1) {
    echo "  <b>User ID:".$usertopurge." has been successfully purged.</b><br/>";
  } else {
    echo "  <b>CATASTROPHIC FAILURE: Negative number of users. Something really horrible just happened.</b><br/>";
  }
  echo "  <br />";
  echo "</center>";
}

function process_admin_scrubuser() {
  $usertopurge = $_REQUEST["userid"];
  echo "<br />";
  echo "<center>";
  echo "  <b>You are scrubbing the user ID:".$usertopurge.".<br/>";
  echo "  This will erase their profile information only. Are you sure?</b><br/>";
  echo "  <form action='admin.php' method='POST'>";
  echo "    <input type='hidden' name='cmd' value='scrubuser4real' />";
  echo "    <input type='hidden' name='userid' value='".$usertopurge."' />";
  echo "    <input type='submit' value=\"I'm sure. Scrub this user.\" />";
  echo "  </form>";
  echo "  <br />";
  echo "</center>";
}

function process_admin_scrubuser4real() {
  $usertopurge = $_REQUEST["userid"];
  $resultcode = db_scrub_user_by_id($usertopurge);
  echo "<br />";
  echo "<center>";
  if($resultcode == 0) {
    echo "  <b>Warning: User ID:".$usertopurge." could not be found in the databases.</b><br/>";
  } else if($resultcode > 1) {
    echo "  <b>Warning: Multiple results for ID:".$usertopurge.". Did not delete anything. Please check the DB.</b><br/>";
  } else if($resultcode == 1) {
    echo "  <b>User ID:".$usertopurge." has been successfully scrubbed.</b><br/>";
  } else {
    echo "  <b>CATASTROPHIC FAILURE: Negative number of users. Something really horrible just happened.</b><br/>";
  }
  echo "  <br />";
  echo "</center>";
}

?>
