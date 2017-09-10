<? date_default_timezone_set('America/New_York');
?>

<!DOCTYPE html>
<html lang="en">

<!-- Header information for webpage (reused except for title) -->
	<head>
		<?
		include_once("includes/head.php");
		?>
	</head>
  
<!-- Body of webpage (not reused, but reusable elements inside) -->
	<body style="padding-top:40px; padding-bottom: 60px">

		
		<div class="container">
			<div class="well">
				<h1 style="text-align:center; font-style:italic; color:#FF7777">Database Error.</h1>
				<div class="row">
					<div class="col-md-4"></div>
					<div class="col-md-4">
						<h2>Sorry, there was a fatal error connecting to the database. Try again in a minute or two and it might have resolved, though.</h2>
						Debugging info: See the attempted database connection in "authenticate.php".
					</div>
				</div>
			</div>
		</div>


	</body>
</html>
