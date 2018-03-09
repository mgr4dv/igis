<? date_default_timezone_set('America/New_York');
require_once("authenticate.php"); 

$permission_level = 3;
include("permission.php");

$currentYear = date('Y');

$classes_query = mysqli_query($link,"SELECT * FROM probieclass ORDER BY date DESC");
$probieClassRows = "";

while ($class=mysqli_fetch_array($classes_query)) {
	$probieClassRows = $probieClassRows."
		<tr>
			<td>".$class['chair']."</td>
			<td>".$class['semester']."</td>
			<td><button class=\"btn btn-xs btn-danger\" onclick=\"deleteClass(".$class['probie_id'].")\">X</button></td>
		</tr>
	"; 
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
			<h1>Manage Probie Classes</h1>
			<div class="well" id="test">
				<div class="row">
					<div class="col-md-3"></div>
					<div class="col-md-6">
						<div class="panel panel-success">
							<div class="panel-heading">
								Add new probie class
							</div>
							<div class="panel-body">
								<table class="table">
									<tr>
										<td>Name:</td>
										<td><input id="newClassNameBox" type="text" class="form-control" value=""></td>
									</tr>
									<tr>
										<td>Semester:</td>
										<td>
											<select id="newClassSemesterBox" class="form-control" style="cursor:pointer; width:100px; display:inline">
												<option value="Fall">Fall</option>
												<option value="Spring">Spring</option>
											</select>
											<input id="newClassYearBox" type="text" class="form-control" value="<?echo $currentYear?>" placeholder="(year)" style="width:100px; display:inline">
										</td>
									</tr>
								</table>
								<p style="text-align:center">
									<button id="newClassSubmitButton" type="button" class="btn btn-primary" data-dismiss="modal" onclick="createClass()">Create</button>
								</p>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-3"></div>
					<div class="col-md-6">
						<div class="panel panel-info">
							<div class="panel-heading">
								Existing probie classes:
							</div>
							<div id="classList" class="panel-body">
								<table class="table">
									<thead>
										<th>Probie Class</th>
										<th>Semester</th>
										<th></th>
									</thead>
									<tbody>
										<?echo $probieClassRows?>
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
		function createClass() {
			var name = $('#newClassNameBox').val();
			var sem = $('#newClassSemesterBox').val();
			var year = $('#newClassYearBox').val();
			var dateStr = '';
			switch(sem) {
				case 'Fall':
					dateStr = year+'-08-01';
					break;
				case 'Spring':
					dateStr = year+'-01-01';
					break;
				default:
					dateStr = year+'-08-01';
					break;
			}
			
			if (name=='') {
				alert('Error: You must specify a name for the probie class.');
			} else if (year=='') {
				alert('Error: You must specify a year for the probie class.');
			} else if (isNaN(Number(year))) {
				alert('Error: The specified year must be a number.');
			} else if (year.length!=2 && year.length!=4) {
				alert('Error: The specified year must be 2 or 4 digits.');
			} else {
				if(year.length==2) {
					alert('Warning: Converting 2-digit year ('+year+') into 4-digit year (20'+year+').')
					year = '20'+year;
					dateStr = '20'+dateStr
				}
				//alert('Adding the '+name+' probie class for the '+sem+' '+year+' semester (start date = '+dateStr+')'); //for debugging
				$('#classList').html('Loading...');
				$.post("functions/changeprobieclass.php",{
					addClass:1,
					deleteClass:0,
					className:name,
					classDate:dateStr,
					classSem:sem+' '+year
				}, function(data) {
					data = JSON.parse(data);
					if(data.error!='') {
						alert(data.error);
					}
					location.reload();
				});
			}
			
		}
		
		function deleteClass(classID) {
			//alert('Deleting class #'+classID); //for debugging
			$('#classList').html('Loading...');
			$.post("functions/changeprobieclass.php",{
					addClass:0,
					deleteClass:1,
					classID:classID
				}, function(data) {
					data = JSON.parse(data);
					if(data.error!='') {
						alert(data.error);
					}
					location.reload();
				});
		}
		
	</script>
</html>
