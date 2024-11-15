<?php

reminderUserLogin();
$reminder = new ImaticReminderPlugin('reminder');

require_once(dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'core.php');


$t_core_path = config_get('core_path');
require_once($t_core_path . 'email_api.php');

$bugsForRemind = $reminder->imaticGetAllNotRemindedIssues();

foreach ($bugsForRemind as $bugReminder) {

    $bug = bug_get_extended_row($bugReminder['issue_id']);
    $summary = $bug['summary'];

    $icon = plugin_config_get('imatic_reminder_mail_subject_prefix_icon');
    $encoded_icon = html_entity_decode($icon, ENT_COMPAT, 'UTF-8');

    $subject = $encoded_icon;
    $subject .= ' ' . plugin_config_get('imatic_reminder_mail_subject_prefix');
    $subject .= ' [' . project_get_name($bug['project_id']) . bug_format_id($bug['id']);
    $subject .= ']: ' . $summary . ' - ' . strtolower(lang_get('reminder'));

    $t_header = "\n" . lang_get( 'on_date' ) . ' ' . date('d.m.Y H:i',$bugReminder['remind_at']) . ', ' . user_get_name($bugReminder['reminded_by_user_id'])  .' '. lang_get( 'sent_you_this_reminder_about' ) . ': ' . "\n\n";
    $t_contents = $t_header . string_get_bug_view_url_with_fqdn( $bug['id'] ) . " \n\n" . $bugReminder['message'];

    email_store($bugReminder['user_email'], $subject, $t_contents);

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


function reminderUserLogin()
{
    global $g_cache_current_user_id;

    $query = sprintf("SELECT id FROM %s WHERE username='%s'",db_get_table( 'user' ), "reminder");

    $result = db_query($query);
    $row = db_fetch_array($result);
    if(!isset($row['id']))
    {
        throw new exception("Need to create user with name reminder");
    }
    $g_cache_current_user_id = $row['id'];

}
