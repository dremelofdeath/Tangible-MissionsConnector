<?php
// Application: Christian Missions Connector
// File: 'makeProfileT.php' 
//   Profile creation- trips
// 
//require_once 'facebook.php';


// Application: Christian Missions Connector
// File: 'profileV.php' 
//   profile creation- volunteer
// 




//$fbid=//pull from facebook;

include_once 'common.php';

echo_dashboard();
echo_tabbar();

?>
<br/><br/>

<fb:editor action="http://apps.facebook.com/missionsconnector/newtrip.php">

<fb:editor-text name="tripname" label="Trip Name"/>
<fb:editor-text name="desc" label="Trip Description"/>
<fb:editor-text name="orgweb" label="Organization Website (Optional)"/>

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


<fb:editor-custom name="spiritserv" value="Spiritual Service Needed">
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





<fb:editor-date label="Departure Date" name="depart"/>
<fb:editor-date label="Return Date" name="return" />
<fb:editor-custom label="Duration" name="dur">
<select name="dur" id="dur" multiple="true">
<option name="dur" value="Any">Any</option>
<option name="dur" value="Short Term: 1-2 Weeks" >Short Term: 1-2 Weeks</option>
<option name="dur" value="Medium Term: 1 Month-2 Years" >Medium Term: 1 Month-2 Years</option>
<option name="dur" value="Long Term: 2+ Years">Long Term: 2+ Years</option>
</select></fb:editor-custom>
<fb:editor-custom label="Planning Stage">
<input type="radio" name="exec" value="false"/>Planning Stage
<input type="radio" name="exec" value="true"/>Execution Stage
</fb:editor-custom>


<fb:editor-text label="Home Zip Code" name="zip"/>


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







<fb:editor-custom name="sharecontact" label="Contact Information Settings">
<input type="radio" name="sharecontact" value="false"/>I do not want to display my contact information as displayed on my Facebook profile.
<input type="radio" name="sharecontact" value="true"/>Display my contact information as displayed on my Facebook profile. Also display these additional methods of contacting me (optional):
<fb:editor-text name="phone" label="Phone Number (Optional)"/>
<fb:editor-text name="email" label="Email Address (Optional)"/>



<fb:editor-buttonset>
<fb:editor-button value="Submit" name="submit"/>
</fb:editor-buttonset>

</fb:editor>


