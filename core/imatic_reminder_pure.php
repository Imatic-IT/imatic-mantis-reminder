<?php

/**
 * Pure, DB-independent scheduling logic for ImaticReminder.
 *
 * These helpers hold the reminder scheduling rules with no dependency on a live
 * Mantis / database environment, so they can be unit-tested in isolation (see
 * tests/ReminderSchedulingTest.php).
 */

/**
 * Is a reminder considered "already reminded"?
 *
 * The DB layer may return the boolean column in various shapes ('f'/'t',
 * '0'/'1', 0/1, false/true), so normalise them all here.
 *
 * @param mixed $reminded raw value of the `reminded` column
 */
function imatic_reminder_is_reminded($reminded): bool
{
    if (is_bool($reminded)) {
        return $reminded;
    }
    $t_value = strtolower(trim((string)$reminded));
    return !in_array($t_value, ['', 'f', 'false', '0'], true);
}

/**
 * Is a reminder soft-deleted?
 *
 * `deleted_at` holds a unix timestamp, or NULL when the reminder is active.
 *
 * @param mixed $deleted_at raw value of the `deleted_at` column
 */
function imatic_reminder_is_deleted($deleted_at): bool
{
    return $deleted_at !== null && trim((string)$deleted_at) !== '' && (int)$deleted_at !== 0;
}

/**
 * Would the reminder cron pick this row up right now?
 *
 * Mirrors the WHERE clause of ImaticReminderPlugin::imaticGetAllNotRemindedIssues():
 *   remind_at <= now + interval  AND  reminded = false  AND  deleted_at IS NULL
 *
 * The `deleted_at IS NULL` part is why a rescheduled-after-fire reminder must
 * have its `deleted_at` cleared, otherwise it stays invisible to the cron.
 *
 * @param array $row                  row with remind_at / reminded / deleted_at
 * @param int   $now                  current unix time
 * @param int   $interval_end_seconds look-ahead window in seconds
 */
function imatic_reminder_is_due(array $row, int $now, int $interval_end_seconds): bool
{
    if (imatic_reminder_is_deleted($row['deleted_at'] ?? null)) {
        return false;
    }
    if (imatic_reminder_is_reminded($row['reminded'] ?? false)) {
        return false;
    }
    return (int)$row['remind_at'] <= $now + $interval_end_seconds;
}

/**
 * Column => value assignments to apply when a reminder is edited / rescheduled.
 *
 * Clearing `deleted_at` (null => SQL NULL) revives a soft-deleted reminder: a
 * reminder the user deleted has `deleted_at` set, and the cron only selects rows
 * with `deleted_at IS NULL`. Editing such a reminder should bring it back, so the
 * reset is required. (It also defends against any legacy row whose deleted_at was
 * set on fire before that behaviour was removed from imaticMarkIssueAsReminded.)
 *
 * @return array ordered column => value; null means SQL NULL
 */
function imatic_reminder_reschedule_assignments(int $remind_at, string $message, int $now): array
{
    return [
        'remind_at'  => $remind_at,
        'message'    => $message,
        'reminded'   => 'false',
        'deleted_at' => null,
        'updated_at' => $now,
    ];
}

/**
 * Render an ordered column => value map into an SQL SET clause body.
 * null renders as `col=NULL`; every other value is single-quoted, matching the
 * existing query style in the plugin.
 */
function imatic_reminder_render_set_clause(array $assignments): string
{
    $t_parts = [];
    foreach ($assignments as $t_col => $t_val) {
        $t_parts[] = $t_val === null ? "$t_col=NULL" : "$t_col='" . $t_val . "'";
    }
    return implode(', ', $t_parts);
}
