<?php

auth_is_user_authenticated();

if ($_POST && !empty($_POST) && !empty($_POST['users']) && !empty($_POST['remind_at']) && !empty($_POST['issue_id'])) {

    $db = db_get_table('imatic_reminder_remind_issue');
    $dbNow = db_now();
    $remindAt = plugin_get()->imaticConvertToUnixTimestamp($_POST['remind_at']);
    $remindedByUserId = auth_get_current_user_id();

    db_param_push();

    $affected_row = [];
    $reminders = [];
    foreach ($_POST['users'] as $userId) {

        $user = user_get_row($userId);

        $t_query = 'INSERT INTO ' . $db . '
                        ( issue_id, remind_at, user_id, username, user_email, message, reminded, created_at, updated_at, reminded_by_user_id )
                      VALUES
                        ( ' . db_param() . ',' . db_param() . ',' . db_param() . ',' . db_param() . ',' . db_param() . ' , ' . db_param() . ' , ' . db_param() . ' , ' . db_param() . ' , ' . db_param() . ' , ' . db_param() . ' )';
        db_query($t_query, array($_POST['issue_id'], $remindAt, $user['id'], $user['username'], $user['email'], $_POST['message'], 0, $dbNow, $dbNow, $remindedByUserId));
        $affectedRows[] = db_affected_rows($db);

        $reminder = [
            'id' => db_insert_id($db),
            'issue_id' => $_POST['issue_id'],
            'remind_at' => $_POST['remind_at'],
            'user_id' => $user['id'],
            'username' => $user['username'],
            'user_email' => $user['email'],
            'message' => $_POST['message'],
            'reminded' => 0,
            'created_at' => $dbNow,
            'updated_at' => $dbNow,
            'reminded_by_user_id' => $remindedByUserId,
            'edit_action' => plugin_page('imatic_edit_remind.php'),
            'delete_action' => plugin_page('imatic_delete_remind.php')
        ];

        $reminders[] = $reminder;

    }

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
        'reminders' => $reminders,
        'message' => lang_get('imatic_reminder_reminder_created_success')
    ];

    plugin_get()->imaticReminderSendResponse(200, $response);

} else {

    $response = [
        'success' => false,
        'status' => 200,
        'message' => 'Data are not valid.'
    ];
    plugin_get()->imaticReminderSendResponse(404, $response);

}