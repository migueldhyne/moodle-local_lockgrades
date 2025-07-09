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
 * Upgrade script for local_lockgrades plugin.
 *
 * @package    local_lockgrades
 * @copyright  2025, Miguël Dhyne
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Execute local_lockgrades upgrade from given old version.
 *
 * @param int $oldversion The version we are upgrading from.
 * @return bool True on success.
 */
function xmldb_local_lockgrades_upgrade($oldversion) {
    global $DB;

    if ($oldversion < 2025070800) {
        // Création de la table log.
        $dbman = $DB->get_manager();

        // Define table local_lockgrades_log to be created.
        $table = new xmldb_table('local_lockgrades_log');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $table->add_field('idnumber', XMLDB_TYPE_CHAR, '255', XMLDB_NOTNULL, null, null, null);
        $table->add_field('pattern', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('action', XMLDB_TYPE_CHAR, '10', XMLDB_NOTNULL, null, null, null);
        $table->add_field('scheduledfor', XMLDB_TYPE_INTEGER, '10', XMLDB_NOTNULL, null, null, null);
        $table->add_field('executed', XMLDB_TYPE_INTEGER, '1', XMLDB_NOTNULL, null, null, '0');
        $table->add_field('executiondate', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('impacted', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('log', XMLDB_TYPE_TEXT, null, null, null, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_index('idx_idnumber', XMLDB_INDEX_NOTUNIQUE, ['idnumber']);
        $table->add_index('idx_action', XMLDB_INDEX_NOTUNIQUE, ['action']);
        $table->add_index('idx_scheduledfor', XMLDB_INDEX_NOTUNIQUE, ['scheduledfor']);
        $table->add_index('idx_executed', XMLDB_INDEX_NOTUNIQUE, ['executed']);

        // Conditionally launch create table for local_lockgrades_log.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Mark upgrade as complete.
        upgrade_plugin_savepoint(true, 2025070800, 'local', 'lockgrades');
    }
    return true;
}
