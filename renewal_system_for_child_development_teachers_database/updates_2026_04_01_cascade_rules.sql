-- Disable remaining delete cascades.
-- Safe to run on the current database to block cascading deletes.

ALTER TABLE teachers
  DROP FOREIGN KEY fk_teachers_user;

ALTER TABLE teachers
  ADD CONSTRAINT fk_teachers_user
  FOREIGN KEY (user_id) REFERENCES users(id)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;

ALTER TABLE renewal_applications
  DROP FOREIGN KEY fk_app_teacher;

ALTER TABLE renewal_applications
  ADD CONSTRAINT fk_app_teacher
  FOREIGN KEY (teacher_user_id) REFERENCES users(id)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;

ALTER TABLE renewal_documents
  DROP FOREIGN KEY fk_docs_app;

ALTER TABLE renewal_documents
  ADD CONSTRAINT fk_docs_app
  FOREIGN KEY (application_id) REFERENCES renewal_applications(id)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;

ALTER TABLE notifications
  DROP FOREIGN KEY fk_notif_user;

ALTER TABLE notifications
  ADD CONSTRAINT fk_notif_user
  FOREIGN KEY (user_id) REFERENCES users(id)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;