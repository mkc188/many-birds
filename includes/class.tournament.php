<?php

if( !defined('IN_PAGE') ) {
	// No direct access to this PHP file.
	exit();
}


class Tournament {
	public $year;
	public $week;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->year = date("Y");
		$this->week = date("W");
	}

	/**
	 * Get current week tournament map
	 *
	 * @return string map
	 */
	public function getCurrentMap($re = false) {
		$result = DB::queryFirstRow("SELECT id, map FROM tournament WHERE year = %i AND week = %i LIMIT 1", $this->year, $this->week);

		if( $result['id'] > 0 ) {
			// map found
			$map = $result['map'];

		} else {
			if( $re ) {
				// can't found even called createTournament(), maybe database error
				return false;
			}

			// not found, create new one
			$this->createTournament();
			$map = $this->getCurrentMap(true);
		}

		return $map;
	}

	/**
	 * Get current week tournament score
	 *
	 * @return array(integer score, integer timestamp)
	 */
	public function getCurrentScore() {
		global $auth;

		return DB::queryFirstRow("SELECT ts.score, ts.timestamp FROM tournament t, tournament_score ts
			WHERE t.year = %i AND t.week = %i AND ts.uid = %i AND ts.tid = t.id LIMIT 1",
			$this->year, $this->week, $auth->uid);
	}

	/**
	 * Deduct score for item shop purchase
	 *
	 * @param int deduct amount
	 */
	public function deductScore($amount) {
		global $auth;
		$current = $this->getCurrentScore();

		if( $auth->valid() && $current['score'] >= $amount ) {
			$tid = DB::queryFirstField("SELECT id FROM tournament WHERE year = %i AND week = %i LIMIT 1",
				$this->year, $this->week);
			DB::query("UPDATE tournament_score SET score = score - %i WHERE tid = %i AND uid = %i LIMIT 1",
				$amount, $tid, $auth->uid);

			return true;
		} else {
			return false;
		}
	}

	/**
	 * Client request session start
	 *
	 * @return string hash
	 */
	public function gameStart() {
		global $_CONF, $auth;

		if( !$auth->valid() ) {
			return false;
		}

		// keep previous X sessions for security
		// X = max concurrent client 
		DB::query("DELETE FROM tournament_sessions WHERE uid = %i AND timestamp < 
			(SELECT IFNULL( 
				(SELECT ts.timestamp FROM 
					(SELECT tmp.* FROM tournament_sessions tmp) ts
				WHERE uid = %i GROUP BY ts.timestamp ORDER BY ts.timestamp LIMIT %i,1)
			, 0) as timestamp)",
		$auth->uid, $auth->uid, $_CONF['max_concurrent_client'] - 1);

		// create new session
		$time = time();
		$hash = substr(md5($auth->uid . $time . $this->randomString()), 0, 15);

		DB::insert('tournament_sessions', array(
			'uid'       => $auth->uid,
			'timestamp' => $time,
			'hash'      => $hash,
			'status'    => TS_STARTED,
		));

		return $hash;
	}

	/**
	 * Client request session end, verify and update if it's new high score
	 *
	 * @return boolean false (if anything incorrect)
	 * or
	 * @return array(boolean isHighScore, array newAchievement)
	 */
	public function gameEnd($hash, $claimedScore) {
		global $_CONF, $auth;

		if( !$auth->valid() ) {
			return false;
		}

		$tournamentId = DB::queryOneField('id', "SELECT id FROM tournament ORDER BY id DESC LIMIT 1");

		// check if claimed score higher than highest score
		// if not, no need to verify, just mark the session as CLAIMED
		$currentScore = DB::queryOneField('score',
			"SELECT score FROM tournament_score WHERE uid = %s AND tid = %i", $auth->uid, $tournamentId);

		// check if new achievement obtained
		require_once('./includes/class.achievement.php');
		$achievement = new Achievement();
		$achievement->addOne(TARGET_PLAYCOUNT);

		if( intval($currentScore) >= $claimedScore ) {
			// mark CLAIMED
			DB::query("UPDATE tournament_sessions SET status = %i WHERE uid = %i AND hash = %s LIMIT 1",
				TS_CLAIMED, $auth->uid, $hash);
			return array('isHighScore' => false, 'newAchievement' => $achievement->newAchievement);
		}

		// check whether the client own the game session or not
		// if owned, verify the score is possible by (score_per_second * time_spent)
		$result = DB::queryFirstRow("SELECT uid, timestamp FROM tournament_sessions WHERE uid = %i AND hash = %s AND status = %i LIMIT 1",
			$auth->uid, $hash, TS_STARTED);

		if( $result['uid'] > 0 ) {
			// session owned

			$time = time();
			$maxPossible = ($time - $result['timestamp'] + 1) * $_CONF['max_score_per_sec'];

			if( $maxPossible > $claimedScore ) {
				// verified, update the tournament score record
				DB::query("INSERT INTO tournament_score (tid,uid,score,timestamp) VALUES (%i,%i,%i,%i) ON DUPLICATE KEY UPDATE score = VALUES(score), timestamp = VALUES(timestamp)",
					$tournamentId, $auth->uid, $claimedScore, $time);

				// and mark as CLAIMED
				DB::query("UPDATE tournament_sessions SET status = %i WHERE uid = %i AND hash = %s LIMIT 1",
				TS_CLAIMED, $auth->uid, $hash);

				$achievement->update(TARGET_HIGHSCORE, $claimedScore);

				return array('isHighScore' => true, 'newAchievement' => $achievement->newAchievement);
			} else {
				return array('isHighScore' => false, 'newAchievement' => $achievement->newAchievement);
			}
		}

		return false;
	}

	/**
	 * Get the ranking list of current user's friends
	 *
	 * @return array
	 */
	public function getRankingList() {
		global $_CONF, $auth;

		if( !$auth->valid() ) {
			return array();
		}

		// do a friend list cache update
		require_once('./includes/class.friend.php');
		$f = new Friend();
		$f->updateCache();

		$return = array();
		$count = $_CONF['max_friend_in_ranking'];

		// left join tables to find friends joined the game
		$myScore = $this->getCurrentScore();
		$myName = $auth->getNames();

		$result1 = DB::query("SELECT * FROM (
			(SELECT f.friendid as id, f.name, ts.score, ts.timestamp, u.username FROM friendship f
				LEFT JOIN tournament_score ts ON f.friendid = ts.uid
					AND ts.tid = (SELECT id FROM tournament WHERE year = %i AND week = %i) 
				LEFT JOIN users u ON f.friendid = u.uid
				WHERE f.uid = %i AND u.username IS NOT NULL LIMIT %i)
			UNION
			(SELECT %i as id, %s as name, %i as score, %i as timestamp, %s as username)) r ORDER BY r.score DESC
			", $this->year, $this->week, $auth->uid, $count, $auth->uid, $myName['name'], $myScore['score'], $myScore['timestamp'], $myName['username']);
	
		if( count($result1) > $count) {
			array_pop($result1);
		}

		// check if user in first $count rank,
		// if not, replace the last one as user and mark as unranked
		$found = false;
		for( $i = 0; $i < count($result1); $i++ ) {
			if( $result1[$i]['id'] == $auth->uid ) {
				$found = true;
				break;
			}
		}

		if( !$found ) {
			$result1[$count - 1] = array('id'        => $auth->uid,
								 		 'name'      => $myName['name'],
										 'score'     => $myScore['score'],
										 'timestamp' => $myScore['timestamp'],
										 'username'  => $myName['username'],
										 'unrank'    => true
									);
		}

		$count = $count - count($result1);

		// found out the bird theme friends are using
		require_once('./includes/class.shop.php');
		$s = new Shop();

		// found out the last two medal friends obtain
		require_once('./includes/class.achievement.php');
		$a = new Achievement();

		// used feature from PHP 5.5, the following library is for < 5.5 version
		// credits: https://github.com/ramsey/array_column
		require_once('./includes/array_column.php');
		$list_birds = $s->listUsersBirds(array_column($result1, 'id'));
		$list_medals = $a->listUsersMedals(array_column($result1, 'id'));

		for( $i = 0; $i < count($result1); $i++ ) {
			$info = ( isset($list_birds['b'.$result1[$i]['id']]) ) ? $list_birds['b'.$result1[$i]['id']] : $s->defaultBird();

			$result1[$i]['bird-icon'] = $info['content_path'];
			$result1[$i]['bird-height'] = $info['height'];
			$result1[$i]['bird-width'] = $info['width'];
			$result1[$i]['bird-name'] = $info['name'];
			$result1[$i]['achievement'] = ( isset($list_medals['a'.$result1[$i]['id']]) ) ? $list_medals['a'.$result1[$i]['id']]: array();
		}

		// left join tables to find friends not joined game
		$result2 = array();
		if( $count > 0 ) {
			$result2 = DB::query("SELECT f.friendid as id, f.name FROM friendship f
									LEFT JOIN users u ON f.friendid = u.uid
									WHERE f.uid = %i AND u.username IS NULL
									ORDER BY RAND() LIMIT %i", $auth->uid, $count);
		}

		return array_merge($result1, $result2);
	}

	/**
	 * Try to insert new record for current week tournament,
	 * may fail since the table is not locked in the inset query
	 *
	 * @return null
	 */
	private function createTournament() {
		$time = time();
		$map = $this->randomString(255);

		// try to insert new tournament
		try {
			DB::insert('tournament', array(
				'year'      => $this->year,
				'week'      => $this->week,
				'timestamp' => $time,
				'map'       => $map,
			));
		} catch(Exception $e) {}

		// clean tournament session 7 days before
		DB::delete('tournament_sessions', "timestamp<%s", $time - (7 * 60 * 60 * 24));
	}

	/**
	 * Get random string of 0-9a-zA-z with mt_rand,
	 *
	 * @return null
	 */
	private function randomString($length = 10) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[mt_rand(0, strlen($characters) - 1)];
		}
		return $randomString;
	}
}

?>
