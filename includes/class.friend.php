<?php

if( !defined('IN_PAGE') ) {
	// No direct access to this PHP file.
	exit();
}


class Friend {
	/**
	 * Check friend list cache need update or not
	 *
	 * @return array
	 */
	public function updateCache() {
		global $_CONF, $auth;

		if( !$auth->valid() ) {
			// return empty array if not logined in
			return array();
		}

		// check if the cache expired or not
		$cache = $auth->getFriendlistDetail();

		// update the cache if epired
		$time = time();
		if( $time > ($cache['time'] + $_CONF['max_cache_friend_time']) ) {
			return $this->update($cache['etag']);
		} else {
			return array();
		}
	}

	/**
	 * Get friend list in an array
	 *
	 * @return array
	 */
	public function get() {
		global $_CONF, $auth;

		if( !$auth->valid() ) {
			// return empty array if not logined in
			return array();
		}

		// check if the cache updated or not
		$cache = $this->updateCache();

		if( count($cache) > 0 ) {
			return $cache;
		} else {
			// obtain cached friend list from database
			return DB::query("SELECT friendid as id, name FROM friendship WHERE uid = %i", $auth->uid);
		}
	}

	/**
	 * Update friend list from Facebook Graph if needed
	 *
	 * @return array (new friend list)
	 */
	private function update($etag = '') {
		global $_CONF, $auth, $facebook;

		if( !$auth->valid() ) {
			// return empty array if not logined in
			return array();
		}

		$return = array();

		// use Facebook SDK to request friend list 
		try {
			if( strlen($etag) > 0 ) {
				$response = $facebook->get('/me/friends?limit=100', null, $etag);
			} else {
				$response = $facebook->get('/me/friends?limit=100');
			}

			if( $response->getHttpStatusCode() == 304 ) {
				// not modified, extend cache time
				$auth->setFriendlistDetail(time(), $etag);

			} else {
				// parse graph response and update the database
				$graph_result = $response->getDecodedBody();

				$keys = array();
				$data = array();
				foreach($graph_result['data'] as $row) {
					$id = intval($row['id']);

					$keys[] = $id;
					$data[] = sprintf("(%d,%d,'%s')", $auth->uid, $id, mysqli_real_escape_string(DB::getMDB()->internal_mysql, $row['name']));
					$return[] = array('id' => $id, 'name' => $row['name']);
				}

				// sync the friend list:
				// 1. insert not in database cache
				DB::query("INSERT INTO friendship (uid,friendid,name) VALUES " . implode(',', $data) . " ON DUPLICATE KEY UPDATE name = VALUES(name)");

				// 2. delete not in facebook friend list
				DB::query("DELETE FROM friendship WHERE uid = %i AND friendid NOT IN (" . implode(',', $keys) . ")", $auth->uid);

				// 3. update cache time
				$auth->setFriendlistDetail(time(), $response->getETag());

			}
		} catch(Exception $e) { print_r($e); exit(); }

		return $return;
	}
}

?>