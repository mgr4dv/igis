<? date_default_timezone_set('America/New_York');
require_once("authenticate.php");

if ($_SESSION['id']==$_REQUEST["id"] || $is_chair || $is_techchair) {
	$editable = true;
} else {
	$editable = false;
}

$id = $_REQUEST["id"];

include("functions/link.php");

$guide_query=mysqli_query($link,"select * from guides WHERE guide_id='$id'");
$guide=mysqli_fetch_array($guide_query);

$updated = date('n/j/y',strtotime($guide['last_update']));
if ($updated=="11/30/-1") { //this is how a string of zeros gets represented using the above function
	$updated = "Never updated";
} else {
	$updated = "Last updated on ".$updated;
}

$firstname = htmlspecialchars($guide['firstname']);
$lastname = htmlspecialchars($guide['lastname']);
$probieclass = unspecifiedIfBlank(htmlspecialchars($guide['probie_class']));
$year = unspecifiedIfBlank(htmlspecialchars($guide['year']));
$school = unspecifiedIfBlank(htmlspecialchars($guide['school']));
$major = unspecifiedIfBlank(htmlspecialchars($guide['major']));
$email = unspecifiedIfBlank(htmlspecialchars($guide['email']));
if ($guide['email']!='') {
	$emailLink1 = '<a href="mailto:'.$guide['email'].'">';
	$emailLink2 = '</a>';
} else {
	$emailLink1 = '';
	$emailLink2 = '';
}
$email = $emailLink1.$email.$emailLink2;
$phone = unspecifiedIfBlank("(".$guide['school_phone_1'].")-".$guide['school_phone_2']."-".$guide['school_phone_3']);
if ($guide['school_phone_1'].$guide['school_phone_2'].$guide['school_phone_3']!='0000000000') {
	$phoneLink1 = '<a href="tel:'.$guide['school_phone_1'].$guide['school_phone_2'].$guide['school_phone_3'].'">';
	$phoneLink2 = '</a>';
} else {
	$phoneLink1 = '';
	$phoneLink2 = '';
}
$phone = $phoneLink1.$phone.$phoneLink2;
$birthday = unspecifiedIfBlank(date('F jS, Y',strtotime($guide['date_of_birth'])));
$hometown = unspecifiedIfBlank(htmlspecialchars($guide['hometown']));
$address = unspecifiedIfBlank(htmlspecialchars($guide['school_address']));

$status = $guide['status'];
switch ($status) {
	case 'current':
		$status = 'Active';
		$statusColor = 'success';
		break;
	case 'abroad':
		$status = 'Abroad';
		$statusColor = 'warning';
		break;
	case 'alum':
		$status = 'Alum';
		$statusColor = 'info';
		break;
	case 'deleted';
		$status = 'Deleted';
		$statusColor = 'danger';
		break;
	default:
		$status = 'unknown';
		$statusColor = 'default';
		break;
}



$photoList = glob("guide_images/".$id.".*");
if (count($photoList)) {
	$hasPicture = true;
	$photoPath = $photoList[0]; //just the first one, in case there are multiples
} else {
	$hasPicture = false;
	$photoPath = "guide_images/unknown.jpg";
}
list($imgwidth_org, $imgheight_org, $imgtype, $imgattr) = getimagesize($photoPath);
$aspectRatio = $imgwidth_org/$imgheight_org;
if ($imgheight_org<=150) {
	$imgheight=$imgheight_org;
} else {
	$imgheight=150;
}
$imgwidth = $imgheight*$aspectRatio;
$imgwidth = round($imgwidth);
$imgheight = round($imgheight);



function unspecifiedIfBlank($data) {
	if ($data=="" || $data=="(000)-000-0000" || $data=="()--" || $data=="0" || $data=="-" || $data=="11/30/-1" || $data=="November 30th, -0001" || $data=="December 31st, 1969") {
		return "<p style=\"color:#BBBBBB; margin:0px\"><em>(unspecified)</em></p>";
	} else {
		return $data;
	}
}
?>

<!DOCTYPE html>

<html lang="en">

<!-- Header information for webpage (reused except for title) -->
	<head>
		<?
		include_once("includes/head.php");
		?>
	</head>

<!-- Body of webpage (not reused, but reusable elements inside) -->
	<body>

		<!-- Navigation bar across the top and footer across the bottom -->
		<?include_once("includes/nav.php")?>
		<?include_once("includes/footer.php")?>


		<div class="container">
			<h1 style="cursor:pointer" onclick="window.location='directory.php'">Directory</h1>
			<div class="well" style="margin-top:10px">
				<div style="text-align:center">
					<h2 style="margin:0px"><?echo $firstname." ".$lastname?></h2> <span class="label label-<?echo $statusColor?>"><?echo $status?></span>
					<p style="color:#999999; margin:5px"><em><?echo $updated?></em></p>
				</div>
				<? if ($hasPicture) echo
				'<div style="text-align:center">
					<img class="img-thumbnail" src="'.$photoPath.'" alt="'.$firstname.' '.$lastname.'\'s profile picture" style="height:'.$imgheight.'px; width:'.$imgwidth.'px">
				</div>'?>
				<div class="row" style="margin-top:20px">
					<div class="col-md-3"></div>
					<div class="col-md-6">
						<table class="table">
							<tr>
								<td>Probie Class:</td>
								<td><strong><?echo $probieclass?></strong></td>
							</tr>
							<tr>
								<td>Class:</td>
								<td><strong><?echo $school." ".$year?></strong></td>
							</tr>
							<tr>
								<td>Major:</td>
								<td><strong><?echo $major?></strong></td>
							</tr>
							<tr>
								<td>Hometown:</td>
								<td><strong><?echo $hometown?></strong></td>
							</tr>
							<tr>
								<td>Birthday:</td>
								<td><strong><?echo $birthday?></strong></td>
							</tr>
							<tr>
								<td>Email Address:</td>
								<td><strong><?echo $email?></strong></td>
							</tr>
							<tr>
								<td>Phone Number:</td>
								<td><strong><?echo $phone?></strong></td>
							</tr>
							<tr>
								<td>Address:</td>
								<td><strong><?echo nl2br($address)?></strong></td>
							</tr>
						</table>
					</div>
				</div>
				<? if ($editable) echo
				'<div style="text-align:center">
					<button id="editButton" class="btn btn-danger">Edit</button>
				</div>' ?>
			</div>
		</div>



	</body>

	<script>

		$('#editButton').click( function(){
			if (<?echo $editable?>) { //redundant check for added security
				window.location = "guideEdit.php?id=<?echo $id?>";
			}
		});
	</script>
</html>
