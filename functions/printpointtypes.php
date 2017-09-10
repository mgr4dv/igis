<? date_default_timezone_set('America/New_York');
require_once("../authenticate.php");

$out = array();
$out['error'] = '';

$header = "<tr><th>Value</th><th>Description</th><th style=\"min-width:70px\"></th>";

//Do positive points:
$point_type_query = mysqli_query($link,"SELECT * FROM point_types WHERE value>0 ORDER BY infraction ASC");
$out['error'] = $out['error'].mysqli_error($link);
if (mysqli_num_rows($point_type_query)>0) {
	$rows = "";
	while($point_type = mysqli_fetch_array($point_type_query)) {
		$editStr = "<button class=\"btn btn-primary btn-xs\" onclick=\"editPointPopup(".$point_type['id'].")\">Edit</button>";
		if (!$point_type['locked']) {
			$deleteStr = "<button class=\"btn btn-danger btn-xs\" onclick=\"deletePoint(".$point_type['id'].")\">X</button>";
		} else {
			$deleteStr = "";
		}
		$rows = $rows."<tr>
							<td style=\"font-weight:bold; color:#FF0000\">+".round(floatval($point_type['value']),1)."</td>
							<td>".$point_type['infraction']."</td>
							<td>".$editStr.$deleteStr."</td>
						</tr>";
	}
	$out['posPoints'] = '<table class="table">
							<thead>
								'.$header.'
							</thead>
							<tbody>
								'.$rows.'
							</tbody>
						</table>';
} else {
	$out['posPoints'] = '<em>No positive-valued points found in the database.</em>';
}

//Do negative points:
$point_type_query = mysqli_query($link,"SELECT * FROM point_types WHERE value<0 ORDER BY infraction ASC");
$out['error'] = $out['error'].mysqli_error($link);
if (mysqli_num_rows($point_type_query)>0) {
	$rows = "";
	while($point_type = mysqli_fetch_array($point_type_query)) {
		$editStr = "<button class=\"btn btn-primary btn-xs\" onclick=\"editPointPopup(".$point_type['id'].")\">Edit</button>";
		if (!$point_type['locked']) {
			$deleteStr = "<button class=\"btn btn-danger btn-xs\" onclick=\"deletePoint(".$point_type['id'].")\">X</button>";
		} else {
			$deleteStr = "";
		}
		$rows = $rows."<tr>
							<td style=\"font-weight:bold; color:#009900\">".round(floatval($point_type['value']),1)."</td>
							<td>".$point_type['infraction']."</td>
							<td>".$editStr.$deleteStr."</td>
						</tr>";
	}
	$out['negPoints'] = '<table class="table">
							<thead>
								'.$header.'
							</thead>
							<tbody>
								'.$rows.'
							</tbody>
						</table>';
} else {
	$out['negPoints'] = '<em>No negative-valued points found in the database.</em>';
}

echo json_encode($out);

?>