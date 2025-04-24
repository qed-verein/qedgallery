<?php

require_once('path.php');
require_once('include/common.php');
require_once('views/image_html_neu.php');

define('L_IMAGE_VIEW', "Bild anzeigen");

$imageId = paramInt('imageid');

if(!testImagePermissions(PERM_VIEW, $imageId))
{
	if($gUser->id == USER_ANONYMOUS_ID)
		redirect(urlLoginAndRedirectToImage($imageId));
	throw new AccessDeniedException();
}

$image = DB_DataObject::factory('image');
handleImageFilters($image, filterFromURL());
$res = $image->get($imageId);
if($res == 0) throw new ImageNotFoundException($imageId);
$album = $image->getLink('albumId');


$imageRenderer = new ImageRendererNeu($image);
echo renderImageLayout(
    sprintf("Bild anzeigen: %s", $image->title),
    $imageRenderer->render());

?>
