<?
require_once("../authenticate.php");

if ($_SESSION['id']!=$_POST['guideID'] && !$is_exec) {
	header("location: ../signups.php");
}

$guideID = $_POST['guideID'];
$tourID = $_POST['tourID'];
$coverRequest = $_POST['coverRequest'];

include("link.php");

$scheduled_query = mysqli_query($link,"SELECT cover_request FROM tours_scheduled WHERE tour_id=$tourID AND guide_id=$guideID");
if (mysqli_num_rows($scheduled_query)){
	$success = mysqli_query($link,"UPDATE tours_scheduled SET cover_request=$coverRequest WHERE tour_id=$tourID AND guide_id=$guideID");
	echo "Query: 'UPDATE tours_scheduled SET cover_request=$coverRequest WHERE tour_id=$tourID AND guide_id=$guideID' \n  NumRows=" . "\nError:" . mysqli_error($link);
} else {
	echo "Error: You aren't scheduled for this tour anymore.";
}

?>
