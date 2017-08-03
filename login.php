<? date_default_timezone_set('America/New_York');

session_start();



//Set page direction values:

$failedLoginPage = 'failedlogin.php';

$successLoginPage = 'home.php';



// grab the values from the log on form

$uid = $_POST['uid'];

$pwd = md5($_POST['pwd']);



// assign session variables (in case of a new login)

$_SESSION['uid'] = $uid;

$_SESSION['pwd'] = $pwd;



// query databse for this info:

include("functions/link.php");

$guideUN_query = mysqli_query($link,"SELECT firstname, lastname, guide_id, status FROM guides WHERE user_name='$uid' AND password='$pwd'");

$guideEmail_query = mysqli_query($link,"SELECT user_name, firstname, lastname, guide_id, status FROM guides WHERE email='$uid' AND password='$pwd'");



// if this was an incorrect login, dump them right away:

if (mysqli_num_rows($guideUN_query) == 0 && mysqli_num_rows($guideEmail_query) == 0) {

	unset($_SESSION['uid']);

	unset($_SESSION['pwd']);

	$_SESSION['login']="failed";

	header("location:failedlogin.php"); //redirect

} elseif (mysqli_num_rows($guideUN_query) == 1) { // otherwise, get other information and log them in:

	$guide=mysqli_fetch_array($guideUN_query);

	$_SESSION['name'] = $guide['firstname'].' '.$guide['lastname'];

	$_SESSION['truncated_name']=substr($guide['firstname'], 0, 1)." ".$guide['lastname'];

	$_SESSION['id']=$guide['guide_id'];

	$_SESSION['status']=$guide['status'];

	// check to make sure they're an active guide:

	if ($_SESSION['status']=="alum") {

		$_SESSION['login']="alum";

		header("location: ".$failedLoginPage); //redirect

	} elseif ($_SESSION['status']=="deleted") {

		$_SESSION['login']="deleted";

		header("location: ".$failedLoginPage); //redirect

	} else {

		$_SESSION['login']="true";

		// header("location: ".$successLoginPage);
		header("location: "."index.php");

	}

} else {

	$guide=mysqli_fetch_array($guideEmail_query);

	$_SESSION['uid'] = $guide['user_name']; //reset cookie's username field if it was entered as an email address

	$_SESSION['name'] = $guide['firstname'].' '.$guide['lastname'];

	$_SESSION['truncated_name']=substr($guide['firstname'], 0, 1)." ".$guide['lastname'];

	$_SESSION['id']=$guide['guide_id'];

	$_SESSION['status']=$guide['status'];

	// check to make sure they're an active guide:

	if ($_SESSION['status']=="alum") {

		$_SESSION['login']="alum";

		header("location: ".$failedLoginPage); //redirect

	} elseif ($_SESSION['status']=="deleted") {

		$_SESSION['login']="deleted";

		header("location: ".$failedLoginPage); //redirect

	} else {

		$_SESSION['login']="true";

		header("location: ".$successLoginPage);

	}

}

?>
