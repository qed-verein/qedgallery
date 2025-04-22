<?php

$GLOBALS['mimetypes'] = array(
	'.jpg'  => 'image/jpeg',
	'.jpeg' => 'image/jpeg',
	'.png'  => 'image/png',
	'.heic'  => 'image/heic',
	'.heif'  => 'image/heif',
	'.svg'  => 'image/svg+xml',
	'.avi'  => 'video/x-msvideo',
	'.mp4'  => 'video/mp4',
	'.webm' => 'video/webm',
	'.flac' => 'audio/flac',
	'.ogg'  => 'audio/ogg',
	'.opus' => 'audio/ogg; codec=opus',
	'.mp3'  => 'audio/mp3',
	'.pdf'  => 'application/pdf',
	'.zip'  => 'application/zip',
	'.txt'  => 'text/plain',
	'.html' => 'text/html');

function extToMime($extension)
{
	$extension = strtolower($extension);
	return isset($GLOBALS['mimetypes'][$extension]) ? $GLOBALS['mimetypes'][$extension] : 'application/octet-stream';
}

function mimeToExt($mimeType)
{
	$extension = array_search($mimeType, $GLOBALS['mimetypes']);
	if(is_null($extension)) return '';
	else return $extension;
}

function imagePath($image, $type)
{
	if($type == 'original' || $type == 'download')
		$filename = IMAGE_DATA_DIR."/album_%d/image_%d";
	elseif($type == 'thumbnail')
		$filename = IMAGE_CACHE_DIR."/album_%d/image_%d_thumbnail";
	elseif($type == 'normal')
		$filename = IMAGE_CACHE_DIR."/album_%d/image_%d_normal";
	else throw new InvalidArgumentException();

	return sprintf($filename, $image->albumId, $image->id);
}

function albumPath($album, $type)
{
	if($type == 'data')
		$dirname = IMAGE_DATA_DIR."/album_%d";
	elseif($type == 'cache')
		$dirname = IMAGE_CACHE_DIR."/album_%d";
	else
		throw new InvalidArgumentException();

	return sprintf($dirname, $album->id);
}


function galleryCreateAlbum($album)
{
	$db =& $album->getDatabaseConnection();
	$dbLock = new DB_Lock($db, "album WRITE");

	$album->insert();
	@mkdir(albumPath($album, 'data'), 0755, true);
	@mkdir(albumPath($album, 'cache'), 0755, true);
}

function galleryInsertImage($image, $filename)
{
	$db = $image->getDatabaseConnection();
	$dbLock = new DB_Lock($db, "album READ LOCAL, image WRITE");

	// Prüfe, ob das Album existiert
	$album = $image->getLink('albumId');
	if($album == false)
		throw new Exception(L_ALBUM_NOT_FOUND);

	$res = $image->insert();
	if($res == false)
		throw new Exception("Interner Fehler: Bild konnte nicht in die Datenbank eingefügt werden");

	$originalPath = imagePath($image, 'original');

	$pathinfo = pathinfo($originalPath);
	@mkdir($pathinfo['dirname'], 0755, true);

	$res = copy($filename, $originalPath);
	if($res == false)
		throw new Exception("Interner Fehler: Hochgeladene Datei kann nicht gespeichert werden");

/*
	regenerateThumbnails($image);
*/
}


function galleryDeleteImage($image)
{
	$db = $image->getDatabaseConnection();
	$dbLock = new DB_Lock($db, "image WRITE");

	unlink(imagePath($image, 'original'));
	unlink(imagePath($image, 'normal'));
	unlink(imagePath($image, 'thumbnail'));
	$image->delete();
}

function galleryDeleteAlbum($album)
{
	$db = $album->getDatabaseConnection();
	$dbLock = new DB_Lock($db, "album WRITE, image WRITE");

	// Lösche alle Bilder aus dem Album
	$image = DB_DataObject::factory('image');
	$image->albumId = $album->id;
	$image->find();
	while($image->fetch())
	{
		@unlink(imagePath($image, 'original'));
		@unlink(imagePath($image, 'normal'));
		@unlink(imagePath($image, 'thumbnail'));
	}

	$image = DB_DataObject::factory('image');
	$image->whereAdd(sprintf("albumId = %d", $album->id));
	$image->delete(DB_DATAOBJECT_WHEREADD_ONLY);

	// Lösche das Album selber
	rmdir(albumPath($album, 'data'));
	rmdir(albumPath($album, 'cache'));
	$album->delete();
}

function regenerateThumbnails($image)
{
	$db =& $image->getDatabaseConnection();
	$dbLock = new DB_Lock($db, "image WRITE");

	$originalPath = imagePath($image, 'original');
	$thumbnailPath = imagePath($image, 'thumbnail');
	$normalPath = imagePath($image, 'normal');

	$pathinfo = pathinfo($normalPath);
	@mkdir($pathinfo['dirname'], 0755, true);

	$imageFile = new Imagick($originalPath);
	$imageFile->thumbnailImage(640, 480, true);
	$imageFile->setFormat('jpg');
	$imageFile->writeImage($normalPath);
	$imageFile->destroy();

	$imageFile = new Imagick($originalPath);
	$imageFile->thumbnailImage(160, 120, true);
	$imageFile->setFormat('jpg');
	$imageFile->writeImage($thumbnailPath);
	$imageFile->destroy();

	#$cmd = "convert -resize 640x480 %s %s";
	#$cmd = sprintf($cmd, escapeshellarg($originalPath), escapeshellarg($normalPath));
	#system($cmd);
}

function rotateImage($image, $rotation)
{
	$db =& $image->getDatabaseConnection();
	$dbLock = new DB_Lock($db, "image WRITE");

	$originalPath = imagePath($image, 'original');

	if($image->mimeType == 'image/jpeg')
	{
		exec(sprintf("jpegtran -copy all -rotate %d -outfile %s %s",
			$rotation, escapeshellarg($originalPath), escapeshellarg($originalPath)));
		exec(sprintf("jhead -rgt %s", escapeshellarg($originalPath)));
	}
	else
	{
		$imageFile = new Imagick($originalPath);
		$imageFile->rotateImage(new ImagickPixel('none'), $rotation);
		$imageFile->writeImage($originalPath);
	}

	regenerateThumbnails($image);
}

?>
