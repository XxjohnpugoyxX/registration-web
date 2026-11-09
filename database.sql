CREATE DATABASE IF NOT EXISTS registration_db;
USE registration_db;

-- Events Table
CREATE TABLE IF NOT EXISTS events (
    evCode   INT AUTO_INCREMENT PRIMARY KEY,
    evName   VARCHAR(150) NOT NULL,
    evDate   DATE         NOT NULL,
    evVenue  VARCHAR(150) NOT NULL,
    evRFee   DECIMAL(10,2) NOT NULL
);

-- Participants Table (evCode is a foreign key to events)
CREATE TABLE IF NOT EXISTS participants (
    partID    INT AUTO_INCREMENT PRIMARY KEY,
    evCode    INT NOT NULL,
    partFName VARCHAR(100) NOT NULL,
    partLName VARCHAR(100) NOT NULL,
    partDRate DECIMAL(10,2) NOT NULL DEFAULT 0,
    FOREIGN KEY (evCode) REFERENCES events(evCode)
);

-- Registration Table (partID is a foreign key to participants)
CREATE TABLE IF NOT EXISTS registration (
    regCode  INT AUTO_INCREMENT PRIMARY KEY,
    partID   INT NOT NULL,
    regDate  DATE NOT NULL,
    regFPaid DECIMAL(10,2) NOT NULL,      -- evRFee - partDRate
    regPMode VARCHAR(10) NOT NULL,        -- Card or Cash
    FOREIGN KEY (partID) REFERENCES participants(partID)
);

-- Optional sample data
INSERT INTO events (evName, evDate, evVenue, evRFee) VALUES
('Tech Summit 2024', '2024-11-15', 'Convention Center', 1500.00),
('Job Fair 2024',    '2024-12-01', 'City Gym',           500.00);

INSERT INTO participants (evCode, partFName, partLName, partDRate) VALUES
(1, 'Juan',  'Dela Cruz', 200.00),
(1, 'Maria', 'Santos',      0.00),
(2, 'Pedro', 'Reyes',     100.00);
