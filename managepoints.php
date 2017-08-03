<? date_default_timezone_set('America/New_York');
require_once("authenticate.php"); 

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
	$startDate = date('Y-m-d',mktime(0,0,0,8,1,$currYear));
	$endDate = date('Y-m-d',mktime(0,0,0,12,31,$currYear));
	$tourReq = $igis_settings['tour_req_fall'];
	$hisReq = $igis_settings['his_req_fall'];
	$admReq = $igis_settings['adm_req_fall'];
	$TIPReq = $igis_settings['tip_req_fall'];
}

$pointDefQuery = mysqli_query($link, "SELECT * FROM point_types ORDER BY infraction ASC");

$addPointOptions = '<option value="0,0">Custom</option>';
$removePointOptions = '<option value="0,0">Custom</option>';
while ($pointDef=mysqli_fetch_array($pointDefQuery)) {
	$pointDefOption = '<option value="'.$pointDef['id'].','.round(floatval($pointDef['value']),1).'">'.$pointDef['infraction'].'</option>';
	if ($pointDef['value']>0) {
		$addPointOptions = $addPointOptions.$pointDefOption;
	} else {
		$removePointOptions = $removePointOptions.$pointDefOption;
	}
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
		<!--typeahead; the auto-complete textbox for picking a guide-->
		<script type="text/javascript" src="js/typeahead.bundle.js"></script>
	</head>
  
<!-- Body of webpage (not reused, but reusable elements inside) -->
	<body style="padding-top:40px; padding-bottom: 60px">
		<?include_once("includes/nav.php")?>
		<?include_once("includes/footer.php")?>

		
		<div class="container">
			<h1>Manage Points</h1>
			<div class="row">
				<div class="col-md-5"></div>
				<div class="col-md-2">
					Order by:
					<select id="orderByBox" class="form-control" style="cursor:pointer; display:inline-block">
						<option value="Points">Number of Points</option>
						<option value="Tours">Number of Tours</option>
						<option value="Last Name">Last Name</option>
						<option value="First Name">First Name</option>
						<option value="Probie Class">Probie Class</option>
						<option value="Year">Year</option>
						<option value="School">School</option>
					</select>
				</div>
				<div class="col-md-5" style="vertical-align:bottom; text-align:right">
					<button class="btn btn-primary" style="margin-bottom:5px" onclick="addRemovePopup(0)">Add/Remove Points</button><br>
					<button class="btn btn-primary" style="margin-bottom:5px" onclick="addRemoveSelectedPopup()">Add/Remove Points To/From Selected</button>
				</div>
			</div>
			<div class="well">
				<table class="table respTable" id="pointsListTable">
					<thead>
						<th>Name</th>
						<th>School</th>
						<th>Year</th>
						<th>Probie Class</th>
						<th>Points</th>
						<th style="min-width:60px"><small><em><?echo $semester." ".$currYear?></em></small><br>Tours</th>
						<th style="max-width:25px; text-align:right"><button class="btn btn-default btn-xs" onclick="selectNone()">Clear</button></th>
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
			table.respTable td:nth-child(2), 
			table.respTable th:nth-child(2), 
			table.respTable td:nth-child(3), 
			table.respTable th:nth-child(3), 
			table.respTable td:nth-child(4), 
			table.respTable th:nth-child(4) {display: none;}
		}
		</style>
		
		
		
		<div class="modal" id="pointsModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header" style="text-align:center">
						<h3 class="modal-title" style="display:inline"><strong>Points Detail: <span id="guideName">[guide]</span></strong></h3>
						<h4 style="margin-top:0px; margin-bottom:5px"><em>Total:</em> <span id="guidePoints">[x points]</span> <span id="guidePointsButton">[+/-]</span></h4>
						<small><em><?echo $semester." ".$currYear?>:</em></small><br>
						<span id="guideTours"></span>
					</div>
					<div class="modal-body">
						<div id="pointsTable">
						</div>
						<p style="text-align:right">
							<button id="closePointsButton" type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						</p>
					</div>
				</div>
			</div>
		</div>
		
		<div class="modal" id="addRemoveModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header" style="text-align:center">
						<h3 class="modal-title" style="display:inline"><strong>Add or remove points</strong></h3>
					</div>
					<div class="modal-body">
						<table class="table">
							<tr>
								<td style="width:130px"></td>
								<td>
									<div class="btn-group" data-toggle="buttons">
										<label id="addPointsRadioContainer" class="btn btn-danger active">
											<input type="radio" name="options" id="addPointsRadio" onclick="addPointsMode()" checked> Add
										</label>
										<label id="removePointsRadioContainer" class="btn btn-success">
											<input type="radio" name="options" id="removePointsRadio" onclick="removePointsMode()"> Remove
										</label>
									</div>
								</td>
							</tr>
							<tr>
								<td style="vertical-align:middle; text-align:right">Guide:</td>
								<td><div id="pointGuideBox"><input id="pointGuideBoxInput" class="typeahead form-control" type="text" placeholder="Guide's name" autocomplete="on"><div></td>
							</tr>
							<tr>
								<td style="vertical-align:middle; text-align:right">Description:</td>
								<td>
									<select id="pointDescriptionBox" class="form-control" style="cursor:pointer">
										<?echo $addPointOptions?>
									</select>
								</td>
							</tr>
							<tr>
								<td style="vertical-align:middle; text-align:right">Number of Points:</td>
								<td>
									<span id="pointSign" style="font-size:12pt; font-weight:bold; color:#FF0000">+</span>
									<input id="pointNumBox" type="text" class="form-control" value="" style="width:50px; display:inline">
								</td>
							</tr>
							<tr>
								<td style="text-align:right">Comment:</td>
								<td><textarea id="pointCommentBox" class="form-control" rows="3" style="resize: none" placeholder="(optional)"></textarea></td>
							</tr>
						</table>
						<p style="text-align:right">
							<button id="cancelAddRemoveButton" type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
							<button id="submitAddRemoveButton" type="button" class="btn btn-primary" onclick="submitPoint()">Submit</button>
						</p>
					</div>
				</div>
			</div>
		</div>
		
		<div class="modal" id="addRemoveSelectedModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header" style="text-align:center">
						<h3 class="modal-title" style="display:inline"><strong>Add or remove points from selected guides (<span id="numSelectedGuides">0</span>)</strong></h3>
					</div>
					<div class="modal-body">
						<table class="table">
							<tr>
								<td style="width:130px"></td>
								<td>
									<div class="btn-group" data-toggle="buttons">
										<label id="addPointsSelectedRadioContainer" class="btn btn-danger active">
											<input type="radio" name="options" id="addPointsSelectedRadio" onclick="addPointsModeSelected()" checked> Add
										</label>
										<label id="removePointsSelectedRadioContainer" class="btn btn-success">
											<input type="radio" name="options" id="removePointsSelectedRadio" onclick="removePointsModeSelected()"> Remove
										</label>
									</div>
								</td>
							</tr>
							<tr>
								<td style="vertical-align:middle; text-align:right">Description:</td>
								<td>
									<select id="pointDescriptionSelectedBox" class="form-control" style="cursor:pointer">
										<?echo $addPointOptions?>
									</select>
								</td>
							</tr>
							<tr>
								<td style="vertical-align:middle; text-align:right">Number of Points:</td>
								<td>
									<span id="pointSignSelected" style="font-size:12pt; font-weight:bold; color:#FF0000">+</span>
									<input id="pointNumSelectedBox" type="text" class="form-control" value="" style="width:50px; display:inline">
								</td>
							</tr>
							<tr>
								<td style="text-align:right">Comment:</td>
								<td><textarea id="pointCommentSelectedBox" class="form-control" rows="3" style="resize: none" placeholder="(optional)"></textarea></td>
							</tr>
						</table>
						<p style="text-align:right">
							<button id="cancelAddRemoveSelectedButton" type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
							<button id="submitAddRemoveSelectedButton" type="button" class="btn btn-primary" onclick="submitPointSelected()">Submit</button>
						</p>
					</div>
				</div>
			</div>
		</div>
		
	</body>
	
	<script>
		var order = 'Points'; //default; this is what the page already is
		var order_query = 'numpoints';
		var categorize = false;
		
		var guideList;
		var guideIDList;

		$(function() {
			refresh();
			
			$('#orderByBox').on('change', function() {
				refreshOrder($(this).val());
			});
			
			$('#pointDescriptionBox').on('change', function() {
				processPointSelection();
			});
			
			$('#addPointsRadio').change( function() {
				addPointsMode();
			});
			
			$('#removePointsRadio').change( function() {
				removePointsMode();
			});
			
			$('#pointDescriptionSelectedBox').on('change', function() {
				processPointSelectionSelected();
			});
			
			$('#addPointsSelectedRadio').change( function() {
				addPointsModeSelected();
			});
			
			$('#removePointsSelectedRadio').change( function() {
				removePointsModeSelected();
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
			
			guideList = <?echo $guides?>;
			guideIDList = <?echo $guideIDs?>;
			 
			$('#pointGuideBox .typeahead').typeahead({
			  hint: true,
			  highlight: true,
			  minLength: 2
			},
			{
			  name: 'guides',
			  displayKey: 'value',
			  source: substringMatcher(guideList)
			});
		});


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


		function refresh() {
			$('#directoryBody').html('<td colspan=7 style="text-align:center"><h3>Loading...</h3></td>');
			$.get("functions/printpointlist.php",{
					guideID:'all',
					order:order_query,
					cat:categorize
				}, function(data) {
					$('#directoryBody').html(data);
					$('#pointsListTable tr').click(function(event) {
						if (event.target.type !== 'checkbox' && event.target.type !== 'button') {
							$(':checkbox', this).trigger('click');
						}
					});
				});
		}
		
		function pointsPopup(guideID) {
			$('.modal').modal('hide');
			$('#pointsModal').modal('show');
			updateGuidePoints(guideID);
		}
		
		function updateGuidePoints(guideID) {
			$('#pointsTable').html('<h3 style="text-align:center">Loading...</h3>');
			$.get("functions/printpointlist.php",{
					guideID:guideID,
					order:order_query,
					cat:categorize
				}, function(data) {
					data = JSON.parse(data);
					$('#pointsTable').html(data.table);
					$('#guideName').html(data.guideName);
					$('#guidePoints').html(data.sumPointsLabel);
					$('#guidePointsButton').html(data.addRemoveButton);
					$('#guideTours').html(data.tourCount);
				});
		}
		
		function addRemovePopup(guideID) {
			$('.modal').modal('hide');
			guideIndex = guideIDList.indexOf(guideID);
			guideName = guideList[guideIndex];
			//alert('guide #'+guideID+' (index '+guideIndex+'): '+guideName); //for debugging
			$('#pointGuideBoxInput').typeahead('val',guideName);
			$('#addPointsRadio').click(); //this also clears the dropdown and point value box via the onclick call to addPointsMode
			$('#pointCommentBox').val('');
			processPointSelection();
			$('#addRemoveModal').modal('show');
		}
		
		function addPointsMode() {
			$('#pointDescriptionBox').html('<?echo addcslashes($addPointOptions,"'")?>');
			$('#pointSign').html('+');
			$('#pointSign').css('color','#FF0000')
			processPointSelection();
		}
		
		function removePointsMode() {
			$('#pointDescriptionBox').html('<?echo addcslashes($removePointOptions,"'")?>');
			$('#pointSign').html('&ndash;');
			$('#pointSign').css('color','#009900')
			processPointSelection();
		}
		
		function processPointSelection() {
			var valueArr = $('#pointDescriptionBox').val().split(",");
			var typeID = Number(valueArr[0]);
			var pointVal = Number(valueArr[1]);
			//alert('typeID='+typeID+'; pointVal='+pointVal); //for debugging
			
			if (typeID==0) {
				$('#pointNumBox').prop('disabled',false);
				$('#pointNumBox').val('');
				$('#pointCommentBox').attr('placeholder','(required)');
			} else {
				$('#pointNumBox').prop('disabled',true);
				$('#pointNumBox').val(Math.abs(pointVal));
				$('#pointCommentBox').attr('placeholder','(optional)');
			}
		}
		
		function submitPoint() {
			var valueArr = $('#pointDescriptionBox').val().split(",");
			var typeID = Number(valueArr[0]);
			var pointVal = Math.abs(Number(valueArr[1]));
			if (typeID==0) {
				pointVal = Number($('#pointNumBox').val());
			}
			var comment = $('#pointCommentBox').val();
			var guideName = $('#pointGuideBoxInput').typeahead('val');
			var guideIndex = guideList.indexOf(guideName);
			var guideID = guideIDList[guideIndex];
			var sign
			if ($('#removePointsRadio').prop('checked')) {
				sign = -1;
			} else {
				sign = 1;
			}
			
			//alert('typeID='+typeID+'\npointVal='+pointVal+'\ncomment='+comment+'\nguideName='+guideName+'\nguideIndex='+guideIndex+'\nguideID='+guideID); //for debugging
			//check for errors before submitting:
			if (guideName=='') {
				alert('Error: You must specify a Guide.');
			} else if (guideIndex==-1) {
				alert('Error: Guide not found.');
			} else if (isNaN(pointVal)){
				alert('Error: Specified point value must be a number');
			} else if (pointVal<=0) {
				alert('Error: Specified point value must be positive. To remove points, click the "Remove" button at the top of the form.');
			} else if (typeID==0 && comment=='') {
				alert('Error: You must specify a comment for a custom point value.');
			} else {
				//alert('Submitting!\n\ntypeID='+typeID+'\npointVal='+pointVal+'\ncomment='+comment+'\nguideName='+guideName+'\nguideIndex='+guideIndex+'\nguideID='+guideID); //for debugging
				$.post("functions/changepoints.php",{
					guideID:guideID,
					typeID:typeID,
					pointVal:(sign*pointVal),
					comment:comment
				}, function(data) {
					data = JSON.parse(data);
					if(data.error!='') {
						alert(data.error);
					}
					pointsPopup(guideID);
					refresh();
				});
			}
		}
		
		//===== BULK ADD/REMOVE: =====
		function addRemoveSelectedPopup() {
			$('.modal').modal('hide');
			$('#addPointsSelectedRadio').click(); //this also clears the dropdown and point value box via the onclick call to addPointsMode
			$('#pointCommentSelectedBox').val('');
			processPointSelectionSelected();
			var numSelected = 0;
			$(':checkbox:checked').each( function() {
				numSelected++;
			});
			$('#numSelectedGuides').html(numSelected);
			if (numSelected!=0) {
				$('#addRemoveSelectedModal').modal('show');
			}
		}
		function addPointsModeSelected() {
			$('#pointDescriptionSelectedBox').html('<?echo addcslashes($addPointOptions,"'")?>');
			$('#pointSignSelected').html('+');
			$('#pointSignSelected').css('color','#FF0000')
			processPointSelectionSelected();
		}
		
		function removePointsModeSelected() {
			$('#pointDescriptionSelectedBox').html('<?echo addcslashes($removePointOptions,"'")?>');
			$('#pointSignSelected').html('&ndash;');
			$('#pointSignSelected').css('color','#009900')
			processPointSelectionSelected();
		}
		function processPointSelectionSelected() {
			var valueArr = $('#pointDescriptionSelectedBox').val().split(",");
			var typeID = Number(valueArr[0]);
			var pointVal = Number(valueArr[1]);
			//alert('typeID='+typeID+'; pointVal='+pointVal); //for debugging
			
			if (typeID==0) {
				$('#pointNumSelectedBox').prop('disabled',false);
				$('#pointNumSelectedBox').val('');
				$('#pointCommentSelectedBox').attr('placeholder','(required)');
			} else {
				$('#pointNumSelectedBox').prop('disabled',true);
				$('#pointNumSelectedBox').val(Math.abs(pointVal));
				$('#pointCommentSelectedBox').attr('placeholder','(optional)');
			}
		}
		function submitPointSelected() {
			var valueArr = $('#pointDescriptionSelectedBox').val().split(",");
			var typeID = Number(valueArr[0]);
			var pointVal = Number(Math.abs(valueArr[1]));
			if (typeID==0) {
				pointVal = Number($('#pointNumSelectedBox').val());
			}
			var comment = $('#pointCommentSelectedBox').val();
			var sign
			if ($('#removePointsSelectedRadio').prop('checked')) {
				sign = -1;
			} else {
				sign = 1;
			}
			//alert('typeID='+typeID+'\npointVal='+pointVal+'\ncomment='+comment+'\nguideName='+guideName+'\nguideIndex='+guideIndex+'\nguideID='+guideID); //for debugging
			//check for errors before submitting:
			if (isNaN(pointVal)){
				alert('Error: Specified point value must be a number');
			} else if (pointVal<=0) {
				alert('Test Error: Specified point value must be positive. To remove points, click the "Remove" button at the top of the form.');
			} else if (typeID==0 && comment=='') {
				alert('Error: You must specify a comment for a custom point value.');
			} else {
				//alert('Submitting!\n\ntypeID='+typeID+'\npointVal='+pointVal+'\ncomment='+comment); //for debugging
				//get list of selected:
				var guides = [];
				$(':checkbox:checked').each( function() {
					guides[guides.length] = $(this).val();
				});
				//alert('Selected Guides: ' + guides); //for debugging
				
				//submit selected:
				for (i=0; i<guides.length; i++) {
					guideID = guides[i];
					$.post("functions/changepoints.php",{
						guideID:guideID,
						typeID:typeID,
						pointVal:(sign*pointVal),
						comment:comment
					}, function(data) {
						data = JSON.parse(data);
						if(data.error!='') {
							alert(data.error);
						}
						refresh();
					});
				}
			}
		}
		
		function selectNone() {
			$(':checkbox:checked').each( function() {
				$(this).prop('checked',false);
			});
		}
		
		function deletePoint(pointID,guideID) {
			$.post("functions/changepoints.php",{
					deletePoint:1,
					pointID:pointID
				}, function(data) {
					data = JSON.parse(data);
					if(data.error!='') {
						alert(data.error);
					}
					updateGuidePoints(guideID);
					refresh();
				});
		}
	</script>
</html>
