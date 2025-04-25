<?php

function filterFromURL()
{
	$filter = array();
	if(isset($_GET['byowner'])) $filter['byowner'] = paramInt('byowner');
	if(isset($_GET['byday'])) $filter['byday'] = paramString('byday');
	if(isset($_GET['byupload'])) $filter['byupload'] = paramString('byupload');
	if(isset($_GET['bycategory'])) $filter['bycategory'] = paramString('bycategory');
	return $filter;
}

function filterParameters($filter)
{
	$params = "";
	if(isset($filter['byowner'])) $params .= sprintf("&byowner=%d", $filter['byowner']);
	if(isset($filter['byday'])) $params .= sprintf("&byday=%s", rawurlencode($filter['byday']));
	if(isset($filter['byupload'])) $params .= sprintf("&byupload=%s", rawurlencode($filter['byupload']));
	if(isset($filter['bycategory'])) $params .= sprintf("&bycategory=%s", rawurlencode($filter['bycategory']));
	return $params;
}

function urlAlbumCreate() {
	return "album_edit.php?action=create_album"; }
function urlAlbumEdit($albumId, $action = '', $filter = array()) {
	return sprintf("album_edit.php?albumid=%d&action=%s%s", $albumId, $action, filterParameters($filter)); }
function urlAlbumDelete($albumId) {
	return sprintf("album_edit.php?albumid=%d&action=delete_album", $albumId); }
function urlAlbumList() {
	return sprintf("album_list.php"); }
function urlAlbumView($albumId, $filter = array(), $page = 1) {
	return sprintf("album_view.php?albumid=%d&page=%d%s", $albumId, $page, filterParameters($filter)); }

function urlImageUpload($albumId) {
	return sprintf("image_upload.php?albumid=%d", $albumId); }
function urlImageEdit($imageId) {
	return sprintf("image_edit.php?imageid=%d", $imageId); }
function urlImageDelete($imageId) {
	return sprintf("image_delete.php?imageid=%d", $imageId); }
function urlImageDetails($imageId, $filter = array()) {
	return sprintf("image_view.php?imageid=%d%s&mode=details", $imageId, filterParameters($filter)); }
function urlImageView($imageId, $filter = array()) {
	return sprintf("image_view.php?imageid=%d%s", $imageId, filterParameters($filter)); }

function urlImageRotateLeft($imageId) {
	return sprintf("image_edit.php?rotate=left&imageid=%d", $imageId); }
function urlImageRotateRight($imageId) {
	return sprintf("image_edit.php?rotate=right&imageid=%d", $imageId); }


function urlImageThumbnail($imageId) {
	return sprintf("image.php?imageid=%d&type=thumbnail", $imageId); }
function urlImageNormal($imageId) {
	return sprintf("image.php?imageid=%d&type=normal", $imageId); }
function urlImageOriginal($imageId) {
	return sprintf("image.php?imageid=%d&type=original", $imageId); }
function urlImageFullHD($imageId) {
	return sprintf("image.php?imageid=%d&type=fullhdbeta", $imageId); }
function urlImageDownload($imageId) {
	return sprintf("image.php?imageid=%d&type=download", $imageId); }


function urlLogin() {
	return "account.php"; }
function urlLoginAndRedirectToImage($imageId) {
	return sprintf("account.php?imageid=%d", $imageId); }
function urlLoginAndRedirectToAlbum($albumId) {
	return sprintf("account.php?albumid=%d", $albumId); }
function urlLogout() {
	return "account.php?logout=logout"; }

function urlAdminPage() {
	return "admin.php"; }
function urlAdminAccess($page) {
	return sprintf("admin_access.php?page=%d", $page); }
?>
