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
 * Language strings for the local_lockgrades plugin.
 *
 * Contains all English language strings used by the Wiki Creator plugin,
 * including those for settings, interface labels, and messages.
 *
 * @package   local_lockgrades
 * @copyright 2025, Miguël Dhyne <miguel.dhyne@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['actions'] = 'Actions';
$string['backtoform'] = 'Back to the form';
$string['categoryid'] = 'Category';
$string['courseid'] = 'Course';
$string['delete'] = 'Delete';
$string['details'] = 'Details';
$string['duplicate'] = 'Duplicate';
$string['edit'] = 'Edit';
$string['error_invalididnumber'] = 'The specified category ID number was not found.';
$string['error_nofilteredcourses'] = 'No items found in courses whose shortname contains "{$a}".';
$string['error_noidnumber'] = 'Insert a valid ID';
$string['executed'] = 'Executed';
$string['executiondate'] = 'Execution date';
$string['fullname'] = 'Fullname';
$string['history'] = 'Operation history';
$string['idnumber'] = 'ID of categories';
$string['itemid'] = 'Item';
$string['itemtype'] = 'Type';
$string['lock_success'] = 'Grades have been successfully locked.';
$string['lockgrades'] = 'Lock grades';
$string['lockgrades:manage'] = 'Manage grade-locking plugin';
$string['lockgrades_info'] = '<strong>Important note:</strong><br>
When you lock a grade, Moodle may display a warning message in the gradebook as well as a "Recalculate anyway" button.<br>
<ul>
<li>This message means that any changes to grades made via the activity will not be reflected as long as the grade remains locked.</li>
<li>The "Recalculate anyway" button allows you to force the update of grades, even for locked items.</li>
<li>Use this button with caution: any forced modification may overwrite a locked grade and cause inconsistency.</li>
</ul>
This behavior is normal and is intended to secure grade management in Moodle.';
$string['logdetails'] = 'Log';
$string['pattern'] = 'Option: only is shortname contains...';
$string['pattern_help'] = 'This is optional: if left empty, all courses will be processed without distinction.';
$string['pluginname'] = 'Lock Grades';
$string['previewimpact'] = 'Impact overview';
$string['privacy:metadata'] = 'The Lockgrades local plugin only lock grades (it uses no data).';
$string['schedule_success'] = 'The lock/unlock action has been successfully scheduled.';
$string['scheduled'] = 'Scheduled';
$string['scheduledfor'] = 'Execution date';
$string['scheduledtask_confirmdelete'] = 'Do you really want to delete this scheduled task (scheduled for {$a})?';
$string['scheduledtask_deleted'] = 'The scheduled task has been successfully deleted.';
$string['scheduledtask_duplicate'] = 'Duplicate scheduled task';
$string['scheduledtask_duplicated'] = 'The scheduled task has been duplicated.';
$string['scheduledtask_edit'] = 'Edit scheduled task';
$string['scheduledtask_updated'] = 'The scheduled task has been updated.';
$string['scheduledtasks'] = 'Scheduled tasks';
$string['scheduledtime'] = 'Scheduled date and time';
$string['scheduledtime_help'] = 'Select a date and time to perform the lock/unlock action.';
$string['scheduledtype'] = 'Action';
$string['schedulelock'] = 'Schedule lock';
$string['schedulesubmit'] = 'Schedule';
$string['scheduleunlock'] = 'Schedule unlock';
$string['shortname'] = 'Shortname';
$string['status'] = 'Status';
$string['taskname'] = 'Scheduled grade lock/unlock task';
$string['unlock_success'] = 'Grades have been successfully unlocked.';
$string['unlockgrades'] = 'Unlock grades';
$string['warning_content'] = '<strong>Note:</strong> Moodle may display a "Recalculate anyway" button in the gradebook to force an update of even locked grades; use it with caution.';
