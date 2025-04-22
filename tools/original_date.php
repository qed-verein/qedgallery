<?php

define('GALLERY_PATH', "..");
set_include_path(get_include_path() . PATH_SEPARATOR . GALLERY_PATH);
require_once('include/common.php');

$image = DB_DataObject::factory('image');
$image->orderBy('originalTime DESC, id DESC');
$image->find();

while($image->fetch())
{
	$nimage = DB_DataObject::factory('image');
	$nimage->id = $image->id;

	if($image->mimeType == 'image/jpeg')
		$exif = exif_read_data(imagePath($image, 'original'));
	if(isset($exif['DateTimeOriginal']))
	{
		list($date, $time) = explode(' ', $exif['DateTimeOriginal']);
		list($year, $month, $day) = explode(':', $date);
		list($hour, $minute, $second) = explode(':', $time);
		$nimage->originalTime = mktime($hour, $minute, $second, $month, $day, $year);
	}
	else
	{
		$nimage->originalTime = null;
		printf("Kein Datum: image=%d, album=%d\n", $image->id, $image->albumId);
	}
	$nimage->update();
}

?>
