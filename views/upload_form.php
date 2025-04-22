<?php

function renderUploadForm($album, $errorMessage = null)
{
	$html  = "<form onload='onLoad();'>\n";
	$html .= "<fieldset class='box'>\n";
	$html .= sprintf("<legend>Bilder zum Album <b>%s</b> hinzufügen</legend>\n", htmlText($album->title));
	$html .= formatError($errorMessage);

	$html .= "<input type='file' id='file' name='file' multiple>\n";
	//$html .= "<input type='hidden' id='sid' name='sid' value='default sid'>\n";
	$html .= sprintf("<input type='hidden' id='albumId' name='albumId' value='%d'>\n",
		htmlText($album->id));

	$html .= "<input type='checkbox' value='false' id='paused' " .
		"onchange='if(this.checked) pauseUpload(); else resumeUpload();' />Pause";
	$html .= "<br>\n";
	$html .= "<textarea id='console' cols='80' rows='10'></textarea>\n";

	$html .= "<p><b>Hinweise:</b>\n";
	$html .= "<ul>\n";
	$formats = array_keys($GLOBALS['mimetypes']);
	sort($formats);
	$html .= sprintf("<li>Folgende Formate sind erlaubt: <i>%s</i></li>\n", 
		htmlspecialchars(join(" ", $formats)));
	$html .= "<li>Es können auch ZIP-Dateien bestehend aus Bildern hochgeladen werden</li>\n";
	$html .= sprintf("<li>Die maximale Dateigröße beträgt: %d Bytes</li>\n", MAX_UPLOAD_SIZE);
	$html .= "<li>Unter Umständen kann der Upload eine längere Zeit dauern.</li>\n";
	$html .= "</ul>\n";

	$html .= "</fieldset>\n";
	$html .= "</form>\n";
	return $html;
}

?>
