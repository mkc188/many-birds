<?php

define('IN_PAGE', true);
define('REQUIRE_FB', true);
define('REQUIRE_TPL', true);
require_once('./common.php');


$tpl = $mustache->loadTemplate('tournament');
echo $tpl->render(array_merge($_FB, array(
	// section toggles
	'showfb'     => true,
	'is-logined' => true,

	// contents
	'top-count'  => $_CONF['max_friend_in_ranking'],
)));

?>