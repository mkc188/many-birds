<?php

define('IN_PAGE', true);
define('REQUIRE_FB', true);
define('REQUIRE_TPL', true);
require_once('./common.php');


if( !$_CONF['battle_mode_enabled'] ) {
	header('Location: ' . $_PAGE['game_online']);
	exit();
}

$tpl = $mustache->loadTemplate('mode_select');
echo $tpl->render(array_merge($_FB, array(
	// section toggles
	'showfb'     => true,
	'is-logined' => true,

	// contents
)));

?>