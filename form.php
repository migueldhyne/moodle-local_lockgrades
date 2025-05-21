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
 * Form for local_lockgrades
 *
 * @package   local_lockgrades
 * @copyright 2025, Miguël Dhyne <miguel.dhyne@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Load core config (so global state is set up).
require_once(__DIR__ . '/../../config.php');

// Security: require the user to be logged in, and check capability.
require_login();
$context = context_system::instance();
require_capability('local/lockgrades:manage', $context);

// Then load the form library.
require_once($CFG->libdir . '/formslib.php');

/**
 * local_lockgrades_form class.
 *
 * Presents a form with:
 *  - A required text field for the user’s idnumber
 *  - A “Lock grades” submit button
 *  - An “Unlock grades” submit button
 */
class local_lockgrades_form extends moodleform {
    /**
     * Defines the form elements.
     *
     * @return void
     */
    public function definition() {
         $mform = $this->_form;

         // Input field for idnumber.
         $mform->addElement('text', 'idnumber', get_string('idnumber', 'local_lockgrades'));
         $mform->setType('idnumber', PARAM_TEXT);
         $mform->addRule('idnumber', null, 'required', null, 'client');

         // Action buttons: one to lock and one to unlock.
         $mform->addElement('submit', 'lock', get_string('lockgrades', 'local_lockgrades'));
         $mform->addElement('submit', 'unlock', get_string('unlockgrades', 'local_lockgrades'));
    }
}
