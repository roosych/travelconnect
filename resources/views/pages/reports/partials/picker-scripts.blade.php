{{-- Shared init for report filter bars: flatpickr dates + select2 agency picker. --}}
<script>
    // Date inputs — flatpickr. No minDate: reports look back in time.
    // altInput shows d.m.Y while the real input keeps Y-m-d for the GET query.
    document.querySelectorAll('.js-report-date').forEach(function (el) {
        flatpickr(el, {
            dateFormat:    'Y-m-d',
            altInput:      true,
            altFormat:     'd.m.Y',
            allowInput:    false,
            disableMobile: true,
        });
    });

    // Agency picker — select2 with search by name.
    $('.js-report-agency').select2({
        placeholder: @json(__('reports.all_agencies')),
        allowClear:  true,
        width:       '100%',
    });
</script>
