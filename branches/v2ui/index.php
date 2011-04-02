<?php

// put PHP code here

function cmc_js_load($src) {
  echo '<script type="text/javascript" src="'.$src.'"></script>';
}

function cmc_library_load_jquery($ver) {
  cmc_js_load('http://code.jquery.com/jquery-'.$ver.'.js');
}

function cmc_library_load_jquery_ui($ver) {
  $src = 'https://ajax.googleapis.com/ajax/libs/jqueryui/'.$ver.'/jquery-ui.js';
  cmc_js_load($src);
}

function cmc_library_load_jquery_ui_theme($ver, $theme) {
  $href = 'http://ajax.googleapis.com/ajax/libs/jqueryui/';
  $href .= $ver;
  $href .= '/themes/';
  $href .= $theme;
  $href .= '/jquery-ui.css';
  echo '<link type="text/css" href="'.$href.'" rel="stylesheet" />';
}

function cmc_jquery_startup($jquery_version, $jquery_ui_version, $theme) {
  cmc_library_load_jquery_ui_theme($jquery_ui_version, $theme);
  cmc_library_load_jquery($jquery_version);
  cmc_library_load_jquery_ui($jquery_ui_version);
  echo "\n";
}

function
cmc_big_button($title, $subtext=FALSE, $onclick=FALSE, $img=FALSE,
               $imgstyle=FALSE, $imgw=75, $imgh=75) {
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
    <meta charset="utf-8">
    <title><?php echo STR_APP_NAME; ?></title>
  </head>
  <body>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <!-- Include jQuery stuff and link stylesheet to the specified theme -->
    <?php cmc_jquery_startup("1.5.1", "1.8.10", "mint-choc"); ?>
    <link rel="stylesheet" href="fcbkcomplete-style.css" type="text/css"
          media="screen" charset="utf-8" />
    <script src="jquery.fcbkcomplete.js" type="text/javascript"></script>
    <div id="fb-root"></div>
    <script src="https://connect.facebook.net/en_US/all.js"></script>
    <script type="text/javascript">
      // JavaScript code goes here
      var CMC = {
        // variables
        loggedInUser : false,
        friends : false,
        requestsOutstanding : 0,

        // methods
        page : function(from, to) {
          $(from).hide("drop", {direction: 'left'}, 250, function() {
            $(to).show("drop", {direction: 'right'}, 250, null);
          });
        },

        showAjaxSpinner : function() {
          $("#ajax-spinner").show();
        },

        hideAjaxSpinner : function() {
          $("#ajax-spinner").hide();
        },

        ajaxNotifyStart : function() {
          if (CMC.requestsOutstanding == 0) {
            CMC.showAjaxSpinner();
          }
          CMC.requestsOutstanding++;
        },

        ajaxNotifyComplete : function() {
          if (CMC.requestsOutstanding > 0) {
            CMC.requestsOutstanding--;
            if (CMC.requestsOutstanding == 0) {
              CMC.hideAjaxSpinner();
            }
          }
        }
      };

      FB.init({
        appId  : '153051888089898',
        status : true,
        cookie : true,
        fbml   : true
      });

      $(function() {
        $("#make-profile, #make-volunteer, #make-organizer").hide();

        $(".cmc-big-button").hover(
          function() { $(this).addClass('ui-state-hover'); },
          function() { $(this).removeClass('ui-state-hover'); }
        );

        $("#tabs").tabs({
          fx: {
            //height: 'toggle',
            opacity: 'toggle',
            duration: 'fast'
          }
        });
        
        $("#ajax-spinner")
          .hide()
          .ajaxStart(function() {
            CMC.ajaxNotifyStart();
          })
          .ajaxStop(function() {
            CMC.ajaxNotifyComplete();
          });

        $("#search-box-select").fcbkcomplete({
          addontab : true,
          cache : true,
          complete_text : "Start typing...",
          filter_hide : true,
          //filter_selected : true, // We want this feature, but it's busted.
          firstselected : true, // circumvent a selection bug
          height : 6,
          maxshownitems : 5,
          newel : true,
          // custom (i.e. undocumented) options here
          cmc_zipcode_detect : true,
          //cmc_icon_class : "ui-icon ui-icon-search" // broken.
        });

        $("#cmc-search-icon").click(function() {
          $("#search-box").children("form").children("ul").children("li.bit-input").children("input").focus();
        });

        CMC.ajaxNotifyStart();
        FB.getLoginStatus(function(response) {
          CMC.ajaxNotifyComplete();
          if (response.session) {
            CMC.loggedInUser = response.session.uid;
            CMC.ajaxNotifyStart();
            FB.api('/me/friends', function(friends) {
              CMC.ajaxNotifyComplete();
              CMC.friends = friends.data;
            });
          }
        });

        // this should be the last thing that happens
        $("#loading").fadeOut(function() {
          $("#tabs").hide().fadeIn();
        });

      });
    </script>
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

      #tabs {
        height: 500px;
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

      .cmc-search-icon {
        position: relative;
        float: right;
        clear: both;
        right: -5px;
        margin-top: 7px;
        margin-left: 0px;
        vertical-align: middle;
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
      
    </style>
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
        <li><a href="#network-tab">My Network</a></li>
        <li><a href="#invite-tab">Invite</a></li>
        <div id="ajax-spinner" style="float: right; margin-top: 6px; margin-right: 5px;">
          <img src="ajax-spinner.gif" />
        </div>
      </ul>
      <div id="welcome-tab">
        <h1>Welcome to Christian Missions Connector.</h1>
        <p>Are you interested in missions work? Do you want to connect with people and organizations who share your passion for missions? Whether you want to find a missions organization, start a mission team, join a mission team or just connect with others who have a passion for missions, Christian Missions Connector can help.</p>
      </div>
      <div id="profile-tab">
        <div id="make-volunteer">
          <h1>Cool! You're a volunteer.</h1>
          <p>We're excited you're here! Now we'd like to sync with your Facebook profile so we can connect you with mission trips all over the world. We'll also let you know if anyone invites you to join their trip!</p>
        </div>
        <div id="make-organizer">
          <h1>Awesome! You're an organizer.</h1>
          <p>It's great to have you onboard! We'd like to take a chance to sync with your Facebook profile so we can connect you to volunteers all over the world. If you have a Facebook page, you can link that too. We'll be sure to let you know when people join your trips!</p>
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
        <div id="no-profile">
          <div class="ui-state-highlight ui-corner-all ui-widget cmc-infobar">
            <p class="cmc-infobar-text">
              <span class="ui-icon ui-icon-info cmc-infobar-icon"></span>
              <strong>Oops!</strong>
              You haven't created a profile yet! Create one now and get involved.
            </p>
          </div>
          <h1>
            <a href="#" onclick="CMC.page('#no-profile', '#make-profile');">
              Create a Profile Now &gt;&gt;
            </a>
          </h1>
        </div>
      </div>
      <div id="trips-tab">
        <p>Proin elit arcu, rutrum commodo, vehicula tempus, commodo a, risus. Curabitur nec arcu. Donec sollicitudin mi sit amet mauris. Nam elementum quam ullamcorper ante. Etiam aliquet massa et lorem. Mauris dapibus lacus auctor risus. Aenean tempor ullamcorper leo. Vivamus sed magna quis ligula eleifend adipiscing. Duis orci. Aliquam sodales tortor vitae ipsum. Aliquam nulla. Duis aliquam molestie erat. Ut et mauris vel pede varius sollicitudin. Sed ut dolor nec orci tincidunt interdum. Phasellus ipsum. Nunc tristique tempus lectus.</p>
      </div>
      <div id="search-tab">
        <div id="search-box" style="width: 500px; margin-left: auto; margin-right: auto">
          <div id="search-tipbar" style="position: relative; height: 16px">
            <div id="search-tipbar-left" style="position: absolute; left: 2px;">
              Search history...
            </div>
            <div id="search-tipbar-right" style="position: absolute; right: -9px;">
              Need some help?
            </div>
          </div>
          <div id="search-box-box">
            <div id="cmc-search-icon" class="ui-icon ui-icon-search cmc-search-icon">
            </div>
            <div id="cmc-search-box">
              <form>
                <select id="search-box-select" name="search-box-select">
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
                  <option value="20">Team Spiritual Leader</option>
                  <option value="21">Individual Outreach (Prayer or Counseling)</option>
                  <option value="22">Evangelism</option>
                  <option value="44">Worship Team</option>
                  <option value="51">Public Speaking</option>
                </select>
              </form>
            </div>
          </div>
        </div>
        <h1>Search Results:</h1>
      </div>
      <div id="network-tab">
        <p>Morbi tincidunt, dui sit amet facilisis feugiat, odio metus gravida ante, ut pharetra massa metus id nunc. Duis scelerisque molestie turpis. Sed fringilla, massa eget luctus malesuada, metus eros molestie lectus, ut tempus eros massa ut dolor. Aenean aliquet fringilla sem. Suspendisse sed ligula in ligula suscipit aliquam. Praesent in eros vestibulum mi adipiscing adipiscing. Morbi facilisis. Curabitur ornare consequat nunc. Aenean vel metus. Ut posuere viverra nulla. Aliquam erat volutpat. Pellentesque convallis. Maecenas feugiat, tellus pellentesque pretium posuere, felis lorem euismod felis, eu ornare leo nisi vel felis. Mauris consectetur tortor et purus.</p>
      </div>
      <div id="invite-tab">
        <p>Mauris eleifend est et turpis. Duis id erat. Suspendisse potenti. Aliquam vulputate, pede vel vehicula accumsan, mi neque rutrum erat, eu congue orci lorem eget lorem. Vestibulum non ante. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Fusce sodales. Quisque eu urna vel enim commodo pellentesque. Praesent eu risus hendrerit ligula tempus pretium. Curabitur lorem enim, pretium nec, feugiat nec, luctus a, lacus.</p>
        <p>Duis cursus. Maecenas ligula eros, blandit nec, pharetra at, semper at, magna. Nullam ac lacus. Nulla facilisi. Praesent viverra justo vitae neque. Praesent blandit adipiscing velit. Suspendisse potenti. Donec mattis, pede vel pharetra blandit, magna ligula faucibus eros, id euismod lacus dolor eget odio. Nam scelerisque. Donec non libero sed nulla mattis commodo. Ut sagittis. Donec nisi lectus, feugiat porttitor, tempor ac, tempor vitae, pede. Aenean vehicula velit eu tellus interdum rutrum. Maecenas commodo. Pellentesque nec elit. Fusce in lacus. Vivamus a libero vitae lectus hendrerit hendrerit.</p>
      </div>
    </div>
    <!-- Do not place HTML markup below this line -->
  </body>
</html>
