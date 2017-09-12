<?
date_default_timezone_set('America/New_York');
require_once("authenticate.php");

$me = $_SESSION['id'];
//Include "genpopoups.php" because it has necessary functions for forming the various type of popovers that are inserted into labels:
include_once("functions/genpopups.php");
//Set the local timezone so that all time operations will be in local time:
date_default_timezone_set('America/New_York');

$unfilledDays = $igis_settings['unfilled_tour_display_days']; //number of days to print unfilled tours
$noDropWindow = intval($igis_settings['drop_time']); //number of hours inside which you can't drop a tour
$coverSearchDays = ceil($noDropWindow/24); //number of days out to search for cover request tours
$noUnfilledToursMsg = "There are no unfilled tours in the next ".$unfilledDays." days.";
$noUnfilledOhMsg = "There are no unfilled OHs in the next ".$unfilledDays." days.";
$notSignedUpMsg = "You are not signed up for any tours right now.";
$notSignedUpOhMsg = "You are not signed up for any OHs right now.";

$today = date("Y-m-d"); //today
$endRange = date("Y-m-d", strtotime("+".$unfilledDays." days")); //$unfilledDays days from now
$endCoverRange = date("Y-m-d", strtotime("+".$coverSearchDays." days")); //$coverSearchDays days from now


//GET UNFILLED TOURS:
$tours=mysqli_query($link, "SELECT tour_id, date, time, notes, abbrev, guides_needed, guides_scheduled FROM tours_info
							INNER JOIN tours_types ON tours_info.type=tours_types.type_id
							WHERE date>='$today' AND date<='$endRange' AND guides_needed>guides_scheduled
							ORDER BY date asc, time asc");

$noUnfilledPlaceholder = "<tr><td colspan=2><em>".$noUnfilledToursMsg."</td></tr>";
$lastDate = "";
$list = "";
while ($tour = mysqli_fetch_array($tours)) {
	if (strtotime($tour['date'].' '.$tour['time'])<time()) {
		continue; //skip this tour if it's in the past (like a tour that was earlier today)
	}
	if ($tour['date']!=$lastDate) {
		//Add a date header if this tour is on a new day
		if ($tour['date']==date('Y-m-d')) {
			$list = $list."<tr><td colspan=2><span style=\"font-weight:bold; font-style:italic; color:#CC0000\">TODAY, ".date('F jS',strtotime($tour['date']))."</span></td></tr>"; //special date header if it's today
		} else {
			$list = $list."<tr><td colspan=2><span style=\"font-weight:bold; font-style:italic; color:#000000\">".date('l, F jS',strtotime($tour['date']))."</span></td></tr>";
		}
	}
	$time = date('g:i',strtotime($tour['time']));
	$reclist = mysqli_query($link,"SELECT * FROM tours_scheduled WHERE tour_id=".$tour['tour_id']." ORDER BY guide_lname ASC, guide_fname ASC");

	$num_unfilled = $tour['guides_needed']-mysqli_num_rows($reclist);
	if ($num_unfilled>1) {
		$pluralize="s";
	} else {
		$pluralize="";
	}
	if (!empty($tour['notes']) AND $tour['notes']!='') {
		$notes=genNotesPopup($tour['notes']);
		$tour_label="<button style=\"line-height:17px; font-size:10.5pt; font-weight:bold; padding-top:3px; padding-bottom:3px; padding-left:7px; padding-right:7px\" class=\"btn btn-sm btn-primary\" data-toggle=\"popover\" data-placement=\"top\" data-html=\"true\" data-content=\"".$notes."\"><u>$time ".$tour['abbrev']."</u></button>";
	} else {
		$tour_label="<span style=\"cursor:default; display:inline-block; line-height:17px; font-size:10.5pt\" class=\"label label-primary\">$time ".$tour['abbrev']."</span>";
	}
	$alreadySignedUp=mysqli_query($link, "SELECT guide_id FROM tours_scheduled WHERE tour_id=".$tour['tour_id']." AND guide_id=$me");
	$alreadySignedUp = (mysqli_num_rows($alreadySignedUp)); //transform the query result into an effective boolean of whether the guide is signed up
	$signupPopup = genSignupPopup($me,$tour['tour_id']);
	if (!$alreadySignedUp) {
		$signup_label = "<button style=\"line-height:17px; font-size:8pt; font-weight:bold;\" class=\"btn btn-xs btn-danger\" data-toggle=\"popover\" data-placement=\"top\" data-html=\"true\" data-content=\"".$signupPopup."\">".$num_unfilled." unfilled spot".$pluralize."</button>";
	} else {
		$signup_label = "<span style=\"cursor:default\" class=\"label label-default\">".$num_unfilled." unfilled spot".$pluralize."</span>";
	}
	$list = $list."\n"."<tr><td style=\"vertical-align:middle; padding-top:0px; padding-bottom:0px\">".$tour_label."</td><td style=\"vertical-align:middle\">".$signup_label."</td></tr>";
	$lastDate = $tour['date'];
}
if ($list=='') {
	$list = $noUnfilledPlaceholder;
}
$unfilledTours =
		'<table class="table">
			'.mysqli_error($link).$list.'
		</table> ';

$coverList='';
$coverRequests = mysqli_query($link,"SELECT tours_scheduled.tour_id, date, time, notes, abbrev, guide_fname, guide_lname, guide_id FROM tours_scheduled
									INNER JOIN tours_info ON tours_info.tour_id=tours_scheduled.tour_id
									INNER JOIN tours_types ON tours_info.type=tours_types.type_id
									WHERE date>='$today' AND date<='$endCoverRange' AND cover_request=1
									ORDER BY date asc, time asc");

$numCoverRequests = mysqli_num_rows($coverRequests);
if ($numCoverRequests) {
	$lastDate='';
	while ($coverReq=mysqli_fetch_array($coverRequests)) {
		if (strtotime($coverReq['date'].' '.$coverReq['time'])<time()) {
			$numCoverRequests--;
			continue; //skip this tour if it's in the past (like a tour that was earlier today)
		}
		if ($coverReq['date']!=$lastDate) {
			//Add a date header if this tour is on a new day
			if ($coverReq['date']==date('Y-m-d')) {
				$coverList = $coverList."<tr><td colspan=2><span style=\"font-weight:bold; font-style:italic; color:#CC0000\">TODAY, ".date('F jS',strtotime($coverReq['date']))."</span></td></tr>"; //special date header if it's today
			} else {
				$coverList = $coverList."<tr><td colspan=2><span style=\"font-weight:bold; font-style:italic; color:#000000\">".date('l, F jS',strtotime($coverReq['date']))."</span></td></tr>";
			}
		}
		$time = date('g:i',strtotime($coverReq['time']));
		if (!empty($coverReq['notes']) AND $coverReq['notes']!='') {
			$notes=genNotesPopup($coverReq['notes']);
			$tour_label="<button style=\"line-height:17px; font-size:10.5pt; font-weight:bold; padding-top:3px; padding-bottom:3px; padding-left:7px; padding-right:7px\" class=\"btn btn-sm btn-primary\" data-toggle=\"popover\" data-placement=\"top\" data-html=\"true\" data-content=\"".$notes."\"><u>".$time." ".$coverReq['abbrev']."</u></button>";
		} else {
			$tour_label="<span style=\"cursor:default; display:inline-block; line-height:17px; font-size:10.5pt\" class=\"label label-primary\">".$time." ".$coverReq['abbrev']."</span>";
		}
		$alreadySignedUp=mysqli_query($link, "SELECT guide_id FROM tours_scheduled WHERE tour_id=".$coverReq['tour_id']." AND guide_id=$me");
		$alreadySignedUp = (mysqli_num_rows($alreadySignedUp)); //transform the query result into an effective boolean of whether the guide is signed up
		$coverPopup = genCoverPopup($me,$coverReq['tour_id'],$coverReq['guide_id']);
		if (!$alreadySignedUp) {
			$cover_label = "<button style=\"line-height:17px; font-size:8pt; font-weight:bold;\" class=\"btn btn-xs btn-warning\" data-toggle=\"popover\" data-placement=\"top\" data-html=\"true\" data-content=\"".$coverPopup."\">".$coverReq['guide_fname']." ".$coverReq['guide_lname']."</button>";
		} else {
			$cover_label = "<span style=\"cursor:default\" class=\"label label-default\">".$coverReq['guide_fname']." ".$coverReq['guide_lname']."</span>";
		}
		$coverList = $coverList."\n"."<tr><td style=\"vertical-align:middle; padding-top:0px; padding-bottom:0px\">".$tour_label."</td><td style=\"vertical-align:middle\">".$cover_label."</td></tr>";
		$lastDate = $coverReq['date'];
	}
}
if ($coverList!='' && $numCoverRequests) {
	$coverRequestTable =
		'<p style="font-weight:bold; text-align:center">Active Cover Requests:</p>
		<table class="table">
			'.mysqli_error($link).$coverList.'
		</table>';
} else {
	$coverRequestTable = '';
}

//GET OH COVERS

$ohcoverRequests = mysqli_query($link,"SELECT * FROM oh_log
									LEFT JOIN guides ON guides.guide_id=oh_log.cover_id
									WHERE sch_time>='$today' AND cover=1
									ORDER BY sch_time");
$ohcoverList = "";

$ohnumCoverRequests = mysqli_num_rows($ohcoverRequests);
if ($ohnumCoverRequests) {
	$lastDate='';
	while ($ohcoverReq=mysqli_fetch_array($ohcoverRequests)) {
		if (strtotime($ohcoverReq['sch_time'])<time()) {
			$ohnumCoverRequests--;
			continue; //skip this tour if it's in the past (like a tour that was earlier today)
		}
		if ($ohcoverReq['sch_time']!=$lastDate) {
			//Add a date header if this tour is on a new day
		$ohcoverList = $ohcoverList."<tr><td colspan=2><span
style=\"font-weight:bold; font-style:italic; color:#000000\">".date('l, F
jS',strtotime($ohcoverReq['sch_time']))."</span></td></tr>";

		}
		$time = date('g:i
a',strtotime($ohcoverReq['sch_time']));
		$oh_label="<span style=\"cursor:default; display:inline-block; line-height:17px; font-size:10.5pt\" class=\"label label-primary\">".$time."</span>";

		$alreadySignedUp=mysqli_query($link, "SELECT cover_id FROM oh_log WHERE log_id=".$ohcoverReq['log_id']." AND
cover_id=$me");
		$alreadySignedUp = (mysqli_num_rows($alreadySignedUp)); //transform the query result into an effective boolean of whether the guide is signed up
		$ohcoverPopup = genOhCoverPopup($me,$ohcoverReq['log_id'],$ohcoverReq['guide_id']);
		if (!$alreadySignedUp) {
			$ohcover_label = "<button style=\"line-height:17px; font-size:8pt; font-weight:bold;\" class=\"btn btn-xs btn-warning\"
data-toggle=\"popover\" data-placement=\"top\" data-html=\"true\" data-content=\"".$ohcoverPopup."\">".$ohcoverReq['firstname']."
".$ohcoverReq['lastname']."</button>";
		} else {
			$ohcover_label = "<span style=\"cursor:default\" class=\"label
label-default\">".$ohcoverReq['firstname']." ".$ohcoverReq['lastname']."</span>";
		}
		$ohcoverList = $ohcoverList."\n"."<tr><td
style=\"vertical-align:middle; padding-top:0px; padding-bottom:0px\">".$oh_label."</td><td style=\"vertical-align:middle\">".$ohcover_label."</td></tr>";
		$lastDate = $ohcoverReq['sch_time'];
	}
}
if ($ohcoverList!='' && $ohnumCoverRequests) {
	$ohcoverRequestTable =
		'<p style="font-weight:bold; text-align:center">Active Cover Requests:</p>
		<table class="table">
			'.mysqli_error($link).$ohcoverList.'
		</table>';
} else {
	$ohcoverRequestTable = "<tr><td colspan=3><em>No active OH covers</em></td></tr>";;
}


//GET MY TOURS:
$tours=mysqli_query($link, "SELECT tours_scheduled.tour_id, date, time, type, notes, abbrev, name FROM tours_scheduled
							INNER JOIN tours_info on tours_info.tour_id=tours_scheduled.tour_id
							INNER JOIN tours_types ON tours_info.type=tours_types.type_id
							WHERE tours_scheduled.guide_id=$me AND tours_info.date>='$today'
							ORDER BY date asc, time asc");
if (mysqli_num_rows($tours)==0) {
	$list = "<tr><td colspan=3><em>".$notSignedUpMsg."</em></td></tr>";
} else {
	$lastDate = "";
	$list = "";
	while ($tour = mysqli_fetch_array($tours)) {
		if (strtotime($tour['date'].' '.$tour['time'])<time()) {
			continue; //skip this tour if it's in the past (like a tour that was earlier today)
		}
		//Get information about this tour's time:
		$timeHour=date("H", strtotime($tour['time'])); //Get the hour of the tour
		$timeMin=date("i", strtotime($tour['time'])); //Get the minutes of the tour
		$month=date("n", strtotime($tour['date'])); //Get the month of the tour
		$day=date("j", strtotime($tour['date'])); //Get the date of the tour
		$year=date("Y", strtotime($tour['date'])); //Get the year of the tour
		$tourTimestamp = mktime($timeHour, $timeMin, 0, $month, $day, $year); //Form a timestamp out of the tour time and this day's date
		//Get information about the current time:
		$localTimeArray = localtime(time(),true); //make associative array, then a timestamp
		$localTimestamp = mktime($localTimeArray['tm_hour'], $localTimeArray['tm_min'], $localTimeArray['tm_sec'], $localTimeArray['tm_mon']+1, $localTimeArray['tm_mday'], $localTimeArray['tm_year']+1900);
		//Get the interval between now and the future tour (for checking if dropping is okay, among other things):
		$interval = ($tourTimestamp-$localTimestamp)/3600; //(convert from seconds to hours)

		//Get cover status:
		$cover_query = mysqli_query($link,"SELECT cover_request FROM tours_scheduled WHERE tour_id=".$tour['tour_id']." AND guide_id=$me ORDER BY guide_lname ASC, guide_fname ASC");
		$cover_request = mysqli_fetch_array($cover_query);

		if ($tour['date']!=$lastDate) {
			//Add a date header if this tour is on a new day
			$list = $list."<tr><td colspan=3><strong><em>".date('l, F jS',strtotime($tour['date']))."</strong></em></td></tr>";
		}
		$time = date('g:i',strtotime($tour['time']));

		if (!empty($tour['notes']) AND $tour['notes']!='') {
			$notes=genNotesPopup($tour['notes']);
			$tour_label="<button style=\"line-height:17px; font-size:10.5pt; font-weight:bold; padding-top:3px; padding-bottom:3px; padding-left:7px; padding-right:7px\" class=\"btn btn-sm btn-primary\" data-toggle=\"popover\" data-placement=\"top\" data-html=\"true\" data-content=\"".$notes."\"><u>$time ".$tour['abbrev']."</u></button>";
		} else {
			$tour_label="<span style=\"cursor:default; display:inline-block; line-height:17px; font-size:11pt\" class=\"label label-primary\">".$time." ".$tour['abbrev']."</span>";
		}
		$tour_label = $tour_label.mysqli_error($link);

		if ($interval > $noDropWindow) {
			$dropPopup = genDropPopup($me,$tour['tour_id']);
			$drop_label = "<button class=\"btn btn-xs btn-danger\" data-toggle=\"popover\" data-placement=\"top\" data-html=\"true\" data-content=\"".$dropPopup."\">Drop</button>";
		} else {
			if ($cover_request[0]) {
				$drop_label = "<button class=\"btn btn-warning btn-xs\" onclick=\"undoRequestCover($me,".$tour['tour_id'].")\">Reclaim Tour</button>";
			} else {
				$drop_label = "<button class=\"btn btn-warning btn-xs\" onclick=\"requestCover($me,".$tour['tour_id'].")\">Request Cover</button>";
			}
		}

		$startTimeStamp = strtotime($tour['date'].' '.$tour['time']);
		$endTimeStamp = strtotime('+75 minutes',$startTimeStamp);
		$calendar_date = gmdate('Ymd',$startTimeStamp).'T'.gmdate('Hi',$startTimeStamp).'00Z/'.gmdate('Ymd',$endTimeStamp).'T'.gmdate('Hi',$endTimeStamp).'00Z';
		$calendar_url = "http://www.google.com/calendar/event?
						action=TEMPLATE
						&text=".$tour['name']." tour
						&dates=".$calendar_date."
						&details=".$tour['notes']."
						&location=
						&trp=false
						&sprop=
						&sprop=name:";
		$calendar_label = '<a href="'.$calendar_url.'" target="_blank"><button class="btn btn-sm btn-default" data-toggle="tooltip" data-placement="right" title="Add to Google Calendar"><span class="glyphicon glyphicon-calendar"></span></button></a>';
		$list = $list."\n"."<tr><td style=\"vertical-align:middle; padding-top:0px; padding-bottom:0px\">".$tour_label."</td><td style=\"vertical-align:middle\">".$drop_label."</td><td>".$calendar_label."</td></tr>";
		$lastDate = $tour['date'];
	}
}

$myTours =
		'<table class="table">
			'.mysqli_error($link).$list.'
		</table>';

$lastDate = "";
$list = "";
// Get my OH
$ohs=mysqli_query($link, "SELECT * FROM oh_log
							WHERE cover_id=$me AND DATE(sch_time)>='$today'
							ORDER BY sch_time asc");
if (mysqli_num_rows($ohs)==0) {
	$list = "<tr><td colspan=3><em>".$notSignedUpOhMsg."</em></td></tr>";
} else {
	$lastDate = "";
	$list = "";
	while ($oh = mysqli_fetch_array($ohs)) {
		if (strtotime($oh['sch_time'])<time()) {
			continue; //skip this tour if it's in the past (like a tour that was earlier today)
		}
		//Get information about this tour's time:
		$timeHour=date("H", strtotime($oh['sch_time'])); //Get the hour of the tour
		$timeMin=date("i", strtotime($oh['sch_time'])); //Get the minutes of the tour
		$month=date("n", strtotime($oh['sch_time'])); //Get the month of the tour
		$day=date("j", strtotime($oh['sch_time'])); //Get the date of the tour
		$year=date("Y", strtotime($oh['sch_time'])); //Get the year of the tour
		$tourTimestamp = mktime($timeHour, $timeMin, 0, $month, $day, $year); //Form a timestamp out of the tour time and this day's date
		//Get information about the current time:
		$localTimeArray = localtime(time(),true); //make associative array, then a timestamp
		$localTimestamp = mktime($localTimeArray['tm_hour'], $localTimeArray['tm_min'], $localTimeArray['tm_sec'], $localTimeArray['tm_mon']+1, $localTimeArray['tm_mday'], $localTimeArray['tm_year']+1900);
		//Get the interval between now and the future tour (for checking if dropping is okay, among other things):
		$interval = ($tourTimestamp-$localTimestamp)/3600; //(convert from seconds to hours)

		//Get cover status:
		$cover_request = $oh['cover'];
		if ($oh['sch_time']!=$lastDate) {
			//Add a date header if this tour is on a new day
			$list = $list."<tr><td colspan=3><strong><em>".date('l, F jS',strtotime($oh['sch_time']))."</strong></em></td></tr>";
		}
		$oh_date = date('g:i a',strtotime($oh['sch_time']));

		$oh_label="<span style=\"cursor:default; display:inline-block; line-height:17px; font-size:11pt\" class=\"label label-primary\">".$oh_date."</span>";

		$oh_label = $oh_label.mysqli_error($link);

		if ($cover_request) {
			$drop_label = "<button class=\"btn btn-warning btn-xs\" onclick=\"undoRequestOhCover($me,".$oh['log_id'].")\">Reclaim Oh</button>";
		} else {
			$drop_label = "<button class=\"btn btn-warning btn-xs\" onclick=\"requestOhCover($me,".$oh['log_id'].")\">Request Cover</button>";
		}


		$startTimeStamp = strtotime($oh['sch_time']);
		$endTimeStamp = strtotime('+75 minutes',$startTimeStamp);
		$list = $list."\n"."<tr><td style=\"vertical-align:middle; padding-top:0px; padding-bottom:0px\">".$oh_label."</td><td style=\"vertical-align:middle\">".$drop_label."</td></tr>";
		$lastDate = $oh['sch_time'];
	}
}
$myOhs =
		'<table class="table">
			'.mysqli_error($link).$list.'
		</table>';

$execAgendaURL = $igis_settings['exec_agenda_url'];
$execMinutesURL = $igis_settings['exec_minutes_url'];
$execMessage = $igis_settings['exec_homepage_msg'];
$constitutionURL = $igis_settings['constitution_url'];
$bylawsURL = $igis_settings['bylaws_url'];

$homepageAlertText = $igis_settings['homepage_alert'];
$homepageAlertColor = $igis_settings['homepage_alert_color'];
?>
<!DOCTYPE html>
<html lang="en">

<!-- Header information for webpage (reused except for title) -->
	<head>
		<?
		include_once("includes/head.php");
		?>
		<style>
			.responsive-iframe-container {
				position: relative;
				padding-bottom: 56.25%;
				padding-top: 30px;
				height: 0;
				overflow: hidden;
			}

			.responsive-iframe-container iframe,
			.responsive-iframe-container object,
			.responsive-iframe-container embed {
				position: absolute;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
			}
		</style>
	</head>

<!-- Body of webpage (not reused, but reusable elements inside) -->
	<body>

		<!-- Navigation bar across the top and footer across the bottom -->
		<?include_once("includes/nav.php")?>
		<?include_once("includes/footer.php")?>


			<div class="container">
			<div class="page-header" style="margin-top:0px">
				<h1>Welcome to IGIS 3.0!</h1>
			</div>
			</div>



		<div class="container">
		<? if ($homepageAlertText!='') {
			echo '<div class="alert alert-'.$homepageAlertColor.'" role="alert">'.$homepageAlertText.'</div>';
		}?>
		<div class="well">
			<div class="row">
				<div class="col-md-2"></div>
				<div class="col-md-4">
					<div class="panel panel-danger">
						<div class="panel-heading"><h4 style="margin:2px"><em>Unfilled Tours</em></h4></div>
						<div class="panel-body" id="unfilledToursDiv">
							<?echo $unfilledTours?>
							<?echo $coverRequestTable?>
						</div>
					</div>
				</div>
				<div class="col-md-4">
					<div class="panel panel-info">
						<div class="panel-heading"><h4 style="margin:2px"><em>Your Tours</em></h4></div>
						<div class="panel-body" id="myToursDiv">
							<?echo $myTours?>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-2"></div>
				<div class="col-md-4">
					<div class="panel panel-info">
						<div class="panel-heading"><h4 style="margin:2px"><em>Active OH Cover Requests</em></h4></div>
						<div class="panel-body" id="activeOhCoversDiv">
							<?echo $ohcoverRequestTable?>
						</div>
					</div>
				</div>
				<div class="col-md-4">
					<div class="panel panel-info">
						<div class="panel-heading"><h4 style="margin:2px"><em>Your Office Hours</em></h4></div>
						<div class="panel-body" id="myOhCovers">
							<?echo $myOhs?>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-2"></div>
				<div class="col-md-8">
					<ul class="list-group">
						<a href="tourrequest.php" class="list-group-item" style="text-align:center">Take a tour request</a>
					</ul>
				</div>
			</div>
			<div class="row">
				<div class="col-md-2"></div>
				<div class="col-md-2">
					<h4>Cover and Credit Forms</h4>
					<ul class="list-group">
						<a href="https://docs.google.com/a/virginia.edu/forms/d/1aRYY1y9Z6ETvvMFuTBpmm5cobWUFGjDn1K2KKfxh-xs/viewform" target="_blank" class="list-group-item">Tour Cover</a>
						<a href="https://goo.gl/forms/S9edkxDEjh5u5z5B2" target="_blank" class="list-group-item">Office Hour Cover</a>
						<a href="https://docs.google.com/forms/d/1mCxcAAb2mHmHuaeOse_7UlYTfLh82cZWO3y_30TQ27U/viewform" target="_blank" class="list-group-item">TIP Credit</a>
					</ul>
				</div>
				<div class="col-md-3">
					<h4>Exec Documents</h4>
					<ul class="list-group" style="margin-bottom:5px">
						<a href="<?echo $execAgendaURL?>" target="_blank" class="list-group-item">Upcoming Exec Agenda</a>
						<a href="<?echo $execMinutesURL?>" target="_blank" class="list-group-item">Last Week's Minutes</a>
					</ul>
					<p style="margin-left:10px; margin-right:10px"><?echo $execMessage?></p>
				</div>
				<div class="col-md-3">
					<h4>Governing Documents</h4>
					<ul class="list-group">
						<a href="<?echo $constitutionURL?>" target="_blank" class="list-group-item">UGS Constitution</a>
						<a href="<?echo $bylawsURL?>" target="_blank" class="list-group-item">UGS Bylaws</a>
					</ul>
				</div>
			</div>
			<div class="row">
				<div class="col-md-1"></div>
				<div class="col-md-10">
					<div class="panel panel-info">
						<div class="panel-heading" onclick="toggleCalendar()" style="cursor:pointer">
							<h4 class="panel-title">
								Calendar
							</h4>
						</div>
						<div id="calendarPanelBody" class="panel-body" style="text-align:center">

								<!-- Responsive iFrame -->
								<div class="responsive-iframe-container">
								<iframe src="https://www.google.com/calendar/embed?showTitle=0&amp;showCalendars=0&amp;height=600&amp;wkst=1&amp;bgcolor=%23FFFFFF&amp;src=uguidestech%40gmail.com&amp;color=%232952A3&amp;src=clisodek4ucpcmkoo37sgp7r8o%40group.calendar.google.com&amp;color=%23B1440E&amp;src=igiscalendar%40gmail.com&amp;color=%232F6309&amp;ctz=America%2FNew_York" style="border-width:0" width="100%" height="100%" frameborder="0" scrolling="no"></iframe>
								</div>

						</div>
					</div>
				</div>
			</div>
		</div>
		</div>


	</body>

	<script>
		$(function () {

			//$('#calendarPanelBody').hide();

			$('[data-toggle="tooltip"]').tooltip();

			$('[data-toggle="popover"]').popover({
				trigger:'focus',
				delay: { show: 0, hide: 200 } //hide delay is so that the popover doesn't disappear right away when clicking a button inside it
			});

			//Whenever a button is clicked, assign it focus if it doesn't already have it:
			//(this is necessary because some browsers, like Firefox/Safari on Mac OS X, don't automatically assign focus to a clicked button)
			$('button').click( function() {
				if (!$(this).is(":focus")) {
					$(this).focus();
				}
			});
		});

		function toggleCalendar() {
			$('#calendarPanelBody').toggle();
		}

		function signup(guideID, tourID) {
			$('#unfilledToursDiv').html('<h4 style="text-align:center">Loading...</h4>');
			$('#myToursDiv').html('<h4 style="text-align:center">Loading...</h4>');
			//Sign up the given guide for the given tour; this is called by a button which is generated in a popover generated by printdays.php.
			$.post("functions/signupguide.php",{
					guideID:guideID,
					tourID:tourID,
				}, function(data) {
					location.reload(); //Then, refresh the page
				});

		}
		function drop(guideID,tourID) {
			$('#unfilledToursDiv').html('<h4 style="text-align:center">Loading...</h4>');
			$('#myToursDiv').html('<h4 style="text-align:center">Loading...</h4>');
			//Drop the given guide from the given tour; this is called by a button which is generated in a popover generated by printdays.php.
			$.post("functions/dropguide.php",{
					guideID:guideID,
					tourID:tourID,
				}, function(data) {
					location.reload(); //Then, refresh the page
				});
		}

		function requestCover(guideID,tourID) {
			$('#unfilledToursDiv').html('<h4 style="text-align:center">Loading...</h4>');
			$('#myToursDiv').html('<h4 style="text-align:center">Loading...</h4>');
			//alert('Requesting Cover!'); //For debugging.
			$.post("functions/changecoverrequest.php",{
					guideID:guideID,
					tourID:tourID,
					coverRequest:1
				}, function(data) {
					//alert(data); //Show popup with info from the server. For debugging.
					location.reload(); //Then, refresh the page
				});
		}

		function requestOhCover(guideID,ohID) {
			$('#activeOhCoversDiv').html('<h4 style="text-align:center">Loading...</h4>');
			$('#myOhCovers').html('<h4 style="text-align:center">Loading...</h4>');
			//alert('Requesting Cover!'); //For debugging.
			$.post("functions/changecoverrequestoh.php",{
					guideID:guideID,
					ohID:ohID,
					coverRequest:1
				}, function(data) {
					//alert(data); //Show popup with info from the server. For debugging.
					location.reload(); //Then, refresh the page
				});
		}

		function undoRequestCover(guideID,tourID) {
			$('#unfilledToursDiv').html('<h4 style="text-align:center">Loading...</h4>');
			$('#myToursDiv').html('<h4 style="text-align:center">Loading...</h4>');
			//alert('Reclaiming tour!'); //For debugging.
			$.post("functions/changecoverrequest.php",{
					guideID:guideID,
					tourID:tourID,
					coverRequest:0
				}, function(data) {
					//alert(data); //Show popup with info from the server. For debugging.
					location.reload(); //Then, refresh the page
				});
		}

		function undoRequestOhCover(guideID,ohID) {
			$('#activeOhCoversDiv').html('<h4 style="text-align:center">Loading...</h4>');
			$('#myOhCovers').html('<h4 style="text-align:center">Loading...</h4>');
			//alert('Reclaiming tour!'); //For debugging.
			$.post("functions/changecoverrequestoh.php",{
					guideID:guideID,
					ohID:ohID,
					coverRequest:0
				}, function(data) {
					//alert(data); //Show popup with info from the server. For debugging.
					location.reload(); //Then, refresh the page
				});
		}

		function switchGuide(guideID,tourID,oldGuideID) {
			$('#unfilledToursDiv').html('<h4 style="text-align:center">Loading...</h4>');
			$('#myToursDiv').html('<h4 style="text-align:center">Loading...</h4>');
			//alert('You (guide #'+guideID+') are covering tour #'+tourID+' for guide #'+oldGuideID+'!'); //For debugging.
			$.post("functions/switchguide.php",{
					oldGuideID:oldGuideID,
					guideID:guideID,
					tourID:tourID
				}, function(data) {
					//alert(data); //Show popup with info from the server. For debugging.
					location.reload(); //Then, refresh the page
				});
		}


		function switchGuideoh(guideID,ohID,oldGuideID) {
			$('#activeOhCoversDiv').html('<h4 style="text-align:center">Loading...</h4>');
			$('#myOhCovers').html('<h4 style="text-align:center">Loading...</h4>');
			//alert('You (guide #'+guideID+') are covering tour #'+tourID+' for guide #'+oldGuideID+'!'); //For debugging.
			$.post("functions/switchguideoh.php",{
					oldGuideID:oldGuideID,
					guideID:guideID,
					ohID:ohID
				}, function(data) {
					//alert(data); //Show popup with info from the server. For debugging.
					location.reload(); //Then, refresh the page
				});
		}


	</script>
</html>
