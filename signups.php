<? date_default_timezone_set('America/New_York');
require_once("authenticate.php");


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
			<h1>Tour Signups <small><em><span id="weekHeader"></span></em></small></h1>
			<div class="row">
				<div class="col-md-4"></div>
				<div class="col-md-4">
					<div class="input-group">
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
					<button id="tourAbbrevButton" class="btn btn-sm btn-default" style="float:right" data-toggle="modal" data-target="#tourAbbrevModal">Tour Types</button>
				</div>
			</div>
			<div class="well" id="tourDays">
				<div class="row">
					<div class="col-md-3" style="width:14.28%" id="day1Tours"> <!--Monday--> </div>
					<div class="col-md-3" style="width:14.28%" id="day2Tours"> <!--Tuesday--> </div>
					<div class="col-md-3" style="width:14.28%" id="day3Tours"> <!--Wednesday--> </div>
					<div class="col-md-3" style="width:14.28%" id="day4Tours"> <!--Thursday--> </div>
					<div class="col-md-3" style="width:14.28%" id="day5Tours"> <!--Friday--> </div>
					<div class="col-md-3" style="width:14.28%" id="day6Tours"> <!--Saturday--> </div>
					<div class="col-md-3" style="width:14.28%" id="day7Tours"> <!--Sunday--> </div>
				</div>
			</div>
		</div>




		<div class="modal" id="tourAbbrevModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
						<h4 class="modal-title" id="myModalLabel">Tour Abbreviations</h4>
					</div>
					<div id="tourTypes" class="modal-body">

					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>

		<div class="modal" id="emailGuidesModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
						<h4 class="modal-title" id="myModalLabel">Email the guides signed up for the <span id="emailTourDesc"></span></h4>
					</div>
					<div id="tourTypes" class="modal-body">
						<input id="emailFrom" hidden></input><input id="emailTo" hidden></input>
						<p><input id="emailSubject" class="form-control"></input></p>
						<p><textarea id="emailMessage" class="form-control" rows="5" style="resize: none" placeholder=""></textarea></p>
						<p>- <?echo $_SESSION['name']?></p>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-primary" onclick="sendEmail()">Send</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
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
		var popoverTrigger;

		//set defaults:
		var defaultEmailSubject = "";
		var defaultEmailMessage = "";



		$(function () { //When the document is ready do the following setup steps (WARNING: only executed once; manually call the reloadEvents() function below for repeat)...
			//Initialize the datepicker:
			$('#dateBox').datepicker({
				format: 'MM d, yyyy',
				autoclose: true,
				todayBtn: 'linked',
				todayHighlight: true
			})

			//Set up the datepicker onChangeDate event:
			$('#dateBox').datepicker()
				.on('changeDate', function(e){
					//When a new date is selected, do the following:
					offsetWeeks = getNewOffset(e.date);
					refreshDays();
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

			$.post("functions/printtourtypes.php",{
					noEdit:1
				}, function(data) {
					//alert(data); //for debugging
					$('#tourTypes').html(data);
				});


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
			$.get("functions/printdays.php",{
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
			//alert('reloadEvents() reached') //for debugging
			$('[data-toggle="popover"]').popover({
				trigger: 'focus',
				delay: { show: 0, hide: 200 } //hide delay is so that the popover doesn't disappear right away when clicking a button inside it
			});

			//Whenever a button is clicked, assign it focus if it doesn't already have it:
			//(this is necessary because some browsers, like Firefox/Safari on Mac OS X, don't automatically assign focus to a clicked button)
			$('button').click( function(e) {
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
			//Sign up the given guide for the given tour; this is called by a button which is generated in a popover generated by printdays.php.
			$.post("functions/signupguide.php",{
					guideID:guideID,
					tourID:tourID,
				}, function(data) {
					refreshDays(); //Then, refresh the tours
				});

		}

		function drop(guideID,tourID) {
			$('#tourDays').html('<h3 style="text-align:center">Loading...</h3>');
			//Drop the given guide from the given tour; this is called by a button which is generated in a popover generated by printdays.php.
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

		function emailGuides(toIDs, fromID, tourDesc) {
			if (toIDs!='' && toIDs!=0 && fromID!='' && fromID!=0) {

				//clear subject and message:
				var subject = $('#emailSubject').val('');
				var message = $('#emailMessage').val('');

				//insert to and from to the hidden fields:
				$('#emailFrom').val(fromID);
				$('#emailTo').val(toIDs);

				//alert("FROM: "+fromID+"\nTO: "+toIDs+"\n\n(this feature is not enabled yet)"); //for debugging

				//fill in tour description in header:
				$('#emailTourDesc').html(tourDesc);

				//update global defaults:
				defaultEmailSubject = "Reminder: You are signed up for the "+tourDesc;
				defaultEmailMessage = "This is an automated message from IGIS, requested by <?echo $_SESSION['name']?>. Be sure to go to the "+tourDesc+" or, if you're not needed there, check IGIS to see if you can cover a different tour in need of help.\n\nThanks!";

				//fill in the default subject/message as placeholder:
				$('#emailSubject').attr("placeholder",defaultEmailSubject);
				$('#emailMessage').attr("placeholder",defaultEmailMessage);

				$('#emailGuidesModal').modal('show');
			}
		}

		function sendEmail() {
			var tourDesc = $('#emailTourDesc').html();
			var subject = $('#emailSubject').val();
			if (subject=='') subject=defaultEmailSubject;
			var message = $('#emailMessage').val();
			if (message==''){
				message=defaultEmailMessage;
			} else {
				//append signature:
				message = message+"\n\n-<?echo $_SESSION['name']?>";
			}
			var fromID = $('#emailFrom').val();
			var toIDs = $('#emailTo').val();
			if (toIDs!='' && toIDs!=0 && fromID!='' && fromID!=0) {
				$.post("functions/emailguides.php",{
					to_ids:toIDs,
					from_id:fromID,
					subject:subject,
					message:message,
					tour_desc:tourDesc
				}, function(data) {
					data = $.parseJSON(data);
					//alert(data.debug); //Show popup with info from the server. For debugging.
					if (data.error!=''){
						alert(data.error); //show any errors that occurred
					} else {
						$('#emailGuidesModal').modal('hide');
					}

				});
			}
		}

	</script>

</html>
