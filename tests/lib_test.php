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
 * Unit tests for some mod Webex Session lib stuff.
 *
 * @package    mod_webexsession
 * @category   phpunit
 * @copyright  2017 Sudhanshu Gupta {@link sudhanshug5@gmail.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 * mod_webexsession tests
 *
 * @package    mod_webexsession
 * @category   phpunit
 * @copyright  2011 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_webexsession_lib_testcase extends advanced_testcase {

    /**
     * Prepares things before this test case is initialised
     * @return void
     */
    public static function setUpBeforeClass() {
        global $CFG;
        require_once($CFG->dirroot . '/mod/webexsession/lib.php');
        require_once($CFG->dirroot . '/mod/webexsession/locallib.php');
    }

    /**
     * Tests the webexsession_appears_valid_webexsession function
     * @return void
     */
    public function test_webexsession_appears_valid_webexsession() {
        $this->assertTrue(webexsession_appears_valid_webexsession('http://example'));
        $this->assertTrue(webexsession_appears_valid_webexsession('http://www.example.com'));
        $this->assertTrue(webexsession_appears_valid_webexsession('http://www.exa-mple2.com'));
        $this->assertTrue(webexsession_appears_valid_webexsession('http://www.example.com/~nobody/index.html'));
        $this->assertTrue(webexsession_appears_valid_webexsession('http://www.example.com#hmm'));
        $this->assertTrue(webexsession_appears_valid_webexsession('http://www.example.com/#hmm'));
        $this->assertTrue(webexsession_appears_valid_webexsession('http://www.example.com/žlutý koníček/lala.txt'));
        $this->assertTrue(webexsession_appears_valid_webexsession('http://www.example.com/žlutý koníček/lala.txt#hmmmm'));
        $this->assertTrue(webexsession_appears_valid_webexsession('http://www.example.com/index.php?xx=yy&zz=aa'));
        $this->assertTrue(webexsession_appears_valid_webexsession('https://user:password@www.example.com/žlutý koníček/lala.txt'));
        $this->assertTrue(webexsession_appears_valid_webexsession('ftp://user:password@www.example.com/žlutý koníček/lala.txt'));

        $this->assertFalse(webexsession_appears_valid_webexsession('http:example.com'));
        $this->assertFalse(webexsession_appears_valid_webexsession('http:/example.com'));
        $this->assertFalse(webexsession_appears_valid_webexsession('http://'));
        $this->assertFalse(webexsession_appears_valid_webexsession('http://www.exa mple.com'));
        $this->assertFalse(webexsession_appears_valid_webexsession('http://www.examplé.com'));
        $this->assertFalse(webexsession_appears_valid_webexsession('http://@www.example.com'));
        $this->assertFalse(webexsession_appears_valid_webexsession('http://user:@www.example.com'));

        $this->assertTrue(webexsession_appears_valid_webexsession('lalala://@:@/'));
    }

    /**
     * Test webexsession_view
     * @return void
     */
    public function test_webexsession_view() {
        global $CFG;

        $CFG->enablecompletion = 1;
        $this->resetAfterTest();

        // Setup test data.
        $course = $this->getDataGenerator()->create_course(array('enablecompletion' => 1));
        $webexsession = $this->getDataGenerator()->create_module('webexsession', array('course' => $course->id),
                                                            array('completion' => 2, 'completionview' => 1));
        $context = context_module::instance($webexsession->cmid);
        $cm = get_coursemodule_from_instance('webexsession', $webexsession->id);

        // Trigger and capture the event.
        $sink = $this->redirectEvents();

        $this->setAdminUser();
        webexsession_view($webexsession, $course, $cm, $context);

        $events = $sink->get_events();
        // 2 additional events thanks to completion.
        $this->assertCount(3, $events);
        $event = array_shift($events);

        // Checking that the event contains the expected values.
        $this->assertInstanceOf('\mod_webexsession\event\course_module_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $webexsession = new \moodle_webexsession('/mod/webexsession/view.php', array('id' => $cm->id));
        $this->assertEquals($webexsession, $event->get_webexsession());
        $this->assertEventContextNotUsed($event);
        $this->assertNotEmpty($event->get_name());

        // Check completion status.
        $completion = new completion_info($course);
        $completiondata = $completion->get_data($cm);
        $this->assertEquals(1, $completiondata->completionstate);

    }
}