<?
require_once("../authenticate.php");

$typeID = $_POST['typeID'];

$query = mysqli_query($link,"SELECT * FROM tours_types WHERE type_id=$typeID");
if($query) {
	$info = mysqli_fetch_array($query);
	echo json_encode($info);
} else {
	echo "Error getting tour info: Server says\"".mysqli_error($link)."\"";
}

?>