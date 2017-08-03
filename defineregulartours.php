<? require_once("authenticate.php"); ?>

<?
//get info for the tour editing stuff:
include("functions/link.php");
$tourTypes=mysqli_query($link,"select * from tours_types WHERE offered='yes' ORDER by name ASC");
$tourTypeOptions = "";
while ($type=mysqli_fetch_array($tourTypes)) {
	$tourTypeOptions = $tourTypeOptions."\n<option value=\"".$type['type_id']."\">";
	$tourTypeOptions = $tourTypeOptions.$type['abbrev']." - ".$type['name'];
	$tourTypeOptions = $tourTypeOptions."</option>";
}

?>

<!DOCTYPE html>

<html lang="en">

<!-- Header information for webpage (reused except for title) -->
	<head>
		<?
		include_once("includes/head.php");
		?>
		<style>
			<!--Style for typeahead autocomplete:-->
			.tt-query,
			.tt-hint {
				width: 396px;
				height: 30px;
				padding: 8px 12px;
				font-size: 10.5pt;
				line-height: 30px;
				border: 2px solid #ccc;
				border-radius: 8px;
				outline: none;
			}

			.tt-query {
				box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
			}

			.tt-hint {
				color: #999
			}

			.tt-dropdown-menu {
				text-align: left;
				margin-top: 12px;
				padding: 8px 0;
				background-color: #fff;
				border: 1px solid #ccc;
				border: 1px solid rgba(0, 0, 0, 0.2);
				border-radius: 8px;
				box-shadow: 0 5px 10px rgba(0,0,0,.2);
				cursor: pointer
			}

			.tt-suggestion {
				padding: 3px 20px;
				font-size: 10.5pt;
				line-height: 24px;
			}

			.tt-suggestion.tt-cursor {
				color: #fff;
				background-color: #0097cf;
			}

			.tt-suggestion p {
				margin: 0;
			}
		</style>
		<!--datetimepicker; for the time picker in the new/edit tour modal-->
		<link type="text/css" href="css/bootstrap-datetimepicker.css" rel="stylesheet">
		<script type="text/javascript" src="js/moment.js"></script>
		<script type="text/javascript" src="js/bootstrap-datetimepicker.js"></script>
		<!--spinedit; the number picker in the new/edit tour modal-->
		<link type="text/css" href="css/bootstrap-spinedit.css" rel="stylesheet">
		<script type="text/javascript" src="js/bootstrap-spinedit.js"></script>
		<!--typeahead; the autocompleting guide box in the new/edit tour modal-->
		<script type="text/javascript" src="js/typeahead.bundle.js"></script>
	</head>

<!-- Body of webpage (not reused, but reusable elements inside) -->
	<body style="padding-top:40px; padding-bottom: 60px">
		<?include_once("includes/nav.php")?>
		<?include_once("includes/footer.php")?>


		<div class="container">
			<h1>Define Regular Tours</h1>
			<div class="row">
				<div class="col-md-8"></div>
				<div class="col-md-4">
					<button style="float:right; margin-right:10px; margin-bottom:5px" id="newTourButton" class="btn btn-primary" type="button" data-toggle="modal" data-target="#newTourModal"><span class="glyphicon glyphicon-plus"></span> Add New Regular Tour</button>
				</div>
			</div>
			<div class="well" id="tourDays" style="background-color:#BB9999">
				<!--The tours fill in here by the refresh() Javascript function-->
			</div>
		</div>



		<div class="modal" id="newTourModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header" style="text-align:center">
						<h3 class="modal-title"><strong>New Regular Tour</strong></h3>
					</div>
					<div class="modal-body">
						<table class="table">
							<tr>
								<td style="text-align:right; vertical-align:middle"><strong>Day:</strong></td>
								<td>
									<select id="newTourDayBox" class="form-control" style="cursor:pointer">
										<option>Monday</option>
										<option>Tuesday</option>
										<option>Wednesday</option>
										<option>Thursday</option>
										<option>Friday</option>
										<option>Saturday</option>
										<option>Sunday</option>
									</select>
								</td>
							</tr>
							<tr>
								<td style="text-align:right; vertical-align:middle"><strong>Time:</strong></td>
								<td>
									<div id='newTourTimeDiv' class='input-group date'>
										<input id='newTourTimeBox' type='text' class="form-control" style="text-align:center;"/>
										<span class="input-group-addon" style="background-color:#FFFFFF"><span class="glyphicon glyphicon-time"></span></span>
									</div>
								</td>
							</tr>
							<tr>
								<td style="text-align:right; vertical-align:middle"><strong>Type:</strong></td>
								<td>
									<select id="newTourTypeBox" class="form-control" style="cursor:pointer">
										<?echo $tourTypeOptions?>
									</select>
								</td>
							</tr>
							<tr>
								<td style="text-align:right; vertical-align:middle"><strong>Guides:</strong></td>
								<td>
									<input type="text" id="newTourNumGuides" class="form-control spinedit" />
								</td>
							</tr>
						</table>
						<p style="text-align:right">
							<button id="newTourCancelButton" type="button" class="btn btn-default" data-dismiss="modal" onclick="clearNewTour();">Cancel</button>
							<button id="newTourSubmitButton" type="button" class="btn btn-primary" data-dismiss="modal">Create Tour</button>
						</p>
					</div>
				</div>
			</div>
		</div>

		<div class="modal" id="editTourModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header" style="text-align:center">
						<h3 class="modal-title" style="display:inline"><strong>Edit Tour</strong></h3><h4 style="display:inline; float:right"><small><em><span id="editTourID">[tour_id]</span></em></small></h4>
					</div>
					<div class="modal-body">
						<table class="table">
							<tr>
								<td style="text-align:right; vertical-align:middle"><strong>Day:</strong></td>
								<td>
									<select id="editTourDayBox" class="form-control" style="cursor:pointer">
										<option>Monday</option>
										<option>Tuesday</option>
										<option>Wednesday</option>
										<option>Thursday</option>
										<option>Friday</option>
										<option>Saturday</option>
										<option>Sunday</option>
									</select>
								</td>
							</tr>
							<tr>
								<td style="text-align:right; vertical-align:middle"><strong>Time:</strong></td>
								<td>
									<div id='editTourTimeDiv' class='input-group date'>
										<input id='editTourTimeBox' type='text' class="form-control" style="text-align:center;"/>
										<span class="input-group-addon" style="background-color:#FFFFFF"><span class="glyphicon glyphicon-time"></span></span>
									</div>
								</td>
							</tr>
							<tr>
								<td style="text-align:right; vertical-align:middle"><strong>Type:</strong></td>
								<td>
									<select id="editTourTypeBox" class="form-control" style="cursor:pointer">
										<?echo $tourTypeOptions?>
									</select>
								</td>
							</tr>
							<tr>
								<td style="text-align:right; vertical-align:middle"><strong>Guides:</strong></td>
								<td>
									<input type="text" id="editTourNumGuides" class="form-control spinedit" />
								</td>
							</tr>
						</table>
						<p style="text-align:right">
							<button id="editTourCancelButton" type="button" class="btn btn-default" data-dismiss="modal" onclick="clearEditTour();">Cancel</button>
							<button id="editTourSubmitButton" type="button" class="btn btn-primary" data-dismiss="modal">Update Tour</button>
						</p>
					</div>
				</div>
			</div>
		</div>


	</body>

	<script>
		$(function () {
			refresh();

			// $('#newTourButton').click( function() {
				// $('#newTourModal').modal('show');
			// });

			$('#newTourTimeDiv').datetimepicker({
				pickDate: false
			});
			$('#editTourTimeDiv').datetimepicker({
				pickDate: false
			});
			//Set up the number-of-guides box
			$('#newTourNumGuides').spinedit({
				maximum: 99, //This is arbitrary. I just assume you'll never need more than that, and I want a maximum so as to avoid burdening the database accidentally.
				minimum: 1,
				step: 1,
				value: 1,
				numberOfDecimals: 0
			});
			$('#editTourNumGuides').spinedit({
				maximum: 99, //This is arbitrary. I just assume you'll never need more than that, and I want a maximum so as to avoid burdening the database accidentally.
				minimum: 1,
				step: 1,
				value: 1,
				numberOfDecimals: 0
			});

			//Set up the buttons' events:
			$('#newTourSubmitButton').click(function() {
				var day = $('#newTourDayBox').val();
				var time = $('#newTourTimeBox').val();
				var type = $('#newTourTypeBox').val();
				var numSlots = $('#newTourNumGuides').val();
				//alert('Adding new tour; day='+day+', time='+time+', type='+type+', numSlots='+numSlots) //for debugging

				newTour(day,time,type,numSlots);
				clearNewTour();
			});
			$('#editTourSubmitButton').click(function() {
				var tourID = $('#editTourID').html();
				var day = $('#editTourDayBox').val();
				var time = $('#editTourTimeBox').val();
				var type = $('#editTourTypeBox').val();
				var numSlots = $('#editTourNumGuides').val();

				adjustTour(tourID,day,time,type,numSlots)
				clearEditTour();
			});
		});

		function editTourPopup(tourID) {
			$.post("functions/gettourinfo.php",{
					tourID:tourID,
					isReg:1
				}, function(data) {
					data = $.parseJSON(data)
					var day = data.day;
					var time = data.time;
					var type = data.tour_type;
					var numSlots = data.guides_needed;
					$('#editTourID').html(tourID)
					$('#editTourDayBox').val(day);
					$('#editTourTimeBox').val(time);
					$('#editTourTypeBox').val(type);
					$('#editTourNumGuides').val(numSlots);

					$('#editTourModal').modal('show');
					//alert(data); //Show popup with info from the server. For debugging.
				});
		}

		function refresh() {
			$('#tourDays').html('<h3 style="text-align:center">Loading...</h3>');
			$.get("functions/printdaysregulartours.php",{},
				function(data) {
					$('#tourDays').html(data); //print the tours
					//initialize the popovers:
					$('[data-toggle="popover"]').popover({
						trigger:'focus',
						delay: { show: 0, hide: 200 } //hide delay is so that the popover doesn't disappear right away when clicking a button inside it
					});

					//Whenever a button is clicked, assign it focus if it doesn't already have it:
					//(this is necessary because some browsers, like Firefox/Safari on Mac OS X, don't automatically assign focus to a clicked button)
					$('button').click( function() {
						if (!$(this).is(":focus")) {
							$(this).focus();
						}
					});
				});
		}

		function adjustTour(tourID,day,time,type,numSlots) {
			//have the server check to make sure the requested numSlots isn't less than the number signed up.
			//properly sanitize the notes
			//alert('Adjusting the tour. (day='+day+', time='+time+', type='+type+', numSlots='+numSlots+')')
			$.post("functions/changetour.php",{
					newTour:0,
					deleteTour:0,
					tourID:tourID,
					day:day,
					time:time,
					type:type,
					numSlots:numSlots,
					isReg:1
				}, function(data) {
					data = JSON.parse(data);
					if (data.error!='') {
						alert(data.error); //Show any errors that occur.
					}
					refresh(); //Then, refresh the tours
				});
		}

		function newTour(day,time,type,numSlots) {
			//properly sanitize the notes
			//alert('Adding the tour. (day='+day+', time='+time+', type='+type+', numSlots='+numSlots+')')
			$.post("functions/changetour.php",{
					newTour:1,
					deleteTour:0,
					day:day,
					time:time,
					type:type,
					numSlots:numSlots,
					isReg:1
				}, function(data) {
					//alert(data); //for debugging
					data = JSON.parse(data);
					if (data.error!='') {
						alert(data.error); //Show any errors that occur.
					}
					refresh(); //Then, refresh the tours
				});
		}

		function deleteTour(tourID) {
			$.post("functions/changetour.php",{
					newTour:0,
					deleteTour:1,
					tourID:tourID,
					isReg:1
				}, function(data) {
					data = JSON.parse(data);
					if (data.error!='') {
						alert(data.error); //Show any errors that occur.
					}
					refresh(); //Then, refresh the tours
				});
		}

		function clearNewTour() {
			$('#newTourDateBox').val('');
			$('#newTourTimeBox').val('');
			$('#newTourTypeBox').val(7); //this is the type_id for AR
			$('#newTourNumGuides').val(1);
			$('#newTourNotes').val('');
		}

		function clearEditTour() {
			$('#editTourDateBox').val('');
			$('#editTourTimeBox').val('');
			$('#editTourTypeBox').val(7); //this is the type_id for AR
			$('#editTourNumGuides').val(1);
			$('#editTourNotes').val('');
		}
	</script>
</html>
