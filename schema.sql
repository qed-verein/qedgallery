CREATE TABLE album (
	id           INT PRIMARY KEY AUTO_INCREMENT,
	ownerId      INT NOT NULL,
	title        VARCHAR(200) NOT NULL,
	description  TEXT NOT NULL,
	creationTime BIGINT,
	originalFrom BIGINT,
	originalTill BIGINT,
	rights       INTEGER NOT NULL
);

CREATE TABLE image (
	id           INT PRIMARY KEY AUTO_INCREMENT,
	albumId      INT NOT NULL,
	ownerId      INT NOT NULL,
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
	id           INT PRIMARY KEY AUTO_INCREMENT,
	imageId      INT NOT NULL,
	ownerId      INT NOT NULL,
	content      TEXT NOT NULL,
	creationTime BIGINT NOT NULL
);

CREATE TABLE access (
	userId       INT NOT NULL,
	requestUri   VARCHAR(256) NOT NULL,
	requestTime  BIGINT NOT NULL,
	requestIp    VARCHAR(32) NOT NULL
);

CREATE TABLE user (
	id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
	username VARCHAR(50) NOT NULL,
	password VARCHAR(50) NOT NULL,
	rank INT NOT NULL
);

INSERT INTO user (id, username, password, rank) VALUES
	(1, 'qedgallery', '0116fd4ce729731f2af07e2d81d73f60edf1e25f', 3);
