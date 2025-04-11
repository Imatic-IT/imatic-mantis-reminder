<?php

require_api('authentication_api.php');

auth_ensure_user_authenticated();

function is_valid_reminder_request(array $post): bool
{
    return !empty($post['id']) && !empty($post['user_id']) && !empty($post['remind_at']);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && is_valid_reminder_request($_POST)) {
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





