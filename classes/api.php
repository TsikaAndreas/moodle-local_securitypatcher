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
 * General Api class for the local_securitypatcher plugin.
 *
 * @package   local_securitypatcher
 * @copyright 2023 onwards Andrei-Robert Tica <andreastsika@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_securitypatcher;

use local_securitypatcher\managers\patch_manager;

/**
 * General purpose api class.
 *
 * @package   local_securitypatcher
 * @copyright 2023 onwards Andrei-Robert Tica <andreastsika@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class api {

    /**
     * Get file manager options for handling security patch attachments.
     *
     * @return array An array of options for the file manager.
     */
    public static function get_patch_filemanager_options(): array {
        global $CFG;

        require_once($CFG->libdir . '/filelib.php');

        return [
                'maxfiles' => 1,                                    // Maximum number of files (1 for single file upload).
                'maxbytes' => $CFG->maxbytes,                       // Maximum file size in bytes.
                'areamaxbytes' => FILE_AREA_MAX_BYTES_UNLIMITED,    // Maximum bytes per file area (unlimited).
                'subdirs' => 0,                                     // Allow subdirectories (0 for no subdirectories).
                'accepted_types' => ['.patch'],                      // Accepted file types (e.g., .patch files).
        ];
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
            $data['lastaction'] = self::get_last_action_name($record->status);
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
     * Retrieves the name of the last action performed based on the patch status.
     *
     * @param int $status The status of the patch.
     * @return \lang_string|string The name of the last action performed.
     */
    private static function get_last_action_name(int $status): \lang_string|string {
        return match ($status) {
            patch_manager::PATCH_APPLIED => get_string('apply', 'local_securitypatcher'),
            patch_manager::PATCH_RESTORED => get_string('restore', 'local_securitypatcher'),
            default => '',
        };
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
        $editurl = new \moodle_url('/local/securitypatcher/patch.php', ['id' => $patch->id]);
        $actions .= '<a href="' . $editurl . '" class="edit-patch-action btn btn-secondary"
                        data-patch="' . $patch->id . '"
                        title="'. get_string('report:editaction_title', 'local_securitypatcher') .'">
                        '. get_string('report:editaction', 'local_securitypatcher') .'
                    </a>';

        // View action.
        $actions .= '<button class="view-patch-action btn btn-info" data-patch="' . $patch->id . '"
                       title="'. get_string('report:viewaction_title', 'local_securitypatcher') .'">
                        '. get_string('report:viewaction', 'local_securitypatcher') .'
                    </button>';

        // Apply action.
        $actions .= '<button class="apply-patch-action btn btn-primary" data-patch="' . $patch->id . '"
                       title="'. get_string('report:applyaction_title', 'local_securitypatcher') .'">
                        '. get_string('report:applyaction', 'local_securitypatcher') .'
                    </button>';

        // Restore action.
        $actions .= '<button class="restore-patch-action btn btn-warning" data-patch="' . $patch->id . '"
                       title="'. get_string('report:restoreaction_title', 'local_securitypatcher') .'">
                        '. get_string('report:restoreaction', 'local_securitypatcher') .'
                    </button>';

        // Delete action.
        $actions .= '<button class="delete-patch-action btn btn-danger" data-patch="' . $patch->id . '"
                       title="'. get_string('report:deleteaction_title', 'local_securitypatcher') .'">
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
}
