<?php
declare(strict_types=1);

use App\Lib\Csrf;
?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h1 class="h4 fw-bold mb-1">My Applications</h1>
  </div>
  <a class="btn btn-primary" href="<?= htmlspecialchars(url('/teacher/apply')) ?>">New / Continue</a>
</div>

<div class="card border-1 shadow-sm" style="min-height: 550px;">
  <div class="card-body">
    <?php if (empty($apps)): ?>
      <div class="text-muted">No applications yet.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table align-middle mb-0" data-searchable>
          <thead>
          <tr>
            <th>ID</th>
            <th>Schedule</th>
            <th>Status</th>
            <th>Submitted</th>
            <th>Reviewed</th>
            <th class="text-end">Action</th>
          </tr>
          </thead>
          <tbody>
          <?php foreach ($apps as $a): ?>
            <tr>
              <td>#<?= (int)$a['id'] ?></td>
              <td><?= htmlspecialchars((string)($a['schedule_title'] ?? '')) ?></td>
              <td>
                <span class="badge bg-<?php
                  echo $a['status'] === 'approved' ? 'success' : (
                    $a['status'] === 'rejected' ? 'danger' : (
                      $a['status'] === 'cancelled' ? 'secondary' : 'warning text-dark'
                    )
                  );
                ?>">
                  <?= htmlspecialchars((string)$a['status']) ?>
                </span>
              </td>
              <td class="text-muted small"><?= htmlspecialchars((string)($a['submitted_at'] ?? '-')) ?></td>
              <td class="text-muted small"><?= htmlspecialchars((string)($a['reviewed_at'] ?? '-')) ?></td>
              <td class="text-end">
                <a class="btn btn-sm btn-primary" href="<?= htmlspecialchars(url('/teacher/applications/view')) ?>?id=<?= (int)$a['id'] ?>">Open</a>
                <?php if (in_array((string)$a['status'], ['draft','submitted'], true)): ?>
                  <a class="btn btn-sm btn-primary" href="<?= htmlspecialchars(url('/teacher/apply')) ?>?id=<?= (int)$a['id'] ?>">Edit</a>
                <?php endif; ?>
                <?php if ((string)$a['status'] === 'submitted'): ?>
                  <form method="post" action="<?= htmlspecialchars(url('/teacher/apply/cancel')) ?>" class="d-inline" onsubmit="return confirm('Cancel this submitted application?');">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">
                    <input type="hidden" name="app_id" value="<?= (int)$a['id'] ?>">
                    <button class="btn btn-sm btn-danger" type="submit">Cancel</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>
