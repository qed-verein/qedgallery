<?php

require_once('path.php');
require_once('include/common.php');
require_once('views/upload_form.php');

define('L_IMAGE_UPLOAD', "Bild hochladen");

$albumId = paramInt('albumid');
$album = DB_DataObject::factory('album');
$res = $album->get($albumId);
if($res == 0) throw new AlbumNotFoundException($albumId);

if(!testAlbumPermissions(PERM_UPLOAD, $albumId))
	throw new AccessDeniedException();

$layout = new StandardLayout;
$layout->title = L_IMAGE_UPLOAD;
$layout->currentAlbum = $album;

if(isset($_REQUEST['upload']))
{
	try
	{
		$filenames = $_REQUEST['file'];
		foreach($filenames as $origName)
		{
			$tempName = $_SESSION['sliceupload'][$origName];

			$pathinfo = pathinfo($origName);
			$extension = isset($pathinfo['extension']) ?
				strtolower($pathinfo['extension']) : "";
			if($extension == "zip")
				handleZipUpload($album, $tempName);
			else
				handleImageUpload($album, $tempName, $origName);
			unlink($tempName);
		}

		$layout->content = formatInformation("Die Bilder wurden hochgeladen",
			urlAlbumView($album->id));
	}
	catch(InvalidInputException $e)
	{
		$layout->uploadMode = true;
		$layout->content = renderUploadForm($album, $e->getMessage());
	}
}
else
{
	$layout->uploadMode = true;
	$layout->content = renderUploadForm($album);
}

echo $layout->render();

?>
