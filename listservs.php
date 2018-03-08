<? date_default_timezone_set('America/New_York');
require_once("authenticate.php");

$permssion_level = 1;
include("permission.php");

$statuses=mysqli_query($link,"SELECT DISTINCT status FROM guides ORDER BY status ASC"); 
$statusOptions = "\n<option value=\"0\">(all)</option>";
while ($status=mysqli_fetch_array($statuses)) {
	if ($status['status']!=''){ //remove blanks
		$statusOptions = $statusOptions."\n<option value=\"".$status['status']."\">";
		$statusOptions = $statusOptions.$status['status'];
		$statusOptions = $statusOptions."</option>";
	}
}

$years=mysqli_query($link,"SELECT DISTINCT year FROM guides ORDER BY year DESC"); 
$yearOptions = "\n<option value=\"0\">(all)</option>";
while ($year=mysqli_fetch_array($years)) {
	if ($year['year']!='' && $year['year']!=0) { //remove blanks
		$yearOptions = $yearOptions."\n<option value=\"".$year['year']."\">";
		$yearOptions = $yearOptions.$year['year'];
		$yearOptions = $yearOptions."</option>";
	}
}

$schools=mysqli_query($link,"SELECT DISTINCT school FROM guides ORDER BY school ASC"); 
$schoolOptions = "\n<option value=\"0\">(all)</option>";
while ($school=mysqli_fetch_array($schools)) {
	if ($school['school']!=''){ //remove blanks
		$schoolOptions = $schoolOptions."\n<option value=\"".$school['school']."\">";
		$schoolOptions = $schoolOptions.$school['school'];
		$schoolOptions = $schoolOptions."</option>";
	}
}

$probieClasses=mysqli_query($link,"SELECT DISTINCT probie_class, date FROM guides
									INNER JOIN probieclass ON guides.probie_class=probieclass.chair
									ORDER BY date DESC"); 
$probieClassOptions = "\n<option value=\"0\">(all)</option>";
while ($probieClass=mysqli_fetch_array($probieClasses)) {
	if ($probieClass['probie_class']!=''){ //remove blanks
		if (date('n',strtotime($probieClass['date']))<8) {
			$season='Spring';
		} else {
			$season='Fall';
		}
		$year = date('Y',strtotime($probieClass['date']));
		$probieClassOptions = $probieClassOptions."\n<option value=\"".$probieClass['probie_class']."\">";
		$probieClassOptions = $probieClassOptions.$probieClass['probie_class'].' ('.$season.' '.$year.')';
		$probieClassOptions = $probieClassOptions."</option>";
	}
}
 
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
		<?include_once("includes/nav.php")?>
		<?include_once("includes/footer.php")?>

		
		<div class="container">
			<h1>Email Lists</h1>
			<div class="well">
				<p>
					<form id="listOptions">
						Status: <select id="statusSelect" name="status" form="listOptions" class="form-control" style="width:100px; display:inline; cursor:pointer; margin-right:10px"><?echo $statusOptions?></select>
						YOG: <select id="yearSelect" name="year" form="listOptions" class="form-control" style="width:100px; display:inline; cursor:pointer; margin-right:10px"><?echo $yearOptions?></select>
						School: <select id="schoolSelect" name="school" form="listOptions" class="form-control" style="width:100px; display:inline; cursor:pointer; margin-right:10px"><?echo $schoolOptions?></select>
						Probie Class: <select id="probieClassSelect" name="probieClass" form="listOptions" class="form-control" style="width:180px; display:inline; cursor:pointer; margin-right:10px"><?echo $probieClassOptions?></select>
						Exec: <input type="checkbox" name="execSelect" value="exec">
						<input type="submit" class="btn btn-primary" value="Display Email List">
						<button type="button" class="btn btn-default" onclick="resetDropdowns()">Reset</button>
					</form>
				</p>
				
				<div class="row">
					<div class="col-md-2"></div>
					<div class="col-md-8">
						<div class="panel panel-primary">
							<div class="panel-heading" id="resultsDescription">
								[none]
							</div>
						</div>
						<div class="panel panel-info">
							<div class="panel-heading">
								General email-formatted results:
								<button class="btn btn-default btn-sm" onclick="SelectText('resultsList')" style="float:right">Select results</button>
							</div>
							<div class="panel-body">
								<p id="resultsList"></p>
							</div>
						</div>
						<div class="panel panel-info">
							<div class="panel-heading">
								Sympa-formatted results:
								<button class="btn btn-default btn-sm" onclick="SelectText('resultsRows')"  style="float:right">Select results</button>
							</div>
							<div class="panel-body">
								<table class="table">
									<thead>
										<tr>
											<th>Email address:</th>
											<th>Name:</th>
										</tr>
									</thead>
									<tbody id="resultsRows">
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>


	</body>
	
	<script>
		$(function () {
			$("#listOptions").submit(function() { // intercepts the submit event
				//alert('Submitting! ' + $("#listOptions").serialize()) //for debugging
				$('#resultsRows').html('Loading...');
				$('#resultsDescription').html('...');
				$.post("functions/getemaillist.php",
					$("#listOptions").serialize(),
					function(data) {
						//alert(data); //for debugging
						data = $.parseJSON(data);
						if (data.error!='') {
							alert(data.error); //Show popup with info from the server. For debugging.
						}
						$('#resultsRows').html(data.resultsRows);
						$('#resultsList').html(data.resultsList);
						$('#resultsDescription').html(data.resultsDescription);
					});
				event.preventDefault(); // avoid to execute the actual submit of the form
			});
		});
		
		function resetDropdowns() {
			$('#statusSelect').val(0);
			$('#yearSelect').val(0);
			$('#schoolSelect').val(0);
			$('#probieClassSelect').val(0);
			$('#execSelect').prop('checked',false);
			$('#resultsDescription').html('[none]');
			$('#resultsRows').html('');
			$('#resultsList').html('');
		}
		
		//This function copied directly from StackOverflow: http://stackoverflow.com/questions/985272/selecting-text-in-an-element-akin-to-highlighting-with-your-mouse
		function SelectText(element) {
			var doc = document
				, text = doc.getElementById(element)
				, range, selection
			;    
			if (doc.body.createTextRange) {
				range = document.body.createTextRange();
				range.moveToElementText(text);
				range.select();
			} else if (window.getSelection) {
				selection = window.getSelection();        
				range = document.createRange();
				range.selectNodeContents(text);
				selection.removeAllRanges();
				selection.addRange(range);
			}
		}
	</script>
</html>
