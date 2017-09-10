<?
require_once('../authenticate.php');

if (isset($_POST['noEdit'])) $noEdit = $_POST['noEdit'];

include("link.php");

$types_query = mysqli_query($link,"SELECT * FROM tours_types WHERE offered='yes'");
$out = "";
while ($type=mysqli_fetch_array($types_query)) {
	if ($type['adm_req']=='yes' && $type['his_req']=='yes') {
		$requirements = "Can fulfill either admissions or history requirement.";
	} else if ($type['adm_req']=='yes' && $type['his_req']=='no') {
		$requirements = "Fulfills admissions requirement.";
	} else if ($type['adm_req']=='no' && $type['his_req']=='yes') {
		$requirements = "Fulfills history requirement.";
	} else if ($type['adm_req']=='no' && $type['his_req']=='no') {
		$requirements = "Fulfills no requirements.";
	} else {
		$requirements = "unknown requirement fulfillment; adm_req=".$type['adm_req'].", his_req=".$type['his_req'];
	}

	if ($type['requestable']==1) {
		$requestable = "<small style=\"float:right; color:#888888; font-style:italic\">Publicly Requestable as \"".$type['public_name']."\"</small>";
	} else {
		$requestable = "";
	}

	if ($noEdit) {
		$out = $out.
			'<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title"><strong>'.$type['name'].' ('.$type['abbrev'].')</strong></h3>
				</div>
				<div class="panel-body">
					<p><strong>'.$type['description'].'</strong></p>
					<p><em>('.$requirements.')</em></p>
				</div>
			</div>';

	} else {
		$out = $out.
			'<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title"><strong>'.$type['name'].' ('.$type['abbrev'].')'.$requestable.'</strong></h3>
				</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-md-8">
							<p><strong>'.$type['description'].'</strong></p>
							<p><em>('.$requirements.')</em></p>
						</div>
						<div class="col-md-4">
							<p style="text-align:center; float:right"><button type="button" class="btn btn-danger" onclick="deleteType('.$type['type_id'].')" data-loading-text="Loading..."><span class="glyphicon glyphicon-remove"></span> Delete</button></p>
							<p style="text-align:center; float:right"><button type="button" class="btn btn-primary" onclick="editTypePopup('.$type['type_id'].')" data-loading-text="Loading..."><span class="glyphicon glyphicon-pencil"></span> Edit...</button></p>
						</div>
					</div>
				</div>
			</div>';
	}
}

echo mysqli_error($link).$out;


?>
