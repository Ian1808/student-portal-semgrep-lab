-- Student Portal Database Schema
-- Run this in phpMyAdmin or MySQL CLI

CREATE DATABASE IF NOT EXISTS student_portal;
USE student_portal;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(32) NOT NULL,  -- VULNERABILITY: MD5 hash length (ASVS 6.4.1)
    email VARCHAR(100),
    role ENUM('student', 'lecturer') DEFAULT 'student',
    full_name VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Grades table
CREATE TABLE IF NOT EXISTS grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    course VARCHAR(50),
    grade VARCHAR(5),
    semester VARCHAR(20),
    FOREIGN KEY (student_id) REFERENCES users(id)
);

-- Attendance table
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    course VARCHAR(50),
    date DATE,
    status VARCHAR(20),
    FOREIGN KEY (student_id) REFERENCES users(id)
);

-- Assignments table
CREATE TABLE IF NOT EXISTS assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    lecturer_id INT,
    title VARCHAR(200),
    filename VARCHAR(255),
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (lecturer_id) REFERENCES users(id)
);

-- Messages table
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT,
    receiver_id INT,
    content TEXT,  -- VULNERABILITY: No content validation (ASVS 5.1.4)
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id),
    FOREIGN KEY (receiver_id) REFERENCES users(id)
);

-- Login logs (minimal, not for security)
CREATE TABLE IF NOT EXISTS login_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50),
    ip_address VARCHAR(45),
    success TINYINT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample data
-- VULNERABILITY: Weak passwords (ASVS 6.4.2, 6.4.3)
INSERT INTO users (username, password, email, role, full_name, phone, address) VALUES
('student1', MD5('password123'), 'student1@uni.edu', 'student', 'John Doe', '555-0101', '123 Campus St'),
('student2', MD5('password123'), 'student2@uni.edu', 'student', 'Jane Smith', '555-0102', '124 Campus St'),
('student3', MD5('123456'), 'student3@uni.edu', 'student', 'Bob Wilson', '555-0103', '125 Campus St'),
('lecturer1', MD5('admin123'), 'lecturer1@uni.edu', 'lecturer', 'Prof. Alice Johnson', '555-0201', 'Faculty Bldg 1'),
('lecturer2', MD5('password'), 'lecturer2@uni.edu', 'lecturer', 'Prof. Charlie Brown', '555-0202', 'Faculty Bldg 2');

-- Insert grades
INSERT INTO grades (student_id, course, grade, semester) VALUES
(1, 'CS101', 'A', 'Fall 2025'),
(1, 'MATH201', 'B+', 'Fall 2025'),
(2, 'CS101', 'B', 'Fall 2025'),
(2, 'MATH201', 'A-', 'Fall 2025'),
(3, 'CS101', 'C', 'Fall 2025'),
(3, 'MATH201', 'B', 'Fall 2025');

-- Insert attendance
INSERT INTO attendance (student_id, course, date, status) VALUES
(1, 'CS101', '2025-04-01', 'Present'),
(1, 'CS101', '2025-04-02', 'Present'),
(2, 'CS101', '2025-04-01', 'Present'),
(2, 'CS101', '2025-04-02', 'Absent'),
(3, 'CS101', '2025-04-01', 'Present');

-- Insert sample messages with XSS payload (for demonstration)
INSERT INTO messages (sender_id, receiver_id, content) VALUES
(4, 1, 'Welcome to the course! Please check the syllabus.'),
(1, 4, 'Thank you professor!'),
(2, 4, '<script>alert("XSS Test")</script>');  -- VULNERABILITY: Stored XSS

SELECT 'Database setup complete!' AS status;