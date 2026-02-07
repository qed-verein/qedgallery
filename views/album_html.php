<?php

class AlbumRenderer
{
	var $album, $page, $images;
	private $owner, $filter;

	function __construct($album, $page, $images)
	{
		$this->album = $album;
		$this->page = $page;
		$this->images = $images;
		$this->filter = filterFromURL();
	}

	private function renderThumbnail($image)
	{
		$html = sprintf("<a href='%s'>\n", htmlText(urlImageDetails($image->id, $this->filter)));
		$html .= sprintf("<img src='%s' alt='%s'>\n",
			htmlText(urlImageThumbnail($image->id)), htmlText($image->title));
		$html .= "</a>\n";
		$html .= sprintf("<p class='title'><small>%s</small></p>\n", htmlText($image->title));
		
		return $html;
	}

	private function renderImageTable()
	{
		$html = "<ul class='imagetable'>";
		foreach($this->images as $image) {
			$html .= "<li>" . $this->renderThumbnail($image) . "</li>";
		}
		$html .= "</ul>";
		return $html;
	}
		
		//$numRows = ceil(count($this->images) / IMAGES_PER_ROW);
		//$html = "<table id='imagetable'>\n";
		//for($i = 0; $i < $numRows; ++$i)
		//{
			//$html .= "<tr>\n";
			//for($j = 0; $j < IMAGES_PER_ROW; ++$j)
				//if(isset($this->images[$i * 4 + $j]))
				//{
					//$html .= "<td>";
					//$html .= $this->renderThumbnail($this->images[$i * 4 + $j]);
					//$html .= "</td>\n";
				//}
			//$html .= "</tr>\n";
		//}
		//$html .= "</table>";
		//return $html;

	private function renderNavigation()
	{
		if($this->page == 0) return "";
		$pageCount = albumPageCount($this->album, $this->filter);


		$htmlFullscreen = sprintf("<p style='text-align: center'><b><a href='%s'>Album im Vollbild anzeigen</a></b></p>\n",
			htmlText(urlImageView($this->images[0]->id)));

		if($this->page == 1)
			$linkPrev = "<img src='style/go-previous-inactive.png' alt='Zum vorherigen Bild'>";
		else
			$linkPrev = sprintf("<a href='%s'><img src='style/go-previous.png' alt='Zum vorherigen Bild'></a>",
				htmlText(urlAlbumView($this->album->id, $this->filter, $this->page - 1)));

		if($this->page == $pageCount)
			$linkNext = "<img src='style/go-next-inactive.png' alt='Zum nächsten Bild'>";
		else
			$linkNext = sprintf("<a href='%s'><img src='style/go-next.png' alt='Zum nächsten Bild'></a>",
				htmlText(urlAlbumView($this->album->id, $this->filter, $this->page + 1)));

		$html = $htmlFullscreen;
		$html .= "<nav style='display: flex; flex-flow: row; justify-content: space-around'>\n";
		$html .= sprintf("<span>%s</span>\n", $linkPrev);
		if($this->page > 0)
			$html .= sprintf("<span>Seite %d von %d</span>", $this->page, $pageCount);
		$html .= sprintf("<span>%s</span>\n", $linkNext);
		$html .= "</nav>\n\n";
		return $html;
	}

	private function renderAlbumInfos()
	{
		global $gUser;

		$html = "<table class='ptable infotable' style='margin: auto'>\n";
		$html .= sprintf("<tr><th>Albumersteller:</th><td>%s</td></tr>\n",
			htmlText($this->owner->username));
		$html .= sprintf("<tr><th>Erstellt am:</th><td>%s</td></tr>\n",
			htmlText(formatDateTime($this->album->creationTime)));
		$html .= sprintf("<tr><th>Aufgenommen:</th><td>%s - %s</td></tr>\n",
			htmlText(strftime("%x", $this->album->originalFrom)),
			htmlText(strftime("%x", $this->album->originalTill)));
		if(userIsAdmin($gUser) || $this->album->ownerId == $gUser->id)
		{
			$html .= sprintf("<tr><th>Rechte:</th><td>%s</td></tr>\n",
				albumPermissionDescriptions()[$this->album->rights]);
		}
		$html .= "</table>\n";

		return $html;
	}

	private function renderFilters()
	{
		$owners = imageOwnerList($this->album);

		$html = "<nav class='image_filter_nav'>\n";
		
		$html .= "<section>\n";
		$html .= "<b>Nach Besitzer:</b>\n";
		$html .= "<ul>\n";
		foreach($owners as $owner)
		{
			$filter = array('byowner' => $owner->id);
			$username = $owner ? $owner->username : "Unbekannter Benutzer";
			$link = sprintf("<a href='%s'>Bilder von %s</a>\n",
				htmlText(urlAlbumView($this->album->id, $filter)), htmlText($username));
			if(isset($this->filter['byowner']) && $owner->id == $this->filter['byowner'])
				$link = "<b>" . $link . "</b>";
			$html .= "<li>" . $link . "</li>\n";
		}
		$html .= "</ul>\n";
		$html .= "</section>\n";


		$days = imageDayList($this->album);
		$html .= "<section>\n";
		$html .= "<b>Nach Datum:</b>";
		$html .= "<ul>\n";
		foreach($days as $day)
		{
			$filter = array('byday' => $day);
			$link = sprintf("<a href='%s'>%s</a>\n",
				htmlText(urlAlbumView($this->album->id, $filter)), htmlText($this->filterText($filter)));
			if(isset($this->filter['byday']) && $day == $this->filter['byday'])
				$link = "<b>" . $link . "</b>";
			$html .= "<li>" . $link . "</li>\n";
		}
		$html .= "</ul>\n";
		$html .= "</section>\n";

		$uploads = imageUploadList($this->album);
		$html .= "<section>\n";
		$html .= "<b>Nach Upload:</b>";
		$html .= "<ul>\n";
		foreach($uploads as $upload)
		{
			$filter = array('byupload' => $upload);
			$link = sprintf("<a href='%s'>%s</a>\n",
				htmlText(urlAlbumView($this->album->id, $filter)), htmlText($this->filterText($filter)));
			if(isset($this->filter['byupload']) && $upload == $this->filter['byupload'])
				$link = "<b>" . $link . "</b>";
			$html .= "<li>" . $link . "</li>\n";
		}
		$html .= "</ul>\n";
		$html .= "</section>\n";

		$categories = imageCategoryList($this->album);
		$html .= "<section>\n";
		$html .= "<b>Nach Kategorie:</b>";
		$html .= "<ul>\n";
		foreach($categories as $category)
		{
			$filter = array('bycategory' => $category);
			$link = sprintf("<a href='%s'>%s</a>\n",
				htmlText(urlAlbumView($this->album->id, $filter)), htmlText($this->filterText($filter)));
			if(isset($this->filter['bycategory']) && $day == $this->filter['bycategory'])
				$link = "<b>" . $link . "</b>";
			$html .= "<li>" . $link . "</li>\n";
		}
		$html .= "</ul>\n";
		$html .= "</section>\n";


		$linkp = sprintf("<a href='%s'>Album seitenweise anzeigen</a>\n",
			htmlText(urlAlbumView($this->album->id)));
		if(empty($this->filter) && $this->page > 0) $linkp = "<b>" . $linkp . "</b>";
		
		$linka = sprintf("<a href='%s'>Gesamtes Album auf einmal zeigen</a>\n",
			htmlText(urlAlbumView($this->album->id, array(), 0)));
		if(empty($this->filter) && $this->page == 0) $linka = "<b>" . $linka . "</b>";
		$html .= "<section style='width: 100%; text-align: center'>" . $linkp . "<br>" . $linka . "</section>\n";

		$html .= "</nav>\n";
		return $html;
	}

	private function initialize()
	{
		if($this->album)
			$this->owner = $this->album->getLink('ownerId');
		if($this->owner === false)
		{
			$this->owner = DB_DataObject::factory('user');
			$this->owner->username = "unbekannt";
		}
	}

	private function filterText($filter)
	{
		if(isset($filter['bycategory'])) return
			empty($filter['bycategory']) ? 'Sonstiges' : $filter['bycategory'];
		if(isset($filter['byday']))
			return empty($filter['byday']) ? 'unbekannt' : sprintf("am %s\n", $filter['byday']);
		if(isset($filter['byupload']))
			return empty($filter['byupload']) ? 'unbekannt' : sprintf("am %s\n", $filter['byupload']);
		if(isset($filter['byowner']))
		{
			$owner = DB_DataObject::factory('user');
			$owner->get($filter['byowner']);
			return sprintf("Bilder von %s", $owner->username);
		}
		return "Alle Bilder";
	}

	function render()
	{
		$this->initialize();
		$html = sprintf("<h2>%s - %s</h2>\n", htmlText($this->album->title), htmlText(
			$this->filterText($this->filter)));
		$html .= sprintf("<p>%s</p>\n", htmlText($this->album->description));

		if(empty($this->images))
		{
			$html .= "<div style='margin: 1cm auto; text-align: center'>";
			$html .= "<b>Dieses Album ist leer</b>";
			$html .= "</div>";
		}
		else
		{
			$htmlNavigation = $this->renderNavigation();
			$html .= "<div style='margin: 1cm auto'>";
			$html .= $htmlNavigation;
			$html .= $this->renderImageTable();
			$html .= $htmlNavigation;
			$html .= "</div>";
		}

		$html .= $this->renderFilters();
		$html .= $this->renderAlbumInfos();
		return $html;
	}
}



function renderAlbumList($albums)
{
	$albumByDate = array();
	foreach($albums as $album)
	{
		$date = strftime("%Y", $album->originalFrom);
		//$date = strftime("%Y-%m", $album->originalFrom);
		if($album->originalFrom == 0) $date = 'none';
		if(!isset($albumByDate[$date])) $albumByDate[$date] = array();
		$albumByDate[$date][] = $album;
	}
	
	$html = "<h2>Liste aller Alben</h2>\n";
	foreach($albumByDate as $date => $albums)
	{
		if($date == 'none') $year = "Weitere Alben";
		else $year = "Jahr " . explode("-", $date)[0];
		//else $month = strftime("%B", mktime(0, 0, 0, explode("-", $date)[1])) . " " . explode("-", $date)[0];
		$html .= sprintf("<h3>%s</h3>", $year);
		$html .= "<ul class='menu' style='list-style-image: url(style/folder.png)'>\n";
		foreach($albums as $album)
		{
			$rights = ($album->rights == 0) ? " <i>(privates Album)</i>" : "";
			$html .= sprintf("<li><a href='%s'>%s</a>%s</li>\n",
				htmlText(urlAlbumView($album->id)), htmlText($album->title), $rights);
		}
		
		$html .= "</ul>\n";
	}

	return $html;
}

?>
