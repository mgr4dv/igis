<?
require_once("../authenticate.php");

$id = $_POST['id'];
if (isset($_FILES['photo'])) {
	//delete any existing photos with this ID number:
	deletePhotos($id);
	//replace it on the server:
	$photo = $_FILES['photo'];
	$extension = pathinfo($photo["name"], PATHINFO_EXTENSION);
	move_uploaded_file($photo["tmp_name"],"../guide_images/".$id.".".$extension);
	//resize:
	resize("../guide_images/".$id.".".$extension,300);
} else {
	//just delete:
	deletePhotos($id);
}


function deletePhotos($guide_id) {
	//delete all photos (though there shouldn't be more than one) that match the ID:
	$photoList = glob("../guide_images/".$guide_id.".*");
	for ($i=0; $i<count($photoList); $i++) {
		unlink($photoList[$i]);
	}
}


function resize($filename, $new_height) {
	// Adapted from code found in IGIS 2.0's "image_resize_functions.php",
	// Which was adapted from forums at php.net

	//Check the dimensions:
	list($org_width, $org_height) = getimagesize($filename);
	//If the height is already less than $new_height, don't do anything (just return true):
	if ($new_height > $height) return true;

	//Turn the file into an image that PHP can use:
	$extension = pathinfo($filename, PATHINFO_EXTENSION);
	switch($extension) {
		case 'gif' :
			$img = imagecreatefromgif($filename);
			break;
		case 'png' :
			$img = imagecreatefrompng($filename);
			break;
		case 'bmp' :
			$img = imagecreatefromwbmp($filename);
		case 'jpg' :
		case 'jpeg' :
			$img = imagecreatefromjpeg($filename);
			break;
		default :
			return false;
			break;
	}

	$scaleFactor = $new_height/$org_height;
	$new_width = $org_width * $scaleFactor;

	$new_img=imagecreatetruecolor ($new_width, $new_height);
	if (!$new_img) return false;
	if (!imagecopyresampled($new_img, $img, 0, 0, 0, 0, $new_width, $new_height, $org_width, $org_height))
		return false;

	switch ($extension) {
		case 'gif' :
			if (!imagegif($new_img, $filename)) return false;
			break;
		case 'png' :
			if (!imagepng($new_img, $filename)) return false;
			break;
		case 'bmp' :
			if (!imagewbmp($new_img, $filename)) return false;
		case 'jpg' :
		case 'jpeg' :
			if (!imagejpeg($new_img, $filename)) return false;
			break;
		default :
			return false;
			break;
	}


	return true;
}
?>
