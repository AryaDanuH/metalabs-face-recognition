<?php include 'components/header.php'; ?>

<div id="dashboard">
  <section style="margin-bottom: var(--gutter);">
    <div class="glass-card" style="padding: var(--unit-lg); height: 256px; position: relative; display: flex; flex-direction: column; justify-content: flex-end; overflow: hidden; border-radius: 12px;">
      <div style="position: absolute; inset: 0; background: linear-gradient(to top, var(--surface), transparent);"></div>
      <div style="position: relative; z-index: 10; display: flex; justify-content: space-between; align-items: flex-end; gap: var(--unit-lg);">
        <div>
          <div class="badge badge-primary" style="margin-bottom: var(--unit-sm);">LIVE STATUS</div>
          <h2 class="font-headline-lg" style="margin-bottom: var(--unit-xs);">Today's Active Duty</h2>
          <p class="font-body-base text-on-surface-variant" style="margin: 0;"><span id="active-assistants">...</span> Laboratory Aslabs currently active.</p>
        </div>
      </div>
    </div>
  </section>

  <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--gutter); margin-bottom: var(--gutter);">
    <div class="glass-card" style="padding: var(--unit-lg); display: flex; flex-direction: column; gap: var(--unit-sm); border-radius: 12px;">
      <div style="display: flex; align-items: center; justify-content: space-between;">
        <span class="font-label-caps text-on-surface-variant">ACTIVE ASLABS</span>
        <span class="material-symbols-outlined text-primary">sensors</span>
      </div>
      <div style="display: flex; align-items: flex-end; gap: 8px;">
        <span class="font-display-lg" id="stat-active" style="margin: 0; line-height: 1;">...</span>
      </div>
    </div>
    
    <div class="glass-card" style="padding: var(--unit-lg); display: flex; flex-direction: column; gap: var(--unit-sm); border-radius: 12px;">
      <div style="display: flex; align-items: center; justify-content: space-between;">
        <span class="font-label-caps text-on-surface-variant">ATTENDANCE RATE</span>
        <span class="material-symbols-outlined text-primary">analytics</span>
      </div>
      <div style="display: flex; align-items: flex-end; gap: 8px;">
        <span class="font-display-lg" id="stat-rate" style="margin: 0; line-height: 1;">...</span>
      </div>
    </div>

    <div class="glass-card" style="padding: var(--unit-lg); display: flex; flex-direction: column; gap: var(--unit-sm); border-radius: 12px;">
      <div style="display: flex; align-items: center; justify-content: space-between;">
        <span class="font-label-caps text-on-surface-variant">PENDING ABSENCES</span>
        <span class="material-symbols-outlined text-primary">event_busy</span>
      </div>
      <div style="display: flex; align-items: flex-end; gap: 8px;">
        <span class="font-display-lg" id="stat-pending" style="margin: 0; line-height: 1;">...</span>
      </div>
    </div>
  </div>

  <div style="display: grid; grid-template-columns: 2fr 1fr; gap: var(--gutter);">
    <div class="glass-card" style="display: flex; flex-direction: column; border-radius: 12px; overflow: hidden;">
      <div style="padding: var(--unit-lg); border-bottom: 1px solid rgba(255,255,255,0.05);">
        <h3 class="font-title-md" style="margin: 0;">Recent Attendance</h3>
      </div>
      <div style="overflow-x: auto;">
        <table class="table">
          <thead>
            <tr>
              <th class="font-label-caps text-on-surface-variant">SUBJECT</th>
              <th class="font-label-caps text-on-surface-variant">TIMESTAMP</th>
              <th class="font-label-caps text-on-surface-variant">STATUS</th>
            </tr>
          </thead>
          <tbody id="logs-body" class="font-body-sm">
            <tr><td colspan="3" style="text-align: center;">Loading...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
  // Silent cron run for absences
  fetch('api/cron.php').catch(err => console.error("Cron failed:", err));
  
  try {
    const statsRes = await fetch('api/stats.php');
    const statsData = await statsRes.json();
    document.getElementById('stat-active').innerText = statsData.activeAssistants;
    document.getElementById('active-assistants').innerText = statsData.activeAssistants;
    document.getElementById('stat-rate').innerText = statsData.attendanceRate;
    document.getElementById('stat-pending').innerText = statsData.pendingAbsences;

    const logsRes = await fetch('api/attendance.php');
    const logsData = await logsRes.json();
    const tbody = document.getElementById('logs-body');
    
    if (logsData.length === 0) {
      tbody.innerHTML = '<tr><td colspan="3" style="text-align: center;">No logs found.</td></tr>';
    } else {
      tbody.innerHTML = logsData.map(log => {
        let color = log.status === 'on_time' ? '#22c55e' : (log.status === 'late' ? '#f59e0b' : '#ef4444');
        return `
          <tr>
            <td>
              <div style="display: flex; align-items: center; gap: var(--unit-md);">
                <div>
                  <p style="margin: 0; font-weight: 500;">${log.name}</p>
                  <p class="font-mono-data text-on-surface-variant" style="margin: 0; font-size: 10px; opacity: 0.6;">ID: ${log.nim}</p>
                </div>
              </div>
            </td>
            <td class="font-mono-data">${new Date(log.timestamp).toLocaleTimeString()}</td>
            <td>
              <div style="display: flex; align-items: center; gap: 8px;">
                <div class="status-pulse" style="width: 8px; height: 8px; border-radius: 50%; background-color: ${color};"></div>
                <span class="font-label-caps" style="font-size: 10px;">${log.status.toUpperCase()}</span>
              </div>
            </td>
          </tr>
        `;
      }).join('');
    }
  } catch (err) {
    console.error(err);
  }
});
</script>

<?php include 'components/footer.php'; ?>
