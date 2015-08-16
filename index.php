<?php

define('IN_PAGE', true);
define('REQUIRE_TPL', true);
require_once('./common.php');


$tpl = $mustache->loadTemplate('index');
echo $tpl->render(array_merge($_FB, array(
	// section toggles
	'showfb'     => true,
	'is-logined' => $auth->valid(),

	// contents
)));

?>