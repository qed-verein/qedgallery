<?php

require_once('path.php');
require_once('include/common.php');

define('L_ALBUM_DELETE', "Album löschen");

$albumId = paramInt('albumid');

$album = DB_DataObject::factory('album');
$res = $album->get($albumId);
if($res == 0) throw new AlbumNotFoundException($albumId);

if(!testAlbumPermissions(PERM_EDIT, $albumId))
	throw new AccessDeniedException();

$layout = new StandardLayout;
$layout->title = L_ALBUM_DELETE;

if(isset($_POST['yes']))
{
	galleryDeleteAlbum($album);

	$layout->content = formatInformation("Das Album wurde gelöscht", urlAlbumList());
	$layout->redirectionURL = urlAlbumList();
}
elseif(isset($_POST['no']))
{
	redirect(urlAlbumView($albumId));
}
else
{
	$message = sprintf("Soll das Album <i>%s</i> wirklich gelöscht werden?",
		htmlText($album->title));

	$layout->currentAlbum = $album;
	$layout->content = formatQuestionYesNo($message, urlAlbumDelete($album->id));
}

echo $layout->render();

?>
