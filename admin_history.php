<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
include 'components/header.php';
require_once 'api/db.php';

$stmt = $pdo->query("
    SELECT att.id, att.timestamp, att.method, att.status, a.name, a.nim 
    FROM attendance_logs att 
    JOIN assistants a ON att.assistant_id = a.id 
    ORDER BY att.timestamp DESC
");
$logs = $stmt->fetchAll();
?>

<div>
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--unit-md);">
    <h2 class="font-headline-lg" style="margin: 0;">Attendance History</h2>
    <a href="api/export_history.php" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px;">
      <span class="material-symbols-outlined">download</span> Export to Excel
    </a>
  </div>
  
  <div class="glass-card">
    <div style="padding: var(--unit-lg); border-bottom: 1px solid rgba(255,255,255,0.05);">
      <h3 class="font-title-md" style="margin: 0;">Full System Logs</h3>
    </div>
    <div style="overflow-x: auto;">
      <table class="table">
        <thead>
          <tr>
            <th class="font-label-caps text-on-surface-variant">ASLAB</th>
            <th class="font-label-caps text-on-surface-variant">TIMESTAMP</th>
            <th class="font-label-caps text-on-surface-variant">STATUS</th>
          </tr>
        </thead>
        <tbody class="font-body-sm">
          <?php if (empty($logs)): ?>
            <tr><td colspan="3" style="text-align: center;">No attendance logs found.</td></tr>
          <?php else: ?>
            <?php foreach ($logs as $log): ?>
              <tr>
                <td>
                  <div style="display: flex; flex-direction: column;">
                    <span style="font-weight: 500;"><?= htmlspecialchars($log['name']) ?></span>
                    <span class="font-mono-data text-on-surface-variant" style="font-size: 10px;"><?= htmlspecialchars($log['nim']) ?></span>
                  </div>
                </td>
                <td class="font-mono-data"><?= date('M d, Y H:i:s', strtotime($log['timestamp'])) ?></td>
                <td>
                  <?php
                    $color = '#ef4444';
                    if ($log['status'] === 'on_time') $color = '#10b981';
                    else if ($log['status'] === 'late') $color = '#eab308';
                  ?>
                  <div style="display: flex; align-items: center; gap: 8px;">
                    <div class="status-pulse" style="width: 8px; height: 8px; border-radius: 50%; background-color: <?= $color ?>;"></div>
                    <span class="font-label-caps" style="font-size: 10px;"><?= strtoupper($log['status']) ?></span>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const res = await fetch('api/cron.php');
        const data = await res.json();
        if (data.absences_logged > 0) {
            window.location.reload();
        }
    } catch (err) {
        console.error("Cron failed:", err);
    }
});
</script>

<?php include 'components/footer.php'; ?>
