<?php
declare(strict_types=1);

use App\Lib\Auth;
use App\Lib\Csrf;

$uid = (int)(Auth::user()['id'] ?? 0);
$email = (string)(Auth::user()['email'] ?? '');
$hasPhoto = !empty($teacher['profile_photo_path']);
$sex = (string)($teacher['sex'] ?? '');
?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    
  </div>
  <a class="btn btn-secondary" href="<?= htmlspecialchars(url('/teacher')) ?>">Back</a>
</div>

<div class="card border-1 shadow-sm mx-auto" style="max-width: 750px; margin-top: -45px;">
  <div class="card-body">
    <div class="d-flex gap-3 align-items-center mb-3">
      <div class="avatar-lg rounded-circle bg-secondary-subtle border overflow-hidden d-flex align-items-center justify-content-center">
        <?php if ($hasPhoto): ?>
          <img src="<?= htmlspecialchars(url('/files/profile-photo')) ?>?user_id=<?= (int)$uid ?>&v=<?= time() ?>" alt="Profile photo" class="w-100 h-100" style="object-fit: cover;">
        <?php else: ?>
          <span class="text-secondary fw-bold">No Photo</span>
        <?php endif; ?>
      </div>
      <div>
        <div class="fw-semibold">Profile Picture</div>
        <div class="text-muted small">JPG/PNG up to 2MB.</div>
      </div>
    </div>

    <form method="post" action="<?= htmlspecialchars(url('/teacher/profile')) ?>" enctype="multipart/form-data" class="row g-3">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">

      <div class="col-6">
        <label class="form-label">Upload Profile Picture</label>
        <input class="form-control" type="file" name="profile_photo" accept=".jpg,.jpeg,.png">
      </div>

      <div class="col-6">
        <label class="form-label">Full Name</label>
        <input class="form-control" value="<?= htmlspecialchars((string)($teacher['full_name'] ?? '')) ?>" readonly disabled>
      </div>

      <div class="col-md-6">
        <label class="form-label">Email</label>
        <input class="form-control" value="<?= htmlspecialchars($email) ?>" readonly disabled>
      </div>

      <div class="col-md-6">
        <label class="form-label">ID</label>
        <input class="form-control" value="<?= htmlspecialchars((string)($teacher['teacher_uid'] ?? '')) ?>" readonly disabled>
      </div>

      <div class="col-md-6">
        <label class="form-label">Assigned Child Development Center</label>
        <input class="form-control" value="<?= htmlspecialchars((string)($teacher['assigned_center'] ?? '')) ?>" readonly disabled>
        <div class="text-muted small mt-1"></div>
      </div>

      <div class="col-md-6">
        <label class="form-label">Place of Birth</label>
        <input class="form-control" name="place_of_birth" maxlength="120" value="<?= htmlspecialchars((string)($teacher['place_of_birth'] ?? '')) ?>">
      </div>

      <div class="col-md-6">
        <label class="form-label">Bachelor's Degree</label>
        <input class="form-control" name="bachelor_degree" maxlength="120" value="<?= htmlspecialchars((string)($teacher['bachelor_degree'] ?? '')) ?>">
      </div>

      <div class="col-md-6">
        <label class="form-label">Religion</label>
        <input class="form-control" name="religion" maxlength="80" value="<?= htmlspecialchars((string)($teacher['religion'] ?? '')) ?>">
      </div>

      <div class="col-md-3">
        <label class="form-label">Nationality</label>
        <input class="form-control" name="nationality" maxlength="60" value="<?= htmlspecialchars((string)($teacher['nationality'] ?? '')) ?>">
      </div>

      <div class="col-md-3">
        <label class="form-label">Civil Status</label>
        <input class="form-control" name="civil_status" maxlength="30" value="<?= htmlspecialchars((string)($teacher['civil_status'] ?? '')) ?>">
      </div>

      <div class="col-md-2">
        <label class="form-label">Blood Type</label>
        <input class="form-control" name="blood_type" maxlength="10" value="<?= htmlspecialchars((string)($teacher['blood_type'] ?? '')) ?>">
      </div>

      <div class="col-md-4">
        <label class="form-label">Contact Number</label>
        <input class="form-control" name="contact_no" inputmode="numeric" pattern="\d+" maxlength="40" value="<?= htmlspecialchars((string)($teacher['contact_no'] ?? '')) ?>">
      </div>

      <div class="col-md-4">
        <label class="form-label">Date of Birth</label>
        <input class="form-control" type="date" name="date_of_birth" value="<?= htmlspecialchars((string)($teacher['date_of_birth'] ?? '')) ?>">
      </div>

      <div class="col-md-2">
        <label class="form-label">Age</label>
        <input class="form-control" type="number" name="age" min="0" max="120" value="<?= htmlspecialchars((string)($teacher['age'] ?? '')) ?>">
      </div>

      <div class="col-md-2">
        <label class="form-label">Sex</label>
        <select class="form-select" name="sex">
          <option value="" <?= $sex === '' ? 'selected' : '' ?>>-</option>
          <option value="Male" <?= $sex === 'Male' ? 'selected' : '' ?>>Male</option>
          <option value="Female" <?= $sex === 'Female' ? 'selected' : '' ?>>Female</option>
        </select>
      </div>

      <div class="col-md-2">
        <label class="form-label">Weight</label>
        <input class="form-control" name="weight_kg" inputmode="decimal" pattern="\d{1,3}(\.\d{1,2})?" maxlength="8" value="<?= htmlspecialchars((string)($teacher['weight_kg'] ?? '')) ?>" placeholder="kg">
      </div>

      <div class="col-md-2">
        <label class="form-label">Height</label>
        <input class="form-control" name="height_ft" inputmode="text" pattern="\d{1,2}'\d{1,2}" maxlength="5" value="<?= htmlspecialchars((string)($teacher['height_ft'] ?? '')) ?>" placeholder="ft">
      </div>

      <div class="col-md-12">
        <label class="form-label">Address</label>
        <input class="form-control" name="address" maxlength="180" value="<?= htmlspecialchars((string)($teacher['address'] ?? '')) ?>">
      </div>

      <div class="col-12"><hr class="my-2"></div>

      <div class="col-12">
        <div class="fw-semibold mb-1">Person to Contact in Case of Emergency</div>
      </div>

      <div class="col-md-6">
        <label class="form-label">Full Name</label>
        <input class="form-control" name="emergency_contact_name" maxlength="120" value="<?= htmlspecialchars((string)($teacher['emergency_contact_name'] ?? '')) ?>">
      </div>

      <div class="col-md-6">
        <label class="form-label">Contact Number</label>
        <input class="form-control" name="emergency_contact_no" inputmode="numeric" pattern="\d+" maxlength="40" value="<?= htmlspecialchars((string)($teacher['emergency_contact_no'] ?? '')) ?>">
      </div>

      <div class="col-md-12">
        <label class="form-label">Address</label>
        <input class="form-control" name="emergency_contact_address" maxlength="180" value="<?= htmlspecialchars((string)($teacher['emergency_contact_address'] ?? '')) ?>">
      </div>

      <div class="col-12">
        <button class="btn btn-primary" type="submit">Save Changes</button>
      </div>
    </form>
  </div>
</div>