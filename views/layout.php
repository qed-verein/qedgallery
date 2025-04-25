<?php

class StandardLayout
{
	var $currentAlbum, $currentImage;
	var $redirectionURL, $uploadMode;
	var $title, $content;

	private $recentAlbums;

	function __construct()
	{
	}

	private function initRecentAlbums()
	{
		global $gUser;
		$album = DB_DataObject::factory('album');
		handleAlbumPermissions($album, $gUser);
		$album->orderBy('creationTime DESC');
		$album->limit(8);
		$album->find();

		$this->recentAlbums = array();
		while($album->fetch())
			$this->recentAlbums[] = clone($album);
	}

	function renderFunctionsMenu()
	{
		global $gUser;
		
		
		
		$s = "<li><a href='%s'>%s</a></li>\n";
		$output = "<ul class='menu'>\n";

		if(testAlbumPermissions(PERM_UPLOAD))
			$output .= sprintf($s, urlAlbumCreate(), "Album erstellen");

		if(!is_null($this->currentAlbum))
		{
			$image = DB_DataObject::factory('image');
			$image->albumId = $this->currentAlbum->id;
			handleImagePermissions($image, $gUser, PERM_EDIT);
			$menuImageEdit = ($image->count() > 0);
			$menuImageUpload = testAlbumPermissions(PERM_UPLOAD, $this->currentAlbum->id);
			$menuAlbumEdit = testAlbumPermissions(PERM_EDIT, $this->currentAlbum->id);

			if($menuAlbumEdit)
				$output .= sprintf("<li><a href='%s'>Albumdaten bearbeiten</a></li>\n",
					urlAlbumEdit($this->currentAlbum->id, 'change_settings', filterFromURL()));
			if($menuImageUpload)
				$output .= sprintf("<li><a href='%s'>Bilder hochladen</a></li>\n",
					urlImageUpload($this->currentAlbum->id));
			if($menuImageEdit)
				$output .= sprintf("<li><a href='%s'>Bilder kategorisieren</a></li>\n",
					urlAlbumEdit($this->currentAlbum->id, 'change_category', filterFromURL()));
			if($menuImageEdit)
				$output .= sprintf("<li><a href='%s'>Bilder drehen</a></li>\n",
					urlAlbumEdit($this->currentAlbum->id, 'rotate_images', filterFromURL()));
			if($menuImageEdit)
				$output .= sprintf("<li><a href='%s'>Bilder löschen</a></li>\n",
					urlAlbumEdit($this->currentAlbum->id, 'delete_images', filterFromURL()));
			if($menuAlbumEdit)
				$output .= sprintf("<li><a href='%s'>Album löschen</a></li>\n",
					urlAlbumEdit($this->currentAlbum->id, 'delete_album', filterFromURL()));
		}
		$output .= "</ul>\n";

		$output .= "<ul class='menu'>\n";
		if(!is_null($this->currentImage))
		{
			$output .= sprintf($s, urlImageOriginal($this->currentImage->id), "Aktuelles Bild herunterladen");
			if(testImagePermissions(PERM_EDIT, $this->currentImage->id))
				$output .= sprintf($s, urlImageEdit($this->currentImage->id), "Bilddaten bearbeiten");
		}
		$output .= "</ul>\n";

		$output .= "<ul class='menu'>\n";
		if(userIsAdmin($gUser))
			$output .= sprintf($s, urlAdminPage(), "Adminbereich");
		$output .= sprintf($s, urlLogout(), "Ausloggen");
		$output .= "</ul>\n";
		return $output;
	}


	function renderNavigation()
	{
		$html  = "<h2>Neueste Alben</h2>\n";
		$html .= "<ul class='menu'>\n";
		foreach($this->recentAlbums as $album)
			$html .= sprintf("<li style='list-style-image: url(style/folder.png)'><a href='%s'>%s</a></li>\n",
				htmlText(urlAlbumView($album->id)),
				htmlText($album->title));
		$html .= sprintf("<li style='list-style-type: none'><b><a href='%s'>%s</a></b></li>\n",
			htmlText(urlAlbumList()), "Alle Alben zeigen");
		$html .= "</ul>\n";

		$html .= "<h2>Funktionen</h2>\n";
		$html .= $this->renderFunctionsMenu();
		return $html;
	}


	function render()
	{
		global $gUser;
		$this->initRecentAlbums();


		$html = "<!doctype html>\n";
		$html .= "<html lang='de'>\n" . "<head>\n";
		$html .= "<link rel='stylesheet' href='style/gallery.css'>\n";
		$html .= "<meta http-equiv='content-type' content='text/html; charset=utf-8'>\n";
		if(!is_null($this->redirectionURL))
			$html .= sprintf("<meta http-equiv='refresh' content='2; URL=%s'>", htmlText($this->redirectionURL));
		$html .= "<meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=no'>";
		if(!is_null($this->uploadMode))
			$html .= sprintf("<script type='text/javascript' src='sliced_upload.js'></script>\n");
		$html .= "<title>" . htmlText($this->title) . "</title>\n";
		$html .= "</head>\n";
		if(!is_null($this->uploadMode))
			$html .= "<body onload='onLoad();'>\n";
		else $html .= "<body>\n";
		$html .= "<header><h1>QED-Photosammlung</h1></header>\n";


		$html .= "<div class='horizontal'>";
		$html .= "<main>\n";
		$html .= $this->content;
		$html .= "</main>\n";
		
		$html .= "<nav id='main_nav'>\n";
		$html .= $this->renderNavigation();
		$html .= "</nav>\n";
		
		$html .= "</div>";


		$html .= "<footer>\n";
		$html .= sprintf("Sie sind eingeloggt als: <b>%s</b><br>\n", htmlText($gUser->username));
		$html .= sprintf("Kontakt: <a href='mailto:%s'>%s</a>\n", htmlText(ADMIN_EMAIL), htmlText(ADMIN_EMAIL));
		$html .= "</footer>";

		$html .= "</body>\n</html>\n";

		return $html;
	}
}

function renderSimpleLayout($title, $content)
{
	$html = "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01//EN' 'http://www.w3.org/TR/html4/strict.dtd'>";
	$html .= "<html>\n" . "<head>\n";
	$html .= "<link rel='stylesheet' href='style/gallery.css'>\n";
	$html .= "<meta http-equiv='content-type' content='text/html; charset=utf-8'>\n";
	$html .= "<title>" . htmlText($title) . "</title>\n";
	$html .= "</head>\n<body>\n" . $content . "</body>\n</html>\n";
	return $html;
}

function renderImageLayout($title, $content)
{
	$html = "<!DOCTYPE html>";
	$html .= "<html>\n" . "<head>\n";
	$html .= "<link rel='stylesheet' href='style/image.css'>\n";
	$html .= "<meta name='viewport' content='width=device-width, initial-scale=1' />";
	$html .= "<title>" . htmlText($title) . "</title>\n";
	$html .= "</head>\n";
	$html .= "<body>\n";
	$html .= "<script type='text/javascript' src='image_view_neu.js'></script>\n";
	$html .= $content . "</body>\n</html>\n";
	return $html;
}


?>
