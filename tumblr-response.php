<?php
/**
 * Tumblr Dashboard
 * @package index
 * @subpackage home
 */

	header( 'Content-Type: text/html' );

	$outcome = $response['outcome'];
	$json = json_encode( $response['data'] );
		
	$args = '"' . $outcome . '", ' . $json;
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
	</head>
	<body>
		<?php require_once 'javascripts.php'; ?>
		<script>						
			var handle = window.open('', 'loginWindow'); 
			handle.site.onTumblrResponse(<?php echo $args; ?>);
			window.close();
		</script>
	</body>
</html>
