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

		// use CURL to make Graph request in order to include eTag
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://graph.facebook.com/me/friends?limit=5000&access_token='.$facebook->getAccessToken($_SESSION['access_token']));

		if( $etag != '' ) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('If-None-Match: ' . $etag));
		}
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		// send request
		$response = curl_exec($ch);

		list($headers, $body) = explode("\r\n\r\n", $response, 2);
		$header = $this->http_parse_headers($headers);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        $return = array();
		if( $http_code != 304 ) {
			// go thru the list to build array for query
			$graph_result = json_decode($body, true);
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
			$auth->setFriendlistDetail(time(), $header['Etag']);

		} else {
			// not modified, extend cache time
			$auth->setFriendlistDetail(time(), $etag);
		}

		return $return;
	}

	/**
	 * Parse string http header into array
	 * http://stackoverflow.com/questions/6368574/how-to-get-the-functionality-of-http-parse-headers-without-pecl
	 *
	 * @param string http header
	 * @return array key-value header
	 */
	private function http_parse_headers($header) {
        $retVal = array();
        $fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header));
        foreach( $fields as $field ) {
            if( preg_match('/([^:]+): (.+)/m', $field, $match) ) {
                $match[1] = preg_replace('/(?<=^|[\x09\x20\x2D])./e', 'strtoupper("\0")', strtolower(trim($match[1])));
                if( isset($retVal[$match[1]]) ) {
                    $retVal[$match[1]] = array($retVal[$match[1]], $match[2]);
                } else {
                    $retVal[$match[1]] = trim($match[2]);
                }
            }
        }
        return $retVal;
    }   
}

?>