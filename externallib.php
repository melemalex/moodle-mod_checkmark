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


defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");
require_once("$CFG->dirroot/user/externallib.php");
require_once($CFG->dirroot . '/mod/checkmark/locallib.php');


class mod_checkmark_external extends external_api {

    public static function get_examples_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'checkmark id'),
            )
        );
    }

    public static function get_examples_returns() {
        return new external_single_structure(
            array(
                'examples' => new external_multiple_structure(self::example_structure(), ''),
                'warnings' => new external_warnings('TODO')
            )
        );
    }

    public static function get_examples($id) {
        global $DB;
        $params = self::validate_parameters(self::get_grades_parameters(),
            array('id' => $id);

        $examples = array();
        $warnings = array();

        $checkmark = new checkmark($id);


        $checkmark->get_examples();

        $result = array();
        $result['examples'] = $examples;
        $result['warnings'] = $warnings;
        return $result;
    }

    private static function example_structure() {
        return new external_single_structure(
            array(
                'id' => new external_value(PARAM_INT, 'example id'),
                'name' => new external_value(PARAM_INT, 'example name'),
            ), 'example information'
        );
    }

}
