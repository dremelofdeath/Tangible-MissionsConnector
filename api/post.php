<?php

include_once 'facebook/facebook.php';
include_once 'config.php';
include_once 'common.php';

$fb = cmc_startup($appapikey, $appsecret,0);

$response = array('response' => array('hasError' => false, 'profilemsg' => 'Welcome to your CMC Profile', 'uid' => 100000022664372));
$somejson = json_encode($response);
$fbid = get_user_id($somejson);

//$fbid = $fb->require_login($required_permissions = 'publish_stream,read_stream');

$res = $fb->api_client->users_hasAppPermission('publish_stream',null);

if (!$res) {
?>

<script type="text/javascript">
Facebook.showPermissionDialog("read_stream,publish_stream,manage_pages,offline_access");
</script>

<?php
}
?>

<?php

echo '<fb:comments xid="missionsconnector" canpost="true" candelete="true" numposts="10" showform="true">';
echo '<fb:title>Write on missionsconnector wall</fb:title>';
echo '</fb:comments>';

?>

