# Reminder Plugin


## Imatic  Features 

### Issue Notification Plugin

This plugin allows you to set up issue notifications that are automatically sent via a cron job.

#### Features:
- **Notification Setup:** Allows users to set notifications for specific issues.
- **Automated Sending:** A cron job automatically sends notifications according to the configured schedule.
- **UTF-8 and Emoji Support:** Notifications can include UTF-8 characters and emojis, properly encoded for email subjects.

### Setting Up and Sending Reminders

You can set up and send reminders directly from an issue in the menu. Look for the button where the native Mantis reminder functionality is also located. This allows you to easily configure and send custom reminders without leaving the issue page.

## Requirements

The plugin requires MantisBT version 2.0 or higher.

Make sure to have this statement in confg_inc.php:

## Installation

Like any other plugin. After copying to your webserver :

- Start mantis as administrator
- Select manage
- Select manage Plugins
- Select Install behind *Reminder*

Ensure you have a configured cron job to run the notification sending script.


#### Configuration Parameters:

##### Time Interval Settings:
- **`imatic_reminder_time_interval_start`:**
   - **Description:** This parameter defines the start of the time interval (in minutes) within which the reminder can be sent.
   - **Example:** `'imatic_reminder_time_interval_start' => 60`
   - **Usage:** If set to 60, the reminder will start being considered for sending 60 minutes before the scheduled time.

- **`imatic_reminder_time_interval_end`:**
   - **Description:** This parameter defines the end of the time interval (in minutes) within which the reminder can be sent.
   - **Example:** `'imatic_reminder_time_interval_end' => 60`
   - **Usage:** If set to 60, the reminder will still be considered valid for sending up to 60 minutes after the scheduled time.

These time intervals create a tolerance window to ensure that reminders are not missed due to slight deviations in the cron job's execution time.

##### Email Subject Prefix:
- **`imatic_reminder_mail_subject_prefix`:**
   - **Description:** This parameter sets the prefix for the email subject. It can be defined in the plugin language file (plugins/ImaticReminder/lang/strings_english.txt).
   - **Example:** `'imatic_reminder_mail_subject_prefix' => lang_get('imatic_reminder_reminder_email_subject_prefix')`
   - **Usage:** Define this in your plugin language file to customize the subject prefix text based on the user's language settings.

- **`imatic_reminder_mail_subject_prefix_icon`:**
   - **Description:** This parameter sets an icon (such as an emoji) to be included in the email subject prefix. It can be defined in the plugin configuration 
   - **Example:** `'imatic_reminder_mail_subject_prefix_icon' => '&#128276;'`  // Icon from [UTF-8 Icons](https://utf8-icons.com/bell-128276)
   - **Usage:** Use the HTML entity for the desired icon to include it in the subject prefix.


### Access Control

- **`imatic_reminders_access_threshold`:**
    - **Description:** This parameter defines the access level required to view all reminders.
    - **Example:** `'imatic_reminders_access_threshold' => MANAGER`
    - **Usage:** Users with the specified access level (e.g., MANAGER) can see all reminders.

Users with insufficient permissions will only see reminders that they have set themselves.


### Example Test

To test the reminder functionality, you can use the following command:

```sh
wget -q -O - 'http://localhost:8888/path_to_your_mantis/imatic-mantis/plugin.php?page=ImaticReminder/imatic_remind_issue.php'
```



##  DEFAULT FEATURES REMINDER PLUGIN
## Reminder Plugin 


   
Copyright (c) 2009  Cas Nuy - cas@nuy.info - http://www.nuy.info

Released under the [GPL 2.0](http://opensource.org/licenses/GPL-2.0)

## Description

This plugin can be used to send  periodic email reminders to bug reporters,
managers, and assignees.

The files in the `plugins/Reminder/scripts` directory should be run directly,
from the command line.

1. `bug_feedback_mail.php` sends emails to reporters listing all bugs awaiting
   their feedback.
2. `bug_reminder_mail.php` sends emails to assignees when bugs are approaching
   their due date.
3. `assigned_bugs.php` sends emails to assignees listing all open bugs that are
   assigned to them.


## Requirements

The plugin requires MantisBT version 2.0 or higher.

Make sure to have this statement in confg_inc.php:
```
$g_path = 'http://path-to-your-mantis-installation/';
```


## Installation

Like any other plugin. After copying to your webserver :

- Start mantis as administrator
- Select manage
- Select manage Plugins
- Select Install behind *Reminder*
- Once installed, click on the plugin's name for further configuration.

No Mantis scripts or tables are being altered.


## Configuration options

```
// What is the body of the E-mail
reminder_mail_subject	= "Following issue will be Due shortly";

// What is the subject of the grouped E-mail
reminder_group_subject	= "You have issues approaching their Due Date";

// What is the start of the body of the grouped E-mail
reminder_groupbody1	= "Please review the following issues";

// What is the end of the body of the grouped E-mail
reminder_groupbody2	= "Please do not reply to this message";

// perform for which project
reminder_project_id = 0; means ALL

// how many days before Due date should we take into account
reminder_days_treshold  = 2;

// Should we use hours instead of days
reminder_hours		  	= OFF;

// Should we store this reminder as bugnote
reminder_store_as_note = OFF;
// only possible for handler

// For which status to send reminders
reminder_bug_status = ASSIGNED

// Ignore reminders for issues with no Due date set
reminder_ignore_unset = ON

// Ignore reminders for issues with Due dates in the past
reminder_ign_past = ON

// only valid for the mail function, downloads will always have duedates that have gone by

// Create overview per handler
reminder_handler = ON

// Group issues by Handler
reminder_group_issues = ON

// Group issues by project/handler
reminder_group_project = OFF

// Create overview per manager/project
reminder_manager_overview = ON
//
// access level for manager= 70
// this needs to be made flexible
// we will only produce overview for those projects that have a separate manager
//

// Select project to receive Feedback mail
reminder_feedback_project = 0; means ALL

// For which status to send feedbackreminders
reminder_bug_status = FEEDBACK

// On which account should we run the background jobs
// In principle I would advise to use an account with enough rights,
// so I suggest something like the admin account (mantis account).
reminder_login = 'admin'
```

## Automatically generating mail

Once configuration is complete, `bug_reminder_mail.php` script can be used
as described below, depending on your operating system.


### Linux

Use a cron job like this:
```
*/1440 *   *   *   * lynx --dump http://mantis.homepage.com/plugins/Reminder/scripts/bug_reminder_mail.php
```

or via PHP command line interface
```
*/1440 *   *   *   * /usr/local/bin/php /path/to/mantis/plugins/Reminder/scripts/bug_reminder_mail.php
```

This would send out a reminder every day.


### Windows

You can use a scheduled task under Windows by calling a batch-file like:

```
REM *** this batch is running as a scheduled task under the ... Account ***
g:
cd \inetpub\wwwroot\mantis
php.exe plugins/Reminder/scripts/bug_reminder_mail.php
```

### Reminders for feedback status

One can also schedule a job to prompt reporters to respond because their
issue has status *Feedback*. In that case use the same methods as described
above, but replace `bug_reminder_mail.php` with `bug_feedback_mail.php`.


## Extras

On top of that, I have created a little variant which will create a
spreadsheet with issues getting due. Call script like:

```
http://www.YourMantisHome.com/plugins/Reminder/scripts/bug_due_overview2.php?days=2&status=50
```

If you do not apply parameters, the script will default to the above.

In the script directory you will also find a script called
`bug_reminder_mail_test.php`, which you should call from within the
browser (once logged on) to provide useful feedback if things are not
working as expected. In case of a blank screen, all is processed normally.

For option Days/Hours, the script will fetch the plugin definition.


## Support

Log new issues against the [Plugin - Reminder] project on
[Github]((https://github.com/mantisbt-plugins/Reminde)).

Source code is also available on [Github](https://github.com/mantisbt-plugins/Reminder).


## Credits

- Mark Ziegler, German translation (May 2010)
