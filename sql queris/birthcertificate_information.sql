CREATE DATABASE IF NOT EXISTS deathcertificate_db;
USE deathcertificate_db;
-- Ensure the `users` table exists
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(100) NOT NULL,
    role VARCHAR(50) NOT NULL
);

-- Ensure the `deathcertificate_information` table exists
CREATE TABLE IF NOT EXISTS deathcertificate_information (
    id INT AUTO_INCREMENT PRIMARY KEY,
    Name_of_the_filler VARCHAR(100) NOT NULL,
    Death_in_the VARCHAR(100),
    District_in_the VARCHAR(100),
    Entry_no VARCHAR(100),
    Place_of_Death VARCHAR(255),
    Occupation VARCHAR(255),
    sex CHAR(50),
    Name_and_Surname_of_Deceased VARCHAR(255),
    Cause_of_Death VARCHAR(255),
    Name_and_Description_of_Informant VARCHAR(255),
    Name_of_Registering_Officer VARCHAR(255),
    Date_of_Registration DATE,
    District_Assistance VARCHAR(255),
    Registrar VARCHAR(255),
    year_year VARCHAR(100),
    month_of_year VARCHAR(100),
    day_of_week VARCHAR(100),
    Date_of_death DATE,
    Age_of_Deceased INT,
    Residence VARCHAR(255),
    submission_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,  
    Date DATE,
    user_id INT
);

-- Add the foreign key constraint
ALTER TABLE deathcertificate_information
ADD CONSTRAINT fk_user_id
FOREIGN KEY (user_id) REFERENCES users(id);
