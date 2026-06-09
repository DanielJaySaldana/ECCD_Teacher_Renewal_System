<?php
declare(strict_types=1);

use App\Lib\Csrf;

/** @var mixed $app */
/** @var mixed $docs */
/** @var mixed $missing */
/** @var mixed $present */
/** @var mixed $schedules */
/** @var mixed $teacher */
$app = is_array($app ?? null) ? $app : null;
$docs = is_array($docs ?? null) ? $docs : [];
$missing = is_array($missing ?? null) ? $missing : [];
$present = is_array($present ?? null) ? $present : [];
$schedules = is_array($schedules ?? null) ? $schedules : [];
$teacher = is_array($teacher ?? null) ? $teacher : [];

$appId = $app !== null ? (int)($app['id'] ?? 0) : 0;
$status = $app !== null ? (string)($app['status'] ?? 'draft') : 'draft';
$hasValidId = (!empty($present['valid_id_national_id']) || !empty($present['valid_id_drivers_license']) || !empty($present['valid_id_passport']) || !empty($present['valid_id_sss_id']));
?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h1 class="h4 fw-bold mb-1">My Application</h1>
  </div>
  <a class="btn btn-secondary" href="<?= htmlspecialchars(url('/teacher')) ?>">Back</a>
</div>

<?php if (($teacher['full_name'] ?? '') === ''): ?>
  <div class="alert alert-warning">Complete your profile first: <a href="<?= htmlspecialchars(url('/teacher/profile')) ?>" class="alert-link">Update Profile</a></div>
<?php endif; ?>

<?php if ($app): ?>
  <div class="alert alert-info d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
      <div class="fw-semibold">Application #<?= (int)$appId ?> <span class="text-capitalize"><?= htmlspecialchars($status) ?></span></div>
      <?php if (!empty($missing)): ?>
        <div class="small mt-1">Missing: <?= htmlspecialchars(implode(', ', $missing)) ?></div>
      <?php else: ?>
        <div class="small mt-1">All required files are uploaded.</div>
      <?php endif; ?>
    </div>
    <?php if ($status === 'submitted'): ?>
      <form method="post" action="<?= htmlspecialchars(url('/teacher/apply/cancel')) ?>" class="mb-0" onsubmit="return confirm('Cancel this submitted application?');">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">
        <input type="hidden" name="app_id" value="<?= (int)$appId ?>">
        <button class="btn btn-danger btn-sm" type="submit">Cancel</button>
      </form>
    <?php endif; ?>
  </div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-12 col-lg-7">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <h2 class="h6 fw-bold mb-3">Upload Requirements</h2>

        <form method="post" action="<?= htmlspecialchars(url('/teacher/apply')) ?>" enctype="multipart/form-data" class="row g-3" id="applicationForm">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">
          <?php if ($appId > 0): ?><input type="hidden" name="app_id" value="<?= (int)$appId ?>"><?php endif; ?>

          <div class="col-12">
            <label class="form-label">Renewal Schedule</label>
            <select class="form-select" name="schedule_id" required>
              <option value="" disabled <?= $app ? '' : 'selected' ?>>Select schedule...</option>
              <?php foreach ($schedules as $s): ?>
                <option value="<?= (int)$s['id'] ?>" <?= $app && (int)$app['schedule_id'] === (int)$s['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars((string)$s['title']) ?> (<?= htmlspecialchars((string)$s['open_from']) ?> -> <?= htmlspecialchars((string)$s['due_until']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
            <div class="text-muted small mt-1">Creating a new application requires an open schedule.</div>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Birth Certificate <?= !empty($present['birth_certificate']) ? '' : '' ?></label>
            <input class="form-control req-file" type="file" name="req_birth_certificate" accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx" data-preview-name="Birth Certificate">
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">Medical Certificate <?= !empty($present['medical_certificate']) ? '' : '' ?></label>
            <input class="form-control req-file" type="file" name="req_medical_certificate" accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx" data-preview-name="Medical Certificate">
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Transcript of Records <?= !empty($present['transcript_of_records']) ? '' : '' ?></label>
            <input class="form-control req-file" type="file" name="req_transcript_of_records" accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx" data-preview-name="Transcript of Records">
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">Accomplishment Report <?= !empty($present['accomplishment_report']) ? '' : '' ?></label>
            <input class="form-control req-file" type="file" name="req_accomplishment_report" accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx" data-preview-name="Accomplishment Report">
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Seminars/Training <?= !empty($present['seminars_training']) ? '' : '' ?></label>
            <input class="form-control req-file" type="file" name="req_seminars_training" accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx" data-preview-name="Seminars/Training">
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">Certificates <?= !empty($present['certificates']) ? '' : '' ?></label>
            <input class="form-control req-file" type="file" name="req_certificates" accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx" data-preview-name="Certificates">
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Cedula <?= !empty($present['cedula']) ? '' : '' ?></label>
            <input class="form-control req-file" type="file" name="req_cedula" accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx" data-preview-name="Cedula">
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Valid ID (choose 1) <?= $hasValidId ? '' : '' ?></label>
            <div class="row g-2">
              <div class="col-6">
                <select class="form-select" name="valid_id_type">
                  <option value="" selected>Select ID...</option>
                  <option value="national_id">National ID</option>
                  <option value="drivers_license">Driver's License</option>
                  <option value="passport">Passport</option>
                  <option value="sss_id">SSS ID</option>
                </select>
              </div>
              <div class="col-6">
                <input class="form-control req-file" type="file" name="req_valid_id" accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx" data-preview-name="Valid ID">
              </div>
            </div>
            <?php if ($hasValidId): ?>
              <div class="text-muted small mt-1">Valid ID already uploaded.</div>
            <?php endif; ?>
          </div>

          <div class="col-12">
            <button class="btn btn-danger" type="submit">Save / Upload</button>
          </div>
        </form>

        <form method="post" action="<?= htmlspecialchars(url('/teacher/apply/submit')) ?>" class="mt-3">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">
          <input type="hidden" name="app_id" value="<?= (int)$appId ?>">
          <button class="btn btn-primary w-100" type="submit" <?= $appId > 0 ? '' : 'disabled' ?>><?= $status === 'submitted' ? 'Update Application' : 'Submit Application' ?></button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-5">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <h2 class="h6 fw-bold mb-2">Uploaded Requirements</h2>

        <div class="preview-box border rounded bg-white overflow-auto p-2 " style="max-height: 575px;">
          <?php if (!$app): ?>
            <div class="text-muted small">No application yet. Select a schedule and upload your requirements.</div>
          <?php elseif (empty($docs)): ?>
            <div class="text-muted small">No files uploaded yet.</div>
          <?php else: ?>
            <div class="list-group list-group-flush">
              <?php foreach ($docs as $d): ?>
                <?php $doc = is_array($d) ? $d : []; ?>
                <div class="list-group-item px-0 py-3">
                  <div class="text-truncate">
                    <div class="small text-muted text-capitalize"><?= htmlspecialchars(str_replace('_', ' ', (string)($doc['doc_type'] ?? ''))) ?></div>
                    <div class="text-body"><?= htmlspecialchars((string)($doc['original_name'] ?? '-')) ?></div>
                    <div class="text-muted small">Uploaded: <?= htmlspecialchars((string)($doc['uploaded_at'] ?? '-')) ?></div>
                  </div>
                  <div class="d-flex gap-2 mt-2">
                    <a class="btn btn-sm btn-secondary" href="<?= htmlspecialchars(url('/files/view')) ?>?id=<?= (int)($doc['id'] ?? 0) ?>" target="_blank" rel="noopener">Preview</a>
                    <a class="btn btn-sm btn-primary" href="<?= htmlspecialchars(url('/files/download')) ?>?id=<?= (int)($doc['id'] ?? 0) ?>">Download</a>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
