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
 * Scheduled task for local_lockgrades plugin.
 *
 * Executes scheduled lock/unlock jobs and applies inherited locking rules
 * for newly created or updated grade items and categories.
 *
 * Double tracking is used: both the last successful run timestamp and
 * the highest processed ID for grade items and categories are recorded,
 * ensuring maximum reliability even on large sites or in case of failures.
 *
 * @package   local_lockgrades
 * @copyright 2025, Miguël Dhyne <miguel.dhyne@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_lockgrades\task;

use core\task\scheduled_task as core_scheduled_task;
use moodle_exception;

/**
 * Defines the scheduled task for the local_lockgrades plugin.
 */
class scheduled_task extends core_scheduled_task {

    /**
     * Returns the name of the scheduled task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('taskname', 'local_lockgrades');
    }

    /**
     * Executes the scheduled task.
     *
     * Obtains a cron lock, processes scheduled jobs, and applies inheritance for new or updated grade items and categories.
     *
     * @return void
     */
    public function execute() {
        global $DB, $CFG;

        require_once($CFG->dirroot . '/grade/lib.php');
        require_once($CFG->dirroot . '/local/lockgrades/lib.php');

        // Obtain cron lock to prevent concurrent runs.
        $factory = \core\lock\lock_config::get_lock_factory('cron');
        $lock    = $factory ? $factory->get_lock('local_lockgrades_cron', 10) : null;
        if ($factory && !$lock) {
            return;
        }

        try {
            $now    = time();
            $margin = 3600; // 1 hour margin for double tracking.

            // Tracking.
            $lastcron   = get_config('local_lockgrades', 'lastcron') ?: 0;
            $lastitemid = get_config('local_lockgrades', 'last_gradeitemid') ?: 0;
            $lastcatid  = get_config('local_lockgrades', 'last_gradecatid') ?: 0;

            // Process scheduled jobs.
            $jobs = $DB->get_records_select('local_lockgrades_schedule', 'scheduledfor <= ?', [$now]);
            if ($jobs) {
                foreach ($jobs as $job) {
                    $gradeitems = $DB->get_records('grade_items', ['idnumber' => $job->idnumber]);
                    if ($job->pattern !== '') {
                        $gradeitems = array_filter($gradeitems, function($gi) use ($DB, $job) {
                            $course = $DB->get_record('course', ['id' => $gi->courseid]);
                            return $course && stripos($course->shortname, $job->pattern) !== false;
                        });
                    }
                    $actionlock = ($job->locktype === 'lock');

                    // Log tracking.
                    $impactedids = [];
                    $logdetails = [];
                    foreach ($gradeitems as $gi) {
                        try {
                            // Always apply to the target category.
                            if ($gi->itemtype == 'category') {
                                $catid = $gi->iteminstance;
                            } else {
                                $catid = $gi->categoryid;
                            }
                            $visited = [];
                            // Force lock for scheduled job execution.
                            local_lockgrades_recursive_lock($catid, $actionlock, $visited, true);

                            $impactedids[] = $catid;
                            $logdetails[] = [
                                'catid'     => $catid,
                                'idnumber'  => $gi->idnumber,
                                'courseid'  => $gi->courseid,
                                'itemtype'  => $gi->itemtype,
                                'itemid'    => $gi->id,
                            ];
                        } catch (moodle_exception $e) {
                            debugging('Error in scheduled_task (job): ' . $e->getMessage(), DEBUG_DEVELOPER);
                        }
                    }

                    // Add the log to the local table.
                    $logrecord = (object)[
                        'idnumber'       => $job->idnumber,
                        'pattern'        => $job->pattern,
                        'action'         => $job->locktype,
                        'scheduledfor'   => $job->scheduledfor,
                        'executed'       => 1,
                        'executiondate'  => $now,
                        'impacted'       => json_encode($impactedids),
                        'log'            => json_encode($logdetails),
                    ];
                    $DB->insert_record('local_lockgrades_log', $logrecord);

                    $DB->delete_records('local_lockgrades_schedule', ['id' => $job->id]);
                }
            }

            // Inheritance: new or updated grade items.
            $maxitemid = $lastitemid;
            $sinceitem = max($lastcron - $margin, 0);
            $items = $DB->get_records_select('grade_items',
                '(timemodified >= ? OR id > ?)',
                [$sinceitem, $lastitemid]
            );
            foreach ($items as $item) {
                $maxitemid = max($maxitemid, $item->id);
                if ($item->itemtype === 'category') {
                    continue;
                }
                $category = $DB->get_record('grade_categories', ['id' => $item->categoryid]);
                if ($category && (self::has_locked_parent($category->path, $DB) || self::is_category_locked($category->id, $DB))) {
                    try {
                        $gi = \grade_item::fetch(['id' => $item->id]);
                        if ($gi && !$gi->is_locked()) {
                            $gi->set_locked(true);
                            $gi->locktime     = $now;
                            $gi->timemodified = $now;
                            $gi->update();
                        }
                    } catch (moodle_exception $e) {
                        debugging('Error locking item (inheritance): ' . $e->getMessage(), DEBUG_DEVELOPER);
                    }
                }
            }

            // Inheritance: new or updated grade categories.
            $maxcatid = $lastcatid;
            $sincecat = max($lastcron - $margin, 0);
            $cats = $DB->get_records_select('grade_categories',
                '(timemodified >= ? OR id > ?)',
                [$sincecat, $lastcatid]
            );
            foreach ($cats as $cat) {
                $maxcatid = max($maxcatid, $cat->id);
                if (self::has_locked_parent($cat->path, $DB)) {
                    try {
                        $visited = [];
                        // Inheritance lock for new/updated category.
                        local_lockgrades_recursive_lock($cat->id, true, $visited, false);
                    } catch (moodle_exception $e) {
                        debugging('Error locking category (inheritance): ' . $e->getMessage(), DEBUG_DEVELOPER);
                    }
                }
            }

            // Update tracking markers.
            set_config('lastcron', $now, 'local_lockgrades');
            set_config('last_gradeitemid', $maxitemid, 'local_lockgrades');
            set_config('last_gradecatid', $maxcatid, 'local_lockgrades');

        } finally {
            if ($lock) {
                $lock->release();
            }
        }
    }

    /**
     * Checks if any parent in the category path is already locked.
     *
     * For each category ID in the path, loads the corresponding grade_item
     * of type 'category' (iteminstance = catid) and checks its locked flag.
     *
     * @param string          $path Path like '/1/23/456'
     * @param \moodle_database $DB   Moodle database handle
     * @return bool True if any parent grade_item is locked.
     */
    protected static function has_locked_parent($path, $DB) {
        if (empty($path)) {
            return false;
        }
        $ids = explode('/', trim($path, '/'));
        array_pop($ids); // Remove the current category itself.
        foreach ($ids as $catid) {
            if (empty($catid)) {
                continue;
            }
            $gi = \grade_item::fetch([
                'itemtype'     => 'category',
                'iteminstance' => (int)$catid,
            ]);
            if ($gi && $gi->is_locked()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks if a given grade category is locked.
     *
     * Loads the grade_item of type 'category' for the provided category ID and returns its locked status.
     *
     * @param int $catid The ID of the grade category to check.
     * @param \moodle_database $DB The Moodle database handle.
     * @return bool True if the category is locked, false otherwise.
     */
    protected static function is_category_locked($catid, $DB) {
        $gi = \grade_item::fetch([
        'itemtype'     => 'category',
        'iteminstance' => (int)$catid,
        ]);
        return $gi && $gi->is_locked();
    }
}
