<?php

if( !defined('IN_PAGE') ) {
	// No direct access to this PHP file.
	exit();
}


require_once('./config.php');

// P3P header to prevent browswer blocking the cookie from Facebook API
// credits: http://stackoverflow.com/questions/5897901/facebook-php-sdk-session-logic
header('P3P: CP="CAO PSA OUR"');

// Composer autoload
require_once __DIR__ . '/vendor/autoload.php';

// Database handler
DB::$user 	  = $_CONF['db']['user'];
DB::$password = $_CONF['db']['pass'];
DB::$dbName   = $_CONF['db']['name'];
DB::$host     = $_CONF['db']['host'];
DB::$port     = $_CONF['db']['port'];
DB::$encoding = 'utf8';
unset($_CONF['db']);

// Session Manager
require_once('./includes/class.session.php');
new Session($_CONF['session_lifetime']);

// Facebook API
$facebook = new Facebook\Facebook([
    'app_id'     => $_CONF['fb']['appid'],
    'app_secret' => $_CONF['fb']['secret'],
    'default_graph_version' => 'v2.4'
]);
unset($_CONF['fb']);

// System constants
require_once('./includes/constants.php');

// User authorization handler
require_once('./includes/class.auth.php');
$auth = new Auth( defined('REQUIRE_FB') );
$_FB = $auth->getBasicInfo();

// Template engine
if( defined('REQUIRE_TPL') ) {
	$mustache = new Mustache_Engine(array(
	    'loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/views'),
    ));
}

?>