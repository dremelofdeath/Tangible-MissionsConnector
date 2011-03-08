<?php
// Application: Christian Missions Connector
// File: 'searchbyzip.php' 
//  searches for users and/or trips based on zip code
// 
//require_once 'facebook.php';

include_once 'common.php';

$fb = cmc_startup($appapikey, $appsecret,0);
$fbid = $fb->require_login("publish_stream,read_stream");
?>

<fb:editor
action="http://apps.facebook.com/missionsconnector/searchresults.php?adv=0" method='get'>

<?php
/*
// get user information
$info = $fb->api_client->users_getInfo($fbid, 'name', 'email', 'current_location');

echo count($info);

for($i=0; $i < count($info); $i++) {
  $record = $info[$i];
  $name = $record['name'];
  $email = $record['email'];
  //$phone = $info[0]['phone'];
  $current_country = $record['current_location']['country'];
  $current_state = $record['current_location']['state'];
  $current_city = $record['current_location']['city'];
}
*/

?>
<br/><br/>


<fb:editor-text name="keys" label="Enter any keywords"/>

<fb:editor-buttonset>
<fb:editor-button value="Submit" name="submit"/>
</fb:editor-buttonset>

</fb:editor>
