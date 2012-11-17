<?php
/**
 * Tumblr Dashboard
 * @package action
 * @subpackage get-user
 */

	// settings.
	require_once 'config.php';

	// prevent caching.
	no_cache();

	if ( ! isset( $_SESSION['token'] ) )
	{
		$response = array( 'outcome' => 'error', 'msg' => 'Session Token Expired' );
	}
	else
	{
		$token = $_SESSION['token'];

		if ( ! isset( $token['oauth_token'], $token['oauth_token_secret'] ) )
		{
			$response = array( 'outcome' => 'error', 'msg' => 'Session Token Malformed' );
		}
		else
		{
			$tumblr = new Tumblr( $token['oauth_token'], $token['oauth_token_secret'] );

			$json = $tumblr->get( 'user/info' );

			$response = json_decode( $json, $assoc = TRUE );

			$response['outcome'] = 'success';
			$response['msg'] 	 = 'User Information Retrieved';
		};
	};

	// if not success delete the session just in case.
	if ( $response['outcome'] !== 'success' )
	{
		unset( $_SESSION['token'] );
	};

	header( 'Content-Type: application/json');
	echo json_encode( $response );
	exit;
?>