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
 * Library functions for Course Availability Delay plugin.
 *
 * Hooks into Moodle's enrolled-course listing to hide courses that are still
 * within their configured delay period for the current student.
 *
 * Supports both Moodle 4.x (callback convention) and Moodle 5.x (same callback
 * remains valid; hook-system alternative not yet required for this filter).
 *
 * @package    local_courseavailabilitydelay
 * @copyright  2026 Essay Grader AI
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Moodle callback: called by get_enrolled_courses_by_timeline_classification()
 * in lib/enrollib.php before the course list is returned to the dashboard.
 *
 * We filter out any courses whose delay period has not yet elapsed for the
 * current user.  Site admins and managers are never filtered.
 *
 * @param array $courses Array of course objects with an ->id property.
 * @return array Filtered array.
 */
function local_courseavailabilitydelay_pre_course_get_enrolled_courses_by_timeline_classification($courses) {
    global $USER;

    // Bail fast when disabled.
    if (!get_config('local_courseavailabilitydelay', 'enabled')) {
        return $courses;
    }

    // Skip filtering for guests, not-logged-in users, and site admins.
    if (!isloggedin() || isguestuser() || is_siteadmin()) {
        return $courses;
    }

    // Skip filtering for users who can manage the plugin (managers/admins).
    if (has_capability('local/courseavailabilitydelay:manage', \context_system::instance(), $USER->id, false)) {
        return $courses;
    }

    // Check unlock status — if not unlocked, do nothing (don't filter).
    if (!class_exists('\local_courseavailabilitydelay\unlock_verifier')) {
        return $courses;
    }
    if (!\local_courseavailabilitydelay\unlock_verifier::is_unlocked()) {
        return $courses;
    }

    if (empty($courses)) {
        return $courses;
    }

    return \local_courseavailabilitydelay\local\course_filter::filter_courses_for_user(
        $courses,
        (int)$USER->id
    );
}
