<? date_default_timezone_set('America/New_York');

session_start();

if (!array_key_exists('login', $_SESSION) || !isset($_SESSION['login']) ) {

	session_unset();

	header("location:signin.php");

	die();

} elseif ($_SESSION['login']!='true') {

	session_unset();

	header("location:signin.php");

	die();

} elseif (!array_key_exists('id', $_SESSION) || !array_key_exists('status', $_SESSION) || !isset($_SESSION['id']) || !isset($_SESSION['status'])) {

	session_unset();

	header("location:signin.php");

	die();

} else {



	//April fool's:

	//if (!array_key_exists('fooled',$_SESSION)) {

		//$_SESSION['fooled']=1;

		//header("location:error_not_found.php");

	//}



	//Start referrer-tracking to be able to send a user "back" wherever they came from:

	if (!array_key_exists('previous_page', $_SESSION)) {

		$REFERRER='';

		$_SESSION['previous_page'] = $_SERVER["REQUEST_URI"];

	} else {

		$REFERRER = $_SESSION['previous_page'];

		$_SESSION['previous_page'] = $_SERVER["REQUEST_URI"];

	}



	//Connect to the database:

	try {

		include("functions/link.php");

	} catch(Exception $ex) {

		header("location:dberror.php");

		die();

	}



	//Define exec previleges:

	$exec_results = mysqli_query($link,"SELECT * FROM exec_board WHERE guide_id=".$_SESSION['id']);

	if (mysqli_num_rows($exec_results)) {

		//initialize to false:

		$is_exec = true;

		$is_chair = false;

		$is_vicechair = false;

		$is_techchair = false;

		$is_scheduler = false;

		$is_disciplinarian = false;

		$exec_pos_str = '';

		//Use a loop to allow for the very rare case where a Guide may have multiple positions:

		while ($exec_privileges = mysqli_fetch_array($exec_results)) {

			if ($exec_privileges['is_chair']==1) $is_chair = true;

			if ($exec_privileges['is_vicechair']==1) $is_vicechair = true;

			if ($exec_privileges['is_techchair']==1) $is_techchair = true;

			if ($exec_privileges['is_scheduler']==1) $is_scheduler = true;

			if ($exec_privileges['is_disciplinarian']==1) $is_disciplinarian = true;

			$exec_pos_str = $exec_pos_str.$exec_privileges['position'].", ";

		}

		$exec_pos_str = substr($exec_pos_str,0,-2); //remove final comma and space

	} else {

		$is_exec = false;

		$is_chair = false;

		$is_vicechair = false;

		$is_techchair = false;

		$is_scheduler = false;

		$is_disciplinarian = false;

	}





	//Load IGIS settings:

	$settings_query = mysqli_query($link,"SELECT name, value FROM igis_settings");

	$igis_settings = array();

	while ($setting = mysqli_fetch_array($settings_query)) {

		$igis_settings[$setting['name']] = $setting['value'];

	}



	//first, base-level privelege saying what kind of guide they are:

	switch($_SESSION['status']){

		case 'current':

			$privileges = "Active guide";

			break;

		case 'abroad':

			$privileges = "Abroad guide";

			break;

		case 'alum':

			$privileges = "Guide alum";

			break;

		case 'deleted':

			$privileges = "Deleted guide (shouldn't be here)";

			break;

		default:

			$privileges = "Unknown guide (error or shouldn't be here)";

			break;

	}

	//then, extra privileges

	if ($is_exec) {

		$privileges = $privileges . ", " . $exec_pos_str;

	}

}



 ?>
