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

$string['backtoform'] = 'Back to the form';
$string['error_invalididnumber'] = 'The specified category ID number was not found.';
$string['error_noidnumber']    = 'Insert a valid ID';
$string['idnumber']            = 'ID of categories';
$string['lock_success'] = 'Grades have been successfully locked.';
$string['lockgrades']          = 'Lock grades';
$string['lockgrades:manage']   = 'Manage grade-locking plugin';
$string['lockgrades_info'] = '<strong>Important note:</strong><br>
When you lock a grade, Moodle may display a warning message in the gradebook as well as a "Recalculate anyway" button.<br>
<ul>
<li>This message means that any changes to grades made via the activity will not be reflected as long as the grade remains locked.</li>
<li>The "Recalculate anyway" button allows you to force the update of grades, even for locked items.</li>
<li>Use this button with caution: any forced modification may overwrite a locked grade and cause inconsistency.</li>
</ul>
This behavior is normal and is intended to secure grade management in Moodle.';
$string['pluginname']          = 'Lock Grades';
$string['privacy:metadata']    = 'The Lockgrades local plugin only lock grades (it uses no data).';
$string['unlock_success'] = 'Grades have been successfully unlocked.';
$string['unlockgrades']        = 'Unlock grades';
