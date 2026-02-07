<?php

define('IMAGES_PER_PAGE', 48);
define('IMAGES_PER_ROW', 4);



function handleImageFilters($image, $filter)
{
	if(isset($filter['byowner']))
		$image->ownerId = $filter['byowner'];

	if(isset($filter['byday']))
	{
		if(empty($filter['byday'])) $image->whereAdd('originalTime IS NULL');
		else $image->whereAdd(sprintf('DATE(FROM_UNIXTIME(originalTime)) = "%s"',
			$image->escape($filter['byday'])));
	}

	if(isset($filter['byupload']))
	{
		if(empty($filter['byupload'])) $image->whereAdd('uploadTime IS NULL');
		else $image->whereAdd(sprintf('DATE(FROM_UNIXTIME(uploadTime)) = "%s"',
			$image->escape($filter['byupload'])));
	}

	if(isset($filter['bycategory']))
		$image->category = $filter['bycategory'];
}

function imageOwnerList($album)
{
	$image = DB_DataObject::factory('image');
	$image->albumId = $album->id;
	$image->groupBy('ownerId');
	$image->find();
	$owners = array();
	while($image->fetch())
	{
		$owner = $image->getLink('ownerId');
		if($owner === false) continue;
		$owners[] = $owner;
	}
  return $owners;
}



function imageDayList($album)
{
	$image = DB_DataObject::factory('image');
	$image->albumId = $album->id;
	$image->selectAdd('DATE(FROM_UNIXTIME(originalTime)) AS day');
	$image->groupBy('day');
	$image->find();
	$days = array();
	while($image->fetch()) $days[] = is_null($image->day) ? '' : $image->day;
	return $days;
}

function imageUploadList($album)
{
	$image = DB_DataObject::factory('image');
	$image->albumId = $album->id;
	$image->selectAdd('DATE(FROM_UNIXTIME(uploadTime)) AS upload');
	$image->groupBy('upload');
	$image->find();
	$uploads = array();
	while($image->fetch()) $uploads[] = is_null($image->upload) ? '' : $image->upload;
	return $uploads;
}

function imageCategoryList($album)
{
	$image = DB_DataObject::factory('image');
	$image->albumId = $album->id;
	$image->groupBy('category');
	$image->find();
	$categories = array();
	while($image->fetch()) $categories[] = $image->category;
	return $categories;
}


function imageSequence($image, $relation, $filter)
{
	global $gUser;
		
	$other = DB_DataObject::factory('image');
	$other->albumId = $image->albumId;
	if($relation == 'nextId')
		$other->whereAdd(sprintf('(originalTime, id) > ("%s", %d)',
			$other->escape($image->originalTime), $image->id));
	elseif($relation == 'prevId' || $relation == 'position')
		$other->whereAdd(sprintf('(originalTime, id) < ("%s", %d)',
			$other->escape($image->originalTime), $image->id));

	handleImagePermissions($other, $gUser);
	handleImageFilters($other, $filter);

	if($relation == 'firstId' || $relation == 'nextId')
		$other->orderBy('originalTime ASC, id ASC');
	elseif($relation == 'lastId' || $relation == 'prevId')
		$other->orderBy('originalTime DESC, id DESC');

	if($relation == 'position')
		$res = $other->count();
	else
	{
		$other->limit(0, 1);
		$res = $other->find();
	}
	
	if(DB::isError($res)) die($res->getMessage());
	$res = $other->fetch();
	return $res ? $other->id : null;
}

function albumPageCount($album, $filter)
{
	global $gUser;
	
	$image = DB_DataObject::factory('image');
	$image->albumId = $album->id;
	handleImagePermissions($image, $gUser);
	handleImageFilters($image, $filter);
	$imageCount = $image->count();
	$pageCount = ceil($imageCount / IMAGES_PER_PAGE);
	if($pageCount == 0) $pageCount = 1;

	return $pageCount;
}

function validateAlbum($album)
{
	if($album->title == '')
		throw new InvalidInputException("Für das Album muss ein Titel angegeben werden");
}

function validateImage($image)
{
	if($image->title == '')
		throw new InvalidInputException("Für das Bild muss ein Titel angegeben werden");
}

function validateComment($comment)
{
	if($comment->content == '')
		throw new InvalidInputException("Bitte einen Text zum Kommentieren angeben");
}


// Diese Funktion ist für das Hochladen von einzelnen Bildern zuständig
function handleImageUpload($album, $tempName, $origName)
{
	global $gUser;

	$pathinfo = pathinfo($origName);
	$basename = $pathinfo['basename'];
	$extension = isset($pathinfo['extension']) ?
		'.' . strtolower($pathinfo['extension']) : '';

	$mimeType = extToMime($extension);
	if(empty($mimeType))
		throw new InvalidInputException(
			sprintf("Die Datei '%s' besitzt eine ungültige Erweiterung", $origName));

	$image = DB_DataObject::Factory('image');
	$image->albumId = $album->id;
	$image->ownerId = $gUser->id;
	$image->title = $basename;
	$image->mimeType = $mimeType;
	$image->uploadTime = time();
	$image->originalTime = $image->uploadTime;
	$image->viewCounter = 0;
	$image->lastViewed = 0;
	$image->category = "";
	$image->rights = 1;

	if($mimeType == 'image/jpeg')
		$exif = @exif_read_data($tempName);
	if(isset($exif['DateTimeOriginal']))
	{
		list($date, $time) = explode(' ', $exif['DateTimeOriginal']);
		list($year, $month, $day) = explode(':', $date);
		list($hour, $minute, $second) = explode(':', $time);
		$image->originalTime = mktime($hour, $minute, $second, $month, $day, $year);
	}

	if($image->mimeType == 'image/jpeg')
	{
		$command = sprintf("jhead -autorot %s", escapeshellarg($tempName));
		exec($command);
	}

	galleryInsertImage($image, $tempName);
}

// Diese Funktion ist für das Hochladen von ZIP-Dateien zuständig
function handleZipUpload($album, $zipName)
{
	$zip = new ZipArchive();
	$zip->open($zipName);

	for($i = 0; $i < $zip->numFiles; ++$i)
	{
		$entryName = $zip->getNameIndex($i);
		if(substr($entryName, -1) == "/") continue;

		$pathinfo = pathinfo($entryName);
		$extension = isset($pathinfo['extension']) ?
			'.' . strtolower($pathinfo['extension']) : '';

		$mimeType = extToMime($extension);
		if(empty($mimeType)) continue;

		$entryStream = $zip->getStream($entryName);
		if(!$entryStream)
			throw new InvalidInputException("Zip-Datei konnte nicht vollständig hochgeladen werden");

		$tempName = tempnam(TEMPORARY_DIR, "");
		$tempStream = fopen($tempName, "wb");
		if($tempStream === false)
			throw new Exception(sprintf("Temporäre Datei '%s' kann nicht angelegt werden", $tempName));

		while(!feof($entryStream))
		{
			$data = fread($entryStream, 8192);
			fwrite($tempStream, $data);
		}
		fclose($tempStream);

		handleImageUpload($album, $tempName, $entryName);

		unlink($tempName);
	}
}

?>
