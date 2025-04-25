<?php

define('GALLERY_PATH', "..");
set_include_path(get_include_path() . PATH_SEPARATOR . GALLERY_PATH);
require_once('include/common.php');

$image = DB_DataObject::factory('image');
$image->orderBy('originalTime DESC, id DESC');
$image->find();

while($image->fetch())
{
	try
	{
		if(!file_exists(imagePath($image, 'original')))
			continue;
		if(file_exists(imagePath($image, 'normal')) && file_exists(imagePath($image, 'thumbnail'))
      && file_exists(imagePath($image, 'fullhd')))
			continue;
	
		if($image->mimeType == 'image/jpeg' || $image->mimeType == 'image/png')
		{
			printf("%s\n", imagePath($image, 'original'));
			regenerateThumbnails($image);
		}    
	}
	catch(Exception $e)
	{
		echo 'Fehler: ',  $e->getMessage(), "\n";
    }
}

?>
