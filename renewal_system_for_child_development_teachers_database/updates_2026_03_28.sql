-- Run these ALTER statements in phpMyAdmin (SQL tab) to update your existing database.

-- 1) Teacher extra fields + profile photo
ALTER TABLE teachers
  ADD COLUMN age TINYINT UNSIGNED NULL AFTER full_name,
  ADD COLUMN sex ENUM('Male','Female') NULL AFTER age,
  ADD COLUMN profile_photo_path VARCHAR(500) NULL AFTER assigned_center;

-- 2) Allow teachers to cancel submitted applications
ALTER TABLE renewal_applications
  ADD COLUMN cancelled_at DATETIME NULL AFTER reviewed_at;


ALTER TABLE renewal_applications
  MODIFY status ENUM('draft','submitted','approved','rejected','cancelled') NOT NULL DEFAULT 'draft';

