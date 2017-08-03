<? date_default_timezone_set('America/New_York');
require_once('../authenticate.php');

$display = $_REQUEST["display"];
$order_by = $_REQUEST["order"];
$categorize = $_REQUEST["cat"];

$display_query = '';
$order_query = '';

switch ($display) {
	case 'active':
		$display_query="status='current'";
		break;
	case 'abroad':
		$display_query="status='abroad'";
		break;
	case 'alumni':
		$display_query="status='alum'";
		break;
	case 'deleted':
		$display_query="status='deleted'";
		break;
	default:
		$display_query="status='current'";
		break;
}

switch ($order_by) {
	case 'lastname':
		$order_query="lastname ASC, firstname ASC";
		break;
	case 'firstname':
		$order_query="firstname ASC, lastname ASC";
		break;
	case 'probieclass':
		$order_query="probie_class ASC, lastname ASC, firstname ASC";
		break;
	case 'year':
		$order_query="year ASC, lastname ASC, firstname ASC";
		break;
	case 'school':
		$order_query="school ASC, lastname ASC, firstname ASC";
		break;
	case 'lastupdated':
		$order_query="last_update DESC, lastname ASC, firstname ASC";
		break;
	default:
		$order_query="lastname ASC, firstname ASC";
		break;
}

$directory_query=mysqli_query($link,"select guide_id, firstname, lastname, picture_status, picture_name, school, year, probie_class, last_update, email, school_phone_1, school_phone_2, school_phone_3 from guides WHERE $display_query ORDER BY $order_query");
$directory_content = '';

$prevCategory = '';
while($guideToPrint=mysqli_fetch_array($directory_query)) {
	//Account for the fact that some people don't update all their information:
	
	$updateStr = date('n/j/y',strtotime($guideToPrint['last_update']));
	if ($updateStr=="11/30/-1") { //this is how a string of zeros gets represented using the above function
		$updateStr = "Never updated";
	} else {
		$updateStr = "Updated ".$updateStr;
	}
	
	if ($guideToPrint['probie_class']=="-" || $guideToPrint['probie_class']=="") {
		$probieStr = "<em style=\"color:#BBBBBB\">unknown</em>";
	} else {
		$probieStr = $guideToPrint['probie_class'];
	}
	
	if ($guideToPrint['year']=="0" || $guideToPrint['year']=="") {
		$yearStr = "<em style=\"color:#BBBBBB\">unset</em>";
	} else {
		$yearStr = $guideToPrint['year'];
	}
	
	if ($guideToPrint['school']=="") {
		$schoolStr = "<em style=\"color:#BBBBBB\">unset</em>";
	} else {
		$schoolStr = $guideToPrint['school'];
	}

	
	
	
	
	$catRow = "";
	$newCategory = false;
	if ($categorize) {
		switch ($order_by) {
			case 'probieclass':
				if ($probieStr!=$prevCategory) {
					$newCategory = true;
				}
				$prevCategory = $probieStr;
				break;
			case 'year':
				if ($yearStr!=$prevCategory) {
					$newCategory = true;
				}
				$prevCategory = $yearStr;
				break;
			case 'school':
				if ($schoolStr!=$prevCategory) {
					$newCategory = true;
				}
				$prevCategory = $schoolStr;
				break;
			default:
				break;
		}
		if ($newCategory) {
			$catRow = "<tr><td colspan=5 style=\"text-align:center\"><h3><span class=\"label label-default\">".$prevCategory."</span></h3></td></tr>";
		}
	}

	
	
	$row = "<tr>
			<td style=\"vertical-align:middle\"><h4 style=\"margin:0px\"><a href=\"guideEdit.php?id=".$guideToPrint['guide_id']."\">".$guideToPrint['firstname']." ".$guideToPrint['lastname']."</a></h4><small style=\"color:#999999\"><em>".$updateStr."</em></small></td>
			<td style=\"vertical-align:middle\">".$schoolStr."</td>
			<td style=\"vertical-align:middle\">".$yearStr."</td>
			<td style=\"vertical-align:middle\">".$probieStr."</td>
			<td style=\"vertical-align:middle\"><input type=\"checkbox\" value=\"".$guideToPrint['guide_id']."\"></td>
			</tr>";
	
	$directory_content = $directory_content . "\n" . $catRow . $row;
}

echo $directory_content;


?>