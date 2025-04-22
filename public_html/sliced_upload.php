<?php

require_once('path.php');
require_once('include/common.php');

function exceptionHandler($exception) {
	http_response_code(400);
	die($exception->getMessage());
}
set_exception_handler('exceptionHandler');


if(!userIsMember($gUser)) throw new Exception("Zugriff wurde verweigert.");
if(!isset($_POST['name'])) throw new Exception("Ungültiger Dateiname.");
$name = $_POST['name'];

if(!isset($_SESSION['sliceupload'][$name]))
	$_SESSION['sliceupload'][$name] = tempnam(TEMPORARY_DIR, "slice");

if(!isset($_FILES['data'])) throw new Exception("Kein Fragment hochgeladen.");
if($_FILES['data']['error'] != 0) throw new Exception("Fehler beim Übertragen des Fragments.");

$offset = intval($_POST['offset']);
$length = $_FILES['data']['size'];
if($offset < 0) throw new Exception("Ungültiges Intervall.");
if($offset + $length > MAX_UPLOAD_SIZE) throw new Exception("Hochgeladene Datei ist zu groß.");

$tempfile = fopen($_SESSION['sliceupload'][$name], 'c');
if(!$tempfile) throw new Exception("Temporäre Datei konnte nicht geöffnet werden"); 
fseek($tempfile, $offset, SEEK_SET);

$upfile = fopen($_FILES['data']['tmp_name'], 'r');
if(!$upfile) throw new Exception("Fragment konnte nicht geöffnet werden"); 
while(!feof($upfile)) fwrite($tempfile, fread($upfile, 8192));
fclose($upfile);
unlink($_FILES['data']['tmp_name']);

?>
