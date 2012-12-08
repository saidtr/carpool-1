
CREATE TABLE Regions (
  Id int(11) NOT NULL AUTO_INCREMENT,
  Name varchar(100) NOT NULL,
  Abbrev varchar(4) NOT NULL,
  DefaultSrcCityId int(11),
  DefaultSrcLocation varchar(256),
  DefaultDestCityId int(11),
  DefaultDestLocation varchar(256),
  PRIMARY KEY (`Id`),
  UNIQUE KEY `Name` (`Name`),
  UNIQUE KEY `Abbrev` (`Abbrev`)
);

-- We must put a default value as there are existing values and we want it to be non-null
-- Better have the deafult value meaningless, otherwise some bugs will be harder to trace
ALTER TABLE `Cities` ADD `Region` INT NOT NULL DEFAULT '0';
ALTER TABLE `Ride` ADD `Region` INT NOT NULL DEFAULT '0';

-- Now, put the default region in the existing data. Assuming default is "1"
UPDATE `Cities` SET Region = 1;
UPDATE `Ride` SET Region = 1;

-- Update indexes on the Cities table
ALTER TABLE Cities DROP INDEX Name;
ALTER TABLE `Cities` ADD UNIQUE (
	`Name` ,
	`Region`
);
ALTER TABLE `Cities` DROP PRIMARY KEY, ADD PRIMARY KEY ( `Id` , `Region` );

