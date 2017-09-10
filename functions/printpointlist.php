<?
date_default_timezone_set('America/New_York');
require_once('../authenticate.php');

$guideID = $_REQUEST["guideID"];
$order_by = $_REQUEST["order"];
$categorize = $_REQUEST["cat"];

if ($guideID=='all') {
	$order_query = '';

	switch ($order_by) {
		case 'lastname':
			$order_query="lastname ASC, firstname ASC";
			break;
		case 'firstname':
			$order_query="firstname ASC, lastname ASC";
			break;
		case 'probieclass':
			$order_query="probie_class ASC, lastname ASC, firstname ASC";
			break;
		case 'year':
			$order_query="year ASC, lastname ASC, firstname ASC";
			break;
		case 'school':
			$order_query="school ASC, lastname ASC, firstname ASC";
			break;
		case 'numpoints':
			$order_query="lastname ASC, firstname ASC";
			break;
		case 'numtours':
			$order_query="lastname ASC, firstname ASC";
			break;
		default:
			$order_query="lastname ASC, firstname ASC";
			break;
	}

	//form array for all info:
	$guides_query = mysqli_query($link, "SELECT * FROM guides WHERE status='current' ORDER BY $order_query");
	$guide = array();
	$pointsList = array();
	$tourCountList = array();
	$guides_query_error = mysqli_error($link);
	$c = 0;
	while ($guideInfo = mysqli_fetch_array($guides_query)) {
		
		//===== get points info: =====
		
		$guide[$c] = $guideInfo;
		$points_query = mysqli_query($link, "SELECT SUM(value) AS TOTAL FROM points WHERE guide=".$guideInfo['guide_id']." GROUP BY guide");
		$points_query_error = mysqli_error($link);
		if (mysqli_num_rows($points_query)==0) { //if there are no point records, they must have no points
			$sumPoints = 0;
		} else {
			$sumPoints = mysqli_fetch_array($points_query);
			$sumPoints = $sumPoints[0];
			$sumPoints = round(floatval($sumPoints),1);
		}
		$guide[$c]['points'] = $sumPoints;
		$pointsList[$c] = $sumPoints;
		
		//===== get tour count info: =====
		
		$currYear = date('Y');
		//if the current date is before June 1st of the current year...
		if (time()<mktime(0,0,0,6,1,$currYear)) {
			//must be spring semester
			$startDate = date('Y-m-d',mktime(0,0,0,1,1,$currYear));
			$endDate = date('Y-m-d',mktime(0,0,0,6,1,$currYear));
			$tourReq = $igis_settings['tour_req_spring'];
			$hisReq = $igis_settings['his_req_spring'];
			$admReq = $igis_settings['adm_req_spring'];
		} else { //otherwise...
			//must be fall semester
			$startDate = date('Y-m-d',mktime(0,0,0,6,2,$currYear));
			$endDate = date('Y-m-d',mktime(0,0,0,12,31,$currYear));
			$tourReq = $igis_settings['tour_req_fall'];
			$hisReq = $igis_settings['his_req_fall'];
			$admReq = $igis_settings['adm_req_fall'];
		}
		$tourCountQuery = mysqli_query($link, "SELECT adm_req, his_req FROM tours_handled 
												INNER JOIN tours_info on tours_info.tour_id=tours_handled.tour_id 
												INNER JOIN tours_types ON tours_info.type=tours_types.type_id 
												WHERE tours_handled.guide_id=".$guide[$c]['guide_id']." AND date>='$startDate' AND date<='$endDate' AND tours_handled.status='credited' 
												ORDER BY date desc, time desc");
		$eitherCount = 0;
		$admCount = 0;
		$hisCount = 0;
		$neitherCount = 0;
		while ($tourCount = mysqli_fetch_array($tourCountQuery)) {
			if ($tourCount['adm_req']=='yes' && $tourCount['his_req']=='yes') {
				$eitherCount++;
			} else if ($tourCount['adm_req']=='yes') {
				$admCount++;
			} else if ($tourCount['his_req']=='yes') {
				$hisCount++;
			} else {
				$neitherCount++;
			}
		}
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
		if ($totalTours>=$tourReq && $fulfilledAdmHis) {
			$fulfilled = true;
		} else {
			$fulfilled = false;
		}
		
		$guide[$c]['tourCount'] = $totalTours;
		$guide[$c]['admCount'] = $admCount;
		$guide[$c]['hisCount'] = $hisCount;
		$guide[$c]['eitherCount'] = $eitherCount;
		$guide[$c]['neitherCount'] = $neitherCount;
		$guide[$c]['fulfilled'] = $fulfilled;
		$guide[$c]['fulfilledAdmHis'] = $fulfilledAdmHis;
		$tourCountList[$c] = $totalTours;
		
		$c++;
	}

	//sort by point totals if that's been requested:
	if ($order_by=='numpoints') {
		$toSort = array($guide, $pointsList);
		array_multisort($toSort[1], SORT_DESC, SORT_NUMERIC, $toSort[0]);
		$guide = $toSort[0];
	}
	//sort by tour totals if that's been requested:
	if ($order_by=='numtours') {
		$toSort = array($guide, $tourCountList);
		array_multisort($toSort[1], SORT_DESC, SORT_NUMERIC, $toSort[0]);
		$guide = $toSort[0];
	}
	//"$guide" is now fully sorted; initially by the mysql query, then by the multisort by number of points, if applicable, so it can be printed in order.

	$points_list_content = '';

	$prevCategory = '';
	for ($i=0; $i<count($guide); $i++) {
		$guideToPrint = $guide[$i];
		
		//Account for the fact that some people don't update all their information:
		if ($guideToPrint['probie_class']=="-" || $guideToPrint['probie_class']=="") {
			$probieStr = "<em style=\"color:#BBBBBB\">unknown</em>";
		} else {
			$probieStr = $guideToPrint['probie_class'];
		}
		
		if ($guideToPrint['year']=="0" || $guideToPrint['year']=="") {
			$yearStr = "<em style=\"color:#BBBBBB\">unset</em>";
		} else {
			$yearStr = $guideToPrint['year'];
		}
		
		if ($guideToPrint['school']=="") {
			$schoolStr = "<em style=\"color:#BBBBBB\">unset</em>";
		} else {
			$schoolStr = $guideToPrint['school'];
		}
		
		if ($guideToPrint['points']<=0) {
			$pointButtonColor = 'success';
		} else if ($guideToPrint['points']<$igis_settings['point_threshold']) {
			$pointButtonColor = 'warning';
		} else {
			$pointButtonColor = 'danger';
		}
		$pointStr = '<button type="button" class="btn btn-'.$pointButtonColor.'" onclick="pointsPopup('.$guideToPrint['guide_id'].')">'.$guideToPrint['points'].'</button>';
		
		//set the color:
		if ($guideToPrint['fulfilled']) {
			$tourCountColor = '#009900';
		} else {
			$tourCountColor = '#FF0000';
		}
		//create note if haven't met adm/his req
		if ($guideToPrint['fulfilledAdmHis']) {
			$admHisLabel = '';
		} else {
			$admHisLabel = '<br><span style="font-size:8pt; font-style:italic; color:#999999">(< A/H)<span>';
		}
		$tourStr = '<span style="font-weight:bold; color:'.$tourCountColor.'">'.$guideToPrint['tourCount'].'</span>'.$admHisLabel.mysqli_error($link);
		
		
		$catRow = "";
		$newCategory = false;
		if ($categorize) {
			switch ($order_by) {
				case 'probieclass':
					if ($probieStr!=$prevCategory) {
						$newCategory = true;
					}
					$prevCategory = $probieStr;
					break;
				case 'year':
					if ($yearStr!=$prevCategory) {
						$newCategory = true;
					}
					$prevCategory = $yearStr;
					break;
				case 'school':
					if ($schoolStr!=$prevCategory) {
						$newCategory = true;
					}
					$prevCategory = $schoolStr;
					break;
				default:
					break;
			}
			if ($newCategory) {
				$catRow = "<tr><td colspan=7 style=\"text-align:center\"><h3><span class=\"label label-default\">".$prevCategory."</span></h3></td></tr>";
			}
		}

		
		
		$row = "<tr>
				<td style=\"vertical-align:middle\"><h4 style=\"margin:0px\"><a href=\"guide.php?id=".$guideToPrint['guide_id']."\">".$guideToPrint['firstname']." ".$guideToPrint['lastname']."</a></h4></td>
				<td style=\"vertical-align:middle\">".$schoolStr."</td>
				<td style=\"vertical-align:middle\">".$yearStr."</td>
				<td style=\"vertical-align:middle\">".$probieStr."</td>
				<td style=\"vertical-align:middle\">".$pointStr."</td>
				<td style=\"vertical-align:middle\">".$tourStr."</td>
				<td style=\"vertical-align:middle\"><input type=\"checkbox\" value=\"".$guideToPrint['guide_id']."\"></td>
				</tr>";
		
		$points_list_content = $points_list_content . "\n" . $catRow . $row;
	}

	echo $points_list_content;

} else {
	$out = array();

	$points_query = mysqli_query($link, "SELECT * FROM points WHERE guide=".$guideID." ORDER BY assigned DESC");
	$points_query_error = mysqli_error($link);
	
	$header = "<tr>
				\n<th style=\"width:110px\">Assigned:</th>
				\n<th>Value:</th>
				\n<th>Description:</th>
				\n<th> </th>
				\n</tr>";
	$rows = "";
	$sumPoints = 0;
	while ($point = mysqli_fetch_array($points_query)) {
		if ($point['value']>0) {
			$pointColor = "FF0000";
			$plus = "+";
		} else {
			$pointColor = "009900";
			$plus="";
		}
		$rows = $rows."\n<tr>
						\n<td>".date("M j Y",strtotime($point['assigned']))."</td>
						\n<td><strong style=\"color:#$pointColor\">".$plus.round(floatval($point['value']),1)."</strong></td>
						\n<td>".$point['comment']."</td>
						\n<td style=\"vertical-align:middle\"><button class=\"btn btn-xs btn-danger\" style=\"float:right\" onclick=\"deletePoint(".$point['point_id'].",".$point['guide'].")\">X</button></td>
						\n</tr>";
		$sumPoints = $sumPoints+$point['value'];
	}
	
	$out['sumPoints'] = $sumPoints;
	if ($sumPoints<=0) {
		$pointLabelColor = 'success';
	} else if ($sumPoints<$igis_settings['point_threshold']) {
		$pointLabelColor = 'warning';
	} else {
		$pointLabelColor = 'danger';
	}
	if ($sumPoints==1 || $sumPoints==-1) {
		$pluralize = "";
	} else {
		$pluralize = "s";
	}
	
	$out['sumPointsLabel'] = '<span class="label label-'.$pointLabelColor.'">'.$sumPoints.' point'.$pluralize.'<span>';
	$out['addRemoveButton'] = '<button class="btn btn-xs btn-primary" onclick="addRemovePopup('.$guideID.')">+ / -</button>';
	
	$out['table'] = '<table class="table">
						<thead>
							'.$header.'
						</thead>
						<tbody>
							'.$rows.'
						</tbody>
					</table>';
			
	$guide = mysqli_query($link, "SELECT firstname, lastname FROM guides WHERE guide_id=$guideID");
	$guide = mysqli_fetch_array($guide);
	$out['guideName'] = $guide['firstname']." ".$guide['lastname'];
	
	
	$currYear = date('Y');
	//if the current date is before June 1st of the current year...
	if (time()<mktime(0,0,0,6,1,$currYear)) {
		//must be spring semester
		$startDate = date('Y-m-d',mktime(0,0,0,1,1,$currYear));
		$endDate = date('Y-m-d',mktime(0,0,0,6,1,$currYear));
		$tourReq = $igis_settings['tour_req_spring'];
		$hisReq = $igis_settings['his_req_spring'];
		$admReq = $igis_settings['adm_req_spring'];
	} else { //otherwise...
		//must be fall semester
		$startDate = date('Y-m-d',mktime(0,0,0,8,1,$currYear));
		$endDate = date('Y-m-d',mktime(0,0,0,12,31,$currYear));
		$tourReq = $igis_settings['tour_req_fall'];
		$hisReq = $igis_settings['his_req_fall'];
		$admReq = $igis_settings['adm_req_fall'];
	}
	$tourCountQuery = mysqli_query($link, "SELECT adm_req, his_req FROM tours_handled 
											INNER JOIN tours_info on tours_info.tour_id=tours_handled.tour_id 
											INNER JOIN tours_types ON tours_info.type=tours_types.type_id 
											WHERE tours_handled.guide_id=".$guideID." AND date>='$startDate' AND date<='$endDate' AND tours_handled.status='credited' 
											ORDER BY date desc, time desc");
	$eitherCount = 0;
	$admCount = 0;
	$hisCount = 0;
	$neitherCount = 0;
	while ($tourCount = mysqli_fetch_array($tourCountQuery)) {
		if ($tourCount['adm_req']=='yes' && $tourCount['his_req']=='yes') {
			$eitherCount++;
		} else if ($tourCount['adm_req']=='yes') {
			$admCount++;
		} else if ($tourCount['his_req']=='yes') {
			$hisCount++;
		} else {
			$neitherCount++;
		}
	}
	
	$totalTours = $eitherCount+$admCount+$hisCount+$neitherCount;
	if ($totalTours>=$tourReq && $admCount>=$admReq && $hisCount>=$hisReq) {
		$fulfilled = true;
	} else if ($totalTours>=$tourReq){
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
			$fulfilled = true;
		} else {
			$fulfilled = false;
		}
	} else {
		$fulfilled = false;
	}
	//set the color:
	if ($fulfilled) {
		$tourCountColor = '#009900';
	} else {
		$tourCountColor = '#FF0000';
	}
	
	$out['tourCount'] = mysqli_error($link)."<span style=\"font-weight:bold; color:$tourCountColor\">".$totalTours." tours given:</span> ".$admCount." admissions, ".$hisCount." historical, ".$eitherCount." arbitrary (and ".$neitherCount." that didn't count)";
	
	echo json_encode($out);
}


?>
