<? date_default_timezone_set('America/New_York');
require_once('../authenticate.php');

$deleteClass = $_POST['deleteClass'];
$addClass = $_POST['addClass'];
if (isset($_POST['className'])) {
	$className = mysqli_real_escape_string($link,$_POST['className']);
	$classDate = $_POST['classDate'];
	$classSem = $_POST['classSem'];
}
if (isset($_POST['classID'])) {
	$classID = $_POST['classID'];
}

$out = array();
$out['error'] = '';

if ($addClass) {
	$success = mysqli_query($link,"INSERT INTO probieclass (chair, semester, date) VALUES ('$className', '$classSem', '$classDate')");
	$out['error'] = mysqli_error($link);
} else if ($deleteClass) {
	$success = mysqli_query($link,"DELETE FROM probieclass WHERE probie_id=$classID");
	$out['error'] = mysqli_error($link);
}

echo json_encode($out);

?>
