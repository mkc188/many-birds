<?php

if( !defined('IN_PAGE') ) {
	// No direct access to this PHP file.
	exit();
}


class Shop {
	/**
	 * List active item of the user
	 *
	 * @return array item list
	 */
	public function listActive() {
		global $auth;

		$return = array();

		if( $auth->valid() ) {
			$results = DB::query("SELECT i.* FROM item_owned o, item i
								  WHERE o.uid = %i AND
								  o.sid = i.id AND
								  o.enabled = 1", $auth->uid);

			foreach ($results as $row) {
				if( $row['type'] == ITEM_BIRDTHEME ) {
					$return['bird'] = $row;
				}
				if( $row['type'] == ITEM_BACKGROUND ) {
					$return['background'] = $row;
				}
			}

			// fallback if no bird or background set
			if( !isset($return['bird']) ) {
				$return['bird'] = $this->defaultBird();
			}
			if( !isset($return['background']) ) {
				$return['background'] = DB::queryFirstRow("SELECT * FROM item WHERE id = %i", DEFAULT_BG);
			}
		}

		return $return;
	}

	/**
	 * Get information about the default bird
	 *
	 * @return array birds info
	 */
	public function defaultBird() {
		return DB::queryFirstRow("SELECT * FROM item WHERE id = %i", DEFAULT_BIRD);;
	}

	/**
	 * List active birds of a list of users
	 *
	 * @param array uid
	 * @return array birds info
	 */
	public function listUsersBirds($arr) {
		$results = DB::query("SELECT o.uid, i.content_path, i.height, i.width, i.name FROM item_owned o
							  LEFT JOIN item i ON i.id = o.sid
							  WHERE o.uid IN (%l) AND
							  o.type = %i AND
							  o.enabled = 1", implode(',', $arr), ITEM_BIRDTHEME);

		$return = array();
		foreach ($results as $row) {
			// make sure the array key is string,
			// it reduce unexpected problem
			$return['b'.$row['uid']] = array('content_path' => $row['content_path'],
											 'height'       => $row['height'],
											 'width'        => $row['width'],
											 'name'         => $row['name']);
		}

		return $return;
	}

	/**
	 * Purchase specific item for user
	 *
	 * @param int item id
	 * @return array(boolean success, string message)
	 */
	public function purchase($itemid) {
		global $auth;

		if( $auth->valid() ) {
			// check is item exists and not owned
			$result = DB::queryFirstRow("SELECT i.id, i.price, i.type, o.timestamp FROM item i
										 LEFT JOIN item_owned o ON o.sid = i.id AND o.uid = %i
										 WHERE i.id = %i LIMIT 1",
				$auth->uid, intval($itemid));

			if( !isset($result['id']) ) {
				return array('success' => false, 'message' => 'Item not exists.');
			}

			if( intval($result['timestamp']) ) {
				return array('success' => false, 'message' => 'Item already purchased.');
			}

			// deduct score point
			require_once('./includes/class.tournament.php');
			$t = new Tournament();

			if( $t->deductScore($result['price']) ) {
				// grant item to user
				$time = time();
				DB::insertIgnore('item_owned', array(
					'uid'       => $auth->uid,
					'sid'       => $result['id'],
					'type'      => $result['type'],
					'enabled'   => 0,
					'timestamp' => $time,
				));
				
				$this->enable($result['id']);
				return array('success' => true, 'message' => '');

			} else {
				return array('success' => false, 'message' => 'Not enough score to purchase.');
			}

		} else {
			return array('success' => false, 'message' => 'Please login first.');
		}
	}

	/**
	 * Enable specific item for user
	 *
	 * @param int item id
	 * @return array(boolean success, string message)
	 */
	public function enable($itemid) {
		global $auth;

		if( $auth->valid() ) {
			// check is item owned
			$type = DB::queryFirstField("SELECT type FROM item_owned WHERE sid = %i AND uid = %i",
				intval($itemid), $auth->uid);

			if( intval($type) == 0 ) {
				return array('success' => false, 'message' => 'Item not owned. Please purchase first.');
			}

			// enable item for user
			DB::query("UPDATE item_owned SET enabled = 0 WHERE uid = %i AND type = %i", $auth->uid, $type);
			DB::query("UPDATE item_owned SET enabled = 1 WHERE uid = %i AND sid = %i", $auth->uid, intval($itemid));

			return array('success' => true, 'message' => '');

		} else {
			return array('success' => false, 'message' => 'Please login first.');
		}
	}

	/**
	 * Grant default items to user,
	 * since method called by auth class, param won't be verifed
	 */
	public function grantDefaultItem($uid) {
		$time = time();
		DB::insertIgnore('item_owned', array(
			'uid'       => $uid,
			'sid'       => DEFAULT_BIRD,
			'type'      => ITEM_BIRDTHEME,
			'enabled'   => 1,
			'timestamp' => $time,
		));
		DB::insertIgnore('item_owned', array(
			'uid'       => $uid,
			'sid'       => DEFAULT_BG,
			'type'      => ITEM_BACKGROUND,
			'enabled'   => 1,
			'timestamp' => $time,
		));
	}
}

?>