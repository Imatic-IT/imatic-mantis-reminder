<?php /** @noinspection PhpExpressionResultUnusedInspection */

// NEXT STEPS
// TODO: bool, daily, weekly, monthly, yearly
// TODO : daily weekly ... remind then set new date to actual date + 1 day, 1 week, 1 month, 1 year
// TODO : daily weekly .. turn of remind where ?.
// TODO VALIDATE IN JS IF DATE IS IN THE PAST OR IS EMPTY

class ImaticReminderPlugin extends MantisPlugin
{
    private const IMATIC_REMINDER_PAGE = 'imatic_remind_issue.php';

    function register()
    {
        $this->name = 'ImaticReminder';
        $this->description = lang_get('reminder_plugin_desc');
        $this->version = '3.0.1';
        $this->requires = array('MantisCore' => '2.0.0',);
        $this->author = 'Imatic software';
        $this->contact = 'info@imatic.cz';
        $this->page = 'config';
    }

    /*** Default plugin configuration.     */
    function config()
    {
        return array(
            'reminder_mail_subject' => 'Following issue will be Due shortly',
            'reminder_days_treshold' => 2,
            'reminder_store_as_note' => OFF,
            'reminder_sender' => 'admin@example.com',
            'reminder_bug_status' => array(ASSIGNED),
            'reminder_ignore_unset' => ON,
            'reminder_ignore_past' => ON,
            'reminder_handler' => ON,
            'reminder_group_issues' => ON,
            'reminder_group_project' => OFF,
            'reminder_manager_overview' => ON,
            'reminder_group_subject' => "You have issues approaching their Due Date",
            'reminder_group_body1' => "Please review the following issues",
            'reminder_group_body2' => "Please do not reply to this message",
            'reminder_project_id' => "0",
            'reminder_include' => ON,
            'reminder_login' => 'admin',
            'reminder_feedback_status' => array(FEEDBACK),
            'reminder_subject' => 'Issues requiring your attention',
            'reminder_finished' => 'Finished processing your selection',
            'reminder_hours' => OFF,
            'reminder_colsep' => ';',
            'reminder_details' => OFF,
            'imatic_reminders_access_threshold' => MANAGER,
            'imatic_reminder_time_interval_start' => 60,
            'imatic_reminder_time_interval_end' => 60,
            'imatic_reminder_mail_subject_prefix' => lang_get('imatic_reminder_reminder_email_subject_prefix'),
            'imatic_reminder_mail_subject_prefix_icon' => '&#128276;',  // Icon from https://utf8-icons.com/bell-128276
        );
    }

    public function schema(): array
    {
        return [
            0 => ['CreateTableSQL', [db_get_table('imatic_reminder_remind_issue'), "
            id                          I               PRIMARY NOTNULL AUTOINCREMENT,
            issue_id                    I               PRIMARY NOTNULL,
            remind_at                   I               NOTNULL,
            user_id                     I               NOTNULL,
            reminded_by_user_id         I               NOTNULL,
            username                    C(64)           NOTNULL,
            user_email                  C(64)           NOTNULL,
            message                     X               NOTNULL,
            reminded				    L  		        NOTNULL DEFAULT \" '0' \",
	        created_at	        		I	           NOTNULL  DEFAULT '" . db_now() . "',
	        updated_at			        I	           NOTNULL  DEFAULT '" . db_now() . "',
	        deleted_at			        I	           
        "]]
        ];
    }

    public function hooks(): array
    {
        return [
            'EVENT_MENU_MANAGE' => 'remdown',
            'EVENT_MENU_ISSUE' => 'remindButton',
            'EVENT_LAYOUT_BODY_END' => 'layoutBodyEndHook',
            'EVENT_VIEW_BUG_EXTRA' => 'event_bugnote_add_form',
        ];
    }

    function remdown()
    {
        return array('<a href="' . plugin_page('bug_due_overview.php') . '">' . lang_get('reminder_download') . '</a>');
    }

    function remindButton()
    {
        $button_data = array(
            lang_get('imatic_reminder_button') => plugin_page('imatic_remind_issue.php')
        );
        return $button_data;
    }


    public function event_bugnote_add_form($p_event)
    {
        include 'inc/imatic_reminder_modal.php';

    }

    public function layoutBodyEndHook($p_event)
    {
        if (!isset($_GET['id'])) {
            return;
        }

        $t_data = htmlspecialchars(json_encode([
            'imatic_reminder_page' => self::IMATIC_REMINDER_PAGE,
        ]));

        echo '
        <link rel="stylesheet" type="text/css" href="' . plugin_file('css/select2.min.css') . '" />;
        <link rel="stylesheet" type="text/css" href="' . plugin_file('css/flatpickr.min.css') . '" />;
        <script  src="' . plugin_file('js/select2.full.min.js') . '"></script>
        <script  src="' . plugin_file('js/flatpickr.js') . '"></script>
        <script id="imaticReminderData" data-data="' . $t_data . '" src="' . plugin_file('imatic-reminder.js') . '&v=' . $this->version . '"></script>
        <link rel="stylesheet" type="text/css" href="' . plugin_file('css/imatic-reminder.css') . '&v=' . $this->version . '" />
        ';
    }

    public function imaticRemindersAccessTreshold()
    {
        $curretUserAccessLevel = user_get_access_level(auth_get_current_user_id());
        if ($curretUserAccessLevel >= plugin_config_get('imatic_reminders_access_threshold')) {
            return true;
        }
        return false;
    }


    public function imaticReminderGetAllIssueUsers()
    {
        if (isset($_GET['id'])) {
            $issueId = $_GET['id'];
            $t_bug = bug_get($issueId, true);

            $t_users = project_get_all_user_rows($t_bug->project_id);

            usort($t_users, function ($a, $b) {
                return strcasecmp($a['username'], $b['username']);
            });

            return $t_users;

        } else {
            return [];
        }
    }

    public function imaticConvertToUnixTimestamp($iso_datetime)
    {
        return strtotime($iso_datetime);
    }

    public function imaticReminderGetAllIssueReminders($issueId)
    {
        if (!plugin_get()->imaticRemindersAccessTreshold()) {
            return [];
        }

        $db = db_get_table('imatic_reminder_remind_issue');
        $sql = 'SELECT * FROM ' . $db . ' WHERE issue_id = ' . $issueId;
        $sql .= ' ORDER BY remind_at ASC';
        $result = iterator_to_array(db_query($sql, []));

        return $result;

    }

    public function imaticReminderGetAllIssueRemindersByUser($issueId, $userId)
    {

        $db = db_get_table('imatic_reminder_remind_issue');
        $sql = 'SELECT * FROM ' . $db . ' WHERE issue_id = ' . $issueId . ' AND reminded_by_user_id = ' . $userId;
        $sql .= ' ORDER BY remind_at ASC';
        $result = iterator_to_array(db_query($sql, []));

        return $result;

    }

    public function imaticReminderDeleteIssueReminder($reminderId, $delete = false)
    {
        $db = db_get_table('imatic_reminder_remind_issue');
        $sql = "UPDATE " . $db . " SET deleted_at='" . db_now() . "' WHERE id=" . $reminderId;

        if ($delete) {
            $sql = "DELETE FROM " . $db . " WHERE id=" . $reminderId;
        }

        db_query($sql);
        return db_affected_rows();
    }

    public function imaticReminderUpdateIssueReminder($reminderId, $userId, $remindAt, $message)
    {
        $remindAt = plugin_get()->imaticConvertToUnixTimestamp($remindAt);
        $db = db_get_table('imatic_reminder_remind_issue');
        $sql = "UPDATE ";
        $sql .= $db . " SET remind_at='" . $remindAt . "'";
        $sql .= ", message='" . $message . "'";
        $sql .= ", reminded='false'";
        $sql .= ", updated_at='" . db_now() . "'";
        $sql .= " WHERE id=" . $reminderId . " AND user_id=" . $userId;

        db_query($sql);
        return db_affected_rows();
    }

    public function imaticReminderSendResponse(int $status = 200, $data = null)
    {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
    }

    public function imaticGetAllNotRemindedIssues()
    {
        $db = db_get_table('imatic_reminder_remind_issue');
        $current_time = time();
        $reminder_time_start = $current_time - (plugin_config_get('imatic_reminder_time_interval_start') * 60); // - minutes from current time
        $reminder_time_end = $current_time + (plugin_config_get('imatic_reminder_time_interval_end') * 60); // + minutes from current time
        $sql = 'SELECT * FROM ' . $db . ' WHERE remind_at >= ' . $reminder_time_start . ' AND remind_at <= ' . $reminder_time_end . ' AND reminded = false AND deleted_at IS NULL';
        $result = iterator_to_array(db_query($sql, []));

        return $result;
    }

    function imaticMarkIssueAsReminded($reminderId, $userId)
    {

        $db = db_get_table('imatic_reminder_remind_issue');
        $sql = "UPDATE " . $db . " SET reminded=true, deleted_at=" . time() . " WHERE id=" . $reminderId . " AND user_id=" . $userId;

        db_query($sql);
        return db_affected_rows();
    }
}

