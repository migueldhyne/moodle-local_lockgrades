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
 * Delete database recording of scheduled (un)lock or executed log
 *
 * @package   local_lockgrades
 * @copyright 2025, Miguël Dhyne <miguel.dhyne@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_login();
$context = context_system::instance();
require_capability('local/lockgrades:manage', $context);

$id = required_param('id', PARAM_INT);
$fromlog = optional_param('fromlog', 0, PARAM_INT); // 0 = schedule, 1 = log

require_sesskey();

if ($fromlog) {
    // Log deleting.
    $record = $DB->get_record('local_lockgrades_log', ['id' => $id], '*', MUST_EXIST);
    $date = $record->executiondate ? userdate($record->executiondate) : '-';
    $msg = get_string('scheduledtask_confirmdelete', 'local_lockgrades', $date);
    $successmsg = get_string('scheduledtask_deleted', 'local_lockgrades');
    $returnurl = new moodle_url('/local/lockgrades/index.php');

    if (optional_param('confirm', 0, PARAM_BOOL)) {
        $DB->delete_records('local_lockgrades_log', ['id' => $id]);
        redirect($returnurl, $successmsg);
    }

} else {
    // Scheduled task deleting.
    $record = $DB->get_record('local_lockgrades_schedule', ['id' => $id], '*', MUST_EXIST);
    $date = $record->scheduledfor ? userdate($record->scheduledfor) : '-';
    $msg = get_string('scheduledtask_confirmdelete', 'local_lockgrades', $date);
    $successmsg = get_string('scheduledtask_deleted', 'local_lockgrades');
    $returnurl = new moodle_url('/local/lockgrades/index.php');

    if (optional_param('confirm', 0, PARAM_BOOL)) {
        $DB->delete_records('local_lockgrades_schedule', ['id' => $id]);
        redirect($returnurl, $successmsg);
    }
}

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/lockgrades/delete.php', ['id' => $id, 'fromlog' => $fromlog]));
$PAGE->set_title(get_string('delete'));
$PAGE->set_heading(get_string('delete'));

echo $OUTPUT->header();
echo $OUTPUT->confirm(
    $msg,
    new moodle_url('/local/lockgrades/delete.php', [
        'id' => $id, 'fromlog' => $fromlog, 'confirm' => 1, 'sesskey' => sesskey(),
    ]),
    $returnurl
);
echo $OUTPUT->footer();
