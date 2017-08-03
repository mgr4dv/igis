<?
date_default_timezone_set('America/New_York');
require_once("../authenticate.php");

if ($_SESSION['id']!=$_POST['guideID'] && !$is_exec) {
	header("location: ../signups.php");
}

$guideID = $_POST['guideID'];
$tourID = $_POST['tourID'];

include("link.php");
$tour_query = mysqli_query($link,"SELECT * FROM tours_info RIGHT JOIN tours_types ON tours_info.type=tours_types.type_id WHERE tours_info.tour_id=$tourID ");
$guide_query = mysqli_query($link,"SELECT guide_id, firstname, lastname FROM guides WHERE guide_id=$guideID");
$already_scheduled_query = mysqli_query($link,"SELECT * FROM tours_scheduled WHERE guide_id=$guideID AND tour_id=$tourID");
$tour_info=mysqli_fetch_array($tour_query);
$guide_info=mysqli_fetch_array($guide_query);
$already_scheduled = mysqli_num_rows($already_scheduled_query); //want this to return zero

//only allow to proceed if there is no entry already existing pairing this guide with this tour:
if (!$already_scheduled) {
	// add the guide to the tours_scheduled table
	$guide_fname = mysqli_real_escape_string($link,$guide_info['firstname']);
	$guide_lname = mysqli_real_escape_string($link,$guide_info['lastname']);
	$success1 = mysqli_query($link,"INSERT INTO tours_scheduled (tour_id, guide_id, guide_fname, guide_lname) VALUES ('$tourID', '$guideID', '$guide_fname', '$guide_lname')");
	$error1 = mysqli_error($link);

	$success2=false;
	if ($success1) {
		// if the new pairing was successfully added to the tours_scheduled table, update the tours_info table to reflect the additional guide :
		$guides_scheduled=$tour_info['guides_scheduled'];
		$guides_scheduled=$guides_scheduled+1;
		$success2 = mysqli_query($link,"UPDATE tours_info SET guides_scheduled=$guides_scheduled WHERE tour_id=$tourID");
		$error2 = mysqli_error($link);
	}

	echo "Guide: ".$guideID.", Tour: ".$tourID." Success: ".var_export($success1,true).",".var_export($success2,true)."\n\nalready_scheduled=$already_scheduled \nCommand: \"INSERT INTO tours_scheduled (tour_id, guide_id, guide_fname, guide_lname) VALUES ($tourID, $guideID, $guide_fname, $guide_lname)\"\n\nError1:\"$error1\"\nError2:\"$error2\"";
} else {
	echo "Error: you're already signed up for this tour.";
}

?>
