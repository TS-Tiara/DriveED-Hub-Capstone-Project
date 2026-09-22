@php
    $calendarId = $calendarId ?? 'schedule-calendar';
    $calendarEvents = $calendarEvents ?? [];
    $calendarPrimary = $calendarPrimary ?? '#3498db';
    $calendarSecondary = $calendarSecondary ?? '#2ecc71';
@endphp

<div id="{{ $calendarId }}" class="schedule-calendar" data-events='{{ json_encode($calendarEvents, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}'>
    <div class="schedule-calendar-header">
        <div class="schedule-calendar-month-nav">
            <button type="button" class="schedule-calendar-nav" data-calendar-action="previous" aria-label="Previous month">&#8249;</button>
            <h2 class="schedule-calendar-month"></h2>
            <button type="button" class="schedule-calendar-nav" data-calendar-action="next" aria-label="Next month">&#8250;</button>
        </div>
        <button type="button" class="schedule-calendar-today" data-calendar-action="today">Today</button>
    </div>
    <div class="schedule-calendar-grid schedule-calendar-weekdays" aria-hidden="true">
        @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $dayName)
            <div>{{ $dayName }}</div>
        @endforeach
    </div>
    <div class="schedule-calendar-grid schedule-calendar-days"></div>
    <div class="schedule-calendar-legend" aria-label="Calendar legend">
        <span class="schedule-calendar-legend-title">Legend</span>
        <span><i class="schedule-calendar-legend-swatch scheduled"></i>Scheduled</span>
        <span><i class="schedule-calendar-legend-swatch available"></i>Available</span>
        <span><i class="schedule-calendar-legend-swatch completed"></i>Completed</span>
        <span><i class="schedule-calendar-legend-swatch pending"></i>Pending</span>
    </div>
    <div class="schedule-calendar-details" hidden>
        <div class="schedule-calendar-details-heading"></div>
        <div class="schedule-calendar-details-list"></div>
    </div>
</div>

<style>
    .schedule-calendar { --schedule-calendar-primary: {{ $calendarPrimary }}; --schedule-calendar-secondary: {{ $calendarSecondary }}; }
    .schedule-calendar-header { display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:16px; padding:14px 18px; color:#fff; background:linear-gradient(135deg, var(--schedule-calendar-primary), var(--schedule-calendar-secondary)); border-radius:10px; }
    .schedule-calendar-month-nav { display:flex; align-items:center; gap:14px; }
    .schedule-calendar-month { min-width:170px; margin:0; color:#fff; text-align:center; font-size:1.25rem; }
    .schedule-calendar-nav, .schedule-calendar-today { border:1px solid rgba(255,255,255,.35); border-radius:6px; padding:7px 12px; color:#fff; background:rgba(255,255,255,.14); cursor:pointer; font-weight:600; }
    .schedule-calendar-nav { width:34px; padding:5px; font-size:1.35rem; line-height:1; }
    .schedule-calendar-nav:hover, .schedule-calendar-today:hover { background:rgba(255,255,255,.28); }
    .schedule-calendar-grid { display:grid; grid-template-columns:repeat(7, minmax(0, 1fr)); gap:8px; }
    .schedule-calendar-weekdays { margin-bottom:8px; color:#64748b; font-size:.78rem; font-weight:700; text-align:center; text-transform:uppercase; }
    .schedule-calendar-weekdays div { padding:5px; }
    .schedule-calendar-day { min-height:118px; padding:9px; border:1px solid #e2e8f0; border-radius:9px; background:#fff; }
    .schedule-calendar-day.is-outside { background:#f8fafc; color:#94a3b8; }
    .schedule-calendar-day.is-today { border:2px solid var(--schedule-calendar-primary); padding:8px; }
    .schedule-calendar-day-number { display:flex; justify-content:space-between; align-items:center; margin-bottom:7px; color:#1e293b; font-size:.85rem; font-weight:700; }
    .schedule-calendar-today-label { padding:2px 5px; border-radius:10px; color:#fff; background:var(--schedule-calendar-primary); font-size:.6rem; }
    .schedule-calendar-event { display:block; width:100%; overflow:hidden; margin-top:4px; padding:4px 6px; border:0; border-radius:4px; color:#fff; background:#10b981; text-align:left; font-size:.7rem; font-weight:700; white-space:nowrap; text-overflow:ellipsis; cursor:pointer; }
    .schedule-calendar-event.is-available { background:#2563eb; }
    .schedule-calendar-event.is-completed { background:#64748b; }
    .schedule-calendar-event.is-pending { background:#f59e0b; }
    .schedule-calendar-more { margin-top:4px; color:#64748b; font-size:.7rem; font-weight:700; }
    .schedule-calendar-legend { display:flex; align-items:center; flex-wrap:wrap; gap:14px; margin-top:16px; padding:12px 14px; border:1px solid #e2e8f0; border-radius:8px; background:#f8fafc; color:#64748b; font-size:.8rem; font-weight:600; }
    .schedule-calendar-legend-title { color:#1e293b; font-weight:700; }
    .schedule-calendar-legend span:not(.schedule-calendar-legend-title) { display:inline-flex; align-items:center; gap:6px; }
    .schedule-calendar-legend-swatch { width:12px; height:12px; display:inline-block; border-radius:3px; background:#10b981; }
    .schedule-calendar-legend-swatch.available { background:#2563eb; }
    .schedule-calendar-legend-swatch.completed { background:#64748b; }
    .schedule-calendar-legend-swatch.pending { background:#f59e0b; }
    .schedule-calendar-details { margin-top:16px; padding:14px 16px; border:1px solid #e2e8f0; border-radius:9px; background:#f8fafc; }
    .schedule-calendar-details-heading { margin-bottom:9px; color:#1e293b; font-weight:700; }
    .schedule-calendar-details-list { display:grid; gap:6px; }
    .schedule-calendar-detail { padding:8px 10px; border-left:4px solid var(--schedule-calendar-primary); border-radius:4px; background:#fff; color:#334155; font-size:.85rem; }
    @media (max-width:700px) { .schedule-calendar-grid { gap:3px; } .schedule-calendar-day { min-height:82px; padding:5px; } .schedule-calendar-day.is-today { padding:4px; } .schedule-calendar-month { min-width:135px; font-size:1rem; } .schedule-calendar-event { padding:3px 4px; font-size:.62rem; } }
</style>

<script>
(function () {
    const calendar = document.getElementById(@json($calendarId));
    if (!calendar || calendar.dataset.initialized === 'true') return;
    calendar.dataset.initialized = 'true';

    const events = JSON.parse(calendar.dataset.events || '[]');
    const monthLabel = calendar.querySelector('.schedule-calendar-month');
    const daysGrid = calendar.querySelector('.schedule-calendar-days');
    const details = calendar.querySelector('.schedule-calendar-details');
    const detailsHeading = calendar.querySelector('.schedule-calendar-details-heading');
    const detailsList = calendar.querySelector('.schedule-calendar-details-list');
    let displayedMonth = new Date();

    const pad = value => String(value).padStart(2, '0');
    const dateKey = date => `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
    const eventsByDate = events.reduce((groups, event) => {
        (groups[event.date] ||= []).push(event);
        return groups;
    }, {});

    function render() {
        const year = displayedMonth.getFullYear();
        const month = displayedMonth.getMonth();
        monthLabel.textContent = displayedMonth.toLocaleDateString(undefined, { month: 'long', year: 'numeric' });
        daysGrid.innerHTML = '';
        const firstDay = new Date(year, month, 1);
        const gridStart = new Date(year, month, 1 - firstDay.getDay());

        for (let index = 0; index < 42; index++) {
            const date = new Date(gridStart);
            date.setDate(gridStart.getDate() + index);
            const key = dateKey(date);
            const dayEvents = eventsByDate[key] || [];
            const cell = document.createElement('div');
            cell.className = 'schedule-calendar-day';
            if (date.getMonth() !== month) cell.classList.add('is-outside');
            if (key === dateKey(new Date())) cell.classList.add('is-today');

            const number = document.createElement('div');
            number.className = 'schedule-calendar-day-number';
            number.textContent = date.getDate();
            if (key === dateKey(new Date())) {
                const today = document.createElement('span');
                today.className = 'schedule-calendar-today-label';
                today.textContent = 'Today';
                number.appendChild(today);
            }
            cell.appendChild(number);

            dayEvents.slice(0, 3).forEach(event => {
                const item = document.createElement('button');
                item.type = 'button';
                item.className = 'schedule-calendar-event' + (event.status === 'available' ? ' is-available' : '') + (event.status === 'completed' ? ' is-completed' : '') + (event.status === 'pending' ? ' is-pending' : '');
                item.textContent = `${event.time} ${event.title}`;
                item.title = `${event.time} ${event.title}`;
                item.addEventListener('click', () => showDetails(key));
                cell.appendChild(item);
            });
            if (dayEvents.length > 3) {
                const more = document.createElement('div');
                more.className = 'schedule-calendar-more';
                more.textContent = `+${dayEvents.length - 3} more`;
                cell.appendChild(more);
            }
            if (dayEvents.length) cell.addEventListener('click', () => showDetails(key));
            daysGrid.appendChild(cell);
        }
    }

    function showDetails(key) {
        const dayEvents = eventsByDate[key] || [];
        if (!dayEvents.length) return;
        details.hidden = false;
        detailsHeading.textContent = new Date(`${key}T00:00:00`).toLocaleDateString(undefined, { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
        detailsList.innerHTML = '';
        dayEvents.forEach(event => {
            const item = document.createElement('div');
            item.className = 'schedule-calendar-detail';
            const time = document.createElement('strong');
            time.textContent = event.time;
            item.appendChild(time);
            item.appendChild(document.createTextNode(` ${event.title}`));
            if (event.meta) item.appendChild(document.createTextNode(` ${event.meta}`));
            detailsList.appendChild(item);
        });
    }

    calendar.querySelector('[data-calendar-action="previous"]').addEventListener('click', () => { displayedMonth.setMonth(displayedMonth.getMonth() - 1); render(); });
    calendar.querySelector('[data-calendar-action="next"]').addEventListener('click', () => { displayedMonth.setMonth(displayedMonth.getMonth() + 1); render(); });
    calendar.querySelector('[data-calendar-action="today"]').addEventListener('click', () => { displayedMonth = new Date(); render(); });
    render();
})();
</script>