<?
date_default_timezone_set('America/New_York');
require_once('../authenticate.php');

//set variables and sanitize them for input:
$id=mysqli_real_escape_string($link, $_POST['id']);
$updated = date("Y-m-d H:i:s"); //timestamp for right now, in MySQL format
if (isset($_POST['school'])) $school = mysqli_real_escape_string($link, $_POST['school']);
if (isset($_POST['year']))$year = intval($_POST['year']); //foce year to be a number
if (isset($_POST['major']))$major = mysqli_real_escape_string($link, $_POST['major']);
if (isset($_POST['hometown']))$hometown = mysqli_real_escape_string($link, $_POST['hometown']);
if (isset($_POST['birthdayY']) && isset($_POST['birthdayM']) && isset($_POST['birthdayD'])) {
	$birthdayY = intval($_POST['birthdayY']); //force year to be a number
	$birthdayM = intval($_POST['birthdayM']); //force month to be a number (it's already been converted from month string at this point)
	$birthdayD = intval($_POST['birthdayD']); //force date to be a number
	$birthday = date("Y-m-d H:i:s",mktime(0,0,0,$birthdayM,$birthdayD,$birthdayY));
}
if (isset($_POST['email']))$email = mysqli_real_escape_string($link, $_POST['email']);
if (isset($_POST['phone1']) && isset($_POST['phone2']) && isset($_POST['phone3'])) {
	$phone1 = intval($_POST['phone1']); //force phone to be a number
	$phone2 = intval($_POST['phone2']);
	$phone3 = intval($_POST['phone3']);
}
if (isset($_POST['address']))$address = mysqli_real_escape_string($link, $_POST['address']);
if (isset($_POST['firstname']))$firstname = mysqli_real_escape_string($link, $_POST['firstname']);
if (isset($_POST['lastname']))$lastname = mysqli_real_escape_string($link, $_POST['lastname']);
if (isset($_POST['probieclass']))$probieclass = mysqli_real_escape_string($link, $_POST['probieclass']);
if (isset($_POST['status']))$status = mysqli_real_escape_string($link, $_POST['status']);
if (isset($_POST['newPassword']))$newPassword = $_POST['newPassword'];

if (isset($_POST['password']))$password = mysqli_real_escape_string($link, 	md5($_POST['password'])) ;

if (isset($_POST['overridelastupdated']))$override = $_POST['overridelastupdated'];

if ($id!=$_SESSION['id'] && !$is_chair && !$is_techchair) {
	echo "Error: You don't have permission to edit this Guide.";
} else {
	if (!$override) {
		$updates = "last_update='$updated', ";
	}
	if (isset($school)) {
		$updates = $updates."school='$school', ";
	}
	if (isset($year)) {
		$updates = $updates."year=$year, ";
	}
	if (isset($major)) {
		$updates = $updates."major='$major', ";
	}
	if (isset($hometown)) {
		$updates = $updates."hometown='$hometown', ";
	}
	if (isset($birthday)) {
		$updates = $updates."date_of_birth='$birthday', ";
	}
	if (isset($email)) {
		$updates = $updates."email='$email', ";
	}
	if (isset($phone1)) {
		$updates = $updates."school_phone_1=$phone1, ";
	}
	if (isset($phone2)) {
		$updates = $updates."school_phone_2=$phone2, ";
	}
	if (isset($phone3)) {
		$updates = $updates."school_phone_3=$phone3, ";
	}
	if (isset($address)) {
		$updates = $updates."school_address='$address', ";
	}
	if (isset($firstname)) {
		$updates = $updates."firstname='$firstname', ";
	}
	if (isset($lastname)) {
		$updates = $updates."lastname='$lastname', ";
	}
	if (isset($probieclass)) {
		$updates = $updates."probie_class='$probieclass', ";
	}
	if (isset($status)) {
		$updates = $updates."status='$status', ";
	}
	if ($newPassword=='true') {
		$updates = $updates."password='$password', ";
	}
	$updates = substr($updates, 0, -2); //Remove last comma and space
	$result = mysqli_query($link,"UPDATE guides SET $updates WHERE guide_id=$id");
	echo $updates."\n\nMySQL Error: ".mysqli_error($link);
}




?>
