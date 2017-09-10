<?
date_default_timezone_set('America/New_York');

include("link.php");
$out = array();

//defaults for description construction:
$execDesc = "";
$execNames = false;
$statusDesc = "";
$yearDesc = "";
$in = "";
$the = "";
$classOf = "";
$schoolDesc = "";
$probieClassDesc = "guides";

if ($_POST['status']=='0'){
	$statusSelect = "(guides.status=guides.status OR guides.status IS NULL)";
} else {
	$statusSelect = "guides.status='".mysqli_real_escape_string($link,$_POST['status'])."'";
	$statusDesc = $_POST['status']." ";
}
if ($_POST['year']=='0'){
	$yearSelect = "(guides.year=guides.year OR guides.year IS NULL)";
} else {
	$yearSelect = "guides.year=".$_POST['year'];
	$yearDesc = " ".$_POST['year'];
	$in = " in";
	$the = " the";
	$classOf = " class of";
}
if ($_POST['school']=='0'){
	$schoolSelect = "(guides.school=guides.school OR guides.school IS NULL)";
} else {
	$schoolSelect = "guides.school='".mysqli_real_escape_string($link,$_POST['school'])."'";
	$schoolDesc = " ".$_POST['school'];
	$in = " in";
}
if ($_POST['probieClass']=='0'){
	$probieClassSelect = "(guides.probie_class=guides.probie_class OR guides.probie_class IS NULL)";
} else {
	$probieClassSelect = "guides.probie_class='".mysqli_real_escape_string($link,$_POST['probieClass'])."'";
	$probieClassDesc = $_POST['probieClass']." probies";
}
if (!isset($_POST['execSelect']) || $_POST['execSelect']!='exec') {
	$execJoin = "";
	$execSelect = "";
} else {
	$execJoin = "INNER JOIN exec_board ON exec_board.guide_id=guides.guide_id";
	$execSelect = "position, is_chair, is_vicechair, is_techchair, is_scheduler, is_disciplinarian,";
	$execDesc = "exec-member ";
	$execNames = true;
}

$out['query'] = "SELECT $execSelect email, firstname, lastname FROM guides $execJoin WHERE $statusSelect AND $yearSelect AND $schoolSelect AND $probieClassSelect ORDER BY lastname ASC";
$query = mysqli_query($link,$out['query']);
$out['error'] = mysqli_error($link);

$rows = '';
$list = '';
$numResults = mysqli_num_rows($query);
while ($guide=mysqli_fetch_array($query)) {
	if (!$execNames) {
		$rows = $rows."<tr><td>".$guide['email']."</td><td>".$guide['firstname']." ".$guide['lastname']."</td></tr>\n";
	} else {
		$rows = $rows."<tr><td>".$guide['email']."</td><td>".$guide['firstname']." ".$guide['lastname']." -- ".$guide['position']."</td></tr>\n";
	}
	$list = $list.$guide['email'].", ";
}
$list = substr($list,0,-2);
$out['resultsRows'] = $rows;
$out['resultsList'] = $list;
$out['resultsDescription'] = $execDesc.$statusDesc.$probieClassDesc.$in.$the.$schoolDesc.$classOf.$yearDesc.' ('.$numResults.' results)';

echo json_encode($out);
?>
