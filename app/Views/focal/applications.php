<?php
declare(strict_types=1);

use App\Lib\Csrf;
?>
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
  <div>
    <h1 class="h4 fw-bold mb-1">Applications</h1>
    <div class="text-muted"></div>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a class="btn btn-primary" href="<?= htmlspecialchars(url('/focal/reports/applications-pdf')) ?>">Download PDF</a>
    <button class="btn btn-danger" type="button" data-bs-toggle="modal" data-bs-target="#clearHistoryModal">Clear History</button>
    <a class="btn btn-secondary" href="<?= htmlspecialchars(url('/focal')) ?>">Back</a>
  </div>
</div>

<div class="modal fade" id="clearHistoryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Clear Renewal History</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-warning mb-2">This will permanently delete all applications and uploaded requirement records.</div>
        <form method="post" action="<?= htmlspecialchars(url('/focal/applications/clear-history')) ?>" class="vstack gap-2">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">
          <label class="form-label">Type <span class="fw-semibold">CLEAR</span> to confirm</label>
          <input class="form-control" name="confirm_text" required>
          <button class="btn btn-danger" type="submit">Confirm</button>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="card border-0 shadow-sm" style="min-height: 550px;">
  <div class="card-body">
    <?php if (empty($apps)): ?>
      <div class="text-muted">No applications found.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table align-middle mb-0" data-searchable>
          <thead>
          <tr>
            <th>ID</th>
            <th>Teacher</th>
            <th>Schedule</th>
            <th>Status</th>
            <th>Submitted</th>
            <th class="text-end">Review</th>
          </tr>
          </thead>
          <tbody>
          <?php foreach ($apps as $a): ?>
            <tr>
              <td class="fw-semibold"><?= htmlspecialchars((string)($a['teacher_uid'] ?: '-')) ?></td>
              <td>
                <div class="fw-semibold"><?= htmlspecialchars((string)($a['full_name'] ?: $a['email'])) ?></div>
                <div class="text-muted small"><?= htmlspecialchars((string)$a['email']) ?></div>
              </td>
              <td><?= htmlspecialchars((string)($a['schedule_title'] ?? '—')) ?></td>
              <td>
                <?php $st = (string)$a['status']; $badge = ($st === 'approved') ? 'success' : (($st === 'rejected') ? 'danger' : (($st === 'terminated') ? 'secondary' : 'warning text-dark')); ?>
                <span class="badge bg-<?= $badge ?>">
                  <?= htmlspecialchars((string)$a['status']) ?>
                </span>
              </td>
              <td class="text-muted small"><?= htmlspecialchars((string)($a['submitted_at'] ?? '—')) ?></td>
              <td class="text-end"><a class="btn btn-sm btn-primary" href="<?= htmlspecialchars(url('/focal/applications/view')) ?>?id=<?= (int)$a['id'] ?>">Open</a></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>
