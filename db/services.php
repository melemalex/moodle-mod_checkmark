<?php

$functions = array(

        'mod_checkmark_get_checkmarks_by_courses' => array(
            'classname'     => 'mod_checkmark_external',
            'methodname'    => 'get_checkmarks_by_courses',
            'classpath'     => 'mod/checkmark/externallib.php',
            'description'   => 'Get all checkmarks in the given courses',
            'type'          => 'read',
        ),

        'mod_checkmark_get_checkmark' => array(
            'classname'     => 'mod_checkmark_external',
            'methodname'    => 'get_checkmark',
            'classpath'     => 'mod/checkmark/externallib.php',
            'description'   => 'Get the checkmark with the given id',
            'type'          => 'read',
        ),

        'mod_checkmark_get_submission' => array(
            'classname'     => 'mod_checkmark_external',
            'methodname'    => 'get_submission',
            'classpath'     => 'mod/checkmark/externallib.php',
            'description'   => 'Get submission for checkmark',
            'type'          => 'read',
        ),

        'mod_checkmark_submit' => array(
            'classname'     => 'mod_checkmark_external',
            'methodname'    => 'submit',
            'classpath'     => 'mod/checkmark/externallib.php',
            'description'   => 'Get submission for checkmark',
            'type'          => 'read',
        ),

        'mod_checkmark_get_checkmark_access_information' => array(
            'classname'     => 'mod_checkmark_external',
            'methodname'    => 'get_checkmark_access_information',
            'classpath'     => 'mod/checkmark/externallib.php',
            'description'   => 'Get submission for checkmark',
            'type'          => 'read',
        ),

);
