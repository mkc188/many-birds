<?php

if( !defined('IN_PAGE') ) {
	// No direct access to this PHP file.
	exit();
}


class Achievement {
	public $newAchievement = array();

	/**
	 * Return text description for achievement requirement
	 *
	 * @param int counter type
	 * @param int target value
	 * @return string
	 */
	public function getDescription($target, $value) {
		global $_PAGE;

		$description = '';
		switch ($target) {
			case TARGET_PLAYCOUNT:
				$description = 'Play Many Birds for <strong>' . $value . '</strong> time(s)';
				break;

			case TARGET_HIGHSCORE:
				$description = 'Achieve a score <strong>' . $value . '</strong> in <a href="' . $_PAGE['game_online'] . '">online multiplayer mode</a>';
				break;
			
			default:
				$description = '...';
				break;
		}

		return $description;
	}

	/**
	 * Add one to specific counter in achievement_progress for user,
	 * also check if user obtain new achievement
	 *
	 * @param int counter type
	 * @return boolean isNewAchievement
	 */
	public function addOne($target) {
		global $auth;

		if( !$auth->valid() ) {
			return false;
		}

		DB::query("INSERT INTO achievement_progress (uid,target,value) VALUES (%i,%i,1) ON DUPLICATE KEY UPDATE value = value+1;",
			$auth->uid, $target);

		return $this->isAchieved($target);
	}

	/**
	 * Update value to specific counter in achievement_progress for user,
	 * also check if user obtain new achievement
	 *
	 * @param int counter type
	 * @param int new counter vlaue
	 * @return boolean isNewAchievement
	 */
	public function update($target, $value) {
		global $auth;

		if( !$auth->valid() ) {
			return false;
		}

		DB::query("INSERT INTO achievement_progress (uid,target,value) VALUES (%i,%i,%i) ON DUPLICATE KEY UPDATE value = VALUES(value);",
			$auth->uid, $target, $value);

		return $this->isAchieved($target);
	}

	/**
	 * List last two achievement of the list of users obtained
	 *
	 * @param array uid
	 * @return array achievement info
	 */
	public function listUsersMedals($arr) {
		$return = array();

		foreach($arr as $userid) {
			$result = DB::query("SELECT o.uid, o.timestamp, a.name, a.icon_path FROM achievement_owned o
								 LEFT JOIN achievement a ON a.id = o.aid
								 WHERE uid = %i
								 ORDER BY timestamp DESC LIMIT 2;", $userid);

			foreach ($result as $row) {
				// make sure the array key is string,
				// it reduce unexpected problem
				$return['a'.$userid][] = array('achievement' => $row['name'], 'achievement_icon' => $row['icon_path']);
			}
		}

		return $return;
	}

	/**
	 * Check for new achievement of specific target
	 *
	 * @param int counter type
	 * @return boolean isNewAchievement
	 */
	private function isAchieved($target) {
		global $auth;

		if( !$auth->valid() ) {
			return false;
		}

		// get current value for the target
		$current = intval(DB::queryOneField('value', "SELECT value FROM achievement_progress WHERE uid = %i AND target = %i",
			$auth->uid, $target));

		// check unachieved item for meeting target value
		$time = time();
		$result = DB::query("SELECT a.* FROM achievement a
							 LEFT JOIN achievement_owned o ON o.aid = a.id AND o.uid = %i
							 WHERE a.target = %i AND
							 ( a.end_time >= %i OR a.end_time = 0 ) AND
							 o.timestamp IS NULL AND
							 a.value <= %i",
			$auth->uid, $target, $time, $current);

		$return = false;

		foreach($result as $row) {
			$return = true;

			// insert achievement owned record
			DB::insertIgnore('achievement_owned', array(
				'uid'       => $auth->uid,
				'aid'       => $row['id'],
				'timestamp' => $time,
			));

			// store achievement information for later access
			$this->newAchievement[] = array(
					'id'   => $row['id'],
					'name' => $row['name'],
					'icon' => $row['icon_path'],
				);
		}

		return $return;
	}
}

?>