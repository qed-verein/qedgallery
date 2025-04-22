<?php

//error_reporting(E_ALL);
//set_exception_handler('globalExceptionHandler');

session_start();
setlocale(LC_ALL, 'de_DE.utf8');
date_default_timezone_set('Europe/Berlin');

$config = parse_ini_file(GALLERY_PATH . '/dataobject.ini', TRUE);
foreach($config as $class => $values) {
    $options = &PEAR::getStaticProperty($class, 'options');
    $options = $values;
}

// disable php magic quotes
function array_stripslashes(&$var)
{
	if(is_string($var))
		$var = stripslashes($var);
	else if(is_array($var))
		foreach($var as $key => $value)
			array_stripslashes($var[$key]);
}

// create dummy object, so that we can access the connection
// to force the encoding of the connection to UTF8
$obj_utf8 = DB_DataObject::Factory('image');
$obj_db = $obj_utf8->getDatabaseConnection();

// only force connection to utf8 if backend db is MySQL
$str_dbtype = $obj_db->dsn['dbsyntax'];
// detect both mysql and mysqli connection types
if ((substr($str_dbtype,0,5)) == "mysql") {
	$obj_utf8->query( 'SET NAMES "utf8"' ); }

// kill the dummy object
unset($obj_utf8);


if(isset($_COOKIE['userid']) && isset($_COOKIE['pwhash']))
	$GLOBALS['userid'] = cookieAuthenticate($_COOKIE['userid'], $_COOKIE['pwhash']);
$gUser = getCurrentUser();

if(OFFLINE == 1)
{
	if($gUser->username != "TamásKorodi")
		die(renderSimpleLayout("Wartungsarbeiten", "Aufgrund von Wartungsarbeiten ist die Bildergallerie derzeit abgeschaltet. In Kürze werden die Bilder wieder zur Verfügung stehen. Wir bitten um Verständnis."));
}

$pathinfo = pathinfo($_SERVER['SCRIPT_NAME']);
$scriptname = $pathinfo['basename'];

//if(isset($_SERVER['REMOTE_ADDR']) && ($scriptname != 'image.php' && $scriptname != 'admin_access.php'))
//{
	//$access = DB_DataObject::Factory('access');
	//$access->userId = $gUser->id;
	//$access->requestUri = $_SERVER['REQUEST_URI'];
	//$access->requestTime = time();
	//$access->requestIp = $_SERVER['REMOTE_ADDR'];
	//$access->insert();
//}

if(isset($_SERVER['REMOTE_ADDR']) && ($scriptname == 'image_view.php'))
{
	$image = DB_DataObject::Factory('image');
	$res = $image->get(paramInt('imageid'));
	if($res !== null && time() >= $image->lastViewed + 60)
	{
		$image->viewCounter += 1;
		$image->lastViewed = time();
		$image->update();
	}
}


?>
