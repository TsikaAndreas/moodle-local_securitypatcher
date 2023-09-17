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
     * @var string The filearea that the security patches are stored.
     */
    protected static string $filearea = 'local_securitypatcher_security_patches';
    /**
     * @var string The component the security files belongs.
     */
    protected static string $component = 'local_securitypatcher';

    /**
     * Saves a patch file to the Moodle file storage and records its metadata in the database.
     *
     * @param object $data An object with the submitted form data about the patch file.
     * @return bool|int Returns the unique identifier of the saved patch record or false if not saved.
     */
    public static function patch_file_save(object $data) {
        global $DB;

        $contextid = context_system::instance()->id;

        // Get all files in the draft area.
        $draftfile = file_get_all_files_in_draftarea($data->attachments);

        // Save files from the draft area to the specified component and file area.
        $patchsaved = file_save_draft_area_files($data->attachments, $contextid, self::$component, self::$filearea, 0);

        // If files were saved successfully.
        if ($patchsaved === null) {
            $currenttime = time();

            // Create a new record for the saved patch file.
            $newrecord = new stdClass();
            $newrecord->name = $data->name;
            $newrecord->itemid = 0;
            $newrecord->filename = $draftfile[0]->filename;
            $newrecord->filepath = $draftfile[0]->filepath;
            $newrecord->applied = 0;
            $newrecord->timecreated = $currenttime;
            $newrecord->timemodified = $currenttime;

            // Insert the new record into the table and return the unique identifier.
            return $DB->insert_record('local_securitypatcher', $newrecord);
        }

        // Return false if the patch was not saved.
        return false;
    }

    /**
     * Fetches the css files for the datatable.
     *
     * @return void
     */
    public static function load_datatables_css() {
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
            $data['filename'] = $record->filename;
            $data['applied'] = self::get_date($record->timeapplied);
            $data['created'] = self::get_date($record->timecreated);
            $data['modified'] = self::get_date($record->timemodified);
            $patches[] = $data;
        }
        $records->close();
        return $patches;
    }

    /**
     * Transforms unix timestamps to date format.
     *
     * @param int|null $timestamp
     * @return string
     */
    public static function get_date(?int $timestamp): string {
        if (!$timestamp) {
            return '-';
        }
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
                (int)$record->itemid,
                $record->filepath,
                $record->filename
        );

        return $file;
    }
}
