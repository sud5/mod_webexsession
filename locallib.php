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
 * Private webexsession module utility functions
 *
 * @package    mod_webexsession
 * @copyright  2017 Sudhanshu Gupta {@link sudhanshug5@gmail.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/filelib.php");
require_once("$CFG->libdir/resourcelib.php");
require_once("$CFG->dirroot/mod/webexsession/lib.php");

/**
 * This methods does weak webexsession validation, we are looking for major problems only,
 * no strict RFE validation.
 *
 * @param $webexsession
 * @return bool true is seems valid, false if definitely not valid Webex Session
 */
function webexsession_appears_valid_webexsession($webexsession) {
    if (preg_match('/^(\/|https?:|ftp:)/i', $webexsession)) {
        // note: this is not exact validation, we look for severely malformed Webex Sessions only
        return (bool)preg_match('/^[a-z]+:\/\/([^:@\s]+:[^@\s]+@)?[a-z0-9_\.\-]+(:[0-9]+)?(\/[^#]*)?(#.*)?$/i', $webexsession);
    } else {
        return (bool)preg_match('/^[a-z]+:\/\/...*$/i', $webexsession);
    }
}

/**
 * Fix common Webex Session problems that we want teachers to see fixed
 * the next time they edit the resource.
 *
 * This function does not include any XSS protection.
 *
 * @param string $webexsession
 * @return string
 */
function webexsession_fix_submitted_webexsession($webexsession) {
    // note: empty webexsessions are prevented in form validation
    $webexsession = trim($webexsession);

    // remove encoded entities - we want the raw URI here
    $webexsession = html_entity_decode($webexsession, ENT_QUOTES, 'UTF-8');

    if (!preg_match('|^[a-z]+:|i', $webexsession) and !preg_match('|^/|', $webexsession)) {
        // invalid URI, try to fix it by making it normal Webex Session,
        // please note relative webexsessions are not allowed, /xx/yy links are ok
        $webexsession = 'http://'.$webexsession;
    }

    return $webexsession;
}

/**
 * Return full webexsession with all extra parameters
 *
 * This function does not include any XSS protection.
 *
 * @param string $webexsession
 * @param object $cm
 * @param object $course
 * @param object $config
 * @return string webexsession with & encoded as &amp;
 */
function webexsession_get_full_webexsession($webexsession, $cm, $course, $config=null) {

    $parameters = empty($webexsession->parameters) ? array() : unserialize($webexsession->parameters);

    // make sure there are no encoded entities, it is ok to do this twice
    $fullwebexsession = html_entity_decode($webexsession->externalwebexsession, ENT_QUOTES, 'UTF-8');

    if (preg_match('/^(\/|https?:|ftp:)/i', $fullwebexsession) or preg_match('|^/|', $fullwebexsession)) {
        // encode extra chars in Webex Sessions - this does not make it always valid, but it helps with some UTF-8 problems
        $allowed = "a-zA-Z0-9".preg_quote(';/?:@=&$_.+!*(),-#%', '/');
        $fullwebexsession = preg_replace_callback("/[^$allowed]/", 'webexsession_filter_callback', $fullwebexsession);
    } else {
        // encode special chars only
        $fullwebexsession = str_replace('"', '%22', $fullwebexsession);
        $fullwebexsession = str_replace('\'', '%27', $fullwebexsession);
        $fullwebexsession = str_replace(' ', '%20', $fullwebexsession);
        $fullwebexsession = str_replace('<', '%3C', $fullwebexsession);
        $fullwebexsession = str_replace('>', '%3E', $fullwebexsession);
    }

    // add variable webexsession parameters
    if (!empty($parameters)) {
        if (!$config) {
            $config = get_config('webexsession');
        }
        $paramvalues = webexsession_get_variable_values($webexsession, $cm, $course, $config);

        foreach ($parameters as $parse=>$parameter) {
            if (isset($paramvalues[$parameter])) {
                $parameters[$parse] = rawurlencode($parse).'='.rawurlencode($paramvalues[$parameter]);
            } else {
                unset($parameters[$parse]);
            }
        }

        if (!empty($parameters)) {
            if (stripos($fullwebexsession, 'teamspeak://') === 0) {
                $fullwebexsession = $fullwebexsession.'?'.implode('?', $parameters);
            } else {
                $join = (strpos($fullwebexsession, '?') === false) ? '?' : '&';
                $fullwebexsession = $fullwebexsession.$join.implode('&', $parameters);
            }
        }
    }

    // encode all & to &amp; entity
    $fullwebexsession = str_replace('&', '&amp;', $fullwebexsession);

    return $fullwebexsession;
}

/**
 * Unicode encoding helper callback
 * @internal
 * @param array $matches
 * @return string
 */
function webexsession_filter_callback($matches) {
    return rawurlencode($matches[0]);
}

/**
 * Print webexsession header.
 * @param object $webexsession
 * @param object $cm
 * @param object $course
 * @return void
 */
function webexsession_print_header($webexsession, $cm, $course) {
    global $PAGE, $OUTPUT;

    $PAGE->set_title($course->shortname.': '.$webexsession->name);
    $PAGE->set_heading($course->fullname);
    $PAGE->set_activity_record($webexsession);
    echo $OUTPUT->header();
}

/**
 * Print webexsession heading.
 * @param object $webexsession
 * @param object $cm
 * @param object $course
 * @param bool $notused This variable is no longer used.
 * @return void
 */
function webexsession_print_heading($webexsession, $cm, $course, $notused = false) {
    global $OUTPUT;
    echo $OUTPUT->heading(format_string($webexsession->name), 2);
}

/**
 * Print webexsession introduction.
 * @param object $webexsession
 * @param object $cm
 * @param object $course
 * @param bool $ignoresettings print even if not specified in modedit
 * @return void
 */
function webexsession_print_intro($webexsession, $cm, $course, $ignoresettings=false) {
    global $OUTPUT;

    $options = empty($webexsession->displayoptions) ? array() : unserialize($webexsession->displayoptions);
    if ($ignoresettings or !empty($options['printintro'])) {
        if (trim(strip_tags($webexsession->intro))) {
            echo $OUTPUT->box_start('mod_introbox', 'webexsessionintro');
            echo format_module_intro('webexsession', $webexsession, $cm->id);
            echo $OUTPUT->box_end();
        }
    }
}

/**
 * Display webexsession frames.
 * @param object $webexsession
 * @param object $cm
 * @param object $course
 * @return does not return
 */
function webexsession_display_frame($webexsession, $cm, $course) {
    global $PAGE, $OUTPUT, $CFG;

    $frame = optional_param('frameset', 'main', PARAM_ALPHA);

    if ($frame === 'top') {
        $PAGE->set_pagelayout('frametop');
        webexsession_print_header($webexsession, $cm, $course);
        webexsession_print_heading($webexsession, $cm, $course);
        webexsession_print_intro($webexsession, $cm, $course);
        echo $OUTPUT->footer();
        die;

    } else {
        $config = get_config('webexsession');
        $context = context_module::instance($cm->id);
        $extewebexsession = webexsession_get_full_webexsession($webexsession, $cm, $course, $config);
        $navwebexsession = "$CFG->wwwroot/mod/webexsession/view.php?id=$cm->id&amp;frameset=top";
        $coursecontext = context_course::instance($course->id);
        $courseshortname = format_string($course->shortname, true, array('context' => $coursecontext));
        $title = strip_tags($courseshortname.': '.format_string($webexsession->name));
        $framesize = $config->framesize;
        $modulename = s(get_string('modulename','webexsession'));
        $contentframetitle = s(format_string($webexsession->name));
        $dir = get_string('thisdirection', 'langconfig');

        $extframe = <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html dir="$dir">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>$title</title>
  </head>
  <frameset rows="$framesize,*">
    <frame src="$navwebexsession" title="$modulename"/>
    <frame src="$extewebexsession" title="$contentframetitle"/>
  </frameset>
</html>
EOF;

        @header('Content-Type: text/html; charset=utf-8');
        echo $extframe;
        die;
    }
}

/**
 * Print webexsession info and link.
 * @param object $webexsession
 * @param object $cm
 * @param object $course
 * @return does not return
 */
function webexsession_print_workaround($webexsession, $cm, $course) {
    global $OUTPUT;

    webexsession_print_header($webexsession, $cm, $course);
    webexsession_print_heading($webexsession, $cm, $course, true);
    webexsession_print_intro($webexsession, $cm, $course, true);

    $fullwebexsession = webexsession_get_full_webexsession($webexsession, $cm, $course);

    $display = webexsession_get_final_display_type($webexsession);
    if ($display == RESOURCELIB_DISPLAY_POPUP) {
        $jsfullwebexsession = addslashes_js($fullwebexsession);
        $options = empty($webexsession->displayoptions) ? array() : unserialize($webexsession->displayoptions);
        $width  = empty($options['popupwidth'])  ? 620 : $options['popupwidth'];
        $height = empty($options['popupheight']) ? 450 : $options['popupheight'];
        $wh = "width=$width,height=$height,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes";
        $extra = "onclick=\"window.open('$jsfullwebexsession', '', '$wh'); return false;\"";

    } else if ($display == RESOURCELIB_DISPLAY_NEW) {
        $extra = "onclick=\"this.target='_blank';\"";

    } else {
        $extra = '';
    }

    echo '<div class="webexsessionworkaround">';
    print_string('clicktoopen', 'webexsession', "<a href=\"$fullwebexsession\" $extra>$fullwebexsession</a>");
    echo '</div>';

    echo $OUTPUT->footer();
    die;
}

/**
 * Display embedded webexsession file.
 * @param object $webexsession
 * @param object $cm
 * @param object $course
 * @return does not return
 */
function webexsession_display_embed($webexsession, $cm, $course) {
    global $CFG, $PAGE, $OUTPUT;

    $mimetype = resourcelib_guess_webexsession_mimetype($webexsession->externalwebexsession);
    $fullwebexsession  = webexsession_get_full_webexsession($webexsession, $cm, $course);
    $title    = $webexsession->name;

    $link = html_writer::tag('a', $fullwebexsession, array('href'=>str_replace('&amp;', '&', $fullwebexsession)));
    $clicktoopen = get_string('clicktoopen', 'webexsession', $link);
    $moodlewebexsession = new moodle_webexsession($fullwebexsession);

    $extension = resourcelib_get_extension($webexsession->externalwebexsession);

    $mediarenderer = $PAGE->get_renderer('core', 'media');
    $embedoptions = array(
        core_media::OPTION_TRUSTED => true,
        core_media::OPTION_BLOCK => true
    );

    if (in_array($mimetype, array('image/gif','image/jpeg','image/png'))) {  // It's an image
        $code = resourcelib_embed_image($fullwebexsession, $title);

    } else if ($mediarenderer->can_embed_webexsession($moodlewebexsession, $embedoptions)) {
        // Media (audio/video) file.
        $code = $mediarenderer->embed_webexsession($moodlewebexsession, $title, 0, 0, $embedoptions);

    } else {
        // anything else - just try object tag enlarged as much as possible
        $code = resourcelib_embed_general($fullwebexsession, $title, $clicktoopen, $mimetype);
    }

    webexsession_print_header($webexsession, $cm, $course);
    webexsession_print_heading($webexsession, $cm, $course);

    echo $code;

    webexsession_print_intro($webexsession, $cm, $course);

    echo $OUTPUT->footer();
    die;
}

/**
 * Decide the best display format.
 * @param object $webexsession
 * @return int display type constant
 */
function webexsession_get_final_display_type($webexsession) {
    global $CFG;

    if ($webexsession->display != RESOURCELIB_DISPLAY_AUTO) {
        return $webexsession->display;
    }

    // detect links to local moodle pages
    if (strpos($webexsession->externalwebexsession, $CFG->wwwroot) === 0) {
        if (strpos($webexsession->externalwebexsession, 'file.php') === false and strpos($webexsession->externalwebexsession, '.php') !== false ) {
            // most probably our moodle page with navigation
            return RESOURCELIB_DISPLAY_OPEN;
        }
    }

    static $download = array('application/zip', 'application/x-tar', 'application/g-zip',     // binary formats
                             'application/pdf', 'text/html');  // these are known to cause trouble for external links, sorry
    static $embed    = array('image/gif', 'image/jpeg', 'image/png', 'image/svg+xml',         // images
                             'application/x-shockwave-flash', 'video/x-flv', 'video/x-ms-wm', // video formats
                             'video/quicktime', 'video/mpeg', 'video/mp4',
                             'audio/mp3', 'audio/x-realaudio-plugin', 'x-realaudio-plugin',   // audio formats,
                            );

    $mimetype = resourcelib_guess_webexsession_mimetype($webexsession->externalwebexsession);

    if (in_array($mimetype, $download)) {
        return RESOURCELIB_DISPLAY_DOWNLOAD;
    }
    if (in_array($mimetype, $embed)) {
        return RESOURCELIB_DISPLAY_EMBED;
    }

    // let the browser deal with it somehow
    return RESOURCELIB_DISPLAY_OPEN;
}

/**
 * Get the parameters that may be appended to Webex Session
 * @param object $config webexsession module config options
 * @return array array describing opt groups
 */
function webexsession_get_variable_options($config) {
    global $CFG;

    $options = array();
    $options[''] = array('' => get_string('chooseavariable', 'webexsession'));

    $options[get_string('course')] = array(
        'courseid'        => 'id',
        'coursefullname'  => get_string('fullnamecourse'),
        'courseshortname' => get_string('shortnamecourse'),
        'courseidnumber'  => get_string('idnumbercourse'),
        'coursesummary'   => get_string('summary'),
        'courseformat'    => get_string('format'),
    );

    $options[get_string('modulename', 'webexsession')] = array(
        'webexsessioninstance'     => 'id',
        'webexsessioncmid'         => 'cmid',
        'webexsessionname'         => get_string('name'),
        'webexsessionidnumber'     => get_string('idnumbermod'),
    );

    $options[get_string('miscellaneous')] = array(
        'sitename'        => get_string('fullsitename'),
        'serverwebexsession'       => get_string('serverwebexsession', 'webexsession'),
        'currenttime'     => get_string('time'),
        'lang'            => get_string('language'),
    );
    if (!empty($config->secretphrase)) {
        $options[get_string('miscellaneous')]['encryptedcode'] = get_string('encryptedcode');
    }

    $options[get_string('user')] = array(
        'userid'          => 'id',
        'userusername'    => get_string('username'),
        'useridnumber'    => get_string('idnumber'),
        'userfirstname'   => get_string('firstname'),
        'userlastname'    => get_string('lastname'),
        'userfullname'    => get_string('fullnameuser'),
        'useremail'       => get_string('email'),
        'usericq'         => get_string('icqnumber'),
        'userphone1'      => get_string('phone1'),
        'userphone2'      => get_string('phone2'),
        'userinstitution' => get_string('institution'),
        'userdepartment'  => get_string('department'),
        'useraddress'     => get_string('address'),
        'usercity'        => get_string('city'),
        'usertimezone'    => get_string('timezone'),
        'userwebexsession'         => get_string('webpage'),
    );

    if ($config->rolesinparams) {
        $roles = role_fix_names(get_all_roles());
        $roleoptions = array();
        foreach ($roles as $role) {
            $roleoptions['course'.$role->shortname] = get_string('yourwordforx', '', $role->localname);
        }
        $options[get_string('roles')] = $roleoptions;
    }

    return $options;
}

/**
 * Get the parameter values that may be appended to Webex Session
 * @param object $webexsession module instance
 * @param object $cm
 * @param object $course
 * @param object $config module config options
 * @return array of parameter values
 */
function webexsession_get_variable_values($webexsession, $cm, $course, $config) {
    global $USER, $CFG;

    $site = get_site();

    $coursecontext = context_course::instance($course->id);

    $values = array (
        'courseid'        => $course->id,
        'coursefullname'  => format_string($course->fullname),
        'courseshortname' => format_string($course->shortname, true, array('context' => $coursecontext)),
        'courseidnumber'  => $course->idnumber,
        'coursesummary'   => $course->summary,
        'courseformat'    => $course->format,
        'lang'            => current_language(),
        'sitename'        => format_string($site->fullname),
        'serverwebexsession'       => $CFG->wwwroot,
        'currenttime'     => time(),
        'webexsessioninstance'     => $webexsession->id,
        'webexsessioncmid'         => $cm->id,
        'webexsessionname'         => format_string($webexsession->name),
        'webexsessionidnumber'     => $cm->idnumber,
    );

    if (isloggedin()) {
        $values['userid']          = $USER->id;
        $values['userusername']    = $USER->username;
        $values['useridnumber']    = $USER->idnumber;
        $values['userfirstname']   = $USER->firstname;
        $values['userlastname']    = $USER->lastname;
        $values['userfullname']    = fullname($USER);
        $values['useremail']       = $USER->email;
        $values['usericq']         = $USER->icq;
        $values['userphone1']      = $USER->phone1;
        $values['userphone2']      = $USER->phone2;
        $values['userinstitution'] = $USER->institution;
        $values['userdepartment']  = $USER->department;
        $values['useraddress']     = $USER->address;
        $values['usercity']        = $USER->city;
        $now = new DateTime('now', core_date::get_user_timezone_object());
        $values['usertimezone']    = $now->getOffset() / 3600.0; // Value in hours for BC.
        $values['userwebexsession']         = $USER->webexsession;
    }

    // weak imitation of Single-Sign-On, for backwards compatibility only
    // NOTE: login hack is not included in 2.0 any more, new contrib auth plugin
    //       needs to be createed if somebody needs the old functionality!
    if (!empty($config->secretphrase)) {
        $values['encryptedcode'] = webexsession_get_encrypted_parameter($webexsession, $config);
    }

    //hmm, this is pretty fragile and slow, why do we need it here??
    if ($config->rolesinparams) {
        $coursecontext = context_course::instance($course->id);
        $roles = role_fix_names(get_all_roles($coursecontext), $coursecontext, ROLENAME_ALIAS);
        foreach ($roles as $role) {
            $values['course'.$role->shortname] = $role->localname;
        }
    }

    return $values;
}

/**
 * BC internal function
 * @param object $webexsession
 * @param object $config
 * @return string
 */
function webexsession_get_encrypted_parameter($webexsession, $config) {
    global $CFG;

    if (file_exists("$CFG->dirroot/local/externserverfile.php")) {
        require_once("$CFG->dirroot/local/externserverfile.php");
        if (function_exists('extern_server_file')) {
            return extern_server_file($webexsession, $config);
        }
    }
    return md5(getremoteaddr().$config->secretphrase);
}

/**
 * Optimised mimetype detection from general Webex Session
 * @param $fullwebexsession
 * @param int $size of the icon.
 * @return string|null mimetype or null when the filetype is not relevant.
 */
function webexsession_guess_icon($fullwebexsession, $size = null) {
    global $CFG;
    require_once("$CFG->libdir/filelib.php");

    if (substr_count($fullwebexsession, '/') < 3 or substr($fullwebexsession, -1) === '/') {
        // Most probably default directory - index.php, index.html, etc. Return null because
        // we want to use the default module icon instead of the HTML file icon.
        return null;
    }

    $icon = file_extension_icon($fullwebexsession, $size);
    $htmlicon = file_extension_icon('.htm', $size);
    $unknownicon = file_extension_icon('', $size);

    // We do not want to return those icon types, the module icon is more appropriate.
    if ($icon === $unknownicon || $icon === $htmlicon) {
        return null;
    }

    return $icon;
}

function resourcelib_guess_webexsession_mimetype($fullurl) {
    global $CFG;
    require_once("$CFG->libdir/filelib.php");

    if ($fullurl instanceof moodle_url) {
        $fullurl = $fullurl->out(false);
    }

    $matches = null;
    if (preg_match("|^(.*)/[a-z]*file.php(\?file=)?(/[^&\?#]*)|", $fullurl, $matches)) {
        // remove the special moodle file serving hacks so that the *file.php is ignored
        $fullurl = $matches[1].$matches[3];
    }

    if (preg_match("|^(.*)#.*|", $fullurl, $matches)) {
        // ignore all anchors
        $fullurl = $matches[1];
    }

    if (strpos($fullurl, '.php')){
        // we do not really know what is in general php script
        return 'text/html';

    } else if (substr($fullurl, -1) === '/') {
        // directory index (http://example.com/smaples/)
        return 'text/html';

    } else if (strpos($fullurl, '//') !== false and substr_count($fullurl, '/') == 2) {
        // just a host name (http://example.com), solves Australian servers "audio" problem too
        return 'text/html';

    } else {
        // ok, this finally looks like a real file
        $parts = explode('?', $fullurl);
        $url = reset($parts);
        return mimeinfo('type', $url);
    }
}
