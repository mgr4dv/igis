<? date_default_timezone_set('America/New_York');
require_once("../authenticate.php");


include("./link.php");

$type = $_REQUEST["type"];

switch($type){
  case "unhandled":
    $where_part = "handled=0 AND CURDATE()>=DATE(sch_time)";
    break;
  case "future":
    $where_part = "CURDATE()<DATE(sch_time)
                  OR (CURDATE()=DATE(sch_time) AND TIME(sch_time)>CURTIME())";
    break;
  case "lastMonth":
    $where_part = "CURDATE()>DATE(sch_time)
                  AND DATE(".date("Ymd",strtotime("-1 Month")).")<=DATE(sch_time)
                  AND handled =1";
    break;
  case "lastAll":
    $where_part = "True";
    break;
  default:
    $where_part = "handled=0 AND CURDATE()>=DATE(sch_time)";
    break;

}

$html =
 "<center>
  <b>With selected:</b><br>
  <button type='button' class='btn btn-primary' onClick='handle(-1)'>Handle</button>
  <button type='button' class='btn btn-success' onClick='cover(-1)'>Cover</button>
  <button type='button' class='btn btn-danger' onClick='miss(-1)'>Miss</button>
  <button type='button' class='btn btn-warning' onClick='late(-1)'>Late</button>
  </center>

<br>

<table class='table' id='oh_table'>
  <thead>
    <tr>
      <th>Delete</th>
      <th>Originally Scheduled</th>
      <th>Covering</th>
      <th>Scheduled Time</th>
      <th>Logged Time</th>
      <th>Actions</th>
      <th>Select</th>
    </tr>
  </thead>
  <tbody>";

$oh_query = mysqli_query($link,"SELECT log_id,sch_id,cover_id,sch_time,log_time,
                                firstname,lastname,handled
                                FROM oh_log LEFT JOIN guides ON cover_id=guide_id
                                WHERE ".$where_part."
                                ORDER BY sch_time");

  while($oh = mysqli_fetch_array($oh_query)){
    $log_id = $oh[0];
    $sch_id = $oh[1];
    $cover_id = $oh[2];
    $sch_time = $oh[3];
    $log_time = $oh[4];
    $firstname= $oh[5];
    $lastname = $oh[6];
    $handled = $oh[7];
    $row_color = "";

    if($sch_id != $cover_id){
      $sch_query = mysqli_query($link,"SELECT firstname,lastname
        FROM guides
        WHERE guide_id=".$sch_id);
        $sch_retrieve = mysqli_fetch_row($sch_query);
        $sch_firstname = $sch_retrieve[0];
        $sch_lastname = $sch_retrieve[1];
        $row_color = "#5eff59";
      } else {
        $sch_firstname = $firstname;
        $sch_lastname = $lastname;
        $row_color = "";
      }

    if ($log_time == ""){
      $log_time = "NULL";
    } else {
      $log_time = date("D j/m/Y - g:i a",strtotime($log_time));
    }


    $delete_btn = "<button type='button' class='btn btn-sm btn-danger' onClick='deleteoh(".$log_id.")'>X</button>";

    if($handled == 0){
      $btn = "<button type='button' class='btn btn-primary' onClick='handle(".$log_id.")'>Handle</button>
          <button type='button' class='btn btn-success' onClick='cover(".$log_id.")'>Cover</button>
          <button type='button' class='btn btn-danger' onClick='miss(".$log_id.")'>Miss</button>
          <button type='button' class='btn btn-warning' onClick='late(".$log_id.")'>Late</button>";
    } else {
      $btn ="<button type='button' class='btn btn-primary' onClick='unhandle(".$log_id.")'>Unhandle</button>";
    }

    $sch_time = date("D j/m/Y - g:i a",strtotime($sch_time));

    $select = "<input type='checkbox' value='$log_id'>";

    $html = $html."<tr bgcolor='$row_color'>
                    <td> $delete_btn</td>
                    <td>".$sch_firstname." ".$sch_lastname."</td>
                    <td>".$firstname." ".$lastname."</td>
                    <td>".$sch_time."</td>
                    <td>".$log_time."</td>
                    <td>".$btn."</td>
                    <td>".$select."</td>
                  </tr>";
  }
  echo $html."</tbody></table>"
?>
