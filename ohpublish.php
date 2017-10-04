<?

include("functions/link.php");


mysqli_query($link,"SET time_zone = 'US/Eastern';");
$db=mysqli_query($link,"SELECT firstname,lastname,log_id
                        FROM oh_log LEFT JOIN guides ON cover_id=guide_id
                        WHERE DATE(sch_time)=CURDATE()
                        AND CURTIME()<ADDDATE(sch_time, INTERVAL 30 
MINUTE)
                        AND CURTIME()>=ADDDATE(sch_time, INTERVAL -30 MINUTE)
                        AND log_time IS NULL
                        ORDER BY sch_time");

  while ($row=mysqli_fetch_array($db)){
    $name = $row[0]." ".$row[1];
    $id = $row[2];
    echo $name.",".$id.";";
  }
?>
