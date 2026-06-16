<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
include 'components/header.php';
?>

<div>
  <h2 class="font-headline-lg" style="margin-bottom: var(--unit-md);">Schedule Allocation</h2>
  
  <div style="display: grid; grid-template-columns: 1fr 2fr; gap: var(--gutter);">
    
    <!-- Allocation Form -->
    <div class="glass-card" style="padding: var(--unit-lg); height: fit-content;">
      <h3 class="font-title-md" style="margin-bottom: var(--unit-md);">Assign Shift</h3>
      <form id="alloc-form" style="display: flex; flex-direction: column; gap: 16px;">
        <div style="display: flex; flex-direction: column; gap: 8px;">
          <label class="font-label-caps">Aslab</label>
          <select id="input-assistant" class="input-field" required>
             <option value="">Loading assistants...</option>
          </select>
        </div>
        <div style="display: flex; flex-direction: column; gap: 8px;">
          <label class="font-label-caps">Day of Week</label>
          <select id="input-day" class="input-field" required>
            <option value="Monday">Monday</option>
            <option value="Tuesday">Tuesday</option>
            <option value="Wednesday">Wednesday</option>
            <option value="Thursday">Thursday</option>
            <option value="Friday">Friday</option>
            <option value="Saturday">Saturday</option>
            <option value="Sunday">Sunday</option>
          </select>
        </div>
        <div style="display: flex; flex-direction: column; gap: 8px;">
          <label class="font-label-caps">Start Time</label>
          <input type="time" id="input-start" class="input-field" required />
        </div>
        <div style="display: flex; flex-direction: column; gap: 8px;">
          <label class="font-label-caps">End Time</label>
          <input type="time" id="input-end" class="input-field" required />
        </div>
        
        <div id="message-box" style="display: none; padding: 12px; border-radius: 4px;"></div>
        
        <input type="hidden" id="input-schedule-id" value="">
        <button type="submit" id="btn-submit" class="btn btn-primary">
           Allocate Shift
        </button>
        <button type="button" id="btn-cancel-edit" class="btn btn-secondary" style="display: none;" onclick="cancelEdit()">
           Cancel Edit
        </button>
      </form>
    </div>

    <!-- Schedule Table -->
    <div class="glass-card" style="grid-column: span 2;">
      <div style="padding: var(--unit-lg); border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; justify-content: space-between; align-items: center;">
        <h3 class="font-title-md" style="margin: 0;">Assigned Lab Shifts</h3>
        <input type="date" id="date-filter" class="input-field" style="width: auto; padding: 4px 8px;" onchange="loadSchedulesForDate(this.value)">
      </div>

      <!-- TIMELINE VIEW -->
      <div class="timeline-container" style="border-radius: 0; border: none; border-bottom: 1px solid var(--outline); margin: 0;">
        <div class="timeline-header-row">
          <div class="timeline-hours">
            <div class="hour-marker">07:00</div>
            <div class="hour-marker">08:00</div>
            <div class="hour-marker">09:00</div>
            <div class="hour-marker">10:00</div>
            <div class="hour-marker">11:00</div>
            <div class="hour-marker">12:00</div>
            <div class="hour-marker">13:00</div>
            <div class="hour-marker">14:00</div>
            <div class="hour-marker">15:00</div>
            <div class="hour-marker">16:00</div>
            <div class="hour-marker">17:00</div>
            <div class="hour-marker">18:00</div>
            <div class="hour-marker">19:00</div>
            <div class="hour-marker">20:00</div>
          </div>
        </div>
        <div id="timeline-body">
            <div style="padding: 24px; text-align: center; color: var(--on-surface-variant);">Loading Timeline...</div>
        </div>
      </div>

      <!-- LIST VIEW -->
      <div style="overflow-x: auto;">
        <table class="table">
          <thead style="background-color: rgba(255,255,255,0.02);">
            <tr>
              <th class="font-label-caps text-on-surface-variant">ASLAB</th>
              <th class="font-label-caps text-on-surface-variant">TIMING</th>
              <th class="font-label-caps text-on-surface-variant" style="text-align: right;">ACTION</th>
            </tr>
          </thead>
          <tbody id="schedule-body" class="font-body-sm">
            <tr><td colspan="3" style="text-align: center;">Loading...</td></tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<script>
let allSchedules = [];

const MIN_HOUR = 7;
const MAX_HOUR = 21;
const TOTAL_HOURS = MAX_HOUR - MIN_HOUR;

let currentSelectedDate = '';

async function loadSchedulesForDate(dateStr) {
    currentSelectedDate = dateStr;
    try {
        const res = await fetch('api/schedules.php?date=' + dateStr);
        allSchedules = await res.json();
        renderTimeline();
        renderTable();
    } catch (err) {
        console.error(err);
        document.getElementById('schedule-body').innerHTML = '<tr><td colspan="3" style="text-align: center; color: #ef4444;">Error loading schedules</td></tr>';
    }
}

function renderTimeline() {
    const tbody = document.getElementById('timeline-body');
    const parts = currentSelectedDate.split('-');
    const selectedDateObj = new Date(parts[0], parts[1] - 1, parts[2]);
    const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    const dayOfWeek = days[selectedDateObj.getDay()];
    
    const filtered = allSchedules.filter(s => s.day_of_week === dayOfWeek);
    
    if (filtered.length === 0) {
        tbody.innerHTML = '<div style="padding: 24px; text-align: center; color: var(--on-surface-variant);">No shifts scheduled for this day.</div>';
        return;
    }

    const now = new Date();
    const currentTotalMinutes = now.getHours() * 60 + now.getMinutes();
    
    const today = new Date();
    today.setHours(0,0,0,0);
    const sDate = new Date(selectedDateObj);
    sDate.setHours(0,0,0,0);
    
    const isToday = sDate.getTime() === today.getTime();
    const isPast = sDate.getTime() < today.getTime();
    const isFuture = sDate.getTime() > today.getTime();

    let html = '';
    
    filtered.forEach(s => {
        const startParts = s.start_time.split(':');
        const endParts = s.end_time.split(':');
        
        const startHour = parseInt(startParts[0]) + (parseInt(startParts[1]) / 60);
        const endHour = parseInt(endParts[0]) + (parseInt(endParts[1]) / 60);
        
        const leftPercent = Math.min(100, Math.max(0, ((startHour - MIN_HOUR) / TOTAL_HOURS) * 100));
        const widthPercent = Math.max(0, Math.min(100 - leftPercent, ((endHour - startHour) / TOTAL_HOURS) * 100));
        
        const startFormatted = s.start_time.substring(0, 5);
        const endFormatted = s.end_time.substring(0, 5);
        
        const startMinutes = parseInt(startParts[0]) * 60 + parseInt(startParts[1]);
        const endMinutes = parseInt(endParts[0]) * 60 + parseInt(endParts[1]);
        
        let statusText = 'Upcoming';
        let statusClass = 'shift-upcoming';
        
        if (s.has_attended_today > 0) {
            statusText = 'Attend';
            statusClass = 'shift-present';
        } else {
            if (isFuture) {
                statusText = 'Upcoming';
                statusClass = 'shift-upcoming';
            } else if (isPast) {
                statusText = 'Absent';
                statusClass = 'shift-absent';
            } else { // isToday
                if (currentTotalMinutes < startMinutes) {
                    statusText = 'Upcoming';
                    statusClass = 'shift-upcoming';
                } else if (currentTotalMinutes >= startMinutes && currentTotalMinutes <= endMinutes) {
                    statusText = 'Pending';
                    statusClass = 'shift-pending';
                } else {
                    statusText = 'Absent';
                    statusClass = 'shift-absent';
                }
            }
        }

        html += `
        <div class="timeline-row">
            <div class="timeline-track">
                <div class="shift-block ${statusClass}" style="left: ${leftPercent}%; width: ${widthPercent}%;" onclick="editSchedule(${s.id}, ${s.assistant_id}, '${s.day_of_week}', '${startFormatted}', '${endFormatted}')" title="Click to edit">
                    <div style="font-weight: bold; margin-bottom: 2px;">${startFormatted}-${endFormatted}</div>
                    <div style="opacity: 0.9; font-size: 10px; margin-bottom: 2px;">${statusText}</div>
                    <div style="opacity: 0.8; font-size: 10px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%;">${s.assistant_name}</div>
                </div>
            </div>
        </div>`;
    });
    
    tbody.innerHTML = html;
}

function renderTable() {
    const tbody = document.getElementById('schedule-body');
    const parts = currentSelectedDate.split('-');
    const selectedDateObj = new Date(parts[0], parts[1] - 1, parts[2]);
    const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    const dayOfWeek = days[selectedDateObj.getDay()];
    
    const filtered = allSchedules.filter(s => s.day_of_week === dayOfWeek);
    
    if (filtered.length === 0) {
      tbody.innerHTML = '<tr><td colspan="3" style="text-align: center;">No shifts scheduled for this day.</td></tr>';
    } else {
      tbody.innerHTML = filtered.map(s => {
        const startFormatted = s.start_time.substring(0, 5); // '15:00'
        const endFormatted = s.end_time.substring(0, 5);
        const timeString = `Every ${s.day_of_week}, ${startFormatted} - ${endFormatted}`;
        
        return `
        <tr>
          <td>
            <div style="display: flex; flex-direction: column;">
              <span style="font-weight: 500;">${s.assistant_name}</span>
              <span class="font-mono-data text-on-surface-variant" style="font-size: 10px;">${s.nim}</span>
            </div>
          </td>
          <td class="font-mono-data" style="font-size: 11px;">${timeString}</td>
          <td style="text-align: right;">
            <button onclick="editSchedule(${s.id}, ${s.assistant_id}, '${s.day_of_week}', '${startFormatted}', '${endFormatted}')" class="btn btn-secondary" style="padding: 4px 8px; color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.2); margin-right: 4px;">
              <span class="material-symbols-outlined" style="font-size: 16px;">edit</span>
            </button>
            <button onclick="deleteSchedule(${s.id})" class="btn btn-secondary" style="padding: 4px 8px; color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2);">
              <span class="material-symbols-outlined" style="font-size: 16px;">delete</span>
            </button>
          </td>
        </tr>
      `}).join('');
    }
}

async function loadSchedules() {
    const today = new Date();
    const tzOffset = today.getTimezoneOffset() * 60000;
    const localISOTime = (new Date(today - tzOffset)).toISOString().split('T')[0];
    
    const dateInput = document.getElementById('date-filter');
    if (dateInput) dateInput.value = localISOTime;
    
    await loadSchedulesForDate(localISOTime);
}

async function loadAssistants() {
  try {
    const res = await fetch('api/assistants.php');
    const assistants = await res.json();
    const select = document.getElementById('input-assistant');
    select.innerHTML = '<option value="">-- Select Aslab --</option>' + 
      assistants.map(a => `<option value="${a.id}">${a.name} (${a.nim})</option>`).join('');
  } catch (err) {
    console.error(err);
  }
}

function editSchedule(id, assistant_id, day, start, end) {
    document.getElementById('input-schedule-id').value = id;
    document.getElementById('input-assistant').value = assistant_id;
    document.getElementById('input-day').value = day;
    document.getElementById('input-start').value = start;
    document.getElementById('input-end').value = end;
    
    document.getElementById('btn-submit').innerText = "Update Shift";
    document.getElementById('btn-cancel-edit').style.display = "block";
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function cancelEdit() {
    document.getElementById('input-schedule-id').value = '';
    document.getElementById('alloc-form').reset();
    document.getElementById('btn-submit').innerText = "Allocate Shift";
    document.getElementById('btn-cancel-edit').style.display = "none";
}

async function deleteSchedule(id) {
    if (!confirm("Are you sure you want to remove this shift?")) return;
    try {
        const res = await fetch('api/delete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ type: 'schedule', id: id })
        });
        if (res.ok) {
            loadSchedules();
        } else {
            alert("Failed to delete shift.");
        }
    } catch (err) {
        console.error(err);
    }
}

document.addEventListener('DOMContentLoaded', async () => {
  const form = document.getElementById('alloc-form');
  const messageBox = document.getElementById('message-box');
  const btnSubmit = document.getElementById('btn-submit');
  // Init complete

  await loadAssistants();
  await loadSchedules();

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    btnSubmit.disabled = true;
    
    const scheduleId = document.getElementById('input-schedule-id').value;
    
    const payload = {
      id: scheduleId ? scheduleId : null,
      assistant_id: document.getElementById('input-assistant').value,
      day_of_week: document.getElementById('input-day').value,
      start_time: document.getElementById('input-start').value + ':00',
      end_time: document.getElementById('input-end').value + ':00'
    };

    try {
      const res = await fetch('api/schedules.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      
      messageBox.style.display = 'block';
      if (res.ok) {
        messageBox.style.backgroundColor = 'rgba(34, 197, 94, 0.2)';
        messageBox.style.color = '#22c55e';
        messageBox.innerText = scheduleId ? "Shift updated successfully!" : "Shift allocated successfully!";
        cancelEdit();
        loadSchedules();
        setTimeout(() => messageBox.style.display = 'none', 3000);
      } else {
        messageBox.style.display = 'block';
        messageBox.style.backgroundColor = 'rgba(239, 68, 68, 0.2)';
        messageBox.style.color = '#ef4444';
        messageBox.innerText = data.error || "Allocation failed.";
      }
    } catch (err) {
      messageBox.style.display = 'block';
      messageBox.style.backgroundColor = 'rgba(239, 68, 68, 0.2)';
      messageBox.style.color = '#ef4444';
      messageBox.innerText = "Network error.";
    }
    btnSubmit.disabled = false;
  });
});
</script>

<style>
.timeline-container {
    background-color: var(--surface);
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid var(--outline);
    margin-bottom: var(--gutter);
}
.timeline-header-row {
    border-bottom: 1px solid var(--outline);
    background-color: rgba(255,255,255,0.02);
}
.timeline-hours {
    display: flex;
    position: relative;
}
.hour-marker {
    flex: 1;
    border-right: 1px solid rgba(255,255,255,0.05);
    padding: 8px;
    text-align: center;
    font-size: 11px;
    color: var(--on-surface-variant);
    font-weight: 500;
}
.hour-marker:last-child {
    border-right: none;
}
.timeline-row {
    border-bottom: 1px solid var(--outline);
}
.timeline-row:last-child {
    border-bottom: none;
}
.timeline-track {
    position: relative;
    display: flex;
    height: 64px;
    background-image: linear-gradient(to right, rgba(255,255,255,0.05) 1px, transparent 1px);
    background-size: calc(100% / 14) 100%; /* 14 columns */
}
.shift-block {
    position: absolute;
    top: 8px;
    bottom: 8px;
    background-color: var(--primary);
    border-radius: 6px;
    align-items: flex-start;
    justify-content: center;
    padding: 4px 8px;
    font-size: 11px;
    color: white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    overflow: hidden;
    line-height: 1.2;
    cursor: pointer;
}
.shift-upcoming {
    background-color: #f59e0b;
    box-shadow: 0 2px 8px rgba(245, 158, 11, 0.4);
}
.shift-pending {
    background-color: #eab308;
    box-shadow: 0 2px 8px rgba(234, 179, 8, 0.4);
}
.shift-present {
    background-color: #10b981;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.4);
}
.shift-absent {
    background-color: #ef4444;
    box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
}
.shift-block:hover {
    filter: brightness(1.2);
}
</style>

<?php include 'components/footer.php'; ?>
