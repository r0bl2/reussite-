CREATE DATABASE edutec_app;
USE edutec_app;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(150) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student','teacher','parent','other','admin') NOT NULL,
    type_lycee ENUM('college','lycee_modern','lycee_technique') DEFAULT NULL,
    niveau VARCHAR(50) DEFAULT NULL,
    classe VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE teacher_subject (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE parents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE institutions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    type ENUM('universite','centre_formation') NOT NULL,
    created_by INT NOT NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    niveau VARCHAR(50),
    classe VARCHAR(50),
    title VARCHAR(255) NOT NULL,
    description TEXT,
    file_path VARCHAR(255),
    active BOOLEAN DEFAULT FALSE,
    created_by INT NOT NULL,  -- admin uniquement
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE summaries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    niveau VARCHAR(50),
    classe VARCHAR(50),
    title VARCHAR(255),
    description TEXT,
    file_path VARCHAR(255),
    active BOOLEAN DEFAULT FALSE,
    created_by INT NOT NULL,
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE exercises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    niveau VARCHAR(50),
    classe VARCHAR(50),
    title VARCHAR(255),
    description TEXT,
    file_path VARCHAR(255),
    active BOOLEAN DEFAULT FALSE,
    created_by INT NOT NULL,
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE corrections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exercise_id INT NOT NULL,
    file_path VARCHAR(255),
    active BOOLEAN DEFAULT FALSE,
    created_by INT NOT NULL,
    FOREIGN KEY (exercise_id) REFERENCES exercises(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    description TEXT,
    file_path VARCHAR(255),
    price DECIMAL(10,2) DEFAULT 0.00,
    active BOOLEAN DEFAULT FALSE,
    created_by INT NOT NULL,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

