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
 * Url module admin settings and defaults
 *
 * @package    mod_webexsession
 * @copyright  2017 Sudhanshu Gupta {@link sudhanshug5@gmail.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once("$CFG->libdir/resourcelib.php");

    $displayoptions = resourcelib_get_displayoptions(array(RESOURCELIB_DISPLAY_AUTO,
                                                           RESOURCELIB_DISPLAY_EMBED,
                                                           RESOURCELIB_DISPLAY_FRAME,
                                                           RESOURCELIB_DISPLAY_OPEN,
                                                           RESOURCELIB_DISPLAY_NEW,
                                                           RESOURCELIB_DISPLAY_POPUP,
                                                          ));
    $defaultdisplayoptions = array(RESOURCELIB_DISPLAY_AUTO,
                                   RESOURCELIB_DISPLAY_EMBED,
                                   RESOURCELIB_DISPLAY_OPEN,
                                   RESOURCELIB_DISPLAY_POPUP,
                                  );

    //--- general settings -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_configtext('webexsession/framesize',
        get_string('framesize', 'webexsession'), get_string('configframesize', 'webexsession'), 130, PARAM_INT));
    $settings->add(new admin_setting_configpasswordunmask('webexsession/secretphrase', get_string('password'),
        get_string('configsecretphrase', 'webexsession'), ''));
    $settings->add(new admin_setting_configcheckbox('webexsession/rolesinparams',
        get_string('rolesinparams', 'webexsession'), get_string('configrolesinparams', 'webexsession'), false));
    $settings->add(new admin_setting_configmultiselect('webexsession/displayoptions',
        get_string('displayoptions', 'webexsession'), get_string('configdisplayoptions', 'webexsession'),
        $defaultdisplayoptions, $displayoptions));

    //--- modedit defaults -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('webexsessionmodeditdefaults', get_string('modeditdefaults', 'admin'), get_string('condifmodeditdefaults', 'admin')));

    $settings->add(new admin_setting_configcheckbox('webexsession/printintro',
        get_string('printintro', 'webexsession'), get_string('printintroexplain', 'webexsession'), 1));
    $settings->add(new admin_setting_configselect('webexsession/display',
        get_string('displayselect', 'webexsession'), get_string('displayselectexplain', 'webexsession'), RESOURCELIB_DISPLAY_AUTO, $displayoptions));
    $settings->add(new admin_setting_configtext('webexsession/popupwidth',
        get_string('popupwidth', 'webexsession'), get_string('popupwidthexplain', 'webexsession'), 620, PARAM_INT, 7));
    $settings->add(new admin_setting_configtext('webexsession/popupheight',
        get_string('popupheight', 'webexsession'), get_string('popupheightexplain', 'webexsession'), 450, PARAM_INT, 7));
}
