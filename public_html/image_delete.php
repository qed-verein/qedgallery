<?php

require_once('path.php');
require_once('include/common.php');

define('L_IMAGE_DELETE', "Bild löschen");

$imageId = paramInt('imageid');

$image = DB_DataObject::factory('image');
$res = $image->get($imageId);
if($res == 0) throw new ImageNotFoundException($imageId);

if(!testImagePermissions(PERM_EDIT, $imageId))
	throw new AccessDeniedException();

$layout = new StandardLayout;
$layout->title = L_IMAGE_DELETE;

if(isset($_POST['yes']))
{
	$albumId = $image->albumId;
	galleryDeleteImage($image);

	$layout->content = formatInformation("Das Bild wurde gelöscht", urlAlbumView($albumId));
	$layout->redirectionURL = urlAlbumView($albumId);
}
elseif(isset($_POST['no']))
{
	redirect(urlImageView($imageId, filterFromURL()));
}
else
{
	$message = sprintf("Soll das Bild <i>%s</i> wirklich gelöscht werden?", htmlText($image->title));
	$layout->currentImage = $image;
	$layout->content = formatQuestionYesNo($message, urlImageDelete($image->id));
}

echo $layout->render();

?>
