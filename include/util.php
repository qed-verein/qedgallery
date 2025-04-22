<?php

function paramString($name, $default = null)
{
	if(!isset($_REQUEST[$name]))
	{
		if(is_null($default)) exit(sprintf("Fehler: Parameter %s fehlt", $name));
		else return $default;
	}

	return $_REQUEST[$name];
}


function paramInt($name, $default = null)
{
	if(!isset($_REQUEST[$name]) || !is_numeric($_REQUEST[$name]))
	{
		if(is_null($default)) exit(sprintf("Fehler: Parameter %s fehlt", $name));
		else return $default;
	}

	return intval($_REQUEST[$name]);
}


function redirect($url)
{
	header('Location: ' . $url);
	exit;
}

class DB_Lock
{
	private $db;

	function __construct($db, $sql)
	{
		$this->db = &$db;
		$this->db->query("LOCK TABLES " . $sql);
	}

	function __destruct()
	{
		$this->db->query("UNLOCK TABLES");
	}
}


?>
