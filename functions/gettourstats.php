<?
date_default_timezone_set('America/New_York');
require_once('../authenticate.php');
include("link.php");


$fallSemYear = $_POST['fallSemYear'];
$springSemYear = $fallSemYear+1;

$startDate = date("Y-m-d",mktime(0,0,0,8,1,$fallSemYear)); //start August 1st
$endDate = date("Y-m-d",mktime(0,0,0,6,1,$springSemYear)); //end June 1st



$toursQuery = mysqli_query($link,"SELECT date, time, guides_needed, abbrev, his_req, adm_req FROM tours_info
									INNER JOIN tours_types WHERE tours_info.type=tours_types.type_id
									AND date>='".$startDate."'
									AND date <='".$endDate."'
									ORDER BY date ASC");
$numResults = mysqli_num_rows($toursQuery);
$academicYearTours = array();
for ($i=0; $i<$numResults; $i++) {
	$tour = mysqli_fetch_array($toursQuery);
	$tourDate = strtotime($tour['date']);
	$tour['lastMonday'] = date('M j',strtotime('last Monday',strtotime('tomorrow',$tourDate)));
	$tour['nextSunday'] = date('M j',strtotime('Sunday',$tourDate));
	array_push($academicYearTours, $tour);
}



$listOfWeeks = array();
for($i = strtotime('last Monday', strtotime('tomorrow', strtotime($startDate))); $i <= strtotime($endDate); $i = strtotime('+1 week', $i)) {
    array_push($listOfWeeks,date('M j',$i));
}

$out['academicYearTours'] = $academicYearTours;
$out['adacemicYearListOfWeeks'] = $listOfWeeks;
$out['alert'] = '';


echo json_encode($out);


?>
