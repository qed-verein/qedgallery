<?php

require_once('path.php');
require_once('include/common.php');
require_once('views/album_form.php');

function renderAlbumEditor($album, $images, $errorMessage = '')
{
	$filter = filterFromURL();
	$selected = isset($_POST['selected']) ? $_POST['selected'] : array();
	$category = paramString('category', '');
	//$rotate = paramInt('rotate', 90);
	
	$numRows = ceil(count($images) / IMAGES_PER_ROW);
	$html = sprintf("<form action='%s' method='post'>\n", 'album_edit2.php?albumid=' . $album->id);
	$html .= formatError($errorMessage);
	$html .= htmlHidden(array('albumid' => $album->id));
	$html .= "<table id='imagetable'>\n";
	for($i = 0; $i < $numRows; ++$i)
	{
		$html .= "<tr>\n";
		for($j = 0; $j < IMAGES_PER_ROW; ++$j)
			if(isset($images[$i * 4 + $j]))
			{
				$html .= "<td>";
				$image = $images[$i * 4 + $j];
				

				$html .= sprintf("<a href='%s'>\n", htmlText(urlImageDetails($image->id, $filter)));
				$html .= sprintf("<img src='%s' alt='%s'>\n",
					htmlText(urlImageThumbnail($image->id)), htmlText($image->title));
				$html .= "</a><br>\n";
				$html .= sprintf("<small>%s</small>\n", htmlText($image->title));

				$sel = isset($selected[$image->id]) ? "selected='selected'" : "";
				$html .= sprintf("<br><input type='checkbox' name='selected[%d]' value='1'> Markieren",
					$image->id, $sel);
				$html .= "</td>";
			}
		$html .= "</tr>\n";
	}
	$html .= "</table>\n";

	$html .= "<h3>Operationen für die markierten Bilder</h3>";
	$html .= "<h4>Neue Kategorie:</h4>";
	$html .= sprintf("<input type='edit' name='category' value='%s' size='30' maxlength='50'>\n",
		htmlText($category));
	$html .= "<input type='submit' name='submit_category' value='Kategorie festlegen'><br>\n";
	
	$html .= "</form>\n";

	
	return $html;
}

define('L_ALBUM_EDIT', "Album bearbeiten");

$albumId = paramInt('albumid');
$selected = isset($_POST['selected[]']) ? $_POST['selected[]'] : array();

if(!testAlbumPermissions(PERM_EDIT, $albumId))
	throw new AccessDeniedException();

$album = DB_DataObject::factory('album');
$res = $album->get($albumId);
if($res == 0) throw new AlbumNotFoundException($albumId);

$image = DB_DataObject::factory('image');
$image->albumId = $album->id;
handleImagePermissions($image, $gUser, PERM_EDIT);
handleImageFilters($image, filterFromURL());
$image->orderBy('originalTime ASC, id ASC');
$image->find();
$images = array();
while($image->fetch())
	$images[] = clone($image);


$layout = new StandardLayout;
$layout->title = L_ALBUM_EDIT;
$layout->currentAlbum = $album;

$submit = isset($_POST['submit_category']);
$submit |= isset($_POST['submit_rotate']);

if($submit)
{
	$selected = isset($_POST['selected']) ? $_POST['selected'] : array();

	try
	{
		$imageIds = array();
		foreach($selected as $id => $value)
			if($value == '1') $imageIds[] = $id;

		foreach($imageIds as $imageId)
			if(!testImagePermissions(PERM_EDIT, $imageId))
				throw new AccessDeniedException();
				
		foreach($imageIds as $imageId)
		{
			if(isset($_POST['submit_category']))
			{
				$image = DB_DataObject::factory('image');
				$image->get($imageId);
				$image->category = paramString('category');
				$image->update();
			}
		}
		
		$layout->content = formatInformation("Die Änderungen wurden gespeichert", urlAlbumView($album->id));
		$layout->redirectionURL = urlAlbumView($album->id);
	}
	catch(InvalidInputException $e)
	{
		$layout->content = renderAlbumEditor($album, $images, $e->getMessage());
	}
}
else
{
	$layout->content = renderAlbumEditor($album, $images);
}


echo $layout->render();

?>
