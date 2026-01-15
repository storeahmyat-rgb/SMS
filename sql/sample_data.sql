-- Sample data for SMS testing
-- Run this after setup.php to populate test data

-- Insert Sample Classes
INSERT INTO classes (name, code) VALUES
('Playgroup', 'PG'),
('Prep', 'PREP'),
('Class 1', 'CLASS1'),
('Class 2', 'CLASS2'),
('Class 3', 'CLASS3'),
('Class 4', 'CLASS4'),
('Class 5', 'CLASS5'),
('Class 6', 'CLASS6'),
('Class 7', 'CLASS7'),
('Class 8', 'CLASS8'),
('Class 9', 'CLASS9'),
('Class 10', 'CLASS10');

-- Insert Sample Sections
INSERT INTO sections (class_id, name) VALUES
(1, 'A'), (1, 'B'),
(2, 'A'), (2, 'B'),
(3, 'A'), (3, 'B'), (3, 'C'),
(4, 'A'), (4, 'B'), (4, 'C'),
(5, 'A'), (5, 'B'), (5, 'C'),
(6, 'A'), (6, 'B'),
(7, 'A'), (7, 'B'),
(8, 'A'), (8, 'B'),
(9, 'A'), (9, 'B'),
(10, 'A'), (10, 'B'),
(11, 'A'), (11, 'B'),
(12, 'A'), (12, 'B');

-- Insert Sample Subjects
INSERT INTO subjects (name, code) VALUES
('English', 'ENG'),
('Mathematics', 'MATH'),
('Science', 'SCI'),
('Social Studies', 'SST'),
('Urdu', 'URD'),
('Islamiat', 'ISL'),
('Physical Education', 'PE'),
('Computer Science', 'CS'),
('Drawing', 'ART'),
('Music', 'MUS');

-- Insert Sample Teachers
INSERT INTO teachers (teacher_id, full_name, cnic, qualification, contact, salary, joining_date, status) VALUES
('T001', 'Ahmed Ali', '12345-1234567-1', 'M.A. English', '03001234567', 50000.00, '2020-01-15', 'Active'),
('T002', 'Fatima Khan', '12345-1234568-1', 'B.S. Mathematics', '03009876543', 50000.00, '2020-02-01', 'Active'),
('T003', 'Muhammad Hassan', '12345-1234569-1', 'B.S. Physics', '03101234567', 55000.00, '2019-06-10', 'Active'),
('T004', 'Zara Ahmed', '12345-1234570-1', 'B.A. Urdu', '03219876543', 45000.00, '2021-01-20', 'Active'),
('T005', 'Bilal Raza', '12345-1234571-1', 'B.S. Chemistry', '03331234567', 55000.00, '2019-09-15', 'Active'),
('T006', 'Ayesha Malik', '12345-1234572-1', 'B.A. Social Studies', '03449876543', 48000.00, '2020-08-01', 'Active'),
('T007', 'Hassan Khan', '12345-1234573-1', 'M.A. Islamic Studies', '03001111111', 52000.00, '2018-03-10', 'Active'),
('T008', 'Saira Mirza', '12345-1234574-1', 'B.Ed. Computer Science', '03212222222', 50000.00, '2021-06-01', 'Active');

-- Insert Sample Students (Class 1, Section A)
INSERT INTO students (admission_no, full_name, father_name, b_form, class_id, section_id, roll_no, gender, dob, contact, address, admission_date, status) VALUES
('ADMS001', 'Ali Ahmed', 'Ahmed Khan', 'B123456', 3, 5, '1', 'Male', '2015-03-15', '03001234567', '123 Street, City', '2020-01-10', 'Active'),
('ADMS002', 'Fatima Khan', 'Khan Sahib', 'B123457', 3, 5, '2', 'Female', '2015-04-20', '03009876543', '456 Avenue, City', '2020-01-10', 'Active'),
('ADMS003', 'Hassan Raza', 'Raza Ali', 'B123458', 3, 5, '3', 'Male', '2015-05-10', '03101234567', '789 Road, City', '2020-01-10', 'Active'),
('ADMS004', 'Zainab Malik', 'Malik Hassan', 'B123459', 3, 5, '4', 'Female', '2015-06-15', '03219876543', '321 Lane, City', '2020-01-10', 'Active'),
('ADMS005', 'Muhammad Bilal', 'Bilal Khan', 'B123460', 3, 5, '5', 'Male', '2015-07-20', '03331234567', '654 Street, City', '2020-01-10', 'Active');

-- Insert Sample Students (Class 2, Section A)
INSERT INTO students (admission_no, full_name, father_name, b_form, class_id, section_id, roll_no, gender, dob, contact, address, admission_date, status) VALUES
('ADMS006', 'Ayesha Saeed', 'Saeed Ahmed', 'B123461', 4, 8, '1', 'Female', '2014-03-15', '03001111111', '100 Avenue, City', '2020-01-10', 'Active'),
('ADMS007', 'Hassan Mirza', 'Mirza Khan', 'B123462', 4, 8, '2', 'Male', '2014-04-20', '03212222222', '200 Road, City', '2020-01-10', 'Active'),
('ADMS008', 'Sara Khan', 'Khan Malik', 'B123463', 4, 8, '3', 'Female', '2014-05-10', '03333333333', '300 Lane, City', '2020-01-10', 'Active'),
('ADMS009', 'Ahmed Raza', 'Raza Hassan', 'B123464', 4, 8, '4', 'Male', '2014-06-15', '03444444444', '400 Street, City', '2020-01-10', 'Active'),
('ADMS010', 'Hira Ahmed', 'Ahmed Bilal', 'B123465', 4, 8, '5', 'Female', '2014-07-20', '03555555555', '500 Avenue, City', '2020-01-10', 'Active');

-- Insert Sample Fee Types
INSERT INTO fees (name, description, amount, fee_type) VALUES
('Monthly Fee', 'Regular monthly school fees', 5000.00, 'Monthly'),
('Admission Fee', 'One-time admission fee', 10000.00, 'Admission'),
('Exam Fee', 'Annual exam fee', 3000.00, 'Exam'),
('Transport Fee', 'Monthly transport fee', 2000.00, 'Transport'),
('Activity Fee', 'Co-curricular activities', 1000.00, 'Other');

-- Insert Sample Fee Payments
INSERT INTO fee_payments (student_id, fee_id, amount, paid_on, paid_by, payment_method, note) VALUES
(1, 1, 5000.00, DATE_SUB(NOW(), INTERVAL 2 MONTH), 1, 'Cash', 'Monthly fee'),
(1, 1, 5000.00, DATE_SUB(NOW(), INTERVAL 1 MONTH), 1, 'Cash', 'Monthly fee'),
(2, 1, 5000.00, DATE_SUB(NOW(), INTERVAL 2 MONTH), 1, 'Bank', 'Monthly fee'),
(2, 1, 5000.00, DATE_SUB(NOW(), INTERVAL 1 MONTH), 1, 'Bank', 'Monthly fee'),
(3, 1, 5000.00, NOW(), 1, 'Cash', 'Monthly fee'),
(4, 1, 5000.00, NOW(), 1, 'Cash', 'Monthly fee'),
(5, 1, 5000.00, DATE_SUB(NOW(), INTERVAL 1 MONTH), 1, 'Card', 'Monthly fee');

-- Insert Sample Expenses
INSERT INTO expenses (category, amount, expense_date, description) VALUES
('Stationary', 25000.00, DATE_SUB(NOW(), INTERVAL 5 DAY), 'Notebooks, pens, pencils'),
('Maintenance', 50000.00, DATE_SUB(NOW(), INTERVAL 10 DAY), 'Building repair and maintenance'),
('Utilities', 15000.00, DATE_SUB(NOW(), INTERVAL 15 DAY), 'Electricity and water bills'),
('Books', 35000.00, DATE_SUB(NOW(), INTERVAL 20 DAY), 'Textbooks and reference materials');

-- Insert Sample Salaries
INSERT INTO salaries (teacher_id, month_year, amount, paid_status, paid_on) VALUES
(1, DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH), '%Y-%m'), 50000.00, 'Paid', DATE_SUB(NOW(), INTERVAL 15 DAY)),
(2, DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH), '%Y-%m'), 50000.00, 'Paid', DATE_SUB(NOW(), INTERVAL 15 DAY)),
(3, DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH), '%Y-%m'), 55000.00, 'Paid', DATE_SUB(NOW(), INTERVAL 15 DAY)),
(1, DATE_FORMAT(NOW(), '%Y-%m'), 50000.00, 'Unpaid', NULL),
(2, DATE_FORMAT(NOW(), '%Y-%m'), 50000.00, 'Unpaid', NULL),
(3, DATE_FORMAT(NOW(), '%Y-%m'), 55000.00, 'Unpaid', NULL);

-- Insert Sample Exams
INSERT INTO exams (name, start_date, end_date) VALUES
('First Term Exam', DATE_SUB(CURDATE(), INTERVAL 60 DAY), DATE_SUB(CURDATE(), INTERVAL 50 DAY)),
('Mid-Year Exam', DATE_SUB(CURDATE(), INTERVAL 30 DAY), DATE_SUB(CURDATE(), INTERVAL 20 DAY)),
('Final Exam', DATE_ADD(CURDATE(), INTERVAL 30 DAY), DATE_ADD(CURDATE(), INTERVAL 40 DAY));

-- Insert Sample Results
INSERT INTO results (exam_id, student_id, subject_id, marks_obtained, total_marks, grade) VALUES
(1, 1, 1, 85, 100, 'A'),
(1, 1, 2, 78, 100, 'B'),
(1, 1, 3, 92, 100, 'A+'),
(1, 2, 1, 88, 100, 'A'),
(1, 2, 2, 82, 100, 'B'),
(1, 2, 3, 90, 100, 'A'),
(1, 3, 1, 75, 100, 'B'),
(1, 3, 2, 88, 100, 'A'),
(1, 3, 3, 80, 100, 'B'),
(2, 1, 1, 90, 100, 'A'),
(2, 1, 2, 85, 100, 'A'),
(2, 2, 1, 92, 100, 'A+'),
(2, 2, 2, 88, 100, 'A');

-- Insert Sample Timetable
INSERT INTO timetable (class_id, section_id, day, period, subject_id, teacher_id, start_time, end_time) VALUES
(3, 5, 'Monday', 1, 2, 2, '08:00:00', '08:45:00'),
(3, 5, 'Monday', 2, 1, 1, '09:00:00', '09:45:00'),
(3, 5, 'Monday', 3, 3, 3, '10:00:00', '10:45:00'),
(3, 5, 'Tuesday', 1, 4, 6, '08:00:00', '08:45:00'),
(3, 5, 'Tuesday', 2, 2, 2, '09:00:00', '09:45:00'),
(3, 5, 'Tuesday', 3, 5, 4, '10:00:00', '10:45:00'),
(3, 5, 'Wednesday', 1, 1, 1, '08:00:00', '08:45:00'),
(3, 5, 'Wednesday', 2, 3, 3, '09:00:00', '09:45:00'),
(3, 5, 'Wednesday', 3, 6, 7, '10:00:00', '10:45:00'),
(3, 5, 'Thursday', 1, 8, 8, '08:00:00', '08:45:00'),
(3, 5, 'Thursday', 2, 2, 2, '09:00:00', '09:45:00'),
(3, 5, 'Thursday', 3, 1, 1, '10:00:00', '10:45:00'),
(3, 5, 'Friday', 1, 9, 1, '08:00:00', '08:45:00'),
(3, 5, 'Friday', 2, 3, 3, '09:00:00', '09:45:00'),
(3, 5, 'Friday', 3, 10, 8, '10:00:00', '10:45:00');

-- Insert Sample Attendance
INSERT INTO student_attendance (student_id, class_id, section_id, attendance_date, status, recorded_by) VALUES
(1, 3, 5, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'Present', 1),
(1, 3, 5, DATE_SUB(CURDATE(), INTERVAL 4 DAY), 'Present', 1),
(1, 3, 5, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'Absent', 1),
(1, 3, 5, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Present', 1),
(1, 3, 5, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Late', 1),
(2, 3, 5, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'Present', 1),
(2, 3, 5, DATE_SUB(CURDATE(), INTERVAL 4 DAY), 'Present', 1),
(2, 3, 5, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'Present', 1),
(2, 3, 5, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Leave', 1),
(2, 3, 5, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Present', 1),
(3, 3, 5, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'Present', 1),
(3, 3, 5, DATE_SUB(CURDATE(), INTERVAL 4 DAY), 'Present', 1),
(3, 3, 5, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'Present', 1),
(3, 3, 5, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Present', 1),
(3, 3, 5, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Present', 1);

-- Insert Sample Biometric Logs
INSERT INTO biometric_logs (device_user_id, mapped_type, mapped_id, timestamp) VALUES
('001', 'student', 1, DATE_SUB(NOW(), INTERVAL 30 MINUTE)),
('002', 'student', 2, DATE_SUB(NOW(), INTERVAL 25 MINUTE)),
('003', 'student', 3, DATE_SUB(NOW(), INTERVAL 20 MINUTE)),
('101', 'teacher', 1, DATE_SUB(NOW(), INTERVAL 45 MINUTE)),
('102', 'teacher', 2, DATE_SUB(NOW(), INTERVAL 40 MINUTE));
