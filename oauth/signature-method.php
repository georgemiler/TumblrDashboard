<?php
/**
 * A class for implementing a Signature Method
 * See section 9 ("Signing Requests") in the spec
 */
abstract class OAuthSignatureMethod 
{

	/**
	 * Needs to return the name of the Signature Method (ie HMAC-SHA1)
	 * @return string
	 */
	abstract public function get_name();

	/**
	 * Build up the signature
	 * NOTE: The output of this function MUST NOT be urlencoded.
	 * the encoding is handled in OAuthRequest when the final
	 * request is serialized
 	 * @param OAuthRequest $request
	 * @param OAuthConsumer $consumer
	 * @param OAuthToken $token
	 * @return string
	 */
	abstract public function build_signature( $request, $consumer, $token );

	/**
	 * Verifies that a given signature is correct
	 * @param OAuthRequest $request
	 * @param OAuthConsumer $consumer
	 * @param OAuthToken $token
	 * @param string $signature
	 * @return bool
	 */
	public function check_signature( $request, $consumer, $token, $signature )
	{
		$built = $this->build_signature( $request, $consumer, $token );

		return $built == $signature;
	}
}

/**
 * The HMAC-SHA1 signature method uses the HMAC-SHA1 signature algorithm as defined in [RFC2104] 
 * where the Signature Base String is the text and the key is the concatenated values (each first 
 * encoded per Parameter Encoding) of the Consumer Secret and Token Secret, separated by an '&' 
 * character (ASCII code 38) even if empty.
 *   - Chapter 9.2 ("HMAC-SHA1")
 */
class OAuthSignatureMethod_HMAC_SHA1 extends OAuthSignatureMethod 
{
	public function get_name() 
	{
		return 'HMAC-SHA1';
	}

	public function build_signature( $request, $consumer, $token ) 
	{
		$base_string = $request->get_signature_base_string();
		$request->base_string = $base_string;

		$key_parts = array( $consumer->secret, ( $token ? $token->secret : '' ) );

		$key_parts = OAuthUtil::urlencode_rfc3986( $key_parts );

		$key = implode( '&', $key_parts );

		return base64_encode( hash_hmac( 'sha1', $base_string, $key, TRUE ) );
	}
}

/**
 * The PLAINTEXT method does not provide any security protection and SHOULD only be used 
 * over a secure channel such as HTTPS. It does not use the Signature Base String.
 *   - Chapter 9.4 ("PLAINTEXT")
 */
class OAuthSignatureMethod_PLAINTEXT extends OAuthSignatureMethod 
{
	public function get_name() 
	{
		return 'PLAINTEXT';
	}

	/**
	 * oauth_signature is set to the concatenated encoded values of the Consumer Secret and 
	 * Token Secret, separated by a '&' character (ASCII code 38), even if either secret is 
	 * empty. The result MUST be encoded again.
	 *   - Chapter 9.4.1 ("Generating Signatures")
	 *
	 * Please note that the second encoding MUST NOT happen in the SignatureMethod, as
	 * OAuthRequest handles this!
	 */
	public function build_signature( $request, $consumer, $token ) 
	{
		$key_parts = array( $consumer->secret, ($token ? $token->secret : '' ) );

		$key_parts = OAuthUtil::urlencode_rfc3986( $key_parts );

		$key = implode( '&', $key_parts );

		$request->base_string = $key;

		return $key;
	}
}

/**
 * The RSA-SHA1 signature method uses the RSASSA-PKCS1-v1_5 signature algorithm as defined in 
 * [RFC3447] section 8.2 (more simply known as PKCS#1), using SHA-1 as the hash function for 
 * EMSA-PKCS1-v1_5. It is assumed that the Consumer has provided its RSA public key in a 
 * verified way to the Service Provider, in a manner which is beyond the scope of this 
 * specification.
 *   - Chapter 9.3 ("RSA-SHA1")
 */
abstract class OAuthSignatureMethod_RSA_SHA1 extends OAuthSignatureMethod 
{
	public function get_name() 
	{
		return 'RSA-SHA1';
	}

	// Up to the SP to implement this lookup of keys. Possible ideas are:
	// (1) do a lookup in a table of trusted certs keyed off of consumer
	// (2) fetch via http using a url provided by the requester
	// (3) some sort of specific discovery code based on request
	//
	// Either way should return a string representation of the certificate
	protected abstract function fetch_public_cert( &$request );

	// Up to the SP to implement this lookup of keys. Possible ideas are:
	// (1) do a lookup in a table of trusted certs keyed off of consumer
	//
	// Either way should return a string representation of the certificate
	protected abstract function fetch_private_cert( &$request );

	public function build_signature( $request, $consumer, $token ) 
	{
		$base_string = $request->get_signature_base_string();
		$request->base_string = $base_string;

		// Fetch the private key cert based on the request
		$cert = $this->fetch_private_cert( $request );

		// Pull the private key ID from the certificate
		$private_key_id = openssl_get_privatekey( $cert );

		// Sign using the key
		$ok = openssl_sign( $base_string, $signature, $private_key_id );

		// Release the key resource
		openssl_free_key( $private_key_id );

		return base64_encode( $signature );
	}

	public function check_signature( $request, $consumer, $token, $signature ) 
	{
		$decoded_sig = base64_decode( $signature );

		$base_string = $request->get_signature_base_string();

		// Fetch the public key cert based on the request
		$cert = $this->fetch_public_cert( $request );

		// Pull the public key ID from the certificate
		$public_key_id = openssl_get_publickey( $cert );

		// Check the computed signature against the one passed in the query
		$ok = openssl_verify( $base_string, $decoded_sig, $public_key_id );

		// Release the key resource
		openssl_free_key( $public_key_id );

		return $ok == 1;
	}
}
?>