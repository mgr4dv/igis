<?

include("functions/link.php");

$db=mysqli_query($link,"SELECT * FROM oh_schedule");

	while ($row=mysqli_fetch_array($db)) {

	    $oh_id = $row[0];
	    $guide_id = $row[1];
	    $day = $row[2];
	    $time = $row[3];

	    $day = $day; 

	    $schedule_date = date('Y-m-d', strtotime("+$day day"));

	    mysqli_query($link,"INSERT INTO oh_log (sch_id,cover_id,sch_time) VALUES ('".$guide_id."','".$guide_id."','".$schedule_date." ".$time."')");
	  }

?>
