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

        $result = mod_checkmark_external::get_checkmark($checkmark->cmid);

        // checkmark name should be equal to 'Checkmark Module'
        $this->assertEquals('Checkmark Module', $result->checkmark->name);

        // Course id in checkmark should be equal to the id of the course
        $this->assertEquals($course->id, $result->checkmark->course);
    }

    public function test_get_checkmark_hidden() {
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
            'name' => 'Hidden Checkmark Module',
            'intro' => 'Checkmark module for automated php unit tests',
            'introformat' => FORMAT_HTML,
            'visible' => 0,
        ]);

        $this->setUser($user);

        // Test should throw require_login_exception
        $this->expectException(require_login_exception::class);

        $result = mod_checkmark_external::get_checkmark($checkmark->cmid);

    }

    public function test_get_submit() {
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

        $result = mod_checkmark_external::get_checkmark($checkmark->cmid);
        echo json_encode($result);

        $submission_examples = [
            ['id' => 1, 'checked' => 1],
            ['id' => 2, 'checked' => 0],
            ['id' => 3, 'checked' => 1],
            ['id' => 4, 'checked' => 0],
            ['id' => 5, 'checked' => 1],
            ['id' => 6, 'checked' => 0],
            ['id' => 7, 'checked' => 1],
            ['id' => 8, 'checked' => 0],
            ['id' => 9, 'checked' => 1],
            ['id' => 10, 'checked' => 0],
        ];

        $result = mod_checkmark_external::submit($checkmark->cmid, $submission_examples);

        // checkmark name should be equal to 'Checkmark Module'
        $this->assertEquals('Checkmark Module', $result->checkmark->name);

        // Course id in checkmark should be equal to the id of the course
        $this->assertEquals($course->id, $result->checkmark->course);

        // check the examples checked status of the result object
        $this->assertEquals(1, $result->checkmark->examples[0]->checked);
        $this->assertEquals(0, $result->checkmark->examples[1]->checked);
        $this->assertEquals(1, $result->checkmark->examples[2]->checked);
        $this->assertEquals(0, $result->checkmark->examples[3]->checked);
        $this->assertEquals(1, $result->checkmark->examples[4]->checked);
        $this->assertEquals(0, $result->checkmark->examples[5]->checked);
        $this->assertEquals(1, $result->checkmark->examples[6]->checked);
        $this->assertEquals(0, $result->checkmark->examples[7]->checked);
        $this->assertEquals(1, $result->checkmark->examples[8]->checked);
        $this->assertEquals(0, $result->checkmark->examples[9]->checked);

        $result = mod_checkmark_external::get_checkmark($checkmark->cmid);

        // checkmark name should be equal to 'Checkmark Module'
        $this->assertEquals('Checkmark Module', $result->checkmark->name);

        // Course id in checkmark should be equal to the id of the course
        $this->assertEquals($course->id, $result->checkmark->course);

        // check the examples checked status was correctly saved
        $this->assertEquals(1, $result->checkmark->examples[0]->checked);
        $this->assertEquals(0, $result->checkmark->examples[1]->checked);
        $this->assertEquals(1, $result->checkmark->examples[2]->checked);
        $this->assertEquals(0, $result->checkmark->examples[3]->checked);
        $this->assertEquals(1, $result->checkmark->examples[4]->checked);
        $this->assertEquals(0, $result->checkmark->examples[5]->checked);
        $this->assertEquals(1, $result->checkmark->examples[6]->checked);
        $this->assertEquals(0, $result->checkmark->examples[7]->checked);
        $this->assertEquals(1, $result->checkmark->examples[8]->checked);
        $this->assertEquals(0, $result->checkmark->examples[9]->checked);
    }
}
