-- Run these ALTER/CREATE statements in phpMyAdmin to enable the new features (Teacher ID and extra personal info).

-- Unique teacher ID + core registration fields
ALTER TABLE teachers
  ADD COLUMN teacher_uid VARCHAR(50) NULL AFTER user_id,
  ADD COLUMN date_of_birth DATE NULL AFTER teacher_uid;

-- Extra personal info (editable by focal; teacher can edit most except name/email/password)
ALTER TABLE teachers
  ADD COLUMN place_of_birth VARCHAR(120) NULL,
  ADD COLUMN bachelor_degree VARCHAR(120) NULL,
  ADD COLUMN religion VARCHAR(60) NULL,
  ADD COLUMN blood_type VARCHAR(10) NULL,
  ADD COLUMN nationality VARCHAR(60) NULL,
  ADD COLUMN civil_status VARCHAR(30) NULL,
  ADD COLUMN weight_kg VARCHAR(10) NULL,
  ADD COLUMN height_ft VARCHAR(10) NULL,
  ADD COLUMN emergency_contact_name VARCHAR(120) NULL,
  ADD COLUMN emergency_contact_no VARCHAR(40) NULL,
  ADD COLUMN emergency_contact_address VARCHAR(180) NULL;

-- Make teacher_uid unique (run after you fill unique IDs for existing teachers)
CREATE UNIQUE INDEX uq_teachers_teacher_uid ON teachers(teacher_uid);

-- OPTIONAL: Clear existing renewal history (irreversible)
-- DELETE FROM renewal_documents;
-- DELETE FROM renewal_applications;