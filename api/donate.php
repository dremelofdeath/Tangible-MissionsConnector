<?php

include_once 'common.php';

$fb = cmc_startup($appapikey, $appsecret,0);

echo "<br><br>Here at Christian Missions Connector, every dollar you generate makes a huge difference. Overall, we use donations for three things:

<br><br><u>1) Paying for overhead:</u> It takes money to pay for the computers that keep this application running 24/7. 

<br><br><u>2) Getting the word out about our application:</u> Through our innovative marketing program, $1 that you give could expose well over 100 people to Missions Connector. The bigger the database is, the more useful it will be to you and the people that so desperately need our help.

<br><br><u>3) Improving the application:</u> The day to day programming maintenance is done by a group of dedicated programmers. However, sometimes we need extra help to build additional features and this helps pay for that. Some cool features we have in mind are:

<br><br>A message board system that shows you a personalized list of conversations that are relevant to you and your organization. 

<br><br>An e-mail system that automatically notifies you when new results come up for a search you've done before. (Of course we will ONLY send you this sort of e-mail if you ask for it and only for the searches you are interested in.) This will save people the time of coming back over and over and will make connections happen faster. 

<br><br>A discounted missions store that will give discounted prices on everything from donated medical supplies to (someday) plane tickets and rural travel recommendations. 

<br><br>However, we can't make any of this happen without your committed support. 

<br><br>So please use the secure paypal donation box below and give a meaningful gift to help us build the world's most effective Christian missions application. When you reach the donation page, please note that we are not yet a tax exempt organization and so your gifts will not be tax deductible (yet). What we can promise is that what you give will go 100% back to the core missions costs associated with Christian Missions Connector. <br><br><br>"; 


?>

<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="HUSZ264QW3UCQ">
<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>





