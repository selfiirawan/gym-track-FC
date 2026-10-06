CREATE DATABASE IF NOT EXISTS gym_track;
USE gym_track;

CREATE TABLE users (
	user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','staff','member') NOT NULL
);

select * from users;

CREATE TABLE staff_profiles (
    staff_profile_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    contact VARCHAR(20),
    job_role VARCHAR(100),
    status ENUM('available', 'on_leave') NOT NULL DEFAULT 'available',
    leave_start DATE NULL,
    leave_end DATE NULL,
    CONSTRAINT fk_staff_user FOREIGN KEY (user_id) REFERENCES users(user_id)
);

select * from staff_profiles;

CREATE TABLE membership_plans (
	plan_id INT AUTO_INCREMENT PRIMARY KEY,
    plan_name VARCHAR(100) NOT NULL,
    price DEC(5,2) NOT NULL,
    duration_days INT NOT NULL
);

select * from membership_plans;

alter table membership_plans add features text;

UPDATE membership_plans SET features = 'Gym access (6am-10pm), Locker room, 1 group class/month, Basic equipments' WHERE plan_name = 'Free Trial';

UPDATE membership_plans SET features = 'Gym access (24/7), Locker room, 4 group classes/month, Basic equipments' WHERE plan_name = 'Monthly Basic';

UPDATE membership_plans SET features = 'Gym access (24/7), Premium locker, Unlimited group classes, All equipments, Priority class booking' WHERE plan_name = 'Monthly Premium';

CREATE TABLE members (
	member_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    contact VARCHAR(20) NOT NULL,
    join_date DATE NOT NULL,
    plan_id INT NOT NULL, 
    expiry_date DATE NOT NULL,
    registered_by INT NOT NULL,
    CONSTRAINT fk_members_plan FOREIGN KEY (plan_id) REFERENCES membership_plans(plan_id),
    CONSTRAINT fk_members_user FOREIGN KEY (registered_by) REFERENCES users(user_id)
);

select * from members;

ALTER TABLE members ADD CONSTRAINT unique_member_email UNIQUE (email);

ALTER TABLE members ADD COLUMN email VARCHAR(100) AFTER contact;

CREATE TABLE classes (
	class_id INT AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(100) NOT NULL UNIQUE,
    instructor VARCHAR(255) NOT NULL, 
    schedule_time DATETIME NOT NULL, 
    capacity INT NOT NULL
);

CREATE TABLE class_bookings (
	booking_id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    class_id INT NOT NULL,
    booked_at DATETIME DEFAULT CURRENT_TIMESTAMP, 
    status ENUM('booked', 'cancelled') NOT NULL, 
    CONSTRAINT fk_booking_member FOREIGN KEY (member_id) REFERENCES members(member_id),
    CONSTRAINT fk_booking_class FOREIGN KEY (class_id) REFERENCES classes(class_id)
);

CREATE TABLE payments (
	payment_id INT AUTO_INCREMENT PRIMARY KEY, 
    member_id INT NOT NULL,
    amount DEC(5,2) NOT NULL,
    payment_date DATE NOT NULL,
    new_expiry_date DATE NOT NULL,
    CONSTRAINT fk_payment_member FOREIGN KEY (member_id) REFERENCES members(member_id)
);

select * from payments;

ALTER TABLE payments ADD COLUMN method VARCHAR(50) NULL;
ALTER TABLE payments ADD COLUMN status ENUM('paid', 'pending', 'failed') NOT NULL DEFAULT 'paid';

CREATE TABLE checkins (
	checkin_id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    checkin_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_checkin_member FOREIGN KEY (member_id) REFERENCES members(member_id)
);

INSERT INTO users (name, email, password_hash, role) VALUES
('Admin 1', 'admin@mail.com', '$2y$12$rMr2ZO8wAFuIKqUscAIMUuwBtzhj1zUnQFV.8iLxhGikD93I5HSOm', 'admin');

INSERT INTO users (name, email, password_hash, role) VALUES
('Staff 1', 'staff@mail.com', '$2y$12$rMr2ZO8wAFuIKqUscAIMUuwBtzhj1zUnQFV.8iLxhGikD93I5HSOm', 'staff'),
('Member 1', 'member@mail.com', '$2y$12$rMr2ZO8wAFuIKqUscAIMUuwBtzhj1zUnQFV.8iLxhGikD93I5HSOm', 'member');


SELECT * FROM users;

SELECT * FROM membership_plans;
SELECT user_id, name, role FROM users WHERE role IN ('admin', 'staff');

SELECT m.member_id, m.name, m.join_date, p.plan_name, m.expiry_date 
FROM members m
JOIN membership_plans p ON m.plan_id = p.plan_id
ORDER BY m.join_date DESC
LIMIT 5;

INSERT INTO membership_plans (plan_name, price, duration_days) VALUES
('Free Trial', 0.00, 14),
('Monthly Basic', 50.00, 30),
('Monthly Premium', 90.00, 30);

INSERT INTO members (name, contact, join_date, plan_id, expiry_date, registered_by) VALUES
('Marcus Rodriguez', '012-3456789', '2026-08-15', 1, '2026-11-15', 1),
('Sophia Chen', '012-9876543', '2026-09-01', 2, '2026-12-01', 1),
('Devon Okafor', '012-1122334', '2025-11-22', 1, '2025-12-22', 2),
('Lena Kovács', '012-5566778', '2026-09-20', 3, '2026-10-20', 2);

SELECT member_id, name FROM members;
SELECT * FROM payments;

INSERT INTO payments (member_id, amount, payment_date, new_expiry_date)
VALUES
(1, 0.00, '2026-08-15', '2026-11-15'),   -- Marcus, Free Trial
(2, 50.00, '2026-09-01', '2026-12-01'),  -- Sophia, Monthly Basic
(3, 0.00, '2025-11-22', '2025-12-22'),   -- Devon, Free Trial
(4, 90.00, '2026-09-20', '2026-10-20');  -- Lena, Monthly Premium

INSERT INTO members (name, contact, join_date, plan_id, expiry_date, registered_by) VALUES
('Wei Ling Tan', '012-7788990', '2026-06-30', 2, '2026-10-03', 1);
