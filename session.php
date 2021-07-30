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
 * This file contains the page view if the user has the capability 'perform_observations'
 *
 * @package   mod_observation
 * @copyright  2021 Endurer Solutions Team
 * @author Matthew Hilton <mj.hilton@outlook.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../../config.php');

// Get data from the session ID.
$sessionid = required_param('sessionid', PARAM_INT);
$sessiondata = \mod_observation\session_manager::get_session_info($sessionid);

$pointid = optional_param('pointid', null, PARAM_INT);

$obid = $sessiondata['obid'];
list($observation, $course, $cm) = \mod_observation\observation_manager::get_observation_course_cm_from_obid($obid);

// Check permissions.
require_login($course, true, $cm);
require_capability('mod/observation:performobservation', $PAGE->context);

// Get observation points and current responses.
$observationpoints = (array)\mod_observation\observation_manager::get_points_and_responses($obid, $sessionid);

// Redirect to the first observation point if none was provided.
if(is_null($pointid)){
    $firstpoint = empty($observationpoints) ? null : reset($observationpoints);
    if(is_null($firstpoint)){
        // No observation points - redirect back with error message
        redirect(new moodle_url('sessionview.php', ['id' => $obid]), get_string('noobservationpoints', 'observation'), null, \core\output\notification::NOTIFY_ERROR);
    } else {
        redirect(new moodle_url('session.php', ['sessionid' => $sessionid, 'pointid' => $firstpoint->point_id]));
    }
    return;
}

// Load point selector form.
$pointids = array_column($observationpoints, 'point_id');

$selectoroptions = [];
foreach($pointids as $id){
    $selectoroptions[$id] = 'Point '.$id;
}

$selectprefill = [
    'pointid' => $pointid,
    'pointid_options' => $selectoroptions,
    'sessionid' => $sessionid,
];
$selectorform = new \mod_observation\pointselector_form(null, $selectprefill);

if($fromform = $selectorform->get_data()) {
    // TODO maybe check if form has been saved ???
    redirect(new moodle_url('session.php', ['sessionid' => $sessionid, 'pointid' => $fromform->pointid]));
    return;
}

// Load point marking form.
$selectedpointdata = $observationpoints[$pointid];

// TODO check if the selected point exists.

$formprefill = (array)$selectedpointdata;
$formprefill['sessionid'] = $sessionid;
$markingform = new \mod_observation\pointmarking_form(null, $formprefill);

// If point marking form was submitted.
if ($fromform = $markingform->get_data()) {
    \mod_observation\observation_manager::submit_point_response($sessionid, $pointid, $fromform);
    redirect(new moodle_url('session.php', ['sessionid' => $sessionid, 'pointid' => $pointid]), get_string('responsesaved', 'observation'), null, \core\output\notification::NOTIFY_SUCCESS);
    return;
}   

// Render page.
$PAGE->set_url(new moodle_url('/mod/observation/session.php', array('sessionid' => $sessionid)));
$PAGE->set_title($course->shortname.': '.$observation->name);
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('markingobservation', 'observation'), 2);

// Observation point table/list block
echo $OUTPUT->container_start();

$selectorform->display();
$markingform->display();

echo print_object($formprefill);

echo print_object($observationpoints);

echo $OUTPUT->container_end();

echo $OUTPUT->footer();
