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
 * Form to begin an observation
 *
 * @package   mod_observation
 * @copyright  2021 Endurer Solutions Team
 * @author Jared Hungerford <hungerford31@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_observation;

defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir.'/formslib.php');

/**
 * Creates a moodle_form to start an observation session.
 *
 * @package   mod_observation
 * @copyright  2021 Endurer Solutions Team
 * @author Jared Hungerford <hungerford31@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assignedfilter_form extends \moodleform {
    /**
     * Defines the session creation form
     */
    public function definition() {
        global $PAGE;

        $mform = $this->_form;
        $prefill = $this->_customdata;

        // Get list of users full names.
        $context = $PAGE->context;
        $finalusers = [];
        $users = get_enrolled_users($context);
        foreach ($users as $u) {
            $finalusers[$u->id] = fullname($u);
        }

        $options = array(
            'multiple' => false,
        );

        // Change the button text depending on if the filter is enabled.
        $buttontext = $prefill['observeefilter_enabled'] === true ? get_string('resetfilter', 'observation')
            : get_string('applyfilter', 'observation');

        $observeeselector = [
            $mform->createElement('select', 'observee', get_string('observee', 'observation'), $finalusers, $options),
            $mform->createElement('submit', 'submit_btn', $buttontext)
        ];

        // Interval selector block.
        $mform->addGroup($observeeselector, 'interval_select_group', get_string('filterobservee', 'observation'), null, false);
        $mform->disabledIf('observee', 'enable_interval');
        $mform->setType('observee', PARAM_INT);

        // Hidden form elements.
        $mform->addElement('hidden', 'id', $prefill['id']);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'observeefilter_enabled', $prefill['observeefilter_enabled']);
        $mform->setType('observeefilter_enabled', PARAM_BOOL);

        // Disable all filter elements except the cancel button if the filter is applied.
        $mform->disabledIf('observee', 'observeefilter_enabled', 'eq', true);

        // Set defaults.
        $this->set_data($prefill);
    }
}
