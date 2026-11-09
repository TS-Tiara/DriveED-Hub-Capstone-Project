<script>
(function () {
    document.querySelectorAll('form').forEach(form => {
        const grade = form.querySelector('[name="session_grade"]');
        if (!grade) return;
        const update = () => {
            const attendance = form.querySelector('[name="attendance_status"]')?.value;
            const status = form.querySelector('[name="status"]')?.value;
            grade.required = attendance !== 'absent' && !['scheduled', 'cancelled'].includes(status);
        };
        form.addEventListener('change', update);
        update();
    });
})();
</script>
