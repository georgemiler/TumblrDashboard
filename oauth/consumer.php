<?php
class OAuthConsumer 
{
	public $key;
	public $secret;

	public function __construct( $key, $secret, $callback_url = NULL ) 
	{
		$this->key 			= $key;
		$this->secret 		= $secret;
		$this->callback_url = $callback_url;
	}

	public function to_string()
	{
		return sprintf( 'OAuthConsumer[key=%s,secret=%s]', $this->key, $this->secret );
	}
}
?>