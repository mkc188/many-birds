<?php

define('IN_PAGE', true);
define('REQUIRE_TPL', true);
require_once('./common.php');


$origin = ( !empty($_REQUEST['origin']) ) ? $_REQUEST['origin'] : '';

// verify oauth request
if( empty($_SESSION['oauth_verify']) || $_SESSION['oauth_verify'] != md5($_CONF['oauth_secret'] . basename($origin)) ) {
	header('Location: ' . $_PAGE['home']);
	exit();
}

// login page with link redirect to OAuth Dialog
$tpl = $mustache->loadTemplate('oauth');
echo $tpl->render(array_merge($_FB, array(
    'origin' => $origin,
)));

?>