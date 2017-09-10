<? date_default_timezone_set('America/New_York');
require_once("../authenticate.php");

if (isset($_POST['chair']) && isset($_POST['vicechair']) && isset($_POST['scheduler']) && isset($_POST['disciplinarian']) && isset($_POST['techchair'])) {
	$chair = $_POST['chair'];
	$vicechair = $_POST['vicechair'];
	$scheduler = $_POST['scheduler'];
	$disciplinarian = $_POST['disciplinarian'];
	$techchair = $_POST['techchair'];
	$others = explode(",",$_POST['others']);
	$otherPositions = explode(",",$_POST['otherPositions']);
	$otherCs = explode(",",$_POST['otherCs']);
	$otherVs = explode(",",$_POST['otherVs']);
	$otherSs = explode(",",$_POST['otherSs']);
	$otherDs = explode(",",$_POST['otherDs']);
	$otherTs = explode(",",$_POST['otherTs']);

	$out = array();
	$out['error'] = '';
	
	//First, clear the Exec list to start fresh every time (ensures no extra people are hanging around):
	$success = mysqli_query($link,"TRUNCATE TABLE exec_board");
	$out['error'] = $out['error'].mysqli_error($link);
	
	//Then, add the chair:
	$success = mysqli_query($link,"INSERT INTO exec_board (guide_id, position, is_chair, official) VALUES (".$chair.",'Chair',1,1)");
	$out['error'] = $out['error'].mysqli_error($link);
	
	//Then, add the vice chair:
	$success = mysqli_query($link,"INSERT INTO exec_board (guide_id, position, is_vicechair, official) VALUES (".$vicechair.",'Vice Chair',1,1)");
	$out['error'] = $out['error'].mysqli_error($link);
	
	//Then, add the scheduler:
	$success = mysqli_query($link,"INSERT INTO exec_board (guide_id, position, is_scheduler, official) VALUES (".$scheduler.",'Scheduler',1,1)");
	$out['error'] = $out['error'].mysqli_error($link);
	
	//Then, add the disciplinarian:
	$success = mysqli_query($link,"INSERT INTO exec_board (guide_id, position, is_disciplinarian, official) VALUES (".$disciplinarian.",'Disciplinarian',1,1)");
	$out['error'] = $out['error'].mysqli_error($link);
	
	//Then, add the tech chair:
	$success = mysqli_query($link,"INSERT INTO exec_board (guide_id, position, is_techchair, official) VALUES (".$techchair.",'Tech Chair',1,1)");
	$out['error'] = $out['error'].mysqli_error($link);
	
	//Then, add the others:
	if (count($others>0)) {
		for ($i=0; $i<count($others); $i++) {
			$otherID = $others[$i];
			$position = $otherPositions[$i];
			$Cpriv = $otherCs[$i];
			$Vpriv = $otherVs[$i];
			$Spriv = $otherSs[$i];
			$Dpriv = $otherDs[$i];
			$Tpriv = $otherTs[$i];
			$success = mysqli_query($link,"INSERT INTO exec_board (guide_id, position, is_chair, is_vicechair, is_scheduler, is_disciplinarian, is_techchair) VALUES ($otherID, '$position', $Cpriv, $Vpriv, $Spriv, $Dpriv, $Tpriv)");
			$out['error'] = $out['error'].mysqli_error($link);
		}
	}

	echo json_encode($out);
} else {
	$out['error'] = "You must specify the entire exec board to be able to set it.";
}
?>