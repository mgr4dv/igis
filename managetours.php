<? require_once("authenticate.php"); 
$permission_level = 2;
include("permission.php");
?>
<?
include("./functions/link.php");
//get info for the tour editing stuff:
$tourTypes=mysqli_query($link,"select * from tours_types WHERE offered='yes' ORDER by name ASC");
$tourTypeOptions = "";
while ($type=mysqli_fetch_array($tourTypes)) {
	$tourTypeOptions = $tourTypeOptions."\n<option value=\"".$type['type_id']."\">";
	$tourTypeOptions = $tourTypeOptions.$type['abbrev']." - ".$type['name'];
	$tourTypeOptions = $tourTypeOptions."</option>";
}
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
		<!--typeahead; the auto-complete textbox for picking a guide-->
		<script type="text/javascript" src="js/typeahead.bundle.js"></script>
	</head>

<!-- Body of webpage (not reused, but reusable elements inside) -->
	<body style="padding-top:40px; padding-bottom: 60px; overflow:visible">

		<!-- Navigation bar across the top and footer across the bottom -->
		<?include_once("includes/nav.php")?>
		<?include_once("includes/footer.php")?>

		<?
		$selectedTimestamp = time(); //<-- this is how weeks will be able to change
		//Get the timestamp of the last Sunday relative to the selected timestamp:
		$lastDay1 = strtotime("last sunday", $selectedTimestamp); //(Yes, believe it or not, this actually works. PHP is great.)
		//Convert the lastMonday timestamp into usable information:
		$currentWeekDay=date("j",$lastDay1)+1;   //"j" = day of month without leading zeros
		$currentWeekMonth=date("n",$lastDay1); //"n" = month number without leading zeros
		$currentWeekYear=date("Y",$lastDay1);  //"Y" = full four-digit year
		//Note: if the date exceeds the number of days in a given month, it'll just roll over into the next, as it should, so adding/subtracting days is perfectly fine.
		?>


		<div class="container">
			<h1>Manage Tours</h1>
			<div id="successAlert" class="alert alert-success alert-dismissible" role="alert" hidden>
				<button type="button" class="close" onclick="$('#successAlert').hide()"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<span id="alertMsg"></span>
			</div>
			<div class="row">
				<div class="col-md-4"></div>
				<div class="col-md-4">
					<div class="input-group" style="margin-bottom:5px">
						<span class="input-group-btn">
							<button id="backButton" class="btn btn-default" type="button"><span class = "glyphicon glyphicon-arrow-left"></span></button>
							<button id="calendarButton" class="btn btn-default" type="button" onclick="$('#dateBox').datepicker('show')"><span class = "glyphicon glyphicon-calendar"></span></button>
						</span>
						<input type="text" class="form-control" id="dateBox" data-provide="datepicker" style="text-align:center" disabled>
						<span class="input-group-btn">
							<button id="refreshButton" class="btn btn-default" type="button"><span class = "glyphicon glyphicon-refresh"></span></button>
							<button id="forwardButton" class="btn btn-default" type="button"><span class = "glyphicon glyphicon-arrow-right"></span></button>
						</span>
					</div>
				</div>
				<div class="col-md-4">
					<button style="float:right; margin-right:10px; margin-bottom:5px" id="regularToursButton" class="btn btn-primary" type="button" data-toggle="modal" data-target="#regTourModal"><span class="glyphicon glyphicon-retweet"></span> Add Regular Tours</button>
					<button style="float:right; margin-right:10px; margin-bottom:5px" id="newTourButton" class="btn btn-primary" type="button" data-toggle="modal" data-target="#newTourModal"><span class="glyphicon glyphicon-plus"></span> New Tour</button>
				</div>
			</div>
			<div class="well" id="tourDays" style="background-color:#BB9999"> <!--reddish color to differentiate at a glance from the regular signups page-->
				<div class="row">
					<div class="col-md-3" id="day1Tours"> <!--Monday--> </div>
					<div class="col-md-3" id="day2Tours"> <!--Tuesday--> </div>
					<div class="col-md-3" id="day3Tours"> <!--Wednesday--> </div>
				</div>
				<div class="row">
					<div class="col-md-3" id="day4Tours"> <!--Thursday--> </div>
					<div class="col-md-3" id="day5Tours"> <!--Friday--> </div>
					<div class="col-md-3" id="day6Tours"> <!--Saturday--> </div>
					<div class="col-md-3" id="day7Tours"> <!--Sunday--> </div>
				</div>
			</div>
		</div>



		<div class="modal" id="newTourModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header" style="text-align:center">
						<h3 class="modal-title"><strong>New Tour</strong></h3>
					</div>
					<div class="modal-body">
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
									<input type="text" id="newTourNumGuides" class="form-control spinedit" />
								</td>
							</tr>
							<tr>
								<td style="text-align:right; vertical-align:top"><strong>Notes:</strong></td>
								<td><textarea id="newTourNotes" class="form-control" rows="3" style="resize: none" placeholder="(optional)"></textarea></td>
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
								<td style="text-align:right; vertical-align:middle"><strong>Date:</strong></td>
								<td>
									<div class="input-group">
										<input type="text" class="form-control" id="editTourDateBox" data-provide="datepicker" style="text-align:center; background-color:#FFFFFF" disabled>
										<span class="input-group-btn">
											<button id="editTourCalendarButton" class="btn btn-default" type="button" onclick="$('#editTourDateBox').datepicker('show')"><span class = "glyphicon glyphicon-calendar"></span></button>
										</span>
									</div>
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
							<tr>
								<td style="text-align:right; vertical-align:top"><strong>Notes:</strong></td>
								<td><textarea id="editTourNotes" class="form-control" rows="3" style="resize: none" placeholder="(optional)"></textarea></td>
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


		<div class="modal" id="regTourModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header" style="text-align:center">
						<h3 class="modal-title" style="display:inline"><strong>Add Regular Tours</strong></h3>
					</div>
					<div class="modal-body" style="text-align: center;">
						<h4>Select dates on which to add the new tours:</h4>
						<div id="regTourDateBox" class="date" style="display: inline-block;"></div>
						<p style="text-align:right">
							<button id="regTourRedirectButton" type="button" class="btn btn-default" style="float:left" onclick="window.location.assign('<?echo $defineregulartours_url?>')">Change Regular Tours</button>
							<button id="regTourCancelButton" type="button" class="btn btn-default" data-dismiss="modal" onclick="clearRegTour();">Cancel</button>
							<button id="regTourSubmitButton" type="button" class="btn btn-primary" data-dismiss="modal">Add Tours</button>
						</p>
					</div>
				</div>
			</div>
		</div>


		<div class="modal" id="replaceGuideModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header" style="text-align:center">
						<h3 class="modal-title" style="display:inline"><strong>Replace <span id="guideToReplaceBox">[name]</span></strong><br> <small>on <span id="dateToReplaceBox">[date]</span> at <span id="timeToReplaceBox">[time]</span>.</small></h3><h4 style="display:inline; float:right"><small><em><span id="replaceGuideTourID">[tour_id]</span></em></small></h4>
					</div>
					<div class="modal-body" style="text-align: center;">
						<h4>Choose the replacement guide:</h4>
						<div id="replaceGuideBox">
							<input id="replaceGuideBoxInput" class="typeahead form-control" type="text" placeholder="Guide's name" autocomplete="on">
						</div>
						<p style="text-align:right">
							<button id="replaceGuideCancelButton" type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
							<button id="replaceGuideSubmitButton" type="button" class="btn btn-primary" data-dismiss="modal">Switch</button>
						</p>
					</div>
				</div>
			</div>
		</div>


		<div class="modal" id="signupGuideModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header" style="text-align:center">
						<h3 class="modal-title" style="display:inline"><strong>Sign up a Guide</strong><br> <small>on <span id="dateToSignupBox">[date]</span> at <span id="timeToSignupBox">[time]</span>.</small></h3><h4 style="display:inline; float:right"><small><em><span id="signupGuideTourID">[tour_id]</span></em></small></h4>
					</div>
					<div class="modal-body" style="text-align: center;">
						<h4>Choose the new guide:</h4>
						<div id="signupGuideBox">
							<input id="signupGuideBoxInput" class="typeahead form-control" type="text" placeholder="Guide's name" autocomplete="on">
						</div>
						<p style="text-align:right">
							<button id="signupGuideCancelButton" type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
							<button id="signupGuideSubmitButton" type="button" class="btn btn-primary" data-dismiss="modal">Signup</button>
						</p>
					</div>
				</div>
			</div>
		</div>


	</body>

	<script>
		//Set up global variables:
		var offsetWeeks = 0; // How many weeks before/after the current week it is
		var newWeekDay;
		var originalDay1 = new Date(<? echo $currentWeekYear ?>, <? echo $currentWeekMonth ?>-1,<? echo $currentWeekDay ?>);



		$(function () { //When the document is ready do the following setup steps (WARNING: only executed once; manually call the reloadEvents() function below for repeat)...

			//Initialize the datepickers:
			$('#dateBox').datepicker({
				format: 'MM d, yyyy',
				autoclose: true,
				todayBtn: 'linked',
				todayHighlight: true
			});
			$('#newTourDateBox').datepicker({
				format: 'MM d, yyyy',
				autoclose: true,
				todayBtn: 'linked',
				todayHighlight: true
			});
			$('#editTourDateBox').datepicker({
				format: 'MM d, yyyy',
				autoclose: true,
				todayBtn: 'linked',
				todayHighlight: true
			});
			$('#regTourDateBox').datepicker({
				format: 'MM d, yyyy',
				multidate: true,
				todayBtn: 'linked',
				todayHighlight: true,
				clearBtn: true,
				startDate: new Date()
			});
			$('#newTourTimeDiv').datetimepicker({
				pickDate: false
			});
			$('#editTourTimeDiv').datetimepicker({
				pickDate: false
			});

			//Set up the datepicker onChangeDate event:
			$('#dateBox').datepicker()
				.on('changeDate', function(e){
					//When a new date is selected, do the following:
					offsetWeeks = getNewOffset(e.date);
					refreshDays();
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

			//This code taken directly from the Typeahead website:
			var substringMatcher = function(strs) {
			  return function findMatches(q, cb) {
				var matches, substrRegex;

				// an array that will be populated with substring matches
				matches = [];

				// regex used to determine if a string contains the substring `q`
				substrRegex = new RegExp(q, 'i');

				// iterate through the pool of strings and for any string that
				// contains the substring `q`, add it to the `matches` array
				$.each(strs, function(i, str) {
				  if (substrRegex.test(str)) {
					// the typeahead jQuery plugin expects suggestions to a
					// JavaScript object, refer to typeahead docs for more info
					matches.push({ value: str });
				  }
				});

				cb(matches);
			  };
			};

			var guideList = <?echo $guides?>;
			var guideIDList = <?echo $guideIDs?>;

			$('#replaceGuideBox .typeahead').typeahead({
			  hint: true,
			  highlight: true,
			  minLength: 2
			},
			{
			  name: 'guides',
			  displayKey: 'value',
			  source: substringMatcher(guideList)
			});

			$('#signupGuideBox .typeahead').typeahead({
			  hint: true,
			  highlight: true,
			  minLength: 2
			},
			{
			  name: 'guides',
			  displayKey: 'value',
			  source: substringMatcher(guideList)
			});


			//Set up the buttons' onClick events:
			$('#backButton').click(function() {
				goBackOneWeek();
			});
			$('#forwardButton').click(function() {
				goForwardOneWeek();
			});
			$('#refreshButton').click(function() {
				refreshDays();
			});
			$('#newTourSubmitButton').click(function() {
				var date = $('#newTourDateBox').val();
				var time = $('#newTourTimeBox').val();
				var type = $('#newTourTypeBox').val();
				var numSlots = $('#newTourNumGuides').val();
				var notes = $('#newTourNotes').val();

				newTour(date,time,type,numSlots,notes);
				clearNewTour();
			});
			$('#editTourSubmitButton').click(function() {
				var tourID = $('#editTourID').html();
				var date = $('#editTourDateBox').val();
				var time = $('#editTourTimeBox').val();
				var type = $('#editTourTypeBox').val();
				var numSlots = $('#editTourNumGuides').val();
				var notes = $('#editTourNotes').val();

				adjustTour(tourID,date,time,type,numSlots,notes)
				clearEditTour();
			});
			$('#regTourSubmitButton').click(function() {
				var datesRaw = $('#regTourDateBox').datepicker('getDates');
				var dates = datesRaw;
				for(i=0; i<datesRaw.length; i++) {
					dates[i] = datesRaw[i].toDateString();
				}
				regTours(dates);
				clearRegTour();
			});
			$('#replaceGuideSubmitButton').click(function() {
				//var newGuideName = $('#replaceGuideBox').typeahead('val') //<-- this "get" should work, but there's a bug in Typeahead right now and it doesn't.
				var newGuideName = $('#replaceGuideBoxInput').val();
				$('#replaceGuideBoxInput').val('');
				var newGuideIndex = guideList.indexOf(newGuideName);
				var newGuideID = guideIDList[newGuideIndex];
				if (newGuideIndex==-1) {
					alert('Error: guide "'+newGuideName+'" not found.');
				} else {
					//alert('Adding guide #'+newGuideID+'.')
					tourID = $('#replaceGuideTourID').html();
					oldGuideName = $('#guideToReplaceBox').html();
					oldGuideIndex = guideList.indexOf(oldGuideName);
					oldGuideID = guideIDList[oldGuideIndex];
					switchGuide(newGuideID,tourID,oldGuideID);
				}
			});
			$('#replaceGuideCancelButton').click(function() {
				$('#replaceGuideBoxInput').val('');
			});
			$("#replaceGuideBoxInput").keypress(function (e) {
				if ((e.which && e.which == 13) || (e.keyCode && e.keyCode == 13)) {
					$('#replaceGuideSubmitButton').click();
				}
			});
			$('#signupGuideSubmitButton').click(function() {
				//var newGuideName = $('#signupGuideBox').typeahead('val') //<-- this "get" should work, but there's a bug in Typeahead right now and it doesn't.
				var newGuideName = $('#signupGuideBoxInput').val();
				$('#signupGuideBoxInput').val('');
				var newGuideIndex = guideList.indexOf(newGuideName);
				var newGuideID = guideIDList[newGuideIndex];
				//alert('Submitting '+newGuideName+' ('+newGuideID+')!');
				if (newGuideIndex==-1) {
					alert('Error: guide "'+newGuideName+'" not found.');
				} else {
					//alert('Adding guide #'+newGuideID+'.')
					tourID = $('#signupGuideTourID').html();
					signup(newGuideID,tourID);
				}
			});
			$('#signupGuideCancelButton').click(function() {
				$('#signupGuideBoxInput').val('');
			});
			$("#signupGuideBoxInput").keypress(function (e) {
				if ((e.which && e.which == 13) || (e.keyCode && e.keyCode == 13)) {
					$('#signupGuideSubmitButton').click();
				}
			});

			//define the indexOf function if it needs to be done (on IE v8 or earlier, and some mobile browsers)
			if (!Array.prototype.indexOf) {
				alert("This browser doesn't seem to support the indexOf function by default. Attempting to shiv it in... (Guide-searching functionality may not work right. Contact the Tech Chair.)");
				Array.prototype.indexOf = function(obj, start) {
					 for (var i = (start || 0), j = this.length; i < j; i++) {
						 if (this[i] === obj) { return i; }
					 }
					 return -1;
				}
			}


			//Load the initial set of tours:
			refreshDays();
		});

		function goBackOneWeek() {
			offsetWeeks--;
			$('#dateBox').val("");
			refreshDays();
		}
		function goForwardOneWeek() {
			offsetWeeks++;
			$('#dateBox').val("");
			refreshDays();
		}

		function refreshDays() {
			newWeekDay = <? echo $currentWeekDay ?> + (offsetWeeks*7); //"current" week start, +/- # of offset weeks
			$('#tourDays').html('<h3 style="text-align:center">Loading...</h3>');
			$.get("functions/printdaysedit.php",{
					guide:<? echo $_SESSION['id'] ?>,
					y:<? echo $currentWeekYear ?>,
					m:<? echo $currentWeekMonth ?>,
					d:newWeekDay
				}, function(data) {
					$('#tourDays').html(data);
					refreshWeekLabel();
					reloadEvents();
				});
		}

		function refreshWeekLabel() {
			//Refresh the gray subtitle of the Tour Signups header
			if (offsetWeeks==0) {
				$('#weekHeader').html("for this week");
			} else if (offsetWeeks==1) {
				$('#weekHeader').html("for next week");
			} else if (offsetWeeks==-1) {
				$('#weekHeader').html("for last week");
			} else {
				$('#weekHeader').html("for the week of " + getDateString(1) + " to " + getDateString(7) + "");
			}
		}

		function reloadEvents() {
			//Initialize the popovers:
			//(this needs to be done every time the HTML refreshes via refreshDays() because it only initializes the elements currently present)
			//(a RIDICULOUS amount of time and effort was spent discovering that so you darn well better appreciate it)
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

		}

		function getDateString(dayNum) {
			//Get a string representation of any day this week (day 1-7); used when generating the header in refreshWeekLabel
			var d = new Date(originalDay1.getFullYear(), originalDay1.getMonth(), originalDay1.getDate() + (offsetWeeks*7) + (dayNum-1));
			return (d.getMonth()+1) + "/" + d.getDate() + "/" + (d.getFullYear()-2000); //note that the 2-digit year format is obtained by subtracting 2000; if this code persists past the year 2099, that won't work.
		}

		function getNewOffset(date) {
			//Get the number of weeks in either direction that a certain date is.
			var diff = date.getTime() - originalDay1.getTime(); //difference, in milliseconds, between the given date and the original start of this week
			var diff = diff/1000/60/60/24; //converted to seconds, minutes, hours, and finally days
			var newOffset = Math.floor(diff/7); //if -1.5, start on week -2. If +1.5, start on week +1
			return newOffset;
		}

		function signup(guideID, tourID) {
			$('#tourDays').html('<h3 style="text-align:center">Loading...</h3>');
			//Sign up the given guide for the given tour.
			$.post("functions/signupguide.php",{
					guideID:guideID,
					tourID:tourID,
				}, function(data) {
					//alert(data) //For debugging.
					refreshDays(); //Then, refresh the tours
				});

		}

		function drop(guideID,tourID) {
			$('#tourDays').html('<h3 style="text-align:center">Loading...</h3>');
			//Drop the given guide from the given tour.
			$.post("functions/dropguide.php",{
					guideID:guideID,
					tourID:tourID,
				}, function(data) {
					refreshDays(); //Then, refresh the tours
				});
		}

		function requestCover(guideID,tourID) {
			$('#tourDays').html('<h3 style="text-align:center">Loading...</h3>');
			//alert('Requesting Cover!'); //For debugging.
			$.post("functions/changecoverrequest.php",{
					guideID:guideID,
					tourID:tourID,
					coverRequest:1
				}, function(data) {
					//alert(data); //Show popup with info from the server. For debugging.
					refreshDays(); //Then, refresh the tours
				});
		}

		function undoRequestCover(guideID,tourID) {
			$('#tourDays').html('<h3 style="text-align:center">Loading...</h3>');
			//alert('Reclaiming tour!'); //For debugging.
			$.post("functions/changecoverrequest.php",{
					guideID:guideID,
					tourID:tourID,
					coverRequest:0
				}, function(data) {
					//alert(data); //Show popup with info from the server. For debugging.
					refreshDays(); //Then, refresh the tours
				});
		}

		function switchGuide(guideID,tourID,oldGuideID) {
			$('#tourDays').html('<h3 style="text-align:center">Loading...</h3>');
			//alert('You (guide #'+guideID+') are covering tour #'+tourID+' for guide #'+oldGuideID+'!'); //For debugging.
			$.post("functions/switchguide.php",{
					oldGuideID:oldGuideID,
					guideID:guideID,
					tourID:tourID
				}, function(data) {
					//alert(data); //Show popup with info from the server. For debugging.
					refreshDays(); //Then, refresh the tours
				});
		}

		function creditGuide(guideID,tourID) {
			$('#tourDays').html('<h3 style="text-align:center">Loading...</h3>');
			//alert('Crediting guide #'+guideID+' for tour #'+tourID);
			$.post("functions/creditguide.php",{
					guideID:guideID,
					tourID:tourID,
					status:'credited'
				}, function(data) {
					if (data!='') {
						alert(data); //Show any errors that occur.
					}
					refreshDays(); //Then, refresh the tours
				});
		}

		function nocreditGuide(guideID,tourID) {
			$.ajax({type:'POST',
					url:"functions/creditguide.php",
					data: {
						guideID:guideID,
						tourID:tourID,
						status:'nocredit'
					},
					success: function(data) {
								if (data!='') {
									alert(data); //Show any errors that occur.
								}
								refreshDays(); //Then, refresh the tours
							},
					async: false
					});
		}

		function markMissed(guideID,tourID) {
			$('#tourDays').html('<h3 style="text-align:center">Loading...</h3>');
			//alert('Marking guide #'+guideID+' as having missed tour #'+tourID);
			$.post("functions/creditguide.php",{
					guideID:guideID,
					tourID:tourID,
					status:'missed'
				}, function(data) {
					if (data!='') {
						alert(data); //Show any errors that occur.
					}
					refreshDays(); //Then, refresh the tours
				});
		}

		function adjustTour(tourID,date,time,type,numSlots,notes) {
			$('#tourDays').html('<h3 style="text-align:center">Loading...</h3>');
			$.post("functions/changetour.php",{
					newTour:0,
					deleteTour:0,
					tourID:tourID,
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
						refreshDays(); //Then, refresh the tours
						$('#alertMsg').html('<b>Successfully modified the tour which is now the '+data.timeStr+' '+data.abbrev+' on '+data.dateStr+'.</b>');
						$('#successAlert').show();
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
						refreshDays(); //Then, refresh the tours
						$('#alertMsg').html('<b>Successfully added the '+data.timeStr+' '+data.abbrev+' on '+data.dateStr+'.</b>');
						$('#successAlert').show();
					}
				});
		}

		function deleteTour(tourID) {
			$('#tourDays').html('<h3 style="text-align:center">Loading...</h3>');
			$.post("functions/changetour.php",{
					newTour:0,
					deleteTour:1,
					tourID:tourID,
					date:"",
					time:"",
					type:0,
					numSlots:0,
					notes:""
				}, function(data) {
					data = JSON.parse(data);
					if (data.error!='') {
						alert(data.error); //Show any errors that occur.
					} else {
						refreshDays(); //Then, refresh the tours
						$('#alertMsg').html('<b>Successfully deleted the '+data.timeStr+' '+data.abbrev+' on '+data.dateStr+'.</b>');
						$('#successAlert').show();
					}
				});
		}

		function regTours(dates) {
			$('#tourDays').html('<h3 style="text-align:center">Loading...</h3>');
			$.post("functions/addregulartours.php",{
					dates:dates
				}, function(data) {
					if (data!='') {
						alert(data); //Show any errors that occur.
					}
					refreshDays(); //Then, refresh the tours
				});
		}

		function editTourPopup(tourID) {
			$.post("functions/gettourinfo.php",{
					tourID:tourID,
					isReg:0
				}, function(data) {
					data = $.parseJSON(data)
					var date = data.date;
					var time = data.time;
					var type = data.type;
					var numSlots = data.guides_needed;
					var notes = data.notes;
					$('#editTourID').html(tourID)
					$('#editTourDateBox').val(date);
					$('#editTourTimeBox').val(time);
					$('#editTourTypeBox').val(type);
					$('#editTourNumGuides').val(numSlots);
					$('#editTourNotes').val(notes);

					$('#editTourModal').modal('show');
					//alert(data); //Show popup with info from the server. For debugging.
				});

		}

		function switchGuidePopup(oldGuideID,tourID) {
			$.post("functions/getguideinfo.php",{
					guideID:oldGuideID,
				}, function(data) {
					//alert(data); //for debugging
					var guide;
					guide = $.parseJSON(data);
					$('#guideToReplaceBox').html(guide['firstname']+" "+guide['lastname']);
				});
			$.post("functions/gettourinfo.php",{
					tourID:tourID,
				}, function(data) {
					//alert(data); //for debugging
					var tour;
					tour = $.parseJSON(data);
					$('#dateToReplaceBox').html(tour['date']);
					$('#timeToReplaceBox').html(tour['time']);
					$('#replaceGuideTourID').html(tour['tour_id']);
				});
			$('#replaceGuideModal').modal('show');
			$('#replaceGuideBoxInput').focus();
			//switchGuide(guideID,tourID,oldGuideID);
		}

		function signupGuidePopup(tourID) {
			$.post("functions/gettourinfo.php",{
					tourID:tourID,
				}, function(data) {
					//alert(data); //for debugging
					var tour;
					tour = $.parseJSON(data);
					$('#dateToSignupBox').html(tour['date']);
					$('#timeToSignupBox').html(tour['time']);
					$('#signupGuideTourID').html(tour['tour_id']);
				});
			$('#signupGuideModal').modal('show');
			$('#signupGuideBoxInput').focus();
			//signup(guideID,tourID);
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

		function clearRegTour() {
			$('#regTourDateBox').datepicker('setDates');
		}

	</script>
</html>
