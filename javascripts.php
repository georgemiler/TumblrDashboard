<?php
/**
 * Tumblr Dashboard
 * @package includes
 * @subpackage javascripts
 */
?>
<script src="jquery.js"></script>
<script src="jquery.cycle.js"></script>
<script src="bootstrap.js"></script>
<script src="main.js"></script>
<script>
	site.auth = <?php echo ( isset( $_SESSION['token'] ) ? 'true' : 'false' ); ?>;
	site.init();
</script>
