-- SQL schema for SMS
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS biometric_logs;
DROP TABLE IF EXISTS results;
DROP TABLE IF EXISTS exams;
DROP TABLE IF EXISTS salaries;
DROP TABLE IF EXISTS fee_payments;
DROP TABLE IF EXISTS fees;
DROP TABLE IF EXISTS student_attendance;
DROP TABLE IF EXISTS teacher_attendance;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS teachers;
DROP TABLE IF EXISTS timetable;
DROP TABLE IF EXISTS subjects;
DROP TABLE IF EXISTS sections;
DROP TABLE IF EXISTS classes;
DROP TABLE IF EXISTS expenses;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('super_admin','teacher','accountant') NOT NULL DEFAULT 'teacher',
  full_name VARCHAR(200),
  email VARCHAR(200),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX (role)
);

CREATE TABLE classes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL,
  code VARCHAR(20),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE sections (
  id INT AUTO_INCREMENT PRIMARY KEY,
  class_id INT NOT NULL,
  name VARCHAR(50) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
);

CREATE TABLE subjects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  code VARCHAR(50),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE teachers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  teacher_id VARCHAR(50) UNIQUE,
  full_name VARCHAR(200) NOT NULL,
  cnic VARCHAR(50),
  qualification VARCHAR(200),
  contact VARCHAR(100),
  salary DECIMAL(10,2) DEFAULT 0,
  joining_date DATE,
  status ENUM('Active','Left') DEFAULT 'Active',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  admission_no VARCHAR(50) UNIQUE,
  full_name VARCHAR(200) NOT NULL,
  father_name VARCHAR(200),
  b_form VARCHAR(50),
  class_id INT,
  section_id INT,
  roll_no VARCHAR(50),
  gender ENUM('Male','Female','Other'),
  dob DATE,
  contact VARCHAR(100),
  address TEXT,
  admission_date DATE,
  status ENUM('Active','Left') DEFAULT 'Active',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL,
  FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE SET NULL
);

CREATE TABLE student_attendance (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  class_id INT,
  section_id INT,
  attendance_date DATE NOT NULL,
  in_time TIME NULL,
  out_time TIME NULL,
  status ENUM('Present','Absent','Leave','Late') DEFAULT 'Present',
  recorded_by INT,
  recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  edited_by INT,
  edited_at DATETIME NULL,
  edit_reason VARCHAR(255) NULL,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  INDEX (attendance_date),
  INDEX (student_id)
);

CREATE TABLE attendance_edits (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  attendance_id BIGINT NOT NULL,
  old_status ENUM('Present','Absent','Leave','Late') NOT NULL,
  new_status ENUM('Present','Absent','Leave','Late') NOT NULL,
  edited_by INT NOT NULL,
  edited_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  reason VARCHAR(255),
  FOREIGN KEY (attendance_id) REFERENCES student_attendance(id) ON DELETE CASCADE,
  INDEX (edited_by)
);

CREATE TABLE teacher_attendance (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  teacher_id INT NOT NULL,
  attendance_date DATE NOT NULL,
  in_time TIME NULL,
  out_time TIME NULL,
  status ENUM('Present','Absent','Leave','Late') DEFAULT 'Present',
  recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
  INDEX (attendance_date),
  INDEX (teacher_id)
);

CREATE TABLE fees (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description TEXT,
  class_id INT NULL,
  amount DECIMAL(10,2) NOT NULL,
  fee_type ENUM('Admission','Monthly','Exam','Transport','Other') DEFAULT 'Monthly',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL
);

CREATE TABLE fee_payments (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  fee_id INT,
  amount DECIMAL(10,2) NOT NULL,
  paid_on DATETIME DEFAULT CURRENT_TIMESTAMP,
  paid_by INT,
  payment_method VARCHAR(50),
  note TEXT,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (fee_id) REFERENCES fees(id) ON DELETE SET NULL,
  INDEX (paid_on)
);

CREATE TABLE salaries (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  teacher_id INT NOT NULL,
  month_year VARCHAR(20) NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  bonus_deduction DECIMAL(10,2) DEFAULT 0,
  total_payout DECIMAL(10,2) NOT NULL,
  paid_status ENUM('Paid','Unpaid') DEFAULT 'Unpaid',
  paid_on DATETIME NULL,
  payment_method VARCHAR(100) NULL,
  payment_notes TEXT NULL,
  FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
  UNIQUE INDEX unique_teacher_month (teacher_id, month_year)
);

CREATE TABLE expenses (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  category VARCHAR(100) NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  expense_date DATE NOT NULL,
  description TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE exams (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  start_date DATE,
  end_date DATE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE results (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  exam_id INT NOT NULL,
  student_id INT NOT NULL,
  subject_id INT NOT NULL,
  marks_obtained DECIMAL(8,2),
  total_marks DECIMAL(8,2),
  grade VARCHAR(10),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

CREATE TABLE biometric_logs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  device_user_id VARCHAR(100),
  mapped_type ENUM('student','teacher') NULL,
  mapped_id INT NULL,
  timestamp DATETIME NOT NULL,
  raw_data TEXT,
  INDEX (device_user_id),
  INDEX (timestamp)
);

CREATE TABLE timetable (
  id INT AUTO_INCREMENT PRIMARY KEY,
  class_id INT NOT NULL,
  section_id INT NOT NULL,
  day ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  period INT NOT NULL,
  subject_id INT NOT NULL,
  teacher_id INT,
  start_time TIME,
  end_time TIME,
  FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
  FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE CASCADE,
  FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
  FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL
);

SET FOREIGN_KEY_CHECKS=1;
