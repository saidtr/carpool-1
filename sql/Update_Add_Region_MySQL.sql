
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
ALTER TABLE `cities` ADD `Region` INT NOT NULL DEFAULT '0';
ALTER TABLE `ride` ADD `Region` INT NOT NULL DEFAULT '0';

-- Now, put the default region in the existing data. Assuming default is "1"
UPDATE `cities` SET Region = 1;
UPDATE `ride` SET Region = 1;
