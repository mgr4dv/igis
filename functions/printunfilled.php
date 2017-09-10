<?

//Include "genpopoups.php" because it has necessary functions for forming the various type of popovers that are inserted into labels:
include_once("genpopups.php");
//Resume the session to be able to grab user data (this file is called in isolation, so the session doesn't automatically carry over):
session_start();
//Set the local timezone so that all time operations will be in local time:
date_default_timezone_set('America/New_York');

if (isset($_REQUEST['days'])) {
	$days = $_REQUEST['days'];
} else{
	$days = 2;
}

$beginRange = date("Y-m-d"); //today
$endRange = date("Y-m-d", strtotime("+".$days." days")); //$days days from now

include("link.php");

$tours=mysqli_query($link, "SELECT tour_id, date, time, type, notes, abbrev, name FROM tours_info
							INNER JOIN tours_types ON tours_info.type=tours_types.type_id
							WHERE date>='$beginRange' AND date<='$endRange' guides_needed>guides_scheduled
							ORDER BY date asc, time asc");

$list = "";
while ($tour = mysqli_fetch_array($tours)) {
	$list = $list."\n"."<tr><td>$tour['date']</td><td>$tour['type']</td></tr>"
}

$unfilledTours =
		'<table style="table">
			$list
		</table>'

?>
