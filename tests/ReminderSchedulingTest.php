<?php

/**
 * Unit tests for the ImaticReminder scheduling logic.
 *
 * These cover the pure functions in core/imatic_reminder_pure.php and do NOT
 * require a live Mantis / database environment.
 *
 * Run from the repository root:
 *   phpunit plugins/ImaticReminder/tests/ReminderSchedulingTest.php
 */

require_once __DIR__ . '/../core/imatic_reminder_pure.php';

use PHPUnit\Framework\TestCase;

class ReminderSchedulingTest extends TestCase
{
    private const INTERVAL = 3600; // 60 min look-ahead, matching the default config

    // ─── imatic_reminder_is_reminded ─────────────────────────────────────────

    public function test_is_reminded_false_variants(): void
    {
        foreach (['f', 'false', '0', '', false, 0] as $v) {
            $this->assertFalse(imatic_reminder_is_reminded($v), var_export($v, true));
        }
    }

    public function test_is_reminded_true_variants(): void
    {
        foreach (['t', 'true', '1', true, 1] as $v) {
            $this->assertTrue(imatic_reminder_is_reminded($v), var_export($v, true));
        }
    }

    // ─── imatic_reminder_is_deleted ──────────────────────────────────────────

    public function test_is_deleted(): void
    {
        $this->assertFalse(imatic_reminder_is_deleted(null));
        $this->assertFalse(imatic_reminder_is_deleted(''));
        $this->assertFalse(imatic_reminder_is_deleted(0));
        $this->assertTrue(imatic_reminder_is_deleted(1783407902));
        $this->assertTrue(imatic_reminder_is_deleted('1783407902'));
    }

    // ─── imatic_reminder_is_due ──────────────────────────────────────────────

    public function test_due_when_active_and_within_window(): void
    {
        $now = 1000000;
        $row = ['remind_at' => $now + 1800, 'reminded' => 'false', 'deleted_at' => null];
        $this->assertTrue(imatic_reminder_is_due($row, $now, self::INTERVAL));
    }

    public function test_not_due_when_remind_at_too_far_ahead(): void
    {
        $now = 1000000;
        $row = ['remind_at' => $now + 7200, 'reminded' => 'false', 'deleted_at' => null];
        $this->assertFalse(imatic_reminder_is_due($row, $now, self::INTERVAL));
    }

    public function test_not_due_when_already_reminded(): void
    {
        $now = 1000000;
        $row = ['remind_at' => $now - 100, 'reminded' => 't', 'deleted_at' => null];
        $this->assertFalse(imatic_reminder_is_due($row, $now, self::INTERVAL));
    }

    /**
     * A fired reminder is excluded by the `reminded` flag alone — it does NOT need
     * deleted_at set. This locks in the root-cause fix: imaticMarkIssueAsReminded
     * only flips `reminded` and leaves deleted_at reserved for user deletion.
     */
    public function test_fired_reminder_excluded_without_deleted_at(): void
    {
        $now = 1000000;
        $fired = ['remind_at' => $now - 100, 'reminded' => 'true', 'deleted_at' => null];
        $this->assertFalse(imatic_reminder_is_due($fired, $now, self::INTERVAL));
    }

    /**
     * Regression guard for issue #0086262 (#85433): a reminder that fired once
     * (or was deleted) has deleted_at set. Even with reminded reset to false and
     * remind_at in the past, the cron must NOT pick it up while deleted_at is set.
     */
    public function test_zombie_reminder_is_not_due(): void
    {
        $now = 1783900800;
        $zombie = [
            'remind_at'  => 1783584000, // in the past
            'reminded'   => 'false',    // shown as "not reminded" in the UI
            'deleted_at' => 1783407902, // stuck from the previous fire
        ];
        $this->assertFalse(imatic_reminder_is_due($zombie, $now, self::INTERVAL));
    }

    // ─── reschedule assignments (the fix) ────────────────────────────────────

    public function test_reschedule_clears_deleted_at_and_resets_reminded(): void
    {
        $assignments = imatic_reminder_reschedule_assignments(1783584000, 'ping', 1783533898);

        $this->assertArrayHasKey('deleted_at', $assignments);
        $this->assertNull($assignments['deleted_at'], 'reschedule must clear deleted_at');
        $this->assertSame('false', $assignments['reminded']);
        $this->assertSame(1783584000, $assignments['remind_at']);
        $this->assertSame('ping', $assignments['message']);
        $this->assertSame(1783533898, $assignments['updated_at']);
    }

    /**
     * End-to-end regression: applying the reschedule assignments to a zombie row
     * must make it due again. Before the fix (deleted_at left untouched) it stayed
     * invisible to the cron.
     */
    public function test_reschedule_revives_zombie_for_cron(): void
    {
        $now = 1783900800;
        $zombie = ['remind_at' => 1783300000, 'reminded' => 'false', 'deleted_at' => 1783407902];
        $this->assertFalse(imatic_reminder_is_due($zombie, $now, self::INTERVAL));

        // reschedule to a moment inside the look-ahead window
        $assignments = imatic_reminder_reschedule_assignments($now + 600, 'again', $now);
        $revived = array_merge($zombie, $assignments);

        $this->assertTrue(imatic_reminder_is_due($revived, $now, self::INTERVAL));
    }

    public function test_render_set_clause_matches_query_style(): void
    {
        $assignments = imatic_reminder_reschedule_assignments(123, 'hi', 456);
        $this->assertSame(
            "remind_at='123', message='hi', reminded='false', deleted_at=NULL, updated_at='456'",
            imatic_reminder_render_set_clause($assignments)
        );
    }
}
