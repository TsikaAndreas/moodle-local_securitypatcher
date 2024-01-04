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
 * Patch manager class for the local_securitypatcher plugin.
 *
 * @package   local_securitypatcher
 * @copyright 2023 onwards Andrei-Robert Tica <andreastsika@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_securitypatcher\managers;

use context_system;
use local_securitypatcher\api;
use stdClass;
use stored_file;

/**
 * Patch manager class responsible for the management of security patches.
 *
 * @package   local_securitypatcher
 * @copyright 2023 onwards Andrei-Robert Tica <andreastsika@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class patch_manager {

    /**
     * Patch is clean, not applied.
     */
    protected const PATCH_CLEAN = 0;

    /**
     * Patch has been applied.
     */
    protected const PATCH_APPLIED = 1;

    /**
     * Patch has been restored.
     */
    protected const PATCH_RESTORED = 2;

    /**
     * @var int The identifier of the security patch.
     */
    protected int $patchid;

    /**
     * @var string The filearea that the security patches are stored.
     */
    public static string $filearea = 'local_securitypatcher_security_patches';
    /**
     * @var string The component the security files belongs.
     */
    public static string $component = 'local_securitypatcher';

    /**
     * Retrieve a security patch record by its ID.
     *
     * @param int $patchid The ID of the security patch to retrieve.
     *
     * @return stdClass|false Returns the security patch record as an object if found, or false if not found.
     */
    public function get_patch(int $patchid): false|stdClass {
        global $DB;

        return $DB->get_record('local_securitypatcher', ['id' => $patchid]);
    }

    /**
     * Creates a new security patch record in the database and stores the file attachment.
     *
     * @param object $formdata An object containing the form data for the new patch.
     *
     * @return bool Returns true if successful, or false if an error occurred.
     */
    public function create_patch(object $formdata): bool {
        global $DB;

        $context = context_system::instance();

        $currenttime = time();

        // Create a new stdClass object to hold patch data.
        $patch = new stdClass();

        // Set the name of the patch.
        $patch->name = $formdata->name;

        // Initialize other fields with default values.
        $patch->status = self::PATCH_CLEAN;
        $patch->attachments = null;
        $patch->timeapplied = null;
        $patch->timerestored = null;
        $patch->timecreated = $currenttime;
        $patch->timemodified = $currenttime;

        // Insert the new patch record into the database.
        $patchid = $DB->insert_record('local_securitypatcher', $patch);

        // Check if the insertion was successful.
        if (empty($patchid)) {
            return false;
        }

        // Assign the generated ID to the patch object.
        $patch->id = $patchid;

        // Update the attachments.
        $formdata = file_postupdate_standard_filemanager($formdata,'attachments', api::get_patch_filemanager_options(),
                $context, self::$component, self::$filearea, $patch->id);

        // Update the 'attachments' field in the patch object.
        $patch->attachments = $formdata->attachments;

        // Update the patch record in the database and return the result.
        return $DB->update_record('local_securitypatcher', $patch);
    }

    /**
     * Updates an existing security patch record in the database.
     *
     * @param object $formdata An object containing the updated form data for the patch.
     *
     * @return bool|int Returns true if the update was successful, false if an error occurred.
     */
    public function update_patch(object $formdata): bool|int {
        global $DB;

        $context = context_system::instance();

        // Retrieve the existing patch record based on its ID.
        $patch = $this->get_patch($formdata->id);

        // Update the modified fields of the patch.
        $patch->name = $formdata->name;
        $patch->timemodified = time();

        // Update the attachments.
        $formdata = file_postupdate_standard_filemanager($formdata, 'attachments', api::get_patch_filemanager_options(),
                $context, self::$component, self::$filearea, $patch->id);

        // Update the 'attachments' field in the patch object.
        $patch->attachments = $formdata->attachments;

        // Update the patch record in the database and return the result.
        return $DB->update_record('local_securitypatcher', $patch);
    }

    /**
     * Retrieves a stored patch file from the Moodle file storage.
     *
     * @return false|stored_file|null Returns the stored file or false if not found.
     */
    public function get_stored_file(): stored_file|bool|null {
        global $DB;

        $contextid = context_system::instance()->id;

        // Retrieve the patch record from the table.
        $record = $DB->get_record('local_securitypatcher', ['id' => $this->patchid], '*', MUST_EXIST);

        // Get the Moodle file storage.
        $fs = get_file_storage();

        // If the file storage is not available, return false.
        if ($fs === null) {
            return false;
        }

        // Retrieve the file.
        return $fs->get_file(
                $contextid,
                self::$component,
                self::$filearea,
                (int) $record->itemid,
                $record->filepath,
                $record->filename
        );
    }

    /**
     * Apply a patch identified by its ID.
     *
     * @param int $patchid The ID of the patch to be applied.
     * @return bool Returns true if the patch is successfully applied, otherwise false.
     */
    public function apply_patch(int $patchid): bool {
        global $DB;

        if (!$DB->record_exists('local_securitypatcher', ['id' => $patchid])) {
            return false;
        }
        $this->patchid = $patchid;

        $file = $this->get_stored_file();

        if (empty($file)) {
            return false;
        }

        // TODO: Add the apply patch logic.
        return true;
    }

    /**
     * Restore a patch identified by its ID.
     * The security patch code changes will be reverted.
     *
     * @param int $patchid The ID of the patch to be restored.
     * @return bool Returns true if the patch is successfully restored, otherwise false.
     */
    public function restore_patch(int $patchid): bool {
        global $DB;

        if (!$DB->record_exists('local_securitypatcher', ['id' => $patchid])) {
            return false;
        }
        $this->patchid = $patchid;

        $file = $this->get_stored_file();

        if (empty($file)) {
            return false;
        }

        // TODO: Add the restore patch logic.
        return true;
    }
}