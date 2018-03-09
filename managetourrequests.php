<?
date_default_timezone_set('America/New_York');
require_once("authenticate.php");
include("functions/link.php");

$permission_level = 2;
include("permission.php");

//get tour types for the new tour modal:
$tourTypes=mysqli_query($link,"select * from tours_types WHERE offered='yes' ORDER by name ASC");
$tourTypeOptions = "";
$tourTypeCodes = "[";
$tourTypeAbbrevs = "[";
while ($type=mysqli_fetch_array($tourTypes)) {
	$tourTypeOptions = $tourTypeOptions."\n<option value=\"".$type['type_id']."\">";
	$tourTypeOptions = $tourTypeOptions.$type['abbrev']." - ".$type['name'];
	$tourTypeOptions = $tourTypeOptions."</option>";
	$tourTypeCodes = $tourTypeCodes.$type['type_id'].",";
	$tourTypeAbbrevs = $tourTypeAbbrevs."'".$type['abbrev']."',";
}
$tourTypeCodes = substr($tourTypeCodes, 0, -1)."]"; //cut off the final comma and then close the bracket
$tourTypeAbbrevs = substr($tourTypeAbbrevs, 0, -1)."]"; //cut off the final comma and then close the bracket
?>
<!DOCTYPE html>

<html lang="en">

<!-- Header information for webpage (reused except for title) -->
	<head>
		<?
		include_once("includes/head.php");
		?>

		<style>
			.modal{overflow:hidden; z-index:1150;}
			.datepicker{z-index:1151 !important;} <!--this is so the calendar picker shows up above the modal (the popup tour editor)-->

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
	</head>

<!-- Body of webpage (not reused, but reusable elements inside) -->
	<body>
		<?include_once("includes/nav.php")?>
		<?include_once("includes/footer.php")?>


		<div class="container">
			<h1>Manage Tour Requests</h1>
			<div id="successAlert" class="alert alert-success alert-dismissible" role="alert" hidden>
				<button type="button" class="close" onclick="$('#successAlert').hide()"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<span id="alertMsg"></span>
			</div>

			<div class="well">
				<div class="row">
					<div class="col-md-4">
						<p><select id="displaySelectBox" class="form-control" style="cursor:pointer">
							<option value=0>Request List</option>
							<option value=1>Archived Requests</option>
						</select></p>
						<div class="list-group" id="requestListGroup">
							<p style="text-align:center; font-style:italic">Loading...</p>
						</div>
					</div>
					<div class="col-md-4">
						<div id="requestDetailsPane" hidden>
							<button type="button" id="markNewButton" class="btn btn-info" onclick="markAsNew(0,1)">
								Mark as New
							</button>
							<button type="button" id="markHandledButton" class="btn btn-success" onclick="markAsHandled(0,1)">
								Mark as Done
							</button>
							<button type="button" id="archiveRequestButton" class="btn btn-warning" onclick="markAsArchived(0,1)">
								Archive
							</button>
							<button type="button" id="unarchiveRequestButton" class="btn btn-warning" onclick="markAsUnarchived(0,1)" style="display:none">
								Un-Archive
							</button>
							<p style="text-align:center; font-size:10pt; font-style:italic">Submitted on <span id="dateSubmittedCell"></span> at <span id="timeSubmittedCell"></span> by <span id="requestedByCell"></span></p>
							<table class="table" id="requestInfoTable">
								<tr>
									<td style="width:100px">Type</td>
									<td id="typeCell" style="color:#0000FF; font-weight:bold"></td>
								</tr>
								<tr>
									<td>Date</td>
									<td id="dateCell" style="color:#0000FF; font-weight:bold"></td>
								</tr>
								<tr>
									<td>Time</td>
									<td id="timeCell" style="color:#0000FF; font-weight:bold"></td>
								</tr>
								<tr>
									<td>Requested Info</td>
									<td id="requestedInfoCell"></td>
								</tr>
								<tr>
									<td>Notes</td>
									<td id="notesCell"></td>
								</tr>
								<tr>
									<td>Tourists</td>
									<td id="numTouristsCell"></td>
								</tr>
								<tr>
									<td>Grade Level</td>
									<td id="gradeLevelCell"></td>
								</tr>
							</table>
							<p style="font-style:italic; text-decoration:underline">Contact info:</p>
							<table class="table" id="requesterInfoTable">
								<tr>
									<td style="width:100px">Name</td>
									<td id="nameCell"></td>
								</tr>
								<tr>
									<td>Organization</td>
									<td id="organizationCell"></td>
								</tr>
								<tr>
									<td>Email</td>
									<td id="emailCell"></td>
								</tr>
								<tr>
									<td>Phone</td>
									<td id="phoneCell"></td>
								</tr>
								<tr>
									<td>Day-of-Tour Phone</td>
									<td id="contactPhoneCell"></td>
								</tr>
							</table>
						</div>
					</div>
					<div class="col-md-4">
						<div id="addTourColumn" hidden>
							<p style="text-align:center; font-size:16pt">
								<span id="unhandledLabel" class="label label-danger">Not yet handled</span>
								<span id="handledLabel" class="label label-success" hidden>&#x2713; Handled</span>
							</p>
							<div class="panel panel-default">
								<div class="panel-heading">
									<h3 class="panel-title">Add Tour to IGIS:</h3>
								</div>
								<div class="panel-body">
									<table class="table">
										<tr>
											<td style="text-align:right; vertical-align:middle"><strong>Date:</strong></td>
											<td>
												<div class="input-group">
													<input type="text" class="form-control" id="newTourDateBox" data-provide="datepicker" style="text-align:center; background-color:#FFFFFF" disabled>
													<span class="input-group-btn">
														<button id="newTourCalendarButton" class="btn btn-default" type="button" onclick="$('#newTourDateBox').datepicker('show')"><span class = "glyphicon glyphicon-calendar"></span></button>
													</span>
												</div>
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
												<input type="text" id="newTourNumGuides" class="form-control spinedit"/>
											</td>
										</tr>
										<tr>
											<td style="text-align:right; vertical-align:top"><strong>Notes:</strong></td>
											<td><textarea id="newTourNotes" class="form-control" rows="5" style="resize: none;" placeholder="(optional)"></textarea></td>
										</tr>
									</table>
									<p style="text-align:center"><button id="newTourSubmitButton" type="button" class="btn btn-primary" data-dismiss="modal">Schedule Tour</button></p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>






	</body>

	<script>

		var currentlyViewing = 0;
		var currentlyArchived = 0;

		var tourTypeCodes = <?echo $tourTypeCodes?>;
		var tourTypeAbbrevs = <?echo $tourTypeAbbrevs?>;

		$(function () {
			//Assign the Archived/Unarchived Refresh event:
			$('#displaySelectBox').on('change', function() {
				$('#requestListGroup').html('<p style="text-align:center; font-style:italic">Loading...</p>');
				currentlyArchived = $(this).val();
				if ($(this).val()==1) {
					$('#archiveRequestButton').hide();
					$('#unarchiveRequestButton').show();
				} else {
					$('#archiveRequestButton').show();
					$('#unarchiveRequestButton').hide();
				}
				refreshRequestList(currentlyArchived);
			});

			//Set up time picker:
			$('#newTourTimeDiv').datetimepicker({
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

			//Set up new tour submit button:
			$('#newTourSubmitButton').click(function() {
				var date = $('#newTourDateBox').val();
				var time = $('#newTourTimeBox').val();
				var type = $('#newTourTypeBox').val();
				var numSlots = $('#newTourNumGuides').val();
				var notes = $('#newTourNotes').val();

				newTour(date,time,type,numSlots,notes);
				markAsHandled(0, 1);
			});


			refreshRequestList(0);
		});

		function refreshRequestList(selectedID) {
			//alert('Refreshing list!');
			$.post("functions/printtourrequestlist.php",{
					archived:currentlyArchived
					}, function(data) {
					data = $.parseJSON(data);
					$('#requestListGroup').html(data.requestList);
					if (data.error!='') {
						alert(data.error);
					}
					$('.list-group-item').removeClass('active');
					if ($('#requestListItem'+selectedID).length) { //check to make sure it's a valid item before trying to do anything for it
						$('#requestListItem'+selectedID).addClass('active');
					} else { //if you're trying to select an invalid item, just hide the details pane
						$('#requestDetailsPane').hide();
						$('#addTourColumn').hide();
					}
				});
		}

		function displayRequest(requestID) {
			$('#requestDetailsPane').hide();
			$('#addTourColumn').hide();
			currentlyViewing = requestID;
			clearNewTour();
			$.post("functions/gettourrequestinfo.php",{
						requestID:requestID
					}, function(data) {
						//alert(data); //for debugging
						data = $.parseJSON(data);
						//Update the tour request details table:
						$('#dateSubmittedCell').html(data.date_requested);
						$('#timeSubmittedCell').html(data.time_requested);
						$('#requestedByCell').html(data.requested_by);
						$('#typeCell').html(data.type);
						$('#dateCell').html(data.date);
						$('#timeCell').html(data.time);
						$('#requestedInfoCell').html(data.requested_info);
						$('#notesCell').html(data.notes);
						$('#numTouristsCell').html(data.num_tourists);
						$('#gradeLevelCell').html(data.grade_level);
						$('#nameCell').html(data.name);
						$('#organizationCell').html(data.organization);
						$('#emailCell').html('<a href="mailto:'+data.email+'">'+data.email+'</a>');
						$('#phoneCell').html('<a href="tel:'+data.phone+'">'+data.phone+'</a>');
						$('#contactPhoneCell').html('<a href="tel:'+data.contact_phone+'">'+data.contact_phone+'</a>');
						if (data.handled==1) {
							$('#handledLabel').show();
							$('#unhandledLabel').hide();
						} else {
							$('#handledLabel').hide();
							$('#unhandledLabel').show();
						}

						//Pre-load the create-tour info stuff:
						var tourTypeIndex = tourTypeAbbrevs.indexOf(data.type);
						var tourTypeCode = tourTypeCodes[tourTypeIndex];
						$('#newTourTypeBox').val(tourTypeCode);
						var notes = '';
						if (data.email!='') {
							notes = notes+"\n\n"
							notes = notes+"\nEmail: "+data.email;
						}
						if (data.contact_phone!='') {
							notes = notes+"\nPhone: "+data.contact_phone;
						} else if (data.phone!='') {
							notes = notes+"\nPhone: "+data.phone;
						}
						$('#newTourNotes').val(notes);
						$('#newTourTimeBox').val(data.time)
						$('#newTourDateBox').val(data.date)

						if (data.num_tourists < 25){
							$('#newTourNumGuides').val(1);
						} else {
							$('#newTourNumGuides').val(Math.floor(data.num_tourists/25) + 1);
						}

						$('#requestDetailsPane').show();
						$('#addTourColumn').show();
					});
			$('.list-group-item').removeClass('active');
			$('#requestListItem'+requestID).addClass('active');
			markAsOld(requestID,1);
		}

		function markAsNew(requestID, doRefresh) {
			if (requestID==0) {
				requestID=currentlyViewing;
				$('#handledLabel').hide();
				$('#unhandledLabel').show();
			}
			$.post("functions/updatetourrequestinfo.php",{
					requestID:requestID,
					markArchived:0,
					markNew:1,
					markHandled:0
				}, function(data) {
					data = $.parseJSON(data);
					if (data.error!='') {
						alert(data.error);
					}
					if (doRefresh) {
						refreshRequestList(requestID);
					}
				});
		}

		function markAsArchived(requestID, doRefresh) {
			if (requestID==0) requestID=currentlyViewing;
			$.post("functions/updatetourrequestinfo.php",{
					requestID:requestID,
					markArchived:1,
					markNew:0
				}, function(data) {
					data = $.parseJSON(data);
					if (data.error!='') {
						alert(data.error);
					}
					if (doRefresh) {
						refreshRequestList(requestID);
					}
				});
		}

		function markAsUnarchived(requestID, doRefresh) {
			if (requestID==0) requestID=currentlyViewing;
			$.post("functions/updatetourrequestinfo.php",{
					requestID:requestID,
					markArchived:0
				}, function(data) {
					data = $.parseJSON(data);
					if (data.error!='') {
						alert(data.error);
					}
					if (doRefresh) {
						refreshRequestList(requestID);
					}
				});
		}

		function markAsOld(requestID, doRefresh) {
			if (requestID==0) requestID=currentlyViewing;
			$.post("functions/updatetourrequestinfo.php",{
					requestID:requestID,
					markNew:0
				}, function(data) {
					data = $.parseJSON(data);
					if (data.error!='') {
						alert(data.error);
					}
					if (doRefresh) {
						refreshRequestList(requestID);
					}
				});
		}

		function markAsHandled(requestID, doRefresh) {
			if (requestID==0) {
				requestID=currentlyViewing;
				$('#handledLabel').show();
				$('#unhandledLabel').hide();
			}
			$.post("functions/updatetourrequestinfo.php",{
					requestID:requestID,
					markNew:0,
					markHandled:1
				}, function(data) {
					data = $.parseJSON(data);
					if (data.error!='') {
						alert(data.error);
					}
					if (doRefresh) {
						refreshRequestList(requestID);
					}
				});
		}



		function newTour(date,time,type,numSlots,notes) {
			$('#tourDays').html('<h3 style="text-align:center">Loading...</h3>');
			$.post("functions/changetour.php",{
					newTour:1,
					deleteTour:0,
					tourID:0,
					date:date,
					time:time,
					type:type,
					numSlots:numSlots,
					notes:notes
				}, function(data) {
					//alert(data); //for debugging
					data = JSON.parse(data);
					if (data.error!='') {
						alert(data.error); //Show any errors that occur.
					} else {
						$('#alertMsg').html('<b>Successfully added the '+data.timeStr+' '+data.abbrev+' on '+data.dateStr+'.</b>');
						$('#successAlert').show();
					}
				});
		}

		function clearNewTour() {
			$('#newTourDateBox').val('');
			$('#newTourTimeBox').val('');
			$('#newTourTypeBox').val(7); //this is the type_id for AR
			$('#newTourNumGuides').val(1);
			$('#newTourNotes').val('');
		}


	</script>
</html>
