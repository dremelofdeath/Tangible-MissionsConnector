<?php
// Application: Christian Missions Connector
// File: 'help.php' 
//  provides information and explanations for users
// 
//require_once 'facebook.php';

include_once 'facebook/facebook.php';
include_once 'config.php';
include_once 'common.php';
$fb = cmc_startup($appapikey, $appsecret,0);

//$facebook = new Facebook($appapikey, $appsecret);
$user_id = get_user_id($fb);
//$user_id = $facebook->require_login("publish_stream,read_stream");


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

<b>Can I post information I don't want other people or governments to see?</b><br><br>

Absolutely not! All information on Christian Missions Connector is completely public. So, while we'll never sell your data, if you're afraid that a government will crack down on your missions trip, you should absolutely not post your profile or trips on this application.<br><br>

<b>Exactly how does the search function work?</b><br><br>

The search function only returns profiles that exactly match your search critera. That means that only people or missions agencies that share ALL of the characteristics you searched for will be displayed. So, if you aren't getting enough results, take off a few criteria and you should get more results. Also, all search results are sorted by zip code so that it's easy for you to find people who are geographically close to you.<br><br>

<b>If I need help or have a suggestion for how to improve the site, who should I contact?</b><br><br>
If you need help on anything at all, please don't hesitate to e-mail us at missionsconnector@tangiblesoft.net and well get back do you quickly.<br><br>

<b>What is a Christian Missions Connector Partner and how do I become one?</b><p>
A Christian Missions Connector Partner is an organization that has taken a special effort to make Christian Missions Connector a success by letting us reach out to their members  to encourage them to join our network and/or providing some other significant form of support. Christian Mission Connector Partners will receive priority access to the key insights about trends in medical missions that we generate as the membership in the site grows. Finally, users have the option to search specifically for trips that are hosted by Partner organizations. We hope that this will improve the quality of trips that our site members go on and help our Partner organizations grow more quickly through helping our site. </p>

<p>If you run a missions organization and want to participate in this program, please e-mail us at cmcpartner@tangiblesoft.net and we'll be more than happy to discuss this opportunity with you. </p>


<b>I want a place to have a discussion about missions, not just find others who can help me or need my help. Do you have a place for that?</b><br><br>

Absolutely! Just click <a href= 'http://www.facebook.com/apps/application.php?id=305928355832&v=app_2373072738'>here</a> to go the discussion boards on our profile page. 
";


?>

