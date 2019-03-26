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
 * URL module main user interface
 *
 * @package    mod_webexsession
 * @copyright  2017 Sudhanshu Gupta {@link sudhanshug5@gmail.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once("$CFG->dirroot/mod/webexsession/lib.php");
require_once("$CFG->dirroot/mod/webexsession/locallib.php");
require_once($CFG->libdir . '/completionlib.php');

$id       = optional_param('id', 0, PARAM_INT);        // Course module ID
$u        = optional_param('u', 0, PARAM_INT);         // URL instance id
$redirect = optional_param('redirect', 0, PARAM_BOOL);

if ($u) {  // Two ways to specify the module
    $webexsession = $DB->get_record('webexsession', array('id'=>$u), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('webexsession', $webexsession->id, $webexsession->course, false, MUST_EXIST);

} else {
    $cm = get_coursemodule_from_id('webexsession', $id, 0, false, MUST_EXIST);
    $webexsession = $DB->get_record('webexsession', array('id'=>$cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/webexsession:view', $context);

// Completion and trigger events.
webexsession_view($webexsession, $course, $cm, $context);

$PAGE->set_url('/mod/webexsession/view.php', array('id' => $cm->id));

// Make sure URL exists before generating output - some older sites may contain empty webexsessions
// Do not use PARAM_URL here, it is too strict and does not support general URIs!
$extwebexsession = trim($webexsession->externalwebexsession);
if (empty($extwebexsession) or $extwebexsession === 'http://') {
    webexsession_print_header($webexsession, $cm, $course);
    webexsession_print_heading($webexsession, $cm, $course);
    webexsession_print_intro($webexsession, $cm, $course);
    notice(get_string('invalidstoredwebexsession', 'webexsession'), new moodle_webexsession('/course/view.php', array('id'=>$cm->course)));
    die;
}
unset($extwebexsession);

$displaytype = webexsession_get_final_display_type($webexsession);
if ($displaytype == RESOURCELIB_DISPLAY_OPEN) {
    // For 'open' links, we always redirect to the content - except if the user
    // just chose 'save and display' from the form then that would be confusing
    if (strpos(get_local_referer(false), 'modedit.php') === false) {
        $redirect = true;
    }
}

if ($redirect) {
    // coming from course page or webexsession index page,
    // the redirection is needed for completion tracking and logging
    $fullwebexsession = str_replace('&amp;', '&', webexsession_get_full_webexsession($webexsession, $cm, $course));

    if (!course_get_format($course)->has_view_page()) {
        // If course format does not have a view page, add redirection delay with a link to the edit page.
        // Otherwise teacher is redirected to the external URL without any possibility to edit activity or course settings.
        $editwebexsession = null;
        if (has_capability('moodle/course:manageactivities', $context)) {
            $editwebexsession = new moodle_webexsession('/course/modedit.php', array('update' => $cm->id));
            $edittext = get_string('editthisactivity');
        } else if (has_capability('moodle/course:update', $context->get_course_context())) {
            $editwebexsession = new moodle_webexsession('/course/edit.php', array('id' => $course->id));
            $edittext = get_string('editcoursesettings');
        }
        if ($editwebexsession) {
            redirect($fullwebexsession, html_writer::link($editwebexsession, $edittext)."<br/>".
                    get_string('pageshouldredirect'), 10);
        }
    }
    redirect($fullwebexsession);
}

switch ($displaytype) {
    case RESOURCELIB_DISPLAY_EMBED:
        webexsession_display_embed($webexsession, $cm, $course);
        break;
    case RESOURCELIB_DISPLAY_FRAME:
        webexsession_display_frame($webexsession, $cm, $course);
        break;
    default:
        webexsession_print_workaround($webexsession, $cm, $course);
        break;
}
