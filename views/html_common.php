<?php

function htmlText($text)
{
	return htmlspecialchars($text, ENT_QUOTES);
}

function parseDateTime($str)
{
	return strtotime($str);
}

function formatDateTime($time)
{
	return strftime("%x %X", $time);
}

//function htmlHidden($list)
//{
	//$output = "\n";
	//foreach($list as $name => $value)
		//$output .= sprintf("<input type='hidden' name='%s' value='%s'>\n",
			//htmlText($name), htmlText($value));
	//return $output;
//}


function htmlHidden($list, $prefix = '')
{
	$output = "\n";
	foreach($list as $key => $value)
	{
		$keystring = $prefix != '' ? $prefix . '[' . $key . ']' : $key;
		if(is_array($value))
			$output .= htmlHidden($value, $keystring);
		else
			$output .= sprintf("<input type='hidden' name='%s' value='%s'>\n",
				htmlText($keystring), htmlText($value));
	}
	return $output;
}


function formatError($error)
{
	if(empty($error)) return "";
	$out = "<div class='error'>\n";
	$out .= sprintf("\t<p>%s</p>\n", nl2br(htmlText($error)));
	$out .= "</div>\n";
	return $out;
}

function formatInformation($message, $url = null)
{
	if(empty($message)) return "";
	$out = "<div class='information'>\n";
	$out .= sprintf("\t<p>%s</p>\n", htmlText($message));
	if(!is_null($url)) $out .= sprintf("\t<p><a href='%s'>weiter</a></p>\n", htmlText($url));
	$out .= "</div>\n";
	return $out;
}

function formatQuestionYesNo($message, $action, $hidden = array())
{
	$s = sprintf("<form action='%s' method='post'>\n", htmlText($action));
	$s .= "<div class='question'>\n";
	$s .= htmlHidden($hidden)."\n";
	$s .= sprintf("<p>%s</p>\n", $message);
	$s .= "<p>\n";
	$s .= "<input type='submit' name='yes' value='Ja'>\n";
	$s .= "<input type='submit' name='no' value='Nein'>\n";
	$s .= "</p>\n";
	$s .= "</div>\n";
	$s .= "</form>\n";
	return $s;
}

?>
