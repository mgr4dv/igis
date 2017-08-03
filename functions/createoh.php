<?
$gid = $_POST["guide_id"];
$day = $_POST["day"];
$time = $_POST["time"];
include("link.php");

$time = strtotime($time);


$delete_query = mysqli_query($link, "INSERT INTO oh_schedule (guide_id, day, time) VALUES ('".$gid."','".$day."','".date("G:i",$time).":00');");

?>
