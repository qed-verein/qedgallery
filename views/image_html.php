<?php

function gps($coordinate, $hemisphere) {
  if (is_string($coordinate)) {
    $coordinate = array_map("trim", explode(",", $coordinate));
  }
  for ($i = 0; $i < 3; $i++) {
    $part = explode('/', $coordinate[$i]);
    if (count($part) == 1) {
      $coordinate[$i] = $part[0];
    } else if (count($part) == 2) {
      $coordinate[$i] = floatval($part[0])/(floatval($part[1])+1.0e-9);
    } else {
      $coordinate[$i] = 0;
    }
  }
  list($degrees, $minutes, $seconds) = $coordinate;
  $sign = ($hemisphere == 'W' || $hemisphere == 'S') ? -1 : 1;
  return $sign * ($degrees + $minutes/60 + $seconds/3600);
}

class ImageRenderer
{
	var $image, $comments;
	var $newComment, $newCommentErrorMessage;
	private $album, $owner;
	private $firstId, $lastId, $prevId, $nextId;
	private $filter;

	function __construct($image)
	{
		$this->image = $image;
	}

	private function initialize()
	{
		$this->album = $this->image->getLink('albumId');
		$this->owner = $this->image->getLink('ownerId');
		if($this->owner === false)
		{
			$this->owner = DB_DataObject::factory('user');
			$this->owner->username = "unbekannt";
		}

		$this->filter = filterFromURL();
		$this->firstId = imageSequence($this->image, 'firstId', $this->filter);
		$this->lastId  = imageSequence($this->image, 'lastId', $this->filter);
		$this->prevId  = imageSequence($this->image, 'prevId', $this->filter);
		$this->nextId  = imageSequence($this->image, 'nextId', $this->filter);
	}

	private function renderExifInfo($exif)
	{
		$html = "";
		if(isset($exif['Orientation']))
			$html .= sprintf("<tr><th>Orientierung:</th><td>%s</td></tr>\n", htmlText($exif['Orientation']));
		if(isset($exif['Make']))
			$html .= sprintf("<tr><th>Kamerahersteller:</th><td>%s</td></tr>\n", htmlText($exif['Make']));
		if(isset($exif['Model']))
			$html .= sprintf("<tr><th>Kameramodel:</th><td>%s</td></tr>\n", htmlText($exif['Model']));
		if(isset($exif['FocalLength']))
			$html .= sprintf("<tr><th>Brennweite:</th><td>%s</td></tr>\n", htmlText($exif['FocalLength']));
		if(isset($exif['FNumber']))
			$html .= sprintf("<tr><th>Blendenzahl:</th><td>%s</td></tr>\n", htmlText($exif['FNumber']));
		if(isset($exif['ExposureTime']))
			$html .= sprintf("<tr><th>Belichtungszeit:</th><td>%s</td></tr>\n", htmlText($exif['ExposureTime']));
		if(isset($exif['ISOSpeedRatings']))
			$html .= sprintf("<tr><th>ISO-Wert:</th><td>%s</td></tr>\n", htmlText(json_encode($exif['ISOSpeedRatings'])));
		if(isset($exif['GPSLongitude']))
			$html .= sprintf("<tr><th>Koordiante:</th><td>%.6F°, %.6F°</td></tr>\n",
				gps($exif['GPSLatitude'], $exif['GPSLatitudeRef']), gps($exif['GPSLongitude'], $exif['GPSLongitudeRef']));
		if(isset($exif['Flash']))
			$html .= sprintf("<tr><th>Blitz benutzt:</th><td>%s</td></tr>\n", htmlText($exif['Flash']));

			
		return $html;
		
	}

	private function renderImageInfos()
	{
		global $gUser;

		$html = "<table class='ptable infotable' style='margin: auto'>\n";
		$html .= sprintf("<tr><th>Album:</th><td>%s</td></tr>\n",
			htmlText($this->album->title));
		$html .= sprintf("<tr><th>Besitzer:</th><td>%s</td></tr>\n",
			htmlText($this->owner->username));
		$html .= sprintf("<tr><th>Dateiformat:</th><td>%s</td></tr>\n",
			htmlText($this->image->mimeType));

		$html .= sprintf("<tr><th>Aufgenommen am:</th><td>%s</td></tr>\n",
			htmlText(formatDateTime($this->image->originalTime)));
		$html .= sprintf("<tr><th>Hochgeladen am:</th><td>%s</td></tr>\n",
			htmlText(formatDateTime($this->image->uploadTime)));
		$html .= sprintf("<tr><th>Anzahl der Anrufe:</th><td>%s</td></tr>\n",
			htmlText($this->image->viewCounter));
		//$html .= sprintf("<tr><th>Letztes Mal aufgerufen:</th><td>%s</td></tr>\n",
			//htmlText(formatDateTime($this->image->lastViewed)));


		// TODO: Rechtestatus bereits anzeigen, falls Bearbeitungsrechte für das Bild vorliegen
		if(testImagePermissions(PERM_EDIT, $this->image->id, $gUser))
		{
			$html .= sprintf("<tr><th>Freigegeben:</th><td>%s</td></tr>\n",
				$this->image->rights ? "Für alle Mitglieder" : "Nicht Freigegeben");
		}
		$html .= "</table>\n";

		$html .= "<details>\n";
		$html .= "<summary  style='text-align: center'>Weitere Informationen</summary>\n";

		if($this->image->mimeType == 'image/jpeg')
		{
			$html .= "<table class='ptable infotable' style='margin: 5mm auto'>\n";
			$exif = @exif_read_data(imagePath($this->image, 'original'));
			$html .= $this->renderExifInfo($exif);
			$html .= "</table>\n";
		}
		$html .= "</details>\n";

		return $html;
	}

	function renderNavigationHeader()
	{
		if($this->image->id == $this->firstId)
			$linkFirst = "<img src='style/go-first-inactive.png' alt='Zum ersten Bild'>";
		else
			$linkFirst = sprintf("<a href='%s'><img src='style/go-first.png' alt='Zum ersten Bild'></a>",
				htmlText(urlImageView($this->firstId, $this->filter)));

		if($this->image->id == $this->lastId)
			$linkLast = "<img src='style/go-last-inactive.png' alt='Zum letzten Bild'>";
		else
			$linkLast = sprintf("<a href='%s'><img src='style/go-last.png' alt='Zum letzten Bild'></a>",
				htmlText(urlImageView($this->lastId, $this->filter)));


		if(is_null($this->prevId))
			$linkPrev = "<img src='style/go-previous-inactive.png' alt='Zum vorherigen Bild'>";
		else
			$linkPrev = sprintf("<a href='%s'><img src='style/go-previous.png' alt='Zum vorherigen Bild'></a>",
				htmlText(urlImageView($this->prevId, $this->filter)));

		if(is_null($this->nextId))
			$linkNext = "<img src='style/go-next-inactive.png' alt='Zum nächsten Bild'>";
		else
			$linkNext = sprintf("<a href='%s'><img src='style/go-next.png' alt='Zum nächsten Bild'></a>",
				htmlText(urlImageView($this->nextId, $this->filter)));


		$linkOriginal = sprintf(
			"<a href='%s'><img src='style/view-fullscreen.png' alt='In Originalgröße anzeigen'></a>",
				htmlText(urlImageViewNeu($this->image->id, $this->filter)));

		$linkDownload= sprintf(
			"<a href='%s'><img src='style/document-save.png' alt='Herunterladen'></a>",
				htmlText(urlImageDownload($this->image->id)));

		$html  = "<div style='text-align: center'>\n";
		$html .= sprintf("<span style='margin-right: 5mm'>%s</span>\n", $linkFirst);
		$html .= sprintf("<span style='margin-right: 1cm'>%s</span>\n", $linkPrev);
		$html .= $linkOriginal . "\n";
		$html .= $linkDownload . "\n";
		$html .= sprintf("<span style='margin-left: 1cm'>%s</span>\n", $linkNext);
		$html .= sprintf("<span style='margin-left: 5mm'>%s</span>\n", $linkLast);
		$html .= sprintf("</div>\n");
		return $html;
	}

	function renderNavigationFooter()
	{
		$pageIndex = imageSequence($this->image, 'position', $this->filter) / IMAGES_PER_PAGE + 1;
		$linkAlbum = sprintf("<a href='%s'>zurück zum Album</a>",
			htmlText(urlAlbumView($this->album->id, $this->filter, $pageIndex)));

		$html  = "<div style='text-align: center; margin: 5mm'>\n";
		$html .= $linkAlbum;
		$html .= "</div>\n";
		return $html;
	}
	
	function renderVideoBox()
	{
		$htmlVideo = sprintf("<video src='%s' preload='none' controls style='max-width: 640px; max-height: 480px'></video>\n", 
			htmlText(urlImageOriginal($this->image->id)));
		return $htmlVideo;
	}

	function renderAudioBox()
	{
		$htmlVideo = sprintf("<audio src='%s' preload='none' controls style='max-width: 640px; max-height: 480px'></video>\n", 
			htmlText(urlImageOriginal($this->image->id)));
		return $htmlVideo;
	}

	function renderImageBox()
	{
		$htmlImage = sprintf("<img src='%s' alt='Bild' id='mainimage' %s'>",
			htmlText(urlImageNormal($this->image->id)),
			htmlText($this->image->title));

		if(!is_null($this->nextId))
		{
			$htmlImage = sprintf("<a href='%s'>%s</a>",
				htmlText(urlImageView($this->nextId, $this->filter)), $htmlImage);
		}
		

		$mimeClass = explode('/', $this->image->mimeType);
		$mimeClass = $mimeClass[0];
		if($mimeClass == 'video')
			$htmlImage = $this->renderVideoBox();
		elseif($mimeClass == 'audio')
			$htmlImage = $this->renderAudioBox();
			
		
		$html = "<div style='text-align: center; margin: 5mm'>\n";
		$html .= sprintf("<div>%s</div>\n", $htmlImage);
		$html .= sprintf("<div><b>%s</b></div>\n", htmlText($this->image->title));
		$html .= "</div>\n";
		return $html;
	}

	function renderComments()
	{
		$html = "<hr style='margin: 1cm auto'>";
		$html .= "<h3>Kommentare</h3>\n";

		if(empty($this->comments))
		{
			$html .= "<div style='margin: 1cm auto; text-align: center'>";
			$html .= "<b>Keine Kommentare</b>";
			$html .= "</div>";
		}

		foreach($this->comments as $comment)
		{
			$html .= "<div class='comment'>\n";
			$html .= sprintf("<b>%s</b> <i>(um %s)</i> <p>%s</p>",
				htmlText($comment->username),
				htmlText(formatDateTime($comment->creationTime)),
				nl2br(htmlText($comment->content)));

			$html .= "</div>\n";
		}

		return $html;
	}

	function renderCommentForm()
	{
		$html = sprintf("<form action='%s' method='post'>\n", htmlText(urlImageView($this->image->id, $this->filter)));
		$html .= "<fieldset class='box' style='margin: auto; text-align: center'>\n";
		$html .= "<legend>Neuen Kommentar hinzufügen</legend>\n";

		$html .= formatError($this->newCommentErrorMessage);

		$html .= sprintf("<textarea name='content' cols='10' rows='10' style='width: 15em'>%s</textarea>\n",
			nl2br(htmlText($this->newComment->content)));
		$html .= "<p><input type='submit' name='addcomment' value='Absenden'></p>\n";

		$html .= "</fieldset>\n";
		$html .= "</form>\n";

		return $html;
	}

	function render()
	{
		$this->initialize();

		$html = $this->renderNavigationHeader() . "\n";
		$html .= $this->renderImageBox() . "\n";
		$html .= $this->renderNavigationFooter() . "\n";
		$html .= $this->renderImageInfos() . "\n";
		if(!is_null($this->comments))
			$html .= $this->renderComments() . "\n";
		if(!is_null($this->newComment))
			$html .= $this->renderCommentForm() . "\n";
		return $html;
	}
}


?>
