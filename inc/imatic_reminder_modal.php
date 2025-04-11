<?php

$users = plugin_get()->imaticReminderGetAllIssueUsers();
if (plugin_get()->imaticRemindersAccessTreshold()) {
    $reminders = plugin_get()->imaticReminderGetAllIssueReminders(gpc_get_string('id'));
} else {
    $reminders = plugin_get()->imaticReminderGetAllIssueRemindersByUser(gpc_get_string('id'), auth_get_current_user_id());
}

$countReminders = count(array_filter($reminders, fn($reminder) => $reminder['reminded'] === 'f'));

?>

<div id="imatic-reminder-modal" class="modal" tabindex="-1" role="dialog">
    <div id="count-reminders" data-count="<?php echo $countReminders ?>"></div>
    <div class="modal-dialog imatic-remind-modal" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo lang_get('imatic_reminder_button') ?></h5>

                <button type="button" class="close imatic-reminde-modal-close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="imatic-reminder-create-form" action="<?php echo plugin_page('imatic_save_remind.php') ?>"
                  method="post">
                <div class="modal-body">

                    <div class="modal-recipients">

                        <label for="recipients-select"><?php echo lang_get('imatic_add_recipients') ?></label>
                        <select id="recipients-select" class="modal-recipients-select w-100" name="users[]" multiple="multiple" required>
                            <?php
                            foreach ($users as $user) {
                                echo '<option value="' . htmlspecialchars($user['id'], ENT_QUOTES, 'UTF-8') . '">'
                                    . print_icon($user['id']) . htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8')
                                    . '</option>';
                            }
                            ?>
                        </select>

                    </div>


                    <br>
                    <hr>

                    <div class="container">
                        <div class="row">
                            <div class="col-md-3">

                                <label for="remind_at"><?php echo lang_get('imatic_add_when_send_reminder') ?></label>
                                <br>
                                <input id="remind_at" name="remind_at" required>
                                <input type="hidden" name="issue_id" value="<?php echo gpc_get_string('id') ?>">

                            </div>

                            <!--                            TODO REMINDER REPEATER-->
                            <!--                            <div class="col-md-3">-->
                            <!--                                <p>-->
                            <?php //echo lang_get('imatic_add_repeat') ?><!-- </p>-->
                            <!---->
                            <!--                                <select disabled name="" id="">-->
                            <!--                                    <option value="">-->
                            <?php //echo lang_get('imatic_remind_do_not_repeat') ?><!--</option>-->
                            <!--                                    <option value="">-->
                            <?php //echo lang_get('imatic_remind_daily') ?><!-- </option>-->
                            <!--                                    <option value="">-->
                            <?php //echo lang_get('imatic_remind_weekly') ?><!--</option>-->
                            <!--                                    <option value="">-->
                            <?php //echo lang_get('imatic_remind_monthly') ?><!--</option>-->
                            <!--                                    <option value="">-->
                            <?php //echo lang_get('imatic_remind_yearly') ?><!--</option>-->
                            <!--                                </select>-->
                            <!--                            </div>-->

                        </div>
                    </div>

                    <br>
                    <hr>
                    <div class="form-group">
                        <label
                                for="comment">
                            <?php echo lang_get('imatic_remind_remind_message') ?>
                        </label>
                        <textarea name="message" class="form-control" rows="5" id="comment"> </textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <input
                            type="submit"
                            id="imatic-reminder-create-remind"
                            value="<?php echo lang_get('imatic_remind_create') ?>"
                            class="btn btn-primary"
                    />
                    <button
                            type="button" class="btn btn-danger text-dark"
                            id="imatic-reminder-close-modal"
                            data-dismiss="modal"
                            aria-label="Close"
                    >
                        <?php echo lang_get('imatic_remind_close') ?>
                    </button>
                </div>
            </form>

            <!--            SHOW REMINDS-->
            <?php
            include 'imatic_reminder_table.php';
            ?>
        </div>


    </div>
</div>


