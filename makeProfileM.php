<?php
// Application: Christian Missions Connector
// File: 'profileM.php' 
//   Profile creation- missionary
// 
//require_once 'facebook.php';


// Application: Christian Missions Connector
// File: 'profileV.php' 
//   profile creation- volunteer
// 


//$fbid=//pull from facebook;
//require_once 'facebook.php';

include_once 'common.php';

echo_dashboard();
echo_tabbar();

?>
<br/><br/>

<fb:editor
action="http://apps.facebook.com/missionsconnector/profilein.php">

<fb:editor-text name="agentname" label="Agency/Mission Name "/>
<fb:editor-custom name="authorized" label="I am authorized to represent the above Agency/Mission.">
<input type="checkbox" name="authorized" value="true"/>
<fb:editor-text name="aboutagent" label="About My Agency "/>
<fb:editor-text name="agenturl" label="Agency/Mission Website "/>




<fb:editor-custom label="Medical Facility Offers" name="medfacil">
<select name="medfacil" multiple="true">
<option name="medfacil" value="Missions Hospital Without Surgical Facilities">Missions Hospital Without Surgical Facilities</option>
<option name="medfacil" value="Missions Hospital With Surgical Facilities">Missions Hospital With Surgical Facilities</option>
<option name="medfacil" value="Missions Hospital With Dental Care Facilities">Missions Hospital With Dental Care Facilities</option>
<option name="medfacil" value="Outpatient Medical/Dental/Eye Care Clinic">Outpatient Medical/Dental/Eye Care Clinic</option>
<option name="medfacil" value="Organizing/Sending Short Term Medical/Dental/Eye Care Missions Agency">Organizing/Sending Short Term Medical/Dental/Eye Care Missions Agency</option>
<option name="medfacil" value="Supplying/Enabling Short Term Medical/Dental/Eye Care Mission Agency">Supplying/Enabling Short Term Medical/Dental/Eye Care Mission Agency</option>
<option name="medfacil" value="Community Development Agency">Community Development Agency</option>
<option name="medfacil" value="Emergency Medical Relief Agency">Emergency Medical Relief Agency</option>
<option name="medfacil" value="Medical/Dental/Eye Care Equipment Supplier">Medical/Dental/Eye Care Equipment Supplier</option>
<option name="medfacil" value="Water Purification/Drilling">Water Purification/Drilling</option>
<option name="medfacil" value="Medical/Dental/Eye Care Training/Education Agency">Medical/Dental/Eye Care Training/Education Agency</option>
</select></fb:editor-custom>

<fb:editor-custom label="Non-Medical Facility Offerings" name="nonmedfacil">
<select name="nonmedfacil" multiple="true">
<option name="nonmedfacil" value="Evangelism and Church Planting Ministry">Evangelism and Church Planting Ministry</option>
<option name="nonmedfacil" value="Food Access">Food Access</option>
<option name="nonmedfacil" value="Transportation (In Country)">Transportation (In Country)</option>
<option name="nonmedfacil" value="Translators">Translators</option>
<option name="nonmedfacil" value="Trip Planning/Itinerary Building">Trip Planning/Itinerary Building</option>
<option name="nonmedfacil" value="Crowd Control">Crowd Control</option>
<option name="nonmedfacil" value="Press Relations">Press Relations</option>
<option name="nonmedfacil" value="Housing for the Missions Team">Housing for the Missions Team</option>
<option name="nonmedfacil" value="Help Getting Through Customs">Help Getting Through Customs</option>
<option name="nonmedfacil" value="Building Supplies (Construction)">Building Supplies (Construction)</option>
<option name="nonmedfacil" value="Education in Cross Cultural Ministry">Education in Cross Cultural Ministry</option>
</select></fb:editor-custom>






<fb:editor-custom label="Medical Skills Needed" name="medskills">
<select name="medskills" id="medskills" multiple="true">
<option name="medskills" value="Advanced Practice Nursing">Advanced Practice Nursing</option>
<option name="medskills" value="Dental Professional">Dental Professional</option>
<option name="medskills" value="Medical Educator">Medical Educator</option>
<option name="medskills" value="Mental Health Professional">Mental Health Professional</option>
<option name="medskills" value="Nurse">Nurse</option>
<option name="medskills" value="Optometrist or Opthamologist">Optometrist or Opthamologist</option>
<option name="medskills" value="Pharmacist">Pharmacist</option>
<option name="medskills" value="Physician" >Physician</option>
<option name="medskills" value="Physician Assistant">Physician Assistant</option>
<option name="medskills" value="Physical or Occupational Therapist">Physical or Occupational Therapist</option>
<option name="medskills" value="Public Health/Community Development Worker">Public Health/Community Development Worker</option>
<option name="medskills" value="Speech Therapist" >Speech Therapist</option>
<option name="medskills" value="Other">Other</option>
</select></fb:editor-custom>


<fb:editor-custom label="Non-Medical Skills Needed" name="otherskills">
<select name="otherskills" id="skills" multiple="true">
<option name="otherskills" value="General Help/Labor">General Help/Labor</option>
<option name="otherskills" value="Team Leader/Primary Organizer">Team Leader/Primary Organizer</option>
<option name="otherskills" value="Business Management Expertise">Business Management Expertise</option>
<option name="otherskills" value="Skilled Construction">Skilled Construction</option>
<option name="otherskills" value="Computer Science">Computer Science</option>
<option name="otherskills" value="Legal Expertise">Legal Expertise</option>
</select></fb:editor-custom>


<fb:editor-custom name="spiritserv" label="Spiritual Service Needed">
<select name="spiritserv" id="spiritserv" multiple="true">
<option name="spiritserv" value="Team Spiritual Leader/Pastor">Team Spiritual Leader/Pastor</option>
<option name="spiritserv" value="Spiritual Outreach Director">Spiritual Outreach Director</option>
<option name="spiritserv" value="Evangelism Only">Evangelism Only</option>
</select></fb:editor-custom>




<fb:editor-custom name="relg" label="Religious Affiliation">
<select name="relg" id="relg">
<option name="relg" value="Secular">Secular</option>
<option name="relg" value="Christian: Protestant">Christian: Protestant</option>
<option name="relg" value="Christian: Roman Catholic" >Christian: Roman Catholic</option>
</select></fb:editor-custom>





<fb:editor-custom label="Duration of Missions" name="dur">
<select name="dur" id="dur" multiple="true">
<option name="dur" value="Any">Any</option>
<option name="dur" value="Short Term: 1-2 Weeks" >Short Term: 1-2 Weeks</option>
<option name="dur" value="Medium Term: 1 Month-2 Years" >Medium Term: 1 Month-2 Years</option>
<option name="dur" value="Long Term: 2+ Years">Long Term: 2+ Years</option>
</select></fb:editor-custom>



<fb:editor-text label="Home Zip Code" name="zip"/



<fb:editor-custom name="region" label="Regions of Interest">
<select name="region" multiple="true">
<option name="region" value="Africa" >Africa</option>
<option name="region" value="Asia and Oceana" >Asia and Oceana</option>
<option name="region" value="Europe and Russia" >Europe and Russia</option>
<option name="region" value="Latin America and the Caribbean" >Latin America and the Caribbean</option>
<option name="region" value="Middle East" >Middle East</option>
<option name="region" value="North America" >North America</option>
</select></fb:editor-custom>




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
$fbid=$user_id;
	$sql = "select longname from countries";
if($result = mysql_query($sql)){
	while($row= mysql_fetch_array($result)){
echo "<option name='country' id='country' value='".$row['longname']."'>".$row['longname']."</option>";
}}
?>
</select>
</fb:editor-custom>





<fb:editor-custom name="sharecontact" label="I do not want to display my contact information as displayed on my Facebook profile.">
<input type="radio" name="sharecontact" id="sharecontact" value="noshare"/>
</fb:editor-custom>
<fb:editor-custom name="sharecontact" label="Display my contact information as displayed on my Facebook profile. Also display these additional methods of contacting me (optional):">
<input type="radio" name="sharecontact" id="sharecontact" value="share"/>
</fb:editor-custom>
<fb:editor-text name="phone" label="Additional Phone Number (Optional)"/>
<fb:editor-text name="email" label="Additional Email Address (Optional)"/>

<fb:dialog id="Code">
<fb:dialog-title>Contact Information</fb:dialog-title>
<fb:dialog-content> Christian Missions Connector pulls uses the contact information from your main Facebook profile so you don't have to worry about keeping your CMC profile up to date. Just keep your Facebook page current, and we'll take care of the rest! If you would like to provide another contact in addition to the one from your profile, just fill in the optional space. If you do not want to show any contact information at all, simply choose the option to hide your contact information.</fb:dialog-content>
<fb:dialog-button type="button" value="Okay" close_dialog="1"/>
</fb:dialog>
<a href="" clicktoshowdialog="Code">Why do we pull your contact information from your profile?</a>




<fb:editor-custom name="profabout" label="Display 'About Me' from my Facebook Profile">
<input type="radio" name="profabout" id="profabout" value="getabout"/>
</fb:editor-custom>
<fb:editor-custom name="profabout" label="Do not display 'About Me' from Facebook Profile. Use the information below (Check this button and leave textbox blank to skip this step.)">
<input type="radio" name="profabout" id="profabout" value="dontgetabout"/>
</fb:editor-custom>
<fb:editor-text name="about" label="About Me"/>
<fb:editor-text name="misexp" label="My Missions Experience (Optional)"/>

<fb:editor-button value="Submit" name="submit"/>
</fb:editor>


<?php
/*

$con = mysql_connect(localhost,"arena","***arena!password!getmoney!getpaid***");
	if(!$con)
	{
		die('Could not connect: ' .  mysql_error());
	}
	
	mysql_select_db("missionsconnector", $con);
	$sql = "select";
if($result = mysql_query($sql)){
	while($row= mysql_fetch_array($result)){
		$name = $row['building_name'];
		$x2 = $row['latitude'];
		$y2 = $row['longitude'];
		$hav = haversine($x, $y, $x2, $y2);
		$buildings[$hav]=$name;
	}
	} else {
		echo "SQL Error ".mysql_error()." ";
	}
*/

?>
