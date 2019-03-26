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
 * Strings for component 'webexsession', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package    mod_webexsession
 * @copyright  2017 Sudhanshu Gupta {@link sudhanshug5@gmail.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['clicktoopen'] = 'Click {$a} link to open resource.';
$string['configdisplayoptions'] = 'Select all options that should be available, existing settings are not modified. Hold CTRL key to select multiple fields.';
$string['configframesize'] = 'When a web page or an uploaded file is displayed within a frame, this value is the height (in pixels) of the top frame (which contains the navigation).';
$string['configrolesinparams'] = 'Enable if you want to include localized role names in list of available parameter variables.';
$string['configsecretphrase'] = 'This secret phrase is used to produce encrypted code value that can be sent to some servers as a parameter.  The encrypted code is produced by an md5 value of the current user IP address concatenated with your secret phrase. ie code = md5(IP.secretphrase). Please note that this is not reliable because IP address may change and is often shared by different computers.';
$string['contentheader'] = 'Content';
$string['createwebexsession'] = 'Create a Webex Session';
$string['displayoptions'] = 'Available display options';
$string['displayselect'] = 'Display';
$string['displayselect_help'] = 'This setting, together with the Webex Session file type and whether the browser allows embedding, determines how the Webex Session is displayed. Options may include:

* Automatic - The best display option for the Webex Session is selected automatically
* Embed - The Webex Session is displayed within the page below the navigation bar together with the Webex Session description and any blocks
* Open - Only the Webex Session is displayed in the browser window
* In pop-up - The Webex Session is displayed in a new browser window without menus or an address bar
* In frame - The Webex Session is displayed within a frame below the navigation bar and Webex Session description
* New window - The Webex Session is displayed in a new browser window with menus and an address bar';
$string['displayselectexplain'] = 'Choose display type, unfortunately not all types are suitable for all Webex Sessions.';
$string['externalwebexsession'] = 'External Webex Session';
$string['framesize'] = 'Frame height';
$string['invalidstoredwebexsession'] = 'Cannot display this resource, Webex Session is invalid.';
$string['chooseavariable'] = 'Choose a variable...';
$string['invalidwebexsession'] = 'Entered Webex Session is invalid';
$string['modulename'] = 'Webex Session';
$string['modulename_help'] = 'The Webex Session module enables a teacher to provide a web link as a course resource. Anything that is freely available online, such as documents or images, can be linked to; the Webex Session doesn’t have to be the home page of a website. The Webex Session of a particular web page may be copied and pasted or a teacher can use the file picker and choose a link from a repository such as Flickr, YouTube or Wikimedia (depending upon which repositories are enabled for the site).

There are a number of display options for the Webex Session, such as embedded or opening in a new window and advanced options for passing information, such as a student\'s name, to the Webex Session if required.

Note that Webex Sessions can also be added to any other resource or activity type through the text editor.';
$string['modulename_link'] = 'mod/webexsession/view';
$string['modulenameplural'] = 'Webex Sessions';
$string['page-mod-webexsession-x'] = 'Any Webex Session module page';
$string['parameterinfo'] = '&amp;parameter=variable';
$string['parametersheader'] = 'Webex Session variables';
$string['parametersheader_help'] = 'Some internal Moodle variables may be automatically appended to the Webex Session. Type your name for the parameter into each text box(es) and then select the required matching variable.';
$string['pluginadministration'] = 'Webex Session module administration';
$string['pluginname'] = 'Webex Session';
$string['popupheight'] = 'Pop-up height (in pixels)';
$string['popupheightexplain'] = 'Specifies default height of popup windows.';
$string['popupwidth'] = 'Pop-up width (in pixels)';
$string['popupwidthexplain'] = 'Specifies default width of popup windows.';
$string['printintro'] = 'Display Webex Session description';
$string['printintroexplain'] = 'Display Webex Session description below content? Some display types may not display description even if enabled.';
$string['rolesinparams'] = 'Include role names in parameters';
$string['search:activity'] = 'Webex Session';
$string['serverwebexsession'] = 'Server Webex Session';
$string['webexsession:addinstance'] = 'Add a new Webex Session resource';
$string['webexsession:view'] = 'View Webex Session';
