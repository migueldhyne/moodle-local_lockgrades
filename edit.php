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
 * Edit database records of scheduled (un)lock
 *
 * Contains all French language strings used by the Wiki Creator plugin,
 * including those for settings, interface labels, and messages.
 *
 * @package   local_lockgrades
 * @copyright 2025, Miguël Dhyne <miguel.dhyne@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/lockgrades/edit_form.php');
require_login();
$context = context_system::instance();
require_capability('local/lockgrades:manage', $context);

global $DB, $OUTPUT, $PAGE;

$id = required_param('id', PARAM_INT); // Scheduled task id.
require_sesskey();

// Task to edit.
$task = $DB->get_record('local_lockgrades_schedule', ['id' => $id], '*', MUST_EXIST);

require_once($CFG->dirroot . '/local/lockgrades/form.php');

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/lockgrades/edit.php', ['id' => $id]));
$PAGE->set_title(get_string('scheduledtask_edit', 'local_lockgrades'));
$PAGE->set_heading(get_string('scheduledtask_edit', 'local_lockgrades'));

// Recorded datas.
$data = (object)[
    'id'            => $task->id,
    'idnumber'      => $task->idnumber,
    'pattern'       => $task->pattern,
    'scheduledtime' => $task->scheduledfor,
    'locktype'      => $task->locktype,
];

// Edit.
$mform = new local_lockgrades_edit_form(null, []);
$mform->set_data($data);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/lockgrades/index.php'));
} else if ($newdata = $mform->get_data()) {
    // Upgrade info.
    $task->idnumber     = $newdata->idnumber;
    $task->pattern      = $newdata->pattern;
    $task->scheduledfor = $newdata->scheduledtime;
    $task->locktype     = $newdata->locktype;
    $DB->update_record('local_lockgrades_schedule', $task);

    redirect(new moodle_url('/local/lockgrades/index.php'),
    get_string('scheduledtask_updated', 'local_lockgrades'), null, \core\output\notification::NOTIFY_SUCCESS);
    ;
}

// Form display.
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('scheduledtask_edit', 'local_lockgrades'), 3);
$mform->display();
echo $OUTPUT->footer();
