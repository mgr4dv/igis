<? date_default_timezone_set('America/New_York');
require_once("authenticate.php");

$id = $_REQUEST["id"];

if ($is_chair || $is_techchair) {
	$execEdit = true;
	$overridelastupdated = 1;
} else {
	$execEdit = false;
	$overridelastupdated = 0;
}

if ($_SESSION['id']==$_REQUEST['id']) {
	$selfEdit = true;
	$overridelastupdated = 0;
} else {
	$selfEdit = false;
}

//Redirect to the regular view-only page if they don't have the right to be editing this guide:
if (!$execEdit && !$selfEdit) {
	header("location: guide.php?id=".$id);
}

include_once("functions/link.php");

$guide_query=mysqli_query($link,"SELECT * FROM guides WHERE guide_id='$id'");
$guide=mysqli_fetch_array($guide_query);

if($execEdit) {
	$probieclass_query = mysqli_query($link,"SELECT chair FROM probieclass ORDER BY date DESC");
}

$updated = date('n/j/y',strtotime($guide['last_update']));
if ($updated=="11/30/-1") { //this is how a string of zeros gets represented using the above function
	$updated = "Never updated";
} else {
	$updated = "Last updated on ".$updated;
}

$firstname = $guide['firstname'];
$lastname = $guide['lastname'];
$probieclass = unspecifiedIfBlank($guide['probie_class']);
$year = unspecifiedIfBlank($guide['year']);
$school = unspecifiedIfBlank($guide['school']);
$schoolRaw = $guide['school'];
$major = unspecifiedIfBlank($guide['major']);
$email = unspecifiedIfBlank($guide['email']);
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
$birthdayMonth = date('F',strtotime($guide['date_of_birth']));
$birthdayDate = date('j',strtotime($guide['date_of_birth']));
$birthdayYear = date('Y',strtotime($guide['date_of_birth']));
$hometown = unspecifiedIfBlank($guide['hometown']);
$address = unspecifiedIfBlank($guide['school_address']);

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

		<style>
		.btn-file {
			position: relative;
			overflow: hidden;
		}
		.btn-file input[type=file] {
			position: absolute;
			top: 0;
			right: 0;
			min-width: 100%;
			min-height: 100%;
			font-size: 999px;
			text-align: right;
			filter: alpha(opacity=0);
			opacity: 0;
			outline: none;
			background: white;
			cursor: inherit;
			display: block;
		}
		</style>
	</head>

<!-- Body of webpage (not reused, but reusable elements inside) -->
	<body style="padding-top:40px; padding-bottom: 60px">

		<!-- Navigation bar across the top and footer across the bottom -->
		<?include_once("includes/nav.php")?>
		<?include_once("includes/footer.php")?>


		<div class="container">
			<h1 style="cursor:pointer" onclick="window.location='directory.php'">Directory</h1>
			<div class="well">
				<div style="text-align:center">
					<?if ($execEdit) {
						echo '<div class="row">
								<div class="col-md-4"></div>
								<div class="col-md-4">
									<input id="firstnameBox" type="text" class="form-control" style="height:45px; font-size:23pt; text-align:center" placeholder="first name" value="'.$firstname.'">
									<input id="lastnameBox" type="text" class="form-control" style="height:45px; font-size:23pt; text-align:center" placeholder="last name" value="'.$lastname.'">
								</div>
							</div>
							<select id="statusBox" class="form-control" style="display:inline; width:100px; cursor:pointer">
								<option value="current">Active</option>
								<option value="abroad">Abroad</option>
								<option value="alum">Alum</option>
								<option value="deleted">Deleted</option>
							</select>';
					} else {
						echo '<h2 style="margin:0px">'.$firstname." ".$lastname.'</h2>
							<span class="label label-'.$statusColor.'">'.$status.'</span>';
					}?>
					<p style="color:#999999; margin:5px"><em><?echo $updated?></em></p>
				</div>
				<div style="text-align:center">
					<span id="profilePhoto">
						<img class="img-thumbnail" src="<?echo $photoPath?>" style="height:<?echo $imgheight?>px; width:<?echo $imgwidth?>px">
					</span><br>
						<?if ($selfEdit) echo
						'<span class="btn btn-default btn-file" id="changePhotoButton">
							<span id="changePhotoButtonText">Change Photo</span> <input type="file" accept="image/*">
						</span>'?>
						<button class="btn btn-default" id="removePhotoButton">
							<span id="removePhotoButtonText">Remove Photo</span>
						</button>

				</div>
				<div class="row">
					<div class="col-md-4"></div>
					<div class="col-md-4">
						<!--<?if ($selfEdit) echo
						'<p>Change photo: <input id="photoBox" type="file" style="display:inline"></p>'
						?>
						<?if ($hasPicture) echo
						'<p>Remove Photo on update: <input id="removePhotoBox" type="checkbox" style="display:inline"></p>' ?>-->
					</div>
				</div>
				<div class="row" style="margin-top:20px">
					<div class="col-md-3"></div>
					<div class="col-md-6">
						<table class="table">
							<tr>
								<td>Probie Class:</td>
								<td>
									<?if ($execEdit){
										echo '<select id="probieclassBox" class="form-control" style="display:inline; width:150px; cursor:pointer">';
										while ($class=mysqli_fetch_array($probieclass_query)) {
											echo '<option value="'.$class[0].'">'.$class[0].'</option>';
										}
										echo '</select>';
									} else {
										echo '<strong>'.$probieclass.'</strong>';
									}?>
								</td>
							</tr>
							<tr>
								<td>School:</td>
								<td>
									<select id="schoolBox" class="form-control" style="display:inline; width:100px; cursor:pointer">
										<option value="CLAS">CLAS</option>
										<option value="SEAS">SEAS</option>
										<option value="NURS">NURS</option>
										<option value="ARCH">ARCH</option>
										<option value="SED">SED</option>
										<option value="KINE">KINE</option>
										<option value="COMM">COMM</option>
										<option value="PPOL">PPOL</option>
										<option value="GBUS">GBUS</option>
										<option value="MED">MED</option>
										<option value="LAW">LAW</option>
										<option value="SCPS">SCPS</option>
									</select>
								</td>
							</tr>
							<tr>
								<td>Graduation Year:</td>
								<td><input id="yearBox" type="text" class="form-control" value="<?echo $guide['year']?>" placeholder="ex: <?echo date('Y')+1?>"></td>
							</tr>
							<tr>
								<td>Major:</td>
								<td><input id="majorBox" type="text" class="form-control" value="<?echo $guide['major']?>"></td>
							</tr>
							<tr>
								<td>Hometown:</td>
								<td><input id="hometownBox" type="text" class="form-control" value="<?echo $guide['hometown']?>"></td>
							</tr>
							<tr>
								<td>Birthday:</td>
								<td>
									<select id="bdMonthBox" class="form-control" style="display:inline; width:120px; cursor:pointer">
										<option value="January">January</option>
										<option value="February">February</option>
										<option value="March">March</option>
										<option value="April">April</option>
										<option value="May">May</option>
										<option value="June">June</option>
										<option value="July">July</option>
										<option value="August">August</option>
										<option value="September">September</option>
										<option value="October">October</option>
										<option value="November">November</option>
										<option value="December">December</option>
									</select>
									<input id="bdDateBox" type="text" class="form-control" value="<?echo $birthdayDate?>" style="width:45px; display:inline">
									<input id="bdYearBox" type="text" class="form-control" value="<?echo $birthdayYear?>" style="width:60px; display:inline">
								</td>
							</tr>
							<tr>
								<td>Email Address:</td>
								<td><input id="emailBox" type="text" class="form-control" value="<?echo $guide['email']?>"></td>
							</tr>
							<tr>
								<td>Phone Number:</td>
								<td>
									<div class="control-group">
									<input id="phone1Box" type="text" class="form-control" value="<?echo $guide['school_phone_1']?>" style="width:50px; display:inline"> -
									<input id="phone2Box" type="text" class="form-control" value="<?echo $guide['school_phone_2']?>" style="width:50px; display:inline"> -
									<input id="phone3Box" type="text" class="form-control" value="<?echo $guide['school_phone_3']?>" style="width:60px; display:inline">
									</div>
								</td>
							</tr>
							<tr>
								<td>Address:</td>
								<td><textarea id="addressBox" class="form-control" rows="3" style="resize: none"><?echo $guide['school_address']?></textarea></td>
							</tr>
								<td>Password Change:</td>
								<?if ($selfEdit) echo
								'<td>
									<input id="password1Box" type="password" class="form-control" placeholder="New Password">
									<input id="password2Box" type="password" class="form-control" placeholder="Confirm Password">
								</td>';
								else echo
								'<td>
									Reset to "password": <input type="checkbox" id="passwordResetBox">
								</td>';
								?>
							<tr>
						</table>
					</div>
				</div>
				<div style="text-align:center">
					<button id="cancelButton" class="btn btn-default">Cancel</button>
					<button class="btn btn-success" id="saveButton">Save</button>
				</div>
				<div id="testing">
				</div>
			</div>
		</div>



	</body>


	<script>
		//Self-edit variables:
		var year;
		var school;
		var major;
		var hometown;
		var bdMonth;
		var bdDate;
		var bdYear;
		var email;
		var phone1;
		var phone2;
		var phone3;
		var address;

		//Exec-edit variables:
		var firstname;
		var lastname;
		var probieclass;
		var status;

		//Password stuff:
		var password1;
		var password2;
		var newPassword;

		//Photo stuff:
		var photo = 'none';


		$('#saveButton').click( function(){
			updateDB();
		});
		$('#cancelButton').click( function(){
			window.location = '<?echo $REFERRER?>';
		});

		$('#bdMonthBox').val("<?echo $birthdayMonth?>");
		//try {
			$('#schoolBox').val("<?echo $schoolRaw?>"); //if the school is unspecified, it won't be able to do this
		//}
		$('#probieclassBox').val("<?echo addslashes($probieclass)?>");
		$('#statusBox').val("<?echo $guide['status']?>");


		function updateDB(){
			id = <?echo $id?>
			//Self-edit stuff:
			school = $('#schoolBox').val();
			year = $('#yearBox').val();
			major = $('#majorBox').val();
			hometown = $('#hometownBox').val();
			bdMonth = $('#bdMonthBox').val();
			bdMonth = getNumberOfMonth(bdMonth);
			bdDate = $('#bdDateBox').val();
			bdYear = $('#bdYearBox').val();
			email = $('#emailBox').val();
			phone1 = $('#phone1Box').val();
			phone2 = $('#phone2Box').val();
			phone3 = $('#phone3Box').val();
			address = $('#addressBox').val(); //val() works here, even though it seems like html() would too

			//Exec-edit stuff:
			if ('<?echo $execEdit?>'==true) { //need to be explicit because PHP false is just blank, not "false" or 0
				firstname = $('#firstnameBox').val();
				lastname = $('#lastnameBox').val();
				probieclass = $('#probieclassBox').val();
				status = $('#statusBox').val();
			} else {
				firstname = '<?echo addslashes($guide['firstname'])?>';
				lastname = '<?echo addslashes($guide['lastname'])?>';
				probieclass = '<?echo addslashes($guide['probie_class'])?>';
				status = '<?echo $guide['status']?>';
			}

			//Password stuff
			if ('<?echo $selfEdit?>'==true) { //need to be explicit because PHP false is just blank, not "false" or 0
				password1 = $('#password1Box').val();
				password2 = $('#password2Box').val();
				if (password1 == password2) {
					if (password1 != '') {
						newPassword = true;
					} else {
						newPassword = false;
					}
				} else {
					alert('Error! Your passwords don\'t match. Your password will therefore not be changed. (Everything else will be, though.)');
					newPassword = false;
				}
			} else { //must be execEdit if it's not the user's own page but they're here anyway
				//check if checkbox is checked, and if so, make the newPassword=true and password1='password';
				if ($('#passwordResetBox').prop('checked')){
					newPassword = true;
					password1 = 'password'; //reset to default value
				} else {
					newPassword = false;
				}
			}

			$.post("functions/editguide.php",{
				id: id,
				school: school,
				year: year,
				major: major,
				hometown: hometown,
				birthdayY: bdYear,
				birthdayM: bdMonth,
				birthdayD: bdDate,
				email: email,
				phone1: phone1,
				phone2: phone2,
				phone3: phone3,
				address: address,
				firstname: firstname,
				lastname: lastname,
				probieclass: probieclass,
				status: status,
				newPassword: newPassword,
				password: password1,
				overridelastupdated: <?echo $overridelastupdated?>
			}, function(data,returnstatus){
				//alert('Data: '+data+'\nStatus: '+returnstatus);
				if (returnstatus=='success') {
					window.location = "guide.php?id="+id;
				} else {
					alert('Error!')
				}
			});
			//alert('Info: \n Exec = '+'<?echo $execEdit?>'+'\n Self = '+'<?echo $selfEdit?>'+'\n\nName = '+firstname+' '+lastname+'\n Status = '+status+'\n School = '+school+'\n Year = '+year+'\n Major = '+major+'\n Hometown = '+hometown+'\n Birthday = '+bdMonth+' '+bdDate+', '+bdYear+'\n Email = '+email+'\n Phone = ('+phone1+')-'+phone2+'-'+phone3+'\n Address = '+address+'\n\n New Password = '+newPassword+' ("'+password1+'")');
		}

		function getNumberOfMonth(monthStr) {
			switch (monthStr) {
				case 'January':
					return 1;
					break;
				case 'February':
					return 2;
					break;
				case 'March':
					return 3;
					break;
				case 'April':
					return 4;
					break;
				case 'May':
					return 5;
					break;
				case 'June':
					return 6;
					break;
				case 'July':
					return 7;
					break;
				case 'August':
					return 8;
					break;
				case 'September':
					return 9;
					break;
				case 'October':
					return 10;
					break;
				case 'November':
					return 11;
					break;
				case 'December':
					return 12;
					break;
				default:
					return 0;
					break;
			}
		}

		//from http://www.surrealcms.com/blog/whipping-file-inputs-into-shape-with-bootstrap-3
		$(document).on('change', '.btn-file :file', function() {
			var input = $(this),
			numFiles = input.get(0).files ? input.get(0).files.length : 1,
			label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
			file = input.get(0).files[0]; //get only the first one (not that there can be multiples here, I think...)
			input.trigger('fileselect', [numFiles, label, file]);
		});

		$(function() {
			$('#changePhotoButton').on('fileselect', function(event, numFiles, label, file) {
				$('#changePhotoButtonText').html('Uploading...');
				$('#changePhotoButton').prop("disabled",true);
				$('#removePhotoButton').prop("disabled",true);
				//The preview part is adapted from http://www.html5rocks.com/en/tutorials/file/dndfiles/
				$('#profilePhoto').html('<h4 class="img-thumbnail">Loading...</h4>');
				var reader = new FileReader();
				reader.onload = (function(theFile) {
					return function(e) {
						// Render thumbnail.
						$('#profilePhoto').html('<img class="img-thumbnail" style="min-height:150px; height:150px" src="'+e.target.result+'">');
					};
				})(file);
				// Read in the image file as a data URL: (not sure what this does - Kevin, 6/28/14)
				reader.readAsDataURL(file);

				//Do the actual uploading (from http://blog.teamtreehouse.com/uploading-files-ajax ):
				// Create a new FormData object.
				var formData = new FormData();
				// If the file is an image, add the file to the request.
				formData.append('id', <?echo $id?>);
				if (file.type.match('image.*')) {
					formData.append('photo', file, file.name);
				}
				// Set up the request.
				var xhr = new XMLHttpRequest();
				// Open the connection.
				xhr.open('POST', 'functions/changephoto.php', true);

				// Send the Data.
				xhr.send(formData);

				// When the request finishes:
				xhr.onload = function () {
					if (xhr.status === 200) {
						// File(s) uploaded.
						//alert('Successfully uploaded.\nEcho from server:\n'+xhr.responseText);
						$('#changePhotoButtonText').html('Change Photo');
						$('#changePhotoButton').prop("disabled",false);
						$('#removePhotoButton').prop("disabled",false);
					} else {
						alert('A server error occurred (in changephoto.php) uploading the file!');
						$('#changePhotoButtonText').html('Change Photo');
						$('#changePhotoButton').prop("disabled",false);
						$('#removePhotoButton').prop("disabled",false);
					}
				};

			});

			$('#removePhotoButton').on('click', function() {
				$('#profilePhoto').html('<img class="img-thumbnail" src="guide_images/unknown.jpg" style="min-height:150px; height:150px">');
				$('#changePhotoButton').prop("disabled",true);
				$('#removePhotoButton').prop("disabled",true);
				//delete the image: (from http://blog.teamtreehouse.com/uploading-files-ajax adapted from above):
				var formData = new FormData();
				formData.append('id', <?echo $id?>);
				var xhr = new XMLHttpRequest();
				xhr.open('POST', 'functions/changephoto.php', true);
				xhr.send(formData);

				// When the request finishes:
				xhr.onload = function () {
					if (xhr.status === 200) {
						$('#changePhotoButton').prop("disabled",false);
						$('#removePhotoButton').prop("disabled",false);
					} else {
						alert('A server error occurred (in changephoto.php) deleting the file!');
						$('#changePhotoButton').prop("disabled",false);
						$('#removePhotoButton').prop("disabled",false);
					}
				};
			});
		});


	</script>
</html>
