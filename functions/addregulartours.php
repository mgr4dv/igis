<?

$dates = $_POST['dates'];
include("link.php");

for ($i=0; $i<count($dates); $i++) {
	$dateStr = $dates[$i];
	$timestamp = strtotime($dateStr);
	$dayOfWeek = date('l',$timestamp);
	$date = date('Y-m-d',$timestamp); //convert to native MySQL format
	$tour_query = mysqli_query($link,"SELECT * FROM tours_reg WHERE day='$dayOfWeek'");
	while ($tour = mysqli_fetch_array($tour_query)) {
		$time = $tour['time'];
		$type = $tour['tour_type'];
		$numSlots = $tour['guides_needed'];
		$success = mysqli_query($link,"INSERT INTO tours_info (date, time, type, guides_needed) VALUES ('$date', '$time', $type, $numSlots)");
		if (!$success) {
			echo "Error adding tour at $time on $date: Database says\"".mysqli_error($link)."\"";
		}
	}
}

?>
