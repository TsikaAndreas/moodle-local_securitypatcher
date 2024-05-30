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
 * Code patch creation and editing form for the local_codepatcher plugin.
 *
 * @package   local_codepatcher
 * @copyright 2023 onwards Andrei-Robert Țîcă <andreastsika@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

use core\output\notification;
use local_codepatcher\forms\addpatch_form;
use local_codepatcher\api;
use local_codepatcher\managers\patch_manager;

global $OUTPUT, $PAGE, $CFG;

require_login();

// Optional Parameters.
$id = optional_param('id', null, PARAM_INT);

// Set page configuration.
$pageurl = new moodle_url('/local/codepatcher/patch.php');
$pachesreporturl = new moodle_url('/local/codepatcher/patches.php');
$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('admin');
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_title(new lang_string('patch:title', 'local_codepatcher'));
$PAGE->set_heading(new lang_string('patch:header', 'local_codepatcher'));

// Initialize the patch manager.
$manager = new patch_manager();

// Check if an ID is provided, indicating whether this is a new or existing code patch.
if (empty($id)) {
    require_capability('local/codepatcher:addpatch', $context);
    $codepatch = new stdClass();
    $codepatch->id = null;
} else {
    require_capability('local/codepatcher:editpatch', $context);
    $codepatch = $manager->get_patch($id);
}

// Set up the form instance with the code patch data.
$filemangeroptions = api::get_patch_filemanager_options();
$formargs = ['patch' => $codepatch];
$mform = new addpatch_form(null, $formargs);
$toform = [];

// Prepare the file manager for handling attachments.
file_prepare_standard_filemanager($codepatch, 'attachments', $filemangeroptions, $context,
        $manager::$component, $manager::$filearea, $codepatch->id);

// Form actions.
if ($mform->is_cancelled()) {
    // If the form is canceled, redirect to the home page.
    redirect(new moodle_url('/'));
} else if ($fromform = $mform->get_data()) {
    // If form data is submitted.
    if (empty($codepatch->id)) {
        // If it's a new code patch then create it.
        $patch = $manager->create_patch($fromform);
    } else {
        // If it's an existing code patch, update it.
        $fromform->id = $codepatch->id;
        $patch = $manager->update_patch($fromform);
    }

    // Redirection based on success or failure.
    if ($patch === false) {
        // Redirect with an error notification in case of failure.
        redirect($pageurl, get_string('notification:failnewpatchsave', 'local_codepatcher'),
                null, notification::NOTIFY_ERROR);
    } else {
        // Redirect with a success notification in case of success.
        redirect($pachesreporturl, get_string('notification:successnewpatchsave', 'local_codepatcher'),
                null, notification::NOTIFY_SUCCESS);
    }
} else {
    // If no form data is submitted, set the form data.
    $mform->set_data($codepatch);
    // Render the page.
    echo $OUTPUT->header();
    // Display the form.
    $mform->display();
    echo $OUTPUT->footer();
}
