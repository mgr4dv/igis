<?
date_default_timezone_set('America/New_York');
require_once("../authenticate.php");

$newType = $_POST['newType'];
$deleteType = $_POST['deleteType'];

$typeID = $_POST['typeID'];
$desc = mysqli_real_escape_string($link,$_POST['desc']);
$val = $_POST['val'];

$out = array();
$out['error']='';

if ($newType) {
	//insert it into the tours_info table: (tour_id is the auto-incremented primary key in MySQL, so it doesn't need to be set)
	$success = mysqli_query($link,"INSERT INTO point_types (infraction, value) VALUES ('$desc', $val)");
	if (!$success) {
		$out['error'] = "Error creating new tour type: Database says \"".mysqli_error($link)."\"";
	}
} else if ($deleteType) {
	//remove from tour and scheduling table:
	$success = mysqli_query($link,"DELETE FROM point_types WHERE id=$typeID");
	if (!$success) {
		$out['error'] = "Error archiving tour type: Database says \"".mysqli_error($link)."\"";
	}
} else { //modify tour
	$success = mysqli_query($link,"UPDATE point_types SET infraction='$desc', value=$val WHERE id=$typeID");
	if (!$success) {
		$out['error'] = "Error modifying tour type: Database says \"".mysqli_error($link)."\"";
	}
}
echo json_encode($out);

?>
