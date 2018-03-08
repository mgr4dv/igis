<? require_once("authenticate.php");
date_default_timezone_set('America/New_York');

$permssion_level = 2;
include("permission.php");

include("functions/link.php");
$unhandled_tours = mysqli_query($link, "SELECT * FROM tours_scheduled
										RIGHT JOIN tours_info ON tours_scheduled.tour_id=tours_info.tour_id
										RIGHT JOIN tours_types ON tours_info.type=tours_types.type_id
										WHERE handled='no' AND date<=NOW()
										ORDER BY date DESC, time DESC, type ASC, tours_info.tour_id DESC");
$unhandled_error = mysqli_error($link);

$noUnhandledTours = false;
if (mysqli_num_rows($unhandled_tours)==0) {
	$unhandledList = "<tr><td colspan=2><h4><em>No unprocessed tours at this time.</em></h4></td></tr>";
	$noUnhandledTours = true; //used to determine whether to display the buttons
} else {
	$unhandledList="";
	$prevTourID = 0;
	$prevDate = "";
	while ($unhandled = mysqli_fetch_array($unhandled_tours)) {
		if (strtotime($unhandled['date']." ".$unhandled['time']) > time()) {
			continue; //skip if the tour is still in the future
		}
		if ($unhandled['date']!=$prevDate) {
			$unhandledList = $unhandledList."\n"."<tr><td colspan=2><h4><em>".date('l, F j, Y',strtotime($unhandled['date']))."</em></h4></td></tr>";
		}
		if ($unhandled['tour_id']!=$prevTourID){
			if($prevTourID!=0) {
				//if this isn't the first tour, close out the previous one:
				$unhandledList = $unhandledList."</td></tr>";
			}
			if ($unhandled['notes']!='') {
				$notes = $unhandled['notes'];
			} else {
				$notes = '';
			}
			$unhandledList = $unhandledList."\n".
				"<tr>
					<td>".
						"<label class=\"checkbox\" style=\"display:inline-block\">
							<input id=\"tourCheck_".$unhandled['tour_id']."\" type=\"checkbox\" class=\"tourCheckBox\">
						<span style=\"cursor:pointer; display:inline-block; line-height:17px; font-size:10.5pt\" class=\"label label-primary\">".date('g:i',strtotime($unhandled['date']." ".$unhandled['time']))." ".$unhandled['abbrev']."</span>
						</label>
						<p style=\"margin-left:20px; margin-top:0px; padding:0px; font-style:italic; font-size:10pt\">".$notes."</p>
					</td>
					<td style=\"vertical-align:middle; min-width:250px\">".
						"<label class=\"checkbox\" style=\"display:block\">
							<input id=\"tourCheck_".$unhandled['tour_id']."_".$unhandled['guide_id']."\" type=\"checkbox\" class=\"guideCheckBox\">".
							" <span style=\"cursor:pointer; display:inline-block; line-height:17px; font-size:10.5pt\" class=\"label label-info\">".$unhandled['guide_fname']." ".$unhandled['guide_lname']."</span>
						</label>";
		} else {
			$unhandledList = $unhandledList."\n".
						"<label class=\"checkbox\" style=\"display:block\">
							<input id=\"tourCheck_".$unhandled['tour_id']."_".$unhandled['guide_id']."\" type=\"checkbox\" class=\"guideCheckBox\">".
							" <span style=\"cursor:pointer; display:inline-block; line-height:17px; font-size:10.5pt\" class=\"label label-info\">".$unhandled['guide_fname']." ".$unhandled['guide_lname']."</span>
						</label>";
		}
		$prevTourID = $unhandled['tour_id'];
		$prevDate = $unhandled['date'];
	}
	$unhandledList = $unhandledList."</td></tr>";
}

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
			.modal{overflow:hidden;}
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
		<!--typeahead; the auto-complete textbox for picking a guide-->
		<script type="text/javascript" src="js/typeahead.bundle.js"></script>
	</head>

<!-- Body of webpage (not reused, but reusable elements inside) -->
	<body style="padding-top:40px; padding-bottom: 60px">
		<?include_once("includes/nav.php")?>
		<?include_once("includes/footer.php")?>


		<div class="container">
			<h1>Manage Tour Credit</h1>
			<div class="row">
				<button style="float:right; margin-right:10px; margin-bottom:5px" id="regularToursButton" class="btn btn-primary" type="button" data-toggle="modal" data-target="#customCreditModal"><span class="glyphicon glyphicon-plus"></span> Custom Credit</button>
			</div>
			<div class="well" id="bigWell" style="background-color:#BB9999">
				<div class="row">
					<div class="col-md-2"></div>
					<div class="col-md-8">
						<div class="panel panel-default">
							<div class="panel-heading"><h4 style="margin:2px">Unprocessed Tours</h4></div>
							<div class="panel-body">
								<?if (!$noUnhandledTours) echo
								'<strong>Do with checked:</strong><br>
								<button style="float:right; margin-right:10px" id="selectNoneButton" class="btn btn-default" type="button">Select None</button>
								<button style="float:right; margin-right:10px" id="selectAllButton" class="btn btn-default" type="button">Select All</button>
								<button style="float:left; margin-right:10px" id="creditButton" class="btn btn-success" type="button"><span class="glyphicon glyphicon-ok-circle"></span> Give Credit</button>
								<button style="float:left; margin-right:10px" id="missedButton" class="btn btn-danger" type="button"><span class="glyphicon glyphicon-remove-circle"></span> Mark as Missed</button>
								<button style="float:left; margin-right:10px" id="dropButton" class="btn btn-danger" type="button"><span class="glyphicon glyphicon-remove"></span> Remove</button>'
								?>
							</div>
							<table id="unhandledToursTable" class="table">
								<?echo $unhandledList?>
								<?echo $unhandled_error?>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>


		<div class="modal" id="customCreditModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header" style="text-align:center">
						<h3 class="modal-title"><strong>Custom Tour Credit</strong></h3>
					</div>
					<div class="modal-body">
						<table class="table">
							<tr>
								<td style="text-align:right; vertical-align:middle"><strong>Date:</strong></td>
								<td>
									<div class="input-group">
										<input type="text" class="form-control" id="customCreditDateBox" data-provide="datepicker" style="text-align:center; background-color:#FFFFFF" disabled>
										<span class="input-group-btn">
											<button id="customCreditCalendarButton" class="btn btn-default" type="button" onclick="$('#customCreditDateBox').datepicker('show')"><span class = "glyphicon glyphicon-calendar"></span></button>
										</span>
									</div>
								</td>
							</tr>
							<tr>
								<td style="text-align:right; vertical-align:middle"><strong>Time:</strong></td>
								<td>
									<div id='customCreditTimeDiv' class='input-group date'>
										<input id='customCreditTimeBox' type='text' class="form-control" style="text-align:center;"/>
										<span class="input-group-addon" style="background-color:#FFFFFF"><span class="glyphicon glyphicon-time"></span></span>
									</div>
								</td>
							</tr>
							<tr>
								<td style="text-align:right; vertical-align:middle"><strong>Type:</strong></td>
								<td>
									<select id="customCreditTypeBox" class="form-control" style="cursor:pointer">
										<?echo $tourTypeOptions?>
									</select>
								</td>
							</tr>
							<tr>
								<td style="text-align:right; vertical-align:middle"><strong>Guide:</strong></td>
								<td>
									<div id="customCreditGuideBox">
										<input id="customCreditGuideBoxInput" class="typeahead form-control" type="text" placeholder="Guide's name" autocomplete="on">
									</div>
								</td>
							</tr>
							<tr>
								<td style="text-align:right; vertical-align:top"><strong>Notes:</strong></td>
								<td><textarea id="customCreditNotes" class="form-control" rows="3" style="resize: none" placeholder="(optional)"></textarea></td>
							</tr>
						</table>
						<p style="text-align:right">
							<button id="customCreditCancelButton" type="button" class="btn btn-default" data-dismiss="modal" onclick="clearCustomCredit();">Cancel</button>
							<button id="customCreditSubmitButton" type="button" class="btn btn-primary" data-dismiss="modal">Create Tour and Assign Credit</button>
						</p>
					</div>
				</div>
			</div>
		</div>


	</body>

	<script>
		$(function() {
			//initialize the datepicker in the custom credit modal:
			$('#customCreditDateBox').datepicker({
				format: 'MM d, yyyy',
				autoclose: true,
				todayBtn: 'linked',
				todayHighlight: true
			});

			//initialize the timepicker in the custom credit modal:
			$('#customCreditTimeDiv').datetimepicker({
				pickDate: false
			});

			//initialize the guide picker in the custom credit modal:
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

			$('#customCreditGuideBox .typeahead').typeahead({
			  hint: true,
			  highlight: true,
			  minLength: 2
			},
			{
			  name: 'guides',
			  displayKey: 'value',
			  source: substringMatcher(guideList)
			});


			//Form control events:

			$('#selectAllButton').click( function() {
				$('.guideCheckBox, .tourCheckBox').each(function() {
					$(this).prop('checked',true);
				});
			});
			$('#selectNoneButton').click( function() {
				$('.guideCheckBox, .tourCheckBox').each(function() {
					$(this).prop('checked',false);
				});
			});

			$('.tourCheckBox').click( function() {
				var id = $(this).attr('id');
				var id = id.split('_');
				var tourID = id[1];
				var checked = $(this).prop('checked');
				$('.guideCheckBox').each(function() {
					var id2 = $(this).attr('id');
					var id2 = id2.split('_');
					var tourID2 = id2[1];
					if (tourID2==tourID) {
						$(this).prop('checked',checked);
					}
				});
			});

			$('#creditButton').click( function() {
				guideIDs = "";
				tourIDs = "";
				$('.guideCheckBox').each(function() {
					if ($(this).prop('checked')) {
						var id = $(this).attr('id');
						var id = id.split('_');
						var tourID = id[1];
						var guideID = id[2];
						guideIDs = guideIDs+guideID+",";
						tourIDs = tourIDs+tourID+",";
					}
				});
				guideIDs = guideIDs.substring(0,guideIDs.length-1);
				tourIDs = tourIDs.substring(0,tourIDs.length-1);
				//alert('guideIDs = '+guideIDs+'\ntourIDs = '+tourIDs); //for debugging
				if (guideIDs.length>0) {
					$.post("functions/creditguide.php",{
							guideID:guideIDs,
							tourID:tourIDs,
							status:'credited'
						}, function(data) {
							if (data!='') {
								alert(data); //Show any errors that occur.
							}
							location.reload();
						});
				}
			});

			$('#missedButton').click( function() {
				guideIDs = "";
				tourIDs = "";
				$('.guideCheckBox').each(function() {
					if ($(this).prop('checked')) {
						var id = $(this).attr('id');
						var id = id.split('_');
						var tourID = id[1];
						var guideID = id[2];
						guideIDs = guideIDs+guideID+",";
						tourIDs = tourIDs+tourID+",";
					}
				});
				guideIDs = guideIDs.substring(0,guideIDs.length-1);
				tourIDs = tourIDs.substring(0,tourIDs.length-1);
				//alert('guideIDs = '+guideIDs+'\ntourIDs = '+tourIDs); //for debugging
				if (guideIDs.length>0) {
					$.post("functions/creditguide.php",{
							guideID:guideIDs,
							tourID:tourIDs,
							status:'missed'
						}, function(data) {
							if (data!='') {
								alert(data); //Show any errors that occur.
							}
							location.reload();
						});
				}
			});

			$('#dropButton').click( function() {
				guideIDs = "";
				tourIDs = "";
				$('.guideCheckBox').each(function() {
					if ($(this).prop('checked')) {
						var id = $(this).attr('id');
						var id = id.split('_');
						var tourID = id[1];
						var guideID = id[2];
						guideIDs = guideIDs+guideID+",";
						tourIDs = tourIDs+tourID+",";
					}
				});
				guideIDs = guideIDs.substring(0,guideIDs.length-1);
				tourIDs = tourIDs.substring(0,tourIDs.length-1);
				//alert('guideIDs = '+guideIDs+'\ntourIDs = '+tourIDs); //for debugging
				if (guideIDs.length>0) {
					$.post("functions/dropguide.php",{
							guideID:guideIDs,
							tourID:tourIDs
						}, function(data) {
							if (data!='') {
								alert(data); //Show any errors that occur.
							}
							location.reload();
						});
				}
			});

			$('#customCreditSubmitButton').click( function() {
				//Create new tour:
				var date = $('#customCreditDateBox').val();
				var time = $('#customCreditTimeBox').val();
				var type = $('#customCreditTypeBox').val();
				var numSlots = 1;
				var notes = $('#customCreditNotes').val();
				tourID = newTour(date,time,type,numSlots,notes);
				//alert('tourID='+tourID); //for debugging
				//Identify guide:
				var newGuideName = $('#customCreditGuideBoxInput').val();
				$('#customCreditGuideBoxInput').val('');
				var newGuideIndex = guideList.indexOf(newGuideName);
				var newGuideID = guideIDList[newGuideIndex];
				if (newGuideIndex==-1) {
					alert('Error: guide "'+newGuideName+'" not found.');
				} else {
					//Sign that guide up:
					signup(newGuideID,tourID);
					//Give that guide tour credit:
					creditGuide(newGuideID,tourID);
				}
			});
		});

		function newTour(date,time,type,numSlots,notes) {
			var tourID = 5;
			$.ajax({type:'POST',
					url: "functions/changetour.php",
					data:{
						newTour:1,
						deleteTour:0,
						date:date,
						time:time,
						type:type,
						numSlots:numSlots,
						notes:notes
					},
					success: function(data) {
								data = JSON.parse(data);
								if (data.error!='') {
									alert(data.error); //Show any errors that occur.
								}
								tourID = data.tour_id;
							},
					async: false
					});
			return tourID;
		}

		function signup(guideID, tourID) {
			$.ajax({type:'POST',
					url: "functions/signupguide.php",
					data:{
						guideID:guideID,
						tourID:tourID,
					},
					success: function(data) {
								//alert(data) //For debugging.
							},
					async:false
					});
		}

		function creditGuide(guideID,tourID) {
			$.ajax({type:'POST',
					url:"functions/creditguide.php",
					data: {
						guideID:guideID,
						tourID:tourID,
						status:'credited'
					},
					success: function(data) {
								if (data!='') {
									alert(data); //Show any errors that occur.
								}
							},
					async: false
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
							},
					async: false
					});
		}

		function clearCustomCredit() {
			$('#customCreditDateBox').val('');
			$('#customCreditTimeBox').val('');
			$('#customCreditTypeBox').val(7); //this is the type_id for AR
			$('#customCreditNumGuides').val(1);
			$('#customCreditNotes').val('');
		}
	</script>

</html>
