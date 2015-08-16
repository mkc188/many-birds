<?php

define('IN_PAGE', true);
require_once('./common.php');


/*
 * ------------------
 * | Important note |
 * ------------------
 * this ajax won't verify any param,
 * only for demo, turn it off when Many Birds goes public
 * turn battle mode off in config.php:
 *
 *     $_CONF['battle_mode_enabled'] = false;
 *
 */

// reject all non-loggined user
if( !$auth->valid() ) {
	header('Content-type: application/json');
	echo json_encode(array('success' => false));
	exit();
}

$action = ( !empty($_REQUEST["do"]) ) ? $_REQUEST["do"] : '';

if( $action == "recreate" ) {
	DB::delete('battle', "hostid=%i", $auth->uid);
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

	header('Content-type: application/json');
	echo json_encode(array('success' => true));
	exit();
}

if( $action == "join" ) {
	$hostid = ( !empty($_REQUEST["hostid"]) ) ? $_REQUEST["hostid"] : '';

	$targetRm = DB::queryFirstRow("SELECT * FROM battle WHERE hostid = %i", $hostid);

	if( isset($targetRm['hostid']) && intval($targetRm['hostid']) > 0 ) {
		$myUid = $auth->uid;

		// logic test
		$isHost = ( $targetRm['hostid'] == $myUid );
		$isJoined = ( $targetRm['p2id'] == $myUid ||
					  $targetRm['p3id'] == $myUid ||
					  $targetRm['p4id'] == $myUid );
		$isP2Empty = ( $targetRm['p2id'] == 0 );
		$isP3Empty = ( $targetRm['p3id'] == 0 );
		$isP4Empty = ( $targetRm['p4id'] == 0 );
		$isFull = !( $isP2Empty || $isP3Empty || $isP4Empty );

		if( $isHost || $isJoined ) {
			header('Content-type: application/json');
			echo json_encode(array('success' => true, 'message' => ''));
			exit();
		}

		if( $isFull ) {
			header('Content-type: application/json');
			echo json_encode(array('success' => false, 'message' => 'Room is full.'));
			exit();
		}

		$targetSeat = ( $isP2Empty ) ? '2' : (
					  ( $isP3Empty ) ? '3' : (
					  ( $isP4Empty ) ? '4' : false));

		DB::update('battle', array(
			'p'.$targetSeat.'id' => $auth->uid,
		), "hostid = %i", $targetRm['hostid']);

		header('Content-type: application/json');
		echo json_encode(array('success' => true));
		exit();

	} else {
		header('Content-type: application/json');
		echo json_encode(array('success' => false, 'message' => 'Room not exists.'));
		exit();
	}
}

if( $action == "end" ) {
	$hostid = ( !empty($_REQUEST["hostid"]) ) ? $_REQUEST["hostid"] : '';
	$score = ( !empty($_REQUEST["score"]) ) ? $_REQUEST["score"] : '';

	$targetRm = DB::queryFirstRow("SELECT * FROM battle WHERE hostid = %i", $hostid);

	if( isset($targetRm['hostid']) && intval($targetRm['hostid']) > 0 ) {
		$myUid = $auth->uid;
		$isHost = ( intval($targetRm['hostid']) == $myUid );
		$isP2Seat = ( intval($targetRm['p2id']) == $myUid );
		$isP3Seat = ( intval($targetRm['p3id']) == $myUid );
		$isP4Seat = ( intval($targetRm['p4id']) == $myUid );
		// $isInRoom = ( $isP2Seat || $isP3Seat || $isP4Seat );

		$targetField = ( $isHost ) ? 'p1score' : (
					   ( $isP2Seat ) ? 'p2score' : (
					   ( $isP3Seat ) ? 'p3score' : (
					   ( $isP4Seat ) ? 'p4score' : 'p1score')));

		DB::update('battle', array(
			"{$targetField}" => intval($score),
			"status"         => ROOM_WAITING,
		), "hostid=%i", $targetRm['hostid']);
	}

	header('Content-type: application/json');
	echo json_encode(array('success' => true));
	exit();
}

if( $action == "rank" ) {
	$hostid = ( !empty($_REQUEST["hostid"]) ) ? $_REQUEST["hostid"] : '';

	$score = array();
	$targetRm = DB::queryFirstRow("SELECT * FROM battle WHERE hostid = %i", $hostid);

	if( isset($targetRm['hostid']) && intval($targetRm['hostid']) > 0 ) {

		$playerInfo = DB::queryFirstRow("SELECT * FROM users WHERE uid = %i", $targetRm['hostid']);
		$score[] = array('score' => $targetRm['p1score'],
						 'name'  => $playerInfo['name'],
						 'id'    => $targetRm['hostid'],
			);

		for( $i = 2; $i <= 4; $i++ ) {
			if( $targetRm['p'.$i.'id'] > 0 ) {

				$playerInfo = DB::queryFirstRow("SELECT * FROM users WHERE uid = %i", $targetRm['p'.$i.'id']);
				$score[] = array('score' => $targetRm['p'.$i.'score'],
								 'name'  => $playerInfo['name'],
								 'id'    => $targetRm['p'.$i.'id'],
					);
			}
		}

		usort($score, function($a, $b) {
		    return $b['score'] - $a['score'];
		});
	}

	header('Content-type: application/json');
	echo json_encode(array('success' => true, 'rank' => $score));
	exit();
}

if( $action == "leave" ) {
	$hostid = ( !empty($_REQUEST["hostid"]) ) ? $_REQUEST["hostid"] : '';

	$targetRm = DB::queryFirstRow("SELECT * FROM battle WHERE hostid = %i", $hostid);

	if( isset($targetRm['hostid']) && intval($targetRm['hostid']) > 0 ) {
		$myUid = $auth->uid;
		$isHost = ( intval($targetRm['hostid']) == $myUid );
		$isP2Seat = ( $targetRm['p2id'] == $myUid );
		$isP3Seat = ( $targetRm['p3id'] == $myUid );
		$isP4Seat = ( $targetRm['p4id'] == $myUid );
		$isInRoom = ( $isHost || $isP2Seat || $isP3Seat || $isP4Seat );

		if( $isInRoom ) {
			$targetSeat = ( $isP2Seat ) ? '2' : (
						  ( $isP3Seat ) ? '3' : (
						  ( $isP4Seat ) ? '4' : false));

			if( $isHost ) {
				DB::update('battle', array(
					"status" => ROOM_WAITING,
				), "hostid=%i", $hostid);
			} else {
				DB::update('battle', array(
					'p'.$targetSeat.'id' => 0,
				), "hostid=%i", $hostid);
			}
		}
	}

	header('Content-type: application/json');
	echo json_encode(array('success' => true));
	exit();
}

if( $action == "pull" ) {
	$time = time();
	$hostid = ( !empty($_REQUEST["hostid"]) ) ? $_REQUEST["hostid"] : '';

	$targetRm = DB::queryFirstRow("SELECT * FROM battle WHERE hostid = %i", $hostid);

	if( isset($targetRm['hostid']) && intval($targetRm['hostid']) > 0 ) {
		do {
			$targetRm = DB::queryFirstRow("SELECT * FROM battle WHERE hostid = %i", $hostid);

			if( $targetRm['status'] == ROOM_STARTED ) {
				header('Content-type: application/json');
				echo json_encode(array('started' => true));
				exit();
			}
		} while ( time() - $time < 30 );
	}
	header('Content-type: application/json');
	echo json_encode(array('started' => false));
	exit();
}

if( $action == "hostStart" ) {
	$hostid = ( !empty($_REQUEST["hostid"]) ) ? $_REQUEST["hostid"] : '';
	$targetRm = DB::queryFirstRow("SELECT * FROM battle WHERE hostid = %i", $hostid);

	if( isset($targetRm['hostid']) && intval($targetRm['hostid']) > 0 ) {
		$myUid = $auth->uid;
		$isHost = ( $targetRm['hostid'] == $myUid );

		DB::update('battle', array(
			'status' => ROOM_STARTED,
		), "hostid=%i", $targetRm['hostid']);
	}
	header('Content-type: application/json');
	echo json_encode(array('success' => true));
	exit();
}

?>