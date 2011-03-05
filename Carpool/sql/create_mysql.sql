DROP TABLE IF EXISTS Cities;
DROP TABLE IF EXISTS Contacts;
DROP TABLE IF EXISTS Languages;
DROP TABLE IF EXISTS QuestionsAnswers;
DROP TABLE IF EXISTS Ride;
DROP TABLE IF EXISTS ShowInterestNotifier;

CREATE TABLE Cities (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `Name` (`Name`)
);

CREATE TABLE Contacts (
  `Identifier` text COLLATE utf8_bin,
  `Email` varchar(100) COLLATE utf8_bin NOT NULL,
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(128) COLLATE utf8_bin NOT NULL,
  `Phone` varchar(24) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `Email` (`Email`)
);

CREATE TABLE IF NOT EXISTS Languages (
  `Id` int(11) NOT NULL,
  `Abbrev` varchar(4) COLLATE utf8_bin NOT NULL,
  `Name` varchar(16) COLLATE utf8_bin NOT NULL,
  `Locale` varchar(16) COLLATE utf8_bin NOT NULL,
  `Direction` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`Id`)
);

INSERT INTO Languages VALUES(1,'en','English','en',0);
INSERT INTO Languages VALUES(2,'he','Hebrew','he_IL.UTF-8',1);

CREATE TABLE QuestionsAnswers (
  `Id` decimal(10,0) DEFAULT NULL,
  `Lang` int(11) NOT NULL,
  `Question` text COLLATE utf8_bin,
  `Answer` text COLLATE utf8_bin,
  UNIQUE KEY `Id` (`Id`,`Lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE Ride (
  `Active` decimal(10,0) DEFAULT NULL,
  `Notify` decimal(10,0) DEFAULT NULL,
  `TimeUpdated` decimal(10,0) DEFAULT NULL,
  `TimeCreated` decimal(10,0) DEFAULT NULL,
  `Comment` text COLLATE utf8_bin,
  `Status` decimal(10,0) DEFAULT NULL,
  `TimeEvening` decimal(10,0) DEFAULT NULL,
  `TimeMorning` decimal(10,0) DEFAULT NULL,
  `ContactId` decimal(10,0) DEFAULT NULL,
  `DestCityId` decimal(10,0) DEFAULT NULL,
  `DestLocation` varchar(512) COLLATE utf8_bin DEFAULT NULL,
  `SrcCityId` decimal(10,0) DEFAULT NULL,
  `SrcLocation` varchar(512) COLLATE utf8_bin DEFAULT NULL,
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`Id`)
);

CREATE TABLE ShowInterestNotifier (
  `LastRun` decimal(10,0) DEFAULT NULL
);

INSERT INTO ShowInterestNotifier (`LastRun`) VALUES (0);
