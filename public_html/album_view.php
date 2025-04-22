<?php

require_once('path.php');
require_once('include/common.php');
require_once('views/album_html.php');

define('L_ALBUM_VIEW', "Album anzeigen");

$albumId = paramInt('albumid');
$page = paramInt('page', 1);

$album = DB_DataObject::factory('album');
$res = $album->get($albumId);
if($res == 0) throw new AlbumNotFoundException($albumId);

if(!testAlbumPermissions(PERM_VIEW, $albumId))
{
	if($gUser->id == USER_ANONYMOUS_ID)
		redirect(urlLoginAndRedirectToAlbum($albumId));
	throw new AccessDeniedException();
}

$image = DB_DataObject::factory('image');
$image->albumId = $albumId;

handleImagePermissions($image, $gUser);
handleImageFilters($image, filterFromURL());

$image->orderBy('originalTime ASC, id ASC');
if($page > 0) $image->limit(IMAGES_PER_PAGE * ($page - 1), IMAGES_PER_PAGE);
$image->find();

$images = array();
while($image->fetch())
	$images[] = clone($image);

$albumRenderer = new AlbumRenderer($album, $page, $images);
$layout = new StandardLayout;
$layout->title = L_ALBUM_VIEW;
$layout->content = $albumRenderer->render();
$layout->currentAlbum = $album;
echo $layout->render();

?>
