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

namespace local_securitypatcher\forms;

use local_securitypatcher\api;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");
require_once($CFG->dirroot . '/repository/lib.php');

/**
 * File description for the local_securitypatcher plugin.
 *
 * @package   local_securitypatcher
 * @copyright 2023 onwards Andrei-Robert Tica <andreastsika@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class addpatch_form extends \moodleform {

    /**
     * The form definition.
     */
    public function definition() {
        $mform = $this->_form;
        $filemanageroptions = api::get_patch_filemanager_options();

        // Hidden element for the edit.
        $mform->addElement('hidden', 'id');

        // Name.
        $mform->addElement('text', 'name', get_string('patch:name', 'local_securitypatcher'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('err:namerequired', 'local_securitypatcher'), 'required');
        $mform->addHelpButton('name', 'patch:name', 'local_securitypatcher');

        // Attachment.
        $mform->addElement('filemanager', 'attachments_filemanager', get_string('patch:file', 'local_securitypatcher'), null,
                $filemanageroptions);
        $mform->addRule('attachments_filemanager', get_string('err:filerequired', 'local_securitypatcher'), 'required');

        // Save and cancel.
        $this->add_action_buttons(true, get_string('patch:save', 'local_securitypatcher'));
    }

    /**
     * Validate this form.
     *
     * @param array $data submitted data
     * @param array $files not used
     * @return array errors
     * @throws \dml_exception | \coding_exception
     */
    public function validation($data, $files): array {
        global $DB;
        $patch = $this->_customdata['patch'];
        $errors = [];

        if (empty($patch->id)) {
            // Check if security patch with the same name exists.
            $record = $DB->record_exists('local_securitypatcher', ['name' => $data['name']]);
            if ($record) {
                $errors['name'] = get_string('err:existingname', 'securitypatcher', s($data['name']));
            }
        }

        return $errors;
    }
}
