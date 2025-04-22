<?php

define('L_ACCESS_DENIED', "Zugriff verweigert");
define('L_ALBUM_NOT_FOUND', "Album wurde nicht gefunden");
define('L_IMAGE_NOT_FOUND', "Bild wurde nicht gefunden");

function globalExceptionHandler($exception)
{
	global $gUser;
	if(get_class($exception) == 'AccessDeniedException')
	{
		//if($gUser->id == USER_GUEST_ID)
			//redirect(urlLogin());
		$htmlMessage = "Zugriff verweigert";
	}
	else
	{
		$adminLink = sprintf("<a href='mailto:%s'>%s</a>", htmlText(ADMIN_EMAIL), htmlText(ADMIN_EMAIL));
		$htmlMessage = sprintf("<b>%s</b>: %s<br>\n",
			htmlText(get_class($exception)),
			htmlText($exception->getMessage()));
		$htmlMessage .= sprintf("Bitte das Webmasterteam unter %s kontaktieren", $adminLink);
	}

	$content = sprintf("<div class='error'><p>%s</p></div>", $htmlMessage);
	exit(renderSimpleLayout('Fehler', $content));
}

// Wird ausgelöst, falls der Benutzer eine ungültige Formulareingabe macht
class InvalidInputException extends Exception
{
	function InvalidInputException($message)
	{
		parent::__construct($message);
	}
}

// Wird ausgelöst, falls der Benutzt nicht die nötigen Rechte hat
class AccessDeniedException extends Exception
{
	function AccessDeniedException()
	{
		parent::__construct(L_ACCESS_DENIED);
	}
}

// Wird ausgelöst, falls ein Album nicht gefunden wurde
class AlbumNotFoundException extends Exception
{
	function AlbumNotFoundException($albumId)
	{
		parent::__construct(L_ALBUM_NOT_FOUND);
	}
}

// Wird ausgelöst, falls ein Bild nicht gefunden wurde
class ImageNotFoundException extends Exception
{
	function ImageNotFoundException($imageId)
	{
		parent::__construct(L_IMAGE_NOT_FOUND);
	}
}

?>
