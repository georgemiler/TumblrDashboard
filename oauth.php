<?php
$oauth = array( 
	'exception', 
	'consumer', 
	'token', 
	'signature-method', 
	'request', 
	'server', 
	'data-store', 
	'util' 
);

foreach ( $oauth as $i ) 
{
	require_once sprintf( 'oauth/%s.php', $i );
}
?>
