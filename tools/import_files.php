<?php

define('GALLERY_PATH', "..");
set_include_path(get_include_path() . PATH_SEPARATOR . GALLERY_PATH);
require_once('include/common.php');

restore_exception_handler();

if($argc != 3)
	die("Verwendung: php import_files.php \$AlbumId \$VerzeichnisMitBildern\n");

$albumId = $argv[1];
$album = DB_DataObject::factory('album');
$res = $album->get($albumId);
if($res == 0) throw new AlbumNotFoundException($albumId);

$gUser = DB_DataObject::factory('user');
$gUser->get($album->ownerId);

$path = $argv[2];
$files = scandir($path);
foreach($files as $file)
{
	if($file == "." || $file == "..") continue;
	printf("Importiere Datei: %s\n", $path . '/' . $file);
	handleImageUpload($album, $path . '/' . $file, $file);
}


?>
