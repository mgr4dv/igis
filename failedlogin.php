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
				<h1 style="text-align:center; font-style:italic; color:#FF7777">Failed Login.</h1>
				<div class="row">
					<div class="col-md-4"></div>
					<div class="col-md-4">
						<form role="form" action="login.php" method="POST">
							<h2>Try again:</h2>
							<input id="uid" name="uid" type="text" class="form-control" placeholder="Username" required autofocus>
							<input id="pwd" name="pwd" type="password" class="form-control" placeholder="Password" required>
							<button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
						</form>
					</div>
				</div>
			</div>
		</div>


	</body>
</html>
