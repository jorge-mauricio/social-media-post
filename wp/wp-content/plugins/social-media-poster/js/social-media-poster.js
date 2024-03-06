document.addEventListener('DOMContentLoaded', function () {
    flatpickr("input[name='schedule']", {
        enableTime: true,
        dateFormat: "Y-m-d H:i",
        minDate: "today",
        time_24hr: true
    });
});