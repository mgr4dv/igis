<?
date_default_timezone_set('America/New_York');
require_once("../authenticate.php");

include("link.php");


$newTour = $_POST['newTour'];
$deleteTour = $_POST['deleteTour'];
$tourID = $_POST['tourID'];
if (isset($_POST['date'])) $date = date('Y-m-d',strtotime($_POST['date'])); //reformat date to MySQL native format
if (isset($_POST['time'])) $time = date('H:i:s',strtotime($_POST['time'])); //reformat time to MySQL native format
if (isset($_POST['type'])) $type = $_POST['type'];
if (isset($_POST['numSlots'])) $numSlots = $_POST['numSlots'];
if (isset($_POST['notes'])) $notes = mysqli_real_escape_string($link, $_POST['notes']);
if (isset($_POST['isReg'])) {
	$isReg = $_POST['isReg'];
} else {
	$isReg = 0;
}
if (isset($_POST['day'])) $day = $_POST['day'];

$out = array();
$out['error']='';
$out['tour_id']='';
$out['dateStr']=date('F jS, Y',strtotime($_POST['date']));
$out['timeStr']=date('g:i a',strtotime($_POST['time']));

//get abbreviation:
$abbrev = mysqli_query($link,"SELECT abbrev FROM tours_types WHERE type_id=".$_POST['type']);
$abbrev = mysqli_fetch_array($abbrev);
$out['abbrev'] = $abbrev[0];

if (!$isReg) {
	if ($newTour) {
		if (isset($_POST['date']) && isset($_POST['time'])) {
			if (strtotime($_POST['date']) && strtotime($_POST['time'])) {
				//insert it into the tours_info table: (tour_id is the auto-incremented primary key in MySQL, so it doesn't need to be set)
				$success = mysqli_query($link,"INSERT INTO tours_info (date, time, type, guides_needed, notes) VALUES ('$date', '$time', $type, $numSlots, '$notes')");
				if (!$success) {
					$out['error']=$out['error']."Error creating new tour: Database says \"".mysqli_error($link)."\"";
				}
			} else {
				$out['error']=$out['error']."Error creating new tour: Invalid date or time.";
			}
		} else {
			$out['error']=$out['error']."Error creating new tour: You must specify a date and a time for the tour.";
		}
		$tourID = mysqli_query($link,"SELECT tour_id FROM tours_info WHERE date='$date' AND time='$time' AND type=$type AND guides_needed=$numSlots AND notes='$notes'");
		$tourID = mysqli_fetch_array($tourID);
		$tourID = $tourID[0];
		$out['tour_id']=$tourID;
	} else if ($deleteTour) {
		//get information about the former tour:
		$tourInfo = mysqli_query($link,"SELECT * FROM tours_info WHERE tour_id=$tourID");
		$tourInfo = mysqli_fetch_array($tourInfo);
		$out['dateStr']=date('F jS, Y',strtotime($tourInfo['date']));
		$out['timeStr']=date('g:i a',strtotime($tourInfo['time']));
		//get abbreviation for the former tour:
		$abbrev = mysqli_query($link,"SELECT abbrev FROM tours_types WHERE type_id=".$tourInfo['type']);
		$abbrev = mysqli_fetch_array($abbrev);
		$out['abbrev'] = $abbrev[0];

		//remove from tour and scheduling table:
		$success1 = mysqli_query($link,"DELETE FROM tours_info WHERE tour_id=$tourID");
		if (!$success1) {
			$out['error']=$out['error']."Error deleting from tours_info table: Database says \"".mysqli_error($link)."\"";
		}
		$success2 = mysqli_query($link,"DELETE FROM tours_scheduled WHERE tour_id=$tourID");
		if (!$success2) {
			$out['error']=$out['error']."Error deleting from tours_scheduled table: Database says \"".mysqli_error($link)."\"";
		}

		//also delete from the tours_handled table so nobody accidentally gets credit for a tour that doesn't exist.
		$success3 = mysqli_query($link,"DELETE FROM tours_handled WHERE tour_id=$tourID");
		if (!$success3) {
			$out['error']=$out['error']."Error deleting from tours_handled table: Database says \"".mysqli_error($link)."\"";
		}
	} else { //modify tour
		if (isset($_POST['date']) && isset($_POST['time'])) { //check to make sure you have a date/time
			if (strtotime($_POST['date']) && strtotime($_POST['time'])) { //check to make sure the date/time are formed correctly
				$success = mysqli_query($link,"UPDATE tours_info SET date='$date', time='$time', type='$type', guides_needed=$numSlots, notes='$notes' WHERE tour_id=$tourID");
				if (!$success) {
					$out['error']=$out['error']."Error modifying tour: Database says \"".mysqli_error($link)."\"";
				}
			} else {
				$out['error']=$out['error']."Error modifying tour: Invalid date or time.";
			}
		} else {
			$out['error']=$out['error']."Error modifying tour: You must specify a date and a time for the tour.";
		}
	}
	echo mysqli_error($link);
} else { //if ($isReg)
	if ($newTour) {
		if (isset($_POST['day']) && isset($_POST['time'])) {
			if (strtotime($_POST['time'])) {
				//insert it into the tours_info table: (tour_id is the auto-incremented primary key in MySQL, so it doesn't need to be set)
				$success = mysqli_query($link,"INSERT INTO tours_reg (day, time, tour_type, guides_needed) VALUES ('$day', '$time', $type, $numSlots)");
				if (!$success) {
					$out['error']=$out['error']."Error creating new regular tour: Database says \"".mysqli_error($link)."\"";
				}
			} else {
				$out['error']=$out['error']."Error creating new regular tour: Invalid time.";
			}
		} else {
			$out['error']=$out['error']."Error creating new regular tour: You must specify a day and a time for the tour.";
		}
	} else if ($deleteTour) {
		//remove from tour and scheduling table:
		$success1 = mysqli_query($link,"DELETE FROM tours_reg WHERE reg_id=$tourID");
		if (!$success1) {
			$out['error']=$out['error']."Error deleting regular tour: Database says \"".mysqli_error($link)."\"";
		}
	} else {
		if (isset($_POST['day']) && isset($_POST['time'])) {
			if (strtotime($_POST['time'])) {
				//edit the targeted tour
				$success = mysqli_query($link,"UPDATE tours_reg SET day='$day', time='$time', tour_type='$type', guides_needed=$numSlots WHERE reg_id=$tourID");
				if (!$success) {
					$out['error']=$out['error']."Error modifying regular tour: Database says \"".mysqli_error($link)."\"";
				}
			} else {
				$out['error']=$out['error']."Error modifying regular tour: Invalid time.";
			}
		} else {
			$out['error']=$out['error']."Error modifying regular tour: You must specify a date and a time for the tour.";
		}
	}
}

echo json_encode($out);

?>
