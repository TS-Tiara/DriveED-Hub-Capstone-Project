@php
    $batchChoices = $batchSlots->filter(fn ($slot) => $slot->batch_group)->map(fn ($slot) => [
        'id' => $slot->id, 'group' => $slot->batch_group, 'day' => $slot->batch_day_number,
        'date' => $slot->date->format('Y-m-d'), 'start' => substr($slot->start_time, 0, 5), 'end' => substr($slot->end_time, 0, 5),
    ])->values();
@endphp
<dialog id="batchSchedulePicker" aria-labelledby="batchScheduleTitle" style="margin:auto;inset:0;position:fixed;width:min(520px,94vw);max-height:85dvh;overflow:auto;padding:24px;border:1px solid #d1d5db;border-radius:12px;">
    <form method="dialog">
        <h2 id="batchScheduleTitle">Choose batch schedules</h2>
        <p>This schedule belongs to a sequential batch. Choose one, all, or selected dates.</p>
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin:12px 0;">
            <button type="button" class="btn btn-secondary" onclick="setBatchSelection('one')">This schedule only</button>
            <button type="button" class="btn btn-secondary" onclick="setBatchSelection('all')">Select all</button>
        </div>
        <div id="batchScheduleOptions" style="display:grid;gap:12px;"></div>
        <p id="batchScheduleError" role="alert" style="color:#b91c1c;"></p>
        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" value="continue" class="btn btn-primary">Continue</button>
            <button type="submit" value="cancel" formnovalidate class="btn btn-secondary">Cancel</button>
        </div>
    </form>
</dialog>
<script>
window.batchScheduleChoices = @json($batchChoices);
window.pickBatchSchedules = function (slotId) {
    const slot = window.batchScheduleChoices.find(s => s.id === Number(slotId));
    if (!slot) return Promise.resolve([Number(slotId)]);
    const group = window.batchScheduleChoices.filter(s => s.group === slot.group);
    const dialog = document.getElementById('batchSchedulePicker');
    dialog.dataset.slot = slotId;
    document.getElementById('batchScheduleError').textContent = '';
    const options = document.getElementById('batchScheduleOptions');
    options.replaceChildren();
    group.forEach(s => {
        const label = document.createElement('label'), input = document.createElement('input');
        input.type = 'checkbox'; input.value = s.id; input.checked = s.id === Number(slotId);
        label.append(input, document.createTextNode(` Day ${s.day}: ${s.date}, ${s.start}–${s.end}`));
        options.append(label);
    });
    dialog.returnValue = '';
    dialog.querySelector('form').onsubmit = event => {
        if (event.submitter?.value === 'continue' && !options.querySelector('input:checked')) {
            event.preventDefault(); document.getElementById('batchScheduleError').textContent = 'Choose at least one schedule.';
        }
    };
    dialog.showModal();
    return new Promise(resolve => dialog.addEventListener('close', () => resolve(dialog.returnValue === 'continue' ? [...options.querySelectorAll('input:checked')].map(i => Number(i.value)) : null), {once:true}));
};
window.setBatchSelection = function (mode) {
    const dialog = document.getElementById('batchSchedulePicker');
    dialog.querySelectorAll('input').forEach(i => i.checked = mode === 'all' || i.value === dialog.dataset.slot);
};
</script>
