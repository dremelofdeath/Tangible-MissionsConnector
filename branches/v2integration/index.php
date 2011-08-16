<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<?php

include_once 'api/common.php';
$con = arena_connect();

	$aCountries = array(
		"Afghanistan",
		"Albania",
		"Algeria",
		"Andorra",
		"Angola",
		"Antigua and Barbuda",
		"Argentina",
		"Armenia",
		"Australia",
		"Austria",
		"Azerbaijan",
		"Bahamas",
		"Bahrain",
		"Bangladesh",
		"Barbados",
		"Belarus",
		"Belgium",
		"Belize",
		"Benin",
		"Bhutan",
		"Bolivia",
		"Bosnia and Herzegovina",
		"Botswana",
		"Brazil",
		"Brunei",
		"Bulgaria",
		"Burkina Faso",
		"Burundi",
		"Cambodia",
		"Cameroon",
		"Canada",
		"Cape Verde",
		"Central African Republic",
		"Chad",
		"Chile",
		"China",
		"Colombia",
		"Comoros",
		"Congo (Brazzaville)",
		"Congo, Democratic Republic of the",
		"Costa Rica",
		"Côte d'Ivoire",
		"Croatia",
		"Cuba",
		"Cyprus",
		"Czech Republic",
		"Denmark",
		"Djibouti",
		"Dominica",
		"Dominican Republic",
		"East Timor",
		"Ecuador",
		"Egypt",
		"El Salvador",
		"Equatorial Guinea",
		"Eritrea",
		"Estonia",
		"Ethiopia",
		"Fiji",
		"Finland",
		"France",
		"Gabon",
		"Gambia, The",
		"Georgia",
		"Germany",
		"Ghana",
		"Greece",
		"Grenada",
		"Guatemala",
		"Guinea",
		"Guinea-Bissau",
		"Guyana",
		"Haiti",
		"Honduras",
		"Hungary",
		"Iceland",
		"India",
		"Indonesia",
		"Iran",
		"Iraq",
		"Ireland",
		"Israel",
		"Italy",
		"Jamaica",
		"Japan",
		"Jordan",
		"Kazakhstan",
		"Kenya",
		"Kiribati",
		"Korea, North",
		"Korea, South",
		"Kuwait",
		"Kyrgyzstan","Laos","Latvia",
		"Lebanon",
		"Lesotho",
		"Liberia",
		"Libya",
		"Liechtenstein",
		"Lithuania",
		"Luxembourg",
		"Macedonia",
		"Madagascar",
		"Malawi",
		"Malaysia",
		"Maldives",
		"Mali",
		"Malta",
		"Marshall Islands",
		"Mauritania",
		"Mauritius",
		"Mexico",
		"Micronesia",
		"Moldova",
		"Monaco",
		"Mongolia",
		"Morocco",
		"Mozambique",
		"Myanmar",
		"Namibia",
		"Nauru",
		"Nepal",
		"Netherlands",
		"New Zealand",
		"Nicaragua",
		"Niger",
		"Nigeria",
		"Norway",
		"Oman",
		"Pakistan",
		"Palau",
		"Panama",
		"Papua New Guinea",
		"Paraguay",
		"Peru",
		"Philippines",
		"Poland",
		"Portugal",
		"Qatar",
		"Romania",
		"Russia",
		"Rwanda",
		"Saint Kitts and Nevis",
		"Saint Lucia",
		"Saint Vincent and The Grenadines",
		"Samoa",
		"San Marino",
		"Sao Tome and Principe",
		"Saudi Arabia",
		"Senegal",
		"Serbia and Montenegro",
		"Seychelles",
		"Sierra Leone",
		"Singapore",
		"Slovakia",
		"Slovenia",
		"Solomon Islands",
		"Somalia",
		"South Africa",
		"Spain",
		"Sri Lanka",
		"Sudan",
		"Suriname",
		"Swaziland",
		"Sweden",
		"Switzerland",
		"Syria",
		"Taiwan",
		"Tajikistan",
		"Tanzania",
		"Thailand",
		"Togo",
		"Tonga",
		"Trinidad and Tobago",
		"Tunisia",
		"Turkey",
		"Turkmenistan",
		"Tuvalu",
		"Uganda",
		"Ukraine",
		"United Arab Emirates",
		"United Kingdom",
		"United States",
		"Uruguay",
		"Uzbekistan",
		"Vanuatu",
		"Vatican City",
		"Venezuela",
		"Vietnam",
		"Western Sahara",
		"Yemen",
		"Zambia",
		"Zimbabwe"
	);
	
	$usstates = array(
	"Alabama",
	"Alaska",
	"American Samoa",
	"Arizona",
    "Arkansas",
    "California",
    "Colorado",
    "Connecticut",
    "Delaware",
    "District of Columbia",
    "Florida",
    "Georgia",
    "Guam",
    "Hawaii",
    "Idaho",
    "Illinois",
    "Indiana",
    "Iowa",
    "Kansas",
    "Kentucky",
    "Louisiana",
    "Maine",
    "Maryland",
    "Massachusetts",
    "Michigan",
    "Minnesota",
    "Mississippi",
    "Missouri",
    "Montana",
    "Nebraska",
    "Nevada",
    "New Hampshire",
    "New Jersey",
    "New Mexico",
    "New York",
    "North Carolina",
    "North Dakota",
    "Northern Marianas Islands",
    "Ohio",
    "Oklahoma",
    "Oregon",
    "Pennsylvania",
    "Puerto Rico",
    "Rhode Island",
    "South Carolina",
    "South Dakota",
    "Tennessee",
    "Texas",
    "Utah",
    "Vermont",
    "Virginia",
    "Virgin Islands",
    "Washington",
    "West Virginia",
    "Wisconsin",
    "Wyoming"
	);
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

function cmc_library_load_jquery_ui_theme($ver, $theme, $custom=false) {
  $href = '';
  if($custom) {
    // NOTE: $theme is ignored for custom themes -- perhaps allow multiple 
    // custom themes in the future? -zack
    $href .= 'css/custom-theme/jquery-ui-' . $ver . '.custom.css';
  } else {
    $href .= 'http://ajax.googleapis.com/ajax/libs/jqueryui/';
    $href .= $ver;
    $href .= '/themes/';
    $href .= $theme;
    $href .= '/jquery-ui.css';
  }
  echo '<link type="text/css" href="'.$href.'" rel="stylesheet" />';
}

function cmc_jquery_startup($jquery_version, $jquery_ui_version, $theme) {
  if($theme == 'custom' || $theme == 'custom-theme') {
    cmc_library_load_jquery_ui_theme($jquery_ui_version, $theme, true);
  } else {
    cmc_library_load_jquery_ui_theme($jquery_ui_version, $theme);
  }
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

function cmc_check_profile_existence($fbid,&$profileexists,$con) {
  
	$sql = 'select * from users where userid="'.$fbid.'"';
	$result = mysql_query($sql,$con);
	if (!$result) {
 		setjsonmysqlerror($has_error,$err_msg,$sql2);
   	}
	else {
    $num_userids = mysql_num_rows($result);
    if ($num_userids == 0) {
      $profileexists = 0;
    }
    else {
      $profileexists = 1;
    }
  }
}
?>

<html>
  <head>
    <meta charset="utf-8">
    <title>Christian Missions Connector</title>
  </head>
  <body>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <!-- Include jQuery stuff and link stylesheet to the specified theme -->
    <?php cmc_jquery_startup("1.6.1", "1.8.11", "custom-theme"); ?>
    <link rel="stylesheet" href="fcbkcomplete-style.css" type="text/css"
          media="screen" charset="utf-8" />
    <link rel="stylesheet" href="tipTip.css" type="text/css" />
    <script src="jquery.fcbkcomplete.js" type="text/javascript"></script>
    <script src="jquery.tipTip.js" type="text/javascript"></script>
    <!-- imagesLoaded plugin obtained from https://gist.github.com/268257 -->
    <script src="jquery.imagesLoaded.js" type="text/javascript"></script>
    <div id="fb-root"></div>
    <script src="https://connect.facebook.net/en_US/all.js"></script>
    <script src="base64.js"></script>
    <script src="json2-min.js"></script>
    <script type="text/javascript" src="cmc.js"></script>
    <script src="jquery.validate.js" type="text/javascript"></script>
    <script src="jquery.validation.functions.js" type="text/javascript"></script>	
	  <link rel="stylesheet" type="text/css" href="jquery.validate.css" />
	  <link href="profilevalidate.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" type="text/css" href="style.css" />
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

        CMC.log("admin load complete");
				
        $("#profile-submit").click(function() {
				    var mtype = $("form").find('.profile-ddl-type-medical');
					var nmtype = $("form").find('.profile-ddl-type-nonmedical');
					var sptype = $("form").find('.profile-ddl-type-spiritual');
					var reltype = $("form").find('.profile-ddl-type-religious');
					var durtype = $("form").find('.profile-ddl-type-duration');
					var state = $("form").find('.profile-ddl-type-state');
				    var city = $("form").find('.profile-input-city');
					var zipcode = $("form").find('.profile-input-zipcode');
					
				    var country = $("form").find('.profile-ddl-type-country');
					var region = $("form").find('.profile-ddl-type-region');
				    var countriesserved = $("form").find('.profile-ddl-type-countriesserved');
					var phone = $("form").find('.profile-input-phone');
				    var email = $("form").find('.profile-input-email');
					var misexp = $("form").find('.profile-input-experience');				    
					
					var zipisvalid = false;
					var emailisvalid = false;
					var reason="";
					var errornum=1;
					
					if (zipcode.val() != "") {

					zipisvalid = validateZipCode(zipcode.val());
					if (!zipisvalid) {
						reason += errornum+'. Incorrect Zipcode format entered\n';
						errornum = errornum + 1;
						isValid = false;
					}
					}

					if (email.val() != "") {
					emailisvalid = validateEmail(email.val());
					if (!emailisvalid) {
						reason += errornum + '. Incorrect Email format entered\n';
						errornum = errornum + 1;
						isValid = false;
					}
					}
					
					if (phone.val() != "") {
					var phoneerror = validatePhone(phone.val(),country.val());
					if (phoneerror != "") {
						reason += errornum + ' ' + phoneerror + '\n';
						errornum = errornum + 1;
						isValid = false;
					}
					}
					
					if (reason != "") {
						alert('Some input fields need correction:\n'+ reason);
						return false;
					}
					else {

					var profileformdata = {};
					profileformdata.profiletype=1;
					if (mtype.val() != 0)
						profileformdata.medskills= mtype.val();
					if (nmtype.val() != 0)
						profileformdata.otherskills=nmtype.val();					
					if (sptype.val() != 0)
						profileformdata.spiritserv=sptype.val();				
					if (region.val() != 0)
						profileformdata.region=region.val();	
					if (country.val() != "")
						profileformdata.country=country.val();	
					if (state.val() != "Select your State")
						profileformdata.state=state.val();	
					if (durtype.val() != 0)
						profileformdata.dur=durtype.val();
					if (reltype.val() != 0)
						profileformdata.relg=reltype.val();						
					if (zipcode.val() != "")
						profileformdata.zip=zipcode.val();
					if (email.val() != "")
						profileformdata.email=email.val();
					if (city.val() != "")
						profileformdata.city=city.val();
					if (phone.val() != "")
						profileformdata.phone=phone.val();
					if (misexp.val() != "")
						profileformdata.misexp=misexp.val();						
						
                  alert('AJAX form submission = ' + JSON.stringify(profileformdata));

                  $.ajax({
                    type: "POST",
                    url: "api/profilein.php",
                    data: {
                        fbid: "25826994",
                        profiledata: JSON.stringify(profileformdata)
                    },
                    dataType: "json",
                    success: function() {
                      alert('Success');
                    },
                    error: function() {
                      alert('Failure');
                    }
                  });
                  return true;
				}
					
                function validateZipCode(elementValue){
				  var zisValid = false;
                  var zipCodePattern = /^\d{5}$|^\d{5}-\d{4}$/;
                  zisValid = zipCodePattern.test(elementValue);
				  return zisValid;
                }
				function validateEmail(email){
					var eisValid =  false;
					var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+[\.]{1}[a-zA-Z]{2,4}$/;  
					eisValid = emailPattern.test(email);  
					return eisValid;
				}	
				
				function validatePhone(fld,country) {
				var error = "";
				var stripped = fld.replace(/[\(\)\.\-\ ]/g, ''); 
				// for international numbers
				var regex = /^\+(?:[0-9] ?){6,14}[0-9]$/;

				if (isNaN(parseInt(stripped))) {
					error = "The phone number contains illegal characters.\n";
				}
				else if (country != "United States") {
					if (!regex.test(fld)) {
						error = "The phone number is not a valid International Number.\n";
					}
				}
				else if (!(stripped.length == 10)) {
					error = "The phone number is the wrong length. Make sure you included an area code.\n";
				}
				
				return error;
				}
			});
				
        // Handles the live form validation
				$("#profile-medical").validate({
                    expression: "if (VAL) return true; else return false;",
                    message: "Please enter the Required field"
                });
				$("#profile-email").validate({
                    expression: "if (VAL.match(/^[^\\W][a-zA-Z0-9\\_\\-\\.]+([a-zA-Z0-9\\_\\-\\.]+)*\\@[a-zA-Z0-9_]+(\\.[a-zA-Z0-9_]+)*\\.[a-zA-Z]{2,4}$/) && VAL) return true; else if (!VAL) return true; else return false;",
                    message: "Please enter a valid Email ID"
                });	
				$("#profile-zipcode").validate({
                    expression: "if (VAL.match(new RegExp(/(^[0-9]{5}$)|(^[0-9]{5}-[0-9]{4}$)/)) && VAL) return true; else if (!VAL) return true; else return false;",
                    message: "Please enter a valid Zipcode"
                });	
				$("#profile-phone").validate({
					expression: "if (VAL.match(new RegExp(/(^[0-9]{10}$)/)) && VAL) return true; else if (!VAL) return true; else return false;",
                    message: "Please enter a valid Phone Number"
                });	
        
            $('.profile-ddl-contents').css('display', 'none');
            $('.profile-ddl-type-country').css('display', 'United States');
            $('.profile-ddl-header').toggle(function() {
                toggleContents($(this).parent().find('.profile-ddl-contents'));
            }, function() { toggleContents($(this).parent().find('.profile-ddl-contents')); });

            function toggleContents(el) {
               $('.profile-ddl-contents').css('display', 'none');
                if (el.css('display') == 'none') el.fadeIn("slow");
                else el.fadeOut("slow");
            }
            $('.profile-ddl-contents a').click(function() {
                $(this).parent().parent().find('.profile-ddl-o select').attr('selectedIndex', $('.profile-ddl-contents a').index(this));
                $(this).parent().parent().find('.profile-ddl-title').html($(this).html());
                $(this).parent().parent().find('.profile-ddl-contents').fadeOut("slow");
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
        line-height: 0px;
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

      .cmc-pagingctl-button {
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
      
        #wrapper
        {
            width: 900px;
            height: 700px;
            margin: auto;
            border: solid 1px black;
            -moz-border-radius: 8px;
            -webkit-border-radius: 8px;
            border-radius: 8px;
            -moz-border-radius: 8px;
            -webkit-border-radius: 8px;
            border-radius: 8px;
        }
        #wrapper #header
        {
            /*width: 99%;*/
            height: 40px;
            color: White;
            font-size: 24px;
            font-weight: bold;
            padding-left: 20px;
            padding-top: 20px;
            -moz-border-radius-topleft: 8px;
            -webkit-border-top-left-radius: 8px;
            border-top-left-radius: 8px;
            -moz-border-radius-topright: 8px;
            -webkit-border-top-right-radius: 8px;
            border-top-right-radius: 8px;
            background-image: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#747577), to(#363739));
            background-image: -moz-linear-gradient(#747577, #363739);
            background-image: -webkit-linear-gradient(#747577, #363739);
            background-image: -o-linear-gradient(#747577, #363739);
            filter: "progid:DXImageTransform.Microsoft.gradient(startColorstr=#747577, endColorstr=#363739)";
            -ms-filter: "progid:DXImageTransform.Microsoft.gradient(startColorstr=#747577, endColorstr=#363739)";
        }
        #wrapper #menu-bar
        {
            width: 100%;
            height: 29px;
            padding-top: 4px;
            background-image: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#E5E5E5), to(#CFCFCF));
            background-image: -moz-linear-gradient(#E5E5E5, #CFCFCF);
            background-image: -webkit-linear-gradient(#E5E5E5, #CFCFCF);
            background-image: -o-linear-gradient(#E5E5E5, #CFCFCF);
            filter: "progid:DXImageTransform.Microsoft.gradient(startColorstr=#E5E5E5, endColorstr=#CFCFCF)";
            -ms-filter: "progid:DXImageTransform.Microsoft.gradient(startColorstr=#E5E5E5, endColorstr=#CFCFCF)";
            border-bottom: solid 1px #747577;
        }
        #wrapper #contents
        {
            width: 600px;
            height: 940px;
            padding-top: 10px;
        }
     
        #wrapper #footer
        {
            width: 100%;
            height: 30px;
            background-image: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#747577), to(#363739));
            background-image: -moz-linear-gradient(#747577, #363739);
            background-image: -webkit-linear-gradient(#747577, #363739);
            background-image: -o-linear-gradient(#747577, #363739);
            filter: "progid:DXImageTransform.Microsoft.gradient(startColorstr=#747577, endColorstr=#363739)";
            -ms-filter: "progid:DXImageTransform.Microsoft.gradient(startColorstr=#747577, endColorstr=#363739)";
            -moz-border-radius-bottomleft: 8px;
            -webkit-border-bottom-left-radius: 8px;
            border-bottom-left-radius: 8px;
            -moz-border-radius-bottomright: 8px;
            -webkit-border-bottom-right-radius: 8px;
            border-bottom-right-radius: 8px;
            border-top: solid 1px #747577;
        }
        #menu-bar ul
        {
            list-style: none;
            margin: 0;
            padding: 0;
            margin-left: 4px;
        }
        #menu-bar ul li
        {
            float: left;
            display: inline-block;
            padding: 4px;
        }
        #menu-bar ul li a, #menu-bar ul li a:active, #menu-bar ul li a:visited
        {
            text-decoration: none;
            color: #747577;
        }
        #menu-bar ul li a:hover
        {
            text-decoration: underline;
            color: #747577;
        }
        #footer div
        {
            padding-top: 14px;
            width: 180px;
            margin: auto;
            color: White;
        }
        #footer div a, #footer div a:active, #footer div a:visited
        {
            text-decoration: none;
            color: white;
        }
        #footer div a:hover
        {
            text-decoration: underline;
            color: white;
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
      </ul>
      <div id="ajax-spinner">
        <img src="ajax-spinner.gif" />
      </div>
      <div id="welcome-tab">
        <h1>Welcome to Christian Missions Connector.</h1>
        <p>Are you interested in missions work? Do you want to connect with people and organizations who share your passion for missions? Whether you want to find a missions organization, start a mission team, join a mission team or just connect with others who have a passion for missions, Christian Missions Connector can help.</p>
      </div>
      <div id="profile-tab">
        <div id="make-volunteer">
          <h1>Cool! You're a volunteer.</h1>
          <p>We're excited you're here! Now we'd like to sync with your Facebook profile so we can connect you with mission trips all over the world. We'll also let you know if anyone invites you to join their trip!</p>
          <h1>
            <a href="#" onclick="CMC.page('#make-volunteer', '#make-volunteer-profile');">Make your Profile &gt;&gt;</a>
          </h1>
        </div>
        <div id="edit-profile">
          <h1>Please Update Your Profile</h1>
          <?php
            cmc_big_button(
              "I'm a volunteer",
              "I'm interested in supporting or going on mission trips",
              "CMC.page('#edit-profile', '#make-volunteer');",
              "icon-volunteer.png",
              "padding-top: 5px;",
              65, 65
            );
          ?>
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
           <?php 
                $fbid = 25826994;
                cmc_check_profile_existence($fbid,$profileexists,$con);
                if ($profileexists == 1) {
              ?>
                  
                  <h1>
                  <a href="#" onclick="CMC.page('#no-profile', '#edit-profile');">Edit your Profile Now &gt;&gt;</a>
                </h1>

              <?php
                }
                else {
  
            ?>
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
            <?php
              }
            ?>
        </div>
      </div>
      <div id="trips-tab">
        <p>Proin elit arcu, rutrum commodo, vehicula tempus, commodo a, risus. Curabitur nec arcu. Donec sollicitudin mi sit amet mauris. Nam elementum quam ullamcorper ante. Etiam aliquet massa et lorem. Mauris dapibus lacus auctor risus. Aenean tempor ullamcorper leo. Vivamus sed magna quis ligula eleifend adipiscing. Duis orci. Aliquam sodales tortor vitae ipsum. Aliquam nulla. Duis aliquam molestie erat. Ut et mauris vel pede varius sollicitudin. Sed ut dolor nec orci tincidunt interdum. Phasellus ipsum. Nunc tristique tempus lectus.</p>
      </div>
      <div id="search-tab">
        <div id="search-box">
          <div id="search-tipbar" style="position: relative; height: 16px">
            <div id="search-tipbar-left" style="position: absolute; left: 2px;">
              <a class="tipbar-link" href="#">Search history...</a>
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
            </div>
          </div>
        </div>
        <div id="cmc-search-results-spacer" style="display: block; height: 16px"></div>
        <div id="cmc-search-results">
          <div id="cmc-search-results-title" style="display: none">
            <div id="cmc-search-results-pagingctl">
              <div id="cmc-search-results-pagingctl-prev" class="cmc-pagingctl-button"></div>
              <div id="cmc-search-results-pagingctl-text" class="ui-state-default ui-corner-all">
                <!-- placeholder text, should be localized elsewhere -->
                <span class="ui-button-text">page 0</span>
              </div>
              <div id="cmc-search-results-pagingctl-next" class="cmc-pagingctl-button"></div>
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
      <div id="network-tab">
        <p>Morbi tincidunt, dui sit amet facilisis feugiat, odio metus gravida ante, ut pharetra massa metus id nunc. Duis scelerisque molestie turpis. Sed fringilla, massa eget luctus malesuada, metus eros molestie lectus, ut tempus eros massa ut dolor. Aenean aliquet fringilla sem. Suspendisse sed ligula in ligula suscipit aliquam. Praesent in eros vestibulum mi adipiscing adipiscing. Morbi facilisis. Curabitur ornare consequat nunc. Aenean vel metus. Ut posuere viverra nulla. Aliquam erat volutpat. Pellentesque convallis. Maecenas feugiat, tellus pellentesque pretium posuere, felis lorem euismod felis, eu ornare leo nisi vel felis. Mauris consectetur tortor et purus.</p>
      </div>
      <div id="invite-tab">
        <p>Mauris eleifend est et turpis. Duis id erat. Suspendisse potenti. Aliquam vulputate, pede vel vehicula accumsan, mi neque rutrum erat, eu congue orci lorem eget lorem. Vestibulum non ante. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Fusce sodales. Quisque eu urna vel enim commodo pellentesque. Praesent eu risus hendrerit ligula tempus pretium. Curabitur lorem enim, pretium nec, feugiat nec, luctus a, lacus.</p>
        <p>Duis cursus. Maecenas ligula eros, blandit nec, pharetra at, semper at, magna. Nullam ac lacus. Nulla facilisi. Praesent viverra justo vitae neque. Praesent blandit adipiscing velit. Suspendisse potenti. Donec mattis, pede vel pharetra blandit, magna ligula faucibus eros, id euismod lacus dolor eget odio. Nam scelerisque. Donec non libero sed nulla mattis commodo. Ut sagittis. Donec nisi lectus, feugiat porttitor, tempor ac, tempor vitae, pede. Aenean vehicula velit eu tellus interdum rutrum. Maecenas commodo. Pellentesque nec elit. Fusce in lacus. Vivamus a libero vitae lectus hendrerit hendrerit.</p>
      </div>
    </div>
    <div id="cmc-footer" style="display: none">
      <div class="leftside">
        <a href="#" id="secret-hideout">Admin</a> :
        <a href="#" id="report-problem">Report a Problem</a> : <a href="#" id="contact-link">Contact Us</a>
      </div>
      <div class="rightside">
        <a href="#" id="copyrights">Copyrights</a> :
        <a href="http://www.tangiblesoft.net/" target="_blank" id="tangible-link">Tangible, LLC</a>
      </div>
    </div>
    <!-- Dialogs and such should go here -->
    <div id="dialogs" style="display: none">
      <div id="copyrights-dialog" title="Copyrights">
        <p>Christian Missions Connector is Copyright 2009-2011 Tangible, LLC. All rights reserved.</p>
        <p>Christian Missions Connector uses the jQuery and jQuery UI libraries. For more information, visit <a href="http://www.jquery.com" target="_blank">www.jquery.com</a>.</p>
        <p>Portions adapted from FCBKcomplete 2.7.5 and TipTip 1.3. FCBKcomplete is Copyright 2010 Emposha (<a href="http://www.emposha.com" target="_blank">www.emposha.com</a>). TipTip is Copyright 2011 Drew Wilson (<a href="http://code.drewwilson.com/entry/tiptip-jquery-plugin" target="_blank">code.drewwilson.com</a>). Both are used and modified with permission under the <a href="http://www.opensource.org/licenses/mit-license.php" target="_blank">MIT license.</a></p>
        <p>Contains content obtained from The Noun Project (<a href="http://www.thenounproject.com" target="_blank">www.thenounproject.com</a>). "Community" reproduced under the Creative Commons Attribution 3.0 Unported license. For licensing information, please visit <a href="http://creativecommons.org/licenses/by/3.0/" target="_blank">http://creativecommons.org/licenses/by/3.0/</a>.</p>
        <p>"Arriving Flights" by Roger Cook and Don Shanosky, 1974. Obtained from the public domain. </p>
      </div>
      <div id="profile-volunteer-dialog" title="Please enter your profile information">
        <form id="profile-volunteer-form">
      <div id="wrapper">
        <div id="header">
            CMC Profile Submission
        </div>
        <div id="contents">
            <div class="profile-container">
                <div class="profile-header">
                    Please enter your profile information</div>
                <div class="profile-contents">
                    <table cellpadding="4" cellspacing="0">
                        <tr>
                            <td style="width: 97px">
                                <label>
                                    Medical Skills</label>
                            </td>
                            <td style="width: 97px">
                                        <select id="profile-medical" multiple="multiple" class="profile-ddl-type-medical">
											<option value="0" selected="selected">Select Medical Skills</option>
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
                            <td style="width: 97px">
                                <label>
                                    Non-Medical Skills</label>
                            </td>
                            <td style="width: 197px">
                                        <select id="profile-nonmedical" multiple="multiple" class="profile-ddl-type-nonmedical">
											<option value="0" selected="selected">Select Non-Medical Skills</option>
                                            <option value="1">General Help/Labor</option>
                                            <option value="2">Team Leader/Primary Organizer</option>
                                            <option value="3">Account and/or Business Management</option>
                                            <option value="4">Skilled Construction and/or Maintenance</option>
                                            <option value="5">Computer Science/Other Technical</option>
                                            <option value="6">Agriculture and/or Animal Husbandry</option>
                                            <option value="7">Mechanic</option>
                                            <option value="8">Office/Secretarial</option>
                                            <option value="9">Teaching</option>
                                            <option value="10">Veterinary</option>
                                            <option value="11">Water Supply Improvement</option>
                                            <option value="12">Writing and/or Translating</option>
                                            <option value="13">Engineering</option>
                                        </select>
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 97px">
                                <label>
                                    Spiritual Service</label>
                            </td>
                            <td style="width: 197px">
                                        <select id="profile-spiritual" class="profile-ddl-type-spiritual">
											<option value="0" selected="selected">Select Spiritual Service</option>
                                            <option value="1">Team Spiritual Leader</option>
                                            <option value="2">Individual Outreach (Prayer and Counseling)</option>
                                            <option value="3">Evangelism</option>
                                            <option value="4">Worship Team</option>
                                            <option value="5">Public Speaking</option>
                                        </select>
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 97px">
                                <label>
                                    Religious Affiliation</label>
                            </td>
                            <td style="width: 197px">
                                        <select id="profile-religion" class="profile-ddl-type-religious">
											<option value="0", selected="selected">Select Religious Affiliation</option>
                                            <option value="1">Secular</option>
                                            <option value="2">Christian: Protestant</option>
                                            <option value="3">Christian: Catholic</option>
                                            <option value="4">Nondenominational</option>
                                        </select>
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 97px">
                                <label>
                                    Duration of Missions</label>
                            </td>
                            <td style="width: 197px">
                                        <select id="profile-duration" class="profile-ddl-type-duration">
											<option value="0" selected="selected">Select Duration of Missions</option>
                                            <option value="1">Short Term: 1-2 weeks</option>
                                            <option value="2">Medium Term: 1 Month-2 Years</option>
                                            <option value="3">Long Term: 2+ Years</option>
                                        </select>
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 197px">
                                <label>
                                    State</label>
                            </td>
                            <td style="width: 197px">
                                        <select id="profile-state" class="profile-ddl-type-state">
                                        <?php
											echo '<option value="Select your State" selected="selected">Select your State</option>';
											foreach($usstates as $key => $state) {
                                              echo '<option value="'.$state.'">'.$state.'</option>';
											}
                                        ?>
                                        </select>
                            </td>
                        </tr>						
                        <tr>
                            <td>
                                <label>
                                    City</label>
                            </td>
                            <td>
                                <input type="text" id="profile-city" class="profile-input-city"/>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label>
                                    Zipcode</label>
                            </td>
                            <td>
                                <input type="text" id="profile-zipcode" class="profile-input-zipcode"/>
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 197px">
                                <label>
                                    Country</label>
                            </td>
                            <td style="width: 197px">
                                        <select id="profile-country" class="profile-ddl-type-country">
                                        <?php
											foreach($aCountries as $key => $country) {
                                              if ($country == "United States")
												    echo '<option selected="selected" value="'.$country.'">'.$country.'</option>';
                                              else
												    echo '<option value="'.$country.'">'.$country.'</option>';

											}
                                        ?>
                                        </select>
                            </td>
                        </tr>							
                        <tr>
                            <td style="width: 197px">
                                <label>
                                    Regions of Interest</label>
                            </td>
                            <td style="width: 197px">
                                        <select id="profile-region" class="profile-ddl-type-region">
											<option value="0" selected="selected">Select Regions of Interest</option>
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
                            <td style="width: 197px">
                                <label>
                                    Countries Served</label>
                            </td>
                            <td style="width: 197px">
                                        <select id="profile-country-served" class="profile-ddl-type-countriesserved">
                                        <?php
											echo '<option selected="selected" value="Select Countries Served">Select Countries Served</option>';
											foreach($aCountries as $key => $country) {											
												echo '<option value="'.$country.'">'.$country.'</option>';
											}
                                        ?>
                                        </select>
                            </td>
                        </tr>						
                        <tr>
                            <td>
                                <label>
                                    Phone</label>
                            </td>
                            <td>
                                <input type="text" id="profile-phone" class="profile-input-phone" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label>
                                    Email Id</label>
                            </td>
                            <td>
                                <input type="text" id="profile-email" class="profile-input-email" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label>
                                    My Missions Experience</label>
                            </td>
                            <td>
                                <input type="text" id="profile-experience" class="profile-input-experience" />
                            </td>
                        </tr>							
                        <tr>
                            <td>&nbsp;
                                
                            </td>
                            <td>
                                <input type="submit" value="Submit" class="profile-submit" id="profile-submit" />
                            </td>
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
          <div id="report-problem-characters-left" class="ui-state-disabled">
            300 characters left<!-- just some placeholder text -->
          </div>
        </form>
      </div>
      <div id="secret-hideout-dialog" title="Administration">
        <p>This is an area for magical unicorns and rainbows.</p>
      </div>
    </div>
    <!-- The debug log. Should not be displayed by default. Enable via the admin panel. -->
    <div id="debug-section" style="display: none">
      <textarea id="debug-log" rows="10" cols="80" spellcheck="false">
      Please wait, loading debug console...
      </textarea>
      <div id="requests-outstanding">
        requests outstanding: <span id="requests-outstanding-value">0</span>
      </div>
      <div id="debug-controls">
        <button id="debug-detach-handlers">detach debug handlers</button>
      </div>
    </div>
    <!-- Do not place HTML markup below this line -->
  </body>
</html>
<!-- vim: ai:et:ts=2:sw=2
-->
