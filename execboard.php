<? date_default_timezone_set('America/New_York');
require_once("authenticate.php"); 
$permssion_level = 3;
include("permission.php");

$guideList=mysqli_query($link,"SELECT firstname, lastname, guide_id FROM guides WHERE status='current'");
$guides = "[";
$guideIDs = "[";
while ($guide=mysqli_fetch_array($guideList)) {
	$guides = $guides."\"".$guide['firstname']." ".$guide['lastname']."\",";
	$guideIDs = $guideIDs.$guide['guide_id'].",";
}
$guides = substr($guides, 0, -1)."]"; //cut off the final comma and then close the bracket
$guideIDs = substr($guideIDs, 0, -1)."]"; //cut off the final comma and then close the bracket

//Get IDs of exec members:
$others = array();
$otherPositions = array();
$execIDs=mysqli_query($link,"SELECT * FROM exec_board ORDER BY position ASC");
$test = '';
while ($exec = mysqli_fetch_array($execIDs)) {
	if ($exec['is_chair'] && $exec['official']==1) {
		$chairID = $exec['guide_id'];
	} else if ($exec['is_vicechair'] && $exec['official']==1) {
		$vicechairID = $exec['guide_id'];
	} else if ($exec['is_scheduler'] && $exec['official']==1) {
		$schedulerID = $exec['guide_id'];
	} else if ($exec['is_disciplinarian'] && $exec['official']==1) {
		$disciplinarianID = $exec['guide_id'];
	} else if ($exec['is_techchair'] && $exec['official']==1) {
		$techchairID = $exec['guide_id'];
	} else {
		$others[count($others)+1] = $exec; //just using $others[] = $exec doesn't work for some reason.
	}
}

//Create other exec boxes:
$numOtherExec = count($others);
$otherRows = '';
for ($i=1; $i<=$numOtherExec; $i++) {
	$C_checked = ($others[$i]['is_chair'] ? 'checked' : '');
	$V_checked = ($others[$i]['is_vicechair'] ? 'checked' : '');
	$S_checked = ($others[$i]['is_scheduler'] ? 'checked' : '');
	$D_checked = ($others[$i]['is_disciplinarian'] ? 'checked' : '');
	$T_checked = ($others[$i]['is_techchair'] ? 'checked' : '');
	$otherRows = $otherRows.'<tr id="other'.$i.'Row"><td style="vertical-align:bottom"><input id="other'.$i.'position" class="form-control" value="'.$others[$i]['position'].'" style="display:inline; width:100px; font-size:8pt; padding:2px"></td><td style="text-align:center"><input id="other'.$i.'CBox" type="checkbox" '.$C_checked.'>C <input id="other'.$i.'VBox" type="checkbox" '.$V_checked.'>V <input id="other'.$i.'SBox" type="checkbox" '.$S_checked.'>S <input id="other'.$i.'DBox" type="checkbox" '.$D_checked.'>D <input id="other'.$i.'TBox" type="checkbox" '.$T_checked.'>T <button class="btn btn-xs btn-danger" onclick="removeOther('.$i.')">X</button><br><div id="other'.$i.'TT"><input id="other'.$i.'Input" class="typeahead form-control" type="text" placeholder="(name)" autocomplete="on" value="'.$others[$i]['guide_id'].'"></div></td>';
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
		<!--typeahead; the auto-complete textbox for picking a guide-->
		<script type="text/javascript" src="js/typeahead.bundle.js"></script>
	</head>
  
<!-- Body of webpage (not reused, but reusable elements inside) -->
	<body style="padding-top:40px; padding-bottom: 60px">
		<?include_once("includes/nav.php")?>
		<?include_once("includes/footer.php")?>

		
		<div class="container">
			<h1>Exec Control Board</h1>
			<div class="well" id="test">
				<div class="row">
					<div class="col-md-4">
						<div class="panel panel-info">
							<div class="panel-heading">Exec Board Members:</div>
							<div class="panel-body">
								<table id="execMembersTable" class="table">
									<tr>
										<td style="vertical-align:middle">Chair:</td>
										<td><div id="chairTT"><input id="chairInput" class="typeahead form-control" type="text" placeholder="(name)" autocomplete="on"></div></td>
									</tr>
									<tr>
										<td style="vertical-align:middle">Vice Chair:</td>
										<td><div id="vicechairTT"><input id="vicechairInput" class="typeahead form-control" type="text" placeholder="(name)" autocomplete="on"></div></td>
									</tr>
									<tr>
										<td style="vertical-align:middle">Scheduler:</td>
										<td><div id="schedulerTT"><input id="schedulerInput" class="typeahead form-control" type="text" placeholder="(name)" autocomplete="on"></div></td>
									</tr>
									<tr>
										<td style="vertical-align:middle">Disciplinarian:</td>
										<td><div id="disciplinarianTT"><input id="disciplinarianInput" class="typeahead form-control" type="text" placeholder="(name)" autocomplete="on"></div></td>
									</tr>
									<tr>
										<td style="vertical-align:middle">Tech Chair:</td>
										<td><div id="techchairTT"><input id="techchairInput" class="typeahead form-control" type="text" placeholder="(name)" autocomplete="on"></div></td>
									</tr>
									<?echo $otherRows;?>
								</table>
								<button class="btn btn-info" onclick="addOther()">Add Other Member</button>
								<button id="execSubmitButton" class="btn btn-primary" onclick="updateExec()" style="float:right">Submit</button>
							</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="panel panel-warning">
							<div class="panel-heading">IGIS Settings:</div>
							<div class="panel-body">
								<table class="table">
									<tr>
										<td style="vertical-align:middle">Unfilled Tour Display:</td>
										<td style="vertical-align:middle"><input id="unfilledTourDays" class="form-control" style="display:inline; width:50px; text-align:center" value="<?echo $igis_settings['unfilled_tour_display_days']?>"> days</td>
									</tr>
									<tr>
										<td style="vertical-align:middle">Drop Tour Window:</td>
										<td style="vertical-align:middle"><input id="dropTime" class="form-control" style="display:inline; width:50px; text-align:center" value="<?echo $igis_settings['drop_time']?>"> hours</td>
									</tr>
									<tr>
										<td style="vertical-align:middle">Expulsion Threshold:</td>
										<td style="vertical-align:middle"><input id="pointThreshold" class="form-control" style="display:inline; width:50px; text-align:center" value="<?echo $igis_settings['point_threshold']?>"> points</td>
									</tr>
									<tr>
										<th colspan=2>Tour Requirements:</th>
									</tr>
									<tr>
										<td style="vertical-align:middle">Fall Total:</td>
										<td style="vertical-align:middle"><input id="fallTourReq" class="form-control" style="display:inline; width:50px; text-align:center" value="<?echo $igis_settings['tour_req_fall']?>"> tours</td>
									</tr>
									<tr>
										<td style="vertical-align:middle">Fall Admissions:</td>
										<td style="vertical-align:middle"><input id="fallAdmReq" class="form-control" style="display:inline; width:50px; text-align:center" value="<?echo $igis_settings['adm_req_fall']?>"> tours</td>
									</tr>
									<tr>
										<td style="vertical-align:middle">Fall Historical:</td>
										<td style="vertical-align:middle"><input id="fallHisReq" class="form-control" style="display:inline; width:50px; text-align:center" value="<?echo $igis_settings['his_req_fall']?>"> tours</td>
									</tr>
									<tr>
										<td style="vertical-align:middle">Spring Total:</td>
										<td style="vertical-align:middle"><input id="springTourReq" class="form-control" style="display:inline; width:50px; text-align:center" value="<?echo $igis_settings['tour_req_spring']?>"> tours</td>
									</tr>
									<tr>
										<td style="vertical-align:middle">Spring Admissions:</td>
										<td style="vertical-align:middle"><input id="springAdmReq" class="form-control" style="display:inline; width:50px; text-align:center" value="<?echo $igis_settings['adm_req_spring']?>"> tours</td>
									</tr>
									<tr>
										<td style="vertical-align:middle">Spring Historical:</td>
										<td style="vertical-align:middle"><input id="springHisReq" class="form-control" style="display:inline; width:50px; text-align:center" value="<?echo $igis_settings['his_req_spring']?>"> tours</td>
									</tr>
									<tr>
										<th colspan=2>TIP Requirements:</th>
									</tr>
									<tr>
										<td style="vertical-align:middle">Fall:</td>
										<td style="vertical-align:middle"><input id="fallTIPReq" class="form-control" style="display:inline; width:50px; text-align:center" value="<?echo $igis_settings['tip_req_fall']?>"> TIPs</td>
									</tr>
									<tr>
										<td style="vertical-align:middle">Spring:</td>
										<td style="vertical-align:middle"><input id="springTIPReq" class="form-control" style="display:inline; width:50px; text-align:center" value="<?echo $igis_settings['tip_req_spring']?>"> TIPs</td>
									</tr>
									<tr>
										<th colspan=2>Document URLs:</th>
									</tr>
									<tr>
										<td colspan=2>
											Constitution:
											<input id="constitutionURL" class="form-control" value="<?echo $igis_settings['constitution_url']?>">
										</td>
									</tr>
									<tr>
										<td colspan=2>
											Bylaws:
											<input id="bylawsURL" class="form-control" value="<?echo $igis_settings['bylaws_url']?>">
										</td>
									</tr>
									<tr>
										<td colspan=2>
											Exec Agenda:
											<input id="execAgendaURL" class="form-control" value="<?echo $igis_settings['exec_agenda_url']?>">
										</td>
									</tr>
									<tr>
										<td colspan=2>
											Exec Minutes:
											<input id="execMinutesURL" class="form-control" value="<?echo $igis_settings['exec_minutes_url']?>">
										</td>
									</tr>
								</table>
								<button id="settingsSubmitButton" class="btn btn-primary" onclick="updateSettings()" style="float:right">Submit</button>
							</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="panel panel-success">
							<div class="panel-heading">Exec Messages:</div>
							<div class="panel-body">
								<p>
									<strong><em>Exec Meeting Message:</em></strong> (for <a href="home.php" target="_blank">IGIS homepage</a>)
									<textarea id="execMessage" class="form-control" rows="3" style="resize: vertical" placeholder="(none)" maxlength="1000"><?echo $igis_settings['exec_homepage_msg']?></textarea>
								</p>
								<p>
									<strong><em>Home Page Alert:</em></strong>
									<textarea id="homepageAlert" class="form-control" rows="3" style="resize: vertical" placeholder="(none)" maxlength="1000"><?echo $igis_settings['homepage_alert']?></textarea>
									Color:
									<select id="homepageAlertColor" class="form-control" style="display:inline; width:120px; cursor:pointer; margin-top:5px">
										<option value="info">Blue</option>
										<option value="success">Green</option>
										<option value="warning">Yellow</option>
										<option value="danger">Red</option>
									</select>
								</p>
								<p>
									<strong><em>IGIS Global Alert:</em></strong>
									<textarea id="globalAlert" class="form-control" rows="3" style="resize: vertical" placeholder="(none)" maxlength="1000"><?echo $igis_settings['global_alert']?></textarea>
									Color:
									<select id="globalAlertColor" class="form-control" style="display:inline; width:120px; cursor:pointer; margin-top:5px">
										<option value="info">Blue</option>
										<option value="success">Green</option>
										<option value="warning">Yellow</option>
										<option value="danger">Red</option>
									</select>
								</p>
								
								<p>
									<span id="test"></span>
									<strong><em>UGS Operating Status:</em></strong> (for <a href="http://www.uvaguides.org/" target="_blank">public-facing site</a>)
									<textarea id="operatingStatus" class="form-control" rows="5" style="resize: vertical" placeholder="(mandatory)" maxlength="1000"><?echo $igis_settings['operating_status']?></textarea>
								</p>
								<button id="alertsSubmitButton" class="btn btn-primary" onclick="updateAlerts()" style="float:right">Submit</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>


	</body>
	
	<script>
	
		var guideList;
		var guideIDList;
		var chair = <?echo $chairID?>;
		var vicechair = <?echo $vicechairID?>;
		var scheduler = <?echo $schedulerID?>;
		var disciplinarian = <?echo $disciplinarianID?>;
		var techchair = <?echo $techchairID?>;
		var numOtherExec = <?echo $numOtherExec?>;

		$(function() {
			
			//pre-set the color drop-downs
			$('#homepageAlertColor').val('<?echo $igis_settings['homepage_alert_color']?>');
			$('#globalAlertColor').val('<?echo $igis_settings['global_alert_color']?>');
			
			//size textareas appropriately:
			window.setTimeout( function() {
				$("#execMessage").height( $("#execMessage")[0].scrollHeight );
				$("#homepageAlert").height( $("#homepageAlert")[0].scrollHeight );
				$("#globalAlert").height( $("#globalAlert")[0].scrollHeight );
				$("#operatingStatus").height( $("#operatingStatus")[0].scrollHeight );
			}, 1);
			
			//This code taken directly from the Typeahead website:
			substringMatcher = function(strs) {
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
			 
			//Activate and fill in the exec members' boxes:
			$('#chairTT .typeahead').typeahead({
			  hint: true,
			  highlight: true,
			  minLength: 2
			},
			{
			  name: 'guides',
			  displayKey: 'value',
			  source: substringMatcher(guideList)
			});
			$('#chairInput').val(guideList[guideIDList.indexOf(chair)]);
			
			$('#vicechairTT .typeahead').typeahead({
			  hint: true,
			  highlight: true,
			  minLength: 2
			},
			{
			  name: 'guides',
			  displayKey: 'value',
			  source: substringMatcher(guideList)
			});
			$('#vicechairInput').val(guideList[guideIDList.indexOf(vicechair)]);
			
			$('#schedulerTT .typeahead').typeahead({
			  hint: true,
			  highlight: true,
			  minLength: 2
			},
			{
			  name: 'guides',
			  displayKey: 'value',
			  source: substringMatcher(guideList)
			});
			$('#schedulerInput').val(guideList[guideIDList.indexOf(scheduler)]);
			
			$('#disciplinarianTT .typeahead').typeahead({
			  hint: true,
			  highlight: true,
			  minLength: 2
			},
			{
			  name: 'guides',
			  displayKey: 'value',
			  source: substringMatcher(guideList)
			});
			$('#disciplinarianInput').val(guideList[guideIDList.indexOf(disciplinarian)]);
			
			$('#techchairTT .typeahead').typeahead({
			  hint: true,
			  highlight: true,
			  minLength: 2
			},
			{
			  name: 'guides',
			  displayKey: 'value',
			  source: substringMatcher(guideList)
			});
			$('#techchairInput').val(guideList[guideIDList.indexOf(techchair)]);
			
			//Activate and fill in the other exec members' boxes:
			for (i=1; i<=numOtherExec; i++) {
				$('#other'+i+'TT .typeahead').typeahead({
				  hint: true,
				  highlight: true,
				  minLength: 2
				},
				{
				  name: 'guides',
				  displayKey: 'value',
				  source: substringMatcher(guideList)
				});
				//replace the ID with the name
				var id = Number($('#other'+i+'Input').val());
				var name = guideList[guideIDList.indexOf(id)];
				//alert('"#other'+i+'Input" value = '+id+'; name = '+name) //for debugging
				$('#other'+i+'Input').val(name);
			}
		});
		
		function updateSettings() {
			$('#settingsSubmitButton').html('Loading...');
			$('#settingsSubmitButton').prop('disabled',true);
			var unfilledTourDays = Number($('#unfilledTourDays').val());
			var dropTime = Number($('#dropTime').val());
			var pointThreshold = Number($('#pointThreshold').val());
			var fallTourReq = Number($('#fallTourReq').val());
			var fallAdmReq = Number($('#fallAdmReq').val());
			var fallHisReq = Number($('#fallHisReq').val());
			var springTourReq = Number($('#springTourReq').val());
			var springAdmReq = Number($('#springAdmReq').val());
			var springHisReq = Number($('#springHisReq').val());
			var fallTIPReq = Number($('#fallTIPReq').val());
			var springTIPReq = Number($('#springTIPReq').val());
			var constitutionURL = $('#constitutionURL').val();
			var bylawsURL = $('#bylawsURL').val();
			var execAgendaURL = $('#execAgendaURL').val();
			var execMinutesURL = $('#execMinutesURL').val();
			//alert("Updating settings:\n\nUnfilled Tour Days: "+unfilledTourDays+"\nDrop Time: "+dropTime+"\nExpulsion Threshold: "+pointThreshold+"\nFall Tour Requirement: "+fallTourReq+" ("+fallAdmReq+" adm, "+fallHisReq+" his)\nSpring Tour Requirement: "+springTourReq+" ("+springAdmReq+" adm, "+springHisReq+" his)\nFall TIP Requirement: "+fallTIPReq+"\nSpring TIP Requirement: "+springTIPReq+"\n\nConstitution URL: "+constitutionURL+"\n\nBylaws URL: "+bylawsURL+"\n\nExec Agenda URL: "+execAgendaURL+"\n\nExecMinutesURL: "+execMinutesURL); //for debugging
			$.post("functions/changesettings.php",{
				names:'unfilled_tour_display_days,drop_time,point_threshold,tour_req_fall,adm_req_fall,his_req_fall,tour_req_spring,adm_req_spring,his_req_spring,tip_req_fall,tip_req_spring',
				values:unfilledTourDays+','+dropTime+','+pointThreshold+','+fallTourReq+','+fallAdmReq+','+fallHisReq+','+springTourReq+','+springAdmReq+','+springHisReq+','+fallTIPReq+','+springTIPReq,
				constitutionURL:constitutionURL,
				bylawsURL:bylawsURL,
				execAgendaURL:execAgendaURL,
				execMinutesURL:execMinutesURL
			}, function(data) {
				data = JSON.parse(data);
				if (data.error!='') {
					alert(data.error);
				}
				$('#settingsSubmitButton').html('Submit')
				$('#settingsSubmitButton').prop('disabled',false);
			});
		}
		
		function updateAlerts() {
			$('#alertsSubmitButton').html('Loading...')
			$('#alertsSubmitButton').prop('disabled',true);
			var execMessage = $('#execMessage').val();
			var homepageAlert = $('#homepageAlert').val();
			var homepageAlertColor = $('#homepageAlertColor').val();
			var globalAlert = $('#globalAlert').val();
			var globalAlertColor = $('#globalAlertColor').val();
			var operatingStatus = $('#operatingStatus').val();
			//alert("Exec Message: \""+execMessage+"\"\n\nHomepage alert ("+homepageAlertColor+"): \""+homepageAlert+"\"\n\nGlobal Alert ("+globalAlertColor+"): \""+globalAlert+"\""); //for debugging
			$.post("functions/changesettings.php",{
				names:'homepage_alert_color,global_alert_color',
				values:homepageAlertColor+','+globalAlertColor,
				execMessage:execMessage,
				homepageAlert:homepageAlert,
				globalAlert:globalAlert,
				operatingStatus:operatingStatus,
			}, function(data) {
				data = JSON.parse(data);
				if (data.error!='') {
					alert(data.error);
				}
				$('#alertsSubmitButton').html('Submit')
				$('#alertsSubmitButton').prop('disabled',false);
			});
		}
		
		function addOther() {
			numOtherExec++;
			rowToInsert = '<tr id="other'+numOtherExec+'Row"><td style="vertical-align:bottom"><input id="other'+numOtherExec+'position" class="form-control" style="display:inline; width:100px; font-size:8pt; padding:2px"></td><td style="text-align:center"><input id="other'+numOtherExec+'CBox" type="checkbox">C <input id="other'+numOtherExec+'VBox" type="checkbox">V <input id="other'+numOtherExec+'SBox" type="checkbox">S <input id="other'+numOtherExec+'DBox" type="checkbox">D <input id="other'+numOtherExec+'TBox" type="checkbox">T <button class="btn btn-xs btn-danger" onclick="removeOther('+numOtherExec+')">X</button><br><div id="other'+numOtherExec+'TT"><input id="other'+numOtherExec+'Input" class="typeahead form-control" type="text" placeholder="(name)" autocomplete="on"></div></td>';
			$('#execMembersTable').append(rowToInsert);
			$('#other'+numOtherExec+'TT .typeahead').typeahead({
			  hint: true,
			  highlight: true,
			  minLength: 2
			},
			{
			  name: 'guides',
			  displayKey: 'value',
			  source: substringMatcher(guideList)
			});
		}
		
		function removeOther(otherNum) {
			$('#other'+otherNum+'Row').remove();
		}
		
		function updateExec() {
			$('#execSubmitButton').html('Loading...');
			$('#execSubmitButton').prop('disabled',true);
			var chairName = $('#chairInput').val();
			var vicechairName = $('#vicechairInput').val();
			var schedulerName = $('#schedulerInput').val();
			var disciplinarianName = $('#disciplinarianInput').val();
			var techchairName = $('#techchairInput').val();
			
			var chairID = guideIDList[guideList.indexOf(chairName)];
			var vicechairID = guideIDList[guideList.indexOf(vicechairName)];
			var schedulerID = guideIDList[guideList.indexOf(schedulerName)];
			var disciplinarianID = guideIDList[guideList.indexOf(disciplinarianName)];
			var techchairID = guideIDList[guideList.indexOf(techchairName)];
			
			var otherIDs = '';
			var otherPositions = '';
			var otherCs = '';
			var otherVs = '';
			var otherSs = '';
			var otherDs = '';
			var otherTs = '';
			for (i=1; i<=numOtherExec; i++) {
				if (typeof $('#other'+i+'position').val() != 'undefined'){
					var otherName = $('#other'+i+'Input').val();
					//alert(otherName); //for debugging
					otherID = guideIDList[guideList.indexOf(otherName)];
					otherIDs = otherIDs+otherID+','
					
					var otherPos = $('#other'+i+'position').val();
					otherPositions = otherPositions+otherPos+',';
					
					//alert('Setting '+otherName+' ('+otherID+') as position "'+otherPos+'"'); //for debugging
					
					if ($('#other'+i+'CBox').prop('checked')) {
						otherCs = otherCs+'1'+',';
					} else {
						otherCs = otherCs+'0'+',';
					}
					if ($('#other'+i+'VBox').prop('checked')) {
						otherVs = otherVs+'1'+',';
					} else {
						otherVs = otherVs+'0'+',';
					}
					if ($('#other'+i+'SBox').prop('checked')) {
						otherSs = otherSs+'1'+',';
					} else {
						otherSs = otherSs+'0'+',';
					}
					if ($('#other'+i+'DBox').prop('checked')) {
						otherDs = otherDs+'1'+',';
					} else {
						otherDs = otherDs+'0'+',';
					}
					if ($('#other'+i+'TBox').prop('checked')) {
						otherTs = otherTs+'1'+',';
					} else {
						otherTs = otherTs+'0'+',';
					}
				} else {
					//alert('Other #'+i+' doesn\'t seem to exist!'); //for debugging
				}
			}
			otherIDs = otherIDs.substring(0,otherIDs.length-1); //cut off last comma
			otherPositions = otherPositions.substring(0,otherPositions.length-1); //cut off last comma
			otherCs = otherCs.substring(0,otherCs.length-1); //cut off last comma
			otherVs = otherVs.substring(0,otherVs.length-1); //cut off last comma
			otherSs = otherSs.substring(0,otherSs.length-1); //cut off last comma
			otherDs = otherDs.substring(0,otherDs.length-1); //cut off last comma
			otherTs = otherTs.substring(0,otherTs.length-1); //cut off last comma
			
			//alert("Updating exec...\n\nChair: "+chairName+" ("+chairID+")\nVice Chair: "+vicechairName+" ("+vicechairID+")\nScheduler: "+schedulerName+" ("+schedulerID+")\nDisciplinarian: "+disciplinarianName+" ("+disciplinarianID+")\nTech Chair: "+techchairName+" ("+techchairID+")\n\nOthers: "+otherIDs)
			//alert("Others' privileges:\n\nNames: "+otherPositions+"\nChair: "+otherCs+"\nVice Chair: "+otherVs+"\nScheduler: "+otherSs+"\nDisciplinarian: "+otherDs+"\nTech Chair: "+otherTs); //for debugging
			$.post("functions/updateexec.php",{
				chair:chairID,
				vicechair:vicechairID,
				scheduler:schedulerID,
				disciplinarian:disciplinarianID,
				techchair:techchairID,
				others:otherIDs,
				otherPositions:otherPositions,
				otherCs:otherCs,
				otherVs:otherVs,
				otherSs:otherSs,
				otherDs:otherDs,
				otherTs:otherTs
			}, function(data) {
				data = JSON.parse(data);
				if (data.error!='') {
					alert(data.error);
				}
				$('#execSubmitButton').html('Submit')
				$('#execSubmitButton').prop('disabled',false);
			});
		}
	</script>
</html>
