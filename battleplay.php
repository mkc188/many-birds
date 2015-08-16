<?php

define('IN_PAGE', true);
define('REQUIRE_FB', true);
define('REQUIRE_TPL', true);
require_once('./common.php');


/*
 * ------------------
 * | Important note |
 * ------------------
 * this php won't verify any param,
 * only for demo, turn it off when Many Birds goes public
 * turn battle mode off in config.php:
 *
 *     $_CONF['battle_mode_enabled'] = false;
 *
 */

// check if the request room is exits or not
$currentRm = DB::queryFirstRow("SELECT * FROM battle WHERE hostid = %i", $_REQUEST['game']);

if( !isset($currentRm['hostid']) ) {
	header('Location: ' . $_PAGE['game_battle']);
	exit();
}

$hostid = $currentRm['hostid'];
$hostname = DB::queryFirstField("SELECT name FROM users WHERE uid = %i", $hostid);

// generate the page
$tpl = $mustache->loadTemplate('battleplay');
echo $tpl->render(array_merge($_FB, array(
	// section toggles
	'showfb'     => true,
	'is-logined' => $auth->valid(),

	// contents,
	'hostid'     => $hostid,
	'hostname'   => $hostname,
	'is-host'    => ( $hostid == $auth->uid ),
)));

?>