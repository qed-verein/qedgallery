<?php

class AlbumFormular
{
	var $album, $images;
	var $errorMessage;
	var $selectedIds, $inputCategory;
	var $operation;


	function __construct()
	{
		$this->action = "";
		$this->album = "";
		$this->images = array();
		$this->filter = array();
		$this->selectedIds = array();
		$this->errorMessage = "";
	}

	private function renderThumbnail($image)
	{
		//$html = sprintf("<a href='%s'>\n", htmlText(urlImageDetails($image->id, $this->filter)));
		//$html .= sprintf("<img src='%s' alt='%s'>\n",
			//htmlText(urlImageThumbnail($image->id)), htmlText($image->title));
		//$html .= "</a><br>\n";
		//$html .= sprintf("<small>%s</small>\n", htmlText($image->title));
		
		$html = sprintf("<img src='%s' alt='%s'>\n",
			htmlText(urlImageThumbnail($image->id)), htmlText($image->title));
		$html .= "<br>\n";
		$html .= sprintf("<small>%s</small>\n", htmlText($image->title));
		return $html;
	}

	private function renderImageTable()
	{
		$numRows = ceil(count($this->images) / IMAGES_PER_ROW);
		$html = "<ul class='imagetable'>\n";
		for($i = 0; $i < count($this->images); ++$i)
		{
			$image = $this->images[$i];
			$html .= sprintf("<li><label for='image_checkbox_%d'>", $image->id);
			$html .= $this->renderThumbnail($image);
			if($this->action == 'change_category' && !empty($image->category))
				$html .= sprintf("<br><small><b>Kategorie:</b> %s</small>", htmlText($image->category));
			
			
			$sel = isset($this->selectedIds[$image->id]) ? "checked='checked'" : "";
			$html .= sprintf("<br>" .
				"<input type='checkbox' id='image_checkbox_%d' name='selected[%d]' value='1' %s> Markieren",
				$image->id, $image->id, $sel);					

			//$html .= sprintf("<br><input type='checkbox' name='selected[%d]' value='1' %s> Markieren",
				//$image->id, $sel);
				
			$html .= "</label></li>\n";
		}
		$html .= "</ul>";
		return $html;
	}

	private function renderImageSelector()
	{
		if(empty($this->images))
			return "<p style='text-align: center'><b>Keine Bilder zum Bearbeiten vorhanden.</b></p>\n";

		$html = "<p>\n";
		$html .= "<input type='submit' name='select_all' value='Alle Bilder markieren'>\n";
		$html .= "<input type='submit' name='select_none' value='Kein Bild markieren'>\n";
		$html .= "</p>\n";
		$html .= $this->renderImageTable();		
		return $html;
	}

	private function renderSettings()
	{
		$html = "<fieldset class='box'>\n";
		$html .= sprintf("<legend>Albumeinstellungen</legend>\n");
	
		$html .= sprintf("<p>Titel des Albums:<br>" . 
			"<input type='text' name='title' value='%s' size='30' maxlength='200'></p>\n",
			htmlText($this->album->title));
	
		$html .= sprintf("<p>Beschreibung des Albums:<br>" .
			"<textarea name='description' rows='3' cols='60'>%s</textarea></p>\n",
			htmlText($this->album->description));
	
		$html .= sprintf("<p>Aufgenommen:<br>" .
			"von <input type='text' name='originalfrom' value='%s' size='10'> ".
			"bis <input type='text' name='originaltill' value='%s' size='10'></p>\n",
			htmlText(strftime("%x", $this->album->originalFrom)),
			htmlText(strftime("%x", $this->album->originalTill)));
	
		$html .= "<label for='albumform_rights'>Rechteeinstellungen:</label><br>";
		$html .= "<select name='rights' id='albumform_rights'>\n";
	
		foreach(albumPermissionDescriptions() as $k => $v)
			$html .= sprintf("<option value='%d' %s>%s</option>\n", $k,
				$k == $this->album->rights ? "selected='selected'" : "", htmlText($v));
		$html .= "</select>\n";

		$html .= sprintf("<p><input type='submit' name='submit' value='%s'></p>\n", 
			$this->action == 'create_album' ? "Album erstellen" : "Änderungen speichern");
	
		$html .= "</fieldset>\n";
	
		return $html;
	}


	function render()
	{
		$formAction = ($this->action == 'create_album') ?
			urlAlbumCreate() : urlAlbumEdit($this->album->id, $this->action, $this->filter);
		$html = sprintf("<form action='%s' method='post'>\n", htmlText($formAction));
		$html .= sprintf("<h3>%s</h3>\n", $this->actionTitle($this->action));
		$html .= formatError($this->errorMessage);

		if($this->action == 'create_album' || $this->action == 'change_settings')
			$html .= $this->renderSettings();
		elseif($this->action == 'change_category')
		{
			$html .= "<p><label>Name der neuen Kategorie: ";
			$html .= sprintf("<input type='edit' name='category' value='%s' size='30' maxlength='50'>\n",
				htmlText($this->inputCategory));
			$html .= "</label></p>\n";
			$html .= "<input type='submit' name='submit' value='Kategorie ändern'><br>\n";
			$html .= $this->renderImageSelector();
		}
		elseif($this->action == 'rotate_images')
		{
			$html .= "<input type='submit' name='rotate270' value='Um -90° drehen'>\n";
			$html .= "<input type='submit' name='rotate90' value='Um 90° drehen'>\n";
			$html .= "<input type='submit' name='rotate180' value='Um 180° drehen'>\n";
			$html .= $this->renderImageSelector();
		}
		elseif($this->action == 'delete_images')
		{
			$html .= "<input type='submit' name='submit' value='Markierte Bilder löschen'><br>\n";
			$html .= $this->renderImageSelector();
		}
		if($this->action != 'create_album')
			$html .= sprintf("<p style='text-align: center'><a href='%s'>zurück zum Album</a></p>",
				htmlText(urlAlbumView($this->album->id)));
		$html .= "</form>\n";
		
		return $html;
	}

	static function actionTitle($action)
	{
		$actionTitles = array(
			'create_album' => "Album erstellen",
			'change_settings' => "Albumdaten bearbeiten",
			'change_category' => "Bilder kategorisieren",
			'change_titles' => "Bildtitel ändern",
			'rotate_images' => "Bilder drehen",
			'delete_images' => "Bilder löschen",
			'delete_album' => "Album löschen");
		return $actionTitles[$action];
	}
};


?>
