<? date_default_timezone_set('America/New_York');
require_once("authenticate.php"); 
$permssion_level = 2;
include("permission.php");

$tourTypes=mysqli_query($link,"select * from tours_types WHERE offered='yes' ORDER by name ASC"); 
$tourTypeOptions = "";
while ($type=mysqli_fetch_array($tourTypes)) {
	$tourTypeOptions = $tourTypeOptions."\n<option value=\"".$type['abbrev']."\">";
	$tourTypeOptions = $tourTypeOptions.$type['name'];
	$tourTypeOptions = $tourTypeOptions."</option>";
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
	<body>
		<?include_once("includes/nav.php")?>
		<?include_once("includes/footer.php")?>

		
		<div class="container">
			<h1>Tour Request</h1>
			<div class="well">
				<div class="row">
					<div class="col-md-2"></div>
					<div class="col-md-8">
						<form id="requestform">
							<input type="hidden" name="requested_by" value="<?echo $_SESSION['name']?>"><br>
							<table class="table">
								<tr>
									<th colspan=2 style="text-align:center; font-style:italic">Contact information</th>
								</tr>
								<tr>
									<td>Name:</td>
									<td><input type="text" name="name" class="form-control" placeholder="(required)" required></td>
								</tr>
								<tr>
									<td>Email:</td>
									<td><input type="text" name="email" class="form-control" placeholder="(required)"></td>
								</tr>
								<tr>
									<td>Phone number:</td>
									<td><input type="text" name="phone" class="form-control" placeholder="(required)"></td>
								</tr>
								<tr>
									<td>Contact phone number for day of tour:</td>
									<td><input type="text" name="contact_phone" class="form-control" placeholder="(required if different from above)"></td>
								</tr>
								<tr>
									<td>Name of group or organization:</td>
									<td><input type="text" name="organization" class="form-control" placeholder="(if applicable)"></td>
								</tr>
								<tr>
									<th colspan=2 style="text-align:center; font-style:italic">Tour information</th>
								</tr>
								<tr>
									<td>Tour date:</td>
									<td><input type="text" name="date" class="form-control" placeholder="(required)" required></td>
								</tr>
								<tr>
									<td>Tour time:</td>
									<td><input type="text" name="time" class="form-control" placeholder="(required)" required></td>
								</tr>
								<tr>
									<td>Tour type:</td>
									<td><select name="type" form="requestform" class="form-control"><?echo $tourTypeOptions?></select></td>
								</tr>
								<tr>
									<td>Number of tourists:</td>
									<td><input type="text" name="num_tourists" class="form-control" placeholder="(approximate, but required)" required></td>
								</tr>
								<tr>
									<td>Tourists' grade level(s):</td>
									<td><input type="text" name="grade_level" class="form-control" placeholder="(only if in school)"></td>
								</tr>
								<tr>
									<td>Specific information requested:</td>
									<td><textarea name="requested_info" class="form-control" rows="3" style="resize: none" placeholder="(optional)"></textarea></td>
								</tr>
								<tr>
									<td>Any other notes:</td>
									<td><textarea name="notes" class="form-control" rows="3" style="resize: none" placeholder="(optional)"></textarea></td>
								</tr>
							</table>
							<p style="text-align:center"><input type="submit" class="btn btn-primary"></p>
						</form>
						<div id="successAlert" class="alert alert-success alert-dismissible" role="alert" hidden>
							<button type="button" class="close" onclick="$('#successAlert').hide()"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
							<b>Successfully submitted tour request.</b>
						</div>
					</div>
				</div>
			</div>
		</div>


	</body>
	
	<script>
		
		$(function () {
			$("#requestform").submit(function() { // intercepts the submit event
				$.post("functions/submittourrequest.php",
					$("#requestform").serialize(),
					function(data) {
						//alert(data); //for debugging
						data = $.parseJSON(data);
						if (data.error!='') {
							alert(data.error); //Show popup with info from the server. For debugging.
						}
						$('#successAlert').show();
					});
				event.preventDefault(); // avoid to execute the actual submit of the form
			});
		});
		
		
	</script>
</html>
