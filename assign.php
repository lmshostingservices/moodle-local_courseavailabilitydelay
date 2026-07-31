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
 * Course Availability Delay — Manual User Override admin page.
 *
 * Allows site administrators to set a custom course availability start date
 * for a specific user, overriding the global per-course delay rule.
 *
 * @package    local_courseavailabilitydelay
 * @copyright  2026 Essay Grader AI
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();
require_capability('local/courseavailabilitydelay:manage', context_system::instance());

// Unlock check.
if (!\local_courseavailabilitydelay\unlock_verifier::is_unlocked()) {
    \core\notification::warning(get_string('unlock_required', 'local_courseavailabilitydelay'));
}

$PAGE->set_url(new moodle_url('/local/courseavailabilitydelay/assign.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('assign_heading', 'local_courseavailabilitydelay'));
$PAGE->set_heading(get_string('assign_heading', 'local_courseavailabilitydelay'));
$PAGE->set_pagelayout('admin');

// Handle delete.
$deleteid = optional_param('delete', 0, PARAM_INT);
if ($deleteid && confirm_sesskey()) {
    $DB->delete_records('local_cad_user_overrides', ['id' => $deleteid]);
    \core\notification::success(get_string('override_deleted', 'local_courseavailabilitydelay'));
    redirect(new moodle_url('/local/courseavailabilitydelay/assign.php'));
}

// Handle save.
if (optional_param('saveoverride', 0, PARAM_BOOL) && confirm_sesskey()) {
    $courseid    = required_param('courseid', PARAM_INT);
    $userid      = required_param('userid', PARAM_INT);
    $customstart = required_param('custom_start', PARAM_RAW);
    $customts    = strtotime($customstart);

    if ($courseid && $userid && $customts !== false) {
        $existing = $DB->get_record('local_cad_user_overrides', [
            'userid'   => $userid,
            'courseid' => $courseid,
        ]);
        $now = time();

        if ($existing) {
            $existing->custom_start  = $customts;
            $existing->timemodified  = $now;
            $DB->update_record('local_cad_user_overrides', $existing);
        } else {
            $DB->insert_record('local_cad_user_overrides', (object)[
                'userid'       => $userid,
                'courseid'     => $courseid,
                'custom_start' => $customts,
                'timecreated'  => $now,
                'timemodified' => $now,
                'createdby'    => $USER->id,
            ]);
        }

        \core\notification::success(get_string('override_saved', 'local_courseavailabilitydelay'));
        redirect(new moodle_url('/local/courseavailabilitydelay/assign.php'));
    }
}

// Fetch all enrolled courses (for dropdown).
$courses = $DB->get_records_menu('course', null, 'fullname ASC', 'id, fullname');
unset($courses[SITEID]);

// Fetch all users for dropdown (site users, non-guest).
$users = $DB->get_records_select_menu(
    'user',
    'deleted = 0 AND suspended = 0 AND id != 1',
    [],
    'lastname ASC, firstname ASC',
    'id, ' . $DB->sql_concat('firstname', "' '", 'lastname', "' (' ", 'email', "')'")
);

// Fetch current overrides for display.
$sql = "SELECT o.*, u.firstname, u.lastname, u.email, c.fullname AS coursename
          FROM {local_cad_user_overrides} o
          JOIN {user} u ON u.id = o.userid
          JOIN {course} c ON c.id = o.courseid
         ORDER BY c.fullname ASC, u.lastname ASC, u.firstname ASC";
$overrides = $DB->get_records_sql($sql);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('assign_heading', 'local_courseavailabilitydelay'));
echo html_writer::tag('p', get_string('assign_desc', 'local_courseavailabilitydelay'));

// Add override form.
echo html_writer::start_tag('form', [
    'method' => 'POST',
    'action' => new moodle_url('/local/courseavailabilitydelay/assign.php'),
    'class'  => 'mb-4',
]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);

// Course selector.
echo html_writer::start_div('mb-3');
echo html_writer::tag('label', get_string('select_course', 'local_courseavailabilitydelay'), ['for' => 'courseid', 'class' => 'form-label']);
$options = html_writer::tag('option', '— ' . get_string('select_course', 'local_courseavailabilitydelay') . ' —', ['value' => '']);
foreach ($courses as $cid => $cname) {
    $options .= html_writer::tag('option', format_string($cname), ['value' => $cid]);
}
echo html_writer::tag('select', $options, ['name' => 'courseid', 'id' => 'courseid', 'class' => 'form-select', 'required' => 'required']);
echo html_writer::end_div();

// User selector.
echo html_writer::start_div('mb-3');
echo html_writer::tag('label', get_string('select_user', 'local_courseavailabilitydelay'), ['for' => 'userid', 'class' => 'form-label']);
$useroptions = html_writer::tag('option', '— ' . get_string('select_user', 'local_courseavailabilitydelay') . ' —', ['value' => '']);
foreach ($users as $uid => $uname) {
    $useroptions .= html_writer::tag('option', $uname, ['value' => $uid]);
}
echo html_writer::tag('select', $useroptions, ['name' => 'userid', 'id' => 'userid', 'class' => 'form-select', 'required' => 'required']);
echo html_writer::end_div();

// Custom start date.
echo html_writer::start_div('mb-3');
echo html_writer::tag('label', get_string('custom_start_date', 'local_courseavailabilitydelay'), ['for' => 'custom_start', 'class' => 'form-label']);
echo html_writer::tag('p', get_string('custom_start_date_desc', 'local_courseavailabilitydelay'), ['class' => 'form-text text-muted']);
echo html_writer::empty_tag('input', [
    'type'     => 'date',
    'name'     => 'custom_start',
    'id'       => 'custom_start',
    'class'    => 'form-control',
    'required' => 'required',
]);
echo html_writer::end_div();

echo html_writer::empty_tag('input', [
    'type'  => 'submit',
    'name'  => 'saveoverride',
    'value' => get_string('save_override', 'local_courseavailabilitydelay'),
    'class' => 'btn btn-primary',
]);
echo html_writer::end_tag('form');

// Current overrides table.
echo html_writer::tag('h3', get_string('current_overrides', 'local_courseavailabilitydelay'), ['class' => 'mt-4']);

if (empty($overrides)) {
    echo html_writer::tag('p', get_string('no_overrides', 'local_courseavailabilitydelay'));
} else {
    $table = new html_table();
    $table->head = [
        get_string('user_label', 'local_courseavailabilitydelay'),
        get_string('course_label', 'local_courseavailabilitydelay'),
        get_string('custom_start_date', 'local_courseavailabilitydelay'),
        get_string('actions', 'local_courseavailabilitydelay'),
    ];
    $table->attributes = ['class' => 'generaltable table table-striped'];

    foreach ($overrides as $override) {
        $deleteurl = new moodle_url('/local/courseavailabilitydelay/assign.php', [
            'delete'  => $override->id,
            'sesskey' => sesskey(),
        ]);
        $deletelink = html_writer::link($deleteurl, get_string('delete_override', 'local_courseavailabilitydelay'), [
            'class'   => 'btn btn-danger btn-sm',
            'onclick' => 'return confirm("Delete this override?");',
        ]);

        $table->data[] = [
            fullname($override) . ' <small class="text-muted">(' . $override->email . ')</small>',
            $override->coursename,
            !empty($override->custom_start)
                ? userdate($override->custom_start, get_string('strftimedatefullshort', 'langconfig'))
                : '—',
            $deletelink,
        ];
    }

    echo html_writer::table($table);
}

// Link back to manage page.
echo html_writer::tag('p',
    html_writer::link(
        new moodle_url('/local/courseavailabilitydelay/manage.php'),
        '← ' . get_string('manage', 'local_courseavailabilitydelay'),
        ['class' => 'btn btn-outline-secondary mt-2']
    )
);

echo $OUTPUT->footer();
