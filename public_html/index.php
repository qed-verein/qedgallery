<?php

require_once('path.php');
require_once('include/common.php');

if($gUser->id != USER_ANONYMOUS_ID)
	redirect(urlAlbumList());
else
	redirect(urlLogin());

?>
