<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
  <div class="sidebar-header" style="justify-content: center; padding: 24px 16px;">
    <img src="assets/logo.png" alt="Metalabs Logo" style="max-width: 160px; height: auto;">
  </div>
  
  <nav class="sidebar-nav">
    <a href="index.php" class="nav-item <?= $currentPage == 'index.php' ? 'active' : '' ?>">
      <span class="material-symbols-outlined">dashboard</span>
      <span class="font-body-sm" style="font-weight: 500;">Dashboard</span>
    </a>
    <a href="assistants.php" class="nav-item <?= $currentPage == 'assistants.php' ? 'active' : '' ?>">
      <span class="material-symbols-outlined">badge</span>
      <span class="font-body-sm" style="font-weight: 500;">Aslabs</span>
    </a>
    <?php if (isset($isAdmin) && $isAdmin): ?>
      <a href="admin_schedule.php" class="nav-item <?= $currentPage == 'admin_schedule.php' ? 'active' : '' ?>">
        <span class="material-symbols-outlined">edit_calendar</span>
        <span class="font-body-sm" style="font-weight: 500;">Schedule Allocation</span>
      </a>
      <a href="admin_history.php" class="nav-item <?= $currentPage == 'admin_history.php' ? 'active' : '' ?>">
        <span class="material-symbols-outlined">history</span>
        <span class="font-body-sm" style="font-weight: 500;">History Logs</span>
      </a>
      <a href="register.php" class="nav-item <?= $currentPage == 'register.php' ? 'active' : '' ?>">
        <span class="material-symbols-outlined">person_add</span>
        <span class="font-body-sm" style="font-weight: 500;">Register Aslab</span>
      </a>
      <a href="attendance.php" class="nav-item <?= $currentPage == 'attendance.php' ? 'active' : '' ?>">
        <span class="material-symbols-outlined">document_scanner</span>
        <span class="font-body-sm" style="font-weight: 500;">Scan Test</span>
      </a>
    <?php else: ?>
      <a href="public_scanner.php" class="nav-item <?= $currentPage == 'public_scanner.php' ? 'active' : '' ?>">
        <span class="material-symbols-outlined">document_scanner</span>
        <span class="font-body-sm" style="font-weight: 500;">Live Scanner</span>
      </a>
      <a href="schedule.php" class="nav-item <?= $currentPage == 'schedule.php' ? 'active' : '' ?>">
        <span class="material-symbols-outlined">calendar_month</span>
        <span class="font-body-sm" style="font-weight: 500;">Schedule</span>
      </a>
    <?php endif; ?>
  </nav>

  <div style="padding: var(--unit-lg); border-top: 1px solid var(--outline);">
    <?php if (isset($isAdmin) && $isAdmin): ?>
      <div style="display: flex; align-items: center; justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: 12px;">
          <div style="width: 36px; height: 36px; border-radius: 50%; background-color: var(--surface-container-highest); display: flex; align-items: center; justify-content: center;">
            <span class="material-symbols-outlined" style="font-size: 20px;">admin_panel_settings</span>
          </div>
          <div>
            <p class="font-body-sm" style="margin: 0; font-weight: 500; color: var(--on-surface);">Admin User</p>
            <p class="font-label-caps text-on-surface-variant" style="margin: 0; font-size: 10px;">SYSTEM OWNER</p>
          </div>
        </div>
        <a href="login.php?logout=1" style="color: var(--on-surface-variant); padding: 4px;" title="Logout">
          <span class="material-symbols-outlined" style="font-size: 20px;">logout</span>
        </a>
      </div>
    <?php else: ?>
      <div style="display: flex; align-items: center; justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: 12px;">
          <div style="width: 36px; height: 36px; border-radius: 50%; background-color: var(--surface-container-highest); display: flex; align-items: center; justify-content: center;">
            <span class="material-symbols-outlined" style="font-size: 20px;">public</span>
          </div>
          <div>
            <p class="font-body-sm" style="margin: 0; font-weight: 500; color: var(--on-surface);">Aslab View</p>
            <p class="font-label-caps text-on-surface-variant" style="margin: 0; font-size: 10px;">PUBLIC ACCESS</p>
          </div>
        </div>
        <a href="login.php" style="color: var(--primary); padding: 4px;" title="Admin Login">
          <span class="material-symbols-outlined" style="font-size: 20px;">login</span>
        </a>
      </div>
    <?php endif; ?>
  </div>
</div>
