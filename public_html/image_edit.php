<?php

require_once('path.php');
require_once('include/common.php');
require_once('views/image_form.php');


//function rotateImage($image, $angle)
//{
	//$originalPath = imagePath($image, 'original');

	//if($image->mimeType == 'image/jpeg')
	//{
		//$command = sprintf("jpegtran -rotate %d -copy all -outfile %s %s", $angle,
			//escapeshellarg($originalPath), escapeshellarg($originalPath));
		//system($command);
	//}
	//else
	//{
		//$imageFile = new Imagick($originalPath);
		//$imageFile->rotateImage(new ImagickPixel(), $angle);
		//$imageFile->writeImage($originalPath);
		//$imageFile->destroy();
	//}

	//regenerateThumbnails($image);
//}

define('L_IMAGE_EDIT', "Bildeinstellungen");

$imageId = paramInt('imageid');

$image = DB_DataObject::factory('image');
$res = $image->get($imageId);
if($res == 0) throw new ImageNotFoundException($imageId);

if(!testImagePermissions(PERM_EDIT, $imageId))
	throw new AccessDeniedException();

$album = $image->getLink('albumId');

$layout = new StandardLayout;
$layout->title = L_IMAGE_EDIT;
$layout->currentImage = $image;

if(isset($_POST['edit']))
{
	$image->title = paramString('title');
	$image->category = paramString('category');
	$image->rights = isset($_REQUEST['rights']);

	try
	{
		validateImage($image);
		$image->update();

		$layout->content = formatInformation("Die Änderungen wurden gespeichert",
			urlImageDetails($image->id, filterFromURL()));
		$layout->redirectionURL = urlImageDetails($image->id, filterFromURL());
	}
	catch(InvalidInputException $e)
	{
		$layout->content = renderImageForm($image, $e->getMessage());
	}
}
//elseif(isset($_REQUEST['rotate']))
//{
	//$direction = paramString('rotate');
	//if($direction == 'left') $angle = 270;
	//elseif($direction == 'right') $angle = 90;
	//else throw InvalidArgumentException();
	//rotateImage($image, $angle);
	//redirect(urlImageView($image->id));
//}
else
{
	$layout->content = renderImageForm($image);
}

echo $layout->render();

?>
