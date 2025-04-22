<?php

function renderLoginForm($imageId = null, $albumId = null, $errorMessage = null)
{
	$html  = sprintf("<form action='%s' method='post'>\n", htmlText(urlLogin()));
	$html .= "<fieldset style='width: 20em; margin: auto' class='box'>\n";
	$html .= "<legend>Anmeldung</legend>\n";

	$html .= "<table>\n";
	$html .= " <tr>\n";
	$html .= "  <td><label for='input_username'>Benutzername:</label></td>\n";
	$html .= "  <td><input name='username' id='input_username'></td>\n";
	$html .= " </tr>\n";
	$html .= " <tr>\n";
	$html .= "  <td><label for='password'>Passwort:</label></td>\n";
	$html .= "  <td><input type='password' name='password' id='input_password'></td>\n";
	$html .= " </tr>\n";
	$html .= " <tr><td colspan='2'><input type='submit' name='login' value='Einloggen'></td></tr>\n";
	$html .= "</table>\n";

	$html .= formatError($errorMessage);

	if(!is_null($imageId))
		$html .= htmlHidden(array('imageid' => $imageId));
	else if(!is_null($albumId))
		$html .= htmlHidden(array('albumid' => $albumId));

	$html .= "</fieldset>\n";
	$html .= "</form>\n";

	$html .= "<div style='margin: 1cm auto; width: 20cm'><b>Hinweise:</b>\n";
	$html .= "<ul>\n";
	$html .= " <li>Nur Personen mit einem QED-Account können auf die Fotosammlung zugreifen.</li>\n";
	$html .= " <li>Der Benutzername und das Passwort sind die gleichen wie auf der QED-Hauptseite.</li>\n";
	$html .= " <li>Die Fotosammlung dient zum internen Austausch der Bilder von QED-Seminaren. " .
		" Eine Weiterverbreitung der Bilder ist nur im Falle einer ausdrücklichen Zustimmung " .
		" der beteiligten Personen gestattet.</li>";
	$html .= " <li>Falls Probleme auftreten sollten, bitte eine Mitteilung an\n";
	$html .= sprintf(" <a href='mailto:%s'>%s</a> schreiben.</li>\n", htmlText(ADMIN_EMAIL), htmlText(ADMIN_EMAIL));
	$html .= "</ul>\n";
	$html .= "</div>\n";
	return $html;
}

?>
