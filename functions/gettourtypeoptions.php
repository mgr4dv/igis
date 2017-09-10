<? date_default_timezone_set('America/New_York');

include("link.php");

$tourTypes=mysqli_query($link,"select * from tours_types WHERE offered='yes' AND requestable=1 ORDER by public_name ASC");
$tourTypeOptions = "";
while ($type=mysqli_fetch_array($tourTypes)) {
	$tourTypeOptions = $tourTypeOptions."\n<option value=\"".$type['abbrev']."\">";
	$tourTypeOptions = $tourTypeOptions.$type['public_name'];
	$tourTypeOptions = $tourTypeOptions."</option>";
}

echo $tourTypeOptions;
?>
