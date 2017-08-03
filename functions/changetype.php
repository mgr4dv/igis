<?
date_default_timezone_set('America/New_York');

include("link.php");

$newType = mysqli_real_escape_string($link,$_POST['newType']);
$deleteType = mysqli_real_escape_string($link,$_POST['deleteType']);
$typeID = mysqli_real_escape_string($link,$_POST['typeID']);
$name = mysqli_real_escape_string($link,$_POST['name']);
$abbrev = mysqli_real_escape_string($link,$_POST['abbrev']);
$historical = mysqli_real_escape_string($link,$_POST['historical']);
$admissions = mysqli_real_escape_string($link,$_POST['admissions']);
$requestable = mysqli_real_escape_string($link,$_POST['requestable']);
$publicName = mysqli_real_escape_string($link,$_POST['publicName']);
$desc = mysqli_real_escape_string($link,$_POST['desc']);

if ($newType) {
	//insert it into the tours_info table: (tour_id is the auto-incremented primary key in MySQL, so it doesn't need to be set)
	$success = mysqli_query($link,"INSERT INTO tours_types (name, abbrev, description, his_req, adm_req, offered, requestable, public_name) VALUES ('$name', '$abbrev', '$desc', '$historical','$admissions', 'yes', $requestable, '$publicName')");
	if (!$success) {
		echo "Error creating new tour type: Database says \"".mysqli_error($link)."\"";
	}
} else if ($deleteType) {
	//remove from tour and scheduling table:
	$success = mysqli_query($link,"UPDATE tours_types SET offered='deleted' WHERE type_id=$typeID");
	if (!$success) {
		echo "Error archiving tour type: Database says \"".mysqli_error($link)."\"";
	}
} else {
	//modify tour
	$success = mysqli_query($link,"UPDATE tours_types SET name='$name', abbrev='$abbrev', description='$desc', his_req='$historical', adm_req='$admissions', requestable=$requestable, public_name='$publicName' WHERE type_id=$typeID");
	if (!$success) {
		echo "Error modifying tour type: Database says \"".mysqli_error($link)."\"";
	}
}
echo mysqli_error($link);

?>
