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
 * Main file of local_lockgrades plugin.
 *
 * Contains the interface for locking/unlocking and scheduling actions
 * on grade items and categories, with ancestral inheritance rules.
 *
 * @package   local_lockgrades
 * @copyright 2025, Miguël Dhyne
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->dirroot . '/local/lockgrades/form.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->dirroot . '/local/lockgrades/lib.php');

require_login();
require_capability('local/lockgrades:manage', context_system::instance());

$PAGE->set_url(new moodle_url('/local/lockgrades/index.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('pluginname', 'local_lockgrades'));
$PAGE->set_heading(get_string('pluginname', 'local_lockgrades'));

echo $OUTPUT->header();

echo '
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css"/>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<style>
    .lockgrades-history-table { width: 100%; font-size: 15px; }
    .badge-executed { background: #28a745; color: #fff; border-radius: 7px; padding: 2px 8px; font-weight: 600; }
    .badge-scheduled { background: #bdbdbd; color: #222; border-radius: 7px; padding: 2px 8px; font-weight: 600; }
    details { margin-top: 2px; }
    .log-actions { min-width: 90px; }
</style>
';

$mform = new local_lockgrades_form();

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/admin/settings.php'));
} else if ($data = $mform->get_data()) {
    $idnumber      = trim($data->idnumber);
    $pattern       = trim($data->pattern);
    $scheduledtime = !empty($data->scheduledtime) ? $data->scheduledtime : null;

    $gradeitems = $DB->get_records('grade_items', [ 'idnumber' => $idnumber ]);
    if (empty($gradeitems)) {
        echo $OUTPUT->notification(get_string('error_invalididnumber', 'local_lockgrades'));
        echo html_writer::link(new moodle_url('/local/lockgrades/index.php'),
            get_string('backtoform', 'local_lockgrades'));
        echo $OUTPUT->footer();
        exit;
    }

    if ($pattern !== '') {
        $gradeitems = array_filter($gradeitems, function($gi) use ($DB, $pattern) {
            $course = $DB->get_record('course', [ 'id' => $gi->courseid ]);
            return $course && stripos($course->shortname, $pattern) !== false;
        });
        if (empty($gradeitems)) {
            echo $OUTPUT->notification(
                get_string('error_nofilteredcourses', 'local_lockgrades', $pattern)
            );
            echo html_writer::link(new moodle_url('/local/lockgrades/index.php'),
                get_string('backtoform', 'local_lockgrades'));
            echo $OUTPUT->footer();
            exit;
        }
    }

    // Preview impact (dry-run).
    if (!empty($data->preview)) {
        $visited = [ 'instances' => [], 'categories' => [] ];
        foreach ($gradeitems as $gi) {
            local_lockgrades_recursive_collect($gi->iteminstance, $visited);
        }
        $courseids = [];
        if (!empty($visited['instances'])) {
            list($in, $params) = $DB->get_in_or_equal($visited['instances']);
            $ris = $DB->get_records_select('grade_items', "iteminstance $in", $params);
            foreach ($ris as $r) {
                $courseids[] = $r->courseid;
            }
        }
        if (!empty($visited['categories'])) {
            list($in2, $params2) = $DB->get_in_or_equal($visited['categories']);
            $rcs = $DB->get_records_select('grade_categories', "id $in2", $params2);
            foreach ($rcs as $c) {
                if (!empty($c->courseid)) {
                    $courseids[] = $c->courseid;
                }
            }
        }
        $courseids = array_unique($courseids);

        if ($courseids) {
            list($in3, $params3) = $DB->get_in_or_equal($courseids);
            $courses = $DB->get_records_select('course', "id $in3", $params3, 'shortname ASC', 'id,shortname,fullname');
            $table = new html_table();
            $table->id = 'lockgradeslogtable';
            $table->head = [
                get_string('shortname', 'local_lockgrades'),
                get_string('fullname', 'local_lockgrades'),
            ];
            $table->data = [];
            foreach ($courses as $c) {
                $url = new moodle_url('/course/view.php', [ 'id' => $c->id ]);
                $table->data[] = [
                    s($c->shortname),
                    html_writer::link($url, s($c->fullname), [ 'target' => '_blank' ]),
                ];
            }
            echo $OUTPUT->heading(get_string('previewimpact', 'local_lockgrades'));
            echo html_writer::table($table);
        } else {
            echo $OUTPUT->notification(
                get_string('preview_noresults', 'local_lockgrades'),
                \core\output\notification::NOTIFY_WARNING
            );
        }
        echo $OUTPUT->continue_button(new moodle_url('/local/lockgrades/index.php'));
        echo $OUTPUT->footer();
        exit;
    }

    // Lock scheduler.
    if (!empty($data->schedulelock) && $scheduledtime) {
        $record = (object)[
            'idnumber'     => $idnumber,
            'pattern'      => $pattern,
            'locktype'     => 'lock',
            'scheduledfor' => $scheduledtime,
            'timecreated'  => time(),
        ];
        $DB->insert_record('local_lockgrades_schedule', $record);
        echo $OUTPUT->notification(
            get_string('schedule_success', 'local_lockgrades'),
            \core\output\notification::NOTIFY_SUCCESS
        );
        echo $OUTPUT->continue_button(new moodle_url('/local/lockgrades/index.php'));
        echo $OUTPUT->footer();
        exit;
    }

    // Unlock scheduler.
    if (!empty($data->scheduleunlock) && $scheduledtime) {
        $record = (object)[
            'idnumber'     => $idnumber,
            'pattern'      => $pattern,
            'locktype'     => 'unlock',
            'scheduledfor' => $scheduledtime,
            'timecreated'  => time(),
        ];
        $DB->insert_record('local_lockgrades_schedule', $record);
        echo $OUTPUT->notification(
            get_string('schedule_success', 'local_lockgrades'),
            \core\output\notification::NOTIFY_SUCCESS
        );
        echo $OUTPUT->continue_button(new moodle_url('/local/lockgrades/index.php'));
        echo $OUTPUT->footer();
        exit;
    }

    // Immediate unlock.
    if (!empty($data->lock) && empty($scheduledtime)) {
        $transaction = $DB->start_delegated_transaction();
        $impacted = [];
        $logdetails = [];
        foreach ($gradeitems as $gi) {
            local_lockgrades_recursive_lock($gi->iteminstance, true);
            $impacted[] = $gi->iteminstance;
            $logdetails[] = [
                'catid'     => $gi->iteminstance,
                'idnumber'  => $gi->idnumber,
                'courseid'  => $gi->courseid,
                'itemtype'  => $gi->itemtype,
                'itemid'    => $gi->id,
            ];
        }
        $transaction->allow_commit();

        // Immediate log.
        $logrecord = (object)[
            'idnumber'       => $idnumber,
            'pattern'        => $pattern,
            'action'         => 'lock',
            'scheduledfor'   => null,
            'executed'       => 1,
            'executiondate'  => time(),
            'impacted'       => json_encode($impacted),
            'log'            => json_encode($logdetails),
        ];
        $DB->insert_record('local_lockgrades_log', $logrecord);

        echo $OUTPUT->notification(
            get_string('lock_success', 'local_lockgrades'),
            \core\output\notification::NOTIFY_SUCCESS
        );
        echo $OUTPUT->box(
            get_string('lockgrades_info', 'local_lockgrades'),
            'generalbox boxaligncenter', 'lockgrades-info'
        );
    }

    // Immediate unlocking (forbidden if an ancestor remains locked).
    if (!empty($data->unlock) && empty($scheduledtime)) {
        $transaction = $DB->start_delegated_transaction();
        $impacted = [];
        $logdetails = [];
        foreach ($gradeitems as $gi) {
            $cat = $DB->get_record('grade_categories', [ 'id' => $gi->categoryid ]);
            if ($cat && scheduled_task::has_locked_parent($cat->path, $DB)) {
                echo $OUTPUT->notification(
                    get_string('error_unlockancestor', 'local_lockgrades', $gi->categoryid),
                    \core\output\notification::NOTIFY_WARNING
                );
                continue;
            }
            local_lockgrades_recursive_lock($gi->iteminstance, false);
            $impacted[] = $gi->iteminstance;
            $logdetails[] = [
                'catid'     => $gi->iteminstance,
                'idnumber'  => $gi->idnumber,
                'courseid'  => $gi->courseid,
                'itemtype'  => $gi->itemtype,
                'itemid'    => $gi->id,
            ];
        }
        $transaction->allow_commit();

        // Immediate log.
        $logrecord = (object)[
            'idnumber'       => $idnumber,
            'pattern'        => $pattern,
            'action'         => 'unlock',
            'scheduledfor'   => null,
            'executed'       => 1,
            'executiondate'  => time(),
            'impacted'       => json_encode($impacted),
            'log'            => json_encode($logdetails),
        ];
        $DB->insert_record('local_lockgrades_log', $logrecord);

        echo $OUTPUT->notification(
            get_string('unlock_success', 'local_lockgrades'),
            \core\output\notification::NOTIFY_SUCCESS
        );
        echo $OUTPUT->box(
            get_string('lockgrades_info', 'local_lockgrades'),
            'generalbox boxaligncenter', 'lockgrades-info'
        );
    }
}

$mform->display();

// ------------------------------
// Combined display of planned actions AND history (log).
// ------------------------------.
echo $OUTPUT->heading(get_string('history', 'local_lockgrades'), 3);

// Planned and executed are merged.
$records = $DB->get_records_sql("
    SELECT
        id, idnumber, pattern, action, scheduledfor, executed, executiondate, log,
        1 as fromlog
    FROM {local_lockgrades_log}
    UNION ALL
    SELECT
        id, idnumber, pattern, locktype as action, scheduledfor, 0 as executed, NULL as executiondate, NULL as log,
        0 as fromlog
    FROM {local_lockgrades_schedule}
    ORDER BY COALESCE(executiondate, scheduledfor) DESC
");

$table = new html_table();
$table->id = 'lockgrades_history_table';
$table->attributes['class'] = 'lockgrades-history-table';
$table->head = [
    get_string('idnumber', 'local_lockgrades'),
    get_string('pattern', 'local_lockgrades'),
    get_string('scheduledtype', 'local_lockgrades'),
    get_string('scheduledfor', 'local_lockgrades'),
    get_string('status', 'local_lockgrades'),
    get_string('executiondate', 'local_lockgrades'),
    get_string('logdetails', 'local_lockgrades'),
    // Column for actions on scheduled tasks.
    get_string('actions', 'local_lockgrades'),
];
$table->data = [];

foreach ($records as $rec) {
    $status = $rec->executed
        ? '<span class="badge-executed">' . get_string('executed', 'local_lockgrades') . '</span>'
        : '<span class="badge-scheduled">' . get_string('scheduled', 'local_lockgrades') . '</span>';

    $scheduled = $rec->scheduledfor ? userdate($rec->scheduledfor, get_string('strftimedatetime', 'langconfig')) : '';
    $execution = $rec->executiondate ? userdate($rec->executiondate, get_string('strftimedatetime', 'langconfig')) : '';

    // Fold-out detail only for actions carried out.
    $details = '';
    if (!empty($rec->log)) {
        $detaildata = json_decode($rec->log, true);
        if (is_array($detaildata)) {
            $details .= '<details><summary>' . get_string('details', 'local_lockgrades') . '</summary>';
            $details .= '<ul style="margin-left:1em;">';
            foreach ($detaildata as $d) {
                $details .= '<li>'
                    . get_string('categoryid', 'local_lockgrades') . ': ' . s($d['catid'] ?? '')
                    . ' | ' . get_string('itemid', 'local_lockgrades') . ': ' . s($d['itemid'] ?? '')
                    . ' | ' . get_string('itemtype', 'local_lockgrades') . ': ' . s($d['itemtype'] ?? '')
                    . ' | ' . get_string('courseid', 'local_lockgrades') . ': ' . s($d['courseid'] ?? '')
                    . '</li>';
            }
            $details .= '</ul></details>';
        }
    }

    // Actions: scheduled (edit/dup/del) or log (just del).
    $actions = '';
    if (!$rec->executed && isset($rec->fromlog) && $rec->fromlog == 0) {
        $edit   = html_writer::link(
            new moodle_url('/local/lockgrades/edit.php', [ 'id' => $rec->id, 'action' => 'edit', 'sesskey' => sesskey() ]),
            '✏️', [ 'title' => get_string('edit', 'local_lockgrades') ]
        );
        $dup    = html_writer::link(
            new moodle_url('/local/lockgrades/duplicate.php', [ 'id' => $rec->id, 'sesskey' => sesskey() ]),
            '📋', [ 'title' => get_string('duplicate', 'local_lockgrades') ]
        );
        $delete = html_writer::link(
            new moodle_url('/local/lockgrades/delete.php', [
                'id' => $rec->id, 'fromlog' => 0, 'sesskey' => sesskey(),
            ]),
            '🗑️', [ 'title' => get_string('delete', 'local_lockgrades') ]
        );
        $actions = '<span class="log-actions">' . $edit . ' ' . $dup . ' ' . $delete . '</span>';
    } else if ($rec->executed && isset($rec->fromlog) && $rec->fromlog == 1) {
        // History/log : deleting.
        $delete = html_writer::link(
            new moodle_url('/local/lockgrades/delete.php', [
                'id' => $rec->id, 'fromlog' => 1, 'sesskey' => sesskey(),
            ]),
            '🗑️', [ 'title' => get_string('delete', 'local_lockgrades') ]
        );
        $actions = '<span class="log-actions">' . $delete . '</span>';
    }

    $table->data[] = [
        s($rec->idnumber),
        s($rec->pattern),
        s($rec->action),
        $scheduled,
        $status,
        $execution,
        $details,
        $actions,
    ];
}
echo html_writer::table($table);

// Script DataTables with filters, research, responsive.
echo "
<script>
$(document).ready(function() {
    $('#lockgrades_history_table').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/2.0.8/i18n/fr-FR.json' },
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        responsive: true,
        order: [[5, 'desc']]
    });
});
</script>
";

echo $OUTPUT->footer();
