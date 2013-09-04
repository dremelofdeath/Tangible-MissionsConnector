<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<?php

include_once 'api/common.php';
$con = arena_connect();

function cmc_get_opts_for_result($result, $idColumn, $nameColumn, $valuePrefix='') {
  $optiontext = '';
  if ($result) {
    if (mysql_num_rows($result) > 0) {
      while($row = mysql_fetch_assoc($result)) {
        $optiontext .= '<option value="'.$valuePrefix.$row[$idColumn].'">'.$row[$nameColumn].'</option>';
      }
    }
  }
  return $optiontext;
}

function cmc_get_opts_for_table($table, $idColumn, $nameColumn, $valuePrefix='') {
  global $con;
  return cmc_get_opts_for_result(mysql_query("SELECT * FROM ".$table.";", $con), $idColumn, $nameColumn, $valuePrefix);
}

function cmc_echo_opt_countries($valuePrefix='') {
  echo cmc_get_opts_for_table("countries", "id", "longname", $valuePrefix);
}

function cmc_echo_opt_usstates($valuePrefix='') {
  echo cmc_get_opts_for_table("usstates", "id", "longname", $valuePrefix);
}

function cmc_echo_opt_languages($valuePrefix='') {
  echo cmc_get_opts_for_table("languages", "id", "englishname", $valuePrefix);
}

function cmc_echo_opt_skills($skilltype=FALSE, $valuePrefix='') {
  global $con;
  $result = false;
  if (!$skilltype) {
    $result = mysql_query("SELECT * FROM skills WHERE type=1 OR type=2 OR type=3;", $con);
  } else {
    $result = mysql_query("SELECT * FROM skills WHERE type=".$skilltype.";", $con);
  }
  echo cmc_get_opts_for_result($result, "id", "skilldesc", $valuePrefix);
}

function cmc_js_load($src) {
  echo '<script type="text/javascript" src="'.$src.'"></script>';
}

function cmc_library_load_jquery($ver) {
  //@/BEGIN/DEBUGONLYSECTION
  $src = 'https://ajax.googleapis.com/ajax/libs/jquery/'.$ver.'/jquery.js';
  /*
  //@/END/DEBUGONLYSECTION
  $src = 'https://ajax.googleapis.com/ajax/libs/jquery/'.$ver.'/jquery.min.js';
  //@/BEGIN/DEBUGONLYSECTION
   */
  //@/END/DEBUGONLYSECTION
  cmc_js_load($src);
}

function cmc_library_load_jquery_ui($ver) {
  //@/BEGIN/DEBUGONLYSECTION
  $src = 'https://ajax.googleapis.com/ajax/libs/jqueryui/'.$ver.'/jquery-ui.js';
  /*
  //@/END/DEBUGONLYSECTION
  $src = 'https://ajax.googleapis.com/ajax/libs/jqueryui/'.$ver.'/jquery-ui.min.js';
  //@/BEGIN/DEBUGONLYSECTION
   */
  //@/END/DEBUGONLYSECTION
  cmc_js_load($src);
}

function cmc_library_load_jquery_ui_theme($theme, $custom=false) {
  $href = '';
  if($custom) {
    // NOTE: $theme is ignored for custom themes -- perhaps allow multiple 
    // custom themes in the future? -zack
    $href .= 'css/custom-theme/jquery-ui-.custom.css';
  } else {
    $href .= 'https://ajax.googleapis.com/ajax/libs/jqueryui/';
    $href .= $ver;
    $href .= '/themes/';
    $href .= $theme;
    $href .= '/jquery-ui.css';
  }
  echo '<link type="text/css" href="'.$href.'" rel="stylesheet" />';
}

function cmc_jquery_startup($jquery_version, $jquery_ui_version, $theme) {
  if($theme == 'custom' || $theme == 'custom-theme') {
    cmc_library_load_jquery_ui_theme($theme, true);
  } else {
    cmc_library_load_jquery_ui_theme($theme);
  }
  cmc_library_load_jquery($jquery_version);
  cmc_library_load_jquery_ui($jquery_ui_version);
  echo "\n";
}

function cmc_big_button($title, $subtext=FALSE, $onclick=FALSE, $img=FALSE, $imgstyle=FALSE, $imgw=75, $imgh=75) {
  echo '<a href="#" class="cmc-button-link"';
  if($onclick) {
    echo ' onclick="'.$onclick.'"';
  }
  echo '>';

  echo '<div class="ui-state-default ui-corner-all cmc-big-button">';
  echo '<div class="cmc-big-button-icon">';

  if($img) {
    echo "<img src=\"$img\" width=\"$imgw\" height=\"$imgh\"";
    if($imgstyle) {
      echo " style=\"$imgstyle\"";
    }
    echo " />";
  }

  echo '</div>'; // cmc-big-button-icon

  echo '<h1 class="cmc-big-button-text"';
  if(!$subtext) {
    echo ' style="padding-top: 12px;"';
  }
  echo '>';
  echo $title;
  echo '</h1>';

  if($subtext) {
    echo '<p class="cmc-big-button-text">';
    echo $subtext;
    echo '</p>';
  }

  echo '</div>'; // cmc-big-button

  echo '</a>';
}
?>

<html>
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <link rel="stylesheet" href="fcbkcomplete-style.css" type="text/css" media="screen" charset="utf-8" />
    <link rel="stylesheet" href="tipTip.css" type="text/css" />
    <link rel="stylesheet" type="text/css" href="jquery.validate.css" />
    <link rel="stylesheet" type="text/css" href="style.css" />
    <link href="datepicker.css" rel="stylesheet" type="text/css" />
    <title>Christian Missions Connector</title>
    <!-- Custom CSS markup goes here -->
    <style type="text/css">

      body {
        font: 11px "Trebuchet MS", sans-serif;
      }

      h1 {
        font: 18px "Trebuchet MS", sans-serif;
      }

      img {
        border: 0px none;
      }

      #tabs, #loading {
        height: 500px;
      }

      #ajax-spinner {
        position: absolute;
        float: right;
        top: 5px;
        right: 2px;
        margin-top: 6px;
        margin-right: 5px;
      }

      #debug-section {
        display: inline-block;
        margin-left: 0px auto;
        margin-top: 20px;
        z-index: -1;
      }

      .cmc-infobar {
        padding-left: 0.7em;
        padding-right: 0.7em;
      }

      .cmc-infobar-text {
        display: inline-block;
      }

      .cmc-infobar-icon {
        float: left;
        margin-right: 0.3em;
      }

      .tab-loading-screen {
        margin-top: 80px;
        margin-left: 194px;
      }

      .tab-loading-text {
        display: block;
        font-size: 2em;
        font-weight: bold;
      }

      .tab-loading-subtext {
        display: block;
        font-size: 1.5em;
        font-weight: bold;
        margin-top: 15px;
      }

      .cmc-big-button {
        height: 75px;
        padding-top: 7px;
        padding-bottom: 7px;
        margin-bottom: 0.3em;
      }

      .cmc-big-button-icon {
        padding-left: 0.7em;
        padding-right: 1.1em;
        float: left;
        position: relative;
        display: inline-block;
        height: 75px;
        width: 75px;
      }

      #welcome-video {
        text-align: center;
        margin-top: 2.75em;
      }

      #search-box {
        position: relative;
        left: -0.7em;
        width: 500px;
        margin-left: auto;
        margin-right: auto;
      }

      .cmc-search-icon {
        position: absolute;
        float: right;
        clear: both;
        right: -5px;
        margin-top: 7px;
        margin-left: 0px;
        vertical-align: middle;
      }

      .tipbar-link {
        text-decoration: none;
      }

      .tipbar-content h4, #recent-searches h4, #saved-searches h4 {
        margin-top: 5px !important;
        margin-bottom: 3px !important;
      }

      .inner-tipbar-content li {
        margin: 15px;
        line-height: 1px; /* must not be zero due to a webkit bug */
      }
      
      h1.cmc-big-button-text {
        margin-bottom: 5px;
      }

      p.cmc-big-button-text {
        margin-top: 7px;
      }

      a.cmc-button-link {
        text-decoration: none;
      }

      #cmc-search-results-title {
        margin-bottom: 24px;
      }

      #cmc-search-results-pagingctl {
        position: absolute;
        right: 18px;
        margin-top: -1px;
      }

      #cmc-search-results-pagingctl-text {
        position: relative;
        display: inline-block;
        top: -8px;
        margin-right: 0.1em;
        text-align: center;
        vertical-align: middle;
        height: 20px;
        width: 75px !important;
      }

      .cmc-square-button {
        height: 20px;
        width: 20px !important;
      }

      .cmc-search-result {
        height: 60px;
        width: 200px;
        position: relative;
      }

      .cmc-search-result .result-picture {
        display: inline;
        float: left;
        margin-right: 7px;
        width: 50px;
        height: 50px;
      }

      .cmc-search-result .result-name {
        font-weight: bold;
      }

      .cmc-search-result-col {
        display: block;
        position: absolute;
      }

      #cmc-search-result-col-0 {
        margin-left: 0px;
      }

      #cmc-search-result-col-1 {
        left: 0px;
        margin-left: 50%;
      }

      /* 
      The original incarnation of the invite tab is very 
      similar to that of the search tab.
      */


      #cmc-invite-results-title {
        margin-bottom: 24px;
      }

      #invite-box {
        position: relative;
        left: -0.7em;
        width: 500px;
        height: 30px;
        margin-left: auto;
        margin-right: auto;
      }
      
      #invite-search-box-text {
        height: 24px;
        margin-top: 3px;
      }

      .cmc-invite-icon {
        position: absolute;
        float: right;
        clear: both;
        right: -5px;
        margin-top: 11px;
        margin-left: 0px;
        vertical-align: middle;
      }

      #cmc-invite-results-pagingctl {
        position: absolute;
        right: 18px;
        margin-top: -1px;
      }

      #cmc-invite-results-pagingctl-text {
        position: relative;
        display: inline-block;
        top: -8px;
        margin-right: 0.1em;
        text-align: center;
        vertical-align: middle;
        height: 20px;
        width: 75px !important;
      }

      .cmc-invite-result-container {
        height: 60px;
        width: 200px;
        position: relative;
      }

      .cmc-invite-result {
        height: 50px;
        width: 200px;
        position: relative;
      }

      .cmc-invite-result .result-picture {
        display: inline;
        float: left;
        margin-right: 7px;
        width: 50px;
        height: 50px;
      }

      .cmc-invite-result .result-name {
        font-weight: bold;
      }

      .cmc-invite-result-col {
        display: block;
        position: absolute;
      }

      .cmc-invite-border-fix {
        padding-top: 1px;
        padding-left: 1px;
      }

      #cmc-invite-result-col-0 {
        margin-left: 0px;
      }

      #cmc-invite-result-col-1 {
        left: 0px;
        margin-left: 50%;
      }

      #cmc-invite-tab-select-controls {
        position: absolute;
        right: 18px;
        bottom: 18px;
      }

      img.srloading {
        z-index: -1;
        display: none;
      }

      #cmc-footer a {
        text-decoration: none;
        color: #102030;
      }

      #cmc-footer .leftside {
        position: absolute;
        left: 8px;
      }

      #cmc-footer .rightside {
        position: absolute;
        right: 8px;
      }

      #report-problem-characters-left {
        float: right;
        margin-top: 7px;
        margin-right: 14px;
      }

      #profile-left-column {
        float: left;
        margin-right: 9px;
        height: 450px;
        width: 180px;
      }

      #trip-profile-left-column {
        float: left;
        margin-right: 9px;
        height: 450px;
        width: 180px;
      }

      .entity-right-content-section {
        height: 410px;
        width: 72%;
        display: inline-block;
        overflow: auto;
      }

      .entity-title {
        font-size: 2em;
        float: left;
      }

      .entity-link {
        text-decoration: none;
      }

      .entity-control {
        display: inline-block;
        margin: 2px 0;
      }

      .entity-control-spacer {
        display: inline-block;
        width: 3px;
        height: 1px;
      }

      .profile-picture {
        width: 180px;
      }
      .trip-profile-picture {
        width: 180px;
      }

      ul.trip-list {
        list-style-type: none;
        padding: 0px;
      }

      .trip-item-control {
        float: right;
      }

      .trip-item-name {
        font-size: 1em;
        font-weight: bold;
        padding-top: 6px;
        position: relative;
      }

      li.trip-list-item {
        margin-bottom: 12px;
        height: 18px;
      }
    </style>
  </head>
  <body>
    <!-- Include jQuery stuff and link stylesheet to the specified theme -->
    <!-- FIXME: don't use PHP here, put stylesheet links in the right place -zack -->
    <?php cmc_jquery_startup("1.7.1", "1.8.18", "custom-theme"); ?>
    <script src="jquery.fcbkcomplete.js" type="text/javascript"></script>
    <script src="jquery.tipTip.js" type="text/javascript"></script>
    <!-- imagesLoaded plugin obtained from https://gist.github.com/268257 -->
    <script src="jquery.imagesLoaded.js" type="text/javascript"></script>
    <div id="fb-root"></div>
    <script src="https://connect.facebook.net/en_US/all.js"></script>
    <script src="base64.js"></script>
    <script src="json2-min.js"></script>
    <script type="text/javascript" src="watermarkTextbox.js"></script>
    <script type="text/javascript" src="cmc.js"></script>
    <script src="jquery.validate.js" type="text/javascript"></script>
    <script src="jquery.validation.functions.js" type="text/javascript"></script> 
    <script src="datepicker.js" type="text/javascript"></script>

    <link rel="stylesheet" type="text/css" href="jquery.multiselect.css" />
    <script type="text/javascript" src="jquery.multiselect.js"></script>

    <!-- @/BEGIN/ADMINCODEBLOCK -->
    <!-- THIS IS THE ADMIN CODE BLOCK! Do NOT put code here for end users, they won't see it! -zack -->
    <script type="text/javascript">
      $(function() {
        CMC.log("loading admin only code");
        $("#secret-hideout-dialog").dialog({
          autoOpen: false,
          draggable: false,
          position: [25, 25],
          resizable: false,
          height: 465,
          width: 700,
          open: function() {
            CMC.dialogOpen(this);
          },
          close: function() {
            CMC.dialogClose(this);
          }
        });

        $("#secret-hideout").click(function() {
          $("#secret-hideout-dialog").dialog('open');
        });

        $("#debug-detach-handlers").button().click(function() { CMC.detachDebugHandlers(); });
        $("#debug-force-login").button().click(function() { CMC.login(); });
        $("#debug-hide-profile-loading").button().click(function() { CMC.hideTabLoading('profile'); });

        CMC.log("admin load complete");
      });
    </script>
    <!-- @/END/ADMINCODEBLOCK -->

    <script type="text/JavaScript">
      function tripzipdisplay() {
        if ($('select#profile-trip-country').val() == 1) {
          $('#profile-trip-dialog').find('#tripzip').show();
        } else {
          $('#profile-trip-dialog').find('#tripzip').hide();
        }
      }

      function orgzipdisplay() {
        if ($('select#profile-org-country').val() == 1) {
          //$('#profile-organizer-dialog').find('#orgzip').attr("style","display:block;");
          $('#profile-organizer-dialog').find('#orgzip').show();
        }
        else {
          $('#profile-organizer-dialog').find('#orgzip').hide();
        }
      }

      function volzipdisplay() {
        if ($('select#profile-country').val() == 1) {
          //$('#profile-volunteer-dialog').find('#volzip').attr("style","display:block;");
          $('#profile-volunteer-dialog').find('#volzip').show();
        }
        else {
          $('#profile-volunteer-dialog').find('#volzip').hide();
        }
      }
    </script> 
    <!-- HTML markup goes here -->
    <div id="loading">
      <div style="vertical-align: middle; text-align: center; display: block">
        <div style="margin-top: 80px; margin-left: auto; margin-right: auto">
          <img src="loading-spinner.gif" />
        </div>
      </div>
    </div>
    <div id="tabs" style="display: none">
      <ul>
        <li><a href="#welcome-tab">Welcome!</a></li>
        <li><a href="#profile-tab">Profile</a></li>
        <li><a href="#trips-tab">Trips</a></li>
        <li><a href="#search-tab">Search</a></li>
        <!-- @/BEGIN/CUTSECTION -->
        <li><a href="#network-tab">My Network</a></li>
        <!-- @/END/CUTSECTION -->
        <li><a href="#invite-tab">Invite</a></li>
      </ul>
      <div id="ajax-spinner">
        <img src="ajax-spinner.gif" />
      </div>
      <div id="welcome-tab">
        <h1>Welcome to Christian Missions Connector.</h1>
        <p>Are you interested in missions work? Do you want to connect with people and organizations who share your passion for missions? Whether you want to find a missions organization, start a mission team, join a mission team or just connect with others who have a passion for missions, Christian Missions Connector can help.</p>
        <p>You can get started by either exploring with the tabs above or watching the instructional video below.</p>
        <div id="welcome-video">
          <iframe width="480" height="270" src="http://www.youtube.com/embed/7qfBm53lp64" frameborder="0" allowfullscreen></iframe>
        </div>
      </div>
      <div id="profile-tab">
        <div id="profile-tab-loading" class="tab-loading-screen">
          <span id="profile-tab-loading-text" class="tab-loading-text">Loading...</span>
          <span id="profile-tab-loading-subtext" class="tab-loading-subtext">Hold on, we're getting that for you...</span>
        </div>
        <div id="make-volunteer">
          <h1>Cool! You're a volunteer.</h1>
          <p>We're excited you're here! Now we'd like to sync with your Facebook profile so we can connect you with mission trips all over the world. We'll also let you know if anyone invites you to join their trip!</p>
          <h1>
            <a href="#" id="make-volunteer-link">Make your Profile &gt;&gt;</a>
          </h1>
        </div>
        <div id="make-organizer">
          <h1>Awesome! You're an organizer.</h1>
          <p>It's great to have you onboard! We'd like to take a chance to sync with your Facebook profile so we can connect you to volunteers all over the world. If you have a Facebook page, you can link that too. We'll be sure to let you know when people join your trips!</p>
          <h1>
            <a href="#" id="make-organizer-link">Make your Profile &gt;&gt;</a>
          </h1>
        </div>
        <div id="make-profile">
          <h1>Create a Profile: Who Are You?</h1>
          <p>Don't worry. If you change your mind, you can come back later and pick something else.</p>
          <?php
            cmc_big_button(
              "I'm a volunteer",
              "I'm interested in supporting or going on mission trips",
              "CMC.page('#make-profile', '#make-volunteer');",
              "icon-volunteer.png",
              "padding-top: 5px;",
              65, 65
            );
            cmc_big_button(
              "I'm an organizer",
              "I lead a missions team or represent an organization",
              "CMC.page('#make-profile', '#make-organizer');",
              "icon-organizer.png"
            );
          ?>
        </div>
        <div id="show-profile" style="display: none">
          <div id="profilecontent">
            <div id="profile-left-column">
              <div id="profile-picture-section">
                <a id="profile-picture-link" class="entity-link" href="#" target="_blank">
                  <img class="profile-picture" src="ajax-spinner.gif">
                </a>
              </div>
              <div id="profile-section-about-me">
                <h3>About Me:</h3>
                <div id="profile-section-about-me-content">
                  Sample About Text
                </div>
              </div>
            </div>
            <div id="profile-right-column">
              <div id="profile-title-section">
                <a id="profile-name-link" class="entity-link" href="#" target="_blank">
                  <span id="profile-name" class="entity-title">Sample Long Name</span>
                </a>
                <div id="profile-controls" class="entity-controls">
                  <div id="profile-controls-spacer" class="entity-control-spacer"></div>
                  <div id="profile-controls-edit" class="entity-control cmc-square-button"></div>
                  <div id="profile-controls-create-trip" class="entity-control cmc-square-button"></div>
                  <div id="profile-controls-back-to-my-profile" class="entity-control cmc-square-button"></div>
                </div>
              </div>
              <div id="profile-right-content-section" class="entity-right-content-section">
                <span id="profile-website">www.example.com</span>
                <div id="profile-section-skills">
                  <h3>Medical Skills:</h3>
                  <div id="profile-medskills">Medskills
                  </div>
                  <h3>Non-Medical Skills:</h3>
                  <div id="profile-nonmedskills">Non-Medskills
                  </div>
                </div>
                <div class="box1">
                  <h3>Personal Information:</h3>
                  <h5 id="profile-email-display">Email: <span id="profile-email">Email</span></h5>
                  <h5 id="profile-phone-display">Phone: <span id="profile-phone">Phone</span></h5>
                  <h5 id="profile-country-display">Country: <span id="profile-country">Country</span></h5>
                  <h5 id="profile-zip-display">Zip: <span id="profile-zip">Zip</span></h5>
                  <h5 id="profile-dur-display">Preferred Duration of Mission Trips: <span id="profile-dur">Duration</span></h5>
                  <h5 id="profile-countries-display">Countries of Interest: <span id="profile-countries">Countries</span></h5>
                </div>
                <h3>Trips Information:</h3>
                <div id="profile-trips-list-section">
                  <ul id="profile-trip-list" class="trip-list">
                    <li class="trip-list-item">
                      <span class="trip-item-name">Example Trip Name</span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div id="no-profile" style="display: none">
          <div class="ui-state-highlight ui-corner-all ui-widget cmc-infobar">
            <p class="cmc-infobar-text">
             <span class="ui-icon ui-icon-info cmc-infobar-icon"></span>
             <strong>Oops!</strong>
             You haven't created a profile yet! Create one now and get involved.
            </p>
          </div>
          <h1>
            <a href="#" onclick="CMC.page('#no-profile', '#make-profile');">Create a Profile Now &gt;&gt;</a>
          </h1>
        </div>
      </div>
      <div id="trips-tab">
        <div id="trips-tab-loading" class="tab-loading-screen" style="display: none"><!-- this isn't done yet... -zack -->
          <span id="trips-tab-loading-text" class="tab-loading-text">Loading...</span>
          <span id="trips-tab-loading-subtext" class="tab-loading-subtext">Hold on, we're getting that for you...</span>
        </div>
        <div id="make-trip">
          <?php
            cmc_big_button(
              "I want to create a trip",
              "for the purpose of conducting missions",
              "CMC.page('#make-trip', '#make-trip-profile');",
              "icon-organizer.png"
            );
          ?>
        </div>
        <div id="show-trip-profile" style="display: none">
          <div id="tripprofilecontent">
            <div id="trip-profile-left-column">
              <div id="tripprofileimage">
                <div id="trip-owner-picture">
                  <img class="trip-profile-picture" src="ajax-spinner.gif" />
                </div>
              </div>
              <h3>Trip Owner:</h3> 
              <span id="profile-trip-owner">Name</span>
              <h3>Trip Description:</h3>
              <h4><span id="trip-profile-about">About</span></h4>
            </div>
            <div id="trip-profile-right-column" class="entity-right-content-section">
              <div id="trip-profile-title-section">
                <span id="profile-trip-name" class="entity-title">Sample Trip Name</span>
                <div id="trip-profile-controls" class="entity-controls">
                  <div id="trip-profile-controls-spacer" class="entity-control-spacer"></div>
                  <div id="trip-profile-controls-back-to-trips" class="entity-control cmc-square-button"></div>
                </div>
              </div>
              <h5>Trip Website: <span id="profile-trip-url"><a href="http://www.example.com/">http://www.example.com/</a></span></h5>
              <h5>Trip Destination: <span id="profile-trip-dest">Trip Destination</span></h5>
              <h5>E-mail: <span id="profile-trip-email">Email</span></h5>
              <h5>Phone: <span id="profile-trip-phone">Phone</span></h5>
              <h5>Execution Stage: <span id="profile-trip-stage">Trip Execution Stage</span></h5>
              <h5>Date of Departure: <span id="profile-trip-depart">Departure Date</span></h5>
              <h5>Date of Return: <span id="profile-trip-return">Return</span></h5>
              <h5>Trip Religion: <span id="profile-trip-religion">Trip religion</span><h5>
              <h5>Trip Accommodation Level: <span id="profile-trip-acco">Trip Accommodation Level</span></h5>
              <h5>Number of People involved in this Trip: <span id="profile-trip-numpeople">Number of people</span></h5>
              <h5>Trip Medical Skills: <span id="profile-trip-medskills">Medskills</span></h5>
              <h5>Trip Non-Medical Skills: <span id="profile-trip-nonmedskills">Non-Medskills</span></h5>
              <h5>Trip Spiritual Skills: <span id="profile-trip-spiritskills">Spiritual skills</span></h5>
              <h5>People involved in this Trip:</h5>
              <div id="profile-trip-people">
                <div id="cmc-trip-member-0" class="cmc-tripmember-results">
                  <div id="profile-tripmember-image">
                    <img class="profile-tripmember-picture" src="ajax-spinner.gif">
                  </div>
                  <div id="profile-tripmember-name">Member Name</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div id="show-trips" style="display: none">
          <h2>Upcoming Trips:</h2>
          <div id="upcoming-trips-list-section">
            <ul id="upcoming-trip-list" class="trip-list">
              <li class="trip-list-item">
                <span class="trip-item-name">Example Trip Name</span>
              </li>
            </ul>
          </div>
        </div>
        <div id="no-trip" style="display: none">
          <h1>There aren't any trips coming up soon.</h1>
          <h1>Why don't you <a href="#" onclick="CMC.page('#no-trip', '#make-trip');">create one</a>?</h1>
        </div>
        <div id="no-profile-trip" style="display: none">
          <div class="ui-state-highlight ui-corner-all ui-widget cmc-infobar">
            <p class="cmc-infobar-text">
             <span class="ui-icon ui-icon-info cmc-infobar-icon"></span>
             <strong>Oops!</strong>
             You haven't created a profile yet! Create one now and get involved.
            </p>
          </div>
          <h1>
            <a href="#" onclick="$('#make-profile, #make-volunteer, #make-organizer').hide(); $('#no-profile').show(); CMC.page('#no-profile', '#make-profile'); $('#tabs').tabs('select', 1);">Create a Profile Now &gt;&gt;</a>
          </h1>
        </div>
      </div>
      <div id="search-tab">
        <div id="search-box">
          <div id="search-tipbar" style="position: relative; height: 16px">
            <div id="search-tipbar-left" style="position: absolute; left: 2px;">
              <!-- @/BEGIN/CUTSECTION -->
              <a class="tipbar-link" href="#">Search history...</a>
              <!-- @/END/CUTSECTION -->
              <div class="tipbar-content" style="display: none">
                <div id="recent-searches">
                  <h4>Recent Searches</h4>
                  <p>You have no recent searches. Perform a search first!</p>
                </div>
                <div id="search-history-spacer" style="display: block; height: 3px;"></div>
                <div id="saved-searches">
                  <h4>Saved Searches</h4>
                  <p>You haven't saved any searches yet. Perform a search, then click the save icon.</p>
                </div>
              </div>
            </div>
            <div id="search-tipbar-right" style="position: absolute; right: -10px;">
              <a class="tipbar-link" href="#">Need some help?</a>
              <div class="tipbar-content" style="display: none">
                <div class="inner-tipbar-content">
                  Type in the characteristics of the sort of volunteer or mission trip you are looking for. Try searching for things like:
                    <li>Profession (ex, translator)</li>
                    <li>Skills (ex, computer science)</li>
                    <li>Countries (ex, Nicaragua)</li>
                    <li>US Zip Code (ex, 98034)</li>
                    <li>...and more!</li>
                </div>
              </div>
            </div>
          </div>
          <div id="search-box-box">
            <div id="cmc-search-icon" class="ui-icon ui-icon-search cmc-search-icon"></div>
            <div id="cmc-search-box">
              <select id="search-box-select" name="search-box-select">
                <?php
                  cmc_echo_opt_skills(false, "!!s:");
                  cmc_echo_opt_countries("!!c:");
                ?>
              </select>
            </div>
          </div>
        </div>
        <div id="cmc-search-results-spacer" style="display: block; height: 16px"></div>
        <div id="cmc-search-results">
          <div id="cmc-search-results-title" style="display: none">
            <div id="cmc-search-results-pagingctl">
              <div id="cmc-search-results-pagingctl-prev" class="cmc-square-button"></div>
              <div id="cmc-search-results-pagingctl-text" class="ui-state-default ui-corner-all">
                <!-- placeholder text, should be localized elsewhere -->
                <span class="ui-button-text">page 0</span>
              </div>
              <div id="cmc-search-results-pagingctl-next" class="cmc-square-button"></div>
            </div>
            <h1 id="cmc-search-results-title-text">Search Results:</h1>
          </div>
          <div id="cmc-search-results-subtitles">
            <h2 id="cmc-search-results-noresultmsg" style="display: none">Sorry, no results were found. :(</h2>
          </div>
          <div id="cmc-search-result-list">
            <div id="cmc-search-result-col-0" class="cmc-search-result-col">
              <div id="cmc-search-result-0" class="cmc-search-result">
                <div class="result-picture">
                  <img src="ajax-spinner.gif" class="srpic srloading"/>
                </div>
                <div class="result-name">Search Result</div>
              </div>
              <div id="cmc-search-result-1" class="cmc-search-result">
                <div class="result-picture">
                  <img src="ajax-spinner.gif" class="srpic srloading"/>
                </div>
                <div class="result-name">Search Result</div>
              </div>
              <div id="cmc-search-result-2" class="cmc-search-result">
                <div class="result-picture">
                  <img src="ajax-spinner.gif" class="srpic srloading"/>
                </div>
                <div class="result-name">Search Result</div>
              </div>
              <div id="cmc-search-result-3" class="cmc-search-result">
                <div class="result-picture">
                  <img src="ajax-spinner.gif" class="srpic srloading"/>
                </div>
                <div class="result-name">Search Result</div>
              </div>
              <div id="cmc-search-result-4" class="cmc-search-result">
                <div class="result-picture">
                  <img src="ajax-spinner.gif" class="srpic srloading"/>
                </div>
                <div class="result-name">Search Result</div>
              </div>
            </div>
            <div id="cmc-search-result-col-1" class="cmc-search-result-col">
              <div id="cmc-search-result-5" class="cmc-search-result">
                <div class="result-picture">
                  <img src="ajax-spinner.gif" class="srpic srloading"/>
                </div>
                <div class="result-name">Search Result</div>
              </div>
              <div id="cmc-search-result-6" class="cmc-search-result">
                <div class="result-picture">
                  <img src="ajax-spinner.gif" class="srpic srloading"/>
                </div>
                <div class="result-name">Search Result</div>
              </div>
              <div id="cmc-search-result-7" class="cmc-search-result">
                <div class="result-picture">
                  <img src="ajax-spinner.gif" class="srpic srloading"/>
                </div>
                <div class="result-name">Search Result</div>
              </div>
              <div id="cmc-search-result-8" class="cmc-search-result">
                <div class="result-picture">
                  <img src="ajax-spinner.gif" class="srpic srloading"/>
                </div>
                <div class="result-name">Search Result</div>
              </div>
              <div id="cmc-search-result-9" class="cmc-search-result">
                <div class="result-picture">
                  <img src="ajax-spinner.gif" class="srpic srloading"/>
                </div>
                <div class="result-name">Search Result</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- @/BEGIN/CUTSECTION -->
      <div id="network-tab">
        <p>Morbi tincidunt, dui sit amet facilisis feugiat, odio metus gravida ante, ut pharetra massa metus id nunc. Duis scelerisque molestie turpis. Sed fringilla, massa eget luctus malesuada, metus eros molestie lectus, ut tempus eros massa ut dolor. Aenean aliquet fringilla sem. Suspendisse sed ligula in ligula suscipit aliquam. Praesent in eros vestibulum mi adipiscing adipiscing. Morbi facilisis. Curabitur ornare consequat nunc. Aenean vel metus. Ut posuere viverra nulla. Aliquam erat volutpat. Pellentesque convallis. Maecenas feugiat, tellus pellentesque pretium posuere, felis lorem euismod felis, eu ornare leo nisi vel felis. Mauris consectetur tortor et purus.</p>
      </div>
      <!-- @/END/CUTSECTION -->
      <div id="invite-tab">
        <div id="invite-box">
          <div id="invite-box-box">
            <div id="cmc-invite-icon" class="ui-icon ui-icon-search cmc-invite-icon"></div>
            <div id="cmc-invite-box">
              <input type="text" id="invite-search-box-text" name="invite-search-box-text" value="Start typing to find your friends..."  class="ui-corner-all watermarkTextbox" style="width: 100%"></input>
            </div>
          </div>
        </div>
        <div id="cmc-invite-results-spacer" style="display: block; height: 10px"></div>
        <div id="cmc-invite-results">
          <div id="cmc-invite-results-title" style="display: none">
            <div id="cmc-invite-results-pagingctl">
              <div id="cmc-invite-results-pagingctl-prev" class="cmc-square-button"></div>
              <div id="cmc-invite-results-pagingctl-text" class="ui-state-default ui-corner-all">
                <!-- placeholder text, should be localized elsewhere -->
                <span class="ui-button-text">page 0</span>
              </div>
              <div id="cmc-invite-results-pagingctl-next" class="cmc-square-button"></div>
            </div>
            <h1 id="cmc-invite-results-title-text">Friends:</h1>
          </div>
          <div id="cmc-invite-results-subtitles">
            <h2 id="cmc-invite-results-noresultmsg" style="display: none">Sorry, couldn't find any friends with that name :(</h2>
          </div>
          <div id="cmc-invite-result-list">
            <div id="cmc-invite-result-col-0" class="cmc-search-result-col">
              <div id="cmc-invite-result-container-0" class="cmc-invite-result-container">
                <div id="cmc-invite-result-0" class="cmc-invite-result cmc-invite-border-fix ui-corner-all">
                  <div class="result-picture">
                    <img src="ajax-spinner.gif" class="srpic srloading"/>
                  </div>
                  <div class="result-name">Invite Result</div>
                </div>
              </div>
              <div id="cmc-invite-result-container-1" class="cmc-invite-result-container">
                <div id="cmc-invite-result-1" class="cmc-invite-result cmc-invite-border-fix ui-corner-all">
                 <div class="result-picture">
                   <img src="ajax-spinner.gif" class="srpic srloading"/>
                  </div>
                  <div class="result-name">Invite Result</div>
                </div>
              </div>
              <div id="cmc-invite-result-container-2" class="cmc-invite-result-container">
                <div id="cmc-invite-result-2" class="cmc-invite-result cmc-invite-border-fix ui-corner-all">
                  <div class="result-picture">
                    <img src="ajax-spinner.gif" class="srpic srloading"/>
                  </div>
                  <div class="result-name">Invite Result</div>
                </div>
              </div>
              <div id="cmc-invite-result-container-3" class="cmc-invite-result-container">
                <div id="cmc-invite-result-3" class="cmc-invite-result cmc-invite-border-fix ui-corner-all">
                  <div class="result-picture">
                    <img src="ajax-spinner.gif" class="srpic srloading"/>
                  </div>
                  <div class="result-name">Invite Result</div>
                </div>
              </div>
              <div id="cmc-invite-result-container-4" class="cmc-invite-result-container">
                <div id="cmc-invite-result-4" class="cmc-invite-result cmc-invite-border-fix ui-corner-all">
                  <div class="result-picture">
                    <img src="ajax-spinner.gif" class="srpic srloading"/>
                  </div>
                  <div class="result-name">Invite Result</div>
                </div>
              </div>
            </div>
            <div id="cmc-invite-result-col-1" class="cmc-invite-result-col">
              <div id="cmc-invite-result-container-5" class="cmc-invite-result-container">
                <div id="cmc-invite-result-5" class="cmc-invite-result cmc-invite-border-fix ui-corner-all">
                  <div class="result-picture">
                    <img src="ajax-spinner.gif" class="srpic srloading"/>
                  </div>
                  <div class="result-name">Invite Result</div>
                </div>
              </div>
              <div id="cmc-invite-result-container-6" class="cmc-invite-result-container">
                <div id="cmc-invite-result-6" class="cmc-invite-result cmc-invite-border-fix ui-corner-all">
                  <div class="result-picture">
                    <img src="ajax-spinner.gif" class="srpic srloading"/>
                  </div>
                  <div class="result-name">Invite Result</div>
                </div>
              </div>
              <div id="cmc-invite-result-container-7" class="cmc-invite-result-container">
                <div id="cmc-invite-result-7" class="cmc-invite-result cmc-invite-border-fix ui-corner-all">
                  <div class="result-picture">
                    <img src="ajax-spinner.gif" class="srpic srloading"/>
                  </div>
                  <div class="result-name">Invite Result</div>
                </div>
              </div>
              <div id="cmc-invite-result-container-8" class="cmc-invite-result-container">
                <div id="cmc-invite-result-8" class="cmc-invite-result cmc-invite-border-fix ui-corner-all">
                  <div class="result-picture">
                    <img src="ajax-spinner.gif" class="srpic srloading"/>
                  </div>
                  <div class="result-name">Invite Result</div>
                </div>
              </div>
              <div id="cmc-invite-result-container-7" class="cmc-invite-result-container">
                <div id="cmc-invite-result-9" class="cmc-invite-result cmc-invite-border-fix ui-corner-all">
                  <div class="result-picture">
                    <img src="ajax-spinner.gif" class="srpic srloading"/>
                  </div>
                  <div class="result-name">Invite Result</div>
                </div>
              </div>
            </div>
          </div>
          <div id="cmc-invite-tab-select-controls">
            <div id="cmc-invite-select-max-hint" style="display: inline-block; margin-right: 20px">
              <span id="cmc-invite-select-max-hint-before">Invite these</span>
              <span id="cmc-invite-select-max-hint-value">50,</span><!-- This value is controlled in JS -zack -->
              <span id="cmc-invite-select-max-hint-after">then you can choose more.</span>
            </div>
            <div id="cmc-invite-selected-button" style="width: 118px"></div>
            <div id="cmc-invite-start-over-button"></div>
          </div>
        </div>
      </div>
    </div>
    <div id="cmc-footer" style="display: none">
      <div class="leftside">
        <!-- @/BEGIN/ADMINCODEBLOCK -->
        <a href="#" id="secret-hideout">Admin</a> :
        <!-- @/END/ADMINCODEBLOCK -->
        <!-- @/BEGIN/CUTSECTION -->
        <a href="#" id="report-problem">Report a Problem</a> : <a href="#" id="contact-link">Contact Us</a>
        <!-- @/END/CUTSECTION -->
      </div>
      <div class="rightside">
        <a href="#" id="copyrights">Copyrights</a>
        <!-- @/BEGIN/CUTSECTION -->
        : <a href="http://www.tangiblesoft.net/" target="_blank" id="tangible-link">Tangible, LLC</a>
        <!-- @/END/CUTSECTION -->
      </div>
    </div>
    <!-- Dialogs and such should go here -->
    <div id="dialogs" style="display: none">
      <div id="copyrights-dialog" title="Copyrights">
        <!-- @/BEGIN/CUTSECTION -->
        <p>Christian Missions Connector is Copyright 2009-2011 Tangible, LLC. All rights reserved.</p>
        <!-- @/END/CUTSECTION -->
        <p>Christian Missions Connector uses the jQuery and jQuery UI libraries. For more information, visit <a href="http://www.jquery.com" target="_blank">www.jquery.com</a>.</p>
        <p>Portions adapted from FCBKcomplete 2.7.5 and TipTip 1.3. FCBKcomplete is Copyright 2010 Emposha (<a href="http://www.emposha.com" target="_blank">www.emposha.com</a>). TipTip is Copyright 2011 Drew Wilson (<a href="http://code.drewwilson.com/entry/tiptip-jquery-plugin" target="_blank">code.drewwilson.com</a>). Both are used and modified with permission under the <a href="http://www.opensource.org/licenses/mit-license.php" target="_blank">MIT license.</a></p>
        <p>Contains content obtained from The Noun Project (<a href="http://www.thenounproject.com" target="_blank">www.thenounproject.com</a>). "Community" reproduced under the Creative Commons Attribution 3.0 Unported license. For licensing information, please visit <a href="http://creativecommons.org/licenses/by/3.0/" target="_blank">http://creativecommons.org/licenses/by/3.0/</a>.</p>
        <p>"Arriving Flights" by Roger Cook and Don Shanosky, 1974. Obtained from the public domain. </p>
      </div>
      <div id="profile-volunteer-dialog" title="Please enter your profile information">
        <form id="profile-volunteer-form">
          <div id="wrapper">
            <div id="toggle-profile-volunteer">
              <a href="#" onclick="CMC.ToggleProfile();">I'd like my profile to be for an agency instead of a volunteer. </a>
            </div>
            <div id="contents">
              <div class="profile-container">
                <div class="profile-contents">
                  <table cellpadding="4" cellspacing="0">
                    <tr>
                      <td style="width: 97px">
                        <label>Medical Skills</label>
                      </td>
                      <td style="width: 97px">
                        <select id="profile-medical-skills" class="cmc-form-spec" multiple="multiple" name="profile-medical-skills">
                          <!--<option class="cmc-default-opt" value="0" selected="selected">Select Medical Skills</option>-->
                          <?php cmc_echo_opt_skills(1); ?>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 97px">
                        <label>Non-Medical Skills</label>
                      </td>
                      <td style="width: 197px">
                        <select id="profile-nonmedical-skills" class="cmc-form-spec" multiple="multiple" name="profile-nonmedical-skills">
                          <!--<option class="cmc-default-opt" value="0" selected="selected">Select Non-Medical Skills</option>-->
                          <?php cmc_echo_opt_skills(2); ?>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 97px">
                        <label>Spiritual Service</label>
                      </td>
                      <td style="width: 197px">
                        <select id="profile-spiritual-skills" class="cmc-form-spec" multiple="multiple" name="profile-spiritual-skills">
                          <!--<option class="cmc-default-opt" value="0" selected="selected">Select Spiritual Service</option>-->
                          <?php cmc_echo_opt_skills(3); ?>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 97px">
                        <label>Religious Affiliation</label>
                      </td>
                      <td style="width: 197px">
                        <select id="profile-religion" class="cmc-form-spec">
                          <option class="cmc-default-opt" value="Select Religious Affiliation", selected="selected">Select Religious Affiliation</option>
                          <option value="Secular">Secular</option>
                          <option value="Christian: Protestant">Christian: Protestant</option>
                          <option value="Christian: Catholic">Christian: Catholic</option>
                          <option value="Nondenominational">Nondenominational</option>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 97px">
                        <label>Duration of Missions</label>
                      </td>
                      <td style="width: 197px">
                        <select id="profile-duration" class="cmc-form-spec">
                          <option class="cmc-default-opt" value="Select Duration of Missions" selected="selected">Select Duration of Missions</option>
                          <option value="1">Short Term: 1-2 weeks</option>
                          <option value="2">Medium Term: 1 Month-2 Years</option>
                          <option value="3">Long Term: 2+ Years</option>
                        </select>
                      </td>
                    </tr>
                    <!--
                    <tr>
                      <td style="width: 197px">
                        <label>State</label>
                      </td>
                      <td style="width: 197px">
                        <select id="profile-state" class="cmc-form-spec">
                          <option class="cmc-default-opt" value="Select your State" selected="selected">Select your State</option>
                          <?php cmc_echo_opt_usstates(); ?>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <label>City</label>
                      </td>
                      <td>
                        <input type="text" id="profile-city" class="cmc-form-spec"/>
                      </td>
                    </tr>
                    -->
                    <tr>
                      <td style="width: 197px">
                        <label>Country</label>
                      </td>
                      <td style="width: 197px">
                        <select id="profile-country" class="cmc-form-spec" onchange="volzipdisplay();">
                          <?php cmc_echo_opt_countries(); ?>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <label>Zipcode</label>
                      </td>
                      <td>
                        <input type="text" id="profile-zipcode" class="cmc-form-spec"/>
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 197px">
                        <label>Regions of Interest</label>
                      </td>
                      <td style="width: 197px">
                        <select id="profile-region" class="cmc-form-spec" multiple="multiple" name="profile-region">
                          <!-- <option class="cmc-default-opt" value="Select Regions of Interest" selected="selected">Select Regions of Interest</option> -->
                          <option id="0" value="1">Africa</option>
                          <option id="1" value="2">Asia and Oceana</option>
                          <option id="2" value="3">Europe and Russia</option>
                          <option id="3" value="4">Latin America</option>
                          <option id="4" value="5">Middle East</option>
                          <option id="5" value="6">North America</option>
                          <option id="6" value="7">Caribbean</option>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 197px">
                        <label>Countries Served</label>
                      </td>
                      <td style="width: 197px">
                        <select id="profile-country-served" class="cmc-form-spec" multiple="multiple" name="profile-country-served">
                          <!-- <option class="cmc-default-opt" selected="selected" value="Select Countries Served">Select Countries Served</option> -->
                          <?php cmc_echo_opt_countries(); ?>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <label>Phone</label>
                      </td>
                      <td>
                        <input type="text" id="profile-phone" class="cmc-form-spec" />
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <label>Email Id</label>
                      </td>
                      <td>
                        <input type="text" id="profile-email" class="cmc-form-spec" />
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <label>My Missions Experience</label>
                      </td>
                      <td>
                        <input type="text" id="profile-experience" class="cmc-form-spec" />
                      </td>
                    </tr>
                    <tr>
                      <td>&nbsp;</td>
                      <td>
                        <input type="button" value="Submit" id="profile-submit" />
                      </td>
                    </tr>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div id="profile-organizer-dialog" title="Please enter your profile information">
        <form id="profile-organizer-form">
          <div id="wrapper">
            <div id="toggle-profile-agency">
              <a href="#" onclick="CMC.ToggleProfile();">I'd like my profile to be for a volunteer instead of an agency. </a>
            </div>
            <div id="contents">
              <div class="profile-container">
                <div class="profile-contents">
                  <table id="orgtable" cellpadding="4" cellspacing="0">
                    <tr>
                      <td><label>Agency/Mission Name</label></td>
                      <td><input type="text" id="profile-org-name" class="cmc-form-spec" /></td>
                    </tr>
                    <tr>
                      <td><label>Agency/Mission Website</label></td>
                      <td><input type="text" id="profile-org-website" class="cmc-form-spec" /></td>
                    </tr>
                    <tr>
                      <td><label>About My Agency</label></td>
                      <td><input type="text" id="profile-org-about" class="cmc-form-spec" /></td>
                    </tr>
                    <tr>
                      <td style="width: 97px"><label>Medical Facility Offerings</label></td>
                      <td style="width: 97px">
                        <select id="profile-org-offer" class="cmc-form-spec" name="profile-org-offer" multiple="multiple">
                          <!--<option class="cmc-default-opt" value="0" selected="selected">Select Medical Skills</option>-->
                          <?php cmc_echo_opt_skills(4); ?>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 97px"><label>Non-Medical Facility Offerings</label></td>
                      <td style="width: 97px">
                        <select id="profile-org-offern" class="cmc-form-spec" name="profile-org-offern" multiple="multiple">
                        <!--<option class="cmc-default-opt" value="0" selected="selected">Select Medical Skills</option>-->
                        <?php cmc_echo_opt_skills(5); ?>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 97px"><label>Medical Skills</label></td>
                      <td style="width: 97px">
                        <select id="profile-org-medical" class="cmc-form-spec" name="profile-org-medical" multiple="multiple">
                          <!--<option class="cmc-default-opt" value="0" selected="selected">Select Medical Skills</option>-->
                          <option value="1">Advanced Practice Nursing</option>
                          <option value="2">Dental Professional</option>
                          <option value="3">Medical Educator</option>
                          <option value="4">Mental Health Professional</option>
                          <option value="5">Nurse</option>
                          <option value="6">Optometrist or Opthalmologist</option>
                          <option value="7">Pharmacist</option>
                          <option value="8">Physician</option>
                          <option value="9">Physician Assistant</option>
                          <option value="10">Physical or Occupational Therapist</option>
                          <option value="11">Public Health/Community Development Worker</option>
                          <option value="12">Speech Therapist</option>
                          <option value="13">Other</option>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 97px"><label>Non-Medical Skills</label></td>
                      <td style="width: 197px">
                        <select id="profile-org-nonmedical" class="cmc-form-spec" name="profile-org-nonmedical" multiple="multiple">
                          <!--<option class="cmc-default-opt" value="0" selected="selected">Select Non-Medical Skills</option>-->
                          <option value="14">General Help/Labor</option>
                          <option value="15">Team Leader/Primary Organizer</option>
                          <option value="16">Account and/or Business Management</option>
                          <option value="17">Skilled Construction and/or Maintenance</option>
                          <option value="18">Computer Science/Other Technical</option>
                          <option value="19">Agriculture and/or Animal Husbandry</option>
                          <option value="45">Mechanic</option>
                          <option value="46">Office/Secretarial</option>
                          <option value="47">Teaching</option>
                          <option value="48">Veterinary</option>
                          <option value="49">Water Supply Improvement</option>
                          <option value="50">Writing and/or Translating</option>
                          <option value="52">Engineering</option>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 97px"><label>Spiritual Service</label></td>
                      <td style="width: 197px">
                        <select id="profile-org-spiritual" class="cmc-form-spec" name="profile-org-spiritual" multiple="multiple">
                          <!--<option class="cmc-default-opt" value="Select Spiritual Serivice" selected="selected">Select Spiritual Service</option>-->
                          <option value="20">Team Spiritual Leader</option>
                          <option value="21">Individual Outreach (Prayer and Counseling)</option>
                          <option value="22">Evangelism</option>
                          <option value="44">Worship Team</option>
                          <option value="51">Public Speaking</option>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 97px"><label></label></td>
                      <td style="width: 197px">
                        <select id="profile-org-religion" class="cmc-form-spec">
                          <option class="cmc-default-opt" value="Select Religious Affiliation", selected="selected">Select Religious Affiliation</option>
                          <option value="Secular">Secular</option>
                          <option value="Christian: Protestant">Christian: Protestant</option>
                          <option value="Christian: Catholic">Christian: Catholic</option>
                          <option value="Nondenominational">Nondenominational</option>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 97px"><label>Duration of Missions</label></td>
                      <td style="width: 197px">
                        <select id="profile-org-duration" class="cmc-form-spec">
                          <option class="cmc-default-opt" value="Select Duration of Missions" selected="selected">Select Duration of Missions</option>
                          <option value="1">Short Term: 1-2 weeks</option>
                          <option value="2">Medium Term: 1 Month-2 Years</option>
                          <option value="3">Long Term: 2+ Years</option>
                        </select>
                      </td>
                    </tr>
                    <!--
                    <tr>
                      <td style="width: 197px"><label>State</label></td>
                      <td style="width: 197px">
                        <select id="profile-org-state" class="cmc-form-spec">
                          <option class="cmc-default-opt" value="Select your State" selected="selected">Select your State</option>
                          <?php cmc_echo_opt_usstates(); ?>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td><label>City</label></td>
                      <td><input type="text" id="profile-org-city" class="cmc-form-spec"/></td>
                    </tr>
                    <tr>
                      <td style="width: 197px"><label>Country</label></td>
                      <td style="width: 197px">
                        <select id="profile-org-country" class="cmc-form-spec" onchange="orgzipdisplay();">
                          <?php cmc_echo_opt_countries(); ?>
                        </select>
                      </td>
                    </tr>
                    -->
                    <tbody id="orgzip">
                      <tr>
                        <td><label>Zipcode</label></td>
                        <td><input type="text" id="profile-org-zipcode" class="cmc-form-spec"/></td>
                      </tr>
                    </tbody>
                    <tr>
                      <td style="width: 197px"><label>Regions of Interest</label></td>
                      <td style="width: 197px">
                        <select id="profile-org-region" class="cmc-form-spec" name="profile-org-region" multiple="multiple">
                          <!--<option class="cmc-default-opt" value="0" selected="selected">Select Regions of Interest</option>-->
                          <option value="1">Africa</option>
                          <option value="2">Asia and Oceana</option>
                          <option value="3">Europe and Russia</option>
                          <option value="4">Latin America</option>
                          <option value="5">Middle East</option>
                          <option value="6">North America</option>
                          <option value="7">Caribbean</option>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 197px"><label>Countries Served</label></td>
                      <td style="width: 197px">
                        <select id="profile-org-countryserved" class="cmc-form-spec" name="profile-org-countryserved" multiple="multiple">
                          <!-- <option class="cmc-default-opt" selected="selected" value="Select Countries Served">Select Countries Served</option> -->
                          <?php cmc_echo_opt_countries(); ?>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td><label>Phone</label></td>
                      <td><input type="text" id="profile-org-phone" class="cmc-form-spec" /></td>
                    </tr>
                    <tr>
                      <td><label>Email Id</label></td>
                      <td><input type="text" id="profile-org-email" class="cmc-form-spec" /></td>
                    </tr>
                    <tr>
                      <td><label>My Missions Experience</label></td>
                      <td><input type="text" id="profile-org-experience" class="cmc-form-spec" /></td>
                    </tr>
                    <tr>
                      <td>&nbsp;</td>
                      <td><input type="button" value="Submit" class="profile-org-submit" id="profile-org-submit" /></td>
                    </tr>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div id="profile-toggle-dialog" title="Please wait...">
        <div id="toggle-image">
          <img class="toggle-wait-image" src="ajax-spinner.gif" />
        </div>
      </div>
      <div id="profile-trip-dialog" title="Please enter your trip profile information">
        <form id="profile-trip-form">
          <div id="wrapper">
            <div id="contents">
              <div class="profile-container">
                <div class="profile-contents">
                  <table cellpadding="4" cellspacing="0">
                    <tr>
                      <td><label>Trip Name</label></td>
                      <td><input type="text" id="profile-trip-name" class="cmc-form-spec" /></td>
                    </tr>
                    <tr>
                      <td><label>Organization Website</label></td>
                      <td><input type="text" id="profile-trip-website" class="cmc-form-spec" /></td>
                    </tr>
                    <tr>
                      <td><label>Trip Description</label></td>
                      <td><input type="text" id="profile-trip-about" class="cmc-form-spec" /></td>
                    </tr>
                    <tr>
                      <td style="width: 97px"><label>Religious Affiliation</label></td>
                      <td style="width: 197px">
                        <select id="profile-trip-religion" class="cmc-form-spec">
                          <option class="cmc-default-opt" value="Select Religious Affiliation", selected="selected">Select Religious Affiliation</option>
                          <option value="Secular">Secular</option>
                          <option value="Christian: Protestant">Christian: Protestant</option>
                          <option value="Christian: Catholic">Christian: Catholic</option>
                          <option value="Nondenominational">Nondenominational</option>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td><label>Anticipated Number of Team Members</label></td>
                      <td><input type="text" id="profile-trip-number" class="cmc-form-spec" /></td>
                    </tr>
                    <tr>
                      <td style="width: 97px"><label>Ongoing Needs</label></td>
                      <td style="width: 197px">
                        <input name="profile-trip-onneeds" id="profile-trip-onneeds" class="cmc-form-spec" type=checkbox value="1" >We have ongoing needs<br>
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 97px"><label>Timeframe</label></td>
                      <td style="width: 197px">
                        <input name="profile-trip-timeframe" id="profile-trip-timeframe" class="cmc-form-spec" type=checkbox value="1"> Our timeframe is flexible<br>
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 97px"><label>Approximate Departure Date</label></td>
                      <td style="width: 197px">
                        <input name="profile-trip-depart" id="profile-trip-depart" class="cmc-form-spec" type=button value="select" onclick="displayDatePicker('profile-trip-depart', false, 'mdy', '.');">
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 97px"><label>Approximate Return Date</label></td>
                      <td style="width: 197px">
                        <input name="profile-trip-return" id="profile-trip-return" class="cmc-form-spec" type=button value="select" onclick="displayDatePicker('profile-trip-return', false, 'mdy', '.');">
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 97px"><label>Duration of Missions</label></td>
                      <td style="width: 197px">
                        <select id="profile-trip-duration" class="cmc-form-spec">
                          <option class="cmc-default-opt" value="Select Duration of Missions" selected="selected">Select Duration of Missions</option>
                          <option value="1">Short Term: 1-2 weeks</option>
                          <option value="2">Medium Term: 1 Month-2 Years</option>
                          <option value="3">Long Term: 2+ Years</option>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 97px"><label>Trip Accommodation Level</label></td>
                      <td style="width: 197px">
                        <select id="profile-trip-acco" class="cmc-form-spec">
                          <option class="cmc-default-opt" value="Select Accommodation Level" selected="selected">Select Accommodation Level</option>
                          <option value="Basic shelter without indoor plumbing">Basic shelter without indoor plumbing</option>
                          <option value="Basic shelter with indoor plumbing">Basic shelter with indoor plumbing</option>
                          <option value="Modest accomodations without hot water">Modest accomodations without hot water</option>
                          <option value="Modest accomodations with hot water">Modest accomodations with hot water</option>
                          <option value="Very comfortable accomodations">Very comfortable accomodations</option>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 97px"><label>Mission Stage</label></td>
                      <td style="width: 197px">
                        <select id="profile-trip-stage" class="cmc-form-spec">
                          <option class="cmc-default-opt" value="Select Mission Stage" selected="selected">Select Mission Stage</option>
                          <option value="1">Planning</option>
                          <option value="2">Execution</option>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td><label>Destination City</label></td>
                      <td><input type="text" id="profile-trip-city" class="cmc-form-spec"/></td>
                    </tr>
                    <tr>
                      <td style="width: 197px"><label>Destination Country</label></td>
                      <td style="width: 197px">
                        <select id="profile-trip-country" class="cmc-form-spec" onchange="tripzipdisplay();">
                          <option class="cmc-default-opt" value="Select your Destination Country" selected="selected">Select your Destination Country</option>
                          <?php cmc_echo_opt_countries(); ?>
                        </select>
                      </td>
                    </tr>
                    <tbody id="tripzip" style="display: none;">
                      <tr>
                        <td><label>Destination Zipcode</label></td>
                        <td><input type="text" id="profile-trip-zipcode" class="cmc-form-spec"/></td>
                      </tr>
                    </tbody>
                    <tr>
                      <td style="width: 197px">
                        <label>Languages</label></td>
                        <td style="width: 197px">
                          <select id="profile-trip-languages" class="cmc-form-spec" name="profile-trip-languages" multiple="multiple">
                          <!-- <option class="cmc-default-opt" selected="selected" value="Select Countries Served">Select Countries Served</option> -->
                          <?php cmc_echo_opt_languages(); ?>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 197px">
                        <label>Trip Medical Skills</label></td>
                      <td style="width: 197px">
                        <select id="profile-trip-medical-skills" class="cmc-form-spec" name="profile-trip-medical-skills", multiple="multiple">
                          <!--<option class="cmc-default-opt" value="0" selected="selected">Select Medical Skills</option>-->
                          <option value="1">Advanced Practice Nursing</option>
                          <option value="2">Dental Professional</option>
                          <option value="3">Medical Educator</option>
                          <option value="4">Mental Health Professional</option>
                          <option value="5">Nurse</option>
                          <option value="6">Optometrist or Opthalmologist</option>
                          <option value="7">Pharmacist</option>
                          <option value="8">Physician</option>
                          <option value="9">Physician Assistant</option>
                          <option value="10">Physical or Occupational Therapist</option>
                          <option value="11">Public Health/Community Development Worker</option>
                          <option value="12">Speech Therapist</option>
                          <option value="13">Other</option>
                          <!--<option class="cmc-default-opt" value="0" selected="selected">Select Medical Skills</option>-->
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 197px">
                        <label>Trip Non-Medical Skills</label></td>
                      <td style="width: 197px">
                        <select id="profile-trip-nonmedical-skills" class="cmc-form-spec" name="profile-trip-nonmedical-skills" multiple="multiple">
                          <!--<option class="cmc-default-opt" value="0" selected="selected">Select Non-Medical Skills</option>-->
                          <option value="14">General Help/Labor</option>
                          <option value="15">Team Leader/Primary Organizer</option>
                          <option value="16">Account and/or Business Management</option>
                          <option value="17">Skilled Construction and/or Maintenance</option>
                          <option value="18">Computer Science/Other Technical</option>
                          <option value="19">Agriculture and/or Animal Husbandry</option>
                          <option value="45">Mechanic</option>
                          <option value="46">Office/Secretarial</option>
                          <option value="47">Teaching</option>
                          <option value="48">Veterinary</option>
                          <option value="49">Water Supply Improvement</option>
                          <option value="50">Writing and/or Translating</option>
                          <option value="52">Engineering</option>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 197px">
                        <label>Trip Spiritual Service</label></td>
                      <td style="width: 197px">
                        <select id="profile-trip-spiritual-skills" class="cmc-form-spec" name="profile-trip-spiritual-skills" multiple="multiple">
                          <!--<option class="cmc-default-opt" value="0" selected="selected">Select Spiritual Service</option>-->
                          <option value="20">Team Spiritual Leader</option>
                          <option value="21">Individual Outreach (Prayer and Counseling)</option>
                          <option value="22">Evangelism</option>
                          <option value="44">Worship Team</option>
                          <option value="51">Public Speaking</option>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td><label>Phone</label></td>
                      <td><input type="text" id="profile-trip-phone" class="cmc-form-spec" /></td>
                    </tr>
                    <tr>
                      <td><label>Email Id</label></td>
                      <td><input type="text" id="profile-trip-email"class="cmc-form-spec" /></td>
                    </tr>
                    <tr>
                      <td>&nbsp;</td>
                      <td><input type="button" value="Submit" class="profile-trip-submit" id="profile-trip-submit" /></td>
                    </tr>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div id="report-problem-dialog" title="What seems to be the matter?">
        <p>Tell us what's wrong, and we'll look into it right away.</p>
        <form id="report-problem-form">
          <textarea id="report-problem-message" height="4" cols="55" style="width: 98%;"></textarea>
          <div style="float: right; margin-right: 1px;" id="report-problem-submit">Submit</div>
          <div id="report-problem-characters-left">
            300 characters left<!-- just some placeholder text -->
          </div>
        </form>
      </div>
      <div id="invite-tab-max-selection-dialog" title="Too many invitations">
        <p id="invite-tab-max-selection-dialog-text">Sorry, but we can only send 50 invites at a time. Send these invites now to choose more.</p>
      </div>
      <!-- @/BEGIN/ADMINCODEBLOCK -->
      <!-- debug only dialogs! remove these before shipping!! -zack -->
      <div id="assert-dialog" title="Assertion failure!">
        <h2>You hit an assert!</h2>
        <p id="assert-message"></p>
      </div>
      <div id="secret-hideout-dialog" title="Administration">
        <p>This is an area for magical unicorns and rainbows.</p>
      </div>
      <!-- @/END/ADMINCODEBLOCK -->
    </div>
    <!-- @/BEGIN/ADMINCODEBLOCK -->
    <!-- The debug log. Should not be displayed by default. Enable via the admin panel. -->
    <div id="debug-section" style="display: none">
      <textarea id="debug-log" rows="10" cols="80" spellcheck="false">
        Please wait, loading debug console...
      </textarea>
      <div id="debug-info-section">
        <div id="requests-outstanding" style="display: inline; margin-right: 13px;">
          requests outstanding: <span id="requests-outstanding-value">0</span>
        </div>
        <div id="logged-in-user" style="display: inline; margin-right: 13px;">
          user: <span id="logged-in-user-value">(not in sync)</span>
        </div>
      <div id="debug-controls">
        <button id="debug-detach-handlers">detach debug handlers</button>
        <button id="debug-force-login">force login</button>
        <button id="debug-hide-profile-loading">hide loading text</button>
      </div>
    </div>
    <!-- Do not place HTML markup below this line -->
    <!-- @/END/ADMINCODEBLOCK -->
  </body>
</html>
<!-- vim: ai:et:ts=2:sw=2
-->
