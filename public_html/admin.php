<?php

require_once('path.php');
require_once('include/common.php');

define('L_ADMIN_PAGE', "Adminbereich");

if(!userIsAdmin($gUser))
	throw new AccessDeniedException();

$album = DB_DataObject::factory('album');
$album->rights = 0;
$album->orderBy('creationTime DESC');
$album->find();
$albums = array();
while($album->fetch())
	$albums[] = clone($album);

$image = DB_DataObject::factory('image');
$image->rights = 0;
$image->orderBy('originalTime DESC');
$image->find();
$images = array();
while($image->fetch())
	$images[] = clone($image);

$layout = new StandardLayout;
$layout->title = L_ADMIN_PAGE;
$layout->content = "<h3>Nicht öffentliche Alben</h3>";
$layout->content .= "<ul class='menu' style='list-style-image: url(style/folder.png)'>";
foreach($albums as $album)
	$layout->content .=  sprintf("<li><a href='%s'>%s</a></li>",
		htmlText(urlAlbumView($album->id)), $album->title);
$layout->content .= "</ul>";

$layout->content .= "<h3>Nicht öffentliche Bilder</h3>";
$layout->content .= "<ul class='menu' style='list-style-image: url(style/image.png)'>";
foreach($images as $image)
	$layout->content .=  sprintf("<li><a href='%s'>%s</a></li>",
		htmlText(urlImageDetails($image->id)), $image->title);
$layout->content .= "</ul>";


$layout->content .= "<p><a href='admin_access.php'>Zugriffsprotokoll anzeigen</a></p>";
echo $layout->render();

?>
