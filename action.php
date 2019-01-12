<?php

define('IN_PAGE', true);
require_once('./common.php');


$action = ( !empty($_REQUEST["do"]) ) ? $_REQUEST["do"] : '';

/*
 * Auth
 */
if( $action == "auth.login" ) {
	if( $auth->login() ) {
		$return = array('success' => true, 'next' => $_PAGE['mode_select']);
    } else {
    	$return = array('success' => false, 'next' => $_PAGE['home']);
    }

	header('Content-type: application/json');
	echo json_encode($return);
	exit();
}

if( $action == "auth.logout" ) {
	header('Content-type: application/json');
	echo json_encode( array('success' => $auth->logout(), 'next' => $_PAGE['home']) );
	exit();
}

/*
 * Tournament
 */
if( $action == "tournament.map" ) {
	require_once('./includes/class.tournament.php');
	$t = new Tournament();
	$map = $t->getCurrentMap();

	if( $map ) {
		$return = array('success' => true, 'map' => $map);
	} else {
		$return = array('success' => false);
	}

	header('Content-type: application/json');
	echo json_encode($return);
	exit();
}

if( $action == "tournament.start" ) {
	// requires user logined
	if( $auth->valid() ) {
		require_once('./includes/class.tournament.php');
		$t = new Tournament();
		$hash = $t->gameStart();

		if( $hash ) {
			// game session created
			$return = array('success' => true, 'gameid' => $hash);

		} else {
			// client would retry the request for this unknown error
			$return = array('success' => false);
		}

	} else {
		// not logined in
		$return = array('success' => false);
	}

	header('Content-type: application/json');
	echo json_encode($return);
	exit();
}

if( $action == "tournament.end" ) {
	$gameid = ( !empty($_POST["gameid"]) ) ? $_POST["gameid"] : '';
	$score = ( !empty($_POST["score"]) ) ? $_POST["score"] : '';

	// requires user logined
	if( $auth->valid() ) {
		require_once('./includes/class.tournament.php');
		$t = new Tournament();

		// return with boolean (isHighScore)
		$return = array('success' => true, 'message' => $t->gameEnd($gameid, $score));

	} else {
		// not logined in
		$return = array('success' => false);
	}

	header('Content-type: application/json');
	echo json_encode($return);
	exit();
}

if( $action == "tournament.rank" ) {
	// requires user logined
	if( $auth->valid() ) {
		require_once('./includes/class.tournament.php');
		$t = new Tournament();

		// return with ranking list
		$return = array('success' => true, 
			'time' => time(),
			'list' => $t->getRankingList(),
			'id'   => $auth->uid,
		);

	} else {
		// not logined in
		$return = array('success' => false);
	}

	header('Content-type: application/json');
	echo json_encode($return);
	exit();
}

/*
 * FAQ
 */
if( $action == "faq.vote" ) {
	// requires user logined
	if( $auth->valid() ) {
		$id = ( !empty($_POST["id"]) ) ? intval($_POST["id"]) : '';
		$vote = ( !empty($_POST["vote"]) ) ? $_POST["vote"] : '';

		// query if there is real question
		$targetQid = DB::queryOneField('id', "SELECT id FROM faq WHERE id = %i", $id);
		if( intval($targetQid) > 0 ) {
			// detemine the vote type
			switch ($vote) {
				case 'up':
					$vote_value = UPVOTE;
					break;

				case 'down':
					$vote_value = DOWNVOTE;
					break;
				
				default:
					$vote_value = 0;
					break;
			}

			if( $vote_value != 0 ) {
				// do an INSERT ... ON DUPLICATE KEY UPDATE for faq_vote table
				DB::insertUpdate('faq_vote', array(
					'qid' => $targetQid,
					'uid' => intval($auth->uid),
				), array(
					'vote' => $vote_value,
				));

				$return = array('success' => true, 'id' => $targetQid, 'value' => $vote);
			} else {
				// not valid vote type
				$return = array('success' => false);
			}

		} else {
			// not valid request
			$return = array('success' => false);
		}

	} else {
		// not logined in
		$return = array('success' => false);
	}

	header('Content-type: application/json');
	echo json_encode($return);
	exit();
}

/*
 * Item Shop
 */
if( $action == "item.buy" ) {
	$itemid = ( !empty($_POST["itemid"]) ) ? $_POST["itemid"] : '';

	if( $auth->valid() ) {
		require_once('./includes/class.shop.php');
		$s = new Shop();

		// return purchase result
		$p = $s->purchase($itemid);
		$return = array(
			'auth'    => true,
			'success' => $p['success'], 
			'message' => $p['message'],
		);

	} else {
		// not logined in
		$return = array('auth' => false);
	}

	header('Content-type: application/json');
	echo json_encode($return);
	exit();
}

if( $action == "item.enable" ) {
	$itemid = ( !empty($_POST["itemid"]) ) ? $_POST["itemid"] : '';

	if( $auth->valid() ) {
		require_once('./includes/class.shop.php');
		$s = new Shop();

		// return purchase result
		$p = $s->enable($itemid);
		$return = array(
			'auth'    => true,
			'success' => $p['success'], 
			'message' => $p['message'],
		);

	} else {
		// not logined in
		$return = array('auth' => false);
	}

	header('Content-type: application/json');
	echo json_encode($return);
	exit();
}

if( $action == "item.active" ) {
	if( $auth->valid() ) {
		require_once('./includes/class.shop.php');
		$s = new Shop();

		// return purchase result
		$return = array(
			'auth'  => true,
			'items' => $s->listActive(),
		);

	} else {
		// not logined in
		$return = array('auth' => false);
	}

	header('Content-type: application/json');
	echo json_encode($return);
	exit();
}

?>