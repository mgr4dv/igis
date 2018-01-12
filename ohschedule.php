<? date_default_timezone_set('America/New_York');
require_once("authenticate.php");
?>

<?
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

			.lastentry {
				border-right: 1px solid #ddd;
			}

			.border_top {
				border-top: 2px solid #000	;
			}

			.responsive-iframe-container {
				position: relative;
				padding-bottom: 56.25%;
				padding-top: 30px;
				height: 0;
				overflow: hidden;
			}

			.responsive-iframe-container iframe,
			.responsive-iframe-container object,
			.responsive-iframe-container embed {
				position: absolute;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
			}
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
		<!--datetimepicker; for the time picker in the new/edit tour modal-->
		<link type="text/css" href="css/bootstrap-datetimepicker.css" rel="stylesheet">
		<script type="text/javascript" src="js/moment.js"></script>
		<script type="text/javascript" src="js/bootstrap-datetimepicker.js"></script>
	</head>

<!-- Body of webpage (not reused, but reusable elements inside) -->
	<body style="padding-top:40px; padding-bottom: 60px">
		<?include_once("includes/nav.php")?>
		<?include_once("includes/footer.php")?>


		<div class="container">
			<h1>Office Hour Schedule <button class="btn btn-danger" data-toggle="modal" data-target="#ohArchiveModal">Archive</button></h1>
			<div class="row">
			<div class="col-md-5"></div>
							<div class="col-md-2"></div>
			<div class="col-md-5" style="vertical-align:bottom; text-align:right">
			</div>
			</div>
			<div class="well" id="target">
				<div class="row">
					<div class="col-md-2">
					<div class="panel pan	el-warning">
					<div class="panel panel-heading">
						<h4>Monday</h4>
					</div>
					<table class=table>
						<thead>
							<tr>
								<td>8:50</td>
							</tr>
						</thead>
						<tbody>

						</tbody>
					</table>
					</div>
				</div>
				<div class="col-md-2">
				<div class="panel panel-warning">
				<div class="panel panel-heading">
					<h4>Tuesday</h4>
				</div>
				<table class=table>
					<thead>
						<tr>
							<td>8:50</td>
						</tr>
					</thead>
						<tbody>

						</tbody>
						<thead>
							<tr>
								<td>8:50</td>
							</tr>
						</thead>
							<tbody>

							</tbody>
					</table>
					</div>
				</div>
				<div class="col-md-2">
				<div class="panel panel-warning">
				<div class="panel panel-heading">
					<h4>Wednesday</h4>
				</div>
				<table class=table>
					<thead>
						<tr>
							<td>8:50</td>
						</tr>
					</thead>
						<tbody>

						</tbody>
					</table>
					</div>
				</div>
				<div class="col-md-2">
				<div class="panel panel-warning">
				<div class="panel panel-heading">
					<h4>Thursday</h4>
				</div>
				<table class=table>
					<thead>
						<tr>
							<td>8:50</td>
						</tr>
					</thead>
						<tbody>

						</tbody>
					</table>
					</div>
				</div>
				<div class="col-md-2">
				<div class="panel panel-warning">
				<div class="panel panel-heading">
					<h4>Friday</h4>
				</div>
				<table class=table>
					<thead>
						<tr>
							<td>8:50</td>
						</tr>
					</thead>
						<tbody>

						</tbody>
					</table>
					</div>
				</div>
				</div>


					</div>
				</div>
				</div>
			</div>
		</div>

		<div class="modal fade bd-example-modal-sm" id="ohAddModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-sm">
				<div class="modal-content modal-sm">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
						<h4 class="modal-title" id="myModalLabel">Add Office Hour</h4>
					</div>
					<div class="modal-body" style="text-align:center">
						<div id='Time' class='input-group date'>
						<b>Time:</b><input  id='TimeBox' type='text' class="form-control" data-provide="time" style="text-align:left;"/>
						</div>
						<div id='Name' class='input-group date'>
						<br>
						<b>Name:</b>
						<div id="replaceGuideBox">
						<input id="replaceGuideBoxInput" class="typeahead form-control" type="text" placeholder="Guide's name" autocomplete="on">
					</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-primary" onclick="create_oh()">Add</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>
			
			
			
			
		<div class="modal fade bd-example-modal-sm" id="ohArchiveModal" tabindex="-1" role="dialog" aria-labelledby="archiveLabel" aria-hidden="true">
			<div class="modal-dialog modal-sm">
				<div class="modal-content modal-sm">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
						<h4 class="modal-title" id="archiveLabel">Archive OH Log</h4>
					</div>
					<div class="modal-body" style="text-align:center">
						<div id='Name' class='input-group date'>
						WARNING: This will delete the OH schedule and permenantly archive the OH Log under the name provided.
						</br>
						</br>
						<b>Archive Name</b><input id='ArchiveNameBox' type='text' class="form-control" data-provide="text" style="text-align:left;"/>
						</div>
						<br>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-danger" onclick="archiveOH()" data-dismiss="modal">Archive</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>


	</body>

	<script>

	var submitDay = 0;

	$(function() {
		$('#TimeBox').datetimepicker({
			pickDate: false
		});

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

		refresh();
	});

	function refresh() {

		$.get("functions/printohschedule.php",{}, function(data) {
				$('#target').html(data);
			});
	}

	function delete_oh(oh_id){
		$.post("functions/deleteoh.php",{
			oh_id:oh_id
		},function(data){
			refresh();
		});
		refresh();
	}

	function create_oh(){
		var guideList = <?echo $guides?>;
		var guideIDList = <?echo $guideIDs?>;
		var newGuideName = $('#replaceGuideBoxInput').val();
		$('#replaceGuideBoxInput').val('');
		var newGuideIndex = guideList.indexOf(newGuideName);
		var newGuideID = guideIDList[newGuideIndex];
		var timeProcess = $('#TimeBox').val();
		//alert('Submitting '+newGuideName+' ('+newGuideID+')!');
		if (newGuideIndex==-1) {
			alert('Error: guide "'+newGuideName+'" not found.');
		} else {
			$.post("functions/createoh.php",{
				guide_id:newGuideID,
				day:submitDay,
				time:timeProcess
			},function(data){
				refresh();
			});
		refresh();
		}
	}

	function setDay(day){
		submitDay = day;
	}

	function archiveOH(){
		var archiveName = $('#ArchiveNameBox').val();
		$.post("functions/archiveoh.php",{
			archiveName:archiveName
		},function(data){
			refresh();
		});
		refresh();
	}

	</script>
</html>
