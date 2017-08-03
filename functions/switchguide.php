<?
require_once("../authenticate.php");

if ($_SESSION['id']!=$_POST['guideID'] && !$is_exec) {
	header("location: ../signups.php");
}

$oldGuideID = $_POST['oldGuideID'];
$guideID = $_POST['guideID'];
$tourID = $_POST['tourID'];

$guide_query = mysqli_query($link,"SELECT guide_id, firstname, lastname, email FROM guides WHERE guide_id=$guideID");
$guide_info=mysqli_fetch_array($guide_query);

$old_guide_query = mysqli_query($link,"SELECT guide_id, firstname, lastname, email FROM guides WHERE guide_id=$oldGuideID");
$old_guide_info=mysqli_fetch_array($old_guide_query);

$tour_query = mysqli_query($link,"SELECT date, time, abbrev, name FROM tours_info
								INNER JOIN tours_types ON tours_info.type=tours_types.type_id 
								WHERE tour_id=$tourID");
$tour_info = mysqli_fetch_array($tour_query);

$already_scheduled_query = mysqli_query($link,"SELECT * FROM tours_scheduled WHERE guide_id=$guideID AND tour_id=$tourID"); //look for the new guide
$already_scheduled = mysqli_num_rows($already_scheduled_query); //want this to return zero (if the new guide is already scheduled, they can't sign up)

$old_scheduled_query = mysqli_query($link,"SELECT * FROM tours_scheduled WHERE guide_id=$oldGuideID AND tour_id=$tourID"); //look for the old guide
$old_scheduled_info = mysqli_fetch_array($old_scheduled_query);
$cover_request = $old_scheduled_info['cover_request'];

//only allow to proceed if there is no entry already existing pairing this guide with this tour, and if the currently-scheduled guide really did request a cover:
if ($cover_request || $is_exec) {
	if (!$already_scheduled) {
		// add the guide to the tours_scheduled table
		// (the tours_info table does not need to be updated because the number of guides scheduled has not changed)
		$guide_fname = $guide_info['firstname'];
		$guide_lname = $guide_info['lastname'];
		$success1 = mysqli_query($link,"UPDATE tours_scheduled SET guide_id=$guideID, guide_fname='".mysqli_real_escape_string($link,$guide_fname)."', guide_lname='".mysqli_real_escape_string($link,$guide_lname)."', cover_request=0 WHERE tour_id=$tourID AND guide_id=$oldGuideID");
		$error1 = mysqli_error($link);
		
		if($cover_request) {
			//if a cover has been requested, let the old guide know that it's been covered.
			$toAddress = $old_guide_info['email'];
			$fromAddress = "uguidestech@gmail.com";
			$subject = $guide_fname." ".$guide_lname." has covered your ".date("g:i a",strtotime($tour_info['time']))." ".$tour_info['abbrev']."!";
			$message = "This is an automated message from IGIS to let you know that ".$guide_fname." ".$guide_lname." has covered your ".date("g:i a",strtotime($tour_info['time']))." ".$tour_info['name']." tour on ".date("l, F jS",strtotime($tour_info['date'])).".\n\nYou are no longer responsible for this tour.";
			mail($toAddress,$subject,$message,"From: $fromAddress\n");
		}

		echo "Guide: ".$guideID.", Tour: ".$tourID." Success: ".var_export($success1,true)."\n\nalready_scheduled=$already_scheduled \nCommand: \"UPDATE tours_scheduled SET guide_id=$guideID, guide_fname='$guide_fname', guide_lname='$guide_lname' WHERE tour_id=$tourID AND guide_id=$oldGuideID\"\n\nError:\"$error1\"";
	} else {
		echo "Error: This guide is already signed up for this tour.\n\n('SELECT * FROM tours_scheduled WHERE guide_id=$guideID AND tour_id=$tourID')";
	}
} else {
	echo "Error: there doesn't appear to be an active tour request open here.\n\n('SELECT * FROM tours_scheduled WHERE guide_id=$oldGuideID AND tour_id=$tourID')";
}
?>