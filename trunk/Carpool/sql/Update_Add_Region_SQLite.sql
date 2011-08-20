
CREATE TABLE Regions (
  Id INTEGER PRIMARY KEY,
  Name TEXT,
  Abbrev TEXT,
  DefaultSrcCityId NUMERIC,
  DefaultSrcLocation TEXT,
  DefaultDestCityId NUMERIC,
  DefaultDestLocation TEXT
);

-- We must put a default value as there are existing values and we want it to be non-null
-- Better have the deafult value meaningless, otherwise some bugs will be harder to trace
ALTER TABLE `cities` ADD `Region` INT NOT NULL DEFAULT '0';
ALTER TABLE `ride` ADD `Region` INT NOT NULL DEFAULT '0';

-- Now, put the default region in the existing data. Assuming default is "1"
UPDATE `cities` SET Region = 1;
UPDATE `ride` SET Region = 1;
