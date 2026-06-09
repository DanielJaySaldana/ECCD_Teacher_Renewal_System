<?php
/** @var mixed $teachers */
declare(strict_types=1);

use App\Lib\Csrf;

$teachers = is_array($teachers ?? null) ? $teachers : [];
?>
<div class="d-flex align-items-center justify-content-between mb-3 gap-2">
  <button class="btn btn-primary fw-bold" type="button" data-bs-toggle="modal" data-bs-target="#registerTeacherModal">Register a Teacher</button>
  <a class="btn btn-secondary" href="<?= htmlspecialchars(url('/focal')) ?>">Back</a>
</div>

<div class="modal fade" id="registerTeacherModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="<?= htmlspecialchars(url('/focal/teachers/create')) ?>">
        <div class="modal-header">
          <h5 class="modal-title">Register a Teacher</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">

          <div class="row g-3">
            <div class="col-12 col-md-6">
              <label class="form-label">Full Name</label>
              <input class="form-control" name="full_name" maxlength="120" required>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label">Teacher ID (Unique)</label>
              <input class="form-control" name="teacher_uid" maxlength="50" required>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label">Email</label>
              <input class="form-control" type="email" name="email" required>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label">Date of Birth</label>
              <input class="form-control" type="date" name="date_of_birth" required>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label">Age</label>
              <input class="form-control" type="number" name="age" min="18" max="80" required>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label">Sex</label>
              <select class="form-select" name="sex" required>
                <option value="" selected disabled>Select</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Address</label>
              <input class="form-control" name="address" maxlength="180" required>
            </div>
            <div class="col-12">
              <label class="form-label">Assigned Child Development Center</label>
              <input class="form-control" name="assigned_center" maxlength="120" required>
            </div>
            <div class="col-12">
              <label class="form-label">Password</label>
              <div class="input-group">
                <input class="form-control" type="password" name="password" required minlength="12" pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{12,}" id="regTeacherPassword" title="Minimum 12 characters with uppercase, lowercase, number, and special character.">
                <button class="btn btn-outline-secondary" type="button" data-toggle-password="#regTeacherPassword">Show</button>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-primary" type="submit">Create Account</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="card border-0 shadow-sm" style="min-height: 500px;" >
  <div class="card-body">
    <div class="table-responsive">
      <table class="table align-middle mb-0" data-searchable>
        <thead>
        <tr>
          <th>ID</th>
          <th> <span style="margin-left: 42px; display: inline-block;">Name</span></th>
          <th> <span style="margin-left: 70px; display: inline-block;">Email</span></th>
          <th> <span style="margin-left: -32px; display: inline-block;">Assigned Child Development Center</span></th>
          <th>Expiration</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($teachers as $row): ?>
          <?php
            $t = is_array($row) ? $row : [];
            $teacherUid = (string)($t['teacher_uid'] ?? '-');
            $teacherEmail = (string)($t['email'] ?? '');
            $display = (string)($t['full_name'] ?? $teacherEmail ?: '-');
          ?>
          <tr>
            <td class="fw-semibold"><?= htmlspecialchars($teacherUid !== '' ? $teacherUid : '-') ?></td>
            <td>
              <div class="fw-semibold">
                <a href="<?= htmlspecialchars(url('/focal/teachers/view')) ?>?user_id=<?= (int)($t['user_id'] ?? 0) ?>" class="text-decoration-none">
                  <?= htmlspecialchars($display) ?>
                </a>
              </div>
            </td>
            <td class="text-muted small"><?= htmlspecialchars($teacherEmail !== '' ? $teacherEmail : '-') ?></td>
            <td><?= htmlspecialchars((string)($t['assigned_center'] ?? '-')) ?></td>
            <td style="min-width: 190px;">
              <?php if ((int)($t['has_approved_renewal'] ?? 0) === 1): ?>
                <form method="post" action="<?= htmlspecialchars(url('/focal/teachers/update-expiration')) ?>" class="d-flex align-items-center gap-2">
                  <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">
                  <input type="hidden" name="user_id" value="<?= (int)($t['user_id'] ?? 0) ?>">
                  <input class="form-control form-control-sm" type="date" name="expiration_date" value="<?= htmlspecialchars((string)($t['expiration_date'] ?? '')) ?>" required>
                  <button class="btn btn-sm btn-outline-primary" type="submit">Save</button>
                </form>
              <?php else: ?>
                <div class="small"><?= htmlspecialchars((string)($t['expiration_date'] ?? 'Accessible once the renewal application is approved')) ?></div>
                <?php if (($t['expiring_days'] ?? null) !== null): ?>
                  <div class="text-muted small"><?= (int)$t['expiring_days'] ?> day(s)</div>
                <?php endif; ?>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
