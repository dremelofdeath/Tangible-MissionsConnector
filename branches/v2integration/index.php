<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<?php

include_once 'api/common.php';
$con = arena_connect();

  $aCountries = array(
"United States",
"Afghanistan",
"Aland Islands",
"Albania",
"Algeria",
"American Samoa",
"Andorra",
"Angola",
"Anguilla",
"Antarctica",
"Antigua and Barbuda",
"Argentina",
"Armenia",
"Aruba",
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
  "Bermuda",
  "Bhutan",
  "Bolivia",
  "Bosnia and Herzegovina",
  "Botswana",
  "Bouvet Island",
  "Brazil",
  "British Indian Ocean Territory",
  "Brunei Darussalam",
  "Bulgaria",
  "Burkina Faso",
  "Burundi",
  "Cambodia",
  "Cameroon",
  "Canada",
  "Cape Verde",
  "Cayman Islands",
  "Central African Republic",
  "Chad",
  "Chile",
  "China",
  "Christmas Island",
  "Cocos (Keeling) Islands",
  "Colombia",
  "Comoros",
  "Congo",
  "Congo, the Democratic Republic of th",
  "Cook Islands",
  "Costa Rica",
  "Cote D'Ivoire",
  "Croatia",
  "Cuba",
  "Cyprus",
  "Czech Republic",
  "Denmark",
  "Djibouti",
  "Dominica",
  "Dominican Republic",
  "Ecuador",
  "Egypt",
  "El Salvador",
  "Equatorial Guinea",
  "Eritrea",
  "Estonia",
  "Ethiopia",
  "Falkland Islands (Malvinas)",
  "Faroe Islands",
  "Fiji",
  "Finland",
  "France",
  "French Guiana",
  "French Polynesia",
  "French Southern Territories",
  "Gabon",
  "Gambia",
  "Georgia",
  "Germany",
  "Ghana",
  "Gibraltar",
  "Greece",
  "Greenland",
  "Grenada",
  "Guadeloupe",
  "Guam",
  "Guatemala",
  "Guinea",
  "Guinea-Bissau",
  "Guyana",
  "Haiti",
  "Heard and McDonald Islands",
  "Holy See (Vatican City State",
  "Honduras",
  "Hong Kong",
  "Hungary",
  "Iceland",
  "India",
  "Indonesia",
  "Iran, Islamic Republic o",
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
  "Korea, Democratic People's Republic of",
  "Korea, Republic of",
  "Kuwait",
  "Kyrgyzstan",
  "Lao People's Democratic Republic",
  "Latvia",
  "Lebanon",
  "Lesotho",
  "Liberia",
  "Libyan Arab Jamahiriya",
  "Liechtenstein",
  "Lithuania",
  "Luxembourg",
  "Macao",
  "Macedonia, the former Yugoslav Republic of",
  "Madagascar",
  "Malawi",
  "Malaysia",
  "Maldives",
  "Mali",
  "Malta",
  "Marshall Islands",
  "Martinique",
  "Mauritania",
  "Mauritius",
  "Mayotte",
  "Mexico",
  "Micronesia, Federated States of",
  "Moldova, Republic of",
  "Monaco",
  "Mongolia",
  "Montserrat",
  "Morocco",
  "Mozambique",
  "Myanmar",
  "Namibia",
  "Nauru",
  "Nepal",
  "Netherlands",
  "Netherlands Antilles",
  "New Caledonia",
  "New Zealand",
  "Nicaragua",
  "Niger",
  "Nigeria",
  "Niue",
  "Norfolk Island",
  "Northern Mariana Islands",
  "Norway",
  "Oman",
  "Pakistan",
  "Palau",
  "Palestinian Territory, Occupie",
  "Panama",
  "Papua New Guinea",
  "Paraguay",
  "Peru",
  "Philippines",
  "Pitcairn",
  "Poland",
  "Portugal",
  "Puerto Rico",
  "Qatar",
  "Reunion",
  "Romania",
  "Russian Federation",
  "Rwanda",
  "Saint Helena",
  "Saint Kitts and Nevis",
  "Saint Lucia",
  "Saint Pierre and Miquelon",
  "Saint Vincent and the Grenadines",
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
  "South Georgia and the South Sandwich Island",
  "Spain",
  "Sri Lanka",
  "Sudan",
  "Suriname",
  "Svalbard and Jan Mayen",
  "Swaziland",
  "Sweden",
  "Switzerland",
  "Syrian Arab Republic",
  "Taiwan",
  "Tajikistan",
  "Tanzania, United Republic of",
  "Thailand",
  "Timor-Lest",
  "Togo",
  "Tokelau",
  "Tonga",
  "Trinidad and Tobago",
  "Tunisia",
  "Turkey",
  "Turkmenistan",
  "Turks and Caicos Islands",
  "Tuvalu",
  "Uganda",
  "Ukraine",
  "United Arab Emirates",
  "United Kingdom",
  "United States Minor Outlying Islands",
  "Uruguay",
  "Uzbekistan",
  "Vanuatu",
  "Venezuela",
  "Vietnam",
  "Virgin Islands (British)",
  "Virgin Islands (U.S.)",
  "Wallis And Futuna Islands",
  "Western Sahara",
  "Yemen",
  "Zambia",
  "Zimbabwe"
  );
  
  $usstates = array(
  "Alabama",
  "Alaska",
  "Arizona",
    "Arkansas",
    "California",
    "Colorado",
    "Connecticut",
    "Delaware",
    "District of Columbia",
    "Florida",
    "Georgia",
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
    "Ohio",
    "Oklahoma",
    "Oregon",
    "Pennsylvania",
    "Rhode Island",
    "South Carolina",
    "South Dakota",
    "Tennessee",
    "Texas",
    "Utah",
    "Vermont",
    "Virginia",
    "Washington",
    "West Virginia",
    "Wisconsin",
    "Wyoming"
  );
 
  $languages = array(
"Afar",
"Abkhazian",
"Achinese",
"Acoli",
"Adangme",
"Adyghe; Adygei",
"Afro-Asiatic languages",
"Afrihili",
"Afrikaans",
"Ainu",
"Akan",
"Akkadian",
"Aleut",
"Algonquian languages",
"Southern Altai",
"Amharic",
"English, Old (ca.450-1100)",
"Angika",
"Apache languages",
"Arabic",
  "Official Aramaic (700-300 BCE); Imperial Aramaic (700-300 BCE)",
  "Aragonese",
  "Armenian",
  "Mapudungun; Mapuche",
  "Arapaho",
  "Artificial languages",
  "Arawak",
  "Assamese",
  "Asturian; Bable; Leonese; Asturleonese",
  "Athapascan languages",
  "Australian languages",
  "Avaric",
  "Avestan",
  "Awadhi",
  "Aymara",
  "Azerbaijani",
  "Banda languages",
  "Bamileke languages",
  "Bashkir",
  "Baluchi",
  "Bambara",
  "Balinese",
  "Basque",
  "Basa",
  "Baltic languages",
  "Beja; Bedawiyet",
  "Belarusian",
  "Bemba",
  "Bengali",
  "Berber languages",
  "Bhojpuri",
  "Bihari",
  "Bikol",
  "Bini; Edo",
  "Bislama",
  "Siksika",
  "Bantu languages",
  "Tibetan",
  "Bosnian",
  "Braj",
  "Breton",
  "Batak languages",
  "Buriat",
  "Buginese",
  "Bulgarian",
  "Blin; Bilin",
  "Caddo",
  "Central American Indian languages",
  "Galibi Carib",
  "Catalan; Valencian",
  "Caucasian languages",
  "Cebuano",
  "Celtic languages",
  "Chamorro",
  "Chibcha",
  "Chechen",
  "Chagatai",
  "Chinese",
  "Chuukese",
  "Mari",
  "Chinook jargon",
  "Choctaw",
  "Chipewyan; Dene Suline",
  "Cherokee",
  "Church Slavic; Old Slavonic; Church Slavonic; Old Bulgarian; Old Church Slavonic",
  "Chuvash",
  "Cheyenne",
  "Chamic languages",
  "Coptic",
  "Cornish",
  "Corsican",
  "Creoles and pidgins, English based",
  "Creoles and pidgins, French-based",
  "Creoles and pidgins, Portuguese-based",
  "Cree",
  "Crimean Tatar; Crimean Turkish",
  "Creoles and pidgins",
  "Kashubian",
  "Cushitic languages",
  "Welsh",
  "Czech",
  "Dakota",
  "Danish",
  "Dargwa",
  "Land Dayak languages",
  "Delaware",
  "Slave (Athapascan)",
  "Dogrib",
  "Dinka",
  "Divehi; Dhivehi; Maldivian",
  "Dogri",
  "Dravidian languages",
  "Lower Sorbian",
  "Duala",
  "Dutch, Middle (ca.1050-1350)",
  "Dyula",
  "Dzongkha",
  "Efik",
  "Egyptian (Ancient)",
  "Ekajuk",
  "Elamite",
  "English",
  "English, Middle (1100-1500)",
  "Esperanto",
  "Estonian",
  "Ewe",
  "Ewondo",
  "Fang",
  "Faroese",
  "Fanti",
  "Fijian",
  "Filipino; Pilipino",
  "Finnish",
  "Finno-Ugrian languages",
  "Fon",
  "French",
  "French, Middle (ca.1400-1600)",
  "French, Old (842-ca.1400)",
  "Northern Frisian",
  "Eastern Frisian",
  "Western Frisian",
  "Fulah",
  "Friulian",
  "Ga",
  "Gayo",
  "Gbaya",
  "Germanic languages",
  "Georgian",
  "German",
  "Geez",
  "Gilbertese",
  "Gaelic; Scottish Gaelic",
  "Irish",
  "Galician",
  "Manx",
  "German, Middle High (ca.1050-1500)",
  "German, Old High (ca.750-1050)",
  "Gondi",
  "Gorontalo",
  "Gothic",
  "Grebo",
  "Greek, Ancient (to 1453)",
  "Greek, Modern (1453-)",
  "Guarani",
  "Swiss German; Alemannic; Alsatian",
  "Gujarati",
  "Gwich'in",
  "Haida",
  "Haitian; Haitian Creole",
  "Hausa",
  "Hawaiian",
  "Hebrew",
  "Herero",
  "Hiligaynon",
  "Himachali",
  "Hindi",
  "Hittite",
  "Hmong",
  "Hiri Motu",
  "Croatian",
  "Upper Sorbian",
  "Hungarian",
  "Hupa",
  "Iban",
  "Igbo",
  "Icelandic",
  "Ido",
  "Sichuan Yi; Nuosu",
  "Ijo languages",
  "Inuktitut",
  "Interlingue; Occidental",
  "Iloko",
  "Interlingua (International Auxiliary Language Association)",
  "Indic languages",
  "Indonesian",
  "Indo-European languages",
  "Ingush",
  "Inupiaq",
  "Iranian languages",
  "Iroquoian languages",
  "Italian",
  "Javanese",
  "Lojban",
  "Japanese",
  "Judeo-Persian",
  "Judeo-Arabic",
  "Kara-Kalpak",
  "Kabyle",
  "Kachin; Jingpho",
  "Kalaallisut; Greenlandic",
  "Kamba",
  "Kannada",
  "Karen languages",
  "Kashmiri",
  "Kanuri",
  "Kawi",
  "Kazakh",
  "Kabardian",
  "Khasi",
  "Khoisan languages",
  "Central Khmer",
  "Khotanese; Sakan",
  "Kikuyu; Gikuyu",
  "Kinyarwanda",
  "Kirghiz; Kyrgyz",
  "Kimbundu",
  "Konkani",
  "Komi",
  "Kongo",
  "Korean",
  "Kosraean",
  "Kpelle",
  "Karachay-Balkar",
  "Karelian",
  "Kru languages",
  "Kurukh",
  "Kuanyama; Kwanyama",
  "Kumyk",
  "Kurdish",
  "Kutenai",
  "Ladino",
  "Lahnda",
  "Lamba",
  "Lao",
  "Latin",
  "Latvian",
  "Lezghian",
  "Limburgan; Limburger; Limburgish",
  "Lingala",
  "Lithuanian",
  "Mongo",
  "Lozi",
  "Luxembourgish; Letzeburgesch",
  "Luba-Lulua",
  "Luba-Katanga",
  "Ganda",
  "Luiseno",
  "Lunda",
  "Luo (Kenya and Tanzania)",
  "Lushai",
  "Macedonian",
  "Madurese",
  "Magahi",
  "Marshallese",
  "Maithili",
  "Makasar",
  "Malayalam",
  "Mandingo",
  "Maori",
  "Austronesian languages",
  "Marathi",
  "Masai",
  "Moksha",
  "Mandar",
  "Mende",
  "Irish, Middle (900-1200)",
  "Mi'kmaq; Micmac",
  "Minangkabau",
  "Uncoded languages",
  "Mon-Khmer languages",
  "Malagasy",
  "Maltese",
  "Manchu",
  "Manipuri",
  "Manobo languages",
  "Mohawk",
  "Mongolian",
  "Mossi",
  "Malay",
  "Multiple languages",
  "Munda languages",
  "Creek",
  "Mirandese",
  "Marwari",
  "Burmese",
  "Mayan languages",
  "Erzya",
  "Nahuatl languages",
  "North American Indian languages",
  "Neapolitan",
  "Nauru",
  "Navajo; Navaho",
  "Ndebele, South; South Ndebele",
  "Ndebele, North; North Ndebele",
  "Ndonga",
  "Low German; Low Saxon; German, Low; Saxon, Low",
  "Nepali",
  "Nepal Bhasa; Newari",
  "Nias",
  "Niger-Kordofanian languages",
  "Niuean",
  "Dutch; Flemish",
  "Norwegian Nynorsk; Nynorsk, Norwegian",
  "Bokmål, Norwegian; Norwegian Bokmål",
  "Nogai",
  "Norse, Old",
  "Norwegian",
  "N'Ko",
  "Pedi; Sepedi; Northern Sotho",
  "Nubian languages",
  "Classical Newari; Old Newari; Classical Nepal Bhasa",
  "Chichewa; Chewa; Nyanja",
  "Nyamwezi",
  "Nyankole",
  "Nyoro",
  "Nzima",
  "Occitan (post 1500)",
  "Ojibwa",
  "Oriya",
  "Oromo",
  "Osage",
  "Ossetian; Ossetic",
  "Turkish, Ottoman (1500-1928)",
  "Otomian languages",
  "Papuan languages",
  "Pangasinan",
  "Pahlavi",
  "Pampanga; Kapampangan",
  "Panjabi; Punjabi",
  "Papiamento",
  "Palauan",
  "Persian, Old (ca.600-400 B.C.)",
  "Persian",
  "Philippine languages",
  "Phoenician",
  "Pali",
  "Polish",
  "Pohnpeian",
  "Portuguese",
  "Prakrit languages",
  "Provençal, Old (to 1500);Occitan, Old (to 1500)",
  "Pushto; Pashto",
  "Reserved for local use",
  "Quechua",
  "Rajasthani",
  "Rapanui",
  "Rarotongan; Cook Islands Maori",
  "Romance languages",
  "Romansh",
  "Romany",
  "Romanian; Moldavian; Moldovan",
  "Rundi",
  "Aromanian; Arumanian; Macedo-Romanian",
  "Russian",
  "Sandawe",
  "Sango",
  "Yakut",
  "South American Indian languages",
  "Salishan languages",
  "Samaritan Aramaic",
  "Sanskrit",
  "Sasak",
  "Santali",
  "Sicilian",
  "Scots",
  "Selkup",
  "Semitic languages",
  "Irish, Old (to 900)",
  "Sign Languages",
  "Shan",
  "Sidamo",
  "Sinhala; Sinhalese",
  "Siouan languages",
  "Sino-Tibetan languages",
  "Slavic languages",
  "Slovak",
  "Slovenian",
  "Southern Sami",
  "Northern Sami",
  "Sami languages",
  "Lule Sami",
  "Inari Sami",
  "Samoan",
  "Skolt Sami",
  "Shona",
  "Sindhi",
  "Soninke",
  "Sogdian",
  "Somali",
  "Songhai languages",
  "Sotho, Southern",
  "Spanish; Castilian",
  "Albanian",
  "Sardinian",
  "Sranan Tongo",
  "Serbian",
  "Serer",
  "Nilo-Saharan languages",
  "Swati",
  "Sukuma",
  "Sundanese",
  "Susu",
  "Sumerian",
  "Swahili",
  "Swedish",
  "Classical Syriac",
  "Syriac",
  "Tahitian",
  "Tai languages",
  "Tamil",
  "Tatar",
  "Telugu",
  "Timne",
  "Tereno",
  "Tetum",
  "Tajik",
  "Tagalog",
  "Thai",
  "Tigre",
  "Tigrinya",
  "Tiv",
  "Tokelau",
  "Klingon; tlhIngan-Hol",
  "Tlingit",
  "Tamashek",
  "Tonga (Nyasa)",
  "Tonga (Tonga Islands)",
  "Tok Pisin",
  "Tsimshian",
  "Tswana",
  "Tsonga",
  "Turkmen",
  "Tumbuka",
  "Tupi languages",
  "Turkish",
  "Altaic languages",
  "Tuvalu",
  "Twi",
  "Tuvinian",
  "Udmurt",
  "Ugaritic",
  "Uighur; Uyghur",
  "Ukrainian",
  "Umbundu",
  "Undetermined",
  "Urdu",
  "Uzbek",
  "Vai",
  "Venda",
  "Vietnamese",
  "Volapük",
  "Votic",
  "Wakashan languages",
  "Wolaitta; Wolaytta",
  "Waray",
  "Washo",
  "Sorbian languages",
  "Walloon",
  "Wolof",
  "Kalmyk; Oirat",
  "Xhosa",
  "Yao",
  "Yapese",
  "Yiddish",
  "Yoruba",
  "Yupik languages",
  "Zapotec",
  "Blissymbols; Blissymbolics; Bliss",
  "Zenaga",
  "Zhuang; Chuang",
  "Zande languages",
  "Zulu",
  "Zuni",
  "No linguistic content; Not applicable",
  "Zaza; Dimili; Dimli; Kirdki; Kirmanjki; Zazaki"
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
    <link rel="stylesheet" type="text/css" href="style.css" />
	  <script src="datepicker.js" type="text/javascript"></script>
	  <link href="datepicker.css" rel="stylesheet" type="text/css" />
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

        CMC.log("admin load complete");
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

      .entity-title {
        font-size: 2em;
        float: left;
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

      /* the stuff below here appears to be stylization for edit profile dialogs 
       * --zack
       */
      
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
  
  <script type="text/JavaScript">
  
  var currentOptions = new Array(7);
  for (i=0; i <7; i++) {
    currentOptions[i]=new Array()   
  }
  var current = new Array(7);
  /*
  var prelength = new Array(6);
  var postlen = new Array(6);

  for (i=0;i<6;i++) {
    prelength[i] = 0;
    postlen[i] = 0;
  }
  */

  function selectMultiple(s,k)
  {
  current[k] = s.selectedIndex;
  
  for (var i=0; i<currentOptions[k].length; i++)
  {
    if (current[k] == currentOptions[k][i])
    {
      CMC.prelength[k] = currentOptions[k].length;
      currentOptions[k].splice(i, 1);
      CMC.postlen[k] = currentOptions[k].length;
      break;
    }
  }
  
  if ((CMC.prelength[k]==0) || (CMC.prelength[k]!=CMC.postlen[k]+1)) {
    if (i >= currentOptions[k].length) currentOptions[k].push(current[k]);
  }

  // reinitialize the lengths
  if (currentOptions[k].length == 0) {
    CMC.prelength[k] = 0;
    CMC.prelength[k] = 0;
  }
      
  for (var i=0; i<s.options.length; i++) s.options[i].selected = false;
  for (var i=0; i<currentOptions[k].length; i++) s.options[currentOptions[k][i]].selected = true;
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
        <div id="make-organizer">
          <h1>Awesome! You're an organizer.</h1>
          <p>It's great to have you onboard! We'd like to take a chance to sync with your Facebook profile so we can connect you to volunteers all over the world. If you have a Facebook page, you can link that too. We'll be sure to let you know when people join your trips!</p>
          <h1>
            <a href="#" onclick="CMC.page('#make-organizer', '#make-org-profile');">Make your Profile &gt;&gt;</a>
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
              <div id="profile-image">
                <img class="profile-picture" src="ajax-spinner.gif" />
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
                <span id="profile-name" class="entity-title">Sample Long Name</span>
                <div id="profile-controls" class="entity-controls">
                  <div id="profile-controls-spacer" class="entity-control-spacer"></div>
                  <div id="profile-controls-edit" class="entity-control cmc-square-button"></div>
                  <div id="profile-controls-create-trip" class="entity-control cmc-square-button"></div>
                  <div id="profile-controls-back-to-my-profile" class="entity-control cmc-square-button"></div>
                </div>
              </div>
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
                <h5> Email: </h5>
                <div id="profile-email">
                  <h6> Email </h6>
                </div>		
                <h5> Phone: </h5>
                <div id="profile-phone">
                  <h6> Phone </h6>
                </div>	
                <h5> Country: </h5>
                <div id="profile-country">
                  <h6> Country </h6>
                </div>	
                <h5> Zip: </h5>
                <div id="profile-zip">
                  <h6> Zip </h6>
                </div>	
                <h5> Preferred Duration of Mission Trips: </h5>
                <div id="profile-dur">
                  <h6> Duration </h6>
                </div>	
                <h5> Countries of Interest: </h5>
                <div id="profile-countries">
                  <h6> Countries </h6>
                </div>	
              </div>
              <h3>Trips Information:</h3>
              <div id="table_wrapper">
                <div id="tbody">
                  <table>
                    <tr>
                      <td>
                        <div class="profile-picture">
                          <img src="ajax-spinner.gif" alt="" height="35"/>
                        </div>
                      </td>
                      <td>
                        <div class="box3 profile-tripname">
                          TripName 
                        </div>						
                      </td>
                      <td class="td2"><input type="submit" value="Trip Description" class="button" id="trip-desc-submit" /></td>
                      <td class="td2"><input type="submit" value="Join This Trip" class="button" id="join-trip-submit" /></td>
                    </tr>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div id="no-profile" style="display: none">
          <script type="text/javascript">
            CMC.profileedit = 0;
          </script>
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
			<div id="backtotrips" style="display: none">
				<a href="#" onclick="CMC.getFutureTrips();">&lt;&lt; Go back to Upcoming Trips </a>
			</div>			
			<div id="trip-profile-left-column">
        <div id="tripprofileimage">
          <div class="trip-owner-picture">
            <img src="ajax-spinner.gif" width="190" />
          </div>
        </div>
        <div class="box2">
          Trip Owner:
          <div class="profile-trip-owner">Name
          </div>
        </div>
        <div class="box2">
          <h3>
            Trip Description: <br />
          </h3>
          <div class="trip-profile-about">
            <h4> About </h4>
          </div>
        </div>
			</div>
			<div id="trip-profile-right-column">
				<div class="box1">
					<h2>Trip Information:</h2>
					<h5> Trip Name: </h5>
					<div class="profile-trip-name">
						<h6> Trip Name </h6> 
					</div>
					<h5> Trip Website: </h5>
					<div class="profile-trip-url">
						<h6> Trip Website </h6>
					</div>
					<h5> Trip Destination: </h5>
					<div class="profile-trip-dest">
						<h6> Trip Destination </h6>
					</div>					
					<h5> Email: </h5>
					<div class="profile-trip-email">
						<h6> Email </h6>
					</div>		
					<h5> Phone: </h5>
					<div class="profile-trip-phone">
						<h6> Phone </h6>
					</div>	
					<h5> Execution Stage: </h5>
					<div class="profile-trip-stage">
						<h6> Trip Execution Stage </h6>
					</div>	
					<h5> Date of Departure: </h5>
					<div class="profile-trip-depart">
						<h6> Departure Date </h6>
					</div>	
					<h5> Date of Return </h5>
					<div class="profile-trip-return">
						<h6> Return </h6>
					</div>	
					<h5> Trip Religion: </h5>
					<div class="profile-trip-religion">
						<h6> Trip religion </h6>
					</div>
					<h5> Trip Accommodation Level: </h5>
					<div class="profile-trip-acco">
						<h6> Trip Accommodation Level </h6>
					</div>	
					<h5> Number of People involved in this Trip: </h5>
					<div class="profile-trip-numpeople">
						<h6> Number of people </h6>
					</div>						
				</div>
			</div>
		</div>
        </div>		 
		 <div id="show-trips" style="display: none">
        <br /><br/>
				<h2>Upcoming Trips:</h2>
				<table>	
				<tr>
				<td>
				<div class="trips-tripname">
					TripName 
				</div>						
				</td>	
				<td class="td2"><input type="submit" value="Trip Description" class="button" id="trips-desc-submit" /></td>
				<td class="td2"><input type="submit" value="Join This Trip" class="button" id="join-trips-submit" /></td>
				</tr>
				</table>
		 </div>		 
         <div id="no-trip" style="display: none">
          <h1>Currently there are no trips scheduled to begin in the future. But, you can create a missions trip very quickly</h1>
          <h1>
            <a href="#" onclick="CMC.page('#no-trip', '#make-trip');">Create a Trip &gt;&gt;</a>
          </h1>
          </div>
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
                                        <select id="profile-medical" multiple="multiple" class="profile-ddl-type-medical" onclick="selectMultiple(this,0);">
                                            <!--<option value="0" selected="selected">Select Medical Skills</option>-->
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
                                        <select id="profile-nonmedical" multiple="multiple" class="profile-ddl-type-nonmedical" onclick="selectMultiple(this,1);">
                                            <!--<option value="0" selected="selected">Select Non-Medical Skills</option>-->
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
                            <td style="width: 97px">
                                <label>
                                    Spiritual Service</label>
                            </td>
                            <td style="width: 197px">
                                        <select id="profile-spiritual" multiple="multiple" class="profile-ddl-type-spiritual" onclick="selectMultiple(this,2);">
                                            <!--<option value="0" selected="selected">Select Spiritual Service</option>-->
                                            <option value="20">Team Spiritual Leader</option>
                                            <option value="21">Individual Outreach (Prayer and Counseling)</option>
                                            <option value="22">Evangelism</option>
                                            <option value="44">Worship Team</option>
                                            <option value="51">Public Speaking</option>
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
                                            <!--<option value="0", selected="selected">Select Religious Affiliation</option>-->
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
                                            <!--<option value="0" selected="selected">Select Duration of Missions</option>-->
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
                                        $i=1;
                      foreach($usstates as $key => $state) {
                                              echo '<option value="'.$i.'">'.$state.'</option>';
                                              $i++;
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
                                        $i=1;
                      foreach($aCountries as $key => $country) {
                                              if ($country == "United States")
                            echo '<option selected="selected" value="'.$i.'">'.$country.'</option>';
                                              else {
                            echo '<option value="'.$i.'">'.$country.'</option>';
                                              }
                                              $i++;
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
                                        <select id="profile-region" multiple="multiple" class="profile-ddl-type-region" onclick="selectMultiple(this,3);">
                                            <!--<option value="0" selected="selected">Select Regions of Interest</option>-->
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
                                        <select id="profile-country-served" multiple="multiple" class="profile-ddl-type-countriesserved" onclick="selectMultiple(this,4);">
                                        <?php
                                        $i=1;
                      //echo '<option selected="selected" value="Select Countries Served">Select Countries Served</option>';
                      foreach($aCountries as $key => $country) {                      
                                              if ($country == "United States")
                            echo '<option value="'.$i.'">'.$country.'</option>';
                                              else {
                        echo '<option value="'.$i.'">'.$country.'</option>';
                                              }
                                              $i++;
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
      <div id="profile-organizer-dialog" title="Please enter your profile information">
        <form id="profile-organizer-form">
      <div id="wrapper">
        <div id="header">
            CMC Profile Submission
        </div>
        <div id="contents">
            <div class="profile-container">
                <div class="profile-header">
                    Please enter your profile information</div>
                <div class="profile-contents">
                    <table id="orgtable" cellpadding="4" cellspacing="0">
                        <tr>
                            <td>
                                <label>
                                    Agency/Mission Name</label>
                            </td>
                            <td>
                                <input type="text" id="profile-org-name" class="profile-org-name" />
                            </td>
                        </tr>  
                        <tr>
                            <td>
                                <label>
                                    Agency/Mission Website</label>
                            </td>
                            <td>
                                <input type="text" id="profile-org-website" class="profile-org-website" />
                            </td>
                        </tr>  
                        <tr>
                            <td>
                                <label>
                                    About My Agency</label>
                            </td>
                            <td>
                                <input type="text" id="profile-org-about" class="profile-org-about" />
                            </td>
                        </tr>  						
                        <tr>
                            <td style="width: 97px">
                                <label>
                                    Medical Facility Offerings</label>
                            </td>
                            <td style="width: 97px">
                                        <select id="profile-org-offer" multiple="multiple" class="profile-org-offer" onclick="selectMultiple(this,0);">
                                            <!--<option value="0" selected="selected">Select Medical Skills</option>-->
                                            <option value="23">Missions Hospital without Surgical Facilities</option>
                                            <option value="24">Missions Hospital with Surgical Facilities</option>
                                            <option value="25">Missions Hospital with Dental Care Facilities</option>
                                            <option value="26">Outpatient Medical/Dental/Eye Care Clinic</option>
                                            <option value="27">Organizing/Sending Short Term Medical/Dental/Eye Care Missions Agency</option>
                                            <option value="28">Supplying/Enhancing Short Term Medical/Dental/Eye Care Missions Agency</option>
                                            <option value="29">Community Development Agency</option>
                                            <option value="30">Emergency Medical Relief Agency</option>
                                            <option value="31">Medical/Dental/Eye Care Equipment Supplier</option>
                                            <option value="32">Water Purification/Drilling</option>
                                            <option value="33">Medical/Dental/Eye Care Training/Education Agency</option>
                                        </select>
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 97px">
                                <label>
                                    Non-Medical Facility Offerings</label>
                            </td>
                            <td style="width: 97px">
                                        <select id="profile-org-offern" multiple="multiple" class="profile-org-offern" onclick="selectMultiple(this,1);">
                                            <!--<option value="0" selected="selected">Select Medical Skills</option>-->
                                            <option value="34">Evangelism and Church Planning Ministry</option>
                                            <option value="35">Food Access</option>
                                            <option value="36">Transportation (In Country)</option>
                                            <option value="37">Translators</option>
                                            <option value="38">Trip Planning/Itinerary Building</option>
                                            <option value="39">Crowd Control</option>
                                            <option value="40">Press Relations</option>
                                            <option value="41">Housing for the Missions Team</option>
                                            <option value="42">Help getting through Customs</option>
                                            <option value="43">Building Supplies (Construction)</option>
                                        </select>
                            </td>
                        </tr>												
                        <tr>
                            <td style="width: 97px">
                                <label>
                                    Medical Skills</label>
                            </td>
                            <td style="width: 97px">
                                        <select id="profile-org-medical" multiple="multiple" class="profile-org-medical" onclick="selectMultiple(this,2);">
                                            <!--<option value="0" selected="selected">Select Medical Skills</option>-->
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
                                        <select id="profile-org-nonmedical" multiple="multiple" class="profile-org-nonmedical" onclick="selectMultiple(this,3);">
                                            <!--<option value="0" selected="selected">Select Non-Medical Skills</option>-->
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
                            <td style="width: 97px">
                                <label>
                                    Spiritual Service</label>
                            </td>
                            <td style="width: 197px">
                                        <select id="profile-org-spiritual" multiple="multiple" class="profile-org-spiritual" onclick="selectMultiple(this,4);">
                                            <!--<option value="0" selected="selected">Select Spiritual Service</option>-->
                                            <option value="20">Team Spiritual Leader</option>
                                            <option value="21">Individual Outreach (Prayer and Counseling)</option>
                                            <option value="22">Evangelism</option>
                                            <option value="44">Worship Team</option>
                                            <option value="51">Public Speaking</option>
                                        </select>
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 97px">
                                <label>
                                    Religious Affiliation</label>
                            </td>
                            <td style="width: 197px">
                                        <select id="profile-org-religion" class="profile-org-religion">
                                            <!--<option value="0", selected="selected">Select Religious Affiliation</option>-->
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
                                        <select id="profile-org-duration" class="profile-org-duration">
                                            <!--<option value="0" selected="selected">Select Duration of Missions</option>-->
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
                                        <select id="profile-org-state" class="profile-org-state">
                                        <?php
                                        echo '<option value="Select your State" selected="selected">Select your State</option>';
                                        $i=1;
                      foreach($usstates as $key => $state) {
                                              echo '<option value="'.$i.'">'.$state.'</option>';
                                              $i++;
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
                                <input type="text" id="profile-org-city" class="profile-org-city"/>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label>
                                    Zipcode</label>
                            </td>
                            <td>
                                <input type="text" id="profile-org-zipcode" class="profile-org-zipcode"/>
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 197px">
                                <label>
                                    Country</label>
                            </td>
                            <td style="width: 197px">
                                        <select id="profile-org-country" class="profile-org-country">
                                        <?php
                                        $i=1;
                      foreach($aCountries as $key => $country) {
                                              if ($country == "United States")
                            echo '<option selected="selected" value="'.$i.'">'.$country.'</option>';
                                              else {
                            echo '<option value="'.$i.'">'.$country.'</option>';
                                              }
                                              $i++;

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
                                        <select id="profile-org-region" multiple="multiple" class="profile-org-region" onclick="selectMultiple(this,5);">
                                            <!--<option value="0" selected="selected">Select Regions of Interest</option>-->
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
                                        <select id="profile-org-countryserved" multiple="multiple" class="profile-org-countryserved" onclick="selectMultiple(this,6);">
                                        <?php
                                        $i=1;
                      //echo '<option selected="selected" value="Select Countries Served">Select Countries Served</option>';
                      foreach($aCountries as $key => $country) {                      
                                              if ($country == "United States")
                            echo '<option value="'.$i.'">'.$country.'</option>';
                                              else {
                        echo '<option value="'.$i.'">'.$country.'</option>';
                                              }
                                              $i++;
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
                                <input type="text" id="profile-org-phone" class="profile-org-phone" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label>
                                    Email Id</label>
                            </td>
                            <td>
                                <input type="text" id="profile-org-email" class="profile-org-email" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label>
                                    My Missions Experience</label>
                            </td>
                            <td>
                                <input type="text" id="profile-org-experience" class="profile-org-experience" />
                            </td>
                        </tr>             
                        <tr>
                            <td>&nbsp;
                                
                            </td>
                            <td>
                                <input type="submit" value="Submit" class="profile-org-submit" id="profile-org-submit" />
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    </form>   
      </div>	  	  
      <div id="profile-trip-dialog" title="Please enter your trip profile information">
        <form id="profile-trip-form">
      <div id="wrapper">
        <div id="header">
            CMC Trip Profile Submission
        </div>
        <div id="contents">
            <div class="profile-container">
                <div class="profile-header">
                    Please enter your trip information</div>
                <div class="profile-contents">
                    <table cellpadding="4" cellspacing="0">
                        <tr>
                            <td>
                                <label>
                                    Trip Name</label>
                            </td>
                            <td>
                                <input type="text" id="profile-trip-name" class="profile-trip-name" />
                            </td>
                        </tr>  
                        <tr>
                            <td>
                                <label>
                                    Organization Website</label>
                            </td>
                            <td>
                                <input type="text" id="profile-trip-website" class="profile-trip-website" />
                            </td>
                        </tr>  
                        <tr>
                            <td>
                                <label>
                                    Trip Description</label>
                            </td>
                            <td>
                                <input type="text" id="profile-trip-about" class="profile-trip-about" />
                            </td>
                        </tr>  						
                        <tr>
                            <td style="width: 97px">
                                <label>
                                    Religious Affiliation</label>
                            </td>
                            <td style="width: 197px">
                                        <select id="profile-trip-religion" class="profile-trip-religion">
                                            <!--<option value="0", selected="selected">Select Religious Affiliation</option>-->
                                            <option value="1">Secular</option>
                                            <option value="2">Christian: Protestant</option>
                                            <option value="3">Christian: Catholic</option>
                                            <option value="4">Nondenominational</option>
                                        </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label>
                                    Anticipated Number of Team Members</label>
                            </td>
                            <td>
                                <input type="text" id="profile-trip-number" class="profile-trip-number" />
                            </td>
                        </tr>  	
                        <tr>
                            <td style="width: 97px">
                                <label>
                                    Approximate Departure Date</label>
                            </td>
                            <td style="width: 197px">
							<input name="profile-trip-depart" id="profile-trip-depart" class="profile-trip-depart" type=button value="select" onclick="displayDatePicker('profile-trip-depart', false, 'mdy', '.');">

                            </td>
                        </tr>						
                        <tr>
                            <td style="width: 97px">
                                <label>
                                    Approximate Return Date</label>
                            </td>
                            <td style="width: 197px">
							<input name="profile-trip-return" id="profile-trip-return" class="profile-trip-return" type=button value="select" onclick="displayDatePicker('profile-trip-return', false, 'mdy', '.');">
                            </td>
                        </tr>							
                        <tr>
                            <td style="width: 97px">
                                <label>
                                    Duration of Missions</label>
                            </td>
                            <td style="width: 197px">
                                        <select id="profile-trip-duration" class="profile-trip-duration">
                                            <!--<option value="0" selected="selected">Select Duration of Missions</option>-->
                                            <option value="1">Short Term: 1-2 weeks</option>
                                            <option value="2">Medium Term: 1 Month-2 Years</option>
                                            <option value="3">Long Term: 2+ Years</option>
                                        </select>
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 97px">
                                <label>
                                    Mission Stage</label>
                            </td>
                            <td style="width: 197px">
                                        <select id="profile-trip-stage" class="profile-trip-stage">
                                            <!--<option value="0" selected="selected">Select Duration of Missions</option>-->
                                            <option value="1">Planning</option>
                                            <option value="2">Execution</option>
                                        </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label>
                                    Destination Zipcode</label>
                            </td>
                            <td>
                                <input type="text" id="profile-trip-zipcode" class="profile-trip-zipcode"/>
                            </td>
                        </tr>						
                        <tr>
                            <td>
                                <label>
                                    Destination City</label>
                            </td>
                            <td>
                                <input type="text" id="profile-trip-city" class="profile-trip-city"/>
                            </td>
                        </tr>

                        <tr>
                            <td style="width: 197px">
                                <label>
                                    Destination Country</label>
                            </td>
                            <td style="width: 197px">
                                        <select id="profile-trip-country" class="profile-trip-country">
                                        <?php
                                        $i=1;
                      foreach($aCountries as $key => $country) {
                                              if ($country == "United States")
                            echo '<option selected="selected" value="'.$i.'">'.$country.'</option>';
                                              else {
                            echo '<option value="'.$i.'">'.$country.'</option>';
                                              }
                                              $i++;

                      }
                                        ?>
                                        </select>
                            </td>
                        </tr>                     
                        <tr>
                            <td style="width: 197px">
                                <label>
                                    Languages</label>
                            </td>
                            <td style="width: 197px">
                                        <select id="profile-trip-languages" multiple="multiple" class="profile-trip-languages" onclick="selectMultiple(this,0);">
                                        <?php
                                        $i=1;
                      //echo '<option selected="selected" value="Select Countries Served">Select Countries Served</option>';
                      foreach($languages as $key => $language) {                      
                                              if ($language == "English")
                            echo '<option selected="selected" value="'.$language.'">'.$language.'</option>';
                                              else {
                        echo '<option value="'.$language.'">'.$language.'</option>';
                                              $i++;
                                              }
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
                                <input type="text" id="profile-trip-phone" class="profile-trip-phone" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label>
                                    Email Id</label>
                            </td>
                            <td>
                                <input type="text" id="profile-trip-email" class="profile-trip-email" />
                            </td>
                        </tr>           
                        <tr>
                            <td>&nbsp;
                                
                            </td>
                            <td>
                                <input type="submit" value="Submit" class="profile-trip-submit" id="profile-trip-submit" />
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
          <div id="report-problem-characters-left">
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
      </div>
    </div>
    <!-- Do not place HTML markup below this line -->
  </body>
</html>
<!-- vim: ai:et:ts=2:sw=2
-->
