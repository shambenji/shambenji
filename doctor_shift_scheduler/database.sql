-- Doctors table
CREATE TABLE IF NOT EXISTS doctors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    specialization VARCHAR(100),
    availability JSON,
    preferences JSON,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    role ENUM('admin','doctor') DEFAULT 'doctor'
);

-- Shifts table
CREATE TABLE IF NOT EXISTS shifts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT NOT NULL,
    shift_date DATE NOT NULL,
    shift_type ENUM('morning','evening','night') NOT NULL,
    status ENUM('scheduled','completed','cancelled') DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
    INDEX (shift_date)
);

-- Shift swaps table
CREATE TABLE IF NOT EXISTS shift_swaps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    requester_id INT NOT NULL,
    current_shift_id INT NOT NULL,
    desired_shift_id INT NOT NULL,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    FOREIGN KEY (requester_id) REFERENCES doctors(id) ON DELETE CASCADE,
    FOREIGN KEY (current_shift_id) REFERENCES shifts(id) ON DELETE CASCADE,
    FOREIGN KEY (desired_shift_id) REFERENCES shifts(id) ON DELETE CASCADE
);