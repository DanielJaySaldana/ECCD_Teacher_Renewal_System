<?php
/** @var mixed $pending */
/** @var mixed $approved */
/** @var mixed $rejected */
/** @var mixed $totalTeachers */
/** @var mixed $nextExpirations */
declare(strict_types=1);

$pending = (int)($pending ?? 0);
$approved = (int)($approved ?? 0);
$rejected = (int)($rejected ?? 0);
$totalTeachers = (int)($totalTeachers ?? 0);
$nextExpirations = is_array($nextExpirations ?? null) ? $nextExpirations : [];
?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h1 class="h4 fw-bold mb-1">ECCD Focal Dashboard</h1>
    <div class="text-muted"></div>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-primary" href="<?= htmlspecialchars(url('/focal/schedules')) ?>">Schedules</a>
    <a class="btn btn-primary" href="<?= htmlspecialchars(url('/focal/applications')) ?>">Review Applications</a>
  </div>
</div>

<div class="row g-3">
  <div class="col-12 col-md-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body focal-dashboard-stat-card">
        <div class="text-muted small">Pending</div>
        <div class="fs-3 fw-bold"><?= $pending ?></div>
        <span class="btn btn-sm mt-2 invisible" aria-hidden="true">Spacer</span>
        <img class="focal-dashboard-stat-img pending-img" src="<?= htmlspecialchars(url('/assets/img/dashboard_pending.png')) ?>" alt="" aria-hidden="true">
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body focal-dashboard-stat-card">
        <div class="text-muted small">Approved</div>
        <div class="fs-3 fw-bold"><?= $approved ?></div>
        <span class="btn btn-sm mt-2 invisible" aria-hidden="true">Spacer</span>
        <img class="focal-dashboard-stat-img approved-img" src="<?= htmlspecialchars(url('/assets/img/dashboard_approved.png')) ?>" alt="" aria-hidden="true">
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body focal-dashboard-stat-card">
        <div class="text-muted small">Rejected</div>
        <div class="fs-3 fw-bold"><?= $rejected ?></div>
        <span class="btn btn-sm mt-2 invisible" aria-hidden="true">Spacer</span>
        <img class="focal-dashboard-stat-img rejected-img" src="<?= htmlspecialchars(url('/assets/img/dashboard_rejected.png')) ?>" alt="" aria-hidden="true">
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body focal-dashboard-stat-card">
        <div class="text-muted small">Total number of Teachers</div>
        <div class="fs-3 fw-bold"><?= $totalTeachers ?></div>
        <a class="btn btn-outline-primary btn-sm mt-2" href="<?= htmlspecialchars(url('/focal/teachers')) ?>">View Teachers</a>
        <img class="focal-dashboard-stat-img teachers-img" src="<?= htmlspecialchars(url('/assets/img/dashboard_teachers.png')) ?>" alt="" aria-hidden="true">
      </div>
    </div>
  </div>
</div>

<div class="card border-0 shadow-sm mt-3" style="min-height: 400px;">
  <div class="card-body">
    <h2 class="h6 fw-bold mb-2">Expirations</h2>
    <?php if ($nextExpirations === []): ?>
      <div class="text-muted">No expiration dates set yet.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead>
          <tr>
            <th>Teacher ID</th>
            <th style="width: 350px;">Name</th>
            <th style="width: 320px;"> <span style="margin-left: -16px; display: inline-block;">Expiration Date</span></th>
            <th style="width: 150px;"> <span style="margin-left: -21px; display: inline-block;">Days Left</span></th>
          </tr>
          </thead>
          <tbody>
          <?php foreach ($nextExpirations as $row): ?>
            <?php $t = is_array($row) ? $row : []; ?>
            <tr>
              <td class="fw-semibold"><?= htmlspecialchars((string)($t['teacher_uid'] ?? '-')) ?></td>
              <td><?= htmlspecialchars((string)($t['full_name'] ?? '-')) ?></td>
              <td><?= htmlspecialchars((string)($t['expiration_date'] ?? '-')) ?></td>
              <td><?= htmlspecialchars((string)($t['days_left'] ?? '-')) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>
