<?php
// Application: Christian Missions Connector
// File: 'search.php' 
//  searches for users and/or trips based on user criteria
// 
//require_once 'facebook.php';

include_once 'common.php';

$fb = cmc_startup($appapikey, $appsecret,0);
$fbid = $fb->require_login("publish_stream,read_stream");

?>
<br/><br/>

<fb:editor
action="http://apps.facebook.com/missionsconnector/searchresults.php?adv=1" method='get'>

<?php //leaders,volunteers, or trips? 

if (isset($_GET['fill'])) {
	$fill = $_GET['fill'];
	echo '<b> WARNING: The search type is a required parameter: Please choose one of the following:<b/><br />';
	echo 'Active Mission Organizers or Volunteers or Upcoming Mission Trips <br/>';
	if (isset($_GET['ms']))
		$medskillsvalue = $_GET['ms'];
	if (isset($_GET['rg']))
		$religionvalue = $_GET['rg'];
	if (isset($_GET['dur']))
		$durvalue = $_GET['dur'];
	if (isset($_GET['dy']))
		$dy = $_GET['dy'];
	if (isset($_GET['dm']))
		$dm = $_GET['dm'];
	if (isset($_GET['dd']))
		$dd = $_GET['dd'];
	if (isset($_GET['ry']))
		$ry = $_GET['ry'];
	if (isset($_GET['rm']))
		$rm = $_GET['rm'];
	if (isset($_GET['rd']))
		$rd = $_GET['rd'];
	if (isset($_GET['ms']))
		$medskillsvalue = $_GET['ms'];
	if (isset($_GET['nm']))
		$nm = $_GET['nm'];
	if (isset($_GET['ct']))
		$countryvalue = $_GET['ct'];
	if (isset($_GET['region']))
		$regionvalue = $_GET['region'];
	if (isset($_GET['zip']))
		$zipvalue = $_GET['zip'];
	if (isset($_GET['p']))
		$partnervalue = $_GET['p'];
	if (isset($_GET['os']))
		$osvalue = $_GET['os'];
	if (isset($_GET['ss']))
		$ssvalue = $_GET['ss'];
}

?>
<fb:editor-custom name="type" label="I am searching for"><br/>
<label for="type">Active Mission Organizers</label><input type="radio" name="type" id="type" value="1" selected />
<label for="type">Volunteers</label><input type="radio" name="type" id="type" value="2" selected />
<label for="type">Upcoming Mission Trips</label><input type="radio" name="type" id="type" value="3" selected />
</fb:editor-custom>


<?php //skills 
?>
<fb:editor-custom label="Medical Skills" name="medskills">
<select name="medskills" id="medskills" multiple="true">
<?php
if (empty($medskillsvalue)) {
echo '<option  value="Any">Any</option>';
echo '<option  value="Advanced Practice Nursing">Advanced Practice Nursing</option>';
echo '<option  value="Dental Professional">Dental Professional </option>';
echo '<option  value="Medical Educator">Medical Educator</option>';
echo '<option  value="Mental Health Professional">Mental Health Professional</option>';
echo '<option  value="Nurse">Nurse</option>';
echo '<option  value="Optometrist or Opthalmologist">Optometrist or Opthalmologist</option>';
echo '<option  value="Pharmacist">Pharmacist</option>';
echo '<option  value="Physician" >Physician</option>';
echo '<option  value="Physician Assistant">Physician Assistant</option>';
echo '<option  value="Physical or Occupational Therapist">Physical or Occupational Therapist</option>';
echo '<option  value="Public Health/Community Development Worker">Public Health/Community Development Worker</option>';
echo '<option  value="Speech Therapist" >Speech Therapist</option>';
echo '<option  value="Other">Other</option>';
}
else {
if (!strcmp($medskillsvalue,"Any"))
echo '<option selected="selected"  value="Any">Any</option>';
else
echo '<option  value="Any">Any</option>';

if (!strcmp($medskillsvalue,"Advanced Practice Nursing"))
echo '<option selected="selected" value="Advanced Practice Nursing">Advanced Practice Nursing</option>';
else
echo '<option  value="Advanced Practice Nursing">Advanced Practice Nursing</option>';

if (!strcmp($medskillsvalue,"Dental Professional"))
echo '<option  selected="selected" value="Dental Professional">Dental Professional </option>';
else
echo '<option  value="Dental Professional">Dental Professional </option>';

if (!strcmp($medskillsvalue,"Medical Educator"))
echo '<option selected="selected" value="Medical Educator">Medical Educator</option>';
else
echo '<option  value="Medical Educator">Medical Educator</option>';

if (!strcmp($medskillsvalue,"Mental Health Professional"))
echo '<option selected="selected" value="Mental Health Professional">Mental Health Professional</option>';
else
echo '<option  value="Mental Health Professional">Mental Health Professional</option>';

if (!strcmp($medskillsvalue,"Nurse"))
echo '<option selected="selected" value="Nurse">Nurse</option>';
else
echo '<option  value="Nurse">Nurse</option>';

if (!strcmp($medskillsvalue,"Optometrist or Opthamologist"))
echo '<option selected="selected" value="Optometrist or Opthamologist">Optometrist or Opthamologist</option>';
else
echo '<option  value="Optometrist or Opthamologist">Optometrist or Opthamologist</option>';

if (!strcmp($medskillsvalue,"Pharmacist"))
echo '<option selected="selected" value="Pharmacist">Pharmacist</option>';
else
echo '<option  value="Pharmacist">Pharmacist</option>';

if (!strcmp($medskillsvalue,"Physician"))
echo '<option selected="selected" value="Physician" >Physician</option>';
else
echo '<option  value="Physician" >Physician</option>';

if (!strcmp($medskillsvalue,"Physician Assistant"))
echo '<option selected="selected" value="Physician Assistant">Physician Assistant</option>';
else
echo '<option  value="Physician Assistant">Physician Assistant</option>';

if (!strcmp($medskillsvalue,"Physical or Occupational Therapist"))
echo '<option select="selected" value="Physical or Occupational Therapist">Physical or Occupational Therapist</option>';
else
echo '<option  value="Physical or Occupational Therapist">Physical or Occupational Therapist</option>';

if (!strcmp($medskillsvalue,"Public Health/Community Development Worker"))
echo '<option selected="selected" value="Public Health/Community Development Worker">Public Health/Community Development Worker</option>';
else
echo '<option  value="Public Health/Community Development Worker">Public Health/Community Development Worker</option>';

if (!strcmp($medskillsvalue,"Speech Therapist"))
echo '<option selected="selected" value="Speech Therapist" >Speech Therapist</option>';
else
echo '<option  value="Speech Therapist" >Speech Therapist</option>';

if (!strcmp($medskillsvalue,"Other"))
echo '<option selected="selected" value="Other">Other</option>';
else
echo '<option  value="Other">Other</option>';

}
?>
</select>
</fb:editor-custom>

<fb:editor-custom label="Non-Medical Skills" name="otherskills">
<select name="otherskills" id="otherskills" multiple="true">;
<?php
if (empty($osvalue)) {
echo '<option name="otherskills" value="Any">Any</option>';
echo '<option name="otherskills" value="General Help/Labor">General Help/Labor</option>';
echo '<option name="otherskills" value="Team Leader/Primary Organizer">Team Leader/Primary Organizer</option>';
echo '<option name="otherskills" value="Accounting and/or Business Management">Accounting and/or Business Management</option>';
echo '<option name="otherskills" value="Skilled Construction and/or Maintenance">Skilled Construction and/or Maintenance</option>';
echo '<option name="otherskills" value="Computer Science/Other Technical">Computer Science/Other Technical</option>';
echo '<option name="otherskills" value="Agriculture and/or Animal Husbandry">Agriculture and/or Animal Husbandry</option>';
echo '<option name="otherskills" value="Mechanic">Mechanic</option>';
echo '<option name="otherskills" value="Office/Secretarial">Office/Secretarial</option>';
echo '<option name="otherskills" value="Teaching">Teaching</option>';
echo '<option name="otherskills" value="Veterinary">Veterinary</option>';
echo '<option name="otherskills" value="Water Supply Improvement">Water Supply Improvement</option>';
echo '<option name="otherskills" value="Writing and/or Translating">Writing and/or Translating</option>';
echo '<option name="otherskills" value="Engineering">Engineering</option>';
}
else {
if (!strcmp($osvalue,"Any"))
echo '<option selected="selected" name="otherskills" value="Any">Any</option>';
else
echo '<option name="otherskills" value="Any">Any</option>';

if (!strcmp($osvalue,"General Help/Labor"))
echo '<option selected="selected" name="otherskills" value="General Help/Labor">General Help/Labor</option>';
else
echo '<option name="otherskills" value="General Help/Labor">General Help/Labor</option>';

if (!strcmp($osvalue,"Team Leader/Primary Organizer"))
echo '<option selected="selected" name="otherskills" value="Team Leader/Primary Organizer">Team Leader/Primary Organizer</option>';
else
echo '<option name="otherskills" value="Team Leader/Primary Organizer">Team Leader/Primary Organizer</option>';

if (!strcmp($osvalue,"Accounting and/or Business Management"))
echo '<option selected="selected" name="otherskills" value="Accounting and/or Business Management">Accounting and/or Business Management</option>';
else
echo '<option name="otherskills" value="Accounting and/or Business Management">Accounting and/or Business Management</option>';

if (!strcmp($osvalue,"Skilled Construction and/or Maintenance"))
echo '<option selected="selected" name="otherskills" value="Skilled Construction and/or Maintenance">Skilled Construction and/or Maintenance</option>';
else
echo '<option name="otherskills" value="Skilled Construction and/or Maintenance">Skilled Construction and/or Maintenance</option>';

if (!strcmp($osvalue,"Computer Science/Other Technical"))
echo '<option selected="selected" name="otherskills" value="Computer Science/Other Technical">Computer Science/Other Technical</option>';
else
echo '<option name="otherskills" value="Computer Science/Other Technical">Computer Science/Other Technical</option>';

if (!strcmp($osvalue,"Agriculture and/or Animal Husbandry"))
echo '<option selected="selected" name="otherskills" value="Agriculture and/or Animal Husbandry">Agriculture and/or Animal Husbandry</option>';
else
echo '<option name="otherskills" value="Agriculture and/or Animal Husbandry">Agriculture and/or Animal Husbandry</option>';

if (!strcmp($osvalue,"Mechanic"))
echo '<option selected="selected" name="otherskills" value="Mechanic">Mechanic</option>';
else
echo '<option name="otherskills" value="Mechanic">Mechanic</option>';

if (!strcmp($osvalue,"Office/Secretarial"))
echo '<option selected="selected" name="otherskills" value="Office/Secretarial">Office/Secretarial</option>';
else
echo '<option name="otherskills" value="Office/Secretarial">Office/Secretarial</option>';

if (!strcmp($osvalue,"Teaching"))
echo '<option selected="selected" name="otherskills" value="Teaching">Teaching</option>';
else
echo '<option name="otherskills" value="Teaching">Teaching</option>';

if (!strcmp($osvalue,"Veterinary"))
echo '<option selected="selected" name="otherskills" value="Veterinary">Veterinary</option>';
else
echo '<option name="otherskills" value="Veterinary">Veterinary</option>';

if (!strcmp($osvalue,"Water Supply Improvement"))
echo '<option selected="selected" name="otherskills" value="Water Supply Improvement">Water Supply Improvement</option>';
else
echo '<option name="otherskills" value="Water Supply Improvement">Water Supply Improvement</option>';

if (!strcmp($osvalue,"Writing and/or Translating"))
echo '<option selected="selected" name="otherskills" value="Writing and/or Translating">Writing and/or Translating</option>';
else
echo '<option name="otherskills" value="Writing and/or Translating">Writing and/or Translating</option>';

if (!strcmp($osvalue,"Engineering"))
echo '<option selected="selected" name="otherskills" value="Engineering">Engineering</option>';
else
echo '<option name="otherskills" value="Engineering">Engineering</option>';
}
?>
</select>
</fb:editor-custom>


<fb:editor-custom name="spiritserv" label="Spiritual Service" value="Spiritual Service">
<select name="spiritserv" id="spiritserv" multiple="true">
<?php
if (empty($ssvalue)) {
echo '<option name="spiritserv" value="Any">Any</option>';
echo '<option name="spiritserv" value="Team Spiritual Leader">Team Spiritual Leader</option>';
echo '<option name="spiritserv" value="Individual Outreach (Prayer or Counseling)">Individual Outreach (Prayer or Counseling)</option>';
echo '<option name="spiritserv" value="Evangelism">Evangelism</option>';
echo '<option name="spiritserv" value="Worship Team">Worship Team</option>';
echo '<option name="spiritserv" value="Public Speaking">Public Speaking</option>';
}
else {
if (!strcmp($ssvalue,"Any"))
echo '<option selected="selected" name="spiritserv" value="Any">Any</option>';
else
echo'<option name="spiritserv" value="Any">Any</option>';

if (!strcmp($ssvalue,"Team Spiritual Leader"))
echo '<option selected="selected" name="spiritserv" value="Team Spiritual Leader">Team Spiritual Leader</option>';
else
echo '<option name="spiritserv" value="Team Spiritual Leader">Team Spiritual Leader</option>';

if (!strcmp($ssvalue,"Individual Outreach (Prayer or Counseling)"))
echo '<option selected="selected" name="spiritserv" value="Individual Outreach (Prayer or Counseling)">Individual Outreach (Prayer or Counseling)</option>';
else
echo '<option name="spiritserv" value="Individual Outreach (Prayer or Counseling)">Individual Outreach (Prayer or Counseling)</option>';

if (!strcmp($ssvalue,"Evangelism"))
echo '<option selected="selected" name="spiritserv" value="Evangelism">Evangelism</option>';
else
echo '<option name="spiritserv" value="Evangelism">Evangelism</option>';

if (!strcmp($ssvalue,"Worship Team"))
echo '<option selected="selected" name="spiritserv" value="Worship Team">Worship Team</option>';
else
echo '<option name="spiritserv" value="Worship Team">Worship Team</option>';

if (!strcmp($ssvalue,"Public Speaking"))
echo '<option selected="selected" name="spiritserv" value="Public Speaking">Public Speaking</option>';
else
echo '<option name="spiritserv" value="Public Speaking">Public Speaking</option>';
}

?>
</select>
</fb:editor-custom>



<?php //affiliation 
?>
<fb:editor-custom name="relg" label="Religious Affiliation">
<select name="relg" >
<?php 
if (empty($religionvalue)) {
echo '<option name="relg" value="Any">Any</option>';
echo '<option name="relg" value="Secular">Secular</option>';
echo '<option name="relg" value="Christian: Protestant">Christian: Protestant</option>';
echo '<option name="relg" value="Christian: Roman Catholic" >Christian: Roman Catholic</option>';
}
else {
if (!strcmp($religionvalue,"Any"))
echo '<option selected="selected" name="relg" value="Any">Any</option>';
else
echo '<option name="relg" value="Any">Any</option>';

if (!strcmp($religionvalue,"Secular"))
echo '<option selected="selected" name="relg" value="Secular">Secular</option>';
else
echo '<option name="relg" value="Secular">Secular</option>';

if (!strcmp($religionvalue,"Christian: Protestant"))
echo '<option selected="selected" name="relg" value="Christian: Protestant">Christian: Protestant</option>';
else
echo '<option name="relg" value="Christian: Protestant">Christian: Protestant</option>';

if (!strcmp($religionvalue,"Christian: Roman Catholic"))
echo '<option selected="selected" name="relg" value="Christian: Roman Catholic" >Christian: Roman Catholic</option>';
else
echo '<option name="relg" value="Christian: Roman Catholic" >Christian: Roman Catholic</option>';

}
?>

</select>
</fb:editor-custom>


<fb:editor-custom name="partner" label="Is a CMC Partner" value="true">
<?php
if (empty($partnervalue)) {
echo "<input type=\"radio\" name=\"partner\" value=\"true\"/>Is a CMC Partner<br/>";
echo "<input type=\"radio\" name=\"partner\" value=\"false\"/>No/Doesn't Matter<br/>";
}
else {
if ($partnervalue==1) {
echo "<input checked=\"checked\" type=\"radio\" name=\"partner\" value=\"true\"/>Is a CMC Partner<br/>";
echo "<input type=\"radio\" name=\"partner\" value=\"false\"/>No/Doesn't Matter<br/>";
}
else if ($partnervalue==2) {
echo "<input type=\"radio\" name=\"partner\" value=\"true\"/>Is a CMC Partner<br/>";
echo "<input checked=\"checked\" type=\"radio\" name=\"partner\" value=\"false\"/>No/Doesn't Matter<br/>";
}
}
?>

</fb:editor-custom>




<?php //dates and duration 

function month_display($val,$mval) {
$month_names = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
for ($i=1;$i<=12;$i++) {
if ($val==1) {
if (empty($mval))
echo '<option value="'.$i.'">'.$month_names[$i-1].'</option>';
else {
if ($mval==$i)
echo '<option selected="selected" value="'.$i.'">'.$month_names[$i-1].'</option>';
else
echo '<option value="'.$i.'">'.$month_names[$i-1].'</option>';
}
}
if ($val==2) {
if ((!empty($mval)) && ($mval==$i))
echo '<option selected="selected" value="'.$i.'">'.$month_names[$i-1].'</option>';
else
echo '<option value="'.$i.'">'.$month_names[$i-1].'</option>';
}

}
}

function day_display($val,$dval) {
for ($i=1;$i<=31;$i++) {
if ($val==1) {
if ((!empty($dval)) && ($dval==$i))
echo '<option selected="selected" value="'.$i.'">'.$i.'</option>';
else
echo '<option value="'.$i.'">'.$i.'</option>';

}
if ($val==2) {
if ((!empty($dval)) && ($dval==$i))
echo '<option selected="selected" value="'.$i.'">'.$i.'</option>';
else
echo '<option value="'.$i.'">'.$i.'</option>';

}
}
}

function year_display($year,$val,$yval) {

for ($i=0;$i<50;$i++) {

	if ($val==1) {
	if (!empty($yval) && ($yval==$year+$i))
        echo '<option selected="selected" value="'.strval($year+$i).'">'.strval($year+$i).'</option>';
	else
        echo '<option value="'.strval($year+$i).'">'.strval($year+$i).'</option>';
	}
	else if ($val==2) {
	if (!empty($yval) && ($yval==$year+$i))
        echo '<option selected="selected" value="'.strval($year+$i).'">'.strval($year+$i).'</option>';
	else
        echo '<option value="'.strval($year+$i).'">'.strval($year+$i).'</option>';
	}
}

}

  $year = (int)date("Y");
  $year = $year + 0;

  //<fb:editor-date label="Departure Date" name="depart"/>
  echo '<fb:editor-custom name="Departure" label="Departure">';
  echo '<select name="DepartYear" id="DepartYear">';
  year_display($year,1,$dy);
  echo '</select>';
  echo '<select name="DepartMonth" id="DepartMonth">';
  echo 'CALLING MONTH DISPLAY <br />';
  month_display(1,$dm);
  echo '</select>';
  echo '<select name="DepartDay" id="DepartDay">';
  day_display(1,$dd);
  echo '</select>';
  echo '</fb:editor-custom>';

  echo '<fb:editor-custom name="Return" label="Return">';
  echo '<select name="ReturnYear" id="ReturnYear">';
  year_display($year,2,$ry);
  echo '</select>';
  echo '<select name="ReturnMonth" id="ReturnMonth">';
  month_display(2,$rm);
  echo '</select>';
  echo '<select name="ReturnDay" id="ReturnDay">';
  day_display(2,$rd);
  echo '</select>';
  echo '</fb:editor-custom>';
  //<fb:editor-date label="Return Date" name="return" />

?>

<fb:editor-custom label="Duration" name="dur">
<select name="dur" >

<?php
if (empty($durvalue)) {
echo '<option name="dur" value="Any" >Any</option>';
echo '<option name="dur" value="Short Term: 1-2 Weeks" >Short Term: 1-2 Weeks</option>';
echo '<option name="dur" value="Medium Term: 1 Month-2 Years" >Medium Term: 1 Month-2 Years</option>';
echo '<option name="dur" value="Long Term: 2+ Years">Long Term: 2+ Years</option>';
}
else {
if (!strcmp($durvalue,"Any"))
echo '<option selected="selected" name="dur" value="Any" >Any</option>';
else
echo '<option name="dur" value="Any" >Any</option>';

if (!strcmp($durvalue,"Short Term: 1-2 Weeks"))
echo '<option selected="selected" name="dur" value="Short Term: 1-2 Weeks" >Short Term: 1-2 Weeks</option>';
else
echo '<option name="dur" value="Short Term: 1-2 Weeks" >Short Term: 1-2 Weeks</option>';

if (!strcmp($durvalue,"Medium Term: 1 Month-2 Years"))
echo '<option selected="selected" name="dur" value="Medium Term: 1 Month-2 Years" >Medium Term: 1 Month-2 Years</option>';
else
echo '<option name="dur" value="Medium Term: 1 Month-2 Years" >Medium Term: 1 Month-2 Years</option>';

if (!strcmp($durvalue,"Long Term: 2+ Years"))
echo '<option selected="selected" name="dur" value="Long Term: 2+ Years">Long Term: 2+ Years</option>';
else
echo '<option name="dur" value="Long Term: 2+ Years">Long Term: 2+ Years</option>';

}
?>
</select>
</fb:editor-custom>

<?php //location 
if (!empty($zipvalue))
echo '<fb:editor-text label="Home Zip Code" value="'.$zipvalue.'" name="zip"/>';
else {
?>

<fb:editor-text label="Home Zip Code" name="zip"/>
<?php //must be between 00000-99999 
}
?>


<fb:editor-custom name="region" label="Regions Served">
<select name="region" multiple="true">

<?php
if (empty($regionvalue)) {
echo '<option name="region" value="Any">Any</option>';
echo '<option name="region" value="Africa" >Africa</option>';
echo '<option name="region" value="Asia and Oceana" >Asia and Oceana</option>';
echo '<option name="region" value="Europe and Russia" >Europe and Russia</option>';
echo '<option name="region" value="Latin America" >Latin America</option>';
echo '<option name="region" value="Middle East" >Middle East</option>';
echo '<option name="region" value="North America" >North America</option>';
echo '<option name="region" value="Caribbean" >Caribbean</option>';
}
else {
if (!strcmp($regionvalue,"Any"))
echo '<option selected="selected" name="region" value="Any">Any</option>';
else
echo '<option name="region" value="Any">Any</option>';

if (!strcmp($regionvalue,"Africa"))
echo '<option selected="selected" name="region" value="Africa" >Africa</option>';
else
echo '<option name="region" value="Africa" >Africa</option>';

if (!strcmp($regionvalue,"Asia and Oceana"))
echo '<option selected="selected" name="region" value="Asia and Oceana" >Asia and Oceana</option>';
else
echo '<option name="region" value="Asia and Oceana" >Asia and Oceana</option>';

if (!strcmp($regionvalue,"Europe and Russia"))
echo '<option selected="selected" name="region" value="Europe and Russia" >Europe and Russia</option>';
else
echo '<option name="region" value="Europe and Russia" >Europe and Russia</option>';

if (!strcmp($regionvalue,"Latin America and the Caribbean"))
echo '<option selected="selected" name="region" value="Latin America and the Caribbean" >Latin America and the Caribbean</option>';
else
echo '<option name="region" value="Latin America and the Caribbean" >Latin America and the Caribbean</option>';

if (!strcmp($regionvalue,"Middle East"))
echo '<option selected="selected" name="region" value="Middle East" >Middle East</option>';
else
echo '<option name="region" value="Middle East" >Middle East</option>';

if (!strcmp($regionvalue,"North America"))
echo '<option selected="selected" name="region" value="North America" >North America</option>';
else
echo '<option name="region" value="North America" >North America</option>';


}
?>
</select>
</fb:editor-custom>




<?php/*
$con = mysql_connect(localhost,"arena", "***arena!password!getmoney!getpaid***");
	if(!$con)
	{
		die('Could not connect: ' .  mysql_error());
	}
	
	mysql_select_db("missionsconnector", $con);
$fbid=$user_id;
	$sql = "select longname from countries";
if($result = mysql_query($sql)){
	while($row= mysql_fetch_array($result)){
		$country = $row['country'];
	}}*/
?>


<fb:editor-custom name="country" label="Countries Served">
<select name="country" multiple="true">
<option value="Any">Any</option>

<?php
$con = mysql_connect(localhost,"arena", "***arena!password!getmoney!getpaid***");
//$con = mysql_connect(localhost,"poornima", "MYdata@1");
	if(!$con)
	{
		die('Could not connect: ' .  mysql_error());
	}
	
	mysql_select_db("missionsconnector", $con);
	$sql = "select longname from countries";
if($result = mysql_query($sql)){
	while($row= mysql_fetch_array($result)){
	if (!strcmp($countryvalue,$row['longname']))
echo "<option selected='selected' name='country' id='country' value='".$row['longname']."'>".$row['longname']."</option>";
	else
echo "<option name='country' id='country' value='".$row['longname']."'>".$row['longname']."</option>";
}}
?>
</select>
</fb:editor-custom>

<?php
if (!empty($nm))
echo '<fb:editor-text name="name" value="'.$nm.'" label="Individual or Organization Name"/>';
else {
?>

<fb:editor-text name="name" label="Individual or Organization Name"/>
<?php
}
?>
<fb:editor-buttonset>
<fb:editor-button value="Submit" name="submit"/>
</fb:editor-buttonset>

</fb:editor>
