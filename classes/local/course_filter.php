<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Core course filtering logic for Course Availability Delay.
 *
 * @package    local_courseavailabilitydelay
 * @copyright  2026 Essay Grader AI
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_courseavailabilitydelay\local;

defined('MOODLE_INTERNAL') || die();

class course_filter {
    /**
     * Determine whether a given user can see a given course right now.
     * Returns true = visible, false = still delayed.
     *
     * @param int $userid
     * @param int $courseid
     * @return bool
     */
    public static function is_course_visible_for_user(int $userid, int $courseid): bool {
        global $DB;

        // Check for a per-user override first.
        $override = $DB->get_record('local_cad_user_overrides', [
            'userid'   => $userid,
            'courseid' => $courseid,
        ]);

        if ($override && !empty($override->custom_start)) {
            return (time() >= (int)$override->custom_start);
        }

        // Fall back to the per-course rule.
        $rule = $DB->get_record('local_cad_course_rules', ['courseid' => $courseid]);

        if (!$rule) {
            // No rule defined — course is always visible.
            return true;
        }

        // If a fixed date is set, use that.
        if (!empty($rule->fixed_date)) {
            return (time() >= (int)$rule->fixed_date);
        }

        // No delay — visible immediately.
        if (empty($rule->delay_days) || (int)$rule->delay_days <= 0) {
            return true;
        }

        // Get the user's enrolment start date for this course.
        $enroldate = self::get_enrolment_date($userid, $courseid);

        if ($enroldate === null) {
            // Not enrolled — visible (let Moodle handle enrolment elsewhere).
            return true;
        }

        $unlock_timestamp = $enroldate + ((int)$rule->delay_days * DAYSECS);
        return (time() >= $unlock_timestamp);
    }

    /**
     * Get the earliest enrolment start date for a user in a course.
     *
     * @param int $userid
     * @param int $courseid
     * @return int|null Unix timestamp of enrolment, or null if not enrolled.
     */
    public static function get_enrolment_date(int $userid, int $courseid): ?int {
        global $DB;

        $sql = "SELECT MIN(ue.timestart)
                  FROM {user_enrolments} ue
                  JOIN {enrol} e ON e.id = ue.enrolid
                 WHERE ue.userid = :userid
                   AND e.courseid = :courseid
                   AND ue.status = 0";

        $result = $DB->get_field_sql($sql, ['userid' => $userid, 'courseid' => $courseid]);

        if ($result === false || $result === null) {
            // Try timecreated as a fallback (timestart may be 0 for some enrolment types).
            $sql2 = "SELECT MIN(ue.timecreated)
                       FROM {user_enrolments} ue
                       JOIN {enrol} e ON e.id = ue.enrolid
                      WHERE ue.userid = :userid
                        AND e.courseid = :courseid
                        AND ue.status = 0";
            $result2 = $DB->get_field_sql($sql2, ['userid' => $userid, 'courseid' => $courseid]);
            return ($result2 !== false && $result2 !== null) ? (int)$result2 : null;
        }

        // timestart = 0 means no explicit start constraint — use timecreated.
        if ((int)$result === 0) {
            $sql3 = "SELECT MIN(ue.timecreated)
                       FROM {user_enrolments} ue
                       JOIN {enrol} e ON e.id = ue.enrolid
                      WHERE ue.userid = :userid
                        AND e.courseid = :courseid
                        AND ue.status = 0";
            $result3 = $DB->get_field_sql($sql3, ['userid' => $userid, 'courseid' => $courseid]);
            return ($result3 !== false && $result3 !== null) ? (int)$result3 : null;
        }

        return (int)$result;
    }

    /**
     * Given an array of course objects (as returned by get_enrolled_courses_by_timeline_classification),
     * filter out any courses that are still within their delay period for the given user.
     *
     * @param array  $courses  Array of course objects with ->id property.
     * @param int    $userid
     * @return array Filtered array (same keys preserved).
     */
    public static function filter_courses_for_user(array $courses, int $userid): array {
        if (empty($courses)) {
            return $courses;
        }

        $filtered = [];
        foreach ($courses as $key => $course) {
            if (self::is_course_visible_for_user($userid, (int)$course->id)) {
                $filtered[$key] = $course;
            }
        }

        return $filtered;
    }
}
