<?
date_default_timezone_set('America/New_York');
$guideID = $_POST['guideID'];
require_once('../authenticate.php');

include("link.php");

$bigQuery = mysqli_query($link,"SET SQL_BIG_SELECTS=1;");

$currYear = date('Y');
//if the current date is before June 1st of the current year...
if (time()<mktime(0,0,0,6,1,$currYear)) {
	//must be spring semester
	$semester = 'spring';
	$startDate = date('Y-m-d',mktime(0,0,0,1,1,$currYear));
	$endDate = date('Y-m-d',mktime(0,0,0,6,1,$currYear));
	$tourReq = $igis_settings['tour_req_spring'];
	$hisReq = $igis_settings['his_req_spring'];
	$admReq = $igis_settings['adm_req_spring'];
	$TIPReq = $igis_settings['tip_req_spring'];
} else { //otherwise...
	//must be fall semester
	$semester = 'fall';
	$startDate = date('Y-m-d',mktime(0,0,0,6,2,$currYear));
	$endDate = date('Y-m-d',mktime(0,0,0,12,31,$currYear));
	$tourReq = $igis_settings['tour_req_fall'];
	$hisReq = $igis_settings['his_req_fall'];
	$admReq = $igis_settings['adm_req_fall'];
	$TIPReq = $igis_settings['tip_req_fall'];
}

//=============================================================================================
//================================== GET THE TOURS ============================================
//=============================================================================================
//Get list of all tours, highlighting this semester, pending credit or not:
$eitherCount = 0;
$admCount = 0;
$hisCount = 0;
$neitherCount = 0;
$totalEitherCount = 0;
$totalAdmCount = 0;
$totalHisCount = 0;
$totalNeitherCount = 0;
$uncreditedCount = 0;

$toursTable = '';

$guideName = mysqli_query($link, "SELECT firstname, lastname FROM guides WHERE guide_id=".$guideID);
$guideName = mysqli_fetch_array($guideName);
$toursQuery = mysqli_query($link, "SELECT date, time, name, status, adm_req, his_req, notes, tours_info.tour_id FROM tours_scheduled
										LEFT JOIN tours_handled ON (tours_handled.tour_id=tours_scheduled.tour_id AND tours_handled.guide_id=tours_scheduled.guide_id)
										INNER JOIN tours_info ON tours_info.tour_id=tours_scheduled.tour_id
										INNER JOIN tours_types ON tours_info.type=tours_types.type_id
										WHERE tours_scheduled.guide_id=".$guideID."
										ORDER BY date DESC, time DESC");
$toursError = mysqli_error($link);
$toursRows = '';
$season = 0;
$prevSeason = 0;
$numSemesters = 1; //of membership in the Guide Service; incremented every time semester count goes up
$runningHis = 0;
$runningAdm = 0;
$runningEither = 0;
$runningNeither = 0;
$i=mysqli_num_rows($toursQuery);
while($tour=mysqli_fetch_array($toursQuery)) {
	//figure out if the semester has changed to know whether to put in a bold divider line
	$year = date('Y',strtotime($tour['date']));
	if (date('n',strtotime($tour['date']))>=6) {
		$season = 'Fall'; //if the month is June or later, it's the fall semester
	} else {
		$season = 'Spring'; //otherwise it's the spring
	}
	if ($i==mysqli_num_rows($toursQuery)) {
		$prevSeason = $season; //if this is the first one, make sure the status is set to "equal to the previous one" so an extra bold line doesn't get drawn at the top
		$prevYear = $year;
	}

	if ($season != $prevSeason || $year != $prevYear) {
		//add a summary of the previous semester:
		$runningTotal = $runningHis+$runningAdm+$runningEither+$runningNeither;
		if($runningTotal==1){
			$pluralizeRunningTotal = "";
		} else {
			$pluralizeRunningTotal = "s";
		}
		$toursRows = $toursRows."<tr style=\"border-bottom:2px solid black;\"><td colspan=4 style=\"font-size:8pt; text-align:center; font-style:italic\">Total for ".$prevSeason." ".$prevYear.": <b>".$runningTotal."</b> tour".$pluralizeRunningTotal." (".$runningAdm." admissions, ".$runningHis." historical, ".$runningEither." that could be either, and ".$runningNeither." that didn't count)</td></tr>";
		//reset semester counts:
		$runningHis = 0;
		$runningAdm = 0;
		$runningEither = 0;
		$runningNeither = 0;
		$numSemesters++;
	}

	$prevSeason = $season; //save the current values to the new "previous" values for the next iteration
	$prevYear = $year;

	//add to list for table:
	if (strtotime($tour['date'].' '.$tour['time'])>strtotime($startDate)) {
		$backgroundStyle = "background-color:#EEEEFF;";
	} else {
		$backgroundStyle = "";
	}
	$tourRowStyle = "style=\"".$backgroundStyle."\"";

	if (strtotime($tour['date'])>time()) {
		$statusSpan = '<span style="font-style:italic; color:#7777FF">[future]</span>';
	} elseif ($tour['status']=='credited') {
		$statusSpan = '<span style="font-weight:bold; color:#009900">Credited</span>';
	} elseif ($tour['status']=='missed') {
		$statusSpan = '<span style="font-weight:bold; color:#FF0000">Missed</span>';
	} elseif (($tour['status']=='nocredit') ) {
		$statusSpan = '<span style="font-weight:bold; color:#997700">No Credit</span>';
	} else {
		$statusSpan = '<span style="font-weight:bold; color:#997700">[unknown]</span>';
		$uncreditedCount++;
	}
	$toursRows = $toursRows."<tr ".$tourRowStyle.">
								<td><span data-toggle=\"tooltip\" data-placement=\"left\" title=\"".$tour['tour_id']."\">#".$i."</span></td>
								<td><span data-toggle=\"tooltip\" data-placement=\"top\" title=\"".$tour['notes']."\">".date('M j Y',strtotime($tour['date'])).", ".date('g:i a',strtotime($tour['time']))."</span></td>
								<td>".$tour['name']."</td>
								<td>".$statusSpan."</td>
							</tr>";
	$i--;

	//Add to counts for this semester:
	if (strtotime($tour['date'].' '.$tour['time'])>strtotime($startDate) && $tour['status']=='credited') {
		if ($tour['adm_req']=='yes' && $tour['his_req']=='yes') {
			$eitherCount++;
		} else if ($tour['adm_req']=='yes') {
			$admCount++;
		} else if ($tour['his_req']=='yes') {
			$hisCount++;
		} else {
			$neitherCount++;
		}
	}

	//Add to counts for all time:
	if ($tour['status']=='credited') {
		if ($tour['adm_req']=='yes' && $tour['his_req']=='yes') {
			$totalEitherCount++;
			$runningEither++;
		} else if ($tour['adm_req']=='yes') {
			$totalAdmCount++;
			$runningAdm++;
		} else if ($tour['his_req']=='yes') {
			$totalHisCount++;
			$runningHis++;
		} else {
			$totalNeitherCount++;
			$runningNeither++;
		}
	}

}
//Add one last semester summary row:
$runningTotal = $runningHis+$runningAdm+$runningEither+$runningNeither;
if($runningTotal==1){
	$pluralizeRunningTotal = "";
} else {
	$pluralizeRunningTotal = "s";
}
$toursRows = $toursRows."<tr style=\"border-bottom:2px solid black;\"><td colspan=4 style=\"font-size:8pt; text-align:center; font-style:italic\">Total for ".$prevSeason." ".$prevYear.": <b>".$runningTotal."</b> tour".$pluralizeRunningTotal." (".$runningAdm." admissions, ".$runningHis." historical, ".$runningEither." that could be either, and ".$runningNeither." that didn't count)</td></tr>";

//Construct table:
$toursTable = "<table class=\"table\">
					<tr>
						<th></th>
						<th>Date/Time:</th>
						<th>Tour Type:</th>
						<th>Status:</th>
					</tr>
					".$toursRows."
				</table>";

//Construct all-time count:
$totalToursEver = $totalEitherCount+$totalAdmCount+$totalHisCount+$totalNeitherCount; //include the "neithers"

//Analyze this-semester tours
//Check whether they've fulfilled at least two of each type:
if ($admCount>=$admReq && $hisCount>=$hisReq) {
	$fulfilledAdmHis = true;
} else {
	//if the guide has given enough tours but not explicitly enough of each, check the shortfall...
	$shortfall=0;
	if($admCount<$admReq) {
		$shortfall += $admReq-$admCount;
	}
	if($hisCount<$hisReq) {
		$shortfall += $hisReq-$hisCount;
	}
	//...and then see if they have enough "either" tours to make up for it
	if ($eitherCount>=$shortfall) {
		$fulfilledAdmHis = true;
	} else {
		$fulfilledAdmHis = false;
	}
}
//Check whether they've fulfilled the total number:
$totalTours = $eitherCount+$admCount+$hisCount+$neitherCount;
if ($totalTours>=$tourReq) {
	if ($fulfilledAdmHis) {
		$fulfilled = true;
		$toursTotalColor = '#009900';
		$admHisMsg = 'and you have met the admissions/historical requirement.';
	} else {
		$fulfilled = false;
		$toursTotalColor = '#FF9900';
		$admHisMsg = 'but you have not yet met the admissions/historical requirement.';
	}
} else {
	$fulfilled = false;
	$toursTotalColor = '#FF9900';
	if ($totalTours<($admReq+$hisReq)) {
		$admHisMsg = 'so you also have not yet met the admissions/historical requirement.';
	} else if ($fulfilledAdmHis) {
		$admHisMsg = 'but you have met the admissions/historical requirement.';
	} else {
		$admHisMsg = 'and you have not yet met the admissions/historical requirement.';
	}
}
$totalToursThisSemester = '<span style="font-weight:bold; color:'.$toursTotalColor.'">'.$totalTours.'</span>';

if($totalTours==1) {
	$pluralizeTours = '';
} else {
	$pluralizeTours = 's';
}


$out['toursError'] = $toursError;

$out['guideFirstname'] = $guideName['firstname'];
$out['guideLastname'] = $guideName['lastname'];

$out['eitherCount'] = $eitherCount;
$out['admCount'] = $admCount;
$out['hisCount'] = $hisCount;
$out['neitherCount'] = $neitherCount;

$out['fulfilled'] = $fulfilled;
$out['fulfilledAdmHis'] = $fulfilledAdmHis;
$out['admHisMsg'] = $admHisMsg;
$out['toursTotalColor'] = $toursTotalColor;
$out['totalToursThisSemester'] = $totalToursThisSemester;
$out['pluralizeTours'] = $pluralizeTours;

$out['totalEitherCount'] = $totalEitherCount;
$out['totalAdmCount'] = $totalAdmCount;
$out['totalHisCount'] = $totalHisCount;
$out['totalNeitherCount'] = $totalNeitherCount;
$out['totalToursEver'] = $totalToursEver;
$out['uncreditedCount'] = $uncreditedCount;
$out['numSemesters'] = $numSemesters;

$out['toursTable'] = $toursTable;

echo json_encode($out);
?>
