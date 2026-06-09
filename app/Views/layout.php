<?php
declare(strict_types=1);

/** @var mixed $title */
/** @var mixed $viewFile */

use App\Lib\Auth;
use App\Lib\Csrf;
use App\Lib\Db;
use App\Lib\Flash;

$appTitle = APP_NAME;
$pageTitle = isset($title) ? (string)$title : $appTitle;
$viewFile = isset($viewFile) ? (string)$viewFile : '';

$currentPath = (string)(parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?? '');
$base = base_url();
if ($base !== '' && str_starts_with($currentPath, $base . '/')) {
    $currentPath = substr($currentPath, strlen($base));
}
$isLoginPage = $currentPath === '/login';
$isNavActive = static function (string $path) use ($currentPath): bool {
    if ($path === '/teacher' || $path === '/focal') {
        return $currentPath === $path;
    }

    return $currentPath === $path || str_starts_with($currentPath, $path . '/');
};

/** @var array{id:int,email:string,role:string,full_name:?string}|null $user */
$user = Auth::check() ? Auth::user() : null;
$unread = 0;
if ($user !== null) {
    $pdo = Db::pdo();
    $stmt = $pdo->prepare('SELECT COUNT(*) AS c FROM notifications WHERE user_id = ? AND read_at IS NULL');
    $stmt->execute([(int)$user['id']]);
    $countRow = $stmt->fetch();
    $unread = (int)(is_array($countRow) ? ($countRow['c'] ?? 0) : 0);
}

$flashItems = Flash::all();
$flashItems = is_array($flashItems) ? $flashItems : [];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle) ?>  <?= htmlspecialchars($appTitle) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= htmlspecialchars(url('/assets/css/app.css')) ?>" rel="stylesheet">
</head>
<body class="<?= $isLoginPage ? 'login-page-body' : 'bg-app' ?>">
  <?php if (!$isLoginPage): ?>
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary border-bottom shadow-sm">
    <div class="container-fluid">
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="topNav">
        <?php if ($user !== null && in_array($user['role'], ['teacher', 'focal'], true)): ?>
          <a href="<?= htmlspecialchars(url($user['role'] === 'teacher' ? '/teacher' : '/focal')) ?>" class="app-header-logo-link" aria-label="Go to dashboard">
            <img src="<?= htmlspecialchars(url('/assets/img/ECCD_Teacher_Renewal_System_logo.png')) ?>" alt="ECCD Teacher Renewal System logo" class="app-header-logo">
          </a>
        <?php endif; ?>
        <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-lg-2">
          <?php if ($user !== null && $user['role'] === 'teacher'): ?>
            <li class="nav-item"><a class="nav-link app-nav-link <?= $isNavActive('/teacher') ? 'active' : '' ?>" href="<?= htmlspecialchars(url('/teacher')) ?>">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link app-nav-link <?= $isNavActive('/teacher/apply') ? 'active' : '' ?>" href="<?= htmlspecialchars(url('/teacher/apply')) ?>">My Application</a></li>
            <li class="nav-item"><a class="nav-link app-nav-link <?= $isNavActive('/teacher/applications') ? 'active' : '' ?>" href="<?= htmlspecialchars(url('/teacher/applications')) ?>">Application History</a></li>
            <li class="nav-item"><a class="nav-link app-nav-link <?= $isNavActive('/teacher/profile') ? 'active' : '' ?>" href="<?= htmlspecialchars(url('/teacher/profile')) ?>">Profile</a></li>
          <?php elseif ($user !== null && $user['role'] === 'focal'): ?>
            <li class="nav-item"><a class="nav-link app-nav-link <?= $isNavActive('/focal') ? 'active' : '' ?>" href="<?= htmlspecialchars(url('/focal')) ?>">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link app-nav-link <?= $isNavActive('/focal/teachers') ? 'active' : '' ?>" href="<?= htmlspecialchars(url('/focal/teachers')) ?>">Teachers</a></li>
            <li class="nav-item"><a class="nav-link app-nav-link <?= $isNavActive('/focal/applications') ? 'active' : '' ?>" href="<?= htmlspecialchars(url('/focal/applications')) ?>">Applications</a></li>
            <li class="nav-item"><a class="nav-link app-nav-link <?= $isNavActive('/focal/schedules') ? 'active' : '' ?>" href="<?= htmlspecialchars(url('/focal/schedules')) ?>">Schedules</a></li>
            <li class="nav-item"><a class="nav-link app-nav-link <?= $isNavActive('/focal/profile') ? 'active' : '' ?>" href="<?= htmlspecialchars(url('/focal/profile')) ?>">Profile</a></li>
          <?php endif; ?>
        </ul>

        <div class="d-flex gap-3 align-items-center">
          <?php if ($user !== null): ?>
            <a class="notification-bell-link" href="<?= htmlspecialchars(url('/notifications')) ?>" aria-label="Notifications" title="Notifications">
              <img src="<?= htmlspecialchars(url('/assets/img/notification_bell.png')) ?>" alt="Notifications" class="notification-bell-img">
              <?php if ($unread > 0): ?>
                <span class="notification-bell-badge"><?= $unread > 99 ? '99+' : htmlspecialchars((string)$unread) ?></span>
              <?php endif; ?>
            </a>

            <?php if ($user['role'] === 'teacher'): ?>
              <div class="dropdown">
                <button class="btn p-0 border-0 d-inline-flex align-items-center gap-2 text-decoration-none" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Profile menu">
                  <span class="avatar-sm rounded-circle border border-white overflow-hidden bg-white">
                    <svg class="w-100 h-100" viewBox="0 0 64 64" aria-hidden="true" focusable="false">
                      <circle cx="32" cy="32" r="32" fill="#b9b9b9"/>
                      <circle cx="32" cy="25" r="12" fill="#eeeeee"/>
                      <path d="M12 59c3.8-13.1 11-20 20-20s16.2 6.9 20 20A31.8 31.8 0 0 1 32 64a31.8 31.8 0 0 1-20-5z" fill="#eeeeee"/>
                    </svg>
                  </span>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                  <a class="dropdown-item" href="<?= htmlspecialchars(url('/teacher/profile')) ?>">Profile</a>
                  <form method="post" action="<?= htmlspecialchars(url('/logout')) ?>" class="mb-0">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">
                    <button class="dropdown-item" type="submit">Logout</button>
                  </form>
                </div>
              </div>
            <?php else: ?>
              <div class="dropdown">
                <button class="btn p-0 border-0 d-inline-flex align-items-center gap-2 text-decoration-none" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Profile menu">
                  <span class="avatar-sm rounded-circle border border-white overflow-hidden bg-white">
                    <img src="<?= htmlspecialchars(url('/files/profile-photo')) ?>?user_id=<?= (int)$user['id'] ?>&v=<?= time() ?>" alt="" class="w-100 h-100" style="object-fit: cover;" onerror="this.style.display='none'; this.parentElement.classList.add('avatar-fallback'); this.parentElement.textContent='';">
                  </span>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                  <a class="dropdown-item" href="<?= htmlspecialchars(url('/focal/profile')) ?>">Profile</a>
                  <form method="post" action="<?= htmlspecialchars(url('/logout')) ?>" class="mb-0">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">
                    <button class="dropdown-item" type="submit">Logout</button>
                  </form>
                </div>
              </div>
            <?php endif; ?>
          <?php else: ?>
            <?php if ($currentPath !== '/login'): ?>
              <a class="btn btn-light btn-sm" href="<?= htmlspecialchars(url('/login')) ?>">Login</a>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </nav>
  <?php endif; ?>

  <main class="<?= $isLoginPage ? 'login-page-main' : 'container py-4' ?>">
    <?php foreach ($flashItems as $item): ?>
      <?php $f = is_array($item) ? $item : []; ?>
      <div class="alert alert-<?= htmlspecialchars((string)($f['type'] ?? 'info')) ?> alert-dismissible fade show <?= $isLoginPage ? 'login-flash' : '' ?>" role="alert">
        <?= htmlspecialchars((string)($f['message'] ?? '')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endforeach; ?>

    <?php require $viewFile; ?>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="<?= htmlspecialchars(url('/assets/js/app.js')) ?>"></script>
</body>
</html>
