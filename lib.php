<?php
// This file is part of Moodle - http://moodle.org/.
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
 * Core library for local_lockgrades plugin.
 *
 * Contains recursive functions for collecting and locking/unlocking grade items and categories,
 * following hierarchical inheritance. Do NOT call unlock recursively for descendants in cron unless
 * you want to break explicit locks set on subcategories/items!
 *
 * @package   local_lockgrades
 * @copyright 2025, Miguël Dhyne
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Recursively collects grade items and categories related to a given item instance.
 *
 * Adds the given item instance to the visited list and collects:
 * - All grade items matching the item instance
 * - Related grade categories
 * - Subcategories (recursively)
 *
 * @param int   $iteminstance ID of the item instance or category to explore.
 * @param array $visited      Reference to an array containing visited instances and categories.
 *                            (Must be passed by reference.)
 * @return void
 */
function local_lockgrades_recursive_collect($iteminstance, array &$visited) {
    global $DB;
    if (in_array($iteminstance, $visited['instances'], true)) {
        return;
    }
    $visited['instances'][] = $iteminstance;
    $items = $DB->get_records('grade_items', [ 'iteminstance' => $iteminstance ]);
    foreach ($items as $item) {
        if (!in_array($item->categoryid, $visited['categories'], true)) {
            $visited['categories'][] = $item->categoryid;
        }
    }
    $items = $DB->get_records('grade_items', [ 'categoryid' => $iteminstance ]);
    foreach ($items as $item) {
        if (!in_array($item->categoryid, $visited['categories'], true)) {
            $visited['categories'][] = $item->categoryid;
        }
    }
    $subcats = $DB->get_records('grade_categories', [ 'parent' => $iteminstance ]);
    foreach ($subcats as $subcat) {
        local_lockgrades_recursive_collect($subcat->id, $visited);
    }
}

/**
 * Recursively locks or unlocks a grade category, its subcategories, and all grade items within.
 *
 * Updates both the grade_categories record and the grade_item of type 'category',
 * then descends to child categories and their items.
 *
 * @param int   $catid    ID of the grade_categories record.
 * @param bool  $lock     True to lock, false to unlock.
 * @param array $visited  Reference to an array of already visited category IDs.
 *                        (Must be passed by reference.)
 * @param bool  $force    True to force (ignore parent locks on unlock).
 * @return void
 */
function local_lockgrades_recursive_lock($catid, $lock = true, array &$visited = [], $force = false) {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/grade/lib.php');

    if (in_array($catid, $visited, true)) {
        return;
    }
    $visited[] = $catid;
    $now = time();

    // Vérifie héritage UNIQUEMENT si ce n'est pas une action planifiée.
    if (!$force && !$lock) {
        // On cherche un parent qui serait encore verrouillé. Si oui, on ne déverrouille pas.
        $cat = $DB->get_record('grade_categories', [ 'id' => $catid ]);
        if ($cat && isset($cat->path)) {
            $ids = explode('/', trim($cat->path, '/'));
            array_pop($ids);
            foreach ($ids as $pid) {
                if (!$pid) {
                    continue;
                }
                $pgi = \grade_item::fetch([
                    'itemtype' => 'category',
                    'iteminstance' => (int)$pid,
                ]);
                if ($pgi && $pgi->is_locked()) {
                    // Parent encore verrouillé, ne pas déverrouiller ici (hors mode "force").
                    return;
                }
            }
        }
    }

    // Le reste du code est identique, on update la catégorie, les items, les sous-catégories.
    $DB->update_record('grade_categories', (object)[
        'id'           => $catid,
        'locked'       => $lock ? 1 : 0,
        'locktime'     => $lock ? $now : 0,
        'timemodified' => $now,
    ]);

    $catgi = \grade_item::fetch([
        'itemtype'     => 'category',
        'iteminstance' => $catid,
    ]);
    if ($catgi) {
        if ($lock && !$catgi->is_locked()) {
            $catgi->set_locked(true);
        } else if (!$lock && $catgi->is_locked()) {
            $catgi->set_locked(false);
        }
        $catgi->locktime     = $lock ? $now : 0;
        $catgi->timemodified = $now;
        $catgi->update();
    }

    $items = $DB->get_records('grade_items', [ 'categoryid' => $catid ]);
    foreach ($items as $item) {
        $gi = \grade_item::fetch([ 'id' => $item->id ]);
        if ($gi) {
            if ($lock && !$gi->is_locked()) {
                $gi->set_locked(true);
            } else if (!$lock && $gi->is_locked()) {
                $gi->set_locked(false);
            }
            $gi->locktime     = $lock ? $now : 0;
            $gi->timemodified = $now;
            $gi->update();
        }
    }

    $subcats = $DB->get_records('grade_categories', [ 'parent' => $catid ]);
    foreach ($subcats as $subcat) {
        local_lockgrades_recursive_lock($subcat->id, $lock, $visited, $force);
    }
}
