DROP TABLE IF EXISTS person;

CREATE TABLE person
(
id integer unsigned NOT NULL AUTO_INCREMENT,
username varchar(255) NOT NULL UNIQUE,
email varchar(255) NOT NULL UNIQUE,
password varchar(255) NULL,
gender tinyint NOT NULL,
first_name varchar(255) NOT NULL,
middle_name varchar(255) NULL,
last_name varchar(255) NOT NULL,
street_address varchar(255) NULL,
city varchar(255) NULL,
state varchar(255) NULL,
zipcode varchar(255) NULL,
country_code varchar(255) NULL,
country varchar(255) NULL,
telephone varchar(255) NULL,
mothers_maiden_name varchar(255) NULL,
birthday datetime NULL DEFAULT NULL,
occupation varchar(255) NULL,
company varchar(255) NULL,
vehicle varchar(255) NULL,
url varchar(255) NULL,
blood_type varchar(255) NULL,
weight decimal(4,2) NULL,
height decimal(4,2) NULL,
latitude decimal(9,6) NULL,
longitude decimal(9,6) NULL,
PRIMARY KEY(id)
) engine=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE person CHANGE birthday birthday date NULL;
ALTER TABLE person CHANGE email email varchar(255) NULL;

CREATE TABLE image
(
id integer unsigned NOT NULL AUTO_INCREMENT,
PRIMARY KEY(id)
) engine=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE person ADD COLUMN primary_image_id integer unsigned NULL;
ALTER TABLE person ADD COLUMN background_id tinyint NULL;

CREATE TABLE friendship
(
source_id integer unsigned,
target_id integer unsigned,
FOREIGN KEY(source_id) REFERENCES person(id),
FOREIGN KEY(target_id) REFERENCES person(id),
PRIMARY KEY(source_id, target_id)
) engine=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE post
(
id integer unsigned NOT NULL AUTO_INCREMENT,
person_id integer unsigned NOT NULL,
poster_id integer unsigned NOT NULL,
date_created datetime NOT NULL,
content text NOT NULL,
PRIMARY KEY(id),
FOREIGN KEY(person_id) REFERENCES person(id) ON DELETE CASCADE ON UPDATE CASCADE,
FOREIGN KEY(poster_id) REFERENCES person(id) ON DELETE CASCADE ON UPDATE CASCADE
) engine=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE comment
(
id integer unsigned NOT NULL AUTO_INCREMENT,
post_id integer unsigned NOT NULL,
poster_id integer unsigned NOT NULL,
date_created datetime NOT NULL,
content text NOT NULL,
PRIMARY KEY(id),
FOREIGN KEY(post_id) REFERENCES post(id) ON DELETE CASCADE ON UPDATE CASCADE,
FOREIGN KEY(poster_id) REFERENCES person(id) ON DELETE CASCADE ON UPDATE CASCADE
) engine=InnoDB DEFAULT CHARSET=utf8;

# Lussuhovi

ALTER TABLE person CHANGE weight weight decimal(6,2) NULL;
ALTER TABLE person CHANGE height height decimal(6,2) NULL;

ALTER TABLE image ADD COLUMN upload_path varchar(500) NULL;

# Lussen

CREATE TABLE company
(
id integer unsigned NOT NULL AUTO_INCREMENT,
name varchar(255) NOT NULL,
PRIMARY KEY(id)
) engine=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE image ADD COLUMN type tinyint unsigned NOT NULL DEFAULT 1;

ALTER TABLE company ADD COLUMN primary_image_id integer unsigned NULL;
ALTER TABLE company ADD COLUMN background_id tinyint NULL;
