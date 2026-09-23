CREATE DATABASE IF NOT EXISTS gym_track;
USE gym_track;

CREATE TABLE users (
	user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','staff','member') NOT NULL
);

CREATE TABLE membership_plans (
	plan_id INT AUTO_INCREMENT PRIMARY KEY,
    plan_name VARCHAR(100) NOT NULL,
    price DEC(5,2) NOT NULL,
    duration_days INT NOT NULL
);

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

CREATE TABLE checkins (
	checkin_id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    checkin_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_checkin_member FOREIGN KEY (member_id) REFERENCES members(member_id)
);
