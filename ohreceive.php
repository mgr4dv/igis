<?

include("./functions/link.php");

$oh_id = $_REQUEST["oh_id"];

mysqli_query($link,"UPDATE oh_log SET log_time='".date("Y-m-d H:i:s")."' WHERE log_id=".$oh_id.";");

include("./ohpublish.php");

?>
