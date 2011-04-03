<?php
// Application: Christian Missions Connector
// File: 'searchbyzip.php' 
//  searches for users and/or trips based on zip code
// 
//require_once 'facebook.php';

include_once 'common.php';
ob_start();

$fb = cmc_startup($appapikey, $appsecret,0);
$fbid = get_user_id($fb);
//$fbid = $fb->require_login("publish_stream,read_stream");

if (isset($_GET['error'])) {
	$error = $_GET['error'];
	if ($error==1)
	echo '<br /><br /><b>All fields in this form are required fields, please enter them <b/><br /> <br />';
	else if ($error==2)
	echo '<br /><br /><b>A wrong Value parameter has been entered<b/><br /> <br />';
}

if (isset($_GET['value'])) {
	$value = $_GET['value'];
}
	
// get user information
$info = $fb->api_client->users_getInfo($fbid, "name,email,current_location");

for($i=0; $i < count($info); $i++) {
  $record = $info[$i];
  $name = $record['name'];
  $email = $record['email'];
  //$phone = $info[0]['phone'];
  if ($value==1)
	$current_country = "United States";
  else
    $current_country = $record['current_location']['country'];

  $current_state = $record['current_location']['state'];
  $current_city = $record['current_location']['city'];
}

?>
<br/><br/>

<fb:editor action="http://apps.facebook.com/missionsconnector/searchzipresults.php" method='get'>

<?php //location

if ((strstr($current_country,"United States")) || (strstr($current_country,"USA"))) {

session_start();
$_SESSION['locvalue'] = 1;
// User selection of the zip code
if (isset($_SESSION['zip'])) 
echo '<fb:editor-text value="'.$_SESSION['zip'].'" label="User Zip Code (Required)" name="zip"/>';
else
echo '<fb:editor-text label="User Zip Code (Required)" name="zip"/>';

//<fb:editor-text label="User Zip Code (Required)" name="zip"/>

?>

<?php
// User selection of the search radius
?>
<fb:editor-custom label="Search Radius" name="searchradius">
<select name="searchradius" id="searchradius" multiple="false">

<?php
if (isset($_SESSION['searchradius'])) {
if ($_SESSION['searchradius']==5)
echo '<option  selected="selected" value="5">5 Miles</option>';
else
echo '<option  value="5">5 Miles</option>';

if ($_SESSION['searchradius']==10)
echo '<option  selected="selected" value="10">10 Miles</option>';
else
echo '<option  value="10">10 Miles</option>';

if ($_SESSION['searchradius']==25)
echo '<option  selected="selected" value="25">25 Miles</option>';
else
echo '<option  value="25">25 Miles</option>';

if ($_SESSION['searchradius']==50)
echo '<option  selected="selected" value="50">50 Miles</option>';
else
echo '<option  value="50">50 Miles</option>';

if ($_SESSION['searchradius']==100)
echo '<option  selected="selected" value="100">100 Miles</option>';
else
echo '<option  value="100">100 Miles</option>';

if ($_SESSION['searchradius']==500)
echo '<option  selected="selected" value="500">500 Miles</option>';
else
echo '<option  value="500">500 Miles</option>';
}
else {
echo '<option  value="5">5 Miles</option>';
echo '<option  value="10">10 Miles</option>';
echo '<option  value="25">25 Miles</option>';
echo '<option  value="50">50 Miles</option>';
echo '<option  value="100">100 Miles</option>';
echo '<option  value="500">500 Miles</option>';
}
?>

</select>
</fb:editor-custom>

<?php
}
// This means the user's country is not USA - this means the search criteria should be different
else {

echo '<br /><b> The facebook profile does not list your country as USA. If this is so, then enter the Country, State and City below <b /><br /><br />';
echo "If your country is USA,<a href='http://apps.facebook.com/missionsconnector/searchbyzip.php?value=1'>click here</a><br /><br />";

session_start();
$_SESSION['locvalue'] = 2;

?>

<?php
//<fb:editor action="http://apps.facebook.com/missionsconnector/searchzipresults.php?value=2" method='get'>;


if (isset($_SESSION['lcountry'])) 
echo '<fb:editor-text value="'.$_SESSION['lcountry'].'" label="User Country (Required)" name="country"/>';
else
echo '<fb:editor-text label="User Country (Required)" name="country"/>';

if (isset($_SESSION['lstate'])) 
echo '<fb:editor-text value="'.$_SESSION['lstate'].'" label="User State (Required)" name="state"/>';
else
echo '<fb:editor-text label="User State (Required)" name="state"/>';

if (isset($_SESSION['lcity'])) 
echo '<fb:editor-text value="'.$_SESSION['lcity'].'" label="User City (Required)" name="city"/>';
else
echo '<fb:editor-text label="User City (Required)" name="city"/>';

}

echo '<fb:editor-buttonset>';
echo '<fb:editor-button value="Submit" name="submit"/>';
echo '</fb:editor-buttonset>';

?>

</fb:editor>
