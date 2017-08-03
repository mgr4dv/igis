<?
require_once("../authenticate.php");

if ($_SESSION['id']!=$_POST['guideID'] && !$is_exec) {
	header("location: ../signups.php");
}

$guideID = $_POST['guideID'];
$tourID = $_POST['tourID'];

include("link.php");
//$query = mysqli_query($link,"ALTER TABLE tours_scheduled ADD cover_request BIT(1)");
$prequery = mysqli_query($link,"UPDATE tours_scheduled SET cover_request=1 WHERE tour_id=$tourID AND guide_id=$guideID");
$query = mysqli_query($link,"SELECT cover_request FROM tours_scheduled WHERE guide_id=$guideID AND tour_id=$tourID");
$result = mysqli_fetch_array($query);

if ($result[0]) {
	$cover_request = 'true';
} else {
	$cover_request = 'false';
}

echo "Query: 'SELECT cover_request FROM tours_scheduled WHERE guide_id=$guideID AND tour_id=$tourID' \n  NumRows=" . mysqli_num_rows($query) . "\nError:" . mysqli_error($link) . "\nResult:" . $cover_request;

?>
