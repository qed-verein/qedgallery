<?php

require_once('path.php');
require_once('include/common.php');
require_once('views/login_form.php');

$errorMessage = null;
$imageId = isset($_REQUEST['imageid']) ? intval($_REQUEST['imageid']) : null;
$albumId = isset($_REQUEST['albumid']) ? intval($_REQUEST['albumid']) : null;

if(isset($_REQUEST['login']))
{
	$username = paramString('username');
	$password = paramString('password');
	$userId = userAuthenticate($username, $password);

	if(!is_null($userId))
	{
		setcookie('userid', $userId, strtotime("+1 month"));
		setcookie('pwhash', sha1($username . $password), strtotime("+1 month"));
		$GLOBALS['userid'] = $userId;
	}
	else
		$errorMessage = "Logindaten sind nicht gültig";
}
elseif(isset($_REQUEST['logout']))
{
	setcookie('userid', '', strtotime("-1 day"));
	setcookie('pwhash', '', strtotime("-1 day"));
	redirect(urlLogin());
}


if(userLoggedIn())
{
	if(!is_null($imageId))
		redirect(urlImageView($imageId));
	elseif(!is_null($albumId))
		redirect(urlAlbumView($albumId));
	else
		redirect(urlAlbumList());
}
else
{
	$content = renderLoginForm($imageId, $albumId, $errorMessage);
	echo renderSimpleLayout('Login', $content);
}

?>
