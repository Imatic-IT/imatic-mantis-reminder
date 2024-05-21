<?php

if (plugin_get()->imaticRemindersAccessTreshold()) {
    $reminders = plugin_get()->imaticReminderGetAllIssueReminders(gpc_get_string('id'));
} else {
    $reminders = plugin_get()->imaticReminderGetAllIssueRemindersByUser(gpc_get_string('id'), auth_get_current_user_id());
}

?>
    <div id="issue-reminds" class="">
        <h3><?php echo lang_get('imatic_remind_issue_reminds') ?></h3>
        <br>
        <table id="reminders-table" class="table table-bordered">
            <thead>
            <tr>
                <th>Id</th>
                <th><?php echo lang_get('imatic_remind_user') ?></th>
                <th><?php echo lang_get('imatic_remind_remind_at') ?></th>
                <th><?php echo lang_get('imatic_remind_message') ?></th>
                <th><?php echo lang_get('imatic_remind_reminded') ?></th>
                <th><?php echo lang_get('imatic_remind_actions') ?></th>
            </tr>
            </thead>

            <div id="imatic-reminder-message"></div>
            <?php
            if (isset($reminders) && !empty($reminders)) {
                foreach ($reminders as $reminder) {
                    ?>

                    <tbody>
                    <tr id="reminder-table-<?php echo $reminder['id'] ?>">
                        <td>
                            <?php echo $reminder['id'] ?>
                        </td>
                        <td>
                            <?php echo $reminder['username'] ?>
                        </td>
                        <td>
                            <input type="datetime-local" name="remind_at" class="form-control"
                                   value="<?php echo date('Y-m-d H:i:s', $reminder['remind_at']) ?>">
                        </td>
                        <td>
                        <textarea name="message" class="form-control" rows="1"
                                  cols="20"><?php echo htmlspecialchars($reminder['message']) ?></textarea>
                        </td>
                        <td>
                            <?php echo $reminder['reminded'] !== 'f' ? '<i class="btn btn-success btn-xs fa fa-check"/>' : '<i class="btn btn-danger btn-xs fa fa-close" />' ?>
                        </td>
                        <td>
                            <button type="button" class="btn btn-primary btn-xs edit-reminder"
                                    data-id="<?php echo $reminder['id'] ?>"
                                    data-action="<?php echo plugin_page('imatic_edit_remind.php') ?>"
                            >
                                <i class="fa fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-xs delete-reminder"
                                    data-id="<?php echo $reminder['id'] ?>"
                                    data-action="<?php echo plugin_page('imatic_delete_remind.php') ?> "
                            >
                                <i class="fa fa-trash" aria-hidden="true"></i>
                            </button>

                            <input type="hidden" name="id" value="<?php echo $reminder['id'] ?>">
                            <input type="hidden" name="user_id" value="<?php echo $reminder['user_id'] ?>">
                        </td>

                    </tr>
                    </tbody>
                    <?php
                }
            }
            ?>
        </table>
    </div>
<?php
