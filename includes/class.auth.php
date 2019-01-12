<?php

if( !defined('IN_PAGE') ) {
	// No direct access to this PHP file.
	exit();
}


class Auth {
	public $uid = 0;
	public $profile = array();
	private $accessToken;

	/**
	 * Constructor
	 */
	public function __construct($force_login) {
		global $_CONF, $_PAGE, $facebook;

		if( !empty($_SESSION['fb_access_token']) ) {
			// set default access token for following requests
			$facebook->setDefaultAccessToken($_SESSION['fb_access_token']);

			$helper = $facebook->getJavaScriptHelper();

			// Grab the signed request entity
			$sr = $helper->getSignedRequest();

			// Get the user ID if signed request exists
			$user = $sr ? $sr->getUserId() : null;

			if( $user ) {
				// user logined in, set uid
				$this->uid = $user;

				$this->accessToken = $_SESSION['fb_access_token'];

				$graphProfile = $this->getGraphProfile();
				if( $graphProfile['success'] ) {
					$this->profile = $graphProfile['profile'];
				}
			}
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
	public function login() {
		global $facebook;

		$success = false;

		$helper = $facebook->getJavaScriptHelper();

		try {
			$accessToken = $helper->getAccessToken();
		} catch( Exception $e ) { }

		if( isset($accessToken) ) {
			$success = true;

			$oAuth2Client = $facebook->getOAuth2Client();
			$accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);

			$token = $accessToken->getValue();
			$_SESSION['fb_access_token'] = $token;

			$facebook->setDefaultAccessToken($accessToken);
			$this->update();

			// remove oauth_verify if the user login via redirection to oauth page
			unset($_SESSION['oauth_verify']);
		}

		return $success;
	}

	/**
	 * Handle logout response
	 *
	 * @return boolern
	 */
	public function logout() {
		global $facebook;

		// clear access token and session
		session_destroy();

		return true;
	}

	/**
	 * Get name of current user
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
			$response = $facebook->get('/me?fields=id,name,updated_time');
			$user_profile = $response->getGraphUser();

			$success = true;
		} catch(Exception $e) { }

		return array('success' => $success, 'profile' => $user_profile);
	}

	/**
	 * Update or create user profile if needed
	 */
	private function update() {
		// get profile from graph if no cached profile
		if( !isset($this->profile['id']) || $this->profile['id'] != $this->uid ) {
			$result = $this->getGraphProfile();
			if( $result['success'] ) {
				$this->profile = $result['profile'];
			} else {
				return;
			}
		}

		$token = ( isset($_SESSION['fb_access_token']) ) ? $_SESSION['fb_access_token'] : null;

		// query the user table
		$result = DB::queryFirstRow("SELECT uid FROM users WHERE uid = %i LIMIT 1", $this->profile['id']);

		// user not exists in database
		DB::insertUpdate('users', array(
			'uid'      => $this->profile['id'],
			'username' => $this->profile['id'],
			'name'     => $this->profile['name'],
			'updated'  => $this->profile['updated_time'],
		));

		// grant default theme for user just created their account
		if( !isset($result['uid']) ) {
			require_once('./includes/class.shop.php');
			$s = new Shop();
			$s->grantDefaultItem($this->profile['id']);
		}
	}
}

?>