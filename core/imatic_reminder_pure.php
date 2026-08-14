<?php

// Pure, DB-independent scheduling logic for ImaticReminder (unit-tested in
// tests/ReminderSchedulingTest.php).

// Normalises the reminded column ('f'/'t', '0'/'1', 0/1, false/true).
function imatic_reminder_is_reminded($reminded): bool
{
    if (is_bool($reminded)) {
        return $reminded;
    }
    $t_value = strtolower(trim((string)$reminded));
    return !in_array($t_value, ['', 'f', 'false', '0'], true);
}

function imatic_reminder_is_deleted($deleted_at): bool
{
    return $deleted_at !== null && trim((string)$deleted_at) !== '' && (int)$deleted_at !== 0;
}

// Mirrors imaticGetAllNotRemindedIssues():
// remind_at <= now + interval AND reminded = false AND deleted_at IS NULL.
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

// Assignments for an edit/reschedule. deleted_at => null revives a soft-deleted
// reminder so the cron (deleted_at IS NULL) sees it again.
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

// Renders column => value into an SQL SET body; null => col=NULL.
function imatic_reminder_render_set_clause(array $assignments): string
{
    $t_parts = [];
    foreach ($assignments as $t_col => $t_val) {
        $t_parts[] = $t_val === null ? "$t_col=NULL" : "$t_col='" . $t_val . "'";
    }
    return implode(', ', $t_parts);
}
