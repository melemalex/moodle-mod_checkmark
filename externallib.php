<?php

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");
require_once("$CFG->dirroot/user/externallib.php");
require_once($CFG->dirroot . '/mod/checkmark/locallib.php');


class mod_checkmark_external extends external_api {


    public static function debug_info_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'checkmark id'),
            )
        );
    }

    public static function debug_info_returns() {
        return new external_single_structure(
            array(
                'debug' => new external_value(PARAM_RAW, "info"),
                'warnings' => new external_warnings('TODO')
            )
        );
    }

    public static function debug_info($id) {
        $params = self::validate_parameters(self::debug_info_parameters(), array('id' => $id));

        $checkmark = new checkmark($params['id']);

        $context = context_module::instance($checkmark->cm->id);
        require_capability('mod/checkmark:view', $context);
        self::validate_context($context);


        $debug_info = array();

        $debug_info["checkmark"] = $checkmark;
        $debug_info["submission"] = $checkmark->get_submission();
        $debug_info["feedback"] = $checkmark->get_feedback();


        $warnings = array();

        $result = array();
        $result['debug'] = json_encode($debug_info);
        $result['warnings'] = $warnings;
        return $result;
    }

    public static function get_checkmarks_by_courses_parameters() {
        return new external_function_parameters(
            array(
                'courseids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'Course id'), 'Array of course ids (all enrolled courses if empty array)', VALUE_DEFAULT, array()
                ),
            )
        );
    }

    public static function get_checkmarks_by_courses_returns() {
        return new external_single_structure(
            array(
                'checkmarks' => new external_multiple_structure(self::checkmark_structure(), ''),
                'warnings' => new external_warnings('TODO')
            )
        );
    }

    public static function get_checkmarks_by_courses($courseids) {
        $warnings = array();

        $params = array(
            'courseids' => $courseids,
        );
        $params = self::validate_parameters(self::get_checkmarks_by_courses_parameters(), $params);

        $rcheckmarks = array();

        $mycourses = array();
        if (empty($params['courseids'])) {
            $mycourses = enrol_get_my_courses();
            $params['courseids'] = array_keys($mycourses);
        }


        // Ensure there are courseids to loop through.
        if (!empty($params['courseids'])) {

            list($courses, $warnings) = external_util::validate_courses($params['courseids'], $mycourses);

            // Get the checkmarks in this course, this function checks users visibility permissions.
            // We can avoid then additional validate_context calls.
            $checkmark_instances = get_all_instances_in_courses("checkmark", $courses);
            foreach ($checkmark_instances as $checkmark_instance) {

                $checkmark = new checkmark($checkmark_instance->coursemodule);
                $rcheckmarks[] = self::export_checkmark($checkmark);
            }
        }

        $result = array();
        $result['checkmarks'] = $rcheckmarks;
        $result['warnings'] = $warnings;
        return $result;
    }

    public static function get_checkmark_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'checkmark id'),
            )
        );
    }

    public static function get_checkmark_returns() {
        return new external_single_structure(
            array(
                'checkmark' => self::checkmark_structure(),
                'warnings' => new external_warnings('TODO')
            )
        );
    }

    public static function get_checkmark($id) {
        $params = self::validate_parameters(self::get_checkmark_parameters(), array('id' => $id));

        $checkmark = new checkmark($params['id']);

        $context = context_module::instance($checkmark->cm->id);
        require_capability('mod/checkmark:view', $context);
        self::validate_context($context);

        $warnings = array();

        $result = array();
        $result['checkmark'] = self::export_checkmark($checkmark);
        $result['warnings'] = $warnings;
        return $result;
    }

    public static function submit_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'checkmark id'),
                'submission_examples' => new external_multiple_structure(self::submit_example_structure(), 'submitted examples'),
            )
        );
    }

    public static function submit_returns() {
        return new external_single_structure(
            array(
                'checkmark' => self::checkmark_structure(),
                'warnings' => new external_warnings('TODO')
            )
        );
    }

    public static function submit($id, $submission_examples) {
        global $USER;
        $params = self::validate_parameters(self::submit_parameters(), array(
            'id' => $id,
            'submission_examples' => $submission_examples
        ));

        $warnings = array();

        $checkmark = new checkmark($params['id']);

        $context = context_module::instance($checkmark->cm->id);
        require_capability('mod/checkmark:view', $context);
        self::validate_context($context);

        $submission = $checkmark->get_submission();
        $feedback = $checkmark->get_feedback();

        // Guest can not submit nor edit an checkmark (bug: 4604)!
        if (!is_enrolled($checkmark->context, $USER, 'mod/checkmark:submit')) {
            $editable = false;
        } else {
            $editable = $checkmark->isopen() && (!$submission || $checkmark->checkmark->resubmit || ($feedback === false));
        }

        if (!$editable) {
            print_error('nosubmissionallowed', 'checkmark');
        }

        // Create the submission if needed & return its id!
        $submission = $checkmark->get_submission(0, true);


        $example_counter = count($submission->get_examples());
        foreach ($submission->get_examples() as $key => $example) {

            $maybe_submission_example = null;
            foreach ($params['submission_examples'] as $submission_example) {
                if ($example->get_id() === $submission_example['id']) {
                    $maybe_submission_example = $submission_example;
                    $example_counter--;
                    break;
                }
            }

            if ($maybe_submission_example && isset($maybe_submission_example['checked']) && $maybe_submission_example['checked'] != 0) {
                $submission->get_example($key)->set_state(\mod_checkmark\example::CHECKED);
            }else {
                $submission->get_example($key)->set_state(\mod_checkmark\example::UNCHECKED);
            }

        }

        if ($example_counter !== 0) {
            throw new InvalidArgumentException("Submission examples do not match the checkmark examples.");
        }

        $checkmark->update_submission($submission);
        $checkmark->email_teachers($submission);


        $result = array();
        $result['checkmark'] = self::export_checkmark($checkmark);
        $result['warnings'] = $warnings;
        return $result;
    }

    public static function get_checkmark_access_information_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'checkmark id'),
            )
        );
    }

    public static function get_checkmark_access_information_returns() {
        return new external_single_structure(
            array(
                'examples' => new external_multiple_structure(self::example_structure(), ''),
                'warnings' => new external_warnings('TODO')
            )
        );
    }

    public static function get_checkmark_access_information($id) {
        global $DB;
        $params = self::validate_parameters(self::get_checkmark_access_information_parameters(), array('id' => $id));
        // TODO use validated params!

        $examples = array();
        $warnings = array();

        $checkmark = new checkmark($id);

        foreach ($checkmark->get_checkmark_access_information() as $example) {
            $r = array();

            $r['id'] = $example->get_id();
            $r['name'] = $example->get_name();
            $r['checked'] = $example->is_checked() ? 1 : 0;

            $examples[] = $r;
        }

        $result = array();
        $result['examples'] = $examples;
        $result['warnings'] = $warnings;
        return $result;
    }

    private static function debug_structure() {
        return new external_single_structure(
            array(
                'all' => new external_value(PARAM_RAW, 'DEBUG'),
            ), 'debug information'
        );
    }

    private static function checkmark_structure() {
        return new external_single_structure(
            array(
                'id' => new external_value(PARAM_INT, 'checkmark id'),
                'instance' => new external_value(PARAM_INT, 'checkmark instance id'),
                'course' => new external_value(PARAM_INT, 'course id the checkmark belongs to'),
                'name' => new external_value(PARAM_TEXT, 'checkmark name'),
                'intro' => new external_value(PARAM_RAW, 'intro/description of the checkmark'),
                'introformat' => new external_value(PARAM_INT, 'intro format'),
                'timedue' => new external_value(PARAM_INT, 'time due of the checkmark'),
                'cutoffdate' => new external_value(PARAM_INT, 'cutoff date of the checkmark'),
                'submission_timecreated' => new external_value(PARAM_INT, 'submission created', VALUE_OPTIONAL),
                'submission_timemodified' => new external_value(PARAM_INT, 'submission changed', VALUE_OPTIONAL),
                'examples' => new external_multiple_structure(self::example_structure(), 'Examples'),
                'feedback' => self::feedback_structure(),
            ), 'example information'
        );
    }

    private static function feedback_structure() {
        return new external_single_structure(
            array(
                'grade' => new external_value(PARAM_TEXT, 'Grade'),
                'feedback' => new external_value(PARAM_TEXT, 'Feedback comment'),
                'timecreated' => new external_value(PARAM_INT, 'Time the feedback was given'),
                'timemodified' => new external_value(PARAM_INT, 'Time the feedback was modified'),
            ), 'submission information',
            VALUE_OPTIONAL
        );
    }

    private static function example_structure() {
        return new external_single_structure(
            array(
                'id' => new external_value(PARAM_INT, 'example id'),
                'name' => new external_value(PARAM_TEXT, 'example name'),
                'checked' => new external_value(PARAM_INT, 'example checked state', VALUE_OPTIONAL),
            ), 'example information'
        );
    }

    private static function submit_example_structure() {
        return new external_single_structure(
            array(
                'id' => new external_value(PARAM_INT, 'example id'),
                'name' => new external_value(PARAM_TEXT, 'example name', VALUE_OPTIONAL),
                'checked' => new external_value(PARAM_INT, 'example checked state'),
            ), 'example information'
        );
    }

    /**
     * @param $checkmark checkmark  The checkmark to be exported
     * @return object               The exported checkmark (conforms to the checkmark_structure)
     * @throws dml_exception
     */
    private static function export_checkmark($checkmark) {
        $result_checkmark = array();

        $result_checkmark['id'] = $checkmark->cm->id;
        $result_checkmark['instance'] = $checkmark->checkmark->id;
        $result_checkmark['course'] = $checkmark->checkmark->course;
        $result_checkmark['name'] = $checkmark->checkmark->name;
        $result_checkmark['intro'] = $checkmark->checkmark->intro;
        $result_checkmark['introformat'] = $checkmark->checkmark->introformat;
        $result_checkmark['timedue'] = $checkmark->checkmark->timedue;
        $result_checkmark['cutoffdate'] = $checkmark->checkmark->cutoffdate;

        if ($checkmark->get_submission()) {
            $result_checkmark['submission_timecreated'] = $checkmark->get_submission()->timecreated;
            $result_checkmark['submission_timemodified'] = $checkmark->get_submission()->timemodified;
            $result_checkmark['examples'] = self::export_examples($checkmark->get_submission()->get_examples(), true);
        }else {
            $result_checkmark['examples'] = self::export_examples($checkmark->get_examples());
        }

        if ($checkmark->get_feedback()) {
            $result_checkmark['feedback'] = self::export_feedback($checkmark->get_feedback());
        }

        return $result_checkmark;
    }

    /**
     * @param $examples \mod_checkmark\example[]    The examples to export
     * @param false $export_checked                 Export the information if the example is checked by the user via a submission
     * @return array                                The exported examples (conforms to the example_structure)
     */
    private static function export_examples($examples, $export_checked = false) {
        $result_examples = array();
        foreach ($examples as $example) {

            $result_example = array();
            $result_example['id'] = $example->get_id();
            $result_example['name'] = $example->get_name();

            if ($export_checked) {
                $result_example['checked'] = $example->is_checked() ? 1 : 0;
            }

            $result_examples[] = $result_example;
        }

        return $result_examples;
    }


    /**
     * @param $feedback object  Feedback to be exported
     * @return object           The exported feedback (conforms to the feedback_structure)
     */
    private static function export_feedback($feedback) {
        $result_feedback = array();
        $result_feedback['grade'] = $feedback->grade;
        $result_feedback['feedback'] = $feedback->feedback;
        $result_feedback['timecreated'] = $feedback->timecreated;
        $result_feedback['timemodified'] = $feedback->timemodified;

        return $result_feedback;
    }

}
