<? date_default_timezone_set('America/New_York');
require_once("../authenticate.php");

$names = explode(",",$_POST['names']);
$values = explode(",",$_POST['values']);
if (isset($_POST['execMessage'])) $execMessage=mysqli_real_escape_string($link,$_POST['execMessage']);
if (isset($_POST['globalAlert'])) $globalAlert=mysqli_real_escape_string($link,$_POST['globalAlert']);
if (isset($_POST['homepageAlert'])) $homepageAlert=mysqli_real_escape_string($link,$_POST['homepageAlert']);
if (isset($_POST['operatingStatus'])) $operatingStatus=mysqli_real_escape_string($link,$_POST['operatingStatus']);
if (isset($_POST['constitutionURL'])) $constitutionURL=mysqli_real_escape_string($link,$_POST['constitutionURL']);
if (isset($_POST['bylawsURL'])) $bylawsURL=mysqli_real_escape_string($link,$_POST['bylawsURL']);
if (isset($_POST['execAgendaURL'])) $execAgendaURL=mysqli_real_escape_string($link,$_POST['execAgendaURL']);
if (isset($_POST['execMinutesURL'])) $execMinutesURL=mysqli_real_escape_string($link,$_POST['execMinutesURL']);

$out = array();
$out['error'] = '';

for ($i=0; $i<count($names); $i++) {
	$success = mysqli_query($link,"UPDATE igis_settings SET value='".mysqli_real_escape_string($link,$values[$i])."' WHERE name='".$names[$i]."'");
	$out['error'] = $out['error'].mysqli_error($link);
}

if (isset($execMessage)) {
	$success = mysqli_query($link,"UPDATE igis_settings SET value='".$execMessage."' WHERE name='exec_homepage_msg'");
	$out['error'] = $out['error'].mysqli_error($link);
}
if (isset($globalAlert)) {
	$success = mysqli_query($link,"UPDATE igis_settings SET value='".$globalAlert."' WHERE name='global_alert'");
	$out['error'] = $out['error'].mysqli_error($link);
}
if (isset($homepageAlert)) {
	$success = mysqli_query($link,"UPDATE igis_settings SET value='".$homepageAlert."' WHERE name='homepage_alert'");
	$out['error'] = $out['error'].mysqli_error($link);
}
if (isset($operatingStatus)) {
	$success = mysqli_query($link,"UPDATE igis_settings SET value='".$operatingStatus."' WHERE name='operating_status'");
	$out['error'] = $out['error'].mysqli_error($link);
}

if (isset($constitutionURL)) {
	$success = mysqli_query($link,"UPDATE igis_settings SET value='".$constitutionURL."' WHERE name='constitution_url'");
	$out['error'] = $out['error'].mysqli_error($link);
}
if (isset($bylawsURL)) {
	$success = mysqli_query($link,"UPDATE igis_settings SET value='".$bylawsURL."' WHERE name='bylaws_url'");
	$out['error'] = $out['error'].mysqli_error($link);
}
if (isset($execAgendaURL)) {
	$success = mysqli_query($link,"UPDATE igis_settings SET value='".$execAgendaURL."' WHERE name='exec_agenda_url'");
	$out['error'] = $out['error'].mysqli_error($link);
}
if (isset($execMinutesURL)) {
	$success = mysqli_query($link,"UPDATE igis_settings SET value='".$execMinutesURL."' WHERE name='exec_minutes_url'");
	$out['error'] = $out['error'].mysqli_error($link);
}


echo json_encode($out);
?>
