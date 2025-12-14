CREATE DATABASE IF NOT EXISTS lt_web_no1
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE lt_web_no1;

-- =========================
-- admins
-- =========================
CREATE TABLE admins (
    id INT(10) AUTO_INCREMENT PRIMARY KEY,
    login_id VARCHAR(20) UNIQUE NOT NULL,
    password VARCHAR(64) NOT NULL,
    actived_flag TINYINT(1) DEFAULT 1 COMMENT '0: not active, 1: active',
    reset_password_token VARCHAR(100),
    updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- classrooms
-- =========================
CREATE TABLE classrooms (
    id INT(10) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(250) NOT NULL,
    avatar VARCHAR(250),
    description TEXT,
    building CHAR(10),
    updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- teachers
-- =========================
CREATE TABLE teachers (
    id INT(10) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(250) NOT NULL,
    avatar VARCHAR(250),
    description TEXT,
    specialized CHAR(10),
    degree CHAR(10),
    updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- devices
-- =========================
CREATE TABLE devices (
    id INT(10) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(250) NOT NULL,
    avatar VARCHAR(250),
    description TEXT,
    updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- transactions
-- =========================
CREATE TABLE transactions (
    id INT(10) AUTO_INCREMENT PRIMARY KEY,
    device_id INT(10),
    teacher_id INT(10),
    classroom_id INT(10),
    comment TEXT,
    start_transaction_plan DATETIME,
    end_transaction_plan DATETIME,
    returned_date DATETIME,
    updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (device_id) REFERENCES devices(id),
    FOREIGN KEY (teacher_id) REFERENCES teachers(id),
    FOREIGN KEY (classroom_id) REFERENCES classrooms(id)
);

INSERT INTO admins (login_id, password, actived_flag)
VALUES (
    'admin001',
    SHA2('123456', 256),
    1
);
