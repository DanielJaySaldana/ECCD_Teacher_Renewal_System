<?php
declare(strict_types=1);

use App\Lib\Csrf;
?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h1 class="h4 fw-bold mb-1">Renewal Schedules</h1>
    <div class="text-muted"></div>
  </div>
  <a class="btn btn-secondary" href="<?= htmlspecialchars(url('/focal')) ?>">Back</a>
</div>

<div class="row g-3">
  <div class="col-12 col-lg-5">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <h2 class="h6 fw-bold">Create Schedule</h2>
        <form method="post" action="<?= htmlspecialchars(url('/focal/schedules/create')) ?>" class="row g-2">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">
          <div class="col-12">
            <label class="form-label">Title</label>
            <input class="form-control" name="title" maxlength="80" required placeholder="e.g., 2026 Renewal (Batch 1)">
          </div>
          <div class="col-6">
            <label class="form-label">Open From</label>
            <input class="form-control" type="date" name="open_from" required>
          </div>
          <div class="col-6">
            <label class="form-label">Due Until</label>
            <input class="form-control" type="date" name="due_until" required>
          </div>
          <div class="col-12">
            <button class="btn btn-primary" type="submit">Create</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="col-12 col-lg-7">
    <div class="card border-0 shadow-sm" style="min-height: 250px;">
      <div class="card-body">
        <h2 class="h6 fw-bold mb-2">All Schedules</h2>
        <?php if (empty($schedules)): ?>
          <div class="text-muted">No schedules yet.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead>
              <tr>
                <th>Title</th>
                <th>Open</th>
                <th>Due</th>
                <th class="text-end">Action</th>
              </tr>
              </thead>
              <tbody>
              <?php foreach ($schedules as $s): ?>
                <tr>
                  <td class="fw-semibold"><?= htmlspecialchars((string)$s['title']) ?></td>
                  <td><?= htmlspecialchars((string)$s['open_from']) ?></td>
                  <td><?= htmlspecialchars((string)$s['due_until']) ?></td>
                  <td class="text-end">
                    <form method="post" action="<?= htmlspecialchars(url('/focal/schedules/delete')) ?>" onsubmit="return confirm('Delete this schedule?');" class="mb-0">
                      <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">
                      <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                      <button class="btn btn-sm btn-danger" type="submit">Delete</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
