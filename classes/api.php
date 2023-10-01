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

namespace local_securitypatcher;

use context_system;
use PhpOffice\PhpSpreadsheet\Reader\Ods\PageSettings;
use stdClass;

/**
 * Class api
 *
 * @package   local_securitypatcher
 * @copyright 2023 onwards Andrei-Robert Tica <andreastsika@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class api {

    /**
     * Patch is clean, not applied.
     */
    protected CONST PATCH_CLEAN = 0;

    /**
     * Patch has been applied.
     */
    protected CONST PATCH_APPLIED = 1;

    /**
     * Patch has been restored.
     */
    protected CONST PATCH_RESTORED = 2;

    /**
     * @var string The filearea that the security patches are stored.
     */
    public static string $filearea = 'local_securitypatcher_security_patches';
    /**
     * @var string The component the security files belongs.
     */
    public static string $component = 'local_securitypatcher';

    /**
     * Get file manager options for handling security patch attachments.
     *
     * @return array An array of options for the file manager.
     */
    public static function get_filemanager_options(): array {
        global $CFG;

        require_once($CFG->libdir . '/filelib.php');

        return [
                'maxfiles' => 1,                                    // Maximum number of files (1 for single file upload).
                'maxbytes' => $CFG->maxbytes,                       // Maximum file size in bytes.
                'areamaxbytes' => FILE_AREA_MAX_BYTES_UNLIMITED,    // Maximum bytes per file area (unlimited).
                'subdirs' => 0,                                     // Allow subdirectories (0 for no subdirectories).
                'accepted_types' => ['.diff'],                      // Accepted file types (e.g., .diff files).
        ];
    }

    /**
     * Retrieve a security patch record by its ID.
     *
     * @param int $patchid The ID of the security patch to retrieve.
     *
     * @return stdClass|false Returns the security patch record as an object if found, or false if not found.
     */
    public static function get_patch(int $patchid): false|stdClass {
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
    public static function patch_create(object $formdata): bool {
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
        $formdata = file_postupdate_standard_filemanager($formdata, 'attachments', self::get_filemanager_options(), $context,
                self::$component,
                self::$filearea, $patch->id);

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
    public static function patch_update(object $formdata): bool|int {
        global $DB;

        $context = context_system::instance();

        // Retrieve the existing patch record based on its ID.
        $patch = self::get_patch($formdata->id);

        // Update the modified fields of the patch.
        $patch->name = $formdata->name;
        $patch->timemodified = time();

        // Update the attachments.
        $formdata = file_postupdate_standard_filemanager($formdata, 'attachments', self::get_filemanager_options(), $context,
                self::$component,
                self::$filearea, $patch->id);

        // Update the 'attachments' field in the patch object.
        $patch->attachments = $formdata->attachments;

        // Update the patch record in the database and return the result.
        return $DB->update_record('local_securitypatcher', $patch);
    }

    /**
     * Fetches the css files for the datatable.
     *
     * @return void
     */
    public static function load_datatables_css(): void {
        global $PAGE;

        $PAGE->requires->css('/local/securitypatcher/styles/dataTables.bootstrap4.min.css');
        $PAGE->requires->css('/local/securitypatcher/styles/buttons.bootstrap4.min.css');
        $PAGE->requires->css('/local/securitypatcher/styles/responsive.bootstrap4.min.css');
        $PAGE->requires->css('/local/securitypatcher/styles/report.css');
    }

    /**
     * Fetches the data for the datatable.
     *
     * @return array An array with the report data.
     */
    public static function get_report_data(): array {
        global $DB;

        $patches = [];
        $records = $DB->get_recordset('local_securitypatcher');

        foreach ($records as $record) {
            $data = null;
            $data['id'] = $record->id;
            $data['name'] = $record->name;
            $data['applied'] = self::get_date($record->timeapplied);
            $data['restored'] = self::get_date($record->timerestored);
            $data['modified'] = self::get_date($record->timemodified);
            $data['created'] = self::get_date($record->timecreated);
            $data['actions'] = self::parse_actions($record);
            $patches[] = $data;
        }
        $records->close();
        return $patches;
    }

    /**
     * Parse actions for a given security patch object.
     *
     * This method generates HTML code for various actions for a security patch.
     *
     * @param object $patch The security patch object to generate actions for.
     *
     * @return string HTML code containing the security patch actions.
     */
    public static function parse_actions(object $patch): string {
        $actions = '';

        // Edit action.
        $editurl = new \moodle_url('/local/securitypatcher/add.php', ['id' => $patch->id]);
        $actions .= '<a href="' . $editurl . '" class="edit-patch-action btn btn-secondary"
                        data-patch="' . $patch->id . '">
                        '. get_string('report:editaction', 'local_securitypatcher') .'
                    </a>';

        // Apply action.
        $actions .= '<button class="apply-patch-action btn btn-primary" data-patch="' . $patch->id . '">
                        '. get_string('report:applyaction', 'local_securitypatcher') .'
                    </button>';

        // Restore action.
        $actions .= '<button class="restore-patch-action btn btn-warning" data-patch="' . $patch->id . '">
                        '. get_string('report:restoreaction', 'local_securitypatcher') .'
                    </button>';

        // Delete action.
        $actions .= '<button class="delete-patch-action btn btn-danger" data-patch="' . $patch->id . '">
                        '. get_string('report:deleteaction', 'local_securitypatcher') .'
                    </button>';

        return $actions;
    }

    /**
     * Convert a timestamp to a formatted date and time string.
     *
     * @param int|null $timestamp The timestamp to convert. Use null to indicate no timestamp (returns "-").
     * @return string Returns the formatted date and time string or "-" if no timestamp is provided.
     */
    public static function get_date(?int $timestamp): string {
        if (!$timestamp) {
            // If no timestamp is provided, or it's null, return a dash ("-").
            return '-';
        }
        // Format the timestamp as "YYYY-MM-DD HH:MM:SS".
        return date('Y-m-d H:i:s', $timestamp);
    }

    /**
     * Retrieves a stored patch file from the Moodle file storage.
     *
     * @param int $patchid The unique identifier for the security patch.
     * @return false|\stored_file|null Returns the stored file or false if not found.
     */
    public static function get_stored_patch_file(int $patchid) {
        global $DB;

        $contextid = context_system::instance()->id;

        // Retrieve the patch record from the table.
        $record = $DB->get_record('local_securitypatcher', ['id' => $patchid], '*', MUST_EXIST);

        // Get the Moodle file storage.
        $fs = get_file_storage();

        // If the file storage is not available, return false.
        if ($fs === null) {
            return false;
        }

        // Retrieve the file.
        $file = $fs->get_file(
                $contextid,
                self::$component,
                self::$filearea,
                (int) $record->itemid,
                $record->filepath,
                $record->filename
        );

        return $file;
    }
}
