<?php

require_api('authentication_api.php');

auth_ensure_user_authenticated();

if (isset($_POST) && !empty($_POST) && isset($_POST['id']) && !empty($_POST['id'] && isset($_POST['user_id']) && !empty($_POST['user_id'] && isset($_POST['remind_at']) && !empty($_POST['remind_at'])))) {

    $affected_row = plugin_get()->imaticReminderUpdateIssueReminder($_POST['id'], $_POST['user_id'], $_POST['remind_at'], $_POST['message']);

    if ($affected_row == 0) {
        $response = [
            'success' => false,
            'status' => 404,
            'message' => lang_get('imatic_reminder_reminder_edit_error')
        ];
        plugin_get()->imaticReminderSendResponse(404, $response);
    }

    $response = [
        'success' => true,
        'status' => 200,
        'message' => lang_get('imatic_reminder_reminder_edit_success')
    ];

    plugin_get()->imaticReminderSendResponse(200, $response);
} else {

    $response = [
        'success' => false,
        'status' => 404,
        'message' => lang_get('imatic_reminder_reminder_not_permission')
    ];
    plugin_get()->imaticReminderSendResponse(404, $response);

}





