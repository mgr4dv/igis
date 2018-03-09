<? date_default_timezone_set('America/New_York');
require_once("authenticate.php");
$permission_level = 1;
include("permission.php");


$beginningOfTourRecords = 2007; //that's the earliest fall semester on record in the IGIS database
$currentYear = date('Y');
if (date('n')<6) {
	$yearToBeginChart = $currentYear-1;
} else {
	$yearToBeginChart = $currentYear;
}
$tourSlotsYearsOptions = '';
for ($i=$yearToBeginChart; $i>=$beginningOfTourRecords; $i--) {
	$tourSlotsYearsOptions = $tourSlotsYearsOptions."<option value=".$i.">".$i." - ".($i+1)."</option>";
}
?>
<!DOCTYPE html>

<html lang="en">

<!-- Header information for webpage (reused except for title) -->
	<head>
		<?
		include_once("includes/head.php");
		?>
		<script src="js/Chart.js"></script>
	</head>

<!-- Body of webpage (not reused, but reusable elements inside) -->
	<body>
		<?include_once("includes/nav.php")?>
		<?include_once("includes/footer.php")?>


		<div class="container">
			<h1>Tour Statistics</h1>
			<div class="well">
				<div class="row">
					<div class="col-md-8">
						<h3 style="display:inline">Weekly tour breakdown: </h3>
						<select id="tourSlotsYearSelect" class="form-control" style="display:inline; width:140px; cursor:pointer; margin-top:5px">
							<?echo $tourSlotsYearsOptions?>
						</select>
						<h4 style="display:inline; float:right"><span style="color:#FF0000">Admissions</span> vs. <span style="color:#0000FF">Historical</span></h4>
						<div style="width:100%; height: 400px">
						<canvas id="tourSlotsChart" style="width:100%; height:100%"></canvas>
						</div>
					</div>
				</div>
			</div>
		</div>


	</body>

	<script>

		var ctx = $("#tourSlotsChart").get(0).getContext("2d");
		var myLineChart = new Chart(ctx).Line({}, {});

		$(function () {
			Chart.defaults.global.animation = false;
			Chart.defaults.global.responsive = true; //set to be responsive so when bootstrap's column width changes, this will change as well
			Chart.defaults.global.maintainAspectRatio = false; //don't bother trying to maintain aspect ratio; Bootstrap will do the heavy lifting
			Chart.defaults.global.multiTooltipTemplate = "<%= value %> <%= datasetLabel %>"; //have the template include the name of the category

			updateTourSlotsChart(<?echo $yearToBeginChart?>);

			//Define event handlers:
			$('#tourSlotsYearSelect').on('change', function() {
				updateTourSlotsChart($(this).val());
			});
		});

		function updateTourSlotsChart(fallSemYear) {

			var yearTourTable;
			var yearListOfWeeks;
			$.post('functions/gettourstats.php',{
				fallSemYear:fallSemYear
			}, function(returnData){
				returnData = $.parseJSON(returnData);
				if (returnData.alert!='') alert('ALERT FROM SERVER: '+returnData.alert); //for debugging

				yearListOfWeeks = returnData.adacemicYearListOfWeeks;
				//Set up data with zeros everywhere:
				admissionsCounts = [];
				historicalCounts = [];
				for (i=0; i<yearListOfWeeks.length; i++) {
					admissionsCounts[i]=0;
					historicalCounts[i]=0;
				}
				//Fill in data:
				yearTourTable = returnData.academicYearTours;
				var tour;
				for (i=0; i<yearTourTable.length; i++) {
					tour = yearTourTable[i];
					weekNum = yearListOfWeeks.indexOf(tour.lastMonday);
					if (tour.his_req=='yes') {
						historicalCounts[weekNum] += parseInt(tour.guides_needed);
					}
					if (tour.adm_req=='yes') {
						admissionsCounts[weekNum] += parseInt(tour.guides_needed);
					}
				}

				//Plot data:
				var data = {
					labels: yearListOfWeeks,
					datasets: [
						{
							label: "Admissions",
							fillColor: "rgba(200,0,0,0.2)",
							strokeColor: "rgba(255,0,0,1)",
							pointColor: "rgba(255,0,0,1)",
							pointStrokeColor: "#fff",
							pointHighlightFill: "#fff",
							pointHighlightStroke: "rgba(255,0,0,1)",
							data: admissionsCounts
						},
						{
							label: "Historical",
							fillColor: "rgba(0,0,200,0.2)",
							strokeColor: "rgba(0,0,255,1)",
							pointColor: "rgba(0,0,255,1)",
							pointStrokeColor: "#fff",
							pointHighlightFill: "#fff",
							pointHighlightStroke: "rgba(0,0,255,1)",
							data: historicalCounts
						}
					]
				};
				try {
					myLineChart.destroy();
				} catch (ex) {
				}
				myLineChart = new Chart(ctx).Line(data, {});
			});
		}


	</script>
</html>
