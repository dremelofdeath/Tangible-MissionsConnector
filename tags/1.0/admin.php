<?php
include_once 'common.php';
//include_once 'pagecounter.php';

$fb = cmc_startup($appapikey, $appsecret,0);
$fbid = $fb->require_login($required_permissions = 'publish_stream');

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
}
else {
echo "Welcome to the Christian Missions Connector's Administrative Area <br /> <br />";
numhits();
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

?>
