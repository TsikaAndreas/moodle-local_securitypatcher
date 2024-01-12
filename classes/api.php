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
}
