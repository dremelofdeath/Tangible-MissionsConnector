<?php
// Application: Christian Missions Connector
// File: 'profileV.php' 
//   profile creation- volunteer
// 

/*
$fbid=//pull from facebook;
//require_once 'facebook.php';
*/

include_once 'common.php';

$fb = cmc_startup($appapikey, $appsecret);

$con = arena_connect();

/**
 * Uses the current database connection to generate HTML option tags based on 
 * the result set of an SQL query. The given fields correspond to column names 
 * in the specified result set. Provides more powerful options than 
 * cmc_fetch_opts.
 * 
 * @param $query The SQL query to execute which will retrieve the requisite 
 * data.
 * @param $id_prefix Text that should be prepended to all generated ID fields. 
 * (This is to differentiate the various components of the Document Object 
 * Model, if necessary.)
 * @param $id_field The name of the column containing the ID of the generated 
 * option tag.
 * @param $value_field The name of the column containing the value of the generated 
 * option tag.
 * @param $text_field The name of the column containing the displayed text of 
 * the generated option tag.
 */
function cmc_fetch_opts_ex($query, $id_prefix, $id_field, $value_field, $text_field) {
  if($result = mysql_query($query)) {
    while($row = mysql_fetch_array($result)) {
      echo '<option id="'.$id_prefix.$row[$id_field].'" value="'.$row[$value_field].'">';
      echo $row[$text_field];
      echo '</option>';
    }
  }
}

/**
 * Uses the current database connection to generate HTML option tags based on 
 * the result set of an SQL query. The given fields correspond to column names 
 * in the specified result set.
 * 
 * @param $query The SQL query to execute which will retrieve the requisite 
 * data.
 * @param $id_field The name of the column containing the ID of the generated 
 * option tag.
 * @param $value_field The name of the column containing the value of the generated 
 * option tag.
 * @param $text_field The name of the column containing the displayed text of 
 * the generated option tag.
 */
function cmc_fetch_opts($query, $id_field, $value_field, $text_field) {
    cmc_fetch_opts_ex($query, '', $id_field, $value_field, $text_field);
}

$sql = 'SELECT type, typedesc FROM skilltypes;';
$skilltypes = array();
if($result = mysql_query($sql)) {
  while($row = mysql_fetch_array($result)) {
    $skilltypes[$row['typedesc']] = $row['type'];
  }
}

$is_volunteer = false;
$is_mission = false;
$is_trip = false;

if($_GET['type'] == "volunteer") $is_volunteer = true;
if($_GET['type'] == "mission") $is_mission = true;
if($_GET['type'] == "trip") $is_trip = true;

?>

<br/><br/>

<fb:editor action="profilein.php" labelwidth="150">

<?php

function cmc_about($name_label, $url_label, $about_label) {
  echo '<fb:editor-text name="name" label="'.$name_label.'"/>';
  echo '<fb:editor-text name="url" label="'.$url_label.'"/>';
  echo '<fb:editor-textarea name="about" label="'.$about_label.'"/>';
}

if($is_mission) {
  cmc_about('Agency/Mission Name', 'Agency/Mission Website', 'About My Agency');
} else if($is_trip) {
  cmc_about('Trip Name', 'Organization Website (Optional)', 'Trip Description');
}

/**
 * Generates a complete custom editor that uses the correct verbiage for 
 * differentiating between skills provided and skills needed. Implicitly calls 
 * cmc_fetch_opts(). This function will also automatically handle the generation 
 * of the correct SQL query.
 *
 * @param $db The name of the table containing the data to be contained in the 
 * custom editor.
 * @param $id The name of the column of the result set containing the ID field 
 * of the options. (As $text_field to cmc_fetch_opts().)
 * @param $val The name of the column of the result set containing the value of 
 * the options. (As $value_field to cmc_fetch_opts().)
 * @param $txt The name of the olumn of the result set containing the text to be 
 * displayed for the options. (As $text_field to cmc_fetch_opts().)
 * @param $lbl The label for this custom editor.
 * @param $name The ID/name field for the tag of the custom editor itself.
 * @param $is The switching variable for displaying "Needed". If true, the 
 * function displays nothing; if false, the function displays the word "Needed" 
 * adjacent to the label.
 * @param $type A text field used as the key into the $ts map. Used to filter 
 * the results so that only a certain skill type is displayed.
 * @param $ts The skilltypes map. Probably should always be $skilltypes. This 
 * doesn't really make much sense. (But it's all my fault. -zack)
 */
function cmc_needed_custom($db, $id, $val, $txt, $lbl, $name, $is, $type, $ts) {
  if($is) {
    echo '<fb:editor-custom label="'.$lbl.'" name="'.$name.'">';
  } else {
    echo '<fb:editor-custom label="'.$lbl.' Needed" name="'.$name.'">';
  }
  echo '<select name="'.$name.'[]" id="'.$name.'" multiple="true">';
  $sql = 'SELECT * FROM '.$db.' WHERE type="'.$ts[$type].'";';
  cmc_fetch_opts($sql, $id, $val, $txt);
  echo '</select>';
  echo '</fb:editor-custom>';
}

if($is_mission) {
  $offering = ' Facility Offerings';
  echo '<fb:editor-custom label="Medical'.$offering.'" name="medfacil">';
  echo '<select name="medfacil[]" multiple="true">';
  $type = 'medical offerings';
  $sql = 'SELECT * FROM skills WHERE type="'.$skilltypes[$type].'";';
  cmc_fetch_opts($sql, 'id', 'id', 'skilldesc');
  echo '</select>';
  echo '</fb:editor-custom>';

  echo '<fb:editor-custom label="Non-Medical'.$offering.'" name="nonmedfacil">';
  echo '<select name="nonmedfacil[]" multiple="true">';
  $type = 'non-medical offerings';
  $sql = 'SELECT * FROM skills WHERE type="'.$skilltypes[$type].'";';
  cmc_fetch_opts($sql, 'id', 'id', 'skilldesc');
  echo '</select>';
  echo '</fb:editor-custom>';
}

cmc_needed_custom('skills', 'id', 'id', 'skilldesc', 'Medical Skills',
                  'medskills', $is_volunteer, 'medical', $skilltypes);
cmc_needed_custom('skills', 'id', 'id', 'skilldesc', 'Non-Medical Skills',
                  'otherskills', $is_volunteer, 'non-medical', $skilltypes);
cmc_needed_custom('skills', 'id', 'id', 'skilldesc', 'Spiritual Service',
                  'spiritserv', $is_volunteer, 'spiritual', $skilltypes);

?>

<fb:editor-custom name="relg" label="Religious Affiliation">
<select name="relg" id="relg">
<option value="Secular">Secular</option>
<option value="Christian: Protestant">Christian: Protestant</option>
<option value="Christian: Roman Catholic" >Christian: Roman Catholic</option>
</select>
</fb:editor-custom>

<?php
if($is_trip) {
  echo '<fb:editor-date label="Departure Date" name="depart"/>';
  echo '<fb:editor-date label="Return Date" name="return"/>';
}
?>

<fb:editor-custom label="Duration of Missions" name="dur">
<select name="dur[]" id="dur" multiple="true">
<?php
cmc_fetch_opts('SELECT * FROM durations', 'id', 'id', 'name');
?>
</select>
</fb:editor-custom>

<?php if($is_trip) { ?>
<fb:editor-custom label="Mission Stage" name="stage">
<select name="stage" id="stage">
<option value="planning">Planning Stage</option>
<option value="execution">Execution Stage</option>
</select>
</fb:editor-custom>
<?php } ?>

<fb:editor-text label="Home Zip Code" name="zip" maxlength="5"/>

<fb:editor-custom name="region" label="Regions of Interest">
<select name="region[]" multiple="true">
<?php
cmc_fetch_opts('SELECT * FROM regions', 'id', 'id', 'name');
?>
</select>
</fb:editor-custom>

<fb:editor-custom name="country" label="Countries Served">
<select name="country[]" multiple="true">
<?php
cmc_fetch_opts('select id, longname from countries;', 'id', 'id', 'longname');
?>
</select>
</fb:editor-custom>

<fb:editor-text name="phone" label="Additional Phone Number (Optional)"/>
<fb:editor-text name="email" label="Additional Email Address (Optional)"/>

<fb:dialog id="Code">
<fb:dialog-title>Contact Information</fb:dialog-title>
<fb:dialog-content>Christian Missions Connector pulls uses the contact information from your main Facebook profile so you don't have to worry about keeping your CMC profile up to date. Just keep your Facebook page current, and we'll take care of the rest! If you would like to provide another contact in addition to the one from your profile, just fill in the optional space. If you do not want to show any contact information at all, simply choose the option to hide your contact information.</fb:dialog-content>
<fb:dialog-button type="button" value="Close" close_dialog="1"/>
</fb:dialog>
<a href="" clicktoshowdialog="Code">Why do we pull your contact information from your profile?</a><br/>

<fb:editor-textarea name="misexp" label="My Missions Experience (Optional)"/>

<fb:editor-buttonset>
<fb:editor-button value="Submit" name="submit"/>
</fb:editor-buttonset>

</fb:editor>
