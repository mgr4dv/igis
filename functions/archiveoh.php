<?php
date_default_timezone_set('America/New_York');
require_once('../authenticate.php');

include("./link.php");

$archiveName = $_POST['archiveName'];

mysqli_query($link,"UPDATE `oh_log` SET `archive`='$archiveName' WHERE `archive` IS NULL ;");

mysqli_query($link,"DELETE * FROM oh_schedule WHERE 1=1");

?>