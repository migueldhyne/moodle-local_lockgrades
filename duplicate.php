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
 * Duplicate database recording of scheduled (un)lock
 *
 * Contains all French language strings used by the Wiki Creator plugin,
 * including those for settings, interface labels, and messages.
 *
 * @package   local_lockgrades
 * @copyright 2025, Miguël Dhyne <miguel.dhyne@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_login();
$context = context_system::instance();
require_capability('local/lockgrades:manage', $context);

global $DB, $OUTPUT, $PAGE;

// GET or POST method.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // No need id.
    $task = null;
} else {
    // Duplicate = id needed.
    $id = required_param('id', PARAM_INT);
    require_sesskey();
    $task = $DB->get_record('local_lockgrades_schedule', ['id' => $id], '*', MUST_EXIST);
}

require_once($CFG->dirroot . '/local/lockgrades/edit_form.php');

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/lockgrades/duplicate.php', ['id' => $id ?? '']));
$PAGE->set_title(get_string('scheduledtask_duplicate', 'local_lockgrades'));
$PAGE->set_heading(get_string('scheduledtask_duplicate', 'local_lockgrades'));

// Recorded info without id.
if ($task) {
    $data = (object)[
        'idnumber'      => $task->idnumber,
        'pattern'       => $task->pattern,
        'scheduledtime' => $task->scheduledfor,
        'locktype'      => $task->locktype,
    ];
} else {
    $data = null;
}
$mform = new local_lockgrades_edit_form(null, ['mode' => 'duplicate']);
if ($data) {
    $mform->set_data($data);
}

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/lockgrades/index.php'));
} else if ($newdata = $mform->get_data()) {
    $newtask = new stdClass();
    $newtask->idnumber     = $newdata->idnumber;
    $newtask->pattern      = $newdata->pattern;
    $newtask->scheduledfor = $newdata->scheduledtime;
    $newtask->locktype     = $newdata->locktype;
    $newtask->timecreated  = time();
    $DB->insert_record('local_lockgrades_schedule', $newtask);

    redirect(new moodle_url('/local/lockgrades/index.php'),
        get_string('scheduledtask_duplicated', 'local_lockgrades'),
        null, \core\output\notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('scheduledtask_duplicate', 'local_lockgrades'), 3);
$mform->display();
echo $OUTPUT->footer();
