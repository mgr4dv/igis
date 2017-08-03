<? date_default_timezone_set('America/New_York');
require_once("authenticate.php");

include("functions/link.php");
$query = mysqli_query($link,"SELECT guide_id FROM guides WHERE status='current'");
$numActive = mysqli_num_rows($query);
$query = mysqli_query($link,"SELECT guide_id FROM guides WHERE status='abroad'");
$numAbroad = mysqli_num_rows($query);
$query = mysqli_query($link,"SELECT guide_id FROM guides WHERE status='alum'");
$numAlumni = mysqli_num_rows($query);
$query = mysqli_query($link,"SELECT guide_id FROM guides WHERE status='deleted'");
$numDeleted = mysqli_num_rows($query);
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

		<!-- Navigation bar across the top and footer across the bottom -->
		<?include_once("includes/nav.php")?>
		<?include_once("includes/footer.php")?>


		<div class="container">
			<h1>Directory</h1>
			<div class="row">
				<div class="col-md-4"></div>
				<div class="col-md-2">
					Display:
					<select id="displayBox" class="form-control" style="cursor:pointer">
						<option value="Active Guides">Active (<?echo $numActive?>)</option>
						<option value="Abroad Guides">Abroad (<?echo $numAbroad?>)</option>
						<option value="Alumni">Alumni (<?echo $numAlumni?>)</option>
						<? if ($is_exec) echo
						'<option value="Deleted Guides">Deleted ('.$numDeleted.')</option>' ?>
					</select>
				</div>
				<div class="col-md-2">
					Sort by:
					<select id="orderByBox" class="form-control" style="cursor:pointer">
						<option value="Last Name">Last Name</option>
						<option value="First Name">First Name</option>
						<option value="Probie Class">Probie Class</option>
						<option value="Year">Year</option>
						<option value="School">School</option>
						<option value="Last Updated">Updated</option>
					</select>
				</div>
			</div>
			<div class="well" id="guideList">
				<table class="table" id="unseen">
					<thead>
						<th>Name</th><th>School</th><th>Year</th><th>Probie Class</th><th>Email and Phone Number</th>
					</thead>
					<tbody id="directoryBody">

					</tbody>
				</table>
			</div>
		</div>

		<!--Collapsing Table CSS from: http://elvery.net/demo/responsive-tables/ -->
		<!--(768px is the Boostrap standard)-->
		<style>
		@media only screen and (max-width: 768px) {
			table td:nth-child(2),
			table th:nth-child(2),
			table td:nth-child(3),
			table th:nth-child(3),
			table td:nth-child(4),
			table th:nth-child(4) {display: none;}
		}
		</style>

	</body>

	<script>
		var order = 'Last Name'; //default; this is what the page already is
		var order_query = 'lastname';
		var display = 'Active Guides';
		var display_query = 'active';
		var categorize = false;

		$(function() {
			refresh();

			$('#orderByBox').on('change', function() {
				refreshOrder($(this).val());
			});

			$('#displayBox').on('change', function() {
				refreshDisplay($(this).val());
			});
		});


		function refreshDisplay(newDisplay) {
			if (newDisplay!=display) {
				display = newDisplay;
				switch (display) {
					case 'Active Guides':
						display_query="active";
						break;
					case 'Abroad Guides':
						display_query="abroad";
						break;
					case 'Alumni':
						display_query="alumni";
						break;
					case 'Deleted Guides':
						display_query="deleted";
						break;
					default:
						display = 'Active Guides';
						display_query="active";
						break;
				}

				refresh();
			}
		}

		function refreshOrder(newMode) {
			if (newMode!=order) {
				order = newMode;
				switch (order) {
					case 'Last Name':
						order_query="lastname";
						categorize = false;
						break;
					case 'First Name':
						order_query="firstname";
						categorize = false;
						break;
					case 'Probie Class':
						order_query="probieclass";
						categorize = true;
						break;
					case 'Year':
						order_query="year";
						categorize = true;
						break;
					case 'School':
						order_query="school";
						categorize = true;
						break;
					case 'Last Updated':
						order_query="lastupdated";
						categorize = false;
						break;
					default:
						order = 'Last Name';
						order_query="lastname";
						categorize = false;
						break;
				}

				refresh();
			}
		}


		function refresh() {
			$('#directoryBody').html('<td colspan=5 style="text-align:center"><h3>Loading...</h3></td>');
			$.get("functions/printdirectory.php",{
					display:display_query,
					order:order_query,
					cat:categorize,
				}, function(data) {
					$('#directoryBody').html(data);
				});
		}
	</script>
</html>
