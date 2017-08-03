<?
require_once("../authenticate.php");

$subject = $_POST['subject'];
$message = $_POST['message'];
$fromID = $_POST['from_id'];
$toIDs = $_POST['to_ids'];
$tourDesc = $_POST['tour_desc'];

//get list of recipients:
$toInfo = mysqli_query($link,"SELECT firstname, lastname, email FROM guides WHERE guide_id IN (".$toIDs.")");
$recipientAddresses = '';
while ($recipient=mysqli_fetch_array($toInfo)) {
	$recipientAddresses .= $recipient['email'].',';
}
$recipientAddresses = substr($recipientAddresses, 0, -1);
//get sender:
$fromInfo = mysqli_query($link,"SELECT firstname, lastname, email FROM guides WHERE guide_id=".$fromID);
$fromInfo = mysqli_fetch_array($fromInfo);
$fromAddress=$fromInfo['email'];

//append disclaimer to message to reduce the possibility for abuse of this system:
$message = $message."/n/n<small>--------------------------/nThis message was sent via IGIS because you are signed up for the ".$tourDesc.".</small>";

//set up email headers:
$headers = "From: $fromAddress\n";
$headers .= "MIME-Version: 1.0\n";
$headers .= "Content-Type: text/html; charset=ISO-8859-1\n";

try {
	mail($recipientAddresses,$subject,$message,"From: $fromAddress\n");
	$out['error'] = '';
} catch (Exception $e) {
	$out['error'] = "Error sending email:\n\n".$e->getMessage();
}

$out['debug'] = "sending message...\nFROM: ".$fromInfo['firstname'].' '.$fromInfo['lastname'].' ('.$fromInfo['email'].")\nTO: ".$recipientAddresses."\nSUBJECT: ".$subject."\nMESSAGE: ".$message;

echo json_encode($out)

?>
