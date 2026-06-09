<?php
declare(strict_types=1);

/** @var mixed $app */
/** @var mixed $docs */
$app = is_array($app ?? null) ? $app : [];
$docs = is_array($docs ?? null) ? $docs : [];
?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h1 class="h4 fw-bold mb-1">Review Application #<?= (int)($app['id'] ?? 0) ?></h1>
  </div>
  <a class="btn btn-secondary" href="<?= htmlspecialchars(url('/teacher/applications')) ?>">Back</a>
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
          <div class="alert alert-success mb-0">This application is approved.</div>
        <?php elseif ((string)($app['status'] ?? '') === 'rejected'): ?>
          <div class="alert alert-danger mb-0">This application is rejected.</div>
        <?php elseif ((string)($app['status'] ?? '') === 'terminated'): ?>
          <div class="alert alert-warning mb-0">This application approval has been terminated.</div>
        <?php elseif ((string)($app['status'] ?? '') === 'cancelled'): ?>
          <div class="alert alert-secondary mb-0">This application is cancelled.</div>
        <?php elseif ((string)($app['status'] ?? '') === 'submitted'): ?>
          <div class="alert alert-warning mb-0">This renewal application is pending for ECCD Focal's decision.</div>
        <?php else: ?>
          <div class="alert alert-info mb-0">This application is in draft.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-7 d-flex">
    <div class="card border-0 shadow-sm w-100 h-100">
      <div class="card-body d-flex flex-column">
        <h2 class="h6 fw-bold mb-2">Submitted Requirements</h2>

        <div class="border rounded bg-white p-2 flex-grow-1 overflow-auto" style="max-height: 520px;">
          <?php if (empty($docs)): ?>
            <div class="text-muted">No documents uploaded.</div>
          <?php else: ?>
            <div class="list-group list-group-flush">
              <?php foreach ($docs as $d): ?>
                <?php $doc = is_array($d) ? $d : []; ?>
                <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center gap-2 flex-wrap">
                  <div class="text-truncate" style="max-width: 450px;">
                    <div class="small text-muted text-capitalize"><?= htmlspecialchars(str_replace('_',' ', (string)($doc['doc_type'] ?? ''))) ?></div>
                    <div class="text-body"><?= htmlspecialchars((string)($doc['original_name'] ?? '-')) ?></div>
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
