
import './files/reminder-modal.js'
import './files/reminder-actions.js'

flatpickr("#remind_at", {
    enableTime: true,
    dateFormat: "Y-m-d H:i",
    time_24hr: true,
    minuteIncrement: 15,
    locale: 'cs',
    allowInput: true
});