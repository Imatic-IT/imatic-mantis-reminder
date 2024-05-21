<?php

require_api('authentication_api.php');

auth_ensure_user_authenticated();

if (isset($_POST) && !empty($_POST) && isset($_POST['id']) && !empty($_POST['id'])) {


    $affected_row = plugin_get()->imaticReminderDeleteIssueReminder($_POST['id']);

    if ($affected_row == 0) {
        $response = [
            'success' => false,
            'status' => 404,
            'message' => lang_get('imatic_reminder_reminder_delete_error')
        ];
        plugin_get()->imaticReminderSendResponse(404, $response);
    }

    $response = [
        'success' => true,
        'status' => 200,
        'message' => lang_get('imatic_reminder_reminder_delete_success')
    ];

    plugin_get()->imaticReminderSendResponse(200, $response);
} else {

    $response = [
        'success' => false,
        'status' => 404,
        'message' => lang_get('imatic_reminder_reminder_not_permisson')
    ];
    plugin_get()->imaticReminderSendResponse(404, $response);

}



