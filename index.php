<?php
/**
 * Tumblr Dashboard
 * @package index
 * @subpackage home
 */
	
	require_once 'config.php'; 
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title><?php echo SITE_NAME; ?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="<?php echo SITE_NAME; ?>">
		<meta name="author" content="@Dreyer">

		<link rel="stylesheet" href="bootstrap.css">
		<link rel="stylesheet" href="main.css">

		<!-- HTML5 -->
		<!--[if lt IE 9]>
		<script src="html5.js"></script>
		<![endif]-->
	</head>
	<body>
		<div class="navbar navbar-fixed-top">
			<div class="navbar-inner">
				<div class="container">
					<a class="brand" href="#"><?php echo SITE_NAME; ?></a>

					<span id="item-counter" 
						class="badge badge-info auth-true hide">0</span>

					<button type="button" id="login" 
						class="btn btn-small pull-right btn-inverse auth-false">
						<i class="icon-user icon-white"></i> Login
					</button> 
					<button type="button" id="logout" 
						class="btn btn-small pull-right auth-true hide">
						<i class="icon-user"></i> Logout 
						<em id="user-name"></em> 
					</button>
					<button type="button" id="update" 
						class="btn btn-info btn-small pull-right auth-true hide">
						<i class="icon-refresh icon-white"></i> Update 
					</button>

					<button type="button" id="slideshow-play" 
						class="btn btn-small pull-right auth-true hide">
						<i class="icon-play"></i> Play 
					</button>

					<div id="slideshow-controls" class="btn-group pull-right hide">
						<button id="slideshow-prev" class="btn btn-small">
							<i class="icon-step-backward"></i> Prev 
						</button>
						<button id="slideshow-stop" class="btn btn-small">
							<i class="icon-stop"></i> Stop 
						</button>
						<button id="slideshow-next" class="btn btn-small">
							 <i class="icon-step-forward"></i> Next 
						</button>
					</div>

				</div>
			</div>
		</div>

		<div id="slideshow" class="container">
		</div><!-- end: #slideshow -->

		<div id="dashboard" class="container">
		</div><!-- end: #dashboard -->

		<div id="footer" class="container">
			<hr />

			<button id="load-more" class="btn btn-small auth-true hide">
				 Load More
			</button>
		</div><!-- end: #footer -->



		<!-- 
		<ul id="pagination" class="pager hide">
			<li><a href="#">Prev</a></li>
			<li><a href="#">Next</a></li>
		</ul>
		-->

		<!-- JavaScript -->
		<?php require_once 'javascripts.php'; ?>
	</body>
</html>
