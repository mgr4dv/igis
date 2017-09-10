<?
require_once("../authenticate.php");

if ($_SESSION['id']!=$_POST['guideID'] && !$is_exec) {
	header("location: ../signups.php");
}

$guideID = $_POST['guideID'];
$ohID = $_POST['ohID'];
$coverRequest = $_POST['coverRequest'];

include("link.php");

$scheduled_query = mysqli_query($link,"UPDATE oh_log
																			SET cover=$coverRequest
																			WHERE log_id=$ohID");

?>
