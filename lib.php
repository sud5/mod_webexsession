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
 * Mandatory public API of webexsession module
 *
 * @package    mod_webexsession
 * @copyright  2017 Sudhanshu Gupta {@link sudhanshug5@gmail.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * List of features supported in URL module
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function webexsession_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}

/**
 * Returns all other caps used in module
 * @return array
 */
function webexsession_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function webexsession_reset_userdata($data) {
    return array();
}

/**
 * List the actions that correspond to a view of this module.
 * This is used by the participation report.
 *
 * Note: This is not used by new logging system. Event with
 *       crud = 'r' and edulevel = LEVEL_PARTICIPATING will
 *       be considered as view action.
 *
 * @return array
 */
function webexsession_get_view_actions() {
    return array('view', 'view all');
}

/**
 * List the actions that correspond to a post of this module.
 * This is used by the participation report.
 *
 * Note: This is not used by new logging system. Event with
 *       crud = ('c' || 'u' || 'd') and edulevel = LEVEL_PARTICIPATING
 *       will be considered as post action.
 *
 * @return array
 */
function webexsession_get_post_actions() {
    return array('update', 'add');
}

/**
 * Add webexsession instance.
 * @param object $data
 * @param object $mform
 * @return int new webexsession instance id
 */
function webexsession_add_instance($data, $mform) {
    global $CFG, $DB;

    require_once($CFG->dirroot.'/mod/webexsession/locallib.php');

    $parameters = array();
    for ($i=0; $i < 100; $i++) {
        $parameter = "parameter_$i";
        $variable  = "variable_$i";
        if (empty($data->$parameter) or empty($data->$variable)) {
            continue;
        }
        $parameters[$data->$parameter] = $data->$variable;
    }
    $data->parameters = serialize($parameters);

    $displayoptions = array();
    if ($data->display == RESOURCELIB_DISPLAY_POPUP) {
        $displayoptions['popupwidth']  = $data->popupwidth;
        $displayoptions['popupheight'] = $data->popupheight;
    }
    if (in_array($data->display, array(RESOURCELIB_DISPLAY_AUTO, RESOURCELIB_DISPLAY_EMBED, RESOURCELIB_DISPLAY_FRAME))) {
        $displayoptions['printintro']   = (int)!empty($data->printintro);
    }
    $data->displayoptions = serialize($displayoptions);

    $data->externalwebexsession = webexsession_fix_submitted_webexsession($data->externalwebexsession);

    $data->timemodified = time();

    $data->id = $DB->insert_record('webexsession', $data);

    return $data->id;
}

/**
 * Update webexsession instance.
 * @param object $data
 * @param object $mform
 * @return bool true
 */
function webexsession_update_instance($data, $mform) {
    global $CFG, $DB;

    require_once($CFG->dirroot.'/mod/webexsession/locallib.php');

    $parameters = array();
    for ($i=0; $i < 100; $i++) {
        $parameter = "parameter_$i";
        $variable  = "variable_$i";
        if (empty($data->$parameter) or empty($data->$variable)) {
            continue;
        }
        $parameters[$data->$parameter] = $data->$variable;
    }
    $data->parameters = serialize($parameters);

    $displayoptions = array();
    if ($data->display == RESOURCELIB_DISPLAY_POPUP) {
        $displayoptions['popupwidth']  = $data->popupwidth;
        $displayoptions['popupheight'] = $data->popupheight;
    }
    if (in_array($data->display, array(RESOURCELIB_DISPLAY_AUTO, RESOURCELIB_DISPLAY_EMBED, RESOURCELIB_DISPLAY_FRAME))) {
        $displayoptions['printintro']   = (int)!empty($data->printintro);
    }
    $data->displayoptions = serialize($displayoptions);

    $data->externalwebexsession = webexsession_fix_submitted_webexsession($data->externalwebexsession);

    $data->timemodified = time();
    $data->id           = $data->instance;

    $DB->update_record('webexsession', $data);

    return true;
}

/**
 * Delete webexsession instance.
 * @param int $id
 * @return bool true
 */
function webexsession_delete_instance($id) {
//   redirect('../../portal/manage-ilt-classes');
    global $DB, $COURSE;
    $current_course_category = $DB->get_field('course', 'category', array('id' => $COURSE->id));
    $BL_template_category = $DB->get_field('course_categories', 'id', array('name' => 'BL Course Template'));
    if ($current_course_category != $BL_template_category) {
        return false;
    }

    if (!$webexsession = $DB->get_record('webexsession', array('id'=>$id))) {
        return false;
    }

    // note: all context files are deleted automatically

    $DB->delete_records('webexsession', array('id'=>$webexsession->id));

    return true;
}

/**
 * Given a course_module object, this function returns any
 * "extra" information that may be needed when printing
 * this activity in a course listing.
 *
 * See {@link get_array_of_activities()} in course/lib.php
 *
 * @param object $coursemodule
 * @return cached_cm_info info
 */
function webexsession_get_coursemodule_info($coursemodule) {
    global $CFG, $DB;
    require_once("$CFG->dirroot/mod/webexsession/locallib.php");

    if (!$webexsession = $DB->get_record('webexsession', array('id'=>$coursemodule->instance),
            'id, name, display, displayoptions, externalwebexsession, parameters, intro, introformat')) {
        return NULL;
    }

    $info = new cached_cm_info();
    $info->name = $webexsession->name;

    //note: there should be a way to differentiate links from normal resources
    $info->icon = webexsession_guess_icon($webexsession->externalwebexsession, 24);

    $display = webexsession_get_final_display_type($webexsession);

    if ($display == RESOURCELIB_DISPLAY_POPUP) {
        $fullwebexsession = "$CFG->wwwroot/mod/webexsession/view.php?id=$coursemodule->id&amp;redirect=1";
        $options = empty($webexsession->displayoptions) ? array() : unserialize($webexsession->displayoptions);
        $width  = empty($options['popupwidth'])  ? 620 : $options['popupwidth'];
        $height = empty($options['popupheight']) ? 450 : $options['popupheight'];
        $wh = "width=$width,height=$height,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes";
        $info->onclick = "window.open('$fullwebexsession', '', '$wh'); return false;";

    } else if ($display == RESOURCELIB_DISPLAY_NEW) {
        $fullwebexsession = "$CFG->wwwroot/mod/webexsession/view.php?id=$coursemodule->id&amp;redirect=1";
        $info->onclick = "window.open('$fullwebexsession'); return false;";

    }

    if ($coursemodule->showdescription) {
        // Convert intro to html. Do not filter cached version, filters run at display time.
        $info->content = format_module_intro('webexsession', $webexsession, $coursemodule->id, false);
    }

    return $info;
}

/**
 * Return a list of page types
 * @param string $pagetype current page type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 */
function webexsession_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $module_pagetype = array('mod-webexsession-*'=>get_string('page-mod-webexsession-x', 'webexsession'));
    return $module_pagetype;
}

/**
 * Export URL resource contents
 *
 * @return array of file content
 */
function webexsession_export_contents($cm, $basewebexsession) {
    global $CFG, $DB;
    require_once("$CFG->dirroot/mod/webexsession/locallib.php");
    $contents = array();
    $context = context_module::instance($cm->id);

    $course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
    $webexsessionrecord = $DB->get_record('webexsession', array('id'=>$cm->instance), '*', MUST_EXIST);

    $fullwebexsession = str_replace('&amp;', '&', webexsession_get_full_webexsession($webexsessionrecord, $cm, $course));
    $iswebexsession = clean_param($fullwebexsession, PARAM_URL);
    if (empty($iswebexsession)) {
        return null;
    }

    $webexsession = array();
    $webexsession['type'] = 'webexsession';
    $webexsession['filename']     = clean_param(format_string($webexsessionrecord->name), PARAM_FILE);
    $webexsession['filepath']     = null;
    $webexsession['filesize']     = 0;
    $webexsession['filewebexsession']      = $fullwebexsession;
    $webexsession['timecreated']  = null;
    $webexsession['timemodified'] = $webexsessionrecord->timemodified;
    $webexsession['sortorder']    = null;
    $webexsession['userid']       = null;
    $webexsession['author']       = null;
    $webexsession['license']      = null;
    $contents[] = $webexsession;

    return $contents;
}

/**
 * Register the ability to handle drag and drop file uploads
 * @return array containing details of the files / types the mod can handle
 */
function webexsession_dndupload_register() {
    return array('types' => array(
                     array('identifier' => 'url', 'message' => get_string('createwebexsession', 'webexsession'))
                 ));
}

/**
 * Handle a file that has been uploaded
 * @param object $uploadinfo details of the file / content that has been uploaded
 * @return int instance id of the newly created mod
 */
function webexsession_dndupload_handle($uploadinfo) {
    // Gather all the required data.
    $data = new stdClass();
    $data->course = $uploadinfo->course->id;
    $data->name = $uploadinfo->displayname;
    $data->intro = '<p>'.$uploadinfo->displayname.'</p>';
    $data->introformat = FORMAT_HTML;
    $data->externalwebexsession = clean_param($uploadinfo->content, PARAM_URL);
    $data->timemodified = time();

    // Set the display options to the site defaults.
    $config = get_config('webexsession');
    $data->display = $config->display;
    $data->popupwidth = $config->popupwidth;
    $data->popupheight = $config->popupheight;
    $data->printintro = $config->printintro;

    return webexsession_add_instance($data, null);
}

/**
 * Mark the activity completed (if required) and trigger the course_module_viewed event.
 *
 * @param  stdClass $webexsession        webexsession object
 * @param  stdClass $course     course object
 * @param  stdClass $cm         course module object
 * @param  stdClass $context    context object
 * @since Moodle 3.0
 */
function webexsession_view($webexsession, $course, $cm, $context) {

    // Trigger course_module_viewed event.
    $params = array(
        'context' => $context,
        'objectid' => $webexsession->id
    );

    $event = \mod_webexsession\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('webexsession', $webexsession);
    $event->trigger();

    // Completion.
    $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}
