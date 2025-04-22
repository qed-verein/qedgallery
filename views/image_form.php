<?php

function renderImageForm($image, $errorMessage = '')
{
	$html  = sprintf("<form action='%s' method='post'>\n",
		htmlText(urlImageEdit($image->id)));
	$html .= "<fieldset class='box'>";
	$html .= sprintf("<legend>%s</legend>", htmlText(L_IMAGE_EDIT));
	$html .= formatError($errorMessage);
	$html .= sprintf("<p>Titel des Bildes: <input type='text' name='title' value='%s' size='30' maxlength='200'></p>",
		htmlText($image->title));
	$html .= sprintf("<p>Kategorie: <input type='text' name='category' value='%s' size='30' maxlength='50'></p>",
		htmlText($image->category));
	$html .= sprintf("<p><input type='checkbox' name='rights' value='rights' %s>Bild freigeben</p>",
		$image->rights ? "checked='checked'" : "");
	$html .= "<p><input type='submit' name='edit' value='Änderungen speichern'></p>";
	$html .= "</fieldset>";
	$html .= "</form>";
	return $html;
}

?>
