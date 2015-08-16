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

// get my room status
$myRoom = DB::queryFirstRow("SELECT * FROM battle WHERE hostid = %i", $auth->uid);

if( !isset($myRoom['hostid']) ) {
	// create new one

	DB::insertIgnore('battle', array(
	  'hostid'    => $auth->uid,
	  'status'    => ROOM_WAITING,
	  'timestamp' => time(),
	  'p2id'      => PLAYER_EMPTY,
	  'p2score'   => 0,
	  'p3id'      => PLAYER_EMPTY,
	  'p3score'   => 0,
	  'p4id'      => PLAYER_EMPTY,
	  'p4score'   => 0,
	));

	// re-query the room.
	$myRoom = DB::queryFirstRow("SELECT * FROM battle WHERE hostid = %i", $auth->uid);
}

$tplInfoMyRoom = array();

// query all player information
for( $i = 2; $i <= 4; $i++ ) {
	if( $myRoom['p'.$i.'id'] > 0 ) {
		$tplInfoMyRoom['is-my-p'.$i] = true;

		$playerInfo = DB::queryFirstRow("SELECT * FROM users WHERE uid = %i", $myRoom['p'.$i.'id']);
		$tplInfoMyRoom['my-p'.$i.'-name'] = $playerInfo['name'];
		$tplInfoMyRoom['my-p'.$i.'-pic'] = 'https://graph.facebook.com/' . $myRoom['p'.$i.'id'] . '/picture';
		$tplInfoMyRoom['my-p'.$i.'-username'] = $playerInfo['username'];

	} else {
		$tplInfoMyRoom['is-my-p'.$i] = false;
	}
}

// do a friend list cache update
require_once('./includes/class.friend.php');
$f = new Friend();
$f->updateCache();

$battle_list = '';
// find all rooms for user's friends:
// 1. obtain a friend list
$friendList = DB::queryFirstColumn("SELECT friendid FROM friendship WHERE `uid` = %i ORDER BY RAND()", $auth->uid);

// 2. search for rooms
$friendRooms = DB::query("SELECT * FROM battle WHERE hostid IN (%l) LIMIT %i", implode(',', $friendList), $_CONF['max_friend_in_battle']);

// 3. fill in player information
foreach($friendRooms as $row) {
	$tplItem = $mustache->loadTemplate('battle_list_row');

	$itemArr = array();
	$hasEmptySeat = false;

	// host
	$playerInfo = DB::queryFirstRow("SELECT * FROM users WHERE uid = %i", $row['hostid']);
	$itemArr['p1-name'] = $playerInfo['name'];
	$itemArr['p1-pic'] = 'https://graph.facebook.com/' . $row['hostid'] . '/picture';
	$itemArr['p1-username'] = $playerInfo['username'];
	$itemArr['hostid'] = $row['hostid'];

	// player
	for( $i = 2; $i <= 4; $i++ ) {
		if( $row['p'.$i.'id'] > 0 ) {
			$itemArr['is-p'.$i] = true;

			$playerInfo = DB::queryFirstRow("SELECT * FROM users WHERE uid = %i", $row['p'.$i.'id']);
			$itemArr['p'.$i.'-name'] = $playerInfo['name'];
			$itemArr['p'.$i.'-pic'] = 'https://graph.facebook.com/' . $row['p'.$i.'id'] . '/picture';
			$itemArr['p'.$i.'-username'] = $playerInfo['username'];

		} else {
			$itemArr['is-p'.$i] = false;
			$hasEmptySeat = true;
		}
	}

	// status
	switch($row['status']) {
		case ROOM_WAITING:
			$itemArr['status-type'] = "text-success";
			$itemArr['status'] = "Open";
			$itemArr['is-playing'] = false;
			break;

		case ROOM_STARTED:
			$itemArr['status-type'] = "text-warning";
			$itemArr['status'] = "Started";
			$itemArr['is-playing'] = true;
			break;
	}

	if( !$hasEmptySeat ) $itemArr['is-playing'] = true;

	$battle_list .= $tplItem->render($itemArr);
}

// generate the page
$tpl = $mustache->loadTemplate('battle');
echo $tpl->render(array_merge($_FB, $tplInfoMyRoom, array(
	// section toggles
	'showfb'     => true,
	'is-logined' => $auth->valid(),

	// contents,
	'battle-list' => $battle_list,
)));

?>