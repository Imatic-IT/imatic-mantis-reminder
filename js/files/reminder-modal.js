$(document).ready(function () {

    $('.modal-recipients-select').select2();

    const toolBox = $('.widget-toolbox a');
    const closeModalButton = $('#imatic-reminder-close-modal')


    toolBox.on('click', function (e) {
        const attributeHref = $(this).attr('href');
        if (attributeHref.includes('imatic_remind_issue.php')) {
            e.preventDefault()

            toggleImaticReminderModal();

        } else {
            return null;
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

