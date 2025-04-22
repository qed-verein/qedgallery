var sliceSize = 1000000;
var uploadURL = 'sliced_upload.php';

var successCallback;
var messageCallback;
var paused = false;
var pending = null;
var timeout = null;

function uploadDebug(text) {
	if(messageCallback) messageCallback(text);
}

function abortUpload() {
	uploadDebug("Das Hochladen wurde abgebrochen.\n");
	clearTimeout(timeout);
	paused = true; pending = null;
}

function pauseUpload() {
	uploadDebug("Das Hochladen wurde angehalten.\n");
	clearTimeout(timeout);
	paused = true;
}

function resumeUpload() {
	uploadDebug("Das Hochladen wird fortgesetzt.\n");
	paused = false;
	if(pending) pending();
}

function uploadPart(file, offset, length, onSuccess)
{
	pending = function() {uploadPart(file, offset, length, onSuccess)};
	if(paused) return;

	uploadDebug("Lade den Bereich [" + offset + ", " + (offset + length - 1) + "] hoch " +
		"(insgesamt " + file.size +  " Bytes).\n");
	var data = file.slice(offset, Math.min(offset + length, file.size));
	
	var form = new FormData();
	form.append("name", file.name);
	form.append("type", file.type);
	form.append("data", data);
	form.append("offset", "" + offset);
	form.append("length", "" + length);

    var ajaxRequest = new XMLHttpRequest();
    ajaxRequest.onreadystatechange = function () {
		if(ajaxRequest.readyState == 4) {
			uploadDebug(ajaxRequest.response);
			if(ajaxRequest.status == 200) onSuccess();
			else timeout = setTimeout(pending, 60000);
		}
	}

    ajaxRequest.open("POST", uploadURL, true);
    ajaxRequest.send(form);
}

function uploadParts(file, offset, length, onSuccess)
{
	length = Math.min(length, file.size - offset);
	uploadPart(file, offset, length, function() {
		if(offset + length == file.size) onSuccess();
		else uploadParts(file, offset + length, length, onSuccess);
	});
}

function uploadFiles(files, index, onSuccess)
{
	uploadDebug("Lade Datei " + (index + 1) + " von " + files.length + " hoch.\n");
	uploadParts(files[index], 0, sliceSize, function() {
		if(index + 1 == files.length) onSuccess();
		else uploadFiles(files, index + 1, onSuccess);
	});
}

function handleFileSelect(event)
{
	paused = false;
	uploadDebug("Beginne mit dem Hochladen.\n");
	uploadFiles(event.target.files, 0, function() {
		uploadDebug("Dateien wurde erfolgreich übertragen.\n");
		if(successCallback) successCallback(event.target.files);
	});
}

function onLoad()
{
	output = document.getElementById('console');
	output.value = "Ausgabe:\n";
	messageCallback = function(text) {
		output.value += text;
		output.scrollTop = output.scrollHeight;
	}
	successCallback = function(files) {
		uploadDebug("Trage die übertragenen Dateien in die Datenbank ein...\n");
		var form = document.createElement('form');
		form.action = 'image_upload.php?upload=1&albumid=' + document.getElementById('albumId').value;
		form.method = 'POST';
		for(var i = 0; i < files.length; ++i)
		{
			var input = document.createElement('input');
			input.type = 'hidden';
			input.name = 'file[]';
			input.value = files[i].name;
			form.appendChild(input);
		}
		document.body.appendChild(form);
		form.submit();
	}
	document.getElementById('file').addEventListener('change', handleFileSelect, false);
}
