<?php

define('GALLERY_PATH', "..");
set_include_path(get_include_path() . PATH_SEPARATOR . GALLERY_PATH);

require_once('include/common.php');

$image = DB_DataObject::factory('image');
$image->orderBy('originalTime DESC, id DESC');
$image->find();

while($image->fetch())
{
	if(!file_exists(imagePath($image, 'original')))
		printf("Fehler: Bild mit Nummer %d im Album %d wurde nicht Dateisystem gefunden!\n",
			$image->id, $image->albumId);
}


$image = DB_DataObject::factory('image');
$image->orderBy('originalTime DESC, id DESC');
$image->whereAdd('NOT EXISTS(SELECT album.id FROM album WHERE album.id = albumId)');
$image->find();

while($image->fetch())
{
	printf("Fehler: Bild mit Nummer %d liegt in einem nicht existierenden Album %d!\n",
		$image->id, $image->albumId);
}


?>
