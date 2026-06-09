-- ECCD Teacher Renewal System schema (MySQL/MariaDB)
-- Import this in phpMyAdmin, then run /setup.php once to create accounts.
CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  email VARCHAR(120) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('teacher','focal') NOT NULL,
  last_login_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS teachers (
  user_id INT UNSIGNED NOT NULL,
  full_name VARCHAR(120) NULL,
  contact_no VARCHAR(40) NULL,
  address VARCHAR(180) NULL,
  assigned_center VARCHAR(120) NULL,
  expiration_date DATE NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (user_id),
  CONSTRAINT fk_teachers_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS focal (
  user_id INT UNSIGNED NOT NULL,
  full_name VARCHAR(120) NULL,
  contact_no VARCHAR(40) NULL,
  address VARCHAR(180) NULL,
  date_of_birth DATE NULL,
  place_of_birth VARCHAR(120) NULL,
  age INT NULL,
  sex ENUM('Male','Female') NULL,
  bachelor_degree VARCHAR(120) NULL,
  religion VARCHAR(80) NULL,
  blood_type VARCHAR(10) NULL,
  nationality VARCHAR(60) NULL,
  civil_status VARCHAR(30) NULL,
  weight_kg DECIMAL(5,2) NULL,
  height_ft VARCHAR(5) NULL,
  emergency_contact_name VARCHAR(120) NULL,
  emergency_contact_no VARCHAR(40) NULL,
  emergency_contact_address VARCHAR(180) NULL,
  profile_photo_path VARCHAR(500) NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (user_id),
  CONSTRAINT fk_focal_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS renewal_schedules (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(80) NOT NULL,
  open_from DATE NOT NULL,
  due_until DATE NOT NULL,
  created_by INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_sched_window (open_from, due_until),
  CONSTRAINT fk_sched_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS renewal_applications (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  teacher_user_id INT UNSIGNED NOT NULL,
  schedule_id INT UNSIGNED NOT NULL,
  status ENUM('draft','submitted','approved','rejected') NOT NULL DEFAULT 'draft',
  submitted_at DATETIME NULL,
  reviewed_by INT UNSIGNED NULL,
  reviewed_at DATETIME NULL,
  remarks TEXT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_app_teacher (teacher_user_id, status),
  KEY idx_app_status (status, submitted_at),
  CONSTRAINT fk_app_teacher FOREIGN KEY (teacher_user_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_app_schedule FOREIGN KEY (schedule_id) REFERENCES renewal_schedules(id) ON DELETE RESTRICT,
  CONSTRAINT fk_app_reviewer FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS renewal_documents (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  application_id INT UNSIGNED NOT NULL,
  doc_type VARCHAR(40) NOT NULL,
  original_name VARCHAR(255) NOT NULL,
  stored_path VARCHAR(500) NOT NULL,
  mime VARCHAR(120) NULL,
  size_bytes INT UNSIGNED NOT NULL,
  uploaded_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_docs_app (application_id),
  CONSTRAINT fk_docs_app FOREIGN KEY (application_id) REFERENCES renewal_applications(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS notifications (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  type VARCHAR(40) NOT NULL,
  title VARCHAR(120) NOT NULL,
  body TEXT NOT NULL,
  link_url VARCHAR(200) NULL,
  meta_json TEXT NULL,
  created_at DATETIME NOT NULL,
  read_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_notif_user (user_id, read_at, created_at),
  CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
