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
 * File description for the local_securitypatcher plugin.
 *
 * @package   local_securitypatcher
 * @copyright 2023 onwards Andrei-Robert Tica <andreastsika@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

use core\output\notification;
use local_securitypatcher\forms\addpatch_form;
use local_securitypatcher\api;

global $OUTPUT, $PAGE;

require_admin();

// Set page configuration.
$pageurl = new moodle_url('/local/securitypatcher/add.php');
$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('admin');
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_title(new lang_string('add:title', 'local_securitypatcher'));
$PAGE->set_heading(new lang_string('add:header', 'local_securitypatcher'));

// Set up the form instance.
$mform = new addpatch_form();
$toform = array();

//echo $OUTPUT->single_button($continueurl, get_string('continueuninstall', 'repository'));

$result = api::get_stored_patch_file(13);
//var_dump($result);

// Form actions.
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/'));
} else if ($fromform = $mform->get_data()) {

    $savedpatch = api::patch_file_save($fromform);

    if ($savedpatch === false) {
        redirect($pageurl, get_string('notification:failnewpatchsave', 'local_securitypatcher'),
                null, notification::NOTIFY_ERROR);
    }

    redirect($pageurl, get_string('notification:successnewpatchsave', 'local_securitypatcher'),
            null, notification::NOTIFY_SUCCESS);
} else {
    $mform->set_data($toform);
    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
}
