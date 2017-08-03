<? date_default_timezone_set('America/New_York');
require_once("authenticate.php"); ?>

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
			<h1>Define Tour Types</h1>
			<div class="row">
				<div class="col-md-8"></div>
				<div class="col-md-4">
					<button style="float:right; margin-right:10px; margin-bottom:5px" id="newTourTypeButton" class="btn btn-primary" type="button" data-toggle="modal" data-target="#newTypeModal" onclick="clearNewType()"><span class="glyphicon glyphicon-plus"></span> Add New Tour Type</button>
				</div>
			</div>
			<div class="well" style="background-color:#BB9999">
				<div class="row">
					<div class="col-md-3"></div>
					<div class="col-md-6" id="tourTypes">
					</div>
				</div>
			</div>
		</div>
		
		
		<div class="modal" id="newTypeModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header" style="text-align:center">
						<h3 class="modal-title" style="display:inline"><strong>New Tour Type</strong></h3>
					</div>
					<div class="modal-body">
						<table class="table">
							<tr>
								<td style="text-align:right; vertical-align:middle"><strong>Name:</strong></td>
								<td>
									<input id="newTypeName" class="form-control">
								</td>
							</tr>
							<tr>
								<td style="text-align:right; vertical-align:middle"><strong>Abbreviation:</strong></td>
								<td>
									<input id="newTypeAbbrev" class="form-control">
								</td>
							</tr>
							<tr>
								<td style="text-align:right; vertical-align:middle"><strong>Requirements:</strong></td>
								<td>
									<label class="checkbox">
										<input id="newTypeAdmissions" type="checkbox"> Admissions
									</label>
									<label class="checkbox">
										<input id="newTypeHistorical" type="checkbox"> Historical
									</label>
								</td>
							</tr>
							<tr>
								<td style="text-align:right; vertical-align:middle"><strong>Requestable:</strong></td>
								<td>
									<label class="checkbox">
										<input id="newTypeRequestable" type="checkbox">
									</label>
								</td>
							</tr>
							<tr>
								<td style="text-align:right; vertical-align:middle"><strong>Public Name:</strong></td>
								<td>
									<input id="newTypePublicName" class="form-control">
								</td>
							</tr>
							<tr>
								<td style="text-align:right; vertical-align:top"><strong>Description:</strong></td>
								<td><textarea id="newTypeDesc" class="form-control" rows="3" style="resize: none"></textarea></td>
							</tr>
						</table>
						<p style="text-align:right">
							<button id="newTypeCancelButton" type="button" class="btn btn-default" data-dismiss="modal" onclick="clearNewType();">Cancel</button>
							<button id="newTypeSubmitButton" type="button" class="btn btn-primary" data-dismiss="modal">Create New Tour Type</button>
						</p>
					</div>
				</div>
			</div>
		</div>

		<div class="modal" id="editTypeModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header" style="text-align:center">
						<h3 class="modal-title" style="display:inline"><strong>Edit Tour Type</strong></h3><h4 style="display:inline; float:right"><small><em><span id="editTypeID">[type_id]</span></em></small></h4>
					</div>
					<div class="modal-body">
						<table class="table">
							<tr>
								<td style="text-align:right; vertical-align:middle"><strong>Name:</strong></td>
								<td>
									<input id="editTypeName" class="form-control">
								</td>
							</tr>
							<tr>
								<td style="text-align:right; vertical-align:middle"><strong>Abbreviation:</strong></td>
								<td>
									<input id="editTypeAbbrev" class="form-control">
								</td>
							</tr>
							<tr>
								<td style="text-align:right; vertical-align:middle"><strong>Requirements:</strong></td>
								<td>
									<label class="checkbox">
										<input id="editTypeAdmissions" type="checkbox"> Admissions
									</label>
									<label class="checkbox">
										<input id="editTypeHistorical" type="checkbox"> Historical
									</label>
								</td>
							</tr>
							<tr>
								<td style="text-align:right; vertical-align:middle"><strong>Requestable:</strong></td>
								<td>
									<label class="checkbox">
										<input id="editTypeRequestable" type="checkbox">
									</label>
								</td>
							</tr>
							<tr>
								<td style="text-align:right; vertical-align:middle"><strong>Public Name:</strong></td>
								<td>
									<input id="editTypePublicName" class="form-control">
								</td>
							</tr>
							<tr>
								<td style="text-align:right; vertical-align:top"><strong>Description:</strong></td>
								<td><textarea id="editTypeDesc" class="form-control" rows="3" style="resize: none"></textarea></td>
							</tr>
						</table>
						<p style="text-align:right">
							<button id="editTypeCancelButton" type="button" class="btn btn-default" data-dismiss="modal" onclick="clearEditType();">Cancel</button>
							<button id="editTypeSubmitButton" type="button" class="btn btn-primary" data-dismiss="modal">Update Tour Type</button>
						</p>
					</div>
				</div>
			</div>
		</div>
		
		
	</body>
	
	<script>
		
		$(function () {
			refresh();
			
			$('#editTypeSubmitButton').click( function() {
				var typeID = $('#editTypeID').html();
				var name = $('#editTypeName').val();
				var abbrev = $('#editTypeAbbrev').val();
				var desc = $('#editTypeDesc').val();
				var historical = $('#editTypeHistorical').prop('checked');
				var admissions = $('#editTypeAdmissions').prop('checked');
				var requestable = $('#editTypeRequestable').prop('checked');
				var publicName = $('#editTypePublicName').val();
				editType(typeID, name, abbrev, desc, historical, admissions, requestable, publicName);
			});
			
			$('#newTypeSubmitButton').click( function() {
				var name = $('#newTypeName').val();
				var abbrev = $('#newTypeAbbrev').val();
				var desc = $('#newTypeDesc').val();
				var historical = $('#newTypeHistorical').prop('checked');
				var admissions = $('#newTypeAdmissions').prop('checked');
				var requestable = $('#newTypeRequestable').prop('checked');
				var publicName = $('#newTypePublicName').val();
				newType(name, abbrev, desc, historical, admissions, requestable, publicName);
			});
		});
		
		function refresh() {
			$.post("functions/printtourtypes.php",{
				}, function(data) {
					//alert(data); //for debugging
					$('#tourTypes').html(data);
				});
		}
		
		function editTypePopup(typeID) {
			$.post("functions/gettypeinfo.php",{
					typeID:typeID
				}, function(data) {
					data = $.parseJSON(data)
					var name = data.name;
					var abbrev = data.abbrev;
					var desc = data.description;
					var historical = data.his_req;
					var admissions = data.adm_req;
					var requestable = data.requestable;
					var publicName = data.public_name
					if (historical=='yes') {
						historical=true;
					} else {
						historical=false;
					}
					if (admissions=='yes') {
						admissions = true;
					} else {
						admissions = false;
					}
					if (requestable==1) {
						requestable = true;
					} else {
						requestable = false;
					}
					$('#editTypeID').html(typeID);
					$('#editTypeName').val(name);
					$('#editTypeAbbrev').val(abbrev);
					$('#editTypeAdmissions').prop('checked',admissions);
					$('#editTypeHistorical').prop('checked',historical);
					$('#editTypeRequestable').prop('checked',requestable);
					$('#editTypePublicName').val(publicName);
					$('#editTypeDesc').val(desc);
					
					$('#editTypeModal').modal('show');
					//alert(data); //Show popup with info from the server. For debugging.
				});
		}
		
		function newType(name, abbrev, desc, historical, admissions, requestable, publicName) {
			var hist_req
			var adm_req
			if (historical) {
				hist_req='yes';
			} else {
				hist_req='no';
			}
			if (admissions) {
				adm_req='yes';
			} else {
				adm_req='no';
			}
			if (requestable) {
				requestable=1;
			} else {
				requestable=0;
			}
			$.post("functions/changetype.php",{
					newType:1,
					deleteType:0,
					name:name,
					abbrev:abbrev,
					desc:desc,
					historical:hist_req,
					admissions:adm_req,
					requestable:requestable,
					publicName:publicName
				}, function(data) {
					if (data!='') {
						alert(data); //Show any errors that occur.
					}
					refresh(); //Then, refresh the tours
				});
		}
		
		function editType(typeID, name, abbrev, desc, historical, admissions, requestable, publicName) {
			var hist_req;
			var adm_req;
			var requestable;
			if (historical) {
				hist_req='yes';
			} else {
				hist_req='no';
			}
			if (admissions) {
				adm_req='yes';
			} else {
				adm_req='no';
			}
			if (requestable) {
				requestable=1;
			} else {
				requestable=0;
			}
			$.post("functions/changetype.php",{
					newType:0,
					deleteType:0,
					typeID:typeID,
					name:name,
					abbrev:abbrev,
					desc:desc,
					historical:hist_req,
					admissions:adm_req,
					requestable:requestable,
					publicName:publicName
				}, function(data) {
					//alert(data); //for debugging
					if (data!='') {
						alert(data); //Show any errors that occur.
					}
					refresh(); //Then, refresh the tours
				});
		}
		
		function deleteType(typeID) {
			$.post("functions/changetype.php",{
					newType:0,
					deleteType:1,
					typeID:typeID
				}, function(data) {
					//alert(data); //for debugging
					if (data!='') {
						alert(data); //Show any errors that occur.
					}
					refresh(); //Then, refresh the tours
				});
		}
		
		function clearNewType() {
			$('#newTypeName').val('');
			$('#newTypeAbbrev').val('');
			$('#newTypeAdmissions').prop('checked',false);
			$('#newTypeHistorical').prop('checked',false);
			$('#newTypeRequestable').prop('checked',false);
			$('#newTypePublicName').val('');
			$('#newTypeDesc').val('');
		}
		
		function clearEditType() {
			$('#editTypeName').val('');
			$('#editTypeAbbrev').val('');
			$('#editTypeAdmissions').prop('checked',false);
			$('#editTypeHistorical').prop('checked',false);
			$('#editTypeRequestable').prop('checked',false);
			$('#editTypePublicName').val('');
			$('#editTypeDesc').val('');
		}
	</script>
</html>
