<?
date_default_timezone_set('America/New_York');
require_once("../authenticate.php");

if ($_SESSION['id']!=$_POST['guideID'] && !$is_exec) {
	header("location: ../signups.php");
}

$guideID = $_POST['guideID'];
$ohID = $_POST['ohID'];

include("link.php");

mysqli_query($link,"UPDATE oh_log SET cover=0,cover_id=$guideID WHERE log_id=$ohID;");

?>
