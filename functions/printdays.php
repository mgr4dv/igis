<?
//This is called by the refreshDays() function in "signupsJavascript.php"
require_once("../authenticate.php");
//Include "genpopoups.php" because it has necessary functions for forming the various type of popovers that are inserted into labels:
include_once("genpopups.php");
//Set the local timezone so that all time operations will be in local time:
date_default_timezone_set('America/New_York');

$noDropWindow = $igis_settings['drop_time']; //number of hours inside which you can't drop a tour

//Grab variables from the given parameters in the AJAX GET URL as called by refreshDays() in "signupsJavascript.php"
$me = $_REQUEST["guide"]; //(integer) the ID of the guide who is viewing the page (used to make the user's name highlighted on the schedule)
$y = $_REQUEST["y"]; //(integer) the year of Day 1
$m = $_REQUEST["m"]; //(integer) the month of Day 1
$d = $_REQUEST["d"]; //(integer) the date of Day 1

//Create the tour table for each day using the function below: ("false" = without editing ability)
$day1 = printTours($y, $m, $d, false, $noDropWindow);
$day2 = printTours($y, $m, $d+1, false, $noDropWindow);
$day3 = printTours($y, $m, $d+2, false, $noDropWindow);
$day4 = printTours($y, $m, $d+3, false, $noDropWindow);
$day5 = printTours($y, $m, $d+4, false, $noDropWindow);
$day6 = printTours($y, $m, $d+5, false, $noDropWindow);
$day7 = printTours($y, $m, $d+6, false, $noDropWindow);

//Put the days' tour tables together into a larger Bootstrap-format grid, to be pasted directly in the "tourDays" well in "signups.php":
$output = "<div class=\"row\">
				<div class=\"col-md-3\" id=\"day1Tours\">$day1</div>
				<div class=\"col-md-3\" id=\"day2Tours\">$day2</div>
				<div class=\"col-md-3\" id=\"day3Tours\">$day3</div>
			</div>
			<div class=\"row\">
				<div class=\"col-md-3\" id=\"day4Tours\">$day4</div>
				<div class=\"col-md-3\" id=\"day5Tours\">$day5</div>
				<div class=\"col-md-3\" id=\"day6Tours\">$day6</div>
				<div class=\"col-md-3\" id=\"day7Tours\">$day7</div>
			</div>";

//Return the above assembled HTML:
echo $output;


//This is the function that generates one day's tour table:
function printTours($year, $month, $day, $withediting, $noDropWindow) {
	//Make the earlier variable "$me" accessible locally:
	global $me;
	//Establish a link to the database:
	include("link.php");
	//Set the date of this day (in a format readable by SQL):
	$date=date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
	//Grab all tours on this day from the database:
	$dbTours=mysqli_query($link,"SELECT tour_id, date, time, type, notes, guides_needed, guides_scheduled, abbrev
	FROM tours_info
	RIGHT JOIN tours_types ON tours_info.type=tours_types.type_id
	WHERE tours_info.date='$date'
	ORDER BY time asc, abbrev asc");

	//Get the day of the week (used for the title of the Bootstrap Panel which contains the tour table):
	$dayOfWeek=date("l", mktime(0, 0, 0, $month, $day, $year));
	//Make the header for the tour table (the date):
	$table_header="<tr><th colspan=2>". date("F j, Y", mktime(0, 0, 0, $month, $day, $year)) ."</th></tr>";

	//Start with an empty string for the body of the table:
	$tours="";

	//If there were no results for the tour query (meaning there are no tours on this day), make the body of the table say "No tours scheduled.":
	if (mysqli_num_rows($dbTours)==0) {
		if (mysqli_error($link)) {
			$tours="<tr><td colspan=2><strong>Database Error:</strong> \"".mysqli_error($link)."\"</td></tr>";
		} else {
			$tours="<tr><td colspan=2><em>No tours scheduled.</em></td></tr>";
		}
	}

	//Loop through each result of the tour query and construct that tour (note - this loop will not run at all if there were no results):
	while ($tour=mysqli_fetch_array($dbTours) ) {

		//Get the ID number of this tour (important for database interfacing);


		//Get time string to display as part of this tour:
		$time=date("g:i", strtotime($tour['time']));

		//Get information about this tour's time:
		$timeHour=date("H", strtotime($tour['time'])); //Get the hour of the tour
		$timeMin=date("i", strtotime($tour['time'])); //Get the minutes of the tour
		$tourTimestamp = mktime($timeHour, $timeMin, 0, $month, $day, $year); //Form a timestamp out of the tour time and this day's date
		//Get information about the current time:
		$localTimeArray = localtime(time(),true); //make associative array, then a timestamp
		$localTimestamp = mktime($localTimeArray['tm_hour'], $localTimeArray['tm_min'], $localTimeArray['tm_sec'], $localTimeArray['tm_mon']+1, $localTimeArray['tm_mday'], $localTimeArray['tm_year']+1900);
		//Get the interval between now and the future tour (for checking if dropping is okay, among other things):
		$interval = ($tourTimestamp-$localTimestamp)/3600; //(convert from seconds to hours)

		//Assume the guide is not already signed up, unless they are found later:
		$alreadySignedUp = false;

		//Grab the list of guides scheduled for this tour (different database):
		$tour_id = $tour['tour_id'];
		$reclist = mysqli_query($link,"SELECT * FROM tours_scheduled WHERE tour_id=$tour_id ORDER BY guide_lname ASC, guide_fname ASC");

		//Start out the list of guides that will go in the next element over from the tour label:
		$tour_spots="<td style=\"vertical-align:middle\">";

		$listOfIDs = ''; //Clear list of guide IDs for forming email
		$numEmailIDs = 0; //start with the assumption that there is only one guide
		for ($j=0; $guides_scheduled=mysqli_fetch_array($reclist); $j++){
			$guidePopup = genGuidePopup($guides_scheduled['guide_id']);
			$coverRequested=$guides_scheduled['cover_request'];
			if ($guides_scheduled['guide_id'] == $_SESSION['id']) { //allow for drop button and turn label green if this guide is the user
				if ($interval>$noDropWindow) { //only actually show drop button if the interval to the tour is outside the no-drop window
					$dropPopup = genDropPopup($_SESSION['id'],$tour_id);
					//$dropButton = "<span style=\"cursor:pointer\" class=\"label label-danger\" data-toggle=\"popover\" data-placement=\"top\" data-html=\"true\" data-content=\"$dropPopup\">X</span>";
					$dropButton = "<button class=\"btn btn-xs btn-danger\" data-toggle=\"popover\" data-placement=\"top\" data-html=\"true\" data-content=\"$dropPopup\">X</button>";
					$coverPopup = "";
					$coverButton = "";
				} else if ($interval>0) { //if the interval to the tour is inside the no-drop window (but still in the future), show the cover request button (and not the drop button)
					$dropPopup = "";
					$dropButton = "";
					if ($coverRequested){
						$coverPopup = genUndoCoverPopup($_SESSION['id'],$tour_id);
						//$coverButton = "<span style=\"cursor:pointer\" class=\"label label-warning\" data-toggle=\"popover\" data-placement=\"top\" data-html=\"true\" data-content=\"$coverPopup\">Reclaim</span>";
						$coverButton = "<button class=\"btn btn-xs btn-warning\" data-toggle=\"popover\" data-placement=\"top\" data-html=\"true\" data-content=\"$coverPopup\">Reclaim</button>";
					} else {
						$coverPopup = genRequestCoverPopup($_SESSION['id'],$tour_id);
						//$coverButton = "<span style=\"cursor:pointer\" class=\"label label-warning\" data-toggle=\"popover\" data-placement=\"top\" data-html=\"true\" data-content=\"$coverPopup\">...</span>";
						$coverButton = "<button class=\"btn btn-xs btn-warning\" data-toggle=\"popover\" data-placement=\"top\" data-html=\"true\" data-content=\"$coverPopup\">...</button>";
					}
				} else { //if the tour is in the past, don't show anything
					$dropPopup = "";
					$dropButton = "";
					$coverPopup = "";
					$coverButton = "";
				}
				$guideButtonColor = "success";
				$alreadySignedUp = true;
			} else { //if the guide is not the user...
				$listOfIDs = $listOfIDs.$guides_scheduled['guide_id'].",";
				$numEmailIDs++;
				$dropPopup = "";
				$dropButton = "";
				if ($coverRequested && $interval>0){ //only show if in the future
					$coverPopup = genCoverPopup($_SESSION['id'],$tour_id,$guides_scheduled['guide_id']);
					//$coverButton = "<small><em><span style=\"cursor:pointer\" class=\"label label-warning\" data-toggle=\"popover\" data-placement=\"top\" data-html=\"true\" data-content=\"$coverPopup\">Cover</span></em></small>";
					$coverButton = "<button class=\"btn btn-xs btn-warning\" data-toggle=\"popover\" data-placement=\"top\" data-html=\"true\" data-content=\"$coverPopup\"><em>Cover</em></button></em></small>";
				} else {
					$coverPopup = "";
					$coverButton = "";
				}
				$guideButtonColor = "info";
			}
			//$tour_spots=$tour_spots."<span style=\"cursor:pointer\" class=\"label label-$guideButtonColor\" data-toggle=\"popover\" data-placement=\"top\" data-html=\"true\" data-content=\"".$guidePopup."\">".substr($guides_scheduled['guide_fname'], 0, 1).". ".$guides_scheduled['guide_lname']."</span> $dropButton $coverButton<br>";
			$tour_spots=$tour_spots."<button style=\"line-height:17px; font-size:8pt; font-weight:bold;\" class=\"btn btn-xs btn-$guideButtonColor\" data-toggle=\"popover\" data-placement=\"top\" data-html=\"true\" title=\"<h4 style='margin:0px; font-weight:bold;'>".$guides_scheduled['guide_fname']." ".$guides_scheduled['guide_lname']."</h4>\" data-content=\"".$guidePopup."\">".substr($guides_scheduled['guide_fname'], 0, 1).". ".$guides_scheduled['guide_lname']."</button> $dropButton $coverButton<br>";
		}

		//Get the number of unfilled slots by subtracting thue number of scheduled guides from the number of slots in the tour:
		$unfilled_slots=$tour['guides_needed']-mysqli_num_rows($reclist);

		//Fill in the remaining unfilled slots with "[unfilled]":
		if ($interval>0 && !$alreadySignedUp) {
			//If it's in the future and the user is not already signed up, make it have a signup popover:
			for ($i=1; $i<=$unfilled_slots; $i++) {
				$signupPopup = genSignupPopup($me,$tour_id);
				//$tour_spots=$tour_spots."<span style=\"cursor:pointer\" class=\"label label-danger\" data-toggle=\"popover\" data-placement=\"top\" data-html=\"true\" data-content=\"".$signupPopup."\">[unfilled]</span><br>";
				$tour_spots=$tour_spots."<button style=\"line-height:17px; font-size:8pt; font-weight:bold;\" class=\"btn btn-xs btn-danger\" data-toggle=\"popover\" data-placement=\"top\" data-html=\"true\" data-content=\"".$signupPopup."\">[unfilled]</button><br>";
			}
		} else {
			//If it's in the past or the user is already signed up, just make it a label
			for ($i=1; $i<=$unfilled_slots; $i++) {
				$tour_spots=$tour_spots."<span style=\"cursor:default\" class=\"label label-default\">[unfilled]</span><br>";
			}
		}
		//Close out the tour spots cell:
		$tour_spots = $tour_spots."</td>";

		//Make email button (only if it's within an hour, unless the user is scheduler, tech chair, or chair; also the recipient list must not be blank)
		$listOfIDs = substr($listOfIDs, 0, -1);
		if ($numEmailIDs > 1) {
			$pluralizeEmailButton = 's';
		} else {
			$pluralizeEmailButton = '';
		}
		global $is_scheduler, $is_techchair, $is_chair;
		if ((abs($interval)<1 || $is_scheduler || $is_techchair || $is_chair) && $listOfIDs!=''){ //$listOfIDs will only be blank if nobody is signed up, or only the user is signed up
			$emailButton = "<br><button class=\"btn btn-xs btn-default\" onclick=\"emailGuides('".$listOfIDs."','".$_SESSION['id']."','".$time." ".$tour['abbrev']."')\">Email Guide".$pluralizeEmailButton."</button>";
		} else {
			$emailButton = "";
		}

		//Create the tour label, with time and type (ex: "10:00 AR"), linking to a popover if there are notes associated with that tour, and filling in the email-guides button:
		if (!empty($tour['notes']) AND $tour['notes']!='') {
			$notes=genNotesPopup($tour['notes']);
			$tour_label="<td><button style=\"line-height:17px; font-size:10.5pt; font-weight:bold; padding-top:3px; padding-bottom:3px; padding-left:7px; padding-right:7px\" class=\"btn btn-sm btn-primary\" data-toggle=\"popover\" data-placement=\"top\" data-html=\"true\" data-content=\"".$notes."\"><u>$time ".$tour['abbrev']."</u></button>".$emailButton."</td>";
		} else {
			$tour_label="<td><span style=\"cursor:default; display:inline-block; line-height:17px; font-size:10.5pt\" class=\"label label-primary\">$time ".$tour['abbrev']."</span>".$emailButton."</td>";
		}

		//Assemble this tour into its final package (ie, one row of the tour table for this day):
		$this_tour="<tr>".$tour_label.$tour_spots."</tr>\n";
		//Add this tour's row into the running tour table body:
		$tours=$tours.$this_tour;
	}


	//Test to see if this date is present/past/future to set the color of the large panel appropriately:
	$today=date("Y-m-d"); //same format as $date was originally set
	if ($date==$today) $daycolor="primary"; //dark blue if it's today
	elseif ($date<$today) $daycolor="warning"; //light yellow if it's in the past
	else $daycolor="info"; //light blue if it's in the future (the only possibility left)

	//Construct the whole panel, containing the day as panel heading, the table header (date) as table heading, and the table of tours as table body:
	$entire_day="
	<div class=\"panel panel-$daycolor\">\n
		<div class=\"panel-heading\"><h4 style=\"margin:2px\"><em>$dayOfWeek</em></h4></div>\n
		<table class=\"table\">\n
			<thead>\n$table_header\n</thead>\n
			<tbody>\n$tours\n</tbody>\n
		</table>\n
	</div>";

	//Return that panel for insertion into the HTML template at the top of this file
	return $entire_day;
}







?>
