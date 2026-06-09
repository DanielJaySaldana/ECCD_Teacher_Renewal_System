<?php
declare(strict_types=1);

/** @var mixed $latestApp */
/** @var mixed $openSchedules */
/** @var mixed $teacher */
$latestApp = is_array($latestApp ?? null) ? $latestApp : null;
$openSchedules = is_array($openSchedules ?? null) ? $openSchedules : [];
$teacher = is_array($teacher ?? null) ? $teacher : [];
?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h1 class="h4 fw-bold mb-1">Teacher Dashboard</h1>
  </div>
  <a class="btn btn-primary" href="<?= htmlspecialchars(url('/teacher/profile')) ?>">Update Profile</a>
</div>

<div class="row g-3 mb-3">
  <div class="col-12 col-lg-4">
    <div class="card border-1 shadow-sm h-100">
      <div class="card-body teacher-dashboard-stat-card">
        <div class="text-muted small">Expiration Date</div>
        <div class="fs-4 fw-semibold"><?= htmlspecialchars((string)($teacher['expiration_date'] ?? 'Not set')) ?></div>
        <img class="teacher-dashboard-stat-img expiration-img" src="<?= htmlspecialchars(url('/assets/img/teacher_dashboard_expiration.png')) ?>" alt="" aria-hidden="true">
      </div>
    </div>
  </div>
  <div class="col-12 col-lg-4">
    <div class="card border-1 shadow-sm h-100">
      <div class="card-body teacher-dashboard-stat-card">
        <div class="text-muted small">Open Schedules</div>
        <div class="fs-4 fw-semibold"><?= (int)count($openSchedules) ?></div>
        <a class="btn btn-primary btn-sm mt-2" href="<?= htmlspecialchars(url('/teacher/apply')) ?>">Apply Now</a>
        <img class="teacher-dashboard-stat-img schedules-img" src="<?= htmlspecialchars(url('/assets/img/teacher_dashboard_schedules.png')) ?>" alt="" aria-hidden="true">
      </div>
    </div>
  </div>
  <div class="col-12 col-lg-4">
    <div class="card border-1 shadow-sm h-100">
      <div class="card-body teacher-dashboard-stat-card">
        <div class="text-muted small">Latest Application</div>
        <?php if ($latestApp): ?>
          <div class="fs-4 fw-semibold text-capitalize"><?= htmlspecialchars((string)$latestApp['status']) ?></div>
          <div class="text-muted small">Submitted: <?= htmlspecialchars((string)($latestApp['submitted_at'] ?? '—')) ?></div>
          <a class="btn btn-primary btn-sm mt-2" href="<?= htmlspecialchars(url('/teacher/applications')) ?>">View</a>
        <?php else: ?>
          <div class="fs-4 fw-semibold">None</div>
          <div class="text-muted small">Start by submitting your first application.</div>
        <?php endif; ?>
        <img class="teacher-dashboard-stat-img latest-img" src="<?= htmlspecialchars(url('/assets/img/teacher_dashboard_latest.png')) ?>" alt="" aria-hidden="true">
      </div>
    </div>
  </div>
</div>

<div class="card border-1 shadow-sm" style="min-height: 400px;">
  <div class="card-body">
    <h2 class="h6 fw-bold mb-2">Open Renewal Schedules</h2>
    <?php if (empty($openSchedules)): ?>
      <div class="text-muted">No open schedules right now.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead>
          <tr>
            <th>Title</th>
            <th>Open From</th>
            <th>Due Until</th>
          </tr>
          </thead>
          <tbody>
          <?php foreach ($openSchedules as $s): ?>
            <tr>
              <td class="fw-semibold"><?= htmlspecialchars((string)$s['title']) ?></td>
              <td><?= htmlspecialchars((string)$s['open_from']) ?></td>
              <td><?= htmlspecialchars((string)$s['due_until']) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>
