<?
  date_default_timezone_set('America/New_York');
  require_once('../authenticate.php');

  $schedule_query = mysqli_query($link, "SELECT oh_id,day,time,firstname,lastname,guides.guide_id FROM oh_schedule LEFT JOIN guides ON oh_schedule.guide_id=guides.guide_id ORDER BY day,time" );


  $days = ["Monday","Tuesday","Wednesday","Thursday","Friday"];
  $day_itr = 0;
  $time_itr = strtotime("00:00:00");

  $html = '<div class="row">
    <div class="col-md-2" style="width:20%">
    <div class="panel panel-warning">
    <div class="panel panel-heading">
      <h4>Monday <button onclick="setDay(0)" class="btn btn-xs btn-primary" style="margin-bottom:5px float:right" data-toggle="modal" data-target="#ohAddModal">+</button></h4>
    </div>
    <table class=table>';

  $row = array();

  while($row = mysqli_fetch_row($schedule_query)){

    $oh_id = $row[0];
    $day = $row[1];
    $time = strtotime($row[2]);
    $firstname = $row[3];
    $lastname = $row[4];
    $guides_id = $row[5];

    while ($day > $day_itr){
      $day_itr = $day_itr+1;
      $html = $html.'</table></div></div>
      <div class="col-md-2" style="width: 20%"">
      <div class="panel panel-warning">
      <div class="panel panel-heading">
        <h4>'.$days[$day_itr].' <button onclick="setDay('.$day_itr.')" class="btn btn-xs btn-primary" style="margin-bottom:5px float:right" data-toggle="modal" data-target="#ohAddModal">+</button></h4>
      </div>
      <table class=table>';
      $time_itr = strtotime("00:00:00");
    }

    if ($time > $time_itr){
      $html = $html.'<tr>
          <td><b>'.date("g:i A",$time).'</td>
          <td> </td>
        </tr>';
      $time_itr = $time;
    }

    $html = $html.'<tr>
          <td>'.$firstname.' '.$lastname.'</td>
          <td>
          <button class="btn btn-xs btn-danger" style="margin-bottom:5px" onclick="delete_oh('.$oh_id.')">X</button>
          </td>
        </tr>';



  }

  echo $html.'</div></div></div>';

?>
