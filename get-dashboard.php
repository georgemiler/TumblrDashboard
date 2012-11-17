<?php
/**
 * Tumblr Dashboard
 * @package action
 * @subpackage get-dashboard
 */

	// settings.
	require_once 'config.php';

	// prevent caching.
	no_cache();

	$whitelist = array(
		'since_id', 'offset', 'limit'
	);

	// check for previsouly set token.
	if ( isset( $_SESSION['token'] ) )
	{
		$token = $_SESSION['token'];

		$tumblr = new Tumblr( $token['oauth_token'], $token['oauth_token_secret'] );

		$parameters = array();

		if ( ! empty( $_POST ) )
		{
			foreach ( $whitelist as $key ) 
			{
				if ( isset( $_POST[$key] ) )
				{
					$parameters[$key] = $_POST[$key];
				};
			};
		};

		$json = $tumblr->get( 'user/dashboard', $parameters );

		echo $json; exit;		
	};
?>