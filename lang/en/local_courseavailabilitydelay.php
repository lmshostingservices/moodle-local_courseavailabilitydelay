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
 * English language strings for Course Availability Delay plugin.
 *
 * @package    local_courseavailabilitydelay
 * @copyright  2026 Essay Grader AI
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Course Availability Delay';
$string['plugindesc'] = 'Delay when enrolled courses appear on a student\'s My Courses dashboard. Configure a delay period per course — students only see the course after the configured number of days since enrolment (or after a fixed date).';

// Capabilities
$string['courseavailabilitydelay:manage'] = 'Manage course availability delay rules';
$string['courseavailabilitydelay:viewreports'] = 'View course availability delay reports';

// Settings
$string['apicredentials'] = 'API Credentials';
$string['apicredentials_desc'] = 'Enter your Essay Grader AI site credentials. If AI Central Config (local_aiconfig) is installed, credentials are read from there automatically — these fields are only needed as a fallback.';
$string['siteid'] = 'Site ID';
$string['siteid_desc'] = 'Your unique site identifier from lms-labs.com.';
$string['apikey'] = 'API Key';
$string['apikey_desc'] = 'Your API key from lms-labs.com.';
$string['centralconfig_fallback'] = '(Used as fallback — Central Config credentials take priority.)';
$string['generalsettings'] = 'General Settings';
$string['enabled'] = 'Enable Course Availability Delay';
$string['enabled_desc'] = 'When enabled, enrolled courses are hidden from a student\'s My Courses dashboard until their configured delay period has elapsed.';

// Unlock / credits
$string['unlock_required'] = 'Course Availability Delay requires 1,000 AI credits to unlock. Please visit your AI Grader dashboard at lms-labs.com to unlock this plugin.';
$string['plugin_locked'] = 'Plugin not unlocked';
$string['plugin_locked_desc'] = 'This plugin requires 1,000 AI credits to activate. Visit <a href="https://lms-labs.com" target="_blank">lms-labs.com</a> to unlock it.';

// Manage page (CSV import)
$string['manage'] = 'Manage Course Delay Rules';
$string['manage_heading'] = 'Course Availability Delay — Bulk Import';
$string['manage_desc'] = 'Upload a CSV file to set delay rules for one or more courses. Each row sets the delay for a single course. Existing rules for a course will be updated.';
$string['csv_format'] = 'CSV Format';
$string['csv_format_desc'] = 'The CSV must have a header row and contain the following columns:';
$string['csv_col_courseid'] = 'Moodle course ID (required)';
$string['csv_col_delay_days'] = 'Number of days after enrolment before the course appears (required; use 0 to remove the delay)';
$string['csv_col_fixed_date'] = 'Optional fixed date (YYYY-MM-DD). If provided, the course becomes visible on this date instead of using delay_days.';
$string['upload_csv'] = 'Upload CSV';
$string['csvfile'] = 'CSV File';
$string['import_success'] = 'Successfully imported {$a} course rule(s).';
$string['import_error_empty'] = 'The uploaded file is empty or could not be read.';
$string['import_error_header'] = 'CSV header row is missing or incorrect. Expected: courseid, delay_days (and optionally fixed_date).';
$string['import_error_row'] = 'Row {$a->row}: {$a->message}';
$string['current_rules'] = 'Current Course Delay Rules';
$string['no_rules'] = 'No delay rules configured yet.';
$string['course_name'] = 'Course';
$string['delay_days_label'] = 'Delay (days)';
$string['fixed_date_label'] = 'Fixed Date';
$string['actions'] = 'Actions';
$string['delete_rule'] = 'Delete rule';
$string['rule_deleted'] = 'Delay rule deleted.';
$string['download_template'] = 'Download CSV template';

// Assign page (manual overrides)
$string['assign'] = 'Assign User Overrides';
$string['assign_heading'] = 'Course Availability Delay — User Overrides';
$string['assign_desc'] = 'Manually set a custom availability start date for a specific user in a course. This overrides the global delay rule for that user.';
$string['select_course'] = 'Select Course';
$string['select_user'] = 'Select User';
$string['custom_start_date'] = 'Custom Start Date';
$string['custom_start_date_desc'] = 'The course will become visible to this user on or after this date.';
$string['save_override'] = 'Save Override';
$string['override_saved'] = 'User override saved.';
$string['override_deleted'] = 'User override deleted.';
$string['current_overrides'] = 'Current User Overrides';
$string['no_overrides'] = 'No user overrides configured yet.';
$string['delete_override'] = 'Delete override';
$string['user_label'] = 'User';
$string['course_label'] = 'Course';

// Privacy
$string['privacy:metadata:local_cad_user_overrides'] = 'Stores custom course availability start dates set by administrators for individual users.';
$string['privacy:metadata:local_cad_user_overrides:userid'] = 'The ID of the user.';
$string['privacy:metadata:local_cad_user_overrides:courseid'] = 'The ID of the course.';
$string['privacy:metadata:local_cad_user_overrides:custom_start'] = 'The custom start date set for this user and course.';
$string['privacy:metadata:local_cad_user_overrides:timecreated'] = 'When the override was created.';
$string['privacy:metadata:local_cad_user_overrides:timemodified'] = 'When the override was last modified.';
