<?php

require_once('path.php');
require_once('include/common.php');
require_once('views/image_html.php');
require_once('views/image_html_neu.php');

define('L_IMAGE_VIEW', "Bild anzeigen");

$imageId = paramInt('imageid');
$mode = paramString('mode', 'details');


if(!testImagePermissions(PERM_VIEW, $imageId))
{
  if($gUser->id == USER_ANONYMOUS_ID)
    redirect(urlLoginAndRedirectToImage($imageId));
  throw new AccessDeniedException();
}

$image = DB_DataObject::factory('image');
handleImageFilters($image, filterFromURL());
$res = $image->get($imageId);
if($res == 0) throw new ImageNotFoundException($imageId);
$album = $image->getLink('albumId');

if($mode == 'fullscreen') {
  $imageRenderer = new ImageRendererNeu($image);
  echo renderImageLayout(
    sprintf("Bild anzeigen: %s", $image->title),
    $imageRenderer->render());
}
else if($mode == 'details') {
  $newComment = DB_DataObject::factory('comment');
  $newComment->imageId = $imageId;
  $newComment->ownerId = $gUser->id;
  $newComment->creationTime = time();
  
  
  $user = DB_DataObject::factory('user');
  $comment = DB_DataObject::factory('comment');
  $comment->imageId = $imageId;
  $comment->orderBy('creationTime ASC');
  $comment->joinAdd($user, 'LEFT');
  $comment->find();
  
  $comments = array();
  while($comment->fetch())
    $comments[] = clone($comment);
  
  $imageRenderer = new ImageRenderer($image);
  $imageRenderer->comments = $comments;
  $imageRenderer->newComment = $newComment;
  
  if(isset($_POST['addcomment']))
  {
    $newComment->content = trim(paramString('content', ''));
  
    try
    {
      validateComment($newComment);
      $newComment->insert();
      redirect(urlImageView($imageId, filterFromURL()));
    }
    catch(InvalidInputException $e)
    {
      $imageRenderer->newCommentErrorMessage = $e->getMessage();
    }
  }
  
  $layout = new StandardLayout;
  $layout->title = L_IMAGE_VIEW;
  $layout->content = $imageRenderer->render();
  $layout->currentImage = $image;
  $layout->currentAlbum = $album;
  echo $layout->render();
}
else {
  die("Invalid mode");
}

?>
