-- Adds "terminated" status for focal termination of approved renewals.

ALTER TABLE renewal_applications
  MODIFY status ENUM('draft','submitted','approved','rejected','cancelled','terminated') NOT NULL DEFAULT 'draft';