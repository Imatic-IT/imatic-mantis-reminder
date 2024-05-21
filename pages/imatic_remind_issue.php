<?php

$reminder = new ImaticReminderPlugin('reminder');

require_once(dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'core.php');
$t_login = plugin_config_get('imatic_reminder_login');
$t_password = plugin_config_get('imatic_reminder_password');

if (!auth_attempt_script_login($t_login, $t_password)) {
    echo "Failed to login\n";
    exit(1);
}
$t_core_path = config_get('core_path');
require_once($t_core_path . 'email_api.php');

$bugsForRemind = $reminder->imaticGetAllNotRemindedIssues();

foreach ($bugsForRemind as $bugReminder) {

    $bug = bug_get_extended_row($bugReminder['issue_id']);
    $summary = $bug['summary'];

    $list = '';
    $url = string_get_bug_view_url_with_fqdn($bug['id']);
    $list .= "  * $summary\n    $url\n";

    $message = "" . $bugReminder['message'] . ":\n\n$list\n";
    $subject = plugin_config_get('imatic_reminder_mail_subject') . ' [' . $bug['id'] . '] - ' . $summary;

    email_store($bugReminder['user_email'], $subject, $message);

    if (OFF == config_get('email_send_using_cronjob')) {
        email_send_all();
        $result = $reminder->imaticMarkIssueAsReminded($bugReminder['id'], $bugReminder['user_id']);

        if ($result) {
            renderResult($bug['id'], $bugReminder['user_email']);

        } else {
            echo "Failed to send reminder for issue: " . $bug['id'] . "\n";
        }
    }
}

function renderResult($bugId, $recipient)
{
    echo sprintf("Date: %s<br />", date('d.m.Y', time()));
    echo sprintf("Time: %s<br />", date('H:i', time()));
    echo sprintf("Reminder sent for issue: %s<br />", $bugId);
    echo sprintf("Recipient: %s<br />", $recipient);
    echo sprintf("%s\n", '');
}
