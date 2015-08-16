<?php

if( !defined('IN_PAGE') ) {
	// No direct access to this PHP file.
	exit();
}


// Pages location
$_PAGE = array();
$_PAGE['home'] = 'index.php';
$_PAGE['ouath'] = 'oauth.php';
$_PAGE['mode_select'] = 'mode_select.php';
$_PAGE['game_online'] = 'online.php';
$_PAGE['game_battle'] = 'battle.php';

// Tournament
define('TS_STARTED', 1);
define('TS_CLAIMED', 2);

// FAQ
define('UPVOTE', 1);
define('DOWNVOTE', -1);

// Achievement
define('TARGET_PLAYCOUNT', 1);
define('TARGET_HIGHSCORE', 2);

// Item Shop
define('ITEM_BIRDTHEME', 1);
define('ITEM_BACKGROUND', 2);
define('DEFAULT_BIRD', 1);
define('DEFAULT_BG', 2);

// Battle
define('PLAYER_EMPTY', 0);
define('ROOM_WAITING', 0);
define('ROOM_STARTED', 1);

?>