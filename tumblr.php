<?php
class Tumblr 
{	
	public $root_url		= ROOT_URL;

	public $consumer_key 	= TUMBLR_CONSUMER_KEY;
	public $consumer_secret	= TUMBLR_CONSUMER_SECRET;
	
	public $client_key;
	public $client_secret;
	
	public $user_agent 		= 'TumblrViewer 1.0';
	
	public $consumer;
	public $token;
	public $signature_method;
	
	public $http_code;
	
	public $authorize_url	= 'http://www.tumblr.com/oauth/authorize';
	public $request_url	 	= 'http://www.tumblr.com/oauth/request_token';
	public $access_url		= 'http://www.tumblr.com/oauth/access_token';
	public $api_url			= 'http://api.tumblr.com/v2';	

	public function __construct( $client_key = NULL, $client_secret = NULL ) 
	{
		$this->signature_method	= new OAuthSignatureMethod_HMAC_SHA1();
	    $this->consumer 		= new OAuthConsumer( $this->consumer_key, $this->consumer_secret );
		
		if ( ! empty( $client_key ) && ! empty( $client_secret ) )
		{		
			$this->client_key		= $client_key;
			$this->client_secret	= $client_secret;

			$this->token = new OAuthConsumer( $client_key, $client_secret );
	    }
	}

	private function api( $cmd, $http_method, $parameters )
	{
		$http_url = $this->api_url . '/' . $cmd;

	    $response = $this->request( $http_url, $http_method, $parameters );

		$json = $response['body'];

		$output = json_decode( $json, $assoc = TRUE );

		if ( $response['code'] !== 200 )
		{
			#echo 'Response Failed.' . PHP_EOL;
			#echo 'Endpoint: ' . $http_url . PHP_EOL;
			#echo 'Message:  ' . $output['meta']['msg'] . PHP_EOL;
			#exit;
		}

	    return $json;
	}

	public function get( $cmd, $parameters )
	{
	    return $this->api( $cmd, 'GET', $parameters );
	}

	public function post( $cmd, $parameters )
	{
	    return $this->api( $cmd, 'POST', $parameters );
	}

	public function get_token( $type, $parameters = array() )
	{
		$defaults = array();
	    
	    if ( count( $parameters ) )
	    {
			$parameters = array_merge( $defaults, $parameters );
	    }

	    $endpoint = $this->{ $type . '_url' };

	    $response = $this->request( $endpoint, 'GET', $parameters );

	    $token = array();

		parse_str( $response['body'], $token );

		$this->token = new OAuthConsumer( $token['oauth_token'], $token['oauth_token_secret'] );
		
		return $token;
	}

	private function prepare_request( $http_url, $http_method, $parameters = FALSE )
	{
		$parameters = ( $parameters ? $parameters : array() );

		$defaults = array(
			'oauth_version' 	=> '1.0',
			'oauth_nonce'		=> md5( mt_rand() . microtime() ),
			'oauth_timestamp'	=> time(),
			'oauth_consumer_key'=> $this->consumer->key,
		);

		if ( $this->token )
		{
			$defaults['oauth_token'] = $this->token->key;			
		}

		$parameters = array_merge( $defaults, $parameters );

		$request = new OAuthRequest( $http_method, $http_url, $parameters );

		$request->sign_request( $this->signature_method, $this->consumer, $this->token );

		return $request;
	}

	private function request( $http_url, $http_method, $parameters )
	{
	    $request = $this->prepare_request( $http_url, $http_method, $parameters );

		$response = array(
			'body' => '',
			'code' => 0,
		);

		$ch = curl_init();
		
		if ( $http_method === 'POST' )
		{
			curl_setopt( $ch, CURLOPT_POST, TRUE );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $parameters );
		}

		curl_setopt( $ch, CURLOPT_URL, $request->to_url() ) ;
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 2 );
		curl_setopt( $ch, CURLOPT_USERAGENT, $this->user_agent );

		$response['body'] = curl_exec( $ch );
		$response['code'] = curl_getinfo( $ch, CURLINFO_HTTP_CODE );

		curl_close( $ch );

	    return $response;
	}

	public function get_authorize_url( $oauth_token, $callback_url = FALSE ) 
	{		
		$callback_url = ( $callback_url ? $callback_url : $this->root_url );

		return $this->authorize_url .= '?' . http_build_query( array(
			'oauth_token'	=> $oauth_token,
			'oauth_callback'=> $callback_url
		) );
	}
}
?>