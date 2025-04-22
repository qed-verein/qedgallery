<?php

require_once('path.php');
require_once('include/common.php');
require_once('views/album_html.php');

define('L_ALBUM_LIST', "Alle Alben anzeigen");

if(!testAlbumPermissions(PERM_VIEW))
{
	if($gUser->id == USER_ANONYMOUS_ID)
		redirect(urlLogin());
	throw new AccessDeniedException();
}

$album = DB_DataObject::factory('album');
handleAlbumPermissions($album, $gUser);
$album->orderBy('originalFrom DESC, title ASC');
$album->find();
$albums = array();
while($album->fetch())
	$albums[] = clone($album);

$layout = new StandardLayout;
$layout->title = L_ALBUM_LIST;
$layout->content = renderAlbumList($albums);
echo $layout->render();

?>
