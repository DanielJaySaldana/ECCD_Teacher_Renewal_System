<?php
declare(strict_types=1);

use App\Lib\Csrf;
?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h1 class="h4 fw-bold mb-1">Notifications</h1>
  </div>
  <div class="d-flex align-items-center gap-2">
    <form method="post" action="<?= htmlspecialchars(url('/notifications/clear')) ?>" class="mb-0">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">
      <button class="btn btn-danger" type="submit">Clear</button>
    </form>
    <a class="btn btn-secondary" href="<?= htmlspecialchars(url('/')) ?>">Back</a>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body">
    <?php if (empty($items)): ?>
      <div class="text-muted">No notifications.</div>
    <?php else: ?>
      <div class="list-group list-group-flush">
        <?php foreach ($items as $n): ?>
          <div class="list-group-item d-flex justify-content-between align-items-start gap-3">
            <div>
              <div class="d-flex align-items-center gap-2">
                <div class="fw-semibold"><?= htmlspecialchars((string)$n['title']) ?></div>
                <?php if (!$n['read_at']): ?><span class="badge bg-warning text-dark">New</span><?php endif; ?>
              </div>
              <div class="text-muted small"><?= htmlspecialchars((string)$n['created_at']) ?></div>
              <div class="mt-1"><?= htmlspecialchars((string)$n['body']) ?></div>
              <?php if (!empty($n['link_url'])): ?>
                <div class="mt-2"><a href="<?= htmlspecialchars(url((string)$n['link_url'])) ?>">Open</a></div>
              <?php endif; ?>
            </div>
            <div class="text-end">
              <?php if (!$n['read_at']): ?>
                <form method="post" action="<?= htmlspecialchars(url('/notifications/read')) ?>" class="mb-0">
                  <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">
                  <input type="hidden" name="id" value="<?= (int)$n['id'] ?>">
                  <button class="btn btn-sm btn-outline-primary" type="submit">Mark read</button>
                </form>
              <?php else: ?>
                <span class="text-muted small">Read</span>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
