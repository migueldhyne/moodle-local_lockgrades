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

require_login();
// Only an administrator can access this plugin.
require_capability('moodle/site:config', context_system::instance());

$PAGE->set_url(new moodle_url('/local/lockgrades/index.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('pluginname', 'local_lockgrades'));
$PAGE->set_heading(get_string('pluginname', 'local_lockgrades'));

$mform = new local_lockgrades_form();

echo $OUTPUT->header();

if ($mform->is_cancelled()) {
    // Redirect to the configuration page in the event of cancellation.
    redirect(new moodle_url('/admin/settings.php'));
} else if ($data = $mform->get_data()) {
    $idnumber = trim($data->idnumber);
    if (empty($idnumber)) {
        echo $OUTPUT->notification(get_string('error_noidnumber', 'local_lockgrades'));
    } else {
        // Use of a transaction to guarantee the integrity of updates.
        $transaction = $DB->start_delegated_transaction();
        if (!empty($data->lock)) {
            lock_grade_items_recursive($idnumber);
            $message = get_string('lock_success', 'local_lockgrades');
        } else if (!empty($data->unlock)) {
            unlock_grade_items_recursive($idnumber);
            $message = get_string('unlock_success', 'local_lockgrades');
        }
        $transaction->allow_commit();
        echo $OUTPUT->notification($message);
    }
}

$mform->display();
echo $OUTPUT->footer();

/**
 * Recursively locks the grade elements for the initial category and its sub-categories.
 *
 * @param string $idnumber The identifier of the initial category.
 */
function lock_grade_items_recursive($idnumber) {
    global $DB;
    $timestamp = time();

    // Step 1: Lock the initial element and its associated elements.
    $sql = "UPDATE {grade_items}
            SET locked = ?,
                timemodified = ?,
                locktime = ?
            WHERE iteminstance IN (SELECT iteminstance FROM {grade_items} WHERE idnumber = ?)
               OR categoryid IN (SELECT iteminstance FROM {grade_items} WHERE idnumber = ?)";
    $DB->execute($sql, [$timestamp, $timestamp, $timestamp, $idnumber, $idnumber]);

    // Retrieve the initial element corresponding to the supplied idnumber.
    $gradeitem = $DB->get_record('grade_items', ['idnumber' => $idnumber]);
    if (!$gradeitem) {
         return;
    }

    // Step 2: Retrieve the sub-categories of the initial element.
    $subcategories = $DB->get_records_sql("SELECT id FROM {grade_categories} WHERE parent = ?", [$gradeitem->iteminstance]);
    if ($subcategories) {
        foreach ($subcategories as $subcategory) {
             // Step 3: Lock sub-category items.
             $sqlupdate = "UPDATE {grade_items}
                           SET locked = ?,
                               timemodified = ?,
                               locktime = ?
                           WHERE iteminstance = ? OR categoryid = ?";
             $DB->execute($sqlupdate, [$timestamp, $timestamp, $timestamp, $subcategory->id, $subcategory->id]);

             // Step 4: Recursive call to process nested sub-categories.
             lock_subcategories_recursive($subcategory->id);
        }
    }
}

/**
 * Recursive function for locking sub-categories from a given parent identifier.
 *
 * @param int $parentid The id of the parent category.
 */
function lock_subcategories_recursive($parentid) {
    global $DB;
    $timestamp = time();
    $sqlupdate = "UPDATE {grade_items}
                  SET locked = ?,
                      timemodified = ?,
                      locktime = ?
                  WHERE iteminstance = ? OR categoryid = ?";
    $DB->execute($sqlupdate, [$timestamp, $timestamp, $timestamp, $parentid, $parentid]);

    // Retrieve sub-categories from the current parent.
    $subcategories = $DB->get_records_sql("SELECT id FROM {grade_categories} WHERE parent = ?", [$parentid]);
    if ($subcategories) {
        foreach ($subcategories as $subcategory) {
             $DB->execute($sqlupdate, [$timestamp, $timestamp, $timestamp, $subcategory->id, $subcategory->id]);
             lock_subcategories_recursive($subcategory->id);
        }
    }
}

/**
 * Recursively unlocks grade elements for the initial category and its sub-categories.
 *
 * @param string $idnumber The identifier of the initial category.
 */
function unlock_grade_items_recursive($idnumber) {
    global $DB;
    $timestamp = time();

    // Step 1: Unlock the initial element and its associated elements by setting locked and locktime to NULL.
    $sql = "UPDATE {grade_items}
        SET locked = 0,
            timemodified = ?,
            locktime = 0
            WHERE iteminstance IN (SELECT iteminstance FROM {grade_items} WHERE idnumber = ?)
               OR categoryid IN (SELECT iteminstance FROM {grade_items} WHERE idnumber = ?)";
    $DB->execute($sql, [$timestamp, $idnumber, $idnumber]);

    // Retrieve the initial element corresponding to the supplied idnumber.
    $gradeitem = $DB->get_record('grade_items', ['idnumber' => $idnumber]);
    if (!$gradeitem) {
         return;
    }

    // Step 2: Retrieve the sub-categories of the initial element.
    $subcategories = $DB->get_records_sql("SELECT id FROM {grade_categories} WHERE parent = ?", [$gradeitem->iteminstance]);
    if ($subcategories) {
        foreach ($subcategories as $subcategory) {
             // Step 3: Unlock sub-category items.
             $sqlupdate = "UPDATE {grade_items}
        SET locked = 0,
            timemodified = ?,
            locktime = 0
                           WHERE iteminstance = ? OR categoryid = ?";
             $DB->execute($sqlupdate, [$timestamp, $subcategory->id, $subcategory->id]);

             // Step 4: Recursive call to process nested sub-categories.
             unlock_subcategories_recursive($subcategory->id);
        }
    }
}

/**
 * Recursive function to unlock sub-categories from a given parent identifier.
 *
 * @param int $parentid The id of the parent category.
 */
function unlock_subcategories_recursive($parentid) {
    global $DB;
    $timestamp = time();
    $sqlupdate = "UPDATE {grade_items}
        SET locked = 0,
            timemodified = ?,
            locktime = 0
                  WHERE iteminstance = ? OR categoryid = ?";
    $DB->execute($sqlupdate, [$timestamp, $parentid, $parentid]);

    // Retrieve sub-categories from the current parent.
    $subcategories = $DB->get_records_sql("SELECT id FROM {grade_categories} WHERE parent = ?", [$parentid]);
    if ($subcategories) {
        foreach ($subcategories as $subcategory) {
             $DB->execute($sqlupdate, [$timestamp, $subcategory->id, $subcategory->id]);
             unlock_subcategories_recursive($subcategory->id);
        }
    }
}
