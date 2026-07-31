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
 * Settings for Course Availability Delay plugin.
 *
 * @package    local_courseavailabilitydelay
 * @copyright  2026 Essay Grader AI
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {

    // Show unlock warning if the plugin is not yet activated.
    $islocked = false;
    if (class_exists('\local_courseavailabilitydelay\unlock_verifier')) {
        if (!\local_courseavailabilitydelay\unlock_verifier::is_unlocked()) {
            $islocked = true;
            $currentsection = optional_param('section', '', PARAM_RAW);
            if ($currentsection === 'local_courseavailabilitydelay') {
                \core\notification::warning(get_string('unlock_required', 'local_courseavailabilitydelay'));
            }
        }
    }

    $settings = new admin_settingpage(
        'local_courseavailabilitydelay',
        get_string('pluginname', 'local_courseavailabilitydelay')
    );

    // ── API Credentials ──────────────────────────────────────────────────────
    $centralconfiginstalled = file_exists($CFG->dirroot . '/local/aiconfig/version.php');

    $settings->add(new admin_setting_heading(
        'local_courseavailabilitydelay/apicredentials',
        get_string('apicredentials', 'local_courseavailabilitydelay'),
        get_string('apicredentials_desc', 'local_courseavailabilitydelay')
    ));

    $settings->add(new admin_setting_configtext(
        'local_courseavailabilitydelay/siteid',
        get_string('siteid', 'local_courseavailabilitydelay'),
        get_string('siteid_desc', 'local_courseavailabilitydelay')
            . ($centralconfiginstalled ? ' ' . get_string('centralconfig_fallback', 'local_courseavailabilitydelay') : ''),
        '',
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configpasswordunmask(
        'local_courseavailabilitydelay/apikey',
        get_string('apikey', 'local_courseavailabilitydelay'),
        get_string('apikey_desc', 'local_courseavailabilitydelay')
            . ($centralconfiginstalled ? ' ' . get_string('centralconfig_fallback', 'local_courseavailabilitydelay') : ''),
        ''
    ));

    // ── General Settings ─────────────────────────────────────────────────────
    $settings->add(new admin_setting_heading(
        'local_courseavailabilitydelay/generalsettings',
        get_string('generalsettings', 'local_courseavailabilitydelay'),
        ''
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_courseavailabilitydelay/enabled',
        get_string('enabled', 'local_courseavailabilitydelay'),
        get_string('enabled_desc', 'local_courseavailabilitydelay'),
        1
    ));

    // ── Quick Links to Admin Pages ────────────────────────────────────────────
    $manageurl  = new \moodle_url('/local/courseavailabilitydelay/manage.php');
    $assignurl  = new \moodle_url('/local/courseavailabilitydelay/assign.php');
    $managelabel = \html_writer::link($manageurl, get_string('manage', 'local_courseavailabilitydelay'));
    $assignlabel = \html_writer::link($assignurl, get_string('assign', 'local_courseavailabilitydelay'));

    $settings->add(new admin_setting_heading(
        'local_courseavailabilitydelay/quicklinks',
        'Quick Links',
        '<ul><li>' . $managelabel . ' — ' . get_string('manage_heading', 'local_courseavailabilitydelay') . '</li>'
        . '<li>' . $assignlabel . ' — ' . get_string('assign_heading', 'local_courseavailabilitydelay') . '</li></ul>'
    ));

    $ADMIN->add('localplugins', $settings);
}
