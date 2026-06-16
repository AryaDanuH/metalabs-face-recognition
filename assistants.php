<?php include 'components/header.php'; ?>

<div>
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--unit-md);">
    <h2 class="font-headline-lg" style="margin: 0;">Aslab Management</h2>
    <?php if ($isAdmin): ?>
    <a href="register.php" class="btn btn-primary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
      <span class="material-symbols-outlined">add</span> Register Aslab
    </a>
    <?php endif; ?>
  </div>
  
  <div class="glass-card">
    <div style="padding: var(--unit-lg); border-bottom: 1px solid rgba(255,255,255,0.05);">
      <h3 class="font-title-md" style="margin: 0;">Registered Personnel</h3>
    </div>
    <div style="overflow-x: auto;">
      <table class="table">
        <thead>
          <tr>
            <th class="font-label-caps text-on-surface-variant">NIM</th>
            <th class="font-label-caps text-on-surface-variant">NAME</th>
            <th class="font-label-caps text-on-surface-variant">DIVISION</th>
            <th class="font-label-caps text-on-surface-variant">ROLE</th>
            <?php if (isset($isAdmin) && $isAdmin): ?>
              <th class="font-label-caps text-on-surface-variant" style="text-align: right;">ACTION</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody id="assistants-body" class="font-body-sm">
          <tr><td colspan="<?= (isset($isAdmin) && $isAdmin) ? '5' : '4' ?>" style="text-align: center;">Loading...</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
const IS_ADMIN = <?= (isset($isAdmin) && $isAdmin) ? 'true' : 'false' ?>;

async function loadAssistants() {
  try {
    const res = await fetch('api/assistants.php');
    const data = await res.json();
    const tbody = document.getElementById('assistants-body');
    
    if (data.length === 0) {
      tbody.innerHTML = `<tr><td colspan="${IS_ADMIN ? '5' : '4'}" style="text-align: center;">No aslabs found.</td></tr>`;
    } else {
      tbody.innerHTML = data.map(a => `
        <tr>
          <td class="font-mono-data">${a.nim}</td>
          <td><p style="margin: 0; font-weight: 500;">${a.name}</p></td>
          <td>${a.division_name || 'N/A'}</td>
          <td>
            <div class="badge ${a.role === 'admin' ? 'badge-primary' : 'badge-secondary'}">
              ${a.role === 'assistant' ? 'ASLAB' : a.role.toUpperCase()}
            </div>
          </td>
          ${IS_ADMIN ? `
          <td style="text-align: right;">
            <button onclick="deleteAssistant(${a.id})" class="btn btn-secondary" style="padding: 4px 8px; color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2);">
              <span class="material-symbols-outlined" style="font-size: 16px;">delete</span>
            </button>
          </td>` : ''}
        </tr>
      `).join('');
    }
  } catch (err) {
    console.error(err);
    document.getElementById('assistants-body').innerHTML = `<tr><td colspan="${IS_ADMIN ? '5' : '4'}" style="text-align: center; color: red;">Error loading data</td></tr>`;
  }
}

async function deleteAssistant(id) {
    if (!confirm("Are you sure you want to remove this aslab? All their schedule data will also be deleted!")) return;
    try {
        const res = await fetch('api/delete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ type: 'assistant', id: id })
        });
        if (res.ok) {
            loadAssistants();
        } else {
            alert("Failed to delete aslab. Unauthorized or network error.");
        }
    } catch (err) {
        console.error(err);
    }
}

document.addEventListener('DOMContentLoaded', loadAssistants);
</script>

<?php include 'components/footer.php'; ?>
