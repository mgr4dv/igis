<? date_default_timezone_set('America/New_York');
require_once("authenticate.php"); 
$permssion_level = 3;
include("permission.php");
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
			<h1>Define Point Values</h1>
			<div class="row">
				<div class="col-md-8"></div>
				<div class="col-md-4">
					<button style="float:right; margin-right:10px; margin-bottom:5px" id="newPointButton" class="btn btn-primary" type="button" data-toggle="modal" data-target="#newPointModal"><span class="glyphicon glyphicon-plus"></span> Add New Point Definition</button>
				</div>
			</div>
			<div class="well">
				<div class="row">
					<div class="col-md-2"></div>
					<div class="col-md-4" id="posPointCol">
						<div class="panel panel-danger">
							<div class="panel-heading">
								<h3 class="panel-title">Disciplinary points</h3>
							</div>
							<div class="panel-body" id="posPoints">
								Loading...
							</div>
						</div>
					</div>
					<div class="col-md-4" id="negPointCol">
						<div class="panel panel-success">
							<div class="panel-heading">
								<h3 class="panel-title">Ways to work off points</h3>
							</div>
							<div class="panel-body" id="negPoints">
								Loading...
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		
		<div class="modal" id="newPointModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header" style="text-align:center">
						<h3 class="modal-title" style="display:inline"><strong>New Point Definition</strong></h3>
					</div>
					<div class="modal-body">
						<table class="table">
							<tr>
								<td style="text-align:right; vertical-align:top"><strong>Description:</strong></td>
								<td><textarea id="newPointDesc" class="form-control" rows="3" style="resize: none"></textarea></td>
							</tr>
							<tr>
								<td style="text-align:right; vertical-align:middle"><strong>Value:</strong></td>
								<td style="vertical-align:middle">
									<input id="newPointVal" type="text" class="form-control" value="" style="width:50px; display:inline"> points
								</td>
							</tr>
						</table>
						<p style="text-align:right">
							<button id="newPointCancelButton" type="button" class="btn btn-default" data-dismiss="modal" onclick="clearNewPoint();">Cancel</button>
							<button id="newPointSubmitButton" type="button" class="btn btn-primary" onclick="submitNewPoint();">Create</button>
						</p>
					</div>
				</div>
			</div>
		</div>
		
		<div class="modal" id="editPointModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header" style="text-align:center">
						<h3 class="modal-title" style="display:inline"><strong>Edit Point Definition</strong></h3> <span style="float:right; color:#999999; font-style:italic" id="editPointID"></span>
					</div>
					<div class="modal-body">
						<table class="table">
							<tr>
								<td style="text-align:right; vertical-align:top"><strong>Description:</strong></td>
								<td><textarea id="editPointDesc" class="form-control" rows="3" style="resize: none"></textarea></td>
							</tr>
							<tr>
								<td style="text-align:right; vertical-align:middle"><strong>Value:</strong></td>
								<td style="vertical-align:middle">
									<input id="editPointVal" type="text" class="form-control" value="" style="width:50px; display:inline"> points
								</td>
							</tr>
						</table>
						<p style="text-align:right">
							<button id="editPointCancelButton" type="button" class="btn btn-default" data-dismiss="modal" onclick="clearEditPoint();">Cancel</button>
							<button id="editPointSubmitButton" type="button" class="btn btn-primary" onclick="submitEditPoint();">Submit</button>
						</p>
					</div>
				</div>
			</div>
		</div>


	</body>
	
	<script>
		
		$(function() {
			refresh();
		});
		
		function refresh() {
			$('#posPoints').html('Loading...');
			$('#negPoints').html('Loading...');
			$.get("functions/printpointtypes.php",function(data) {
				data = JSON.parse(data);
				if (data.error!='') {
					alert(data.error);
				}
				$('#posPoints').html(data.posPoints);
				$('#negPoints').html(data.negPoints);
			});
		}
		
		function editPointPopup(pointID) {
			//alert('popup for point #'+pointID); //for debugging
			$.post("functions/getpointinfo.php",{
				pointID:pointID
			}, function (data) {
				data = JSON.parse(data);
				if (data.error!='') {
					alert(data.error);
				}
				$('#editPointID').html(pointID);
				$('#editPointDesc').val(data.desc);
				$('#editPointVal').val(data.val);
				$('#editPointModal').modal('show');
			});
		}
		
		function deletePoint(pointID) {
			//alert('deleting point #'+pointID); //for debugging
			$.post("functions/changepointtype.php",{
					newType:0,
					deleteType:1,
					typeID:pointID
				}, function(data) {
					data = JSON.parse(data);
					if (data.error!='') {
						alert(data.error);
					}
					refresh();
					$('#newPointModal').modal('hide');
				});
		}
		
		function submitNewPoint() {
			pointDesc = $('#newPointDesc').val();
			pointVal = Number($('#newPointVal').val());
			if (pointDesc=='') {
				alert('Error: You must specify a description.');
			} else if (isNaN(pointVal)) {
				alert('Error: Point value must be a number.');
			} else if (pointVal==0) {
				alert('Error: You must specify a positive or negative point value.');
			} else {
				//alert('Submitting new point...\n\nDescription: '+pointDesc+'\n\nValue: '+pointVal) //for debugging
				$.post("functions/changepointtype.php",{
					newType:1,
					deleteType:0,
					desc:pointDesc,
					val:pointVal
				}, function(data) {
					data = JSON.parse(data);
					if (data.error!='') {
						alert(data.error);
					}
					refresh();
					clearNewPoint();
					$('#newPointModal').modal('hide');
				});
			}
		}
		function clearNewPoint() {
			$('#newPointDesc').val('');
			$('#newPointVal').val('');
		}
		
		function submitEditPoint() {
			pointID = $('#editPointID').html();
			pointDesc = $('#editPointDesc').val();
			pointVal = Number($('#editPointVal').val());
			if (pointDesc=='') {
				alert('Error: You must specify a description.');
			} else if (isNaN(pointVal)) {
				alert('Error: Point value must be a number.');
			} else if (pointVal==0) {
				alert('Error: You must specify a positive or negative point value.');
			} else {
				//alert('Submitting edited point...\n\nID:'+pointID+'\n\nDescription: '+pointDesc+'\n\nValue: '+pointVal) //for debugging
				$.post("functions/changepointtype.php",{
					newType:0,
					deleteType:0,
					typeID:pointID,
					desc:pointDesc,
					val:pointVal
				}, function(data) {
					data = JSON.parse(data);
					if (data.error!='') {
						alert(data.error);
					}
					refresh();
					clearEditPoint();
					$('#editPointModal').modal('hide');
				});
			}
		}
		function clearEditPoint() {
			$('#editPointDesc').val('');
			$('#editPointVal').val('');
		}
	</script>
</html>
