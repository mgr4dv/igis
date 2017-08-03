<?
date_default_timezone_set('America/New_York');
$guideID = $_POST['guideID'];
require_once('../authenticate.php');

include("link.php");
$query = mysqli_query($link,"SELECT * FROM guides WHERE guide_id=$guideID");
if($query) {
	$info = mysqli_fetch_array($query);

	$info['date'] = date('F j, Y',strtotime($info['date'])); //reformat date to match what the datepicker outputs
	$info['time'] = date('g:i A',strtotime($info['time'])); //reformat time to match what the timepicker outputs

	echo json_encode($info);
} else {
	echo "Error getting guide info: Server says\"".mysqli_error($link)."\"";
}

?>
