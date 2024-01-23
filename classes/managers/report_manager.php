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
 * Report manager class for the local_securitypatcher plugin.
 *
 * @package   local_securitypatcher
 * @copyright 2023 onwards Andrei-Robert Tica <andreastsika@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_securitypatcher\managers;

use local_securitypatcher\api;

/**
 * Report manager class responsible for the reports.
 *
 * @package   local_securitypatcher
 * @copyright 2023 onwards Andrei-Robert Tica <andreastsika@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_manager {

    /**
     * Fetches the css files for the datatable.
     *
     * @return void
     */
    public function load_datatables_css(): void {
        global $PAGE;

        $PAGE->requires->css('/local/securitypatcher/styles/dataTables.bootstrap4.min.css');
        $PAGE->requires->css('/local/securitypatcher/styles/buttons.bootstrap4.min.css');
        $PAGE->requires->css('/local/securitypatcher/styles/responsive.bootstrap4.min.css');
        $PAGE->requires->css('/local/securitypatcher/styles/dataTables.dateTime.min.css');
    }

    /**
     * Get the list of patch reports based on the specified patch ID.
     *
     * @param int $patchid The ID of the patch.
     * @return array An array containing the patch report list.
     */
    public function get_patch_report_list(int $patchid): array {
        global $DB;

        $reports = [];
        $records = $DB->get_records('local_securitypatcher_data', ['patchid' => $patchid], 'id DESC', '*', 0, 20);

        foreach ($records as $record) {
            $data['id'] = $record->id;
            $data['status'] = patch_manager::get_patch_report_status((int) $record->status);
            $data['operation'] = patch_manager::get_operation_name($record->operation);
            $data['timecreated'] = api::get_date($record->timecreated);
            $data['actions'] = '';
            $reports[] = $data;
        }
        return $reports;
    }
}
