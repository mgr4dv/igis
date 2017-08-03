<? date_default_timezone_set('America/New_York');
require_once("../authenticate.php");

include("link.php");

$out = array();

//Fetch un-archived tour requests from database:
$tourRequests=mysqli_query($link,"select * from tour_requests WHERE archived=".$_POST['archived']." ORDER by date_submitted DESC");
$out['error'] = mysqli_error($link);

//Assemble list of tour requests:
$requestList = "";
while($request=mysqli_fetch_array($tourRequests)) {
	if ($request['new']) {
		$newLabel = '<span id="requestListItem'.$request['request_id'].'NewLabel" class="label label-info">NEW</span> ';
	} else {
		$newLabel = '';
	}
	if ($request['handled']) {
		$handledLabel = '<span id="requestListItem'.$request['request_id'].'HandledLabel" class="label label-success">&#x2713;</span> ';
	} else {
		$handledLabel = '';
	}
	$requestList=$requestList."<a href=\"#\" id=\"requestListItem".$request['request_id']."\" class=\"list-group-item\" onclick=\"displayRequest(".$request['request_id'].")\">\n";
	$requestList=$requestList.$newLabel.$handledLabel."<h4 class=\"list-group-item-heading\" style=\"display:inline\">".$request['type']." on \"".$request['date']."\" at \"".$request['time']."\"</h4>\n";
	$requestList=$requestList."<p class=\"list-group-item-text\" style=\"text-align:right; font-size:smaller; font-style:italic;\">Submitted on ".date('M j, Y',strtotime($request['date_submitted']))." at ".date('g:i a',strtotime($request['date_submitted']))."</p>\n";
	$requestList=$requestList."</a>\n";
}

//If it wound up empty (ie, there were no requests in the list), put in a note about that:
if ($requestList=='' && $_POST['archived']) {
	$requestList = "<p style=\"text-align:center; font-style:italic\">No archived requests!</p>";
} elseif ($requestList=='' && !$_POST['archived']) {
	$requestList = "<p style=\"text-align:center; font-style:italic\">No un-archived requests!</p>";
}
$out['requestList'] = $requestList;

echo json_encode($out);

?>
