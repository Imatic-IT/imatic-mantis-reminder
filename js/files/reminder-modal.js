$(document).ready(function () {

    $('.modal-recipients-select').select2();

    const toolBox = $('.widget-toolbox a');
    const closeModalButton = $('#imatic-reminder-close-modal')

    toolBox.each(function () {
        const attributeHref = $(this).attr('href');
        if (attributeHref.includes('imatic_remind_issue.php')) {
            const reminderCount = $('#count-reminders').data('count');
            if (reminderCount > 0) {
                $(this).removeClass('btn-primary').addClass('btn-warning');
            }

            $(this).on('click', function (e) {
                e.preventDefault()
                toggleImaticReminderModal();
            })
        }
    })

    function toggleImaticReminderModal() {
        const imaticReminderModal = $('#imatic-reminder-modal');
        imaticReminderModal.toggleClass("show");
    }

    closeModalButton.on('click', function (e) {
        e.preventDefault()
        toggleImaticReminderModal();
    })


    $('.close').on('click', function () {
        toggleImaticReminderModal();
    });

    $('.other-button').on('click', function () {
        toggleImaticReminderModal();
    });
});

