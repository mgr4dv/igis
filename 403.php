<? date_default_timezone_set('America/New_York');
require_once("authenticate.php");

?>


<!DOCTYPE html>

<html lang="en">

<!-- Header information for webpage (reused except for title) -->
	<head>
		<?
		include_once("includes/head.php");
		?>

		<style>
			.lastentry {
				border-right: 1px solid #ddd;
			}

			.border_top {
				border-top: 2px solid #000	;
			}

			.responsive-iframe-container {
				position: relative;
				padding-bottom: 56.25%;
				padding-top: 30px;
				height: 0;
				overflow: hidden;
			}

			.responsive-iframe-container iframe,
			.responsive-iframe-container object,
			.responsive-iframe-container embed {
				position: absolute;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
			}
		</style>
	</head>
	<!-- Body of webpage (not reused, but reusable elements inside) -->
	<body style="padding-top:40px; padding-bottom: 60px">
			<?include_once("includes/nav.php")?>
			<?include_once("includes/footer.php")?>
			<h1>403: Permission denied</h2></br>
			It looks like you tried accessing something you shouldn't've 
		</div>
		</div>

	</body>

<html>