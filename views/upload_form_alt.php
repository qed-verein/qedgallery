<?php

function renderUploadForm($album, $errorMessage = null)
{
	$html  = sprintf("<form action='%s' method='post' enctype='multipart/form-data'>\n",
		htmlText(urlImageUpload($album->id)));
	$html .= "<fieldset class='box'>\n";
	$html .= sprintf("<legend>Bilder zum Album <b>%s</b> hinzufügen</legend>\n", htmlText($album->title));
	$html .= formatError($errorMessage);

	$html .= "<p><input type='file' name='image' size='40'></p>\n";
	$html .= "<p><input type='submit' name='upload' value='Hochladen'></p>\n";

	$html .= "<p><b>Hinweise:</b>\n";
	$html .= "<ul>\n";
	$html .= "<li>Folgende Formate sind erlaubt: <i>JPG, PNG, AVI</i></li>\n";
	$html .= "<li>Es können auch ZIP-Dateien bestehend aus Bildern hochgeladen werden</li>\n";
	$html .= sprintf("<li>Die maximale Dateigröße beträgt: %s Bytes</li>\n", ini_get('upload_max_filesize'));
	$html .= "<li>Unter Umständen kann der Upload eine längere Zeit dauern.</li>\n";
	$html .= "</ul>\n";

	$html .= "</fieldset>\n";
	$html .= "</form>\n";
	return $html;
}

?>
