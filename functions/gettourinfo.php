<?

$tourID = $_POST['tourID'];
$isReg = 0; //default value
$isReg = isset($_POST['value']) ? $_POST['value'] : '';

include("link.php");

if (!$isReg) {
	$query = mysqli_query($link,"SELECT * FROM tours_info WHERE tour_id=$tourID");
	if($query) {
		$info = mysqli_fetch_array($query);

		$info['date'] = date('F j, Y',strtotime($info['date'])); //reformat date to match what the datepicker outputs
		$info['time'] = date('g:i A',strtotime($info['time'])); //reformat time to match what the timepicker outputs
		$info['notes'] = htmlspecialchars_decode($info['notes']); //reformat back into readable text

		echo json_encode($info);
	} else {
		echo "Error getting tour info: Server says\"".mysqli_error($link)."\"";
	}
} else {
	$query = mysqli_query($link,"SELECT * FROM tours_reg WHERE reg_id=$tourID");
	if($query) {
		$info = mysqli_fetch_array($query);
		$info['time'] = date('g:i A',strtotime($info['time'])); //reformat time to match what the timepicker outputs

		echo json_encode($info);
	} else {
		echo "Error getting tour info: Server says\"".mysqli_error($link)."\"";
	}
}

?>
