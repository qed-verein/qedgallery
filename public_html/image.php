<?php

require_once('path.php');
require_once('include/common.php');

ignore_user_abort(false);
session_write_close();

$imageId = paramInt('imageid');
$type = paramString('type');

$image = DB_DataObject::Factory('image');
$res = $image->get($imageId);
if($res == 0) throw new ImageNotFoundException($imageId);

if(!testImagePermissions(PERM_VIEW, $imageId))
	throw new AccessDeniedException();

$filename = imagePath($image, $type);

function mimeIcon($mimeType)
{
	$mimeClass = explode('/', $mimeType);
	$mimeClass = $mimeClass[0];

	if($mimeType == 'image/jpeg' || $mimeType == 'image/png' || $mimeType == 'image/heic' || $mimeType == 'image/heif'
     || $mimeType == 'image/svg+xml') return false;
	if($mimeType == 'text/html') return "text-html.png";
	//if($image->mimeType == 'application/pdf') return "x-office-document.png";

	$classes = array('image', 'text', 'audio', 'video');
	if(in_array($mimeClass, $classes))
		return $mimeClass . "-x-generic.png";
	return "x-office-document.png";
}

if($type == 'normal' || $type == 'thumbnail')
{
	$mimeIcon = mimeIcon($image->mimeType);
	if($mimeIcon !== false)
	{
		redirect("style/" . $mimeIcon);
		exit();
	}

	if(!file_exists($filename))
		regenerateThumbnails($image);
}

if(!file_exists($filename))
	throw new Exception("Interner Fehler: Bilddatei konnte im Dateisystem nicht gefunden werden" . $filename);

if($type == 'download')
{
	$basename = preg_replace("/[^a-zA-Z0-9_.]/i", "_", trim($image->title));
	$extension = mimeToExt($image->mimeType);
	if(strtolower(substr($basename, -strlen($extension))) == $extension)
		$basename = substr($basename, 0, strlen($basename) - strlen($extension));
	$downloadName = $basename . $extension;
	header(sprintf("Content-Disposition: attachment; filename=\"%s\"", addslashes($downloadName)));
}

function parseRangeRequest($string, $fileSize)
{
	if(strtolower(substr($string, 0, 6)) != 'bytes=')
		return null;
	$ranges = explode(",", substr($string, 6, -1));
	for($i = 0; $i < count($ranges); $i++)
		$ranges[$i] = explode("-", $ranges[$i]);
	for($i = 0; $i < count($ranges); $i++)
		if(count($ranges[$i]) == 1)
			$ranges[$i] = [intval($ranges[$i][0]), $fileSize - 1];
		else if(count($ranges[$i]) == 2 && $ranges[$i][0] == "")
			$ranges[$i] = [$fileSize - intval($ranges[$i][1]), $fileSize - 1];
		else if(count($ranges[$i]) == 2 && $ranges[$i][0] != "")
			$ranges[$i] = [intval($ranges[$i][0]), intval($ranges[$i][1])];
		else
			return null;
	return $ranges;
}


if(isset($_SERVER['HTTP_RANGE']))
{
	$fileSize = filesize($filename);
	$ranges = parseRangeRequest($_SERVER['HTTP_RANGE'], $fileSize);
	if(count($ranges) != 1) {http_response_code(501); die();} //TODO Multipart Response

	header("X-Tamas: " . $ranges[0][0] . " " . $ranges[0][1]);
	header(sprintf("Content-Range: bytes %d-%d/%d",
		$ranges[0][0], $ranges[0][1], $fileSize));
	http_response_code(206);
}

header("Content-Type: " . $image->mimeType);
header("Cache-Control: private, max-age=604800");
header("Expires: " . gmdate('D, d M Y H:i:s \G\M\T', time() + 604800));
header("Pragma: cache");
header("Access-Ranges: bytes");

if(isset($_SERVER['HTTP_RANGE']))
{
	$file = fopen($filename, 'r');
	fseek($file, $ranges[0][0]);
	$bytes = $ranges[0][1] - $ranges[0][0] + 1;
	header("Content-Length: " . $bytes);
	while($bytes > 0)
	{
		$blockSize = min(65536, $bytes);
		echo(fread($file, $blockSize));
		$bytes -= $blockSize;
	}
	fclose($file);
}
else
{
	header("Content-Length: " . filesize($filename));
	readfile($filename);
}


//$file = fopen($filename, 'r');
//while(!feof($file)) fread($file, 1024);
//fclose($file);

?>
