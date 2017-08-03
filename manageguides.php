<? date_default_timezone_set('America/New_York');
require_once("authenticate.php"); ?>

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
			<h1>Manage Guides</h1>
			<div class="row">
				<div class="col-md-4"></div>
				<div class="col-md-2">
					Display:
					<select id="displayBox" class="form-control" style="cursor:pointer">
						<option value="Active Guides">Active Guides</option>
						<option value="Abroad Guides">Abroad Guides</option>
						<option value="Alumni">Alumni</option>
						<? if ($is_exec) echo
						'<option value="Deleted Guides">Deleted Guides</option>' ?>
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
				<div class="col-md-4">
					Make the selected guides:
					<div id="activeButtonGroup" class="btn-group">
						<button id="abroadButton" class="btn btn-warning" onclick="makeGuides('abroad')">Abroad</button>
						<button id="alumButton" class="btn btn-primary" onclick="makeGuides('alum')">Alumni</button>
						<button id="deleteButton" class="btn btn-danger" onclick="makeGuides('deleted')">Deleted</button>
					</div>
					<div id="abroadButtonGroup" class="btn-group" style="display:none">
						<button id="activeButton" class="btn btn-success" onclick="makeGuides('current')">Active</button>
						<button id="alumButton" class="btn btn-primary" onclick="makeGuides('alum')">Alumni</button>
						<button id="deleteButton" class="btn btn-danger" onclick="makeGuides('deleted')">Deleted</button>
					</div>
					<div id="alumButtonGroup" class="btn-group" style="display:none">
						<button id="activeButton" class="btn btn-success" onclick="makeGuides('current')">Active</button>
						<button id="abroadButton" class="btn btn-warning" onclick="makeGuides('abroad')">Active but Abroad</button>
						<button id="deleteButton" class="btn btn-danger" onclick="makeGuides('deleted')">Deleted</button>
					</div>
					<div id="deletedButtonGroup" class="btn-group" style="display:none">
						<button id="activeButton" class="btn btn-success" onclick="makeGuides('current')">Active</button>
						<button id="abroadButton" class="btn btn-warning" onclick="makeGuides('abroad')">Active but Abroad</button>
						<button id="alumButton" class="btn btn-primary" onclick="makeGuides('alum')">Alumni</button>
					</div>
				</div>
			</div>
			<div class="well" id="guideList">
				<table class="table" id="unseen">
					<thead>
						<th>Name</th><th>School</th><th>Year</th><th>Probie Class</th><th><button class="btn btn-default btn-xs" onclick="selectNone()">Clear</button></th>
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


		function makeGuides(status) {
			var changeto;
			switch (status) {
				case 'active':
					changeto = 'current';
					break;
				case 'abroad':
					changeto = 'abroad';
					break;
				case 'alumni':
					changeto = 'alum';
					break;
				case 'deleted':
					changeto = 'deleted';
					break;
			}
		}


		function refreshDisplay(newDisplay) {
			if (newDisplay!=display) {
				display = newDisplay;
				switch (display) {
					case 'Active Guides':
						display_query="active";
						$('#activeButtonGroup').show()
						$('#abroadButtonGroup').hide()
						$('#alumButtonGroup').hide()
						$('#deletedButtonGroup').hide()
						break;
					case 'Abroad Guides':
						display_query="abroad";
						$('#activeButtonGroup').hide()
						$('#abroadButtonGroup').show()
						$('#alumButtonGroup').hide()
						$('#deletedButtonGroup').hide()
						break;
					case 'Alumni':
						display_query="alumni";
						$('#activeButtonGroup').hide()
						$('#abroadButtonGroup').hide()
						$('#alumButtonGroup').show()
						$('#deletedButtonGroup').hide()
						break;
					case 'Deleted Guides':
						display_query="deleted";
						$('#activeButtonGroup').hide()
						$('#abroadButtonGroup').hide()
						$('#alumButtonGroup').hide()
						$('#deletedButtonGroup').show()
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
			$.get("functions/printmanageguides.php",{
					display:display_query,
					order:order_query,
					cat:categorize,
				}, function(data) {
					$('#directoryBody').html(data);
					$('#guideList tr').click(function(event) {
						if (event.target.type !== 'checkbox' && event.target.type !== 'button') {
							$(':checkbox', this).trigger('click');
						}
					});
				});
		}

		function selectNone() {
			$(':checkbox:checked').each( function() {
				$(this).prop('checked',false);
			});
		}

		//get list of selected:
		function makeGuides(status) {
			var guides = [];
			$(':checkbox:checked').each( function() {
				guides[guides.length] = $(this).val();
			});
			//alert('Selected Guides: ' + guides); //for debugging
			$('#directoryBody').html('<td colspan=5 style="text-align:center"><h3>Submitting changes...</h3></td>');

			//submit selected:
			var numReturned = 0;
			for (i=0; i<guides.length; i++) {
				guideID = guides[i];
				$.post("functions/editguide.php",{
					id:guideID,
					status:status,
					overridelastupdated:1
				}, function(data) {
					numReturned++
					if(data!='') {
						//alert(data+"\n\nnumReturned="+numReturned); //for debugging
					}
					if (numReturned==(guides.length)) {
						//alert('Refreshing!') //for debugging
						refresh(); //only refresh on the last one
					}
				});
			}
		}
	</script>
</html>
