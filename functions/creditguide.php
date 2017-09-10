<?
date_default_timezone_set('America/New_York');
require_once("../authenticate.php");

if (!$is_exec) {
	header("location: ../signups.php");
}

$guideIDs = explode(',',$_POST['guideID']);
$tourIDs = explode(',',$_POST['tourID']);
$status = $_POST['status'];

if (count($guideIDs)!=count($tourIDs)) {
	exit("Error: number of guide IDs and tour IDs given does not match.");
}
for ($i=0; $i<count($guideIDs); $i++) {
	$guideID = $guideIDs[$i];
	$tourID = $tourIDs[$i];
	//First, look for an existing table entry:
	$already_credited = mysqli_query($link, "SELECT status FROM tours_handled WHERE guide_id=$guideID AND tour_id=$tourID");
	$error1 = mysqli_error($link);
	$already_credited = mysqli_num_rows($already_credited);

	//Then, assign tour credit, creating the table entry if it doesn't exist already.
	if($already_credited) {
		$success2 = mysqli_query($link, "UPDATE tours_handled SET status='$status' WHERE tour_id=$tourID AND guide_id=$guideID");
		$error2 = mysqli_error($link);

		$success3 = mysqli_query($link, "UPDATE tours_scheduled SET handled='yes' WHERE tour_id=$tourID AND guide_id=$guideID");
		$error3 = mysqli_error($link);

		$error2 = $error2.$error3;
	} else {
		$names = mysqli_query($link, "SELECT firstname, lastname FROM guides WHERE guide_id=$guideID");
		$names = mysqli_fetch_array($names);
		//$names = $names[0];
		$fname = mysqli_real_escape_string($link,$names['firstname']);
		$lname = mysqli_real_escape_string($link,$names['lastname']);
		$error2 = mysqli_error($link);

		$success = mysqli_query($link, "INSERT INTO tours_handled (tour_id, guide_id, guide_fname, guide_lname, status) VALUES ($tourID, $guideID, '$fname', '$lname', '$status')");
		$error3 = mysqli_error($link);

		$success4 = mysqli_query($link, "UPDATE tours_scheduled SET handled='yes' WHERE tour_id=$tourID AND guide_id=$guideID");
		$error4 = mysqli_error($link);

		$error2 = $error2.$error3.$error4; //combine errors for a single output
	}

	//Finally, assign this guide points if the tour is being marked as missed.
	$error3='';
	$error4='';
	if ($status=='missed') {
		//Form information about the tour:
		$tour = mysqli_query($link,"SELECT date, time, type, abbrev FROM tours_info RIGHT JOIN tours_types ON tours_info.type=tours_types.type_id WHERE tour_id=$tourID");
		$error3 = mysqli_error($link);
		$tour = mysqli_fetch_array($tour);
		$tourTime = date('g:i',strtotime($tour['time']));
		$tourDate = date('n/j/y',strtotime($tour['date']));
		$tourType = $tour['abbrev'];
		$comment = "Missed the ".$tourTime." ".$tourType." on ".$tourDate;

		//Form information about the missing-a-tour infraction:
		$typeID = $igis_settings['missed_tour_point_id']; //this is the ID number of the infraction involving missing a tour
		$pointVal = mysqli_query($link,"SELECT value FROM point_types WHERE id=$typeID");
		$error4 = mysqli_error($link);
		$pointVal = mysqli_fetch_array($pointVal);
		$pointVal = round(floatval($pointVal[0]),1);

		//Assign the points:
		$assigned = date('Y-m-d');
		$success = mysqli_query($link,"INSERT INTO points (guide, value, assigned, comment) VALUES ($guideID, $pointVal, '$assigned', '$comment')");
	} else if ($status=='credited' && $already_credited) {
		//This means they must have been marked as missed already, so check for an auto-generated point entry and delete it:

		//Form information about the tour, specifically so the comment can be searched for:
		$tour = mysqli_query($link,"SELECT date, time, type, abbrev FROM tours_info RIGHT JOIN tours_types ON tours_info.type=tours_types.type_id WHERE tour_id=$tourID");
		$error3 = mysqli_error($link);
		$tour = mysqli_fetch_array($tour);
		$tourTime = date('g:i',strtotime($tour['time']));
		$tourDate = date('n/j/y',strtotime($tour['date']));
		$tourType = $tour['abbrev'];
		$comment = "Missed the ".$tourTime." ".$tourType." on ".$tourDate;

		//Delete an auto-generated missed-tour point entry, if it exists:
		$success = mysqli_query($link,"DELETE FROM points WHERE comment='$comment'");
		$error4 = mysqli_error($link);
	}

	echo $error1.$error2.$error3.$error4;
}



?>
