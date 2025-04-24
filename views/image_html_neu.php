<?php

class ImageRendererNeu
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
		//~ $this->firstId = imageSequence($this->image, 'firstId', $this->filter);
		//~ $this->lastId  = imageSequence($this->image, 'lastId', $this->filter);
		$this->prevId  = imageSequence($this->image, 'prevId', $this->filter);
		$this->nextId  = imageSequence($this->image, 'nextId', $this->filter);
	}

	function renderVideoBox()
	{
		return sprintf("<video src='%s' preload='none' controls id='mainimage' style='z-index: 1'></video>\n", 
			htmlText(urlImageOriginal($this->image->id)));
	}

	function renderAudioBox()
	{
		return sprintf("<audio src='%s' preload='none' controls id='mainimage'></video>\n", 
			htmlText(urlImageOriginal($this->image->id)));
	}

	function renderNavigation()
	{
/*		if($this->image->id == $this->firstId)
			$linkFirst = "<img src='style/go-first-inactive.png' alt='Zum ersten Bild'>";
		else
			$linkFirst = sprintf("<a href='%s'><img src='style/go-first.png' alt='Zum ersten Bild'></a>",
				htmlText(urlImageView($this->firstId, $this->filter)));

		if($this->image->id == $this->lastId)
			$linkLast = "<img src='style/go-last-inactive.png' alt='Zum letzten Bild'>";
		else
			$linkLast = sprintf("<a href='%s'><img src='style/go-last.png' alt='Zum letzten Bild'></a>",
				htmlText(urlImageView($this->lastId, $this->filter)));*/

    $prevIcon = "<img width=64 src='style/go-previous.png' alt='Zum vorherigen Bild' />";
    $nextIcon = "<img width=64 src='style/go-next.png' alt='Zum nächsten Bild' />";
    $albumIcon = "<img width=64 src='style/folder.png' alt='Zurück zum Album' />";

		if(is_null($this->prevId))
			$linkPrev = "";
		else
			$linkPrev = sprintf("<a href='%s' id='leftarrow' class='side-hover'>%s</a>\n",
				htmlText(urlImageViewNeu($this->prevId, $this->filter)), $prevIcon);

		if(is_null($this->nextId))
			$linkNext = "";
		else
			$linkNext = sprintf("<a href='%s' id='rightarrow' class='side-hover'>%s</a>\n",
				htmlText(urlImageViewNeu($this->nextId, $this->filter)), $nextIcon);
   
    $pageIndex = imageSequence($this->image, 'position', $this->filter) / IMAGES_PER_PAGE + 1;
    
    $linkAlbum = sprintf("<a href='%s' id='backtoalbum' class='footer-hover'>%s</a>\n",
      htmlText(urlAlbumView($this->album->id, $this->filter, $pageIndex)), $albumIcon);

    $html = $linkPrev;
    $html .= $linkNext;
    $html .= $linkAlbum;
    
		return $html;
	}

	function renderImageBox()
	{
    $htmlImage = sprintf("<img src='%s' alt='%s' id='mainimage'>\n",
      htmlText(urlImageOriginal($this->image->id)),
      htmlText($this->image->title));

   $mimeClass = explode('/', $this->image->mimeType);
   $mimeClass = $mimeClass[0];
   if($mimeClass == 'video')
     $htmlImage = $this->renderVideoBox();
   elseif($mimeClass == 'audio')
     $htmlImage = $this->renderAudioBox();

   $html = "<div class='container'>\n";
   $html .= $htmlImage;
   $html .= "</div>\n"; 

		return $html;
	}

	function render()
	{
		$this->initialize();
		$html = $this->renderImageBox() . "\n";
		$html .= $this->renderNavigation() . "\n";
		return $html;
	}
}


?>
