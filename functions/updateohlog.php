<?
date_default_timezone_set('America/New_York');
include("./link.php");

$status = $_REQUEST["status"];
$id = $_REQUEST["id"];

if($status == 2){
  mysqli_query($link,"DELETE FROM oh_log WHERE log_id=".$id);
}
else{
  mysqli_query($link,"UPDATE oh_log SET handled=".$status." WHERE log_id=".$id);
}

?>
