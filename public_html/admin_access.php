<?php

require_once('path.php');
require_once('include/common.php');

define('ACCESS_ENTRIES_PER_PAGE', 25);

function accessPageCount()
{
	$access = DB_DataObject::factory('access');
	$accessCount = $access->count('*');

	$pageCount = ceil($accessCount / ACCESS_ENTRIES_PER_PAGE);
	if($pageCount == 0) $pageCount = 1;

	return $pageCount;
}

function renderAccessList($page, $accessEntries)
{
	$pageCount = accessPageCount();
	$pageNumberText = sprintf("Seite %d von %d", $page, $pageCount);

	if($page == 1)
		$linkPrev = "vorherige Seite";
	else
		$linkPrev = sprintf("<a href='%s'>vorherige Seite</a>",
			htmlText(urlAdminAccess($page - 1)));

	if($page == $pageCount)
		$linkNext = "nächste Seite";
	else
		$linkNext = sprintf("<a href='%s'>nächste Seite</a>",
			htmlText(urlAdminAccess($page + 1)));


	$htmlNav = "<div style='text-align: center'>\n";
	$htmlNav .= "<span style='margin-right: 5cm'>$linkPrev</span>\n";
	$htmlNav .= $pageNumberText."\n";
	$htmlNav .= "<span style='margin-left: 5cm'>$linkNext</span>\n";
	$htmlNav .= "</div>\n";

	$content = $htmlNav;
	$content .= "<table class='ptable'>\n";
    $content .= "<tr>";
    $content .= "<th style='width: 10em'>Benutzer</th>";
    $content .= "<th style='width: 25em'>Seite</th>";
    $content .= "<th style='width: 11em'>Zeitpunkt</th>";
    $content .= "<th style='width: 8em'>IP-Adresse</th>";

	foreach($accessEntries as $access)
	{
		$content .= "<tr>\n";
		$content .= sprintf("<td>%s</td>\n",
			htmlText($access->username));
		$content .= sprintf("<td>%s</td>\n",
			htmlText($access->requestUri));
		$content .= sprintf("<td>%s</td>\n",
			htmlText(formatDateTime($access->requestTime)));
		$content .= sprintf("<td>%s</td>\n",
			htmlText($access->requestIp));
		$content .= "</tr>\n";
	}

	$content .= "</table>";
	$content .= $htmlNav;

	return $content;
}

if(!userIsAdmin($gUser))
	throw new AccessDeniedException();

$page = paramInt('page', 1);

$user =  DB_DataObject::factory('user');
$access = DB_DataObject::factory('access');
$access->orderBy('requestTime DESC');
$access->limit(($page - 1) * ACCESS_ENTRIES_PER_PAGE, ACCESS_ENTRIES_PER_PAGE);
//$access->joinAdd($user, 'LEFT');
$access->find();
$accessEntries = array();
while($access->fetch())
{
	$user = $access->getLink('userId');
	$access->username = $user ? $user->username : 'anonym';
	$accessEntries[] = clone($access);
}

$layout = new StandardLayout;
$layout->title = "Adminseite - Protokoll";


$layout->content = renderAccessList($page, $accessEntries);
echo $layout->render();

?>
