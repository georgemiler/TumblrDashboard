<?php
class OAuthToken 
{
	public $key;
	public $secret;

	public function __construct( $key, $secret )
	{
		$this->key = $key;
		$this->secret = $secret;
	}

	/**
	 * generates the basic string serialization of a token that a server
	 * would respond to request_token and access_token calls with
	 */
	function to_string()
	{
		return sprintf( 
			'oauth_token=%s&oauth_token_secret=%s',
			OAuthUtil::urlencode_rfc3986( $this->key ),
			OAuthUtil::urlencode_rfc3986( $this->secret )
		);
	}
}
?>