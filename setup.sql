-- Run this SQL in your MySQL/phpMyAdmin to set up the database

CREATE DATABASE IF NOT EXISTS php_crud CHARACTER SET utf8 COLLATE utf8_general_ci;

USE php_crud;

CREATE TABLE IF NOT EXISTS students (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)  NOT NULL,
    email       VARCHAR(150)  NOT NULL UNIQUE,
    roll_number VARCHAR(50)   NOT NULL UNIQUE,
    class       VARCHAR(50)   NOT NULL,
    teacher     VARCHAR(100)  NOT NULL,
    profile_image VARCHAR(255) DEFAULT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
