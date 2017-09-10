<?
  date_default_timezone_set('America/New_York');
  require_once('../authenticate.php');

  $schedule_query = mysqli_query($link, "SELECT * FROM oh_attendance ORDER BY scheduled_datetime" );



  $html_table = "";

  $row = array();
  $time_itr = 8;

  while($row = mysqli_fetch_row($schedule_query)){

    $oh_id = $row[0];
    $sch_time = $row[1];
    $sch_id = $row[2];
    $sch_name = $row[3];
    $log_id = $row[4];
    $log_time = $row[5];
    $log_name = $row[6];


    $sch_time_obj = new DateTime($sch_time);
    $log_time_obj = new DateTime($log_time);

    if(date("N",strtotime($sch_time)) > $date_itr){
      $time_itr = 8;
      $date_itr = $date_itr + 1;
      $html_table = $html_table."</table>
      <div class=\"panel-heading\">
        <h4>".date("l",strtotime($sch_time))."</h4>
      </div>
        <table class=\"table\" id=\"".date("N",strtotime($sch_time))."\">
            <tr>
              <th>Time</th>
              <th class=\"lastentry\">Name</th>
              <th>Covered By</th>
              <th>Sign-in time</th>
              <th>Select</th>
            </tr>";
    }

    $border_status = '';

    if ($time_itr == date_format($sch_time_obj, "h")){
      $time_itr = ($time_itr + 1) % 13;
      $border_status = 'class="border_top" '.date_format($sch_time_obj, "h");
    } else {
      $border_status = '';
    }

    if($time_itr == 0){
      $time_itr = ($time_itr + 1);
    }

    $statusColor = "";

    if ($log_name == ""){
      $statusColor = '#FFFFFF';
    } else if (false) {
      $statusColor = "#FF8080";
    } else if (date_format("N",$log_time_obj) !=date_format("N",$sch_time_obj)) {
      $statusColor = "#FF8080";
    } else {
      $statusColor = "#ccffcc".strtotime(date_format("N",$log_time_obj));
    }

    $html_table = $html_table."<tr ".$border_status.">
      <td>".date_format($sch_time_obj, "h:i")."</td>
      <td id=\"".$sch_id."\" class=\"lastentry\">".$sch_name."</td>
      <td bgcolor=\"".$statusColor."\" id=\"".$log_id."\">".$log_name."</td>
      <td bgcolor=\"".$statusColor."\">".date_format($log_time_obj, "h:i")."</td>
      <td bgcolor=\"".$statusColor."\" ><input type=\"checkbox\" value=\"".$sch_id.",".$log_id.",".$sch_time.",".$oh_id."\"> </td>
    </tr>";
  }

  $html_table = $html_table."<table>";

  echo $html_table;

?>
