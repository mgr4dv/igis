<? date_default_timezone_set('America/New_York');
require_once("authenticate.php");

$guideList=mysqli_query($link,"SELECT firstname, lastname, guide_id FROM guides WHERE status='current'");
$guides = "[";
$guideIDs = "[";
while ($guide=mysqli_fetch_array($guideList)) {
	$guides = $guides."\"".$guide['firstname']." ".$guide['lastname']."\",";
	$guideIDs = $guideIDs.$guide['guide_id'].",";
}
$guides = substr($guides, 0, -1)."]"; //cut off the final comma and then close the bracket
$guideIDs = substr($guideIDs, 0, -1)."]"; //cut off the final comma and then close the bracket

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
			<div class="container">
			<h1>Office Hour Public Schedule</h1>
			<div style="text-align:center">
				<br>
			</div>
			<div class="well" id="oh_log">

			</div>
		</div>
		</div>

	</body>

	<script>

	getOhLog();


	function getOhLog(type){
		$('#oh_log').html("Loading...");
		$.post("functions/getohlogpublic.php",{
		},function(data){
			$('#oh_log').html(data);
		});
	}



	</script>
</html>
