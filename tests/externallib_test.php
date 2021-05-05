<?php

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/mod/checkmark/externallib.php');

/**
 * External mod checkmark functions unit tests
 */
class mod_assign_external_testcase extends externallib_advanced_testcase {

    public function test_get_checkmarks_by_courses() {
        global $CFG, $DB, $USER;

        $this->resetAfterTest(true);

        $user = $this->getDataGenerator()->create_user();

        $course1 = $this->getDataGenerator()->create_course([
            'fullname' => 'PHPUnitTestCourse1',
            'summary' => 'Test course for automated php unit tests',
            'summaryformat' => FORMAT_HTML
        ]);

        $this->getDataGenerator()->enrol_user($user->id, $course1->id);

        $course2 = $this->getDataGenerator()->create_course([
            'fullname' => 'PHPUnitTestCourse2',
            'summary' => 'Test course for automated php unit tests',
            'summaryformat' => FORMAT_HTML
        ]);

        $this->getDataGenerator()->enrol_user($user->id, $course2->id);

        $course3 = $this->getDataGenerator()->create_course([
            'fullname' => 'PHPUnitTestCourse3',
            'summary' => 'Test course for automated php unit tests',
            'summaryformat' => FORMAT_HTML
        ]);

        $this->getDataGenerator()->enrol_user($user->id, $course3->id);

        $checkmark1 = self::getDataGenerator()->create_module('checkmark', [
            'course' => $course1->id,
            'name' => 'Checkmark Module 1',
            'intro' => 'Checkmark module for automated php unit tests',
            'introformat' => FORMAT_HTML,
        ]);

        $checkmark2 = self::getDataGenerator()->create_module('checkmark', [
            'course' => $course2->id,
            'name' => 'Checkmark Module 2',
            'intro' => 'Checkmark module for automated php unit tests',
            'introformat' => FORMAT_HTML,
        ]);

        $checkmark3 = self::getDataGenerator()->create_module('checkmark', [
            'course' => $course3->id,
            'name' => 'Checkmark Module 3',
            'intro' => 'Checkmark module for automated php unit tests',
            'introformat' => FORMAT_HTML,
        ]);

        $this->setUser($user);

        $result = mod_checkmark_external::get_checkmarks_by_courses([]);

        // user is enrolled only in course1 and course2, so the third checkmark module in course3 should not be included
        $this->assertEquals(2, count($result->checkmarks));
    }

    public function test_get_checkmark() {
        global $CFG, $DB, $USER;

        $this->resetAfterTest(true);

        $user = $this->getDataGenerator()->create_user();

        $course = $this->getDataGenerator()->create_course([
            'fullname' => 'PHPUnitTestCourse',
            'summary' => 'Test course for automated php unit tests',
            'summaryformat' => FORMAT_HTML
        ]);

        $this->getDataGenerator()->enrol_user($user->id, $course->id);

        $checkmark = self::getDataGenerator()->create_module('checkmark', [
            'course' => $course->id,
            'name' => 'Checkmark Module',
            'intro' => 'Checkmark module for automated php unit tests',
            'introformat' => FORMAT_HTML,
        ]);

        $this->setUser($user);

        $result = mod_checkmark_external::get_checkmark($checkmark->id);

        // checkmark name should be equal to 'Checkmark Module'
        $this->assertEquals('Checkmark Module', $result->checkmark->name);

        // Course id in checkmark should be equal to the id of the course
        $this->assertEquals($course->id, $result->checkmark->course);
    }

}