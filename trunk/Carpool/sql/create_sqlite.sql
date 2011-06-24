DROP TABLE IF EXISTS Cities;
DROP TABLE IF EXISTS Contacts;
DROP TABLE IF EXISTS Languages;
DROP TABLE IF EXISTS QuestionsAnswers;
DROP TABLE IF EXISTS Ride;
DROP TABLE IF EXISTS ShowInterestNotifier;

CREATE TABLE Cities (Id INTEGER PRIMARY KEY, Name TEXT);
CREATE TABLE Contacts (Identifier TEXT, Email TEXT, Id INTEGER PRIMARY KEY, Name TEXT, Phone TEXT, Role NUMERIC);
CREATE TABLE Languages (Id INTEGER PRIMARY KEY, Abbrev TEXT, Name TEXT, Locale TEXT, Direction NUMERIC);
INSERT INTO Languages VALUES(1,'en','English','en',0);
INSERT INTO Languages VALUES(2,'he','Hebrew','he_IL.UTF-8',1);
CREATE TABLE QuestionsAnswers (Id NUMERIC, Lang TEXT, Question TEXT, Answer TEXT);
CREATE TABLE Ride (Active NUMERIC, Notify NUMERIC, TimeUpdated NUMERIC, TimeCreated NUMERIC, Comment TEXT, Status NUMERIC, TimeEvening NUMERIC, TimeMorning NUMERIC, ContactId NUMERIC, DestCityId NUMERIC, DestLocation TEXT, SrcCityId NUMERIC, SrcLocation TEXT, Id INTEGER PRIMARY KEY);
CREATE TABLE ShowInterestNotifier (LastRun NUMERIC);
INSERT INTO ShowInterestNotifier VALUES(0);
CREATE UNIQUE INDEX Unique_City ON Cities(Name ASC);

