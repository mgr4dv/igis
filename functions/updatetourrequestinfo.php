<?
date_default_timezone_set('America/New_York');
require_once("../authenticate.php");

include("link.php");

if (isset($_POST['markArchived'])) {
	$archivedSet = $_POST['markArchived'];
} else {
	$archivedSet = "archived";
}
if (isset($_POST['markHandled'])) {
	$handledSet = $_POST['markHandled'];
} else {
	$handledSet = "handled";
}
if (isset($_POST['markNew'])) {
	$newSet = $_POST['markNew'];
} else {
	$newSet = "new";
}

$success = mysqli_query($link,"UPDATE tour_requests SET archived=$archivedSet, handled=$handledSet, new=$newSet WHERE request_id=".$_POST['requestID']);

$out['error'] = mysqli_error($link);

echo json_encode($out);

?>
