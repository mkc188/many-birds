<?php

if( !defined('IN_PAGE') ) {
	// No direct access to this PHP file.
	exit();
}


require_once('./config.php');

// P3P header to prevent browswer blocking the cookie from Facebook API
// credits: http://stackoverflow.com/questions/5897901/facebook-php-sdk-session-logic
header('P3P: CP="CAO PSA OUR"');

// Database handler
require_once('./includes/meekrodb.2.2.class.php');
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
require_once('./includes/Facebook/facebook.php');
$facebook = new Facebook(array(
	'appId'  => $_CONF['fb']['appid'],
	'secret' => $_CONF['fb']['secret'],
));
unset($_CONF['fb']);

// System constants
require_once('./includes/constants.php');

// User authorization handler
require_once('./includes/class.auth.php');
$auth = new Auth( defined('REQUIRE_FB') );
$_FB = $auth->getBasicInfo();

// Template engine
if( defined('REQUIRE_TPL') ) {
	require './includes/Mustache/Autoloader.php';
	Mustache_Autoloader::register();
	$mustache = new Mustache_Engine(array(
	    'loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/views'),
    ));
}

?>