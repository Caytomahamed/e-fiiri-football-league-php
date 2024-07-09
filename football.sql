-- Ensure the existence of the football database and create if necessary
CREATE DATABASE IF NOT EXISTS football;

-- Use the football database
USE football;

-- CREATE TEAMS TABLE
CREATE TABLE TEAMS (
    TEAM_ID INT PRIMARY KEY AUTO_INCREMENT,
    TEAM_NAME VARCHAR(255) NOT NULL,
    HOME_STADIUM VARCHAR(255) NOT NULL
);

-- INSERT TEAMS INTO TEAMS TABLE
INSERT INTO TEAMS (TEAM_ID, TEAM_NAME, HOME_STADIUM)
VALUES (1, 'Maaliyada FC', 'Maaliyada Stadium'),
       (2, 'Asluubta GFC', 'Asluubta Stadium'),
       (3, 'Dawlaada Hoose FC', 'DHH Stadium'),
       (4, 'Tamarta FC', 'Tamarta Stadium'),
       (5, 'Xidigaha Cirka FC', 'Cirka Stadium'),
       (6, 'Gaashaan FC', 'Goodir Stadium'),
       (7, 'Goodir FC', 'Goodir Stadium'),
       (8, 'Caafimadka FC', 'Caafimadka Stadium'),
       (9, 'Waxool FC', 'Waxool Stadium'),
       (10, 'Ganacsiga FC', 'Ganacsiga Stadium');

-- Create LEAGUE Table
CREATE TABLE LEAGUE (
    POSITION INT PRIMARY KEY AUTO_INCREMENT,
    TEAM_ID INT,
    WON INT DEFAULT 0,
    LOST INT DEFAULT 0,
    DRAW INT DEFAULT 0,
    GOALS_FOR INT DEFAULT 0,
    GOALS_AGAINST INT DEFAULT 0,
    POINTS INT DEFAULT 0,
    STATUS VARCHAR(50) DEFAULT 'Promoted',
    FOREIGN KEY (TEAM_ID) REFERENCES TEAMS(TEAM_ID)
);

-- INSERT TEAMS INTO LEAGUE TABLE
INSERT INTO LEAGUE (TEAM_ID) VALUES
(1),
(2),
(3),
(4),
(5),
(6),
(7),
(8),
(9),
(10);

-- Create PLAYER Table
CREATE TABLE PLAYER (
    PLAYER_ID INT PRIMARY KEY AUTO_INCREMENT,
    FNAME VARCHAR(255) NOT NULL,
    MNAME VARCHAR(255),
    LNAME VARCHAR(255) NOT NULL,
    DOB DATE,
    POSITION VARCHAR(50),
    WEIGHT INT,
    HEIGHT INT,
    NATIONALITY VARCHAR(255),
    KIT_NUMBER INT,
    TEAM_ID INT,
    CONTRACT_ID INT,
    FOREIGN KEY (TEAM_ID) REFERENCES TEAMS(TEAM_ID)
);



CREATE TABLE fixtures (
    FIXTURE_ID INT PRIMARY KEY AUTO_INCREMENT,
    HOME_TEAM_ID INT NOT NULL,
    AWAY_TEAM_ID INT NOT NULL,
    MATCH_DATE DATE NOT NULL,
    VENUE VARCHAR(255) NOT NULL,
    HOME_SCORE INT DEFAULT 0,
    AWAY_SCORE INT DEFAULT 0,
    FOREIGN KEY (HOME_TEAM_ID) REFERENCES teams(TEAM_ID),
    FOREIGN KEY (AWAY_TEAM_ID) REFERENCES teams(TEAM_ID)
);


-- Insert Players into PLAYER Table
INSERT INTO PLAYER (FNAME, MNAME, LNAME, DOB, POSITION, WEIGHT, HEIGHT, NATIONALITY, KIT_NUMBER, TEAM_ID, CONTRACT_ID) VALUES
('Ahmed', 'Mohamed', 'Ali', '1990-05-15', 'Forward', 75, 180, 'Somaliland', 9, 1, 101),
('Ismail', 'Abdi', 'Hassan', '1992-07-20', 'Midfielder', 70, 175, 'Somaliland', 8, 2, 102),
('Abdikarim', 'Omar', 'Jama', '1989-03-10', 'Defender', 80, 185, 'Somaliland', 5, 3, 103),
('Yusuf', 'Abdirahman', 'Hussein', '1995-12-25', 'Goalkeeper', 85, 190, 'Somaliland', 1, 4, 104),
('Ali', 'Hassan', 'Mohamoud', '1993-09-30', 'Forward', 76, 178, 'Somaliland', 10, 5, 105),
('Hassan', 'Ahmed', 'Omar', '1991-11-11', 'Midfielder', 72, 177, 'Somaliland', 7, 6, 106),
('Mohamed', 'Ismail', 'Abdi', '1990-01-01', 'Defender', 82, 183, 'Somaliland', 4, 7, 107),
('Abdi', 'Yusuf', 'Ali', '1988-08-20', 'Goalkeeper', 88, 188, 'Somaliland', 13, 8, 108);
