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
