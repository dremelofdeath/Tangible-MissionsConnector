<?php
// Application: Christian Missions Connector
// File: 'help.php' 
//  provides information and explanations for users
// 
//require_once 'facebook.php';

include_once '../facebook/facebook.php';
include_once 'config.php';
include_once 'common.php';
$fb = cmc_startup($appapikey, $appsecret);

$facebook = new Facebook($appapikey, $appsecret);
$user_id = $facebook->require_login();




echo "

<br>
<b>What is Christian Missions Connector about?</b><br><br>

The goal of Christian Missions Connector is to provide a safe and easy to environment for people who are interested in missions to connect with each other and with missions agencies.<br><br>

<b>How can I use Christian Missions Connector?</b><br><br> 

You can use Christian Missions Connector in the following ways:<br><br>
<b>1)</b> Easily locate missions organizations who are working in the geographic area of your interest and/or doing the type of work in which you are interested.<br> 
<b>2)</b> Join existing or planned missions teams.<br> 
<b>3)</b> Post invitations for volunteers to form your own team.<br> 
<b>4)</b> Connect with people in your geographic area who are interested in missions.<br><br>

<b>How does Christian Missions Connector work?</b><br><br>

Using Christian Missions Connector is easy. Just follow these four easy steps:<br><br>
<b>1)</b> Create your profile using the Create Profile button.<br> 
<b>2)</b> Register any missions trips you are planning and need volunteers for by using the Create Trip button.<br> 
<b>3)</b> Search for trips you could join, other people like yourself that are interested in missions, permanent missions stations you can help, or missions agencies you can otherwise assist in the Search tab.<br>
<b>4)</b> We also hope you will invite all of your friends who might be interested in using the application. The more people that use this application, the more useful it will be to everyone who uses it.<br><br>

<b>Do you protect my privacy?</b><br><br>

Christian Missions Connector will never spam you or give any information you give us to another organization, period. The only information available to users of Christian Missions Connectors is the information you decide to make available. Spammers will absolutely not be tolerated on this application. If you spam, your account on this application will be deleted.<br><br>

<b>Can I post information I dont want other people or governments to see?</b><br><br>

Absolutely not! All information on Christian Missions Connector is completely public. So, while well never sell your data, if youre afraid that a government will crack down on your missions trip, you should absolutely not post your profile or trips on this application.<br><br>

<b>Exactly how does the search function work?</b><br><br>

The search function works on an exact match basis. Only people or missions agencies that share ALL of the characteristics you searched for will be displayed. So, if you arent getting enough results, take off a few criteria and you should get more results. Also, all search results are sorted by zip code so that its easy for you to find people who are geographically close to you.<br><br>

<b>If I need help, who should I contact?</b><br><br>
If youre interested in becoming a Christian Missions Connector Partner or just have a generic technical question, please e-mail us at missionsconnector@tangiblesoft.net and well get back do you quickly.<br><br>

<b>I want a place to have a discussion about missions, not just find others who can help me or need my help. Do you have a place for that?</b><br><br>

Absolutely! Just go to the profile page of our application and you'll find a discussion board that meets those needs exactly. To save you some time, here's the direct link to the discussion board http://www.facebook.com/apps/application.php?id=305928355832&v=app_2373072738 .
";


?>

