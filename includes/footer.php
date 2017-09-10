<?date_default_timezone_set('America/New_York');
if (!isset($_SESSION['login'])) {
	session_unset();
	header("location: http://www.uvaguides.org/igis/signin.php");
}
?>
<div class="footer navbar-fixed-bottom navbar-default" style="box-shadow: 0px -5px 5px #888888">
	<div class="container">
		<h4>Logged in as <?
			if ($_SESSION['name'] == 'Leigh Engel')
			{
				echo "Smegma Slugmen";
			}
			if ($_SESSION['name'] == 'Josh Davis')
			{
				echo "Vegan Bitch";
			} else {
				echo $_SESSION['name'];
			}
			?> <small><? echo $privileges?></small></h4>
	</div>
</div>
