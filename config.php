<?php
/**
 * Tumblr Dashboard
 * @package config
 * @subpackage core
 */
    
    // define vanity header.
    header( 'X-Powered-By: @Dreyer' );
	
	// set max execution time.
	set_time_limit( 0 );
	
	// set the default timezone to use.
	date_default_timezone_set( 'Europe/London' );
	
	// const.
	define( 'ROOT_PATH',				dirname( __FILE__ ) );
	define( 'CACHE_PATH',				ROOT_PATH . '/cache' );
	define( 'ONLINE',					file_exists( ROOT_PATH . '/ENV.PRODUCTION' ) );	
	define( 'ENVIRONMENT',				( ONLINE ? 'production' : 'development' ) );
	define( 'ROOT_URL', 				'http://localhost/sandbox/tumblr_dashboard' );
	define( 'SITE_NAME',				'Tumblr Dashboard' );

	// tumblr.
	require_once 'config-tumblr.php';

	require_once 'functions.php';
	require_once 'oauth.php';
	require_once 'tumblr.php';

	session_start();

	#echo session_id() . PHP_EOL;
?>