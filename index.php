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
 *
 * This script initializes the Lock/Unlock Grades plugin page, enforcing login
 * and capability checks before displaying a form to lock or unlock grade items
 * by their category idnumber. It then processes form submissions within a
 * database transaction, recursively updating the grade_items and
 * grade_categories tables to apply or remove locks.
 *
 * @package   local_lockgrades
 * @copyright 2025, Miguël Dhyne <miguel.dhyne@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../../config.php');
require_once($CFG->dirroot . '/local/lockgrades/form.php');
require_once($CFG->libdir . '/gradelib.php');

require_login();
require_capability('local/lockgrades:manage', context_system::instance());

$PAGE->set_url(new moodle_url('/local/lockgrades/index.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('pluginname', 'local_lockgrades'));
$PAGE->set_heading(get_string('pluginname', 'local_lockgrades'));

$mform = new local_lockgrades_form();

echo $OUTPUT->header();

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/admin/settings.php'));
} else if ($data = $mform->get_data()) {

    $idnumber = trim($data->idnumber);

    // We start with all grade_items having this idnumber (and therefore potentially several).
    $gradeitems = $DB->get_records('grade_items', ['idnumber' => $idnumber]);

    if (!$gradeitems) {
        echo $OUTPUT->notification(get_string('error_invalididnumber', 'local_lockgrades'));
        echo html_writer::link(new moodle_url('/local/lockgrades/index.php'), get_string('backtoform', 'local_lockgrades'));
        echo $OUTPUT->footer();
        exit;
    }

    $transaction = $DB->start_delegated_transaction();

    foreach ($gradeitems as $gradeitem) {
        // Run recursion for each item.
        if (!empty($data->lock)) {
            local_lockgrades_recursive_lock($gradeitem->iteminstance);
        } else if (!empty($data->unlock)) {
            local_lockgrades_recursive_lock($gradeitem->iteminstance, false);
        }
    }

    $transaction->allow_commit();

    $message = !empty($data->lock) ? get_string('lock_success', 'local_lockgrades')
    : get_string('unlock_success', 'local_lockgrades');
    echo $OUTPUT->notification($message);

    // Display of explanatory insert AFTER lock/unlock action.
    echo $OUTPUT->box(get_string('lockgrades_info', 'local_lockgrades'), 'generalbox boxaligncenter', 'lockgrades-info');
}

$mform->display();
echo $OUTPUT->footer();

/**
 * Recursively locks (or unlocks) all items and subcategories starting from an iteminstance of grade_items.
 *
 * @param int $iteminstance The starting iteminstance (main category)
 * @param bool $lock true = lock, false = unlock
 * @param array &$visited Array of IDs already processed (avoids infinite loops).
 */
function local_lockgrades_recursive_lock($iteminstance, $lock = true, &$visited = []) {
    global $DB;

    if (in_array($iteminstance, $visited, true)) {
        return;
    }
    $visited[] = $iteminstance;

    $now = time();

    // STEP 1 & 3: (Un)lock all items with iteminstance OR categoryid = $iteminstance.
    $items = $DB->get_records('grade_items', [
        'iteminstance' => $iteminstance,
    ]);
    foreach ($items as $item) {
        $gi = grade_item::fetch(['id' => $item->id]);
        if ($gi) {
            $gi->set_locked($lock);
            $gi->locktime = $lock ? $now : 0;
            $gi->timemodified = $now;
            $gi->update();
        }
    }
    // We also do categoryid.
    $items = $DB->get_records('grade_items', [
        'categoryid' => $iteminstance,
    ]);
    foreach ($items as $item) {
        $gi = grade_item::fetch(['id' => $item->id]);
        if ($gi) {
            $gi->set_locked($lock);
            $gi->locktime = $lock ? $now : 0;
            $gi->timemodified = $now;
            $gi->update();
        }
    }

    // STEP 2 & 4: search for sub-categories.
    $subcategories = $DB->get_records('grade_categories', ['parent' => $iteminstance]);
    foreach ($subcategories as $subcategory) {
        // Recursive call for each sub-category found.
        local_lockgrades_recursive_lock($subcategory->id, $lock, $visited);
    }
}
