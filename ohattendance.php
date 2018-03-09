<? date_default_timezone_set('America/New_York');
require_once("authenticate.php");
$permission_level = 3;
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
			<h1>Office Hour Attendance</h1>
			<div style="text-align:center">
				<button id="unhandled" class="btn btn-primary active" style="margin-bottom:5px" onclick="setUnhandled(0)">Unhandled</button>
				<button	id="future"  class="btn btn-primary" style="margin-bottom:5px" onclick="setFuture(0)">Future</button>
				<button id="lastMonth" class="btn btn-primary" style="margin-bottom:5px" onclick="setLastMonth(0)">Last Month</button>
				<button id="lastAll" class="btn btn-primary" style="margin-bottom:5px" onclick="setAll(0)">All</button>
				<br>
				<button class="btn btn-primary" style="margin-bottom:5px float:right" data-toggle="modal" data-target="#ohAddModal">Add new OH</button>
				<br>
			</div>
			<div class="well" id="oh_log" >

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

	</body>

	<script>

	$(function() {
		$('#TimeBox').datetimepicker({
			pickDate: true
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
		$('#oh_log').html("Loading...");
		$.post("functions/getohlog.php",{
			type:"unhandled"
		},function(data){
			$('#oh_log').html(data);
			$('#oh_table tr').click(function(event) {
				if (event.target.type !== 'checkbox' && event.target.type !== 'button') {
					$(':checkbox', this).trigger('click');
				}
			});
		});
	}

	getOhLog("unhandled");

	function setUnhandled(){
		document.getElementById("unhandled").className = "btn btn-primary active";
		document.getElementById("future").className = "btn btn-primary";
		document.getElementById("lastMonth").className = "btn btn-primary";
		document.getElementById("lastAll").className = "btn btn-primary";
		getOhLog("unhandled");
	}

	function setFuture(){
		document.getElementById("unhandled").className = "btn btn-primary";
		document.getElementById("future").className = "btn btn-primary active";
		document.getElementById("lastMonth").className = "btn btn-primary";
		document.getElementById("lastAll").className = "btn btn-primary";
		getOhLog("future");
	}

	function setLastMonth(){
		document.getElementById("unhandled").className = "btn btn-primary ";
		document.getElementById("future").className = "btn btn-primary";
		document.getElementById("lastMonth").className = "btn btn-primary active";
		document.getElementById("lastAll").className = "btn btn-primary";
		getOhLog("lastMonth");
	}

	function setAll(){
		document.getElementById("unhandled").className = "btn btn-primary ";
		document.getElementById("future").className = "btn btn-primary";
		document.getElementById("lastMonth").className = "btn btn-primary";
		document.getElementById("lastAll").className = "btn btn-primary active";
		getOhLog("lastAll");
	}

	function getOhLog(type){
		if (type === "current"){
			type = document.getElementsByClassName("btn btn-primary active")[0].getAttribute('id');
		}
		$('#oh_log').html("Loading...");
		$.post("functions/getohlog.php",{
			type:type
		},function(data){
			$('#oh_log').html(data);
			$('#oh_table tr').click(function(event) {
				if (event.target.type !== 'checkbox' && event.target.type !== 'button') {
					$(':checkbox', this).trigger('click');
				}
			});
		});
	}

	function deleteoh(id){
			$.post("functions/updateohlog.php",{
				id:id,
				status:2
			},function(data){
				getOhLog("current");
			});
		}

	function handle(id){
			if (id === -1){
				multideleteoh();
				return;
			}

			$.post("functions/updateohlog.php",{
				id:id,
				status:1
			},function(data){
				getOhLog("current");
			});
		}

	function multideleteoh(){
			var ohs = [];
			$(':checkbox:checked').each( function() {
				ohs[ohs.length] = $(this).val();
			});

			ohs.forEach(function(entry){
				$.post("functions/updateohlog.php",{
					id:entry,
					status:1
				},function(data){});
		});
			getOhLog("current");
		}

	function unhandle(id){
			$.post("functions/updateohlog.php",{
				id:id,
				status:0
			},function(data){
				getOhLog("current");
			});
		}


		function cover(id){
			if( id === -1){
				multicoveroh();
				return;
			}
				$.post("functions/updateohlog.php",{
					id:id,
					status:1
				},function(data){
					getOhLog("current");
				});

				$.post("functions/changepoints.php",{
					oh_id:id,
					pointVal:-1,
					deletePoint:0,
					comment:"Covered OH"
				},function(data){

				});
			}

		function multicoveroh(){
				var ohs = [];
				$(':checkbox:checked').each( function() {
					ohs[ohs.length] = $(this).val();
				});

				ohs.forEach(function(entry){
					$.post("functions/updateohlog.php",{
						id:entry,
						status:1
					},function(data){});

					$.post("functions/changepoints.php",{
						oh_id:entry,
						pointVal:-1,
						deletePoint:0,
						comment:"Covered OH"
					},function(data){});

			});
				getOhLog("current");
			}

		function miss(id){
			if( id === -1 ){
				multimissoh();
				return;
			}

				$.post("functions/updateohlog.php",{
					id:id,
					status:1
				},function(data){
					getOhLog("current");
				});

				$.post("functions/changepoints.php",{
					oh_id:id,
					pointVal:1,
					deletePoint:0,
					comment:"Missed OH"
				},function(data){

				});
			}

			function multimissoh(){
					var ohs = [];
					$(':checkbox:checked').each( function() {
						ohs[ohs.length] = $(this).val();
					});

					ohs.forEach(function(entry){
						$.post("functions/updateohlog.php",{
							id:entry,
							status:1
						},function(data){});

						$.post("functions/changepoints.php",{
							oh_id:entry,
							pointVal:1,
							deletePoint:0,
							comment:"Missed OH"
						},function(data){});

				});
					getOhLog("current");
				}


			function late(id){
				if( id === -1 ){
					multilateoh();
					return;
				}

					$.post("functions/updateohlog.php",{
						id:id,
						status:1
					},function(data){
						getOhLog("current");
					});

					$.post("functions/changepoints.php",{
						oh_id:id,
						pointVal:0.5,
						deletePoint:0,
						comment:"Late to OH"
					},function(data){

					});
				}

				function multilateoh(){
						var ohs = [];
						$(':checkbox:checked').each( function() {
							ohs[ohs.length] = $(this).val();
						});

						ohs.forEach(function(entry){
							$.post("functions/updateohlog.php",{
								id:entry,
								status:1
							},function(data){});

							$.post("functions/changepoints.php",{
								oh_id:entry,
								pointVal:1,
								deletePoint:0,
								comment:"Late to OH"
							},function(data){});

					});
						getOhLog("current");
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
					$.post("functions/createohlog.php",{
						guide_id:newGuideID,
						time:timeProcess
					},function(data){
						refresh();
					});
				refresh();
				}
			}


	</script>
</html>
