<?
$gid = $_POST["guide_id"];
$time = $_POST["time"];
include("link.php");

$time = strtotime($time);
$time = date("Y-m-d G:i:s",$time);
$query = mysqli_query($link, "INSERT INTO oh_log (sch_id,cover_id,sch_time,handled,cover,log_time) VALUES ('$gid','$gid','$time',0,0,NULL);");
?>
