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
// 5) Besucher sehen ebenfalls die Alben, welche die Mitglieder sehen können,
//      verfügen aber im Gegensatz zu Mitgliedern nur über Leserechte.

function handleAlbumPermissions($album, $user, $action = PERM_VIEW)
{
	if($user->id == USER_ANONYMOUS_ID) {
		$album->whereAdd('FALSE'); return;}
	if(($action & (PERM_UPLOAD | PERM_EDIT)) && $user->rank < RANK_MEMBER) {
		$album->whereAdd('FALSE'); return;}

	if(($action & PERM_VIEW) && $user->rank <= RANK_MEMBER)
		$album->whereAdd(sprintf('rights >= 1 OR ownerId = %d', $user->id));
	if(($action & PERM_UPLOAD) && $user->rank <= RANK_MEMBER)
		$album->whereAdd(sprintf('rights >= 2 OR ownerId = %d', $user->id));
	if(($action & PERM_EDIT) && $user->rank <= RANK_MEMBER)
		$album->whereAdd(sprintf('rights >= 3 OR ownerId = %d', $user->id));
}

// Die Rechte für ein Bild werden wie folgt bestimmt:
// 1) Alle Rechte für ein Bild werden von dem jeweiligen Album geerbt.
// 2) Der Besitzer eines Bildes verfügt über alle Rechte für dieses Bild, auch wenn ihm das Album nicht gehört.
// 3) Bilder eines Albums können gesperrt werden. In diesem Fall ist das Bild nur noch für
//       Leute mit Bearbeitungsrechten sichtbar.

function handleImagePermissions($image, $user, $action = PERM_VIEW)
{
	if($user->id == USER_ANONYMOUS_ID) {
		$image->whereAdd('FALSE'); return;}
	if(($action & PERM_EDIT) && $user->rank < RANK_MEMBER) {
		$image->whereAdd('FALSE'); return;}
		
	$whereSql = "ownerId = %d OR EXISTS(SELECT album.id FROM album WHERE album.id = image.albumId AND
		(album.ownerId = %d OR album.rights >= 3 %s))";
	if(($action & PERM_VIEW) && $user->rank <= RANK_MEMBER)
		$image->whereAdd(sprintf($whereSql, $user->id, $user->id, "OR (image.rights >= 1 AND album.rights >= 1)"));
	if(($action & PERM_EDIT) && $user->rank <= RANK_MEMBER)
		$image->whereAdd(sprintf($whereSql, $user->id, $user->id, ""));
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
	return array("Dieses Album ist privat.", "Andere Mitglieder dürfen dieses Album anschauen.",
		"Andere Mitglieder dürfen weitere Bilder hochladen.", "Andere Mitglieder dürfen alle Bilder bearbeiten.");
}
		
?>
