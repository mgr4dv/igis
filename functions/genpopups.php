<?
//These functions all generate the HTML content to be placed into an automatically-generated Bootstrap popover.

function genGuidePopup($guideID) {
	//Generate a popover containing basic directory information about the given guide

	include("link.php");
	$directory_query=mysqli_query($link,"select * from guides WHERE guide_id='$guideID'");
	$guide_info = mysqli_fetch_array($directory_query); //gather info about this guide from the database, then use it to construct the guide popup

	if ($guide_info['school_phone_1']==0 || $guide_info['school_phone_1']=='') {
		$phoneLink = "<em style=\"color:#BBBBBB\">(not provided)</em>";
	} else {
		$phoneLink = "<a href=\"tel:".$guide_info['school_phone_1'].$guide_info['school_phone_2'].$guide_info['school_phone_3']."\">(".$guide_info['school_phone_1'].")-".$guide_info['school_phone_2']."-".$guide_info['school_phone_3']."</a>";
	}

	$returnVar = "
	<span><em>".$guide_info['school']." ".$guide_info['year']."</em></span>
	<span style=\"color:#BBBBBB; float:right\"><em>".$guide_info['probie_class']."</em></span>
	<table class=\"table\" style=\"margin-top:7px\">
		<tbody>
			<tr>
				<td>Email:</td>
				<td><a href=\"mailto:".$guide_info['email']."\">".$guide_info['email']."</a></td>
			</tr>
			<tr>
				<td>Phone:</td>
				<td>".$phoneLink."</td>
			</tr>
		</tbody>
	</table>
	";
	return htmlspecialchars($returnVar);//Sanitize the above because it's going into an HTML tag and you don't want to screw up the surrounding HTML.
}

function genNotesPopup($notes) {
	//auto-link phone numbers:
	$regexPhone = '~\(?\d{3,3}\)?-? ?.?\d{3,3}-? ?.?\d{4,4}~';
    $replacePhone = '<a href="tel:$0">$0</a>';
	$notes=preg_replace($regexPhone, $replacePhone, $notes);
	//auto-link email addresses:
	$regexEmail = '/(\S+@\S+\.\S+)/';
    $replaceEmail = '<a href="mailto:$1">$1</a>';
	$notes=preg_replace($regexEmail, $replaceEmail, $notes);
	return nl2br(htmlspecialchars($notes));
}

function genSignupPopup($guideID,$tourID) {
	//Generate a popover containing the signup button for a tour

	$returnVar = "<button type=\"button\" class=\"btn btn-info\" onclick=\"signup($guideID,$tourID)\" data-loading-text=\"Loading...\">Sign up for this tour!</button>";
	return htmlspecialchars($returnVar);//Sanitize the above because it's going into an HTML tag and you don't want to screw up the surrounding HTML.
}

function genDropPopup($guideID,$tourID) {
	//Generate a popover containing the drop button for a tour

	$returnVar = "<button type=\"button\" class=\"btn btn-danger\" onclick=\"drop($guideID,$tourID)\" data-loading-text=\"Loading...\"><span class=\"glyphicon glyphicon-remove\"></span> Drop this tour!</button>";
	return htmlspecialchars($returnVar);//Sanitize the above because it's going into an HTML tag and you don't want to screw up the surrounding HTML.
}

function genRequestCoverPopup($guideID,$tourID) {
	//Generate a popover containing the request-a-cover button for a tour

	$returnVar = "<button type=\"button\" class=\"btn btn-warning\" onclick=\"requestCover($guideID,$tourID)\" data-loading-text=\"Loading...\"><span class=\"glyphicon glyphicon-search\"></span> Request a cover</button>";
	return htmlspecialchars($returnVar);//Sanitize the above because it's going into an HTML tag and you don't want to screw up the surrounding HTML.
}

function genUndoCoverPopup($guideID,$tourID) {
	//Generate a popover containing the undo-request-a-cover button for a tour

	$returnVar = "<button type=\"button\" class=\"btn btn-warning\" onclick=\"undoRequestCover($guideID,$tourID)\" data-loading-text=\"Loading...\"><span class=\"glyphicon glyphicon-zoom-out\"></span> Retract cover request</button>";
	return htmlspecialchars($returnVar);//Sanitize the above because it's going into an HTML tag and you don't want to screw up the surrounding HTML.
}

function genCoverPopup($guideID,$tourID,$oldGuideID) {
	//Generate a popover containing the cover button for a tour

	$returnVar = "<button type=\"button\" class=\"btn btn-warning\" onclick=\"switchGuide($guideID,$tourID,$oldGuideID)\" data-loading-text=\"Loading...\">Cover this tour!</button>";
	return htmlspecialchars($returnVar);//Sanitize the above because it's going into an HTML tag and you don't want to screw up the surrounding HTML.
}

function genOhCoverPopup($guideID,$ohID,$oldGuideID) {
	//Generate a popover containing the cover button for a tour

	$returnVar = "<button type=\"button\" class=\"btn btn-warning\" onclick=\"switchGuideoh($guideID,$ohID,$oldGuideID)\" data-loading-text=\"Loading...\">Cover this OH!</button>";
	return htmlspecialchars($returnVar);//Sanitize the above because it's going into an HTML tag and you don't want to screw up the surrounding HTML.
}


//functions for third-party editing (by the Scheduler):
function genEditGuidePopup($guideID,$tourID) {
	include("link.php");
	$scheduled_query = mysqli_query($link,"SELECT cover_request FROM tours_scheduled WHERE guide_id=$guideID AND tour_id=$tourID");
	$cover_status = mysqli_fetch_array($scheduled_query);
	//$error = mysqli_error($link);

	//Enclosing the buttons in paragraphs just so they have a little margin and can also be easily centered.
	$dropString = "<p style=\"text-align:center\"><button type=\"button\" class=\"btn btn-danger\" onclick=\"drop($guideID,$tourID)\" data-loading-text=\"Loading...\"><span class=\"glyphicon glyphicon-remove\"></span> Drop this guide</button></p>";
	if ($cover_status[0]) {
		$coverString = "<p style=\"text-align:center\"><button type=\"button\" class=\"btn btn-warning\" onclick=\"undoRequestCover($guideID,$tourID)\" data-loading-text=\"Loading...\"><span class=\"glyphicon glyphicon-zoom-out\"></span> Retract cover request</button></p>";
	} else {
		$coverString = "<p style=\"text-align:center\"><button type=\"button\" class=\"btn btn-warning\" onclick=\"requestCover($guideID,$tourID)\" data-loading-text=\"Loading...\"><span class=\"glyphicon glyphicon-search\"></span> Request cover</button></p>";
	}
	$switchString = "<p style=\"text-align:center\"><button type=\"button\" class=\"btn btn-info\" onclick=\"switchGuidePopup($guideID,$tourID)\" data-loading-text=\"Loading...\"><span class=\"glyphicon glyphicon-user\"></span> Switch this guide...</button></p>";

	$returnvar = $dropString.$coverString.$switchString;
	//$returnvar = $returnvar."<br>".$error //for debugging (must uncomment the $error definition line above)
	return htmlspecialchars($returnvar);
}

function genCreditGuidePopup($guideID,$tourID) {
	include("link.php");
	$handled_status = mysqli_query($link,"SELECT status FROM tours_handled WHERE tour_id=".$tourID." AND guide_id=".$guideID);
	$handled_status = mysqli_fetch_array($handled_status);
	$handled_status = $handled_status[0];
	$error = mysqli_error($link);

	//Enclosing the buttons in paragraphs just so they have a little margin and can also be easily centered.

	switch ($handled_status) {
		case 'credited':
			$creditString = "";
			$missedString = "<p style=\"text-align:center\"><button type=\"button\" class=\"btn btn-danger\" onclick=\"markMissed($guideID,$tourID)\" data-loading-text=\"Loading...\"><span class=\"glyphicon glyphicon-remove-circle\"></span> Mark as missed</button></p>";
			$removeString = "<p style=\"text-align:center\"><button type=\"button\" class=\"btn btn-danger\" onclick=\"drop($guideID,$tourID)\" data-loading-text=\"Loading...\"><span class=\"glyphicon glyphicon-remove\"></span> Remove Guide</button></p>";
			$nocreditString = "<p style=\"text-align:center\"><button type=\"button\" class=\"btn btn-warning\" onclick=\"nocreditGuide($guideID,$tourID)\" data-loading-text=\"Loading...\"><span class=\"glyphicon glyphicon-remove\"></span> No Credit Guide</button></p>";
			break;
		case 'missed':
			$creditString = "<p style=\"text-align:center\"><button type=\"button\" class=\"btn btn-success\" onclick=\"creditGuide($guideID,$tourID)\" data-loading-text=\"Loading...\"><span class=\"glyphicon glyphicon-ok-circle\"></span> Credit this Guide</button></p>";
			$missedString = "";
			$removeString = "<p style=\"text-align:center\"><button type=\"button\" class=\"btn btn-danger\" onclick=\"drop($guideID,$tourID)\" data-loading-text=\"Loading...\"><span class=\"glyphicon glyphicon-remove\"></span> Remove Guide</button></p>";
			$nocreditString = "<p style=\"text-align:center\"><button type=\"button\" class=\"btn btn-warning\" onclick=\"nocreditGuide($guideID,$tourID)\" data-loading-text=\"Loading...\"><span class=\"glyphicon glyphicon-remove\"></span> No Credit Guide</button></p>";
			break;
		case 'nocredit':
			$creditString = "<p style=\"text-align:center\"><button type=\"button\" class=\"btn btn-success\" onclick=\"creditGuide($guideID,$tourID)\" data-loading-text=\"Loading...\"><span class=\"glyphicon glyphicon-ok-circle\"></span> Credit this Guide</button></p>";
			$missedString = "<p style=\"text-align:center\"><button type=\"button\" class=\"btn btn-danger\" onclick=\"markMissed($guideID,$tourID)\" data-loading-text=\"Loading...\"><span class=\"glyphicon glyphicon-remove-circle\"></span> Mark as missed</button></p>";
			$removeString = "<p style=\"text-align:center\"><button type=\"button\" class=\"btn btn-danger\" onclick=\"drop($guideID,$tourID)\" data-loading-text=\"Loading...\"><span class=\"glyphicon glyphicon-remove\"></span> Remove Guide</button></p>";
			$nocreditString = "<p style=\"text-align:center\"><button type=\"button\" class=\"btn btn-warning\" onclick=\"nocreditGuide($guideID,$tourID)\" data-loading-text=\"Loading...\"><span class=\"glyphicon glyphicon-remove\"></span> No Credit Guide</button></p>";
			break;
		default:
			$creditString = "<p style=\"text-align:center\"><button type=\"button\" class=\"btn btn-success\" onclick=\"creditGuide($guideID,$tourID)\" data-loading-text=\"Loading...\"><span class=\"glyphicon glyphicon-ok-circle\"></span> Credit this Guide</button></p>";
			$missedString = "<p style=\"text-align:center\"><button type=\"button\" class=\"btn btn-danger\" onclick=\"markMissed($guideID,$tourID)\" data-loading-text=\"Loading...\"><span class=\"glyphicon glyphicon-remove-circle\"></span> Mark as missed</button></p>";
			$removeString = "<p style=\"text-align:center\"><button type=\"button\" class=\"btn btn-danger\" onclick=\"drop($guideID,$tourID)\" data-loading-text=\"Loading...\"><span class=\"glyphicon glyphicon-remove\"></span> Remove Guide</button></p>";
			$nocreditString = "<p style=\"text-align:center\"><button type=\"button\" class=\"btn btn-warning\" onclick=\"nocreditGuide($guideID,$tourID)\" data-loading-text=\"Loading...\"><span class=\"glyphicon glyphicon-remove\"></span> No Credit Guide</button></p>";
			break;
	}

	$returnvar = $creditString.$missedString.$removeString.$nocreditString.$error;
	return htmlspecialchars($returnvar);
}

function genAddGuidePopup($tourID) {
	$returnvar = "<button type=\"button\" class=\"btn btn-info\" onclick=\"signupGuidePopup($tourID)\" data-loading-text=\"Loading...\"><span class=\"glyphicon glyphicon-user\"></span> Sign up a guide...</button>";
	return htmlspecialchars($returnvar);
}

function genEditTourPopup($tourID) {
	//Enclosing the buttons in paragraphs just so they have a little margin and can also be easily centered.
	$returnvar = "<p style=\"text-align:center\"><button type=\"button\" class=\"btn btn-danger\" onclick=\"deleteTour($tourID)\" data-loading-text=\"Loading...\"><span class=\"glyphicon glyphicon-remove\"></span> Delete this tour</button></p>
					<p style=\"text-align:center\"><button type=\"button\" class=\"btn btn-primary\" onclick=\"editTourPopup($tourID)\" data-loading-text=\"Loading...\"><span class=\"glyphicon glyphicon-pencil\"></span> Edit this tour...</button></p>";
	return htmlspecialchars($returnvar);
}

function genEditRegTourPopup($tourID) {
	//Enclosing the buttons in paragraphs just so they have a little margin and can also be easily centered.
	$returnvar = "<p style=\"text-align:center\"><button type=\"button\" class=\"btn btn-danger\" onclick=\"deleteTour($tourID)\" data-loading-text=\"Loading...\"><span class=\"glyphicon glyphicon-remove\"></span> Delete this tour</button></p>
					<p style=\"text-align:center\"><button type=\"button\" class=\"btn btn-primary\" onclick=\"editTourPopup($tourID)\" data-loading-text=\"Loading...\"><span class=\"glyphicon glyphicon-pencil\"></span> Edit this tour...</button></p>";
	return htmlspecialchars($returnvar);
}

function genHandleTourPopup($guideID, $tourID) {
	$returnvar = "Testing for guide #".$guideID.", tour #".$tourID;
	return htmlspecialchars($returnvar);
}

?>
