<? date_default_timezone_set('America/New_York');
require_once("../authenticate.php");

$pointID = $_POST['pointID'];

$out = array();

$query = mysqli_query($link,"SELECT * FROM point_types WHERE id=$pointID");
$out['error'] = mysqli_error($link);
$info = mysqli_fetch_array($query);

$out['desc'] = $info['infraction'];
$out['val'] = round($info['value'],1);
$out['locked'] = $info['locked'];

echo json_encode($out);

?>
