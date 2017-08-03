<?
date_default_timezone_set('America/New_York');
require_once('../authenticate.php');

$computingIDstr = mysqli_real_escape_string($link, $_POST['computingIDs']);
$firstnamestr = mysqli_real_escape_string($link, $_POST['firstnames']);
$lastnamestr = mysqli_real_escape_string($link, $_POST['lastnames']);
$probieclass = mysqli_real_escape_string($link, $_POST['probieclass']);

$computingIDs = explode(',',$computingIDstr);
$firstnames = explode(',',$firstnamestr);
$lastnames = explode(',',$lastnamestr);

$out = array();
$out['error'] = '';

if (count($computingIDs)!=count($firstnames) || count($computingIDs)!=count($lastnames)) {
	$out['error'] = 'The number of computing IDs, first names, and last names do not match';
} else {
	for ($i=0; $i<count($computingIDs); $i++) {
		$computingID = $computingIDs[$i];
		$email = $computingID."@virginia.edu";
		$firstname = $firstnames[$i];
		$lastname = $lastnames[$i];
		mysqli_query($link,"INSERT INTO guides (user_name, 
password, email, firstname, lastname, status, probie_class) VALUES 
('$computingID','5f4dcc3b5aa765d61d8327deb882cf99','$email','$firstname','$lastname','current','$probieclass')");
		$out['error'] = $out['error'].mysqli_error($link);
	}
}

echo json_encode($out);

?>
