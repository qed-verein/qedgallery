-- Created from schema.sql via
-- sed -e 's/AUTO_INCREMENT/AUTOINCREMENT/g' -e 's/\(BIG\)\?INT /INTEGER /g'

CREATE TABLE album (
	id           INTEGER PRIMARY KEY AUTOINCREMENT,
	ownerId      INTEGER NOT NULL,
	title        VARCHAR(200) NOT NULL,
	description  TEXT NOT NULL,
	creationTime BIGINT,
	originalFrom BIGINT,
	originalTill BIGINT,
	rights       INTEGER NOT NULL
);

CREATE TABLE image (
	id           INTEGER PRIMARY KEY AUTOINCREMENT,
	albumId      INTEGER NOT NULL,
	ownerId      INTEGER NOT NULL,
	title        VARCHAR(200) NOT NULL,
	mimeType     VARCHAR(50) NOT NULL,
	uploadTime   BIGINT,
	originalTime BIGINT,
	viewCounter  BIGINT,
	lastViewed   BIGINT,
	category     VARCHAR(200) NOT NULL,
	rights       INTEGER NOT NULL
);

CREATE TABLE comment (
	id           INTEGER PRIMARY KEY AUTOINCREMENT,
	imageId      INTEGER NOT NULL,
	ownerId      INTEGER NOT NULL,
	content      TEXT NOT NULL,
	creationTime INTEGER NOT NULL
);

CREATE TABLE access (
	userId       INTEGER NOT NULL,
	requestUri   VARCHAR(256) NOT NULL,
	requestTime  INTEGER NOT NULL,
	requestIp    VARCHAR(32) NOT NULL
);

CREATE TABLE user (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	username VARCHAR(50) NOT NULL,
	password VARCHAR(50) NOT NULL,
	rank INTEGER NOT NULL
);

INSERT INTO user (id, username, password, rank) VALUES
	(1, 'qedgallery', '0116fd4ce729731f2af07e2d81d73f60edf1e25f', 3);
