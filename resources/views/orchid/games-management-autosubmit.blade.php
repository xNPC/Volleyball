<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selects = document.querySelectorAll('select[name="tournament_id"], select[name="stage_id"], select[name="group_id"]');

        selects.forEach(select => {
            select.addEventListener('change', function() {
                this.form.submit();
            });
        });
    });
</script>
