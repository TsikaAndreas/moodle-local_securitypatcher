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
 * Security patch creation and editing form for the local_securitypatcher plugin.
 *
 * @package   local_securitypatcher
 * @copyright 2023 onwards Andrei-Robert Tica <andreastsika@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

use core\output\notification;
use local_securitypatcher\forms\addpatch_form;
use local_securitypatcher\api;
use local_securitypatcher\managers\patch_manager;

global $OUTPUT, $PAGE, $CFG;

require_login();

// Optional Parameters.
$id = optional_param('id', null, PARAM_INT);

// Set page configuration.
$pageurl = new moodle_url('/local/securitypatcher/patch.php');
$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('admin');
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_title(new lang_string('patch:title', 'local_securitypatcher'));
$PAGE->set_heading(new lang_string('patch:header', 'local_securitypatcher'));

// Initialize the patch manager.
$manager = new patch_manager();

// Check if an ID is provided, indicating whether this is a new or existing security patch.
if (empty($id)) {
    require_capability('local/securitypatcher:addpatch', $context);
    $securitypatch = new stdClass();
    $securitypatch->id = null;
} else {
    require_capability('local/securitypatcher:editpatch', $context);
    $securitypatch = $manager->get_patch($id);
}

// Set up the form instance with the security patch data.
$filemangeroptions = api::get_patch_filemanager_options();
$formargs = ['patch' => $securitypatch];
$mform = new addpatch_form(null, $formargs);
$toform = array();

// Prepare the file manager for handling attachments.
file_prepare_standard_filemanager($securitypatch, 'attachments', $filemangeroptions, $context,
        $manager::$component, $manager::$filearea, $securitypatch->id);

// Form actions.
if ($mform->is_cancelled()) {
    // If the form is canceled, redirect to the home page.
    redirect(new moodle_url('/'));
} else if ($fromform = $mform->get_data()) {
    // If form data is submitted.
    if (empty($securitypatch->id)) {
        // If it's a new security patch then create it.
        $patch = $manager->create_patch($fromform);
    } else {
        // If it's an existing security patch, update it.
        $fromform->id = $securitypatch->id;
        $patch = $manager->update_patch($fromform);
    }

    // Redirection based on success or failure.
    if ($patch === false) {
        // Redirect with an error notification in case of failure.
        redirect($pageurl, get_string('notification:failnewpatchsave', 'local_securitypatcher'),
                null, notification::NOTIFY_ERROR);
    } else {
        // Redirect with a success notification in case of success.
        redirect($pageurl, get_string('notification:successnewpatchsave', 'local_securitypatcher'),
                null, notification::NOTIFY_SUCCESS);
    }
} else {
    // If no form data is submitted, set the form data.
    $mform->set_data($securitypatch);
    // Render the page.
    echo $OUTPUT->header();
    // Display the form.
    $mform->display();
    echo $OUTPUT->footer();
}
