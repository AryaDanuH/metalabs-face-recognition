<?php include 'components/header.php'; ?>

<style>
.timeline-container {
    background-color: var(--surface);
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid var(--outline);
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
</style>

<div>
  <div style="display: flex; gap: var(--unit-md); align-items: center; margin-bottom: var(--unit-lg);">
    <label class="font-label-caps">Date</label>
    <input type="date" id="date-filter" class="input-field" onchange="loadSchedulesForDate(this.value)" style="width: auto;">
  </div>
  
  <div class="timeline-container">
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
    <div id="schedule-body">
        <div style="padding: 24px; text-align: center; color: var(--on-surface-variant);">Loading...</div>
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
    const res = await fetch('api/schedules.php?date=' + dateStr);
    allSchedules = await res.json();
    renderTimeline();
}

function renderTimeline() {
    const tbody = document.getElementById('schedule-body');
    
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
                <div class="shift-block ${statusClass}" style="left: ${leftPercent}%; width: ${widthPercent}%;">
                    <div style="font-weight: bold; margin-bottom: 2px;">${s.start_time.substring(0,5)}-${s.end_time.substring(0,5)}</div>
                    <div style="opacity: 0.9; font-size: 10px; margin-bottom: 2px;">${statusText}</div>
                    <div style="opacity: 0.8; font-size: 10px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%;">ASLAB: ${s.assistant_name}</div>
                </div>
            </div>
        </div>`;
    });
    
    tbody.innerHTML = html;
}

document.addEventListener('DOMContentLoaded', async () => {
    try {
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        const todayStr = `${yyyy}-${mm}-${dd}`;
        
        document.getElementById('date-filter').value = todayStr;
        await loadSchedulesForDate(todayStr);
    } catch (err) {
        console.error(err);
        document.getElementById('schedule-body').innerHTML = '<div style="padding: 24px; text-align: center; color: #ef4444;">Error loading schedules</div>';
    }
});
</script>

<?php include 'components/footer.php'; ?>
