<? date_default_timezone_set('America/New_York');
require_once("authenticate.php");
$permssion_level = 1;
include("permission.php");

$currYear = date('Y');
//if the current date is before June 1st of the current year...
if (time()<mktime(0,0,0,7,31,$currYear)) {
	//must be spring semester
	$semester = 'Spring';
	$startDate = date('Y-m-d',mktime(0,0,0,1,1,$currYear));
	$endDate = date('Y-m-d',mktime(0,0,0,6,1,$currYear));
	$tourReq = $igis_settings['tour_req_spring'];
	$hisReq = $igis_settings['his_req_spring'];
	$admReq = $igis_settings['adm_req_spring'];
	$TIPReq = $igis_settings['tip_req_spring'];
} else { //otherwise...
	//must be fall semester
	$semester = 'Fall';
	$startDate = date('Y-m-d',mktime(0,0,0,6,2,$currYear));
	$endDate = date('Y-m-d',mktime(0,0,0,12,31,$currYear));
	$tourReq = $igis_settings['tour_req_fall'];
	$hisReq = $igis_settings['his_req_fall'];
	$admReq = $igis_settings['adm_req_fall'];
	$TIPReq = $igis_settings['tip_req_fall'];
}
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
		<?include_once("includes/nav.php")?>
		<?include_once("includes/footer.php")?>


		<div class="container">
			<h1>Tour Count List</h1>
			<div class="row">
				<div class="col-md-5"></div>
				<div class="col-md-2">
					Order by:
					<select id="orderByBox" class="form-control" style="cursor:pointer; display:inline-block">
						<option value="Tours">Number of Tours</option>
						<option value="Last Name">Last Name</option>
						<option value="First Name">First Name</option>
						<option value="Probie Class">Probie Class</option>
						<option value="Year">Year</option>
						<option value="School">School</option>
					</select>
				</div>
			</div>
			<div class="well">
				<table class="table respTable" id="pointsListTable">
					<thead>
						<th>Name</th>
						<th>School</th>
						<th>Year</th>
						<th>Probie Class</th>
						<th style="min-width:60px">Tours</th>
					</thead>
					<tbody id="directoryBody">

					</tbody>
				</table>
			</div>
		</div>

		<div class="modal" id="toursModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header" style="text-align:center">
						<h3 class="modal-title" style="display:inline"><strong><span id="toursDialogName"></span>'s Tours</strong></h3>
						<h4 style="margin-top:0px; margin-bottom:5px"><em><?echo $semester." ".$currYear?> total:</em> <span id="guideTours"></span></h4>
						<h4 style="margin-top:0px; margin-bottom:0px"><em>Overall total:</em> <span id="totalToursEver"></span> <br><small>(over <span id="semestersInUGS"></span>)</small></h4>
						<span id="toursError"></span>
					</div>
					<div class="modal-body">
						<table class="table">
							<thead>
								<tr>
									<th>Tour type</th>
									<th><?echo $semester." ".$currYear?></th>
									<th>Ever</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>Admissions Tours (<?echo $admReq?> required):</td>
									<td id="admCount"></td>
									<td id="totalAdmCount"></td>
								</tr>
								<tr>
									<td>Historical Tours (<?echo $hisReq?> required):</td>
									<td id="hisCount"></td>
									<td id="totalHisCount"></td>
								</tr>
								<tr>
									<td>Tours that can be either:</td>
									<td id="eitherCount"></td>
									<td id="totalEitherCount"></td>
								</tr>
								<tr>
									<td>Tours that didn't count as either:</td>
									<td id="neitherCount"></td>
									<td id="totalNeitherCount"></td>
								</tr>
							</tbody>
						</table>
						<div id="toursTable">
						</div>
						<p style="text-align:right">
							<button id="closeToursButton" type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						</p>
					</div>
				</div>
			</div>
		</div>

		<!--Collapsing Table CSS from: http://elvery.net/demo/responsive-tables/ -->
		<!--(768px is the Boostrap standard)-->
		<style>
		@media only screen and (max-width: 768px) {
			table.respTable td:nth-child(2),
			table.respTable th:nth-child(2),
			table.respTable td:nth-child(3),
			table.respTable th:nth-child(3),
			table.respTable td:nth-child(4),
			table.respTable th:nth-child(4) {display: none;}
		}
		</style>
			</div>
		</div>


	</body>

	<script>
		var order = 'Tours'; //default; this is what the page already is
		var order_query = 'numtours';
		var categorize = false;

		$(function() {
			refresh();

			$('#orderByBox').on('change', function() {
				refreshOrder($(this).val());
			});
		});

		function displayList(guideID) {
			$.post("functions/gettourlist.php",{
					guideID:guideID
				}, function(data) {
					data = $.parseJSON(data);
					$('#toursDialogName').html(data.guideFirstname+' '+data.guideLastname);
					$('#guideTours').html(data.totalToursThisSemester+' tour'+data.pluralizeTours);
					$('#toursError').html(data.toursError);
					$('#toursTable').html(data.toursTable);
					if (data.totalToursEver==1) {
						$('#totalToursEver').html(data.totalToursEver+" tour");
					} else {
						$('#totalToursEver').html(data.totalToursEver+" tours");
					}
					$('#totalAdmCount').html(data.totalAdmCount);
					$('#totalHisCount').html(data.totalHisCount);
					$('#totalEitherCount').html(data.totalEitherCount);
					$('#totalNeitherCount').html(data.totalNeitherCount);
					$('#admHisMsg').html(data.admHisMsg);
					$('#totalToursThisSemester').html(data.totalToursThisSemester);
					$('#uncreditedCount').html(data.uncreditedCount);
					$('#admCount').html(data.admCount);
					$('#hisCount').html(data.hisCount);
					$('#eitherCount').html(data.eitherCount);
					$('#neitherCount').html(data.neitherCount);
					if (data.numSemesters==1) {
						$('#semestersInUGS').html(data.numSemesters+' semester');
					} else {
						$('#semestersInUGS').html(data.numSemesters+' semesters');
					}

					//Do this last so that all the new things that were just pasted in are activated:
					$('[data-toggle="tooltip"]').tooltip();
					//Then show the modal:
					$('#toursModal').modal('show');
				});
		}

		function refresh() {
			$('#directoryBody').html('<td colspan=7 style="text-align:center"><h3>Loading...</h3></td>');
			$.get("functions/printtourcountlist.php",{
					order:order_query,
					cat:categorize
				}, function(data) {
					$('#directoryBody').html(data);
				});
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
					case 'Points':
						order_query="numpoints";
						categorize = false;
						break;
					case 'Tours':
						order_query="numtours";
						categorize = false;
						break;
					default:
						order = 'Points';
						order_query="numpoints";
						categorize = false;
						break;
				}

				refresh();
			}
		}
	</script>
</html>
