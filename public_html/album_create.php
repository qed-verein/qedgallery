<?php

require_once('path.php');
require_once('include/common.php');
require_once('views/album_form.php');

define('L_ALBUM_CREATE', "Album erstellen");

if(!testAlbumPermissions(PERM_UPLOAD))
	throw new AccessDeniedException();

$album = DB_DataObject::factory('album');
$album->ownerId = $gUser->id;
$album->creationTime = time();

$layout = new StandardLayout;
$layout->title = L_ALBUM_CREATE;

if(isset($_POST['create']))
{
	$album->title = paramString('title');
	$album->description = paramString('description');
	$album->originalFrom = parseDateTime(paramString('originalfrom'));
	$album->originalTill = parseDateTime(paramString('originaltill'));
	$album->rights = paramInt('rights');

	try
	{
		validateAlbum($album);
		galleryCreateAlbum($album);

		$layout->currentAlbum = $album;
		$layout->content = formatInformation("Das Album wurde erstellt", urlAlbumView($album->id));
		$layout->redirectionURL = urlAlbumView($album->id);
	}
	catch(InvalidInputException $e)
	{
		$layout->content = renderAlbumForm($album, false, $e->getMessage());
	}
}
else
{
	$album->title = '';
	$album->rights = 1;
	$layout->content = renderAlbumForm($album, false);
}

echo $layout->render();

?>
