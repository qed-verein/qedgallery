<?php

require_once('path.php');
require_once('include/common.php');
require_once('views/album_form.php');

class AlbumEditController
{
	var $layout, $formular, $action;
	var $album, $selectedImages;

	
	private function permissionCheck()
	{
		$permitted = false;
		if($this->action == 'create_album')
			$permitted = testAlbumPermissions(PERM_UPLOAD);
		elseif(in_array($this->action, array('change_settings', 'delete_album'), true))
			$permitted = testAlbumPermissions(PERM_EDIT, $this->album->id);
		elseif(in_array($this->action, array('change_category', 'delete_images', 'rotate_images'), true))
		{
			if(isset($_POST['submit']) && empty($this->selectedImages))
				throw new InvalidInputException("Keine Bilder ausgewählt");
				
			$permitted = true;
			foreach($this->selectedImages as $image)
				$permitted &= testImagePermissions(PERM_EDIT, $image->id) &&
					$image->albumId == $this->album->id;
		}
		
		if(!$permitted) throw new AccessDeniedException();
	}

	private function selectionHandler()
	{
		if(isset($_POST['select_all']))
			foreach($this->formular->images as $image)
				$this->formular->selectedIds[$image->id] = 1;
		elseif(isset($_POST['select_none']))
			$this->formular->selectedIds = array();
	}


	private function initializeVariables()
	{
		global $gUser;

		$this->formular->action = $this->action;
		if($this->action == 'create_album')
			return;

		$albumId = paramInt('albumid');
		$this->album = DB_DataObject::factory('album');
		$this->filter = filterFromURL();
		$res = $this->album->get($albumId);
		if($res == 0) throw new AlbumNotFoundException($albumId);
		$this->layout->currentAlbum = $this->album;
		
		$this->formular->album = $this->album;
		$this->formular->filter = $this->filter;
		$this->formular->selectedIds = isset($_POST['selected']) ? $_POST['selected'] : array();
		$this->formular->inputCategory = paramString('category', '');

		$image = DB_DataObject::factory('image');
		$image->albumId = $this->album->id;
		handleImagePermissions($image, $gUser, PERM_EDIT);
		handleImageFilters($image, $this->filter);
		$image->orderBy('originalTime ASC, id ASC');
		$image->find();
		$this->formular->images = array();
		while($image->fetch())
			$this->formular->images[] = clone($image);

		$this->selectedImages = array();
		foreach($this->formular->selectedIds as $id => $value)
		{
			if($value != '1') continue;
			$image = DB_DataObject::factory('image');
			$image->get($id);
			$this->selectedImages[] = clone($image);
		}
	}

	
	private function albumFromParameters($album)
	{
		$album->title = paramString('title', '');
		$album->description = paramString('description', '');
		$album->originalFrom = parseDateTime(paramString('originalfrom', 0));
		$album->originalTill = parseDateTime(paramString('originaltill', 0));
		$album->rights = paramInt('rights', 1);
	}


	private function successMessage($message, $redirect)
	{
		$this->layout->content = formatInformation($message, $redirect);
		$this->layout->redirectionURL = $redirect;
	}
	
	private function createAlbumHandler()
	{
		global $gUser;			
		$album = DB_DataObject::factory('album');
		$this->albumFromParameters($album);
		$album->ownerId = $gUser->id;
		$album->creationTime = time();
		$this->formular->album = $album;
			
		if(isset($_POST['submit']))
		{
			validateAlbum($album);
			galleryCreateAlbum($album);
			$this->successMessage("Das Album wurde erfolgreich erstellt.", urlAlbumView($album->id));
		}
	}

	private function changeSettingsHandler()
	{		
		if(isset($_POST['submit']))
		{
			$this->albumFromParameters($this->album);
			$this->album->id = $this->album->id;
			validateAlbum($this->album);
			$this->album->update();
			$this->successMessage("Die Albumdaten wurden erfolgreich geändert.", urlAlbumView($this->album->id));
		}
	}
	
	private function changeCategoryHandler()
	{
		if(isset($_POST['submit']))
		{
			foreach($this->selectedImages as $image)
			{
				$image->category = $this->formular->inputCategory;
				$image->update();
			}
			$this->successMessage("Die Kategorie der Bilder wurde erfolgreich geändert.",
				urlAlbumView($this->album->id));
		}
	}

	private function rotateImagesHandler()
	{
		$rotation = 0;
		if(isset($_POST['rotate90'])) $rotation = 90;
		if(isset($_POST['rotate180'])) $rotation = 180;
		if(isset($_POST['rotate270'])) $rotation = 270;

		if($rotation != 0)
		{
			foreach($this->selectedImages as $image)
				rotateImage($image, $rotation);
			$this->successMessage("Die Bilder wurden erfolgreich gedreht.",
				urlAlbumEdit($this->album->id, $this->action));
		}
	}
	
	private function deleteImagesHandler()
	{
		if(isset($_POST['yes']))
		{
			foreach($this->selectedImages as $image)
				galleryDeleteImage($image);
			$this->successMessage("Die Bilder wurden erfolgreich gelöscht.", urlAlbumView($this->album->id));
		}
		elseif(isset($_POST['submit']))
		{
			$message = "Sollen die markierten Bilder wirklich gelöscht werden?";
			$this->layout->content = formatQuestionYesNo($message, urlAlbumEdit($this->album->id, $this->action),
				array('delete_images' => '1', 'selected' => $this->formular->selectedIds));
		}
	}
	
	private function deleteAlbumHandler()
	{	
		if(isset($_POST['yes']))
		{
			galleryDeleteAlbum($this->album);
			$this->layout->currentAlbum = null;
			$this->successMessage("Die Album wurden gelöscht.", urlAlbumList());
		}
		elseif(isset($_POST['no']))
			redirect(urlAlbumView($this->album->id));
		else
		{
			$message = sprintf("Soll das Album <i>%s</i> wirklich gelöscht werden?",
				htmlText($this->album->title));
			$this->layout->content = formatQuestionYesNo($message, urlAlbumEdit($this->album->id, 'delete_album'),
				array('delete_album' => '1'));
		}
	}

	function run()
	{
		$allActions = array('create_album', 'change_settings', 'delete_album', 'delete_images',
			'change_category', 'select_all', 'select_none', 'rotate_images');
		$this->action = paramString('action');
		if(array_search($this->action, $allActions) === false)
			throw new Exception("Ungültiger Parameter: action");
		$this->initializeVariables();
		$this->layout->title = $this->formular->actionTitle($this->action);
		
		try
		{
			$this->permissionCheck();
			if(isset($_POST['select_all']) || isset($_POST['select_none']))
				$this->selectionHandler();
			if($this->action == 'create_album')
				$this->createAlbumHandler();
			elseif($this->action == 'change_settings')
				$this->changeSettingsHandler();
			elseif($this->action == 'change_category')
				$this->changeCategoryHandler();
			elseif($this->action == 'rotate_images')
				$this->rotateImagesHandler();
			elseif($this->action == 'delete_images')
				$this->deleteImagesHandler();
			elseif($this->action == 'delete_album')
				$this->deleteAlbumHandler();
		}
		catch(InvalidInputException $e)
		{
			$this->formular->errorMessage = $e->getMessage();
		}

		if(empty($this->layout->content))
			$this->layout->content = $this->formular->render();
		echo($this->layout->render());
	}
	
	function __construct()
	{
		$this->layout = new StandardLayout;
		$this->formular = new AlbumFormular;
	}
};

$controller = new AlbumEditController;
$controller->run();

?>
