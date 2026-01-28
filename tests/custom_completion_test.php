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

// Project implemented by the "Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos..

/**
 * Custom completion test
 *
 * @package    mod_kuet
 * @copyright  2024 Proyecto UNIMOODLE {@link https://unimoodle.github.io}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_kuet;

use advanced_testcase;
use coding_exception;
use cm_info;
use mod_kuet\completion\custom_completion;

/**
 * Custom completion test class
 *
 * @package    mod_kuet
 * @copyright  2024 Proyecto UNIMOODLE
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_kuet\completion\custom_completion
 */
class custom_completion_test extends advanced_testcase {

    /**
     * Test get_defined_custom_rules.
     *
     * @return void
     */
    public function test_get_defined_custom_rules(): void {
        $rules = custom_completion::get_defined_custom_rules();
        $this->assertContains('completionanswerall', $rules);
    }

    /**
     * Test get_state for completionanswerall when not complete.
     *
     * @return void
     * @throws coding_exception
     */
    public function test_get_state_completionanswerall_incomplete(): void {
        global $DB;
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $student = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');

        $kuet = $this->getDataGenerator()->create_module('kuet', [
            'course' => $course->id,
            'completionanswerall' => 1,
        ]);

        $cm = get_coursemodule_from_instance('kuet', $kuet->id);
        $cminfo = cm_info::create($cm);

        $customcompletion = new custom_completion($cminfo, $student->id);
        $this->assertEquals(COMPLETION_INCOMPLETE, $customcompletion->get_state('completionanswerall'));
    }

    /**
     * Test get_state for completionanswerall when complete.
     *
     * @return void
     * @throws coding_exception
     */
    public function test_get_state_completionanswerall_complete(): void {
        global $DB;
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $student = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');

        $kuet = $this->getDataGenerator()->create_module('kuet', [
            'course' => $course->id,
            'completionanswerall' => 1,
        ]);

        // Create a session.
        $sessionrecord = new \stdClass();
        $sessionrecord->kuetid = $kuet->id;
        $sessionrecord->name = 'Test Session';
        $sessionrecord->anonymousanswer = 0;
        $sessionrecord->sessionmode = 'manual';
        $sessionrecord->sgrade = 0;
        $sessionrecord->countdown = 0;
        $sessionrecord->showgraderanking = 0;
        $sessionrecord->randomquestions = 0;
        $sessionrecord->randomanswers = 0;
        $sessionrecord->showfeedback = 0;
        $sessionrecord->showfinalgrade = 0;
        $sessionrecord->startdate = 0;
        $sessionrecord->enddate = 0;
        $sessionrecord->automaticstart = 0;
        $sessionrecord->timemode = 'session';
        $sessionrecord->sessiontime = 0;
        $sessionrecord->questiontime = 30;
        $sessionrecord->groupings = 0;
        $sessionrecord->status = 0;
        $sessionrecord->sessionid = '';
        $sessionrecord->submitbutton = 0;
        $sessionrecord->usermodified = 2;
        $sessionrecord->timecreated = time();
        $sessionrecord->timemodified = time();
        $sessionid = $DB->insert_record('kuet_sessions', $sessionrecord);

        // Create a response.
        $responserecord = new \stdClass();
        $responserecord->kuet = $kuet->id;
        $responserecord->session = $sessionid;
        $responserecord->userid = $student->id;
        $responserecord->kid = 1;
        $responserecord->questionid = 1;
        $responserecord->anonymise = 0;
        $responserecord->response = '';
        $responserecord->hasfeedbacks = 0;
        $responserecord->result = 1;
        $responserecord->usermodified = $student->id;
        $responserecord->timecreated = time();
        $responserecord->timemodified = time();
        $DB->insert_record('kuet_questions_responses', $responserecord);

        $cm = get_coursemodule_from_instance('kuet', $kuet->id);
        $cminfo = cm_info::create($cm);

        $customcompletion = new custom_completion($cminfo, $student->id);
        $this->assertEquals(COMPLETION_COMPLETE, $customcompletion->get_state('completionanswerall'));
    }

    /**
     * Test get_custom_rule_descriptions.
     *
     * @return void
     * @throws coding_exception
     */
    public function test_get_custom_rule_descriptions(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $student = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');

        $kuet = $this->getDataGenerator()->create_module('kuet', [
            'course' => $course->id,
            'completionanswerall' => 1,
        ]);

        $cm = get_coursemodule_from_instance('kuet', $kuet->id);
        $cminfo = cm_info::create($cm);

        $customcompletion = new custom_completion($cminfo, $student->id);
        $descriptions = $customcompletion->get_custom_rule_descriptions();

        $this->assertArrayHasKey('completionanswerall', $descriptions);
        $this->assertIsString($descriptions['completionanswerall']);
    }

    /**
     * Test get_sort_order.
     *
     * @return void
     * @throws coding_exception
     */
    public function test_get_sort_order(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $student = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');

        $kuet = $this->getDataGenerator()->create_module('kuet', [
            'course' => $course->id,
        ]);

        $cm = get_coursemodule_from_instance('kuet', $kuet->id);
        $cminfo = cm_info::create($cm);

        $customcompletion = new custom_completion($cminfo, $student->id);
        $sortorder = $customcompletion->get_sort_order();

        $this->assertIsArray($sortorder);
        $this->assertContains('completionanswerall', $sortorder);
        $this->assertContains('completionusegrade', $sortorder);
        $this->assertContains('completionpassgrade', $sortorder);
    }
}
