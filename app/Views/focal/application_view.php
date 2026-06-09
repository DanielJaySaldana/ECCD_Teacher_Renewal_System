<?php
/** @var mixed $app */
/** @var mixed $docs */
declare(strict_types=1);

use App\Lib\Csrf;

$app = is_array($app ?? null) ? $app : [];
$docs = is_array($docs ?? null) ? $docs : [];
?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h1 class="h4 fw-bold mb-1">Review Application #<?= (int)($app['id'] ?? 0) ?></h1>
    <div class="text-muted">Check completeness of requirements and approve or reject.</div>
  </div>
  <a class="btn btn-secondary" href="<?= htmlspecialchars(url('/focal/applications')) ?>">Back</a>
</div>

<div class="row g-3 align-items-stretch">
  <div class="col-12 col-lg-5 d-flex flex-column">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <h2 class="h6 fw-bold mb-2">Teacher Info</h2>
        <div class="mb-2">
          <div class="text-muted small">Name</div>
          <div class="fw-semibold"><?= htmlspecialchars((string)($app['full_name'] ?? '-')) ?></div>
        </div>
        <div class="mb-2">
          <div class="text-muted small">Email</div>
          <div><?= htmlspecialchars((string)($app['email'] ?? '-')) ?></div>
        </div>
        <div class="mb-2">
          <div class="text-muted small">Expiration Date</div>
          <div><?= htmlspecialchars((string)($app['expiration_date'] ?? 'Not set')) ?></div>
        </div>
        <div class="mb-2">
          <div class="text-muted small">Schedule</div>
          <div><?= htmlspecialchars((string)($app['schedule_title'] ?? '-')) ?></div>
        </div>
        <div class="mb-2">
          <div class="text-muted small">Submitted</div>
          <div><?= htmlspecialchars((string)($app['submitted_at'] ?? '-')) ?></div>
        </div>
        <div class="mb-0">
          <div class="text-muted small">Status</div>
          <div class="fw-semibold text-capitalize"><?= htmlspecialchars((string)($app['status'] ?? '-')) ?></div>
        </div>
      </div>
    </div>

    <div class="card border-0 shadow-sm mt-3 flex-grow-1">
      <div class="card-body">
        <h2 class="h6 fw-bold mb-2">Decision</h2>

        <?php if (!empty($app['remarks'])): ?>
          <div class="mb-3">
            <div class="text-muted small">Remarks</div>
            <div class="border rounded p-2 bg-light"><?= nl2br(htmlspecialchars((string)$app['remarks'])) ?></div>
          </div>
        <?php endif; ?>

        <?php if ((string)($app['status'] ?? '') === 'approved'): ?>
          <div class="alert alert-success">This application is approved.</div>
          <form method="post" action="<?= htmlspecialchars(url('/focal/applications/terminate')) ?>" class="vstack gap-2" onsubmit="return confirm('Terminate this approval? This will allow the teacher to reapply again.');">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">
            <input type="hidden" name="app_id" value="<?= (int)($app['id'] ?? 0) ?>">
            <label class="form-label">Type <span class="fw-semibold">TERMINATE</span> to confirm</label>
            <input class="form-control" name="confirm_text" required>
            <button class="btn btn-outline-danger" type="submit">Terminate Approval</button>
          </form>
        <?php elseif ((string)($app['status'] ?? '') !== 'submitted'): ?>
          <div class="alert alert-info mb-0">This application is already <?= htmlspecialchars((string)($app['status'] ?? '-')) ?>.</div>
        <?php else: ?>
          <form method="post" action="<?= htmlspecialchars(url('/focal/applications/decide')) ?>" class="vstack gap-2">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">
            <input type="hidden" name="app_id" value="<?= (int)($app['id'] ?? 0) ?>">
            <div>
              <label class="form-label">Add Remarks (optional)</label>
              <textarea class="form-control" name="remarks" rows="3" placeholder="Add remarks for the teacher..."></textarea>
            </div>
            <div class="d-flex gap-2">
              <button class="btn btn-success" type="submit" name="decision" value="approved">Approve</button>
              <button class="btn btn-danger" type="submit" name="decision" value="rejected">Reject</button>
            </div>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-7 d-flex">
    <div class="card border-0 shadow-sm w-100 h-100">
      <div class="card-body d-flex flex-column h-100">
        <h2 class="h6 fw-bold mb-2">Submitted Requirements</h2>

        <div class="preview-box border rounded bg-white p-2 flex-grow-1 overflow-auto" style="max-height: 520px;">
          <?php if ($docs === []): ?>
            <div class="text-muted">No documents uploaded.</div>
          <?php else: ?>
            <div class="list-group list-group-flush">
              <?php foreach ($docs as $d): ?>
                <?php $doc = is_array($d) ? $d : []; ?>
                <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center gap-2 flex-wrap">
                  <div class="text-truncate" style="max-width: 360px;">
                    <div class="small text-muted text-capitalize"><?= htmlspecialchars(str_replace('_', ' ', (string)($doc['doc_type'] ?? ''))) ?></div>
                    <div class="text-decoration-none text-body">
                      <?= htmlspecialchars((string)($doc['original_name'] ?? '-')) ?>
                    </div>
                    <div class="text-muted small">Uploaded: <?= htmlspecialchars((string)($doc['uploaded_at'] ?? '-')) ?></div>
                  </div>
                  <div class="d-flex gap-2">
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
