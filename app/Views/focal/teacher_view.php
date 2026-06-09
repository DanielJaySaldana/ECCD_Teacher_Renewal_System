<?php
/** @var mixed $teacherUser */
/** @var mixed $teacher */
/** @var mixed $approvedDocuments */
declare(strict_types=1);

use App\Lib\Csrf;

$teacherUser = is_array($teacherUser ?? null) ? $teacherUser : [];
$teacher = is_array($teacher ?? null) ? $teacher : [];
$approvedDocuments = is_array($approvedDocuments ?? null) ? $approvedDocuments : [];
$userId = (int)($teacherUser['id'] ?? 0);
$hasPhoto = !empty($teacher['profile_photo_path']);
?>
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
  <div>
    <h1 class="h4 fw-bold mb-1">Teacher Details</h1>
  </div>
  <a class="btn btn-secondary" href="<?= htmlspecialchars(url('/focal/teachers')) ?>">Back</a>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-4">
    <div class="row g-4 align-items-stretch">
      <div class="col-12 col-lg-4 d-flex">
        <div class="d-flex flex-column w-100 h-100">
          <div class="d-flex gap-3 align-items-center">
            <div class="avatar-lg rounded-circle bg-secondary-subtle border overflow-hidden d-flex align-items-center justify-content-center flex-shrink-0">
              <?php if ($hasPhoto): ?>
                <img src="<?= htmlspecialchars(url('/files/profile-photo')) ?>?user_id=<?= $userId ?>&v=<?= time() ?>" alt="Profile photo" class="w-100 h-100" style="object-fit: cover;">
              <?php else: ?>
                <span class="text-secondary fw-bold">No Photo</span>
              <?php endif; ?>
            </div>
            <div>
              <div class="fw-semibold"><?= htmlspecialchars((string)($teacher['full_name'] ?? '')) ?></div>
              <div class="text-muted small">Teacher ID: <?= htmlspecialchars((string)($teacher['teacher_uid'] ?? '')) ?></div>
              <div class="text-muted small"><?= htmlspecialchars((string)($teacher['assigned_center'] ?? '-')) ?></div>
            </div>
          </div>

          <hr class="my-4">

          <h2 class="h6 fw-bold">Password</h2>
          <form method="post" action="<?= htmlspecialchars(url('/focal/teachers/update-password')) ?>" class="vstack gap-2">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">
            <input type="hidden" name="user_id" value="<?= $userId ?>">
            <div class="input-group">
              <input class="form-control" type="password" name="password" minlength="12" pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{12,}" required id="teacherNewPassword" title="Minimum 12 characters with uppercase, lowercase, number, and special character.">
              <button class="btn btn-outline-secondary" type="button" data-toggle-password="#teacherNewPassword">Show</button>
            </div>
            <button class="btn btn-primary" type="submit">Update Password</button>
          </form>

          <form method="post" action="<?= htmlspecialchars(url('/focal/teachers/delete-account')) ?>" class="teacher-delete-account-form" onsubmit="return confirm('Are you sure you want to delete this teacher account? This action cannot be undone.');">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">
            <input type="hidden" name="user_id" value="<?= $userId ?>">
            <button class="btn btn-danger w-100" type="submit">Delete Account</button>
          </form>

          <div class="mt-4 d-flex flex-column flex-grow-1 teacher-approved-documents">
            <h2 class="h6 fw-bold mb-2">Current Approved Renewal Documents</h2>
            <div class="border rounded p-2 bg-light-subtle teacher-approved-documents-box">
              <?php if ($approvedDocuments !== []): ?>
                <div class="vstack gap-2">
                  <?php foreach ($approvedDocuments as $row): ?>
                    <?php $doc = is_array($row) ? $row : []; ?>
                    <div class="border rounded bg-white p-2">
                      <div class="small text-muted text-capitalize mb-1"><?= htmlspecialchars(str_replace('_', ' ', (string)($doc['doc_type'] ?? 'Document'))) ?></div>
                      <div class="small fw-semibold text-break mb-2"><?= htmlspecialchars((string)($doc['original_name'] ?? 'Unnamed document')) ?></div>
                      <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                        <div class="text-muted small">Uploaded: <?= htmlspecialchars((string)($doc['uploaded_at'] ?? '-')) ?></div>
                        <div class="d-flex gap-2">
                          <a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars(url('/files/view')) ?>?id=<?= (int)($doc['id'] ?? 0) ?>" target="_blank" rel="noopener">Preview</a>
                          <a class="btn btn-sm btn-outline-secondary" href="<?= htmlspecialchars(url('/files/download')) ?>?id=<?= (int)($doc['id'] ?? 0) ?>">Download</a>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <div class="text-muted small">No approved renewal documents found.</div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-8 d-flex">
        <div class="w-100 d-flex flex-column">
          <h2 class="h6 fw-bold mb-3">Personal Information</h2>
          <form method="post" action="<?= htmlspecialchars(url('/focal/teachers/update')) ?>" class="row g-3 w-100">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">
            <input type="hidden" name="user_id" value="<?= $userId ?>">

            <div class="col-md-6">
              <label class="form-label">Full Name</label>
              <input class="form-control" name="full_name" maxlength="120" required value="<?= htmlspecialchars((string)($teacher['full_name'] ?? '')) ?>">
            </div>

            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input class="form-control" type="email" name="email" maxlength="120" required value="<?= htmlspecialchars((string)($teacherUser['email'] ?? '')) ?>">
            </div>

            <div class="col-md-6">
              <label class="form-label">Teacher ID</label>
              <input class="form-control" name="teacher_uid" maxlength="50" required value="<?= htmlspecialchars((string)($teacher['teacher_uid'] ?? '')) ?>">
            </div>

            <div class="col-md-6">
              <label class="form-label">Assigned Child Development Center</label>
              <input class="form-control" name="assigned_center" maxlength="120" value="<?= htmlspecialchars((string)($teacher['assigned_center'] ?? '')) ?>">
            </div>

            <div class="col-md-6">
              <label class="form-label">Place of Birth</label>
              <input class="form-control" name="place_of_birth" maxlength="120" value="<?= htmlspecialchars((string)($teacher['place_of_birth'] ?? '')) ?>">
            </div>

            <div class="col-md-6">
              <label class="form-label">Contact No.</label>
              <input class="form-control" name="contact_no" inputmode="numeric" pattern="\d*" value="<?= htmlspecialchars((string)($teacher['contact_no'] ?? '')) ?>">
            </div>

            <div class="col-md-6">
              <label class="form-label">Bachelor's Degree</label>
              <input class="form-control" name="bachelor_degree" maxlength="120" value="<?= htmlspecialchars((string)($teacher['bachelor_degree'] ?? '')) ?>">
            </div>

            <div class="col-md-6">
              <label class="form-label">Civil Status</label>
              <input class="form-control" name="civil_status" maxlength="30" value="<?= htmlspecialchars((string)($teacher['civil_status'] ?? '')) ?>">
            </div>

            <div class="col-md-6">
              <label class="form-label">Religion</label>
              <input class="form-control" name="religion" maxlength="60" value="<?= htmlspecialchars((string)($teacher['religion'] ?? '')) ?>">
            </div>

            <div class="col-md-3">
              <label class="form-label">Blood Type</label>
              <input class="form-control" name="blood_type" maxlength="10" value="<?= htmlspecialchars((string)($teacher['blood_type'] ?? '')) ?>">
            </div>

            <div class="col-md-3">
              <label class="form-label">Nationality</label>
              <input class="form-control" name="nationality" maxlength="60" value="<?= htmlspecialchars((string)($teacher['nationality'] ?? '')) ?>">
            </div>

            <div class="col-md-4">
              <label class="form-label">Date of Birth</label>
              <input class="form-control" type="date" name="date_of_birth" data-age-source="#teacherAge" value="<?= htmlspecialchars((string)($teacher['date_of_birth'] ?? '')) ?>">
            </div>
            <div class="col-md-2">
              <label class="form-label">Age</label>
              <input class="form-control" type="number" name="age" id="teacherAge" min="0" max="120" readonly value="<?= htmlspecialchars((string)($teacher['age'] ?? '')) ?>">
            </div>

            <div class="col-md-2">
              <label class="form-label">Sex</label>
              <select class="form-select" name="sex">
                <option value="" <?= empty($teacher['sex']) ? 'selected' : '' ?>>-</option>
                <option value="Male" <?= (string)($teacher['sex'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                <option value="Female" <?= (string)($teacher['sex'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
              </select>
            </div>

            <div class="col-md-2">
              <label class="form-label">Weight</label>
              <input class="form-control" name="weight_kg" maxlength="10" value="<?= htmlspecialchars((string)($teacher['weight_kg'] ?? '')) ?>" placeholder="kg">
            </div>

            <div class="col-md-2">
              <label class="form-label">Height</label>
              <input class="form-control" name="height_ft" maxlength="10" value="<?= htmlspecialchars((string)($teacher['height_ft'] ?? '')) ?>" placeholder="ft">
            </div>

            <div class="col-12">
              <label class="form-label">Address</label>
              <input class="form-control" name="address" maxlength="180" value="<?= htmlspecialchars((string)($teacher['address'] ?? '')) ?>">
            </div>

            <div class="col-12">
              <h3 class="h6 fw-bold mt-2">Emergency Contact</h3>
            </div>
            <div class="col-md-4">
              <label class="form-label">Full Name</label>
              <input class="form-control" name="emergency_contact_name" maxlength="120" value="<?= htmlspecialchars((string)($teacher['emergency_contact_name'] ?? '')) ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Contact No.</label>
              <input class="form-control" name="emergency_contact_no" inputmode="numeric" pattern="\d*" value="<?= htmlspecialchars((string)($teacher['emergency_contact_no'] ?? '')) ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Address</label>
              <input class="form-control" name="emergency_contact_address" maxlength="180" value="<?= htmlspecialchars((string)($teacher['emergency_contact_address'] ?? '')) ?>">
            </div>

            <div class="col-12">
              <button class="btn btn-primary" type="submit">Save Changes</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
