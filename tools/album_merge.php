<?php

define('GALLERY_PATH', "..");
set_include_path(get_include_path() . PATH_SEPARATOR . GALLERY_PATH);
require_once('include/common.php');

$albumFrom = intval($argv[1]);
$albumTo = intval($argv[2]);

$image = DB_DataObject::factory('image');
$image->albumId = $albumTo;
$image->whereAdd(sprintf('albumId = %d', $albumFrom));
$image->update(DB_DATAOBJECT_WHEREADD_ONLY);

system(sprintf("mv %s/album_%d/* %s/album_%d", IMAGE_DATA_DIR, $albumFrom, IMAGE_DATA_DIR, $albumTo));
//system(sprintf("rmdir %s/album_%d", IMAGE_DATA_DIR, $albumFrom));
system(sprintf("mv %s/album_%d/* %s/album_%d", IMAGE_CACHE_DIR, $albumFrom, IMAGE_CACHE_DIR, $albumTo));
//system(sprintf("rmdir %s/album_%d", IMAGE_CACHE_DIR, $albumFrom));

?>
