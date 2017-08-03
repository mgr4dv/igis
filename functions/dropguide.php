<?
require_once("../authenticate.php");

if ($_SESSION['id']!=$_POST['guideID'] && !$is_exec) {
	header("location: ../signups.php");
}

$guideIDs = explode(',',$_POST['guideID']);
$tourIDs = explode(',',$_POST['tourID']);

include("link.php");

for ($i=0; $i<count($guideIDs); $i++) {
	$guideID = $guideIDs[$i];
	$tourID = $tourIDs[$i];

	$tour_query = mysqli_query($link,"select * from tours_info RIGHT JOIN tours_types ON tours_info.type=tours_types.type_id WHERE tours_info.tour_id='$tourID' ");
	$scheduled_query = mysqli_query($link,"SELECT * FROM tours_scheduled WHERE guide_id=$guideID AND tour_id=$tourID");
	$tour_info=mysqli_fetch_array($tour_query);
	$already_scheduled = mysqli_num_rows($scheduled_query); //want this to return one (or more if there was a bug and the guide signed up more than once)

	$error1="";
	$error2="";
	$error3="";
	//Only allow to proceed if it's already on the list
	if ($already_scheduled) {
		// remove the guide from the tours_scheduled table
		$success1 = mysqli_query($link,"DELETE FROM tours_scheduled WHERE guide_id=$guideID and tour_id=$tourID");

		if (!$success1) {
			$error1="Error deleting guide from tours_scheduled table: Database says \"".mysqli_error($link)."\"";
		}

		$success2=false;
		if ($success1) {
			// if the new pairing was successfully added to the tours_scheduled table, update the tours_info table to reflect the removed guide :
			$guides_scheduled=$tour_info['guides_scheduled'];
			$guides_scheduled=$guides_scheduled-$already_scheduled; //subtract the number of times they were scheduled for this tour; should be one, but on the offchance it's multiple times, that needs to be updated.
			$success2 = mysqli_query($link,"UPDATE tours_info SET guides_scheduled=$guides_scheduled WHERE tour_id=$tourID");
			if (!$success2) {
				$error2="\n\nError updating number of guides scheduled in tours_info table: Database says \"".mysqli_error($link)."\"";
			}

			//also remove this guide from the tours_handled database, just in case this is the scheduler removing a guide retroactively. This makes it so that if the guide has credit, that disappears.
			$success3 = mysqli_query($link,"DELETE FROM tours_handled WHERE guide_id=$guideID AND tour_id=$tourID");
			if (!$success3) {
				$error3="\n\nError deleting guide/tour combo from tours_handled table: Database says \"".mysqli_error($link)."\"";
			}
		}

		echo $error1.$error2.$error3;
	} else {
		echo "Error: This guide isn't signed up for this tour.";
	}
}





?>
