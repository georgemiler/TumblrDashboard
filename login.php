<?php
/**
 * Tumblr Dashboard
 * @package action
 * @subpackage login
 */

	// settings.
	require_once 'config.php';

	unset( $_SESSION['token'] );

	// prevent caching.
	no_cache();

	$tumblr = new Tumblr();

	$authorized	= FALSE;

	/* Authorized Credentials */
	if ( isset( $_REQUEST['authorized'] ) )
	{
		$authorized = json_decode( $_REQUEST['authorized'], $assoc = TRUE );

		unset( $_REQUEST['authorized'] );
	};

	/* Authorize Response: Access Granted */
	if ( $authorized && isset( $_REQUEST['oauth_token'], $_REQUEST['oauth_verifier'] ) )
	{
		#var_dump( $_REQUEST, $authorized ); exit;

		$tumblr = new Tumblr( $authorized['token'], $authorized['token_secret'] );

		$parameters = array( 'oauth_verifier' => $_REQUEST['oauth_verifier'] );

		$token = $tumblr->get_token( 'access', $parameters );

	    /* TODO: Store oauth_token, oauth_token_secret for repeat requests to API. */

	    $_SESSION['token'] = $token;

		$tumblr = new Tumblr( $token['oauth_token'], $token['oauth_token_secret'] );

		$user_info = $tumblr->get( 'user/info' );

		$response = array(
			'outcome' => 'success',
			'data' 	  => $user_info,
		);

	    require_once 'tumblr-response.php';
	}
	/* Authorize Response: Access Denied */
	elseif ( $authorized )
	{
		$response = array(
			'outcome' => 'denied',
			'data' 	  => $authorized,
		);

	    require_once 'tumblr-response.php';

	    /*
		echo 'User Denied Access.' . PHP_EOL;
		var_dump( $authorized );
		exit;
		*/
	}
	/* Authentication Start */
	else
	{
		/* Step 1: Request */

		$parameters = array(); #array( 'oauth_callback' => $oauth_callback );

		$token = $tumblr->get_token( 'request', $parameters );

		/* Step 2: Authorize */

		$query_data = array(
			'key'			=> $tumblr->consumer_key,
			'secret'		=> $tumblr->consumer_secret,
			'token'			=> $token['oauth_token'],
			'token_secret'	=> $token['oauth_token_secret'],
			'endpoint'		=> urlencode( $tumblr->authorize_url ),
		);

		$callback_url = $tumblr->root_url . '/login.php?authorized=' . json_encode( $query_data );

		$authorize_url = $tumblr->get_authorize_url( $token['oauth_token'], $callback_url );

		header( 'Location: ' . $authorize_url );
	};
?>