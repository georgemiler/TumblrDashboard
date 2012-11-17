<?php
/**
 * Tumblr Dashboard
 * @package includes
 * @subpackage functions
 */
    
	// prevent caching.
	function no_cache()
	{
		header( 'Content-Type: text/plain' );
		header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );		
	};
?>