<?php
declare(strict_types=1);

use App\Lib\Csrf;
?>
<section class="login-shell">
  <div class="login-frame shadow-lg">
    <div class="login-showcase">
      <div class="login-showcase-inner">
        <div class="login-logo-card">
          <img src="<?= htmlspecialchars(url('/assets/img/cswdo_logo.png')) ?>" alt="CSWDO Logo" class="login-logo">
        </div>

        <div class="login-brand-copy">
          <h1 class="login-brand-title">ECCD Teacher<br>Renewal<br>System</h1>
          <span class="login-brand-line"></span>
          <p class="login-brand-text">Streamlined renewal process that reduces manual effort, and ensures a faster, more efficient user experience through automated tracking and timely notifications.</p>
        </div>
      </div>
    </div>

    <div class="login-panel">
      <div class="login-panel-inner">
        <h2 class="login-welcome">Welcome back!</h2>

        <form method="post" action="<?= htmlspecialchars(url('/login')) ?>" class="login-form">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">

          <div class="login-field-group">
            <label class="login-label" for="loginEmail">Email address</label>
            <input class="form-control login-input" id="loginEmail" type="email" name="email" required autocomplete="username">
          </div>

          <div class="login-field-group">
            <label class="login-label" for="loginPassword">Password</label>
            <input class="form-control login-input" id="loginPassword" type="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
            <div class="form-check mt-2 ms-3">
              <input class="form-check-input" type="checkbox" id="showLoginPassword" data-toggle-password="#loginPassword">
              <label class="form-check-label" for="showLoginPassword">Show password</label>
            </div>
          </div>

          <button class="btn login-submit-btn w-100" type="submit">Log in</button>
        </form>

        <div class="login-panel-lines" aria-hidden="true">
          <span></span>
          <span></span>
        </div>
      </div>
    </div>
  </div>
</section>
