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
ob_start();

$fb = cmc_startup($appapikey, $appsecret,0);
$fbid = get_user_id($fb);
//$fbid = $fb->require_login("publish_stream,read_stream");
//$con = arena_connect();

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
function cmc_fetch_opts_ex($query, $id_prefix, $id_field, $value_field, $text_field,$editprofile,$skillsrow) {
  if($result = mysql_query($query)) {
    while($row = mysql_fetch_array($result)) {
      if ($editprofile) {
        $selectthis = 0;
        for ($i=0;$i<count($skillsrow);$i++) {
          if ($skillsrow[$i] == $row[$value_field]) {
            $selectthis = 1;
            continue 1;
          }
        }
        if ($selectthis) {
          echo '<option selected="selected" id="'.$id_prefix.$row[$id_field].'" value="'.$row[$value_field].'">';
          echo $row[$text_field];
          echo '</option>';
        } else {
          echo '<option id="'.$id_prefix.$row[$id_field].'" value="'.$row[$value_field].'">';
          echo $row[$text_field];
          echo '</option>';
        }
      } else {
        echo '<option id="'.$id_prefix.$row[$id_field].'" value="'.$row[$value_field].'">';
        echo $row[$text_field];
        echo '</option>';
      }
    }
  }
}

function cmc_fetch_optsnew($query, $id_field, $value_field, $text_field, $editprofile,$myrow) {
	if ($result = mysql_query($query)) {
    while ($row = mysql_fetch_array($result)) {
      if ($editprofile) {

        if (!strcmp($row[$text_field],$myrow['country'])) {
          echo '<option selected="selected" id="'.$row[$id_field].'" value="'.$row[$value_field].'">';
        } else {
          echo '<option id="'.$row[$id_field].'" value="'.$row[$value_field].'">';
        }

        echo $row[$text_field];
        echo '</option>';

      } else {
        echo '<option id="'.$row[$id_field].'" value="'.$row[$value_field].'">';
        echo $row[$text_field];
        echo '</option>';
      }
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
function cmc_fetch_opts($query, $id_field, $value_field, $text_field,$editprofile,$skillsrow) {
    cmc_fetch_opts_ex($query, '', $id_field, $value_field, $text_field,$editprofile,$skillsrow);
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

if (isset($_GET['type'])) {
  if($_GET['type'] == "volunteer") $is_volunteer = true;
  if($_GET['type'] == "mission") $is_mission = true;
  if($_GET['type'] == "trip") {
    $is_trip = true;
    // check to see if there is a CMC profile for this user
    $sql = 'select * from users where userid="'.$fbid.'"';
    if ($result = mysql_query($sql)) {
      $numrows = mysql_num_rows($result);
      if ($numrows == 0)
        echo "<fb:redirect url='new.php?error=1' />";
    } else {
      echo "SQL Error ".mysql_error()." ";
    }
    //echo 'TRIP='.$is_trip.'<br />';
  }
}

if (isset($_GET['error'])) {
  if ($_GET['error']) {
    echo '<br /><br />';
    if ($_GET['error'] == 1)
      echo '<b>WARNING: Zip Code is a required Field, please re-enter the data with a zip code <b/><br/><br />';
    else if ($_GET['error'] == 2)
      echo '<b>WARNING: Departure Date out of range - please re-enter data <b/><br /> <br />';
    else if ($_GET['error'] == 3) 
      echo '<b>WARNING: Return Date out of range - please re-enter data <b/><br /> <br />';
    else if ($_GET['error'] == 4) 
      echo '<b>WARNING: Return Date cannot be before Departure Date <b/><br /> <br />';
    else if ($_GET['error'] == 5) 
      echo '<b>WARNING: Trip name is a required value <b/><br /> <br />';
    else if ($_GET['error'] == 6) 
      echo '<b>WARNING: You entered an invalid URL for website, perhaps you are missing http:// or https:// at the front of the url.  <b/><br /> <br />';
    else if ($_GET['error'] == 7) 
      echo '<b>WARNING: You entered an invalid Email <b/><br /> <br />';		
    else if ($_GET['error'] == 8) 
      echo '<b>WARNING: You entered an invalid city string <b/><br /> <br />';		
  }
}

// check if this is an update form
if (isset($_GET['update'])) {
  if ($_GET['update']) {
    $update = $_GET['update'];
  }
}

if (isset($_GET['edit'])) {
  if ($_GET['edit']) {
    $editprofile = $_GET['edit'];
    $sql = 'select * from users where userid="'.$fbid.'"';
    $result = mysql_query($sql);
    $editrow = mysql_fetch_array($result,MYSQL_ASSOC);
    $sql2 = 'select * from skillsselected where userid="'.$fbid.'"';
    $result = mysql_query($sql2);
    while ($mrow = mysql_fetch_array($result,MYSQL_ASSOC)) {
      $skillsrow[] = $mrow['id'];
    }
    $sql2 = 'select * from regionsselected where userid="'.$fbid.'"';
    $result = mysql_query($sql2);
    while ($mrow = mysql_fetch_array($result,MYSQL_ASSOC)) {
      $regionsrow[] = $mrow['id'];
    }
    $sql2 = 'select * from usstatesselected where userid="'.$fbid.'"';
    $result = mysql_query($sql2);
    while ($mrow = mysql_fetch_array($result,MYSQL_ASSOC)) {
      $usstatesrow[] = $mrow['id'];
    }
    $sql2 = 'select * from countriesselected where userid="'.$fbid.'"';
    $result = mysql_query($sql2);
    while ($mrow = mysql_fetch_array($result,MYSQL_ASSOC)) {
      $countriesrow[] = $mrow['id'];
    }
    $sql2 = 'select * from durationsselected where userid="'.$fbid.'"';
    $result = mysql_query($sql2);

    while ($mrow = mysql_fetch_array($result,MYSQL_ASSOC)) {
      $durationsrow[] = $mrow['id'];
    }
    $sql = 'select * from trips where id="'.$update.'"';
    $result = mysql_query($sql);
    $triprow = mysql_fetch_array($result,MYSQL_ASSOC);
  }
} else {
  $editprofile = false;
  $triprow = array(); // this is horrible. I'm so sorry, code -zack
}

if ((!$is_trip) && (!$is_volunteer) && (!$is_mission)) {
   echo 'You should choose your profile to be either volunteer or a mission or you should create a trip <br /><br />';
   echo 'Check the link and try again <br /><br />';
   echo"<a href='welcome.php'>Go Back to the Welcome Page</a>";
} else {
  session_start();
  if ($is_trip) {
    $_SESSION["mytype"] = 2;
    echo '<br /><br/><b> Use the form below to create your trip. after creating the trip, be sure to share it with your friends. </b><br /><br />';
    //echo 'Main Trip:'.$_SESSION["mytype"].'<br />';
}
else if ($is_volunteer) {
  $_SESSION["mytype"] = 1;
 //echo 'Main Vol:'.$_SESSION["mytype"].'<br />';
}
else {
  $_SESSION["mytype"] = 0;
 //echo 'Main Mission:'.$_SESSION["mytype"].'<br />';
 }

 if (isset($_GET['update'])) 
 	//$_SESSION['update'] = $update;
	echo '<fb:editor name="mprofile" action="profilein.php?update='.$_GET['update'].'" labelwidth="150">';
else
	echo '<fb:editor name="mprofile" action="profilein.php" labelwidth="150">';



//<br/><br/>
//<fb:editor name="mprofile" action="profilein.php" labelwidth="150">


?>

<?php

function cmc_about($name_label, $url_label, $about_label,$editprofile,$editrow) {
 if (($editprofile) && (!empty($editrow['organization']))) 
   echo '<fb:editor-text name="name" value="'.$editrow['organization'].'" label="'.$name_label.'"/>';
 else if (($editprofile) && (!empty($editrow['tripname'])))
   echo '<fb:editor-text name="name" value="'.$editrow['tripname'].'" label="'.$name_label.'"/>';
 else
   echo '<fb:editor-text name="name" label="'.$name_label.'"/>';

 if (($editprofile) && (!empty($editrow['website'])))
   echo '<fb:editor-text name="url" value="'.$editrow['website'].'" label="'.$url_label.'"/>';
 else
   echo '<fb:editor-text name="url" label="'.$url_label.'"/>';

 if (($editprofile) && (!empty($editrow['aboutme'])))
   echo '<fb:editor-text name="about" value="'.$editrow['aboutme'].'" label="'.$about_label.'"/>';
 else if (($editprofile) && (!empty($editrow['tripdesc'])))
   echo '<fb:editor-text name="about" value="'.$editrow['tripdesc'].'" label="'.$about_label.'"/>';
 else
   echo '<fb:editor-textarea name="about" label="'.$about_label.'"/>';
}

if ($editprofile) {
  if (!$is_trip) {
    echo '<fb:editor-custom name="toggle" label="Change from Missionary to Mission-Goer or vice-versa">';
    echo '<select name="toggle" id="toggle">';
    echo '<option value="0">No</option>';
    echo '<option value="1">Yes</option>';
    echo '</select>';
    echo '</fb:editor-custom>';
  }
}


if($is_mission) {
  cmc_about('Agency/Mission Name', 'Agency/Mission Website', 'About My Agency',$editprofile,$editrow);
} else if($is_trip) {
  cmc_about('Trip Name', 'Organization Website (Optional)', 'Trip Description',$editprofile,$triprow);
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
function cmc_needed_custom($db, $id, $val, $txt, $lbl, $name, $is, $type, $ts,$editprofile,$skillsrow) {
  if($is) {
    echo '<fb:editor-custom label="'.$lbl.'" name="'.$name.'">';
  } else {
    echo '<fb:editor-custom label="'.$lbl.' Needed" name="'.$name.'">';
  }
  echo '<i>Use cntrl+click to select multiple fields <i/><br />';
  echo '<select name="'.$name.'[]" id="'.$name.'" multiple="true">';
  $sql = 'SELECT * FROM '.$db.' WHERE type="'.$ts[$type].'";';
  cmc_fetch_opts($sql, $id, $val, $txt,$editprofile,$skillsrow);
  echo '</select>';
  echo '</fb:editor-custom>';
}

if($is_mission) {
  $offering = ' Facility Offerings';
  echo '<fb:editor-custom label="Medical'.$offering.'" name="medfacil">';
  echo '<i>Use cntrl+click to select multiple fields <i/><br />';
  echo '<select name="medfacil[]" multiple="true">';
  $type = 'medical offerings';
  $sql = 'SELECT * FROM skills WHERE type="'.$skilltypes[$type].'";';
  cmc_fetch_opts($sql, 'id', 'id', 'skilldesc',$editprofile,$skillsrow);
  echo '</select>';
  echo '</fb:editor-custom>';

  echo '<fb:editor-custom label="Non-Medical'.$offering.'" name="nonmedfacil">';
  echo '<i>Use cntrl+click to select multiple fields <i/><br />';
  echo '<select name="nonmedfacil[]" multiple="true">';
  $type = 'non-medical offerings';
  $sql = 'SELECT * FROM skills WHERE type="'.$skilltypes[$type].'";';
  cmc_fetch_opts($sql, 'id', 'id', 'skilldesc',$editprofile,$skillsrow);
  echo '</select>';
  echo '</fb:editor-custom>';
}

if (!$is_trip) {
cmc_needed_custom('skills', 'id', 'id', 'skilldesc', 'Medical Skills',
                  'medskills', $is_volunteer, 'medical', $skilltypes,$editprofile,$skillsrow);
cmc_needed_custom('skills', 'id', 'id', 'skilldesc', 'Non-Medical Skills',
                  'otherskills', $is_volunteer, 'non-medical', $skilltypes,$editprofile,$skillsrow);
cmc_needed_custom('skills', 'id', 'id', 'skilldesc', 'Spiritual Service',
                  'spiritserv', $is_volunteer, 'spiritual', $skilltypes,$editprofile,$skillsrow);
}
?>

<fb:editor-custom name="relg" label="Religious Affiliation">
<select name="relg" id="relg">
<?php 
if ($editprofile) {
if ($is_trip) {
if (!strcmp($triprow['religion'],"  "))
echo '<option selected="selected" value="">  </option>';
else
echo '<option value="">  </option>';

if (!strcmp($triprow['religion'],"Secular"))
echo '<option selected="selected" value="Secular">Secular</option>';
else
echo '<option value="Secular">Secular</option>';

if (!strcmp($triprow['religion'],"Christian: Protestant"))
echo '<option selected="selected" value="Christian: Protestant">Christian: Protestant</option>';
else
echo '<option value="Christian: Protestant">Christian: Protestant</option>';

if (!strcmp($triprow['religion'],"Christian: Roman Catholic"))
echo '<option selected="selected" value="Christian: Roman Catholic" >Christian: Roman Catholic</option>';
else
echo '<option value="Christian: Roman Catholic" >Christian: Roman Catholic</option>';

if (!strcmp($triprow['religion'],"Nondenominational"))
echo '<option selected="selected" value="Nondenominational" >Nondenominational</option>';
else
echo '<option value="Nondenominational" >Nondenominational</option>';

echo '</select>';
echo '</fb:editor-custom>';
}
else {
if (!strcmp($editrow['religion'],"  "))
echo '<option selected="selected" value="">  </option>';
else
echo '<option value="">  </option>';

if (!strcmp($editrow['religion'],"Secular"))
echo '<option selected="selected" value="Secular">Secular</option>';
else
echo '<option value="Secular">Secular</option>';

if (!strcmp($editrow['religion'],"Christian: Protestant"))
echo '<option selected="selected" value="Christian: Protestant">Christian: Protestant</option>';
else
echo '<option value="Christian: Protestant">Christian: Protestant</option>';

if (!strcmp($editrow['religion'],"Christian: Roman Catholic"))
echo '<option selected="selected" value="Christian: Roman Catholic" >Christian: Roman Catholic</option>';
else
echo '<option value="Christian: Roman Catholic" >Christian: Roman Catholic</option>';

if (!strcmp($editrow['religion'],"Nondenominational"))
echo '<option selected="selected" value="Nondenominational" >Nondenominational</option>';
else
echo '<option value="Nondenominational" >Nondenominational</option>';

echo '</select>';
echo '</fb:editor-custom>';
}
}
else {
?>

<option value="">   </option>'
<option value="Secular">Secular</option>
<option value="Christian: Protestant">Christian: Protestant</option>
<option value="Christian: Roman Catholic" >Christian: Roman Catholic</option>
<option value="Nondenominational" >Nondenominational</option>
</select>
</fb:editor-custom>

<?php
}

function month_display($newdp) {
$month_names = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");

for ($i=1;$i<=12;$i++) {
if (!empty($newdp)) {
  if ($newdp[1]==$i)
  echo '<option selected="selected" value="'.$i.'">'.$month_names[$i-1].'</option>';
  else
  echo '<option value="'.$i.'">'.$month_names[$i-1].'</option>';  
}
else
  echo '<option value="'.$i.'">'.$month_names[$i-1].'</option>';
}

}

function day_display($newdp) {
for ($i=1;$i<=31;$i++) {
if (!empty($newdp)) {
 if ($newdp[2]==$i)
 echo '<option selected="selected" value="'.$i.'">'.$i.'</option>';
 else
 echo '<option value="'.$i.'">'.$i.'</option>';

}
else
 echo '<option value="'.$i.'">'.$i.'</option>';
}
}

function year_display($year,$newdp) {

for ($i=0;$i<50;$i++) {
	if (!empty($newdp)) {
	if ($newdp[0] == ($year+$i))
	echo '<option selected="selected" value="'.strval($year+$i).'">'.strval($year+$i).'</option>';
	else
	echo '<option value="'.strval($year+$i).'">'.strval($year+$i).'</option>';

	}
	else
	echo '<option value="'.strval($year+$i).'">'.strval($year+$i).'</option>';
}

}

if($is_trip) {
  $year = (int)date("Y");
  $year = $year + 0;

  if (($editprofile) && (!empty($triprow['numpeople']))) 
  echo '<fb:editor-text label="Anticipated Number of Team Members" name="numpeople" value="'.$triprow["numpeople"].'"/>';
  else
  echo '<fb:editor-text label="Anticipated Number of Team Members" name="numpeople"/>';

  if (($editprofile) && (!empty($triprow['departure']))) {
  $newvar = explode(' ',$triprow['departure']);
  $newdp = explode('-',$newvar[0]);
  }

  echo '<fb:editor-custom name="Departure" label="Approximate Departure">';
  echo '<select name="DepartYear" id="DepartYear">';

  year_display($year,$newdp);
  echo '</select>';
  echo '<select name="DepartMonth" id="DepartMonth">';
  month_display($newdp);
  echo '</select>';
  echo '<select name="DepartDay" id="DepartDay">';
  day_display($newdp);
  echo '</select>';
  echo '</fb:editor-custom>';

  if (($editprofile) && (!empty($triprow['returning']))) {
  $newvar = explode(' ',$triprow['returning']);
  $newdp2 = explode('-',$newvar[0]);
  }

  echo '<fb:editor-custom name="Return" label="Approximate Return">';
  echo '<select name="ReturnYear" id="ReturnYear">';
  year_display($year,$newdp2);
  echo '</select>';
  echo '<select name="ReturnMonth" id="ReturnMonth">';
  month_display($newdp2);
  echo '</select>';
  echo '<select name="ReturnDay" id="ReturnDay">';
  day_display($newdp2);
  echo '</select>';
  echo '</fb:editor-custom>';

  }
?>

<fb:editor-custom label="Duration of Missions" name="dur">
<select name="dur[]" id="dur" multiple="true">
<?php
cmc_fetch_opts('SELECT * FROM durations', 'id', 'id', 'name',$editprofile,$durationsrow);
?>
</select>
</fb:editor-custom>

<?php if($is_trip) { 

if (($editprofile) && (!empty($triprow['isinexecutionstage']))) {

echo '<fb:editor-custom label="Mission Stage" name="stage">';
echo '<select name="stage" id="stage">';
if ($triprow['isinexecutionstage'] == 0)
echo '<option selected="selected" "value="0">Planning Stage</option>';
else
echo '<option value="0">Planning Stage</option>';

if ($triprow['isinexecutionstage'] == 1)
echo '<option selected="selected" value="1">Execution Stage</option>';
else
echo '<option value="1">Execution Stage</option>';

echo '</select>';
echo '</fb:editor-custom>';

}
else {

?>
<fb:editor-custom label="Mission Stage" name="stage">
<select name="stage" id="stage">
<option value="0">Planning Stage</option>
<option value="1">Execution Stage</option>
</select>
</fb:editor-custom>
<?php 
}

} ?>

<?php 
if (!$is_trip) {

?>
<fb:editor-custom name="state" label="State">
<select name="state" id="state">
<?php
echo '<option value="">   </option>';
cmc_fetch_opts('select id, longname from usstates;', 'id', 'id', 'longname',$editprofile,$usstatesrow);
?>
</select>
</fb:editor-custom>

<?php
if ($editprofile) {
//if (!empty($editrow['state']))
//echo '<fb:editor-text label="State" name="state" value="'.$editrow['state'].'" maxlength="50"/>';
//else
//echo '<fb:editor-text label="State" name="state" maxlength="50"/>';

if (!empty($editrow['city']))
echo '<fb:editor-text label="City" name="city" value="'.$editrow['city'].'" maxlength="50"/>';
else
echo '<fb:editor-text label="City" name="city" maxlength="50"/>';

}
else {

//<fb:editor-text label="State" name="state" maxlength="50"/>
?>

<fb:editor-text label="City" name="city" maxlength="50"/>

<?php
}
}

if ($is_trip) {
if (($editprofile) && (!empty($triprow['zipcode'])))
echo '<fb:editor-text label="Destination Zip Code" name="zip" value="'.$triprow['zipcode'].'" maxlength="5"/>';
else
echo '<fb:editor-text label="Destination Zip Code" name="zip" maxlength="5"/>';
}
else {
if (($editprofile) && (!empty($editrow['zipcode'])))
echo '<fb:editor-text label="Zip Code" name="zip" value="'.$editrow['zipcode'].'" maxlength="5"/>';
else
echo '<fb:editor-text label="Zip Code" name="zip" maxlength="5"/>';
}

/*
if ($editprofile) {
if (!empty($editrow['zipcode'])) {
if ($is_trip)
echo '<fb:editor-text label="Destination Zip Code" name="zip" value="'.$editrow['zipcode'].'" maxlength="5"/>';
else
echo '<fb:editor-text label="Zip Code" name="zip" value="'.$editrow['zipcode'].'" maxlength="5"/>';
}
else {
if ($is_trip)
echo '<fb:editor-text label="Destination Zip Code" name="zip" maxlength="5"/>';
else
echo '<fb:editor-text label="Zip Code" name="zip" maxlength="5"/>';
}
}
else {

if ($is_trip)
echo '<fb:editor-text label="Destination Zip Code" name="zip" maxlength="5"/>';
else
echo '<fb:editor-text label="Zip Code" name="zip" maxlength="5"/>';
}
*/

?>


<?php 
if (!$is_trip) {
?>

<fb:editor-custom name="region" label="Regions of Interest">
<select name="region[]" multiple="true">
<?php
cmc_fetch_opts('SELECT * FROM regions', 'id', 'id', 'name',$editprofile,$regionsrow);
?>
</select>
</fb:editor-custom>


<?php
}

if ($is_trip) {
  if (($editprofile) && (!empty($triprow['destination']))) 
  echo '<fb:editor-text label="Destination city" name="destination" value="'.$triprow['destination'].'"/>';
  else
  echo '<fb:editor-text label="Destination city" name="destination"/>';
?>

<fb:editor-custom name="country" label="Destination Country">
<?php
}
else {
?>

<fb:editor-custom name="country" label="Countries Served">
<?php
}
?>

<select name="country[]" multiple="true">
<?php
if ($is_trip)
cmc_fetch_optsnew('select id, longname from countries;', 'id', 'id', 'longname',$editprofile,$triprow);
else
cmc_fetch_opts('select id, longname from countries;', 'id', 'id', 'longname',$editprofile,$countriesrow);
?>
</select>
</fb:editor-custom>

<?php

if ($is_trip) {
if (($editprofile) && (!empty($triprow['phone'])))
echo '<fb:editor-text name="phone" label="Phone Number" value="'.$triprow['phone'].'"/>';
else
echo '<fb:editor-text name="phone" label="Phone Number"/>';

if (($editprofile) && (!empty($triprow['email'])))
echo '<fb:editor-text name="email" label="Email Address" value="'.$triprow['email'].'"/>';
else
echo '<fb:editor-text name="email" label="Email Address"/>';

}
else {
if (($editprofile) && (!empty($editrow['phone'])))
echo '<fb:editor-text name="phone" label="Phone Number" value="'.$editrow['phone'].'"/>';
else
echo '<fb:editor-text name="phone" label="Phone Number"/>';

if (($editprofile) && (!empty($editrow['email'])))
echo '<fb:editor-text name="email" label="Email Address" value="'.$editrow['email'].'"/>';
else
echo '<fb:editor-text name="email" label="Email Address"/>';

}
?>

<?php
/*
<fb:dialog id="Code">
<fb:dialog-title>Contact Information</fb:dialog-title>
<fb:dialog-content>Christian Missions Connector pulls uses the contact information from your main Facebook profile so you don't have to worry about keeping your CMC profile up to date. Just keep your Facebook page current, and we'll take care of the rest! If you would like to provide another contact in addition to the one from your profile, just fill in the optional space. If you do not want to show any contact information at all, simply choose the option to hide your contact information.</fb:dialog-content>
<fb:dialog-button type="button" value="Close" close_dialog="1"/>
</fb:dialog>
<a href="" clicktoshowdialog="Code">Why do we pull your contact information from your profile?</a><br/>
*/

if (!$is_trip) {
if (($editprofile) && (!empty($editrow['missionsexperience'])))
echo '<fb:editor-text name="misexp" label="My Missions Experience" value="'.$editrow["missionsexperience"].'"/>';
else
echo '<fb:editor-text name="misexp" label="My Missions Experience"/>';
}
?>

<fb:editor-buttonset>
<fb:editor-button value="Submit" name="submit"/>
</fb:editor-buttonset>

</fb:editor>

<?php
}
?>
