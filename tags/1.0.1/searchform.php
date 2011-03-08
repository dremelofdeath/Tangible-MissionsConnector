<?php
// Application: Christian Missions Connector
// File: 'search.php' 
//  searches for users and/or trips based on user criteria
// 
//require_once 'facebook.php';

include_once 'common.php';

$fb = cmc_startup($appapikey, $appsecret,0);
$fbid = $fb->require_login("publish_stream,read_stream");

echo "<br /><br />";

echo "To use the search, just type in anything you're interested in about missions and press 'submit' to see what other people, missionaries, and mission trips have a profile with that sort of information in it. Your search term can be anything from a country of interest to a medical profession.<br /><br />"; 
echo "If you want more specific results, we strongly recommend you use our <b>advanced search</b> by clicking <a href='advancedsearch.php'> <b>here</b></a>. To view everyone interested in missions near a particular <b>zip code</b>, just click <a href='searchbyzip.php'> <b>here</b></a>. ";

?>
<br/><br/>

<fb:editor
action="http://apps.facebook.com/missionsconnector/searchresults.php?adv=0" method='get'>

<br/><br/>
<fb:editor-text name="keys" label="Enter any keywords"/>

<fb:editor-buttonset>
<fb:editor-button value="Submit" name="submit"/>
</fb:editor-buttonset>
</fb:editor>


<?php 
//echo "1) ";
//echo "<a href='basicsearch.php'>Basic Search</a>";

//echo "<br> <br> 1) ";
//echo "<a href='advancedsearch.php'>Advanced Search</a>";
//echo "<br> <br> 2) ";
//echo "<a href='searchbyzip.php'>Search by ZipCode</a>";
//echo "<br> <br> ";

?>
