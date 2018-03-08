<? date_default_timezone_set('America/New_York');
require_once('../authenticate.php');

$deletePoint = $_REQUEST['deletePoint'];
$pointID = $_REQUEST['pointID'];
$oh_id = $_REQUEST['oh_id'];


$guideID = $_REQUEST['guideID'];
$typeID = $_REQUEST['typeID'];
$pointVal = $_REQUEST['pointVal'];
$comment = mysqli_real_escape_string($link,$_REQUEST['comment']);


if( !is_null($oh_id)){
	if($pointVal > 0){
		$typeID =  45;
	} else{
		$typedID = 35;
	}
	$guide_find = mysqli_query($link,"SELECT cover_id FROM oh_log WHERE log_id=".$oh_id);
	$guideID = mysqli_fetch_array($guide_find)[0];
}

$toInfo = mysqli_query($link,"SELECT firstname, lastname, email FROM guides WHERE guide_id=".$guideID);
$toInfo = mysqli_fetch_array($toInfo);
$toAddress=$toInfo['email'];
$toName = $toInfo['firstname'];

$fromInfo = mysqli_query($link,"SELECT firstname, lastname, email FROM guides WHERE guide_id=940");
$fromInfo = mysqli_fetch_array($fromInfo);
$fromAddress=$fromInfo['email'];

$out = array();
$out['error'] = '';

if ($deletePoint==1) {
	//delete pointID entry
	$success = mysqli_query($link,"DELETE FROM points WHERE point_id=$pointID");
	$out['error'] = $out['error'].mysqli_error($link);
	$out['success']=$success;
} else {
	//look up description of point and form full description:
	if($typeID!=0){
		$pointDesc = mysqli_query($link,"SELECT infraction FROM point_types WHERE id=$typeID");
		$out['error'] = $out['error'].mysqli_error($link);
		$pointDesc = mysqli_fetch_array($pointDesc);
		$pointDesc = $pointDesc[0];
		$fullDesc = $pointDesc." - ".$comment;
	} else {
		$fullDesc = $comment; //if $typeID==0, that's a custom point, meaning the comment is the whole description
	}
	//add new entry to points table:
	$assigned = date('Y-m-d');
	$success = mysqli_query($link,"INSERT INTO points (guide, value, assigned, comment) VALUES ($guideID, $pointVal, '$assigned', '$fullDesc')");
	$out['error'] = $out['error'].mysqli_error($link);
	$out['success']=$success;

	$headers .= "MIME-Version: 1.0\n";
	$headers .= "Content-Type: text/html; charset=ISO-8859-1\n";

	try {
	mail($toAddress,"Updated Points for ".$toName,"You're receiving this email because your point value has been updated. ".
	"You received ".$pointVal." point(s) for the following reason:\n".$fullDesc."\n\nThis messsage was automatically sent by IGIS.
	You can reply to it to contact the current disciplinarian.",
	"From:disciplinarian@uvaguides.org\n");
	} catch (Exception $e) {
		$out['error'] = "Error sending email:\n\n".$e->getMessage();
	}
}




echo json_encode($out);



?>
