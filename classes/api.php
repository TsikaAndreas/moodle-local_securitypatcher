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

/**
 * General purpose api class.
 *
 * @package   local_securitypatcher
 * @copyright 2023 onwards Andrei-Robert Tica <andreastsika@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class api {

    /**
     * Retrieve file manager options specifically designed for handling security patch attachments.
     *
     * @return array An associative array containing options for the file manager.
     *   - 'maxfiles': Maximum number of files (1 for single file upload).
     *   - 'maxbytes': Maximum file size in bytes.
     *   - 'areamaxbytes': Maximum bytes per file area (unlimited).
     *   - 'subdirs': Allow subdirectories (0 for no subdirectories).
     *   - 'accepted_types': Accepted file types (e.g., .patch files).
     */
    public static function get_patch_filemanager_options(): array {
        global $CFG;

        require_once($CFG->libdir . '/filelib.php');

        return [
                'maxfiles' => 1,
                'maxbytes' => $CFG->maxbytes,
                'areamaxbytes' => FILE_AREA_MAX_BYTES_UNLIMITED,
                'subdirs' => 0,
                'accepted_types' => ['.patch'],
        ];
    }

    /**
     * Convert a timestamp to a formatted date and time string.
     *
     * @param int|null $timestamp The timestamp to convert. Use null to indicate no timestamp (returns "-").
     * @return string Returns the formatted date and time string or "-" if no timestamp is provided.
     */
    public static function get_date(?int $timestamp): string {
        if (!$timestamp) {
            // If no timestamp is provided, or it's null.
            return '-';
        }
        // Format the timestamp as "YYYY-MM-DD HH:MM:SS".
        return date('d-m-Y H:i:s', $timestamp);
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
        $PAGE->requires->css('/local/securitypatcher/styles/dataTables.dateTime.min.css');
    }
}
