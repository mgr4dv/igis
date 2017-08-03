<?
//This is called by the refreshDays() function in "defineregulartours.php". It is identical to "printdays.php" except for the actual content.

//Include "genpopoups.php" because it has necessary functions for forming the various type of popovers that are inserted into labels:
include("genpopups.php");
//Resume the session to be able to grab user data (this file is called in isolation, so the session doesn't automatically carry over):
session_start();
//Set the local timezone so that all time operations will be in local time:
date_default_timezone_set('America/New_York');

//Create the tour table for each day using the function below: ("false" = without editing ability)
$day1 = printTours('Monday');
$day2 = printTours('Tuesday');
$day3 = printTours('Wednesday');
$day4 = printTours('Thursday');
$day5 = printTours('Friday');
$day6 = printTours('Saturday');
$day7 = printTours('Sunday');

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
function printTours($dayOfWeek) {
	//Make the earlier variable "$me" accessible locally:
	global $me;
	//Establish a link to the database:
	include("link.php");
	//Grab all tours on this day from the database:
	$dbTours=mysqli_query($link,"SELECT * FROM tours_reg RIGHT JOIN tours_types ON tours_reg.tour_type=tours_types.type_id WHERE day='$dayOfWeek' ORDER BY time asc, abbrev asc");

	//Start with an empty string for the body of the table:
	$tours="";

	//If there were no results for the tour query (meaning there are no tours on this day), make the body of the table say "No tours scheduled.":
	if (mysqli_num_rows($dbTours)==0) {
		$tours="<tr><td colspan=2><em>No regular tours scheduled.</em></td></tr>";
	}

	//Loop through each result of the tour query and construct that tour (note - this loop will not run at all if there were no results):
	while ($tour=mysqli_fetch_array($dbTours) ) {

		//Get the ID number of this tour (important for database interfacing);


		//Get time string to display as part of this tour:
		$time=date("g:i", strtotime($tour['time']));

		//Create the tour label, with time and type (ex: "10:00 AR"), linking to an edit popover:
		$editTourPopup=genEditRegTourPopup($tour['reg_id']);
		$tour_label="<td><button style=\"line-height:17px; font-size:10.5pt; font-weight:bold; padding-top:3px; padding-bottom:3px; padding-left:7px; padding-right:7px\" class=\"btn btn-sm btn-primary\" data-toggle=\"popover\" data-placement=\"top\" data-html=\"true\" data-content=\"$editTourPopup\"><u>$time ".$tour['abbrev']."</u></button></td>";

		//Start out the list of slots that will go in the next element over from the tour label:
		$tour_spots="<td style=\"vertical-align:middle\">";

		//Get the number of tour slots:
		$unfilled_slots=$tour['guides_needed'];

		//Fill in the remaining unfilled slots with "[unfilled]":{
		for ($i=1; $i<=$unfilled_slots; $i++) {
			$tour_spots=$tour_spots."<span style=\"cursor:default\" class=\"label label-default\">[ slot ]</a></span><br>";
		}
		//Close out the tour spots cell:
		$tour_spots = $tour_spots."</td>";

		//Assemble this tour into its final package (ie, one row of the tour table for this day):
		$this_tour="<tr>".$tour_label.$tour_spots."</tr>\n";
		//Add this tour's row into the running tour table body:
		$tours=$tours.$this_tour;
	}


	$daycolor="info"; //light blue, like it's in the future
	//Construct the whole panel, containing the day as panel heading, the table header (date) as table heading, and the table of tours as table body:
	$entire_day="
	<div class=\"panel panel-$daycolor\">\n
		<div class=\"panel-heading\"><h4 style=\"margin:2px\"><em>$dayOfWeek</em></h4></div>\n
		<table class=\"table\">\n
			<tbody>\n$tours\n</tbody>\n
		</table>\n
	</div>";

	//Return that panel for insertion into the HTML template at the top of this file
	return $entire_day;
}







?>
