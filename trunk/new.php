<?php
// Application: Christian Missions Connector
// File: 'about.php' 
//  provides background information on application and tangible llc, includes user warnings
// 
//require_once 'facebook.php';

include_once 'common.php';


$fb = cmc_startup($appapikey, $appsecret);

?>
<br/><br/>


<h1>Before creating a profile, please consult our help page if you have any concerns about how we store and use your information. Christian Missions Connector will not sell your information to spam lists.</h1>
<br/><br/>
<h1>
I am:<br/><br/>
<a href="makeprofile.php?type=volunteer">A volunteer interested in supporting existing missions</a><br/><br/>
<a href="makeprofile.php?type=mission">An individual or group currently leading a mission</a><br/><br/>
<a href="makeprofile.php?type=trip">Creating a new trip</a>
</h1>
