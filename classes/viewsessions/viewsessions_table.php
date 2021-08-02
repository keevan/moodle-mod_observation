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
 * This file contains functions to view observation sessions in a table
 *
 * @package   mod_observation
 * @copyright  2021 Endurer Solutions Team
 * @author Matthew Hilton <mj.hilton@outlook.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_observation\viewsessions;

defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir . '/tablelib.php');

/**
 * Table to view observation sessions using tablelib
 *
 * @package   mod_observation
 * @copyright  2021 Endurer Solutions Team
 * @author Matthew Hilton <mj.hilton@outlook.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class viewsessions_table extends \table_sql implements \renderable {

    /**
     * Constructs the table and defines how the data from the SQL query is displayed
     * @param string $uniqueid ID that uniquely identifies this element on the HTML page
     * @param \moodle_url $callbackurl URL used for callback for action buttons in the table
     * @param int $perpage number of entries per page for the table
     */
    public function __construct(string $uniqueid, \moodle_url $callbackurl, int $perpage = 50) {
        parent::__construct($uniqueid);

        $this->define_columns([
            'id',
            'observer_username',
            'observee_username',
            'state',
            'start_time',
            'finish_time',
            'action'
        ]);

        $this->define_headers([
            get_string('id', 'observation'),
            get_string('observer', 'observation'),
            get_string('observee', 'observation'),
            get_string('state', 'observation'),
            get_string('starttime', 'observation'),
            get_string('finishtime', 'observation'),
            get_string('actions', 'observation'),
        ]);

        $this->pagesize = $perpage;

        $systemcontext = \context_system::instance();
        $this->context = $systemcontext;
        $this->collapsible(false);
        $this->pageable(true);
        $this->is_downloadable(false);
        $this->define_baseurl($callbackurl);
        $this->no_sorting('action');
    }

     /**
     * Data converter for the state column
     * @param mixed $row current row
     */
    public function col_state($row) {
        return get_string($row->state, 'observation');
    }

    public function col_finish_time($row) {
        return $this->format_as_date($row->finish_time);
    }

    public function col_start_time($row) {
        return $this->format_as_date($row->start_time);
    }

    private function format_as_date($epochs) {
        if(!is_null($epochs)){
            return date('r', $epochs);
        } else {
            return null;
        }
    }

    private function action_button(\moodle_url $url, string $text) {
        return \html_writer::link(
            $url,
            $text,
            ['class' => 'btn btn-secondary']
        );
    }

    /**
     * Data converter for the action column
     * @param mixed $row current row
     */
    public function col_action($row) {
        // Add action buttons.
        $htmlout = '';

        if($row->state === 'complete' || $row->state === 'inprogress'){
            $htmlout .= $this->action_button(
                new \moodle_url('session.php', ['sessionid' => $row->id]), 
                get_string('resume', 'observation'));

            $htmlout .= $this->action_button(
                new \moodle_url('sessionsummary.php', ['sessionid' => $row->id, 'mode' => 'viewing']),
                get_string('viewsummary', 'observation'));
        } 

        return $htmlout;
    }
}
