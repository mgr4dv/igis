<? date_default_timezone_set('America/New_York');
require_once("authenticate.php");

$pointsGuide = $_SESSION['id'];
$TIPsGuide = $_SESSION['id'];
$toursGuide = $_SESSION['id'];
$infoGuide = $_SESSION['id'];

$currYear = date('Y');
//if the current date is before June 1st of the current year...
if (time()<mktime(0,0,0,7,31,$currYear)) {
	//must be spring semester
	$semester = 'Spring';
	$startDate = date('Y-m-d',mktime(0,0,0,1,1,$currYear));
	$endDate = date('Y-m-d',mktime(0,0,0,6,1,$currYear));
	$tourReq = $igis_settings['tour_req_spring'];
	$hisReq = $igis_settings['his_req_spring'];
	$admReq = $igis_settings['adm_req_spring'];
	$TIPReq = $igis_settings['tip_req_spring'];
} else { //otherwise...
	//must be fall semester
	$semester = 'Fall';
	$startDate = date('Y-m-d',mktime(0,0,0,8,1,$currYear));
	$endDate = date('Y-m-d',mktime(0,0,0,12,31,$currYear));
	$tourReq = $igis_settings['tour_req_fall'];
	$hisReq = $igis_settings['his_req_fall'];
	$admReq = $igis_settings['adm_req_fall'];
	$TIPReq = $igis_settings['tip_req_fall'];
}

$bigQuery = mysqli_query($link,"SET SQL_BIG_SELECTS=1;");

//=============================================================================================
//===================================== TOURS =================================================
//=============================================================================================
//replaced by AJAX call


//=============================================================================================
//====================================== TIPS =================================================
//=============================================================================================
//Get list of all TIPs, highlighting this semester, pending credit or not:
$TIPsTable = '';
$TIPsQuery = mysqli_query($link, "SELECT date, time, title, credits, attendance FROM tips_scheduled
										INNER JOIN tips_info ON tips_info.tip_id=tips_scheduled.tip_id
										WHERE tips_scheduled.guide_id=".$TIPsGuide."
										ORDER BY date DESC, time DESC");
$TIPsError = mysqli_error($link);
$totalTIPs = 0;
$TIPsRows = '';
$i=mysqli_num_rows($TIPsQuery);
while($TIP=mysqli_fetch_array($TIPsQuery)) {
	if (strtotime($TIP['date'].' '.$TIP['time'])>strtotime($startDate)) {
		$TIPRowStyle = "style=\"background-color:#EEEEFF\"";
		if ($TIP['attendance']==1) {
			//give as many credits as the TIP is worth, but only if attendance has been marked as true
			$totalTIPs += $TIP['credits'];
		}
	} else {
		$TIPRowStyle = "";
	}
	if (strtotime($TIP['date'])>time()) {
		$attendanceSpan = '<span style="font-style:italic; color:#7777FF">[future]</span>';
	} elseif ($TIP['attendance']==1) {
		$attendanceSpan = '<span style="font-weight:bold; color:#009900">Attended</span>';
	} elseif ($TIP['attendance']==0) {
		$attendanceSpan = '<span style="font-weight:bold; color:#777777">Missed</span>';
	} else {
		$attendanceSpan = '<span style="font-weight:bold; color:#997700">[unknown]</span>';
	}
	$TIPsRows = $TIPsRows."<tr>
								<td ".$TIPRowStyle.">#".$i."</td>
								<td ".$TIPRowStyle.">".date('M j Y',strtotime($TIP['date'])).", ".date('g:i a',strtotime($TIP['time']))."</td>
								<td ".$TIPRowStyle.">".$TIP['title']."</td>
								<td ".$TIPRowStyle.">".$TIP['credits']."</td>
								<td ".$TIPRowStyle.">".$attendanceSpan."</td>
							</tr>";
	$i--;
}
$TIPsTable = "<table class=\"table\">
					<tr>
						<th></th>
						<th>Date/Time:</th>
						<th>Title:</th>
						<th>Credits:</th>
						<th>Attendance:</th>
					</tr>
					".$TIPsRows."
				</table>";
if ($totalTIPs==1) {
	$pluralizeTIPs='';
} else {
	$pluralizeTIPs='s';
}

if ($totalTIPs>=$TIPReq) {
	$totalTIPsColor = '#009900';
} else {
	$totalTIPsColor = '#FF9900';
}
$totalTIPsSpan = '<span style="font-weight:bold; color:'.$totalTIPsColor.'">'.$totalTIPs.'</span>';


//=============================================================================================
//===================================== POINTS ================================================
//=============================================================================================
//Get point list:
$pointsQuery = mysqli_query($link, "SELECT * FROM points WHERE guide=".$pointsGuide." ORDER BY assigned DESC");
$pointsError = mysqli_error($link);

$pointsHeader = "<tr>
			\n<th style=\"width:110px\">Assigned:</th>
			\n<th>Value:</th>
			\n<th>Description:</th>
			\n<th> </th>
			\n</tr>";
$pointsRows = "";
$sumPoints = 0;
while ($point = mysqli_fetch_array($pointsQuery)) {
	if ($point['value']>0) {
		$pointColor = "FF0000";
		$plus = "+";
	} else {
		$pointColor = "009900";
		$plus="";
	}
	$pointsRows = $pointsRows."\n<tr>
					\n<td>".date("M j Y",strtotime($point['assigned']))."</td>
					\n<td><strong style=\"color:#$pointColor\">".$plus.round(floatval($point['value']),1)."</strong></td>
					\n<td>".$point['comment']."</td>
					\n</tr>";
	$sumPoints = $sumPoints+$point['value'];
}

if ($sumPoints<=0) {
	$pointLabelColor = 'success';
	$pointSpanColor = '#009900';
	$pointMessage = '.';
} else if ($sumPoints<$igis_settings['point_threshold']) {
	$pointLabelColor = 'warning';
	$pointSpanColor = '#FF9900';
	$pointMessage = '.';
} else {
	$pointLabelColor = 'danger';
	$pointSpanColor = '#FF0000';
	$pointMessage = ', which is above the threshold for expulsion.';
}
if ($sumPoints==1 || $sumPoints==-1) {
	$pluralizePoints = "";
} else {
	$pluralizePoints = "s";
}

$sumPointsLabel = '<span class="label label-'.$pointLabelColor.'">'.$sumPoints.' point'.$pluralizePoints.'<span>';
$sumPointsSpan = '<span style="color:'.$pointSpanColor.'; font-weight:bold">'.$sumPoints.'</span>';

$pointsTable = '<table class="table">
					<thead>
						'.$pointsHeader.'
					</thead>
					<tbody>
						'.$pointsRows.'
					</tbody>
				</table>';



//=============================================================================================
//=================================== GUIDE INFO ==============================================
//=============================================================================================
$guideQuery = mysqli_query($link,"SELECT * FROM guides WHERE guide_id=".$infoGuide);

$guide = mysqli_fetch_array($guideQuery);


$probieclass = unspecifiedIfBlank($guide['probie_class']);
$year = unspecifiedIfBlank($guide['year']);
$school = unspecifiedIfBlank($guide['school']);
$major = unspecifiedIfBlank($guide['major']);
$email = unspecifiedIfBlank($guide['email']);
if ($guide['email']!='') {
	$emailLink1 = '<a href="mailto:'.$guide['email'].'">';
	$emailLink2 = '</a>';
} else {
	$emailLink1 = '';
	$emailLink2 = '';
}
$email = $emailLink1.$email.$emailLink2;
$phone = unspecifiedIfBlank("(".$guide['school_phone_1'].")-".$guide['school_phone_2']."-".$guide['school_phone_3']);
if ($guide['school_phone_1'].$guide['school_phone_2'].$guide['school_phone_3']!='0000000000') {
	$phoneLink1 = '<a href="tel:'.$guide['school_phone_1'].$guide['school_phone_2'].$guide['school_phone_3'].'">';
	$phoneLink2 = '</a>';
} else {
	$phoneLink1 = '';
	$phoneLink2 = '';
}
$phone = $phoneLink1.$phone.$phoneLink2;
$birthday = unspecifiedIfBlank(date('F jS, Y',strtotime($guide['date_of_birth'])));
$hometown = unspecifiedIfBlank($guide['hometown']);
$address = unspecifiedIfBlank($guide['school_address']);

function unspecifiedIfBlank($data) {
	global $infoGuide;
	if ($data=="" || $data=="(000)-000-0000" || $data=="()--" || $data=="0" || $data=="-" || $data=="11/30/-1" || $data=="November 30th, -0001" || $data=="December 31st, 1969") {
		return "<a style=\"color:#FF0000; margin:0px; font-weight:bold\" href=\"guide.php?id=".$infoGuide."\">Fill this out!</a>";
	} else {
		return $data;
	}
}
?>

<!DOCTYPE html>

<html lang="en">

<!-- Header information for webpage (reused except for title) -->
	<head>
		<?
		include_once("includes/head.php");
		?>
	</head>

<!-- Body of webpage (not reused, but reusable elements inside) -->
	<body style="padding-top:40px; padding-bottom: 60px">
		<?include_once("includes/nav.php")?>
		<?include_once("includes/footer.php")?>


		<div class="container">
			<h1>My IGIS</h1>
			<div class="well">
				<div class="row">
					<div class="col-md-2"></div>
					<div class="col-md-4">
						<div class="panel panel-warning">
							<div class="panel-heading">Status:</div>
							<div class="panel-body">
								<h3 style="cursor:pointer; font-weight:bold; text-decoration:underline; margin-top:0px" onclick="showTours()">Tours:</h3>
								<p style="font-size:14pt">You have given <span id="totalToursThisSemester">__</span> out of <span><?echo $tourReq?></span> required this semester, <span id="admHisMsg">...</span> <br><small style="font-style:italic; color:#777777">(<span id="uncreditedCount">__</span> pending credit)</small></p>
								<table class="table">
									<tr>
										<td>Admissions Tours (<?echo $admReq?> required):</td>
										<td id="admCount"></td>
									</tr>
									<tr>
										<td>Historical Tours (<?echo $hisReq?> required):</td>
										<td id="hisCount"></td>
									</tr>
									<tr>
										<td>Tours that can be either:</td>
										<td id="eitherCount"></td>
									</tr>
									<tr>
										<td>Tours that didn't count as either:</td>
										<td id="neitherCount"></td>
									</tr>
								</table>
								<p>In your <span id="semestersInUGS">__ semesters active</span> in the Guide Service, you have given <span id="totalToursEver">__</span> tours (<span id="totalAdmCount">__</span> admissions, <span id="totalHisCount">__</span> historical, <span id="totalEitherCount">__</span> that could be either, and <span id="totalNeitherCount">__</span> that didn't count).
								<h3 style="cursor:pointer; font-weight:bold; text-decoration:underline" onclick="showPoints()">Points:</h3>
								<p style="font-size:14pt">You currently have <?echo $sumPointsSpan?> point<?echo $pluralizePoints.$pointMessage?> <?echo $pointsError?></p>
								<h3 style="cursor:pointer; font-weight:bold; text-decoration:underline" onclick="showTIPs()">TIPs:</h3>
								<p style="font-size:14pt">You have attended <?echo $totalTIPsSpan?> TIP<?echo $pluralizeTIPs?> this semester.<?echo $TIPsError?></p>
								<p style="text-align:center; font-style:italic; margin-top:0px">[TIP information is not accurate because TIPs are not yet computerized on IGIS.]</p>
							</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="panel panel-info">
							<div class="panel-heading">Info:</div>
							<div class="panel-body">
								<table class="table">
									<tr>
										<td style="min-width:130px">Probie Class:</td>
										<td><strong><?echo $probieclass?></strong></td>
									</tr>
									<tr>
										<td>Class:</td>
										<td><strong><?echo $school." ".$year?></strong></td>
									</tr>
									<tr>
										<td>Major:</td>
										<td><strong><?echo $major?></strong></td>
									</tr>
									<tr>
										<td>Hometown:</td>
										<td><strong><?echo $hometown?></strong></td>
									</tr>
									<tr>
										<td>Birthday:</td>
										<td><strong><?echo $birthday?></strong></td>
									</tr>
									<tr>
										<td>Email Address:</td>
										<td><strong><?echo $email?></strong></td>
									</tr>
									<tr>
										<td>Phone Number:</td>
										<td><strong><?echo $phone?></strong></td>
									</tr>
									<tr>
										<td>Address:</td>
										<td><strong><?echo nl2br($address)?></strong></td>
									</tr>
								</table>
								<p style="text-align:center"><button id="editButton" class="btn btn-danger">Edit your info</button></p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>


		<div class="modal" id="pointsModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header" style="text-align:center">
						<h3 class="modal-title" style="display:inline"><strong>Points Detail</strong></h3>
						<h4 style="margin-top:0px"><em>Total:</em> <span id="guidePoints"><?echo $sumPointsSpan?> point<?echo $pluralizePoints?></span></h4>
						<?echo $pointsError?>
					</div>
					<div class="modal-body">
						<div id="pointsTable">
							<?echo $pointsTable?>
						</div>
						<p style="text-align:right">
							<button id="closePointsButton" type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						</p>
					</div>
				</div>
			</div>
		</div>


		<div class="modal" id="toursModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header" style="text-align:center">
						<h3 class="modal-title" style="display:inline"><strong>Tours Detail</strong></h3>
						<h4 style="margin-top:0px"><em><?echo $semester." ".$currYear?> total:</em> <span id="guideTours">Loading...</span></h4>
						<span id="toursError"></span>
					</div>
					<div class="modal-body">
						<div id="toursTable">
						</div>
						<p style="text-align:right">
							<button id="closeToursButton" type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						</p>
					</div>
				</div>
			</div>
		</div>


		<div class="modal" id="TIPsModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header" style="text-align:center">
						<h3 class="modal-title" style="display:inline"><strong>TIPs Detail</strong></h3>
						<h4 style="margin-top:0px"><em><?echo $semester." ".$currYear?> total:</em> <span id="guideTIPs"><?echo $totalTIPsSpan?> TIP<?echo $pluralizeTIPs?></span></h4>
						<?echo $TIPsError?>
					</div>
					<div class="modal-body">
						<p style="text-align:center; font-style:italic">[This information is not accurate because TIPs are not yet computerized on IGIS.]</p>
						<div id="TIPsTable">
							<?echo $TIPsTable?>
						</div>
						<p style="text-align:right">
							<button id="closeTIPsButton" type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						</p>
					</div>
				</div>
			</div>
		</div>


	</body>

	<script>
		$(function() {
			$.post("functions/gettourlist.php",{
					guideID:<?echo $_SESSION['id']?>
				}, function(data) {
					data = $.parseJSON(data);
					$('#guideTours').html(data.totalToursThisSemester+' tour'+data.pluralizeTours);
					$('#toursError').html(data.toursError);
					$('#toursTable').html(data.toursTable);
					$('#totalToursEver').html(data.totalToursEver);
					$('#totalAdmCount').html(data.totalAdmCount);
					$('#totalHisCount').html(data.totalHisCount);
					$('#totalEitherCount').html(data.totalEitherCount);
					$('#totalNeitherCount').html(data.totalNeitherCount);
					$('#admHisMsg').html(data.admHisMsg);
					$('#totalToursThisSemester').html(data.totalToursThisSemester);
					$('#uncreditedCount').html(data.uncreditedCount);
					$('#admCount').html(data.admCount);
					$('#hisCount').html(data.hisCount);
					$('#eitherCount').html(data.eitherCount);
					$('#neitherCount').html(data.neitherCount);
					if (data.numSemesters==1) {
						$('#semestersInUGS').html(data.numSemesters+' semester active');
					} else {
						$('#semestersInUGS').html(data.numSemesters+' semesters active');
					}

					//Do this last so that all the new things that were just pasted in are activated:
					$('[data-toggle="tooltip"]').tooltip();
				});
		});
		function showPoints() {
			$('#pointsModal').modal('show');
		}

		function showTours() {
			$('#toursModal').modal('show');
		}

		function showTIPs() {
			$('#TIPsModal').modal('show');
		}

		$('#editButton').click( function(){
			window.location = "guideEdit.php?id=<?echo $infoGuide?>";
		});
	</script>
</html>
