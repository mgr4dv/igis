<?
date_default_timezone_set('America/New_York');
require_once("../authenticate.php");

include("link.php");

$query = mysqli_query($link,"SELECT * FROM tour_requests WHERE request_id=".$_POST['requestID']);

$tourInfo = mysqli_fetch_array($query);
$tourInfo['date_requested'] = date('F jS, Y',strtotime($tourInfo['date_submitted']));
$tourInfo['time_requested'] = date('g:i a',strtotime($tourInfo['date_submitted']));

echo json_encode($tourInfo);

?>
