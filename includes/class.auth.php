<?php

if( !defined('IN_PAGE') ) {
	// No direct access to this PHP file.
	exit();
}


class Auth {
	public $uid = 0;
	public $profile = array();

	/**
	 * Constructor
	 */
	public function __construct($force_login) {
		global $_CONF, $_PAGE, $facebook;

		// set access token in seesion (if any)
		if( !empty($_SESSION['access_token']) ) {
		    $facebook->setAccessToken($_SESSION['access_token']);
		}

		if( $facebook->getUser() > 0 ) {
			/* 
			 * From FB SDK, a non-zero uid returned, hence we assume user logined in,
			 * to verify user has an active access token, manually call:
			 *
			 *     $this->getGraphProfile();
			 *
			 * instead, note: calling this function for every page would be a performance impact.
			 */
			$this->uid = $facebook->getUser();
		}

		if( defined('REQUIRE_FB') && $this->uid == 0 ) {
			// page requires facebook logined, clear current session (if any)
			$this->logout();

			// starts new session
			new Session($_CONF['session_lifetime']);
			$_SESSION['oauth_verify'] = md5($_CONF['oauth_secret'] . basename($_SERVER['REQUEST_URI']));

			// redirect user to oauth page
			header('Location: ' . ($_PAGE['ouath'] . "?origin=" . $_SERVER['REQUEST_URI']));
			exit();
		}
	}

	/**
	 * Check if valid session
	 *
	 * @return boolern
	 */
	public function valid() {
		return ( $this->uid != 0 );
	}

	/**
	 * Handle login response
	 *
	 * @param string token
	 * @return boolern
	 */
	public function login($signed_request) {
		global $facebook;

		$success = false;

		$data = $this->parse_signed_request($signed_request);
		if( !is_array($data) ) {
			// bad JSON signuature
			return false;
		}

		// test token with Graph API
		$token = $this->tokenFromOAuth($data['code']);
		$facebook->setAccessToken($token);
		$result = $this->getGraphProfile();

		if( $result['success'] ) {
			// get extended access token from Facebook
			$facebook->setExtendedAccessToken();
			$token = $facebook->getAccessToken();
			$facebook->setAccessToken($token);

			$this->profile = $result['profile'];
			$_SESSION['access_token'] = $token;
			$this->update($token);

			// remove oauth_verify if the user login via redirection to oauth page
			unset($_SESSION['oauth_verify']);
		}

		return $result['success'];
	}

	/**
	 * Handle logout response
	 *
	 * @return boolern
	 */
	public function logout() {
		global $facebook;

		// clear access token and session
		$facebook->destroySession();
		session_destroy();

		return true;
	}

	/**
	 * Get username and name of current user
	 *
	 * @return array
	 */
	public function getNames() {
		if( !$this->valid() ) {
			return array();
		}
		return DB::queryFirstRow("SELECT name, username FROM users WHERE uid = %s LIMIT 1", $this->uid);
	}

	/**
	 * Get basic information of user to put in navbar
	 *
	 * @return array
	 */
	public function getBasicInfo() {
		global $facebook;

		return array('fb_uid'  => ( $this->uid ) ? $this->uid : 0,
					 'fb_pic'  => ( $this->uid ) ? "https://graph.facebook.com/{$this->uid}/picture" : 'img/transparent.png',
			);
	}

	/**
	 * Get friend list fields from user table
	 *
	 * @return array
	 */
	public function getFriendlistDetail() {
		if( !$this->valid() ) {
			return array();
		}
		return DB::queryFirstRow("SELECT friend_list_time as time, friend_list_etag as etag FROM users WHERE uid = %s LIMIT 1", $this->uid);
	}

	/**
	 * Update friend list fields from user table
	 *
	 * @return null
	 */
	public function setFriendlistDetail($time, $etag) {
		if( !$this->valid() ) {
			return false;
		}

		DB::update('users', array(
			'friend_list_time' => $time,
			'friend_list_etag' => $etag,
		), "uid=%i", $this->uid);
	}

	/**
	 * Get user profile from Graph
	 *
	 * @return array (boolern success, array profile)
	 */
	private function getGraphProfile() {
		global $facebook;

		$success = false;
		$user_profile = array();
		try {
			$user_profile = $facebook->api('/me','GET');
			$success = true;
		} catch(FacebookApiException $e) {}

		return array('success' => $success, 'profile' => $user_profile);
	}

	/**
	 * Update or create user profile if needed
	 *
	 * @param string token
	 */
	private function update($token) {
		// get profile from graph if no cached profile
		if( !isset($this->profile['id']) || $this->profile['id'] != $this->uid ) {
			$result = $this->getGraphProfile();
			if( $result['success'] ) {
				$this->profile = $result['profile'];
			} else {
				return;
			}
		}

		// query the user table
		$result = DB::queryFirstRow("SELECT uid, updated, access_token FROM users WHERE uid = %i LIMIT 1", $this->profile['id']);

		if( $result['uid'] > 0 ) {
			// user exists in database

			$isOutdated = (strcmp($result['updated'], $this->profile['updated_time']) != 0);
			$isDiffToken = (strcmp($token, $result['access_token']) != 0);

			if( $isOutdated || $isDiffToken ) {
				// profile updated
				DB::update('users', array(
					'uid'              => $this->profile['id'],
					'username'         => $this->profile['username'],
					'name'             => $this->profile['name'],
					'updated'          => $this->profile['updated_time'],
					'access_token'     => $token,
				), "uid=%i", $this->profile['id']);
			}

		} else {
			// user not exists in database
			DB::insert('users', array(
				'uid'              => $this->profile['id'],
				'username'         => $this->profile['username'],
				'name'             => $this->profile['name'],
				'updated'          => $this->profile['updated_time'],
				'access_token'     => $token,
			));

			// create default theme for user
			require_once('./includes/class.shop.php');
			$s = new Shop();
			$s->grantDefaultItem($this->profile['id']);
		}
	}

	/**
	 * Exchange code for token with Facebook Graph
	 *
	 * @param string code
	 * @return string token
	 */
	public function tokenFromOAuth($code, $redirect_uri = '') {
		global $facebook;

    	$path = "https://graph.facebook.com/oauth/access_token";
    	$query = array(
            'client_id'     => $facebook->getAppId(),
            'client_secret' => $facebook->getAppSecret(),
            'redirect_uri'  => $redirect_uri,
            'code'          => $code,
        );

		$response = file_get_contents($path . "?" . http_build_query($query));
		$params = null;
		parse_str($response, $params);
		return $params['access_token'];
	}

	/**
	 * Get JSON content from Facebook signed request
	 * credits: https://developers.facebook.com/docs/facebook-login/using-login-with-games/
	 *
	 * @param string signed request
	 * @return string JSON array
	 */
	private function parse_signed_request($signed_request) {
		global $facebook;

		list($encoded_sig, $payload) = explode('.', $signed_request, 2); 

		// decode the data
		$sig = $this->base64_url_decode($encoded_sig);
	 	$data = json_decode($this->base64_url_decode($payload), true);

		// confirm the signature
		$expected_sig = hash_hmac('sha256', $payload, $facebook->getAppSecret(), $raw = true);
		if ($sig !== $expected_sig) {
			error_log('Bad Signed JSON signature!');
			return null;
		}

		return $data;
	}

	/**
	 * Base64 URL decode
	 *
	 * @param string signed request
	 * @return string JSON array
	 */
	private function base64_url_decode($input) {
		return base64_decode(strtr($input, '-_', '+/'));
	}
}

?>