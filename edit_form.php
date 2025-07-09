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
 * Editind form for database records of scheduled (un)lock
 *
 * Contains all French language strings used by the Wiki Creator plugin,
 * including those for settings, interface labels, and messages.
 *
 * @package   local_lockgrades
 * @copyright 2025, Miguël Dhyne <miguel.dhyne@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Formulaire d’édition/duplication pour local_lockgrades_schedule.
 */
class local_lockgrades_edit_form extends moodleform {
    /**
     * Defines the form fields for scheduling a grade lock/unlock task.
     *
     * Adds fields for idnumber, course pattern, scheduled time, and lock type.
     * Adjusts behavior based on the form mode ('edit' or 'duplicate').
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;
        $mode = $this->_customdata['mode'] ?? 'edit';

        // Hidden id only if edit.
        if ($mode === 'edit') {
            $mform->addElement('hidden', 'id');
            $mform->setType('id', PARAM_INT);
        }

        $mform->addElement('text', 'idnumber', get_string('idnumber', 'local_lockgrades'));
        $mform->setType('idnumber', PARAM_TEXT);
        $mform->addRule('idnumber', null, 'required', null, 'client');

        $mform->addElement('text', 'pattern', get_string('pattern', 'local_lockgrades'));
        $mform->setType('pattern', PARAM_TEXT);
        $mform->addHelpButton('pattern', 'pattern', 'local_lockgrades');

        $mform->addElement('date_time_selector', 'scheduledtime',
        get_string('scheduledtime', 'local_lockgrades'), ['optional' => false]);
        $mform->addHelpButton('scheduledtime', 'scheduledtime', 'local_lockgrades');

        $choices = [
            'lock' => get_string('lockgrades', 'local_lockgrades'),
            'unlock' => get_string('unlockgrades', 'local_lockgrades'),
        ];
        $mform->addElement('select', 'locktype', get_string('scheduledtype', 'local_lockgrades'), $choices);
        $mform->addRule('locktype', null, 'required', null, 'client');

        $buttonlabel = ($mode === 'duplicate')
            ? get_string('duplicate', 'local_lockgrades')
            : get_string('edit', 'local_lockgrades');
        $this->add_action_buttons(true, $buttonlabel);
    }
}
