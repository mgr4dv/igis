<? date_default_timezone_set('America/New_York');
session_start();
// print_r($_SESSION);
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

	<body>



		

		<div class="container">

			<div class="well" style="position:fixed; top:40%; left:50%; transform:translate(-50%,-50%); width:300px;">

				<form role="form" action="login.php" method="POST">

					<h2 style="margin-top:3px;">Sign in to IGIS:</h2>

					<input id="uid" name="uid" type="text" class="form-control" placeholder="Username / Email Address" required autofocus>

					<input id="pwd" name="pwd" type="password" class="form-control" placeholder="Password" required>

					<button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>

				</form>

			</div>

		</div>





	</body>

	

	<script>



	</script>

</html>

