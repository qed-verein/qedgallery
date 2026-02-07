<?php

define('USER_ANONYMOUS_ID', -1);
define('USER_ANONYMOUS_NAME', "Anoynmer Benutzer");

define('PERM_NONE', 0);
define('PERM_VIEW', 1);
define('PERM_UPLOAD', 2);
define('PERM_EDIT', 4);
define('PERM_ALBUM_ALL', PERM_VIEW | PERM_UPLOAD | PERM_EDIT);
define('PERM_IMAGE_ALL', PERM_VIEW | PERM_EDIT);

define('RANK_VISITOR', 1);
define('RANK_MEMBER', 2);
define('RANK_ADMIN', 3);

function userAuthenticate($username, $password)
{
	$user = DB_DataObject::factory('user');
	$user->username = $username;
	$user->password = sha1($username . $password);
	//$user->whereAdd("rank >= 2");
	$res = $user->find();
	if($res == 0) return null;
	$user->fetch();
	return $user->id;
}

function cookieAuthenticate($userid, $pwhash)
{
	$user = DB_DataObject::factory('user');
	$user->id = $userid;
	$user->pwhash = $pwhash;
	$res = $user->find();
	if($res == 0) return null;
	$user->fetch();
	return $user->id;
}


function userLoggedIn()
{
	return !empty($GLOBALS['userid']);
}


function getCurrentUser()
{
	$userId = isset($GLOBALS['userid']) ? $GLOBALS['userid'] : USER_ANONYMOUS_ID;

	if($userId == USER_ANONYMOUS_ID)
	{
		$user = DB_DataObject::factory('user');
		$user->id = USER_ANONYMOUS_ID;
		$user->username = USER_ANONYMOUS_NAME;
		$user->rank = 0;
		return $user;
	}
	else
	{
		$user = DB_DataObject::factory('user');
		$res = $user->get($userId);
		if($res == 0) throw new Exception("Interner Fehler: Eingeloggter Benutzer existiert nicht");
		return $user;
	}
}

function userIsVisitor($user) {return $user->rank >= RANK_VISITOR;}
function userIsMember($user) {return $user->rank >= RANK_MEMBER;}
function userIsAdmin($user) {return $user->rank >= RANK_ADMIN;}

// Die Rechte für ein Album werden wie folgt bestimmt:
// 1) Es gibt Administratoren, Mitglieder und Besucher.
// 2) Adminstratoren können alle Alben anschauen und bearbeiten.
// 3) Der Besitzer eines Albums verfügt ebenfalls über alle Rechte für dieses Album.
// 4) Mitglieder dürfen ebenfalls ein Album sehen/ergänzen/bearbeiten, sofern dies vom Besitzer erlaubt wurde.
// 5) Besucher sind allerdings auf von ihnen besuchte Veranstaltung eingeschränkt.
//    Dort haben sie die gleichen Rechte wie Mitglieder

function albumPermissionSQL($user, $action) {
	if($user->id == USER_ANONYMOUS_ID)
		return "FALSE";

	$sqlAlbumOwner = sprintf('(album.ownerId = %d)', $user->id);

	$requiredAlbumRights = 0;
	if($action & PERM_VIEW) $requiredAlbumRights = 1;
	elseif($action & PERM_UPLOAD) $requiredAlbumRights = 2;
	elseif($action & PERM_EDIT) $requiredAlbumRights = 3;
	$sqlAlbumRights = sprintf('(album.rights >= %d)', $requiredAlbumRights);

	$sqlUserAlbumRights = sprintf('EXISTS(SELECT * FROM user_album_permission AS uap ' .
		'WHERE uap.userId = %d AND uap.albumId = album.id AND uap.permission >= %d)',
		$user->id, $requiredAlbumRights);

	if($user->rank == RANK_ADMIN)
		return "TRUE";
	elseif($user->rank == RANK_MEMBER)
		return $sqlAlbumOwner . " OR " . $sqlAlbumRights;
	elseif($user->rank == RANK_VISITOR)
		return $sqlAlbumOwner . " OR " . "(" . $sqlAlbumRights . " AND " . $sqlUserAlbumRights . ")";
	else
		return "FALSE";
}

function handleAlbumPermissions($album, $user, $action = PERM_VIEW)
{
	$album->whereAdd(albumPermissionSQL($user, $action));
}

// Die Rechte für ein Bild werden wie folgt bestimmt:
// 1) Alle Rechte für ein Bild werden von dem jeweiligen Album geerbt.
// 2) Der Besitzer eines Bildes verfügt über alle Rechte für dieses Bild, auch wenn ihm das Album nicht gehört.
// 3) Bilder eines Albums können gesperrt werden. In diesem Fall ist das Bild nur noch für
//       Leute mit Bearbeitungsrechten sichtbar.

function imagePermissionSQL($user, $action) {
	if($user->id == USER_ANONYMOUS_ID)
		return "FALSE";
	if($user->rank == RANK_ADMIN)
		return "TRUE";

	if($user->rank == RANK_MEMBER || $user->rank == RANK_VISITOR)
	{
		$sqlAlbumCondition = sprintf("EXISTS(SELECT * FROM album WHERE album.id = image.albumId AND (%s))",
			albumPermissionSQL($user, $action));
		$sqlImageOwner = sprintf('(image.ownerId = %d)', $user->id);
		$sqlImagePublic = sprintf('(image.rights = 1)', $user->id);

		if($action & PERM_EDIT)
			return $sqlImageOwner . " OR " . $sqlAlbumCondition;
		elseif($action & PERM_VIEW)
			return $sqlImageOwner . " OR " . "(" . $sqlAlbumCondition . " AND ".  $sqlImagePublic . ")";
		else return "TRUE";
	}
	return "FALSE";
}

function handleImagePermissions($image, $user, $action = PERM_VIEW)
{
	$image->whereAdd(imagePermissionSQL($user, $action));
}

function testAlbumPermissions($action, $albumId = null, $user = null)
{
	global $gUser;
	if(is_null($user)) $user = $gUser;

	if(is_null($albumId))
	{
		if($user->rank >= RANK_MEMBER) return $action & (PERM_VIEW | PERM_UPLOAD);
		else if($user->rank == RANK_VISITOR) return $action & PERM_VIEW;
		return false;
	}
	
	$album = DB_DataObject::factory('album');
	$album->id = $albumId;
	handleAlbumPermissions($album, $user, $action);
	return $album->count() == 1;
}

function testImagePermissions($action, $imageId, $user = null)
{
	global $gUser;
	if(is_null($user)) $user = $gUser;

	$image = DB_DataObject::factory('image');
	$image->id = $imageId;
	handleImagePermissions($image, $user, $action);
	return $image->count() == 1;
}

function albumPermissionDescriptions()
{
	return array(
		"Dieses Album ist privat.",
		"Andere Mitglieder dürfen dieses Album anschauen.",
		"Andere Mitglieder dürfen weitere Bilder hochladen.",
		"Andere Mitglieder dürfen alle Bilder bearbeiten.");
}

?>
