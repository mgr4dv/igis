<?
$delete_oh = $_POST["oh_id"];

include("link.php");

$delete_query = mysqli_query($link, "DELETE FROM oh_schedule WHERE oh_id='".$delete_oh."'");

?>
