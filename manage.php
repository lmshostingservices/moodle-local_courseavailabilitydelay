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
 * Course Availability Delay — Bulk CSV Import admin page.
 *
 * Allows site administrators to upload a CSV of course delay rules.
 * CSV format: courseid, delay_days[, fixed_date]
 *
 * @package    local_courseavailabilitydelay
 * @copyright  2026 Essay Grader AI
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/csvlib.class.php');

require_login();
require_capability('local/courseavailabilitydelay:manage', context_system::instance());

// Unlock check.
if (!\local_courseavailabilitydelay\unlock_verifier::is_unlocked()) {
    \core\notification::warning(get_string('unlock_required', 'local_courseavailabilitydelay'));
}

$PAGE->set_url(new moodle_url('/local/courseavailabilitydelay/manage.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('manage_heading', 'local_courseavailabilitydelay'));
$PAGE->set_heading(get_string('manage_heading', 'local_courseavailabilitydelay'));
$PAGE->set_pagelayout('admin');

// Handle delete action.
$deleteid = optional_param('delete', 0, PARAM_INT);
if ($deleteid && confirm_sesskey()) {
    $DB->delete_records('local_cad_course_rules', ['id' => $deleteid]);
    \core\notification::success(get_string('rule_deleted', 'local_courseavailabilitydelay'));
    redirect(new moodle_url('/local/courseavailabilitydelay/manage.php'));
}

// Handle CSV upload.
$errors   = [];
$imported = 0;

if (optional_param('submitupload', 0, PARAM_BOOL) && confirm_sesskey()) {
    $file = $_FILES['csvfile'] ?? null;

    if (!$file || empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        $errors[] = get_string('import_error_empty', 'local_courseavailabilitydelay');
    } else {
        $content = file_get_contents($file['tmp_name']);
        if (empty($content)) {
            $errors[] = get_string('import_error_empty', 'local_courseavailabilitydelay');
        } else {
            $lines = array_filter(array_map('trim', explode("\n", str_replace("\r", "", $content))));
            $lines = array_values($lines);

            // Validate header.
            $header = array_map('strtolower', array_map('trim', str_getcsv($lines[0])));
            if (!in_array('courseid', $header) || !in_array('delay_days', $header)) {
                $errors[] = get_string('import_error_header', 'local_courseavailabilitydelay');
            } else {
                $colidx     = array_flip($header);
                $courseidx  = $colidx['courseid'];
                $delayidx   = $colidx['delay_days'];
                $fixeddateidx = isset($colidx['fixed_date']) ? $colidx['fixed_date'] : null;

                for ($i = 1; $i < count($lines); $i++) {
                    $row = array_map('trim', str_getcsv($lines[$i]));
                    if (empty($row) || (count($row) === 1 && $row[0] === '')) {
                        continue;
                    }

                    $rownumber = $i + 1;

                    if (!isset($row[$courseidx]) || !is_numeric($row[$courseidx])) {
                        $errors[] = get_string('import_error_row', 'local_courseavailabilitydelay', (object)[
                            'row'     => $rownumber,
                            'message' => 'courseid must be a number',
                        ]);
                        continue;
                    }

                    if (!isset($row[$delayidx]) || !is_numeric($row[$delayidx])) {
                        $errors[] = get_string('import_error_row', 'local_courseavailabilitydelay', (object)[
                            'row'     => $rownumber,
                            'message' => 'delay_days must be a number',
                        ]);
                        continue;
                    }

                    $courseid  = (int)$row[$courseidx];
                    $delaydays = (int)$row[$delayidx];

                    if ($delaydays < 0) {
                        $errors[] = get_string('import_error_row', 'local_courseavailabilitydelay', (object)[
                            'row'     => $rownumber,
                            'message' => 'delay_days cannot be negative',
                        ]);
                        continue;
                    }

                    $fixeddate = null;

                    if ($fixeddateidx !== null && isset($row[$fixeddateidx]) && $row[$fixeddateidx] !== '') {
                        $ts = strtotime($row[$fixeddateidx]);
                        if ($ts === false) {
                            $errors[] = get_string('import_error_row', 'local_courseavailabilitydelay', (object)[
                                'row'     => $rownumber,
                                'message' => 'fixed_date must be in YYYY-MM-DD format',
                            ]);
                            continue;
                        }
                        $fixeddate = $ts;
                    }

                    // Upsert.
                    $existing = $DB->get_record('local_cad_course_rules', ['courseid' => $courseid]);
                    $now      = time();

                    if ($existing) {
                        $existing->delay_days   = $delaydays;
                        $existing->fixed_date   = $fixeddate;
                        $existing->timemodified = $now;
                        $DB->update_record('local_cad_course_rules', $existing);
                    } else {
                        $DB->insert_record('local_cad_course_rules', (object)[
                            'courseid'     => $courseid,
                            'delay_days'   => $delaydays,
                            'fixed_date'   => $fixeddate,
                            'timecreated'  => $now,
                            'timemodified' => $now,
                            'createdby'    => $USER->id,
                        ]);
                    }

                    $imported++;
                }

                if ($imported > 0 && empty($errors)) {
                    \core\notification::success(
                        get_string('import_success', 'local_courseavailabilitydelay', $imported)
                    );
                    redirect(new moodle_url('/local/courseavailabilitydelay/manage.php'));
                }
            }
        }
    }
}

// Fetch all current rules for display.
$sql = "SELECT r.*, c.fullname AS coursename
          FROM {local_cad_course_rules} r
          LEFT JOIN {course} c ON c.id = r.courseid
         ORDER BY c.fullname ASC";
$rules = $DB->get_records_sql($sql);

// Build CSV template download.
if (optional_param('downloadtemplate', 0, PARAM_BOOL)) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="courseavailabilitydelay_template.csv"');
    echo "courseid,delay_days,fixed_date\n";
    echo "123,14,\n";
    echo "456,0,2026-09-01\n";
    die;
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manage_heading', 'local_courseavailabilitydelay'));

// Description.
echo html_writer::tag('p', get_string('manage_desc', 'local_courseavailabilitydelay'));

// CSV format info.
echo html_writer::tag('h4', get_string('csv_format', 'local_courseavailabilitydelay'));
echo html_writer::tag('p', get_string('csv_format_desc', 'local_courseavailabilitydelay'));
echo html_writer::start_tag('ul');
echo html_writer::tag('li', '<code>courseid</code> — ' . get_string('csv_col_courseid', 'local_courseavailabilitydelay'));
echo html_writer::tag('li', '<code>delay_days</code> — ' . get_string('csv_col_delay_days', 'local_courseavailabilitydelay'));
echo html_writer::tag('li', '<code>fixed_date</code> — ' . get_string('csv_col_fixed_date', 'local_courseavailabilitydelay'));
echo html_writer::end_tag('ul');

// Download template link.
$templateurl = new moodle_url('/local/courseavailabilitydelay/manage.php', ['downloadtemplate' => 1]);
echo html_writer::link($templateurl, get_string('download_template', 'local_courseavailabilitydelay'), ['class' => 'btn btn-secondary btn-sm mb-3']);

// Show errors.
foreach ($errors as $error) {
    echo $OUTPUT->notification($error, 'notifyerror');
}

// Upload form.
echo html_writer::start_tag('form', [
    'method'  => 'POST',
    'enctype' => 'multipart/form-data',
    'action'  => new moodle_url('/local/courseavailabilitydelay/manage.php'),
]);
echo html_writer::input_hidden_params(new moodle_url('/local/courseavailabilitydelay/manage.php'));
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);

echo html_writer::start_div('mb-3');
echo html_writer::tag('label', get_string('csvfile', 'local_courseavailabilitydelay'), ['for' => 'csvfile', 'class' => 'form-label']);
echo html_writer::empty_tag('input', ['type' => 'file', 'name' => 'csvfile', 'id' => 'csvfile', 'accept' => '.csv', 'class' => 'form-control']);
echo html_writer::end_div();

echo html_writer::empty_tag('input', [
    'type'  => 'submit',
    'name'  => 'submitupload',
    'value' => get_string('upload_csv', 'local_courseavailabilitydelay'),
    'class' => 'btn btn-primary',
]);
echo html_writer::end_tag('form');

// Current rules table.
echo html_writer::tag('h3', get_string('current_rules', 'local_courseavailabilitydelay'), ['class' => 'mt-4']);

if (empty($rules)) {
    echo html_writer::tag('p', get_string('no_rules', 'local_courseavailabilitydelay'));
} else {
    $table = new html_table();
    $table->head = [
        get_string('course_name', 'local_courseavailabilitydelay'),
        'Course ID',
        get_string('delay_days_label', 'local_courseavailabilitydelay'),
        get_string('fixed_date_label', 'local_courseavailabilitydelay'),
        get_string('actions', 'local_courseavailabilitydelay'),
    ];
    $table->attributes = ['class' => 'generaltable table table-striped'];

    foreach ($rules as $rule) {
        $deleteurl = new moodle_url('/local/courseavailabilitydelay/manage.php', [
            'delete'  => $rule->id,
            'sesskey' => sesskey(),
        ]);
        $deletelink = html_writer::link($deleteurl, get_string('delete_rule', 'local_courseavailabilitydelay'), [
            'class'   => 'btn btn-danger btn-sm',
            'onclick' => 'return confirm("Delete this rule?");',
        ]);

        $table->data[] = [
            $rule->coursename ?? '(Course ' . $rule->courseid . ')',
            $rule->courseid,
            $rule->delay_days . ' days',
            !empty($rule->fixed_date) ? userdate($rule->fixed_date, get_string('strftimedatefullshort', 'langconfig')) : '—',
            $deletelink,
        ];
    }

    echo html_writer::table($table);
}

// Link to assign page.
echo html_writer::tag('p',
    html_writer::link(
        new moodle_url('/local/courseavailabilitydelay/assign.php'),
        get_string('assign', 'local_courseavailabilitydelay') . ' →',
        ['class' => 'btn btn-outline-primary mt-2']
    )
);

echo $OUTPUT->footer();
