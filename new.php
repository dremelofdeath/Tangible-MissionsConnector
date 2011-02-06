<?php
// Application: Christian Missions Connector
// File: 'about.php' 
//  provides background information on application and tangible llc, includes user warnings
// 
//require_once 'facebook.php';

include_once 'common.php';


$fb = cmc_startup($appapikey, $appsecret,0);

?>
<br/><br/>




<?php
if (isset($_GET['error'])) {
	$myerror = $_GET['error'];
	if ($_GET['error'] == 1)
		echo '<b>WARNING: User must have a profile before creating a trip <b/><br/><br />';		
}
?>

<h1>Before creating a profile, please consult our help page if you have any concerns about how we store and use your information. Christian Missions Connector will not sell your information to spam lists.</h1>
<br/><br/>
<h1>
I am:<br/><br/>

<a href="makeprofile.php?type=volunteer">A volunteer interested in supporting existing missions</a><br/><br/>
<a href="makeprofile.php?type=mission">An individual or group currently leading a mission</a><br/><br/>

<?php 

if (!isset($_GET['error'])) {

?>

<a href="makeprofile.php?type=trip">Creating a new trip</a><br /><br />
<a href="tripoptions.php">Interested in Modifying Existing Trips</a><br /><br />

<?php
}
?>

</h1>
