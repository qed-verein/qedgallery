# qedgallery

Schritte für Setup:
* include/config.template.php nach include/config.php kopieren und Werte passend einsetzen
* dataobject.template.ini nach dataobject.ini kopieren und Eintrag für Datenbankverbindung anpassend.
* SQL-Databank anlegen und anschließend in dieser Datenbank die Befehle in schema.sql ausführen
* Es wird Standardbenutzer 'qedgallery' mit Passwort 'qedgallery' angelegt.

Mit docker-compose:
* include/config.template.php nach include/config.php kopieren (Werte sollten passen)
* `dataobject.docker.template.ini` nach `dataobject.ini` kopieren (Werte sollten passen)
* `docker compose up -d` ausführen
* DB sollte nun unter `http://localhost:8000` erreichbar sein
