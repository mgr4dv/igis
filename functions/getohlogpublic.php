<? date_default_timezone_set('America/New_York');
require_once("../authenticate.php");


include("./link.php");


$where_part = "CURDATE()<DATE(sch_time)
              OR (CURDATE()=DATE(sch_time) AND TIME(sch_time)>CURTIME())";



$html =
"<table class='table'>
  <thead>
    <tr>
      <th>Originally Scheduled</td>
      <th>Covering</th>
    </tr>
  </thead>
  <tbody>";

$oh_query = mysqli_query($link,"SELECT log_id,sch_id,cover_id,sch_time,log_time,
                                firstname,lastname,handled
                                FROM oh_log LEFT JOIN guides ON cover_id=guide_id
                                WHERE ".$where_part."
                                ORDER BY sch_time");

  $last_time = 0;
  while($oh = mysqli_fetch_array($oh_query)){
    $log_id = $oh[0];
    $sch_id = $oh[1];
    $cover_id = $oh[2];
    $sch_time = $oh[3];
    $log_time = $oh[4];
    $firstname= $oh[5];
    $lastname = $oh[6];
    $handled = $oh[7];

    if ($log_time == ""){
      $log_time = "NULL";
    } else {
      $log_time = date("D jS - g:i a",strtotime($log_time));
    }

    if($sch_id != $cover_id){
        $sch_query = mysqli_query($link,"SELECT firstname,lastname
                                        FROM guides
                                        WHERE guide_id=$sch_id");
        $sch_retrieve = mysqli_fetch_row($sch_query);
        $sch_firstname = $sch_retrieve[0];
        $sch_lastname = $sch_retrieve[1];
    } else {
      $sch_firstname = $firstname;
      $sch_lastname = $lastname;
    }
    if(strtotime($sch_time) != strtotime($last_time)){
      $header = date("D jS - g:i a",strtotime($sch_time));
      $html = $html."<tr class='bg-info'>
                  <th>$header</th>
                  <th></th>
                  </tr>";
      $last_time = $sch_time;
    }


    $sch_time = date("l, F jS - g:i a",strtotime($sch_time));

    $html = $html."<tr>
                    <td>".$sch_firstname." ".$sch_lastname."</td>
                    <td>".$firstname." ".$lastname."</td>
                    </tr>";
  }
  echo $html."</tbody></table>"
?>
