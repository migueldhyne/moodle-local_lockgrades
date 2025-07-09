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
 * Form of local_lockgrades plugin
 *
 * @package   local_lockgrades
 * @copyright 2025, Miguël Dhyne <miguel.dhyne@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Form for local_gradelocks plugin
 *
 * @package   local_lockgrades
 * @copyright 2025, Miguël Dhyne <miguel.dhyne@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_lockgrades_form extends moodleform {
    /**
     * Defines the form elements for the local_lockgrades plugin.
     *
     * Adds required and optional fields, a date/time selector with optional activation,
     * and a group of action buttons (immediate and scheduled locking/unlocking, and preview).
     * Also injects JavaScript to enable or disable buttons dynamically depending
     * on whether scheduling is enabled.
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;

        // Required fields : category idnumber.
        $mform->addElement('text', 'idnumber', get_string('idnumber', 'local_lockgrades'));
        $mform->setType('idnumber', PARAM_TEXT);
        $mform->addRule('idnumber', null, 'required', null, 'client');

        // Filter pattern.
        $mform->addElement('text', 'pattern', get_string('pattern', 'local_lockgrades'));
        $mform->setType('pattern', PARAM_TEXT);
        $mform->addHelpButton('pattern', 'pattern', 'local_lockgrades');

        // Select date/time.
        $mform->addElement('date_time_selector', 'scheduledtime',
            get_string('scheduledtime', 'local_lockgrades'), ['optional' => true]);
        $mform->addHelpButton('scheduledtime', 'scheduledtime', 'local_lockgrades');

        // Buttons.
        $buttons = [];
        $buttons[] = $mform->createElement('submit', 'lock', get_string('lockgrades', 'local_lockgrades'));
        $buttons[] = $mform->createElement('submit', 'unlock', get_string('unlockgrades', 'local_lockgrades'));
        $buttons[] = $mform->createElement('submit', 'schedulelock', get_string('schedulelock', 'local_lockgrades'));
        $buttons[] = $mform->createElement('submit', 'scheduleunlock', get_string('scheduleunlock', 'local_lockgrades'));
        $buttons[] = $mform->createElement('submit', 'preview', get_string('previewimpact', 'local_lockgrades'));
        $mform->addGroup($buttons, 'actionbuttons', '', [' '], false);

        // JS for dynamic buttons.
        $script = <<<JS
<script>
document.addEventListener('DOMContentLoaded', function() {
    function updateButtons() {
        var enabledBox = document.querySelector('input[name="scheduledtime[enabled]"]');
        var isEnabled = enabledBox ? enabledBox.checked : false;

        // Boutons immédiats (actifs si pas planifié)
        var lockBtn = document.querySelector('input[name="lock"]');
        var unlockBtn = document.querySelector('input[name="unlock"]');
        if (lockBtn) lockBtn.disabled = isEnabled;
        if (unlockBtn) unlockBtn.disabled = isEnabled;

        // Boutons planification (actifs si planifié)
        var schedLockBtn = document.querySelector('input[name="schedulelock"]');
        var schedUnlockBtn = document.querySelector('input[name="scheduleunlock"]');
        if (schedLockBtn) schedLockBtn.disabled = !isEnabled;
        if (schedUnlockBtn) schedUnlockBtn.disabled = !isEnabled;

        // Preview toujours actif
        var previewBtn = document.querySelector('input[name="preview"]');
        if (previewBtn) previewBtn.disabled = false;
    }

    document.querySelectorAll('select[name^="scheduledtime["],input[name="scheduledtime[enabled]"]').forEach(function(el){
        el.addEventListener('change', updateButtons);
    });

    updateButtons();
});
</script>
JS;
        $mform->addElement('html', $script);
    }
}
