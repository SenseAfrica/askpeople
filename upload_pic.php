<?php 
//TODO before packaging:
//upload_max_filesize in php.ini
session_start();
$_SESSION['img_name'] = "";
$_SESSION['img_error'] = "";
$random_key = /*microtime(true)*/$id; 				
$upload_path = "logos/";
$upload_dir = "logos"; 				 					        
$max_size = "3"; 							
$max_width = "100";													
//allowed image types for upload
$allowed_image_types = array('image/pjpeg'=>"jpg",'image/jpeg'=>"jpg",'image/jpg'=>"jpg",'image/png'=>"png",'image/x-png'=>"png",'image/gif'=>"gif");
$allowed_image_ext = array_unique($allowed_image_types);
$image_ext = "";
foreach ($allowed_image_ext as $mime_type => $ext) {
    $image_ext.= strtoupper($ext)." ";
}
if(!is_dir($upload_dir)){
	mkdir($upload_dir);
}
//Image Locations
$image_location = $upload_path.$random_key;
/*
if (isset($_POST["orgname"])) {
*/
	//Get the file information
	$userfile_name = $_FILES['logo']['name'];
	$userfile_tmp = $_FILES['logo']['tmp_name'];
	$userfile_size = $_FILES['logo']['size'];
	$userfile_type = $_FILES['logo']['type'];
	$filename = basename($_FILES['logo']['name']);
	$file_ext = strtolower(substr($filename, strrpos($filename, '.') + 1));
	
	//Only process if the file is a JPG, PNG or GIF and below the allowed limit
	if(!empty($_FILES["logo"]) && ($_FILES['logo']['error'] == 0)){
		foreach ($allowed_image_types as $mime_type => $ext) {
			if($file_ext==$ext && $userfile_type==$mime_type){
				$error = "";
				break;
			}else{
				$error = "unsupported file format";
			}
		}
		//check file size
		if ($userfile_size > ($max_size*1048576)) {
			$error.= "Image size exceeded: max ".$max_size."MB";
		}
	}
	else{	$error= "Image is invalid"; }
	if (strlen($error)==0){ //upload file
		if (isset($_FILES['logo']['name'])){
			$fullname = $image_location.".jpg";//new image extension jpg
			$image_location = $image_location.".".$file_ext;
			move_uploaded_file($userfile_tmp, $image_location);
			//Scale image
			list($width, $height) = getimagesize($image_location);
			$scale = $max_width/$width;
			$uploaded = resizeImage($image_location,$width,$height,$scale);
			$_SESSION['img_name'] = $uploaded;		
		}
	}
/*
	$_SESSION['img_error'] = $error;

echo <<<ENDHTML
<script type="text/javascript">
	parent.uploadDone();
</script>
ENDHTML;
	exit();
}
*/
function resizeImage($image,$width,$height,$scale) {
	$size = getimagesize($image);
	$imageType = $size[2];
	$imageType = image_type_to_mime_type($imageType);
	$newImageWidth = ceil($width * $scale);
	$newImageHeight = ceil($height * $scale);
	$newImage = imagecreatetruecolor($newImageWidth,$newImageHeight);
	switch($imageType) {
		case "image/gif":
			$source=imagecreatefromgif($image); 
			break;
	    case "image/pjpeg":
		case "image/jpeg":
		case "image/jpg":
			$source=imagecreatefromjpeg($image); 
			break;
	    case "image/png":
		case "image/x-png":
			$source=imagecreatefrompng($image); 
			break;
  	}
	imagecopyresampled($newImage,$source,0,0,0,0,$newImageWidth,$newImageHeight,$width,$height);
	//convert all images to jpg formats
	global $fullname;
	unlink($image);
	switch($imageType) {
		case "image/gif":
      	case "image/pjpeg":
		case "image/jpeg":
		case "image/jpg":
		case "image/png":
		case "image/x-png":
			imagejpeg($newImage,$fullname,90);	  
			break;
    }
	return $fullname;
}

?>