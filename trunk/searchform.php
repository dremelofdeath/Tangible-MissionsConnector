<?php
// Application: Christian Missions Connector
// File: 'search.php' 
//  searches for users and/or trips based on user criteria
// 
//require_once 'facebook.php';

include_once 'common.php';

$fb = cmc_startup($appapikey, $appsecret);
$fbid = $fb->require_login();

?>
<br/><br/>

<fb:editor
action="http://apps.facebook.com/missionsconnector/searchresults.php" method='get'>

<?php //leaders,volunteers, or trips? 
?>
<fb:editor-custom name="type" label="I am searching for"><br/>
<label for="type">Active Mission Organizers</label><input type="checkbox" name="type" id="type" value="Active Mission Organizers" selected />
<label for="type">Volunteers</label><input type="checkbox" name="type" id="type" value="Volunteers" selected />
<label for="type">Upcoming Mission Trips</label><input type="checkbox" name="type" id="type" value="Upcoming Mission Trips" selected />
</fb:editor-custom>


<?php //skills 
?>
<fb:editor-custom label="Medical Skills" name="medskills">
<select name="medskills" id="medskills" multiple="true">
<option  value="Any">Any</option>
<option  value="Advanced Practice Nursing">Advanced Practice Nursing</option>
<option  value="Dental Professional">Dental Professional</option>
<option  value="Medical Educator">Medical Educator</option>
<option  value="Mental Health Professional">Mental Health Professional</option>
<option  value="Nurse">Nurse</option>
<option  value="Optometrist or Opthamologist">Optometrist or Opthamologist</option>
<option  value="Pharmacist">Pharmacist</option>
<option  value="Physician" >Physician</option>
<option  value="Physician Assistant">Physician Assistant</option>
<option  value="Physical or Occupational Therapist">Physical or Occupational Therapist</option>
<option  value="Public Health/Community Development Worker">Public Health/Community Development Worker</option>
<option  value="Speech Therapist" >Speech Therapist</option>
<option  value="Other">Other</option>
</select>
</fb:editor-custom>

<fb:editor-custom label="Non-Medical Skills" name="otherskills">
<select name="otherskills" id="otherskills" multiple="true">
<option name="otherskills" value="Any">Any</option>
<option name="otherskills" value="General Help/Labor">General Help/Labor</option>
<option name="otherskills" value="Team Leader/Primary Organizer">Team Leader/Primary Organizer</option>
<option name="otherskills" value="Business Management Expertise">Business Management Expertise</option>
<option name="otherskills" value="Skilled Construction">Skilled Construction</option>
<option name="otherskills" value="Computer Science">Computer Science</option>
<option name="otherskills" value="Legal Expertise">Legal Expertise</option>
</select>
</fb:editor-custom>


<fb:editor-custom name="spiritserv" label="Spiritual Service" value="Spiritual Service">
<select name="spiritserv" id="spiritserv" multiple="true">
<option name="spiritserv" value="Any">Any</option>
<option name="spiritserv" value="Team Spiritual Leader/Pastor">Team Spiritual Leader/Pastor</option>
<option name="spiritserv" value="Spiritual Outreach Director">Spiritual Outreach Director</option>
<option name="spiritserv" value="Evangelism Only">Evangelism Only</option>
</select>
</fb:editor-custom>



<?php //affiliation 
?>
<fb:editor-custom name="relg" label="Religious Affiliation">
<select name="relg" >
<option name="relg" value="Any">Any</option>
<option name="relg" value="Secular">Secular</option>
<option name="relg" value="Christian: Protestant">Christian: Protestant</option>
<option name="relg" value="Christian: Roman Catholic" >Christian: Roman Catholic</option>
</select>
</fb:editor-custom>


<fb:editor-custom name="partner" label="Is a CMC Partner" value="true">
<input type="radio" name="partner" value="true"/>Is a CMC Partner<br/>
<input type="radio" name="partner" value="false"/>No/Doesn't Matter<br/>
</fb:editor-custom>




<?php //dates and duration 
?>
<fb:editor-date label="Departure Date" name="depart"/>
<fb:editor-date label="Return Date" name="return" />
<fb:editor-custom label="Duration" name="dur">
<select name="dur" >
<option name="dur" value="Any" >Any</option>
<option name="dur" value="Short Term: 1-2 Weeks" >Short Term: 1-2 Weeks</option>
<option name="dur" value="Medium Term: 1 Month-2 Years" >Medium Term: 1 Month-2 Years</option>
<option name="dur" value="Long Term: 2+ Years">Long Term: 2+ Years</option>
</select>
</fb:editor-custom>




<?php //location 
?>
<fb:editor-text label="Home Zip Code (Required)" name="zip"/>
<?php //must be between 00000-99999 
?>


<fb:editor-custom name="region" label="Regions Served">
<select name="region" multiple="true">
<option name="region value="Any">Any</option>
<option name="region" value="Africa" >Africa</option>
<option name="region" value="Asia and Oceana" >Asia and Oceana</option>
<option name="region" value="Europe and Russia" >Europe and Russia</option>
<option name="region" value="Latin America and the Caribbean" >Latin America and the Caribbean</option>
<option name="region" value="Middle East" >Middle East</option>
<option name="region" value="North America" >North America</option>
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
	if(!$con)
	{
		die('Could not connect: ' .  mysql_error());
	}
	
	mysql_select_db("missionsconnector", $con);
	$sql = "select longname from countries";
if($result = mysql_query($sql)){
	while($row= mysql_fetch_array($result)){
echo "<option name='country' id='country' value='".$row['longname']."'>".$row['longname']."</option>";
}}
?>
</select>
</fb:editor-custom>


<fb:editor-text name="name" label="Individual or Organization Name"/>

<fb:editor-button value="Submit" name="submit"/>
</fb:editor>
