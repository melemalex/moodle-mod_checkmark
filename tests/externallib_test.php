<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/checkmark/externallib.php');

/**
 * External mod checkmark functions unit tests
 */
class mod_assign_external_testcase extends externallib_advanced_testcase {

    public function test_get_checkmark() {
        global $CFG, $DB, $USER;

        $this->resetAfterTest(true);

        $course = $this->getDataGenerator()->create_course([
            'idnumber' => 'idnumbercourse',
            'fullname' => 'PHPUnitTestCourse',
            'summary' => 'Test course for automated php unit tests',
            'summaryformat' => FORMAT_HTML
        ]);

        $user = $this->getDataGenerator()->create_user();

        $this->getDataGenerator()->enrol_user($user->id, $course->id);

        $checkmark = self::getDataGenerator()->create_module('checkmark', [
            'course' => $course->id,
            'name' => 'Checkmark Module',
            'intro' => 'Checkmark module for automated php unit tests',
            'introformat' => FORMAT_HTML,
        ]);

        $this->setUser($user);

        $result = mod_checkmark_external::get_checkmarks_by_courses([]);

        $this->assertEquals(1, count($result['checkmarks']));

        $this->assertEquals('Checkmark Module', $result['checkmarks'][0]['name']);
    }

}