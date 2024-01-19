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
    }

    /**
     * Fetches the security patches list data for the datatable.
     *
     * @return array An array with the data.
     */
    public function get_patches_list(): array {
        global $DB;

        $patches = [];
        $records = $DB->get_recordset('local_securitypatcher', null, 'timecreated DESC');

        foreach ($records as $record) {
            $data['id'] = $record->id;
            $data['name'] = $record->name;
            $data['lastaction'] = (new patch_manager())->get_last_operation_name($record->status);
            $data['applied'] = $this->get_date($record->timeapplied);
            $data['restored'] = $this->get_date($record->timerestored);
            $data['modified'] = $this->get_date($record->timemodified);
            $data['created'] = $this->get_date($record->timecreated);
            $data['actions'] = $this->parse_patches_actions($record);
            $patches[] = $data;
        }
        $records->close();
        return $patches;
    }

    /**
     * Convert a timestamp to a formatted date and time string.
     *
     * @param int|null $timestamp The timestamp to convert. Use null to indicate no timestamp (returns "-").
     * @return string Returns the formatted date and time string or "-" if no timestamp is provided.
     */
    public function get_date(?int $timestamp): string {
        if (!$timestamp) {
            // If no timestamp is provided, or it's null.
            return '';
        }
        // Format the timestamp as "YYYY-MM-DD HH:MM:SS".
        return date('Y-m-d H:i:s', $timestamp);
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
    public function parse_patches_actions(object $patch): string {
        global $OUTPUT;

        $actions = \html_writer::start_div('patch-actions-wrapper');

        // Edit action.
        $actions .= \html_writer::link(
                new \moodle_url('/local/securitypatcher/patch.php', ['id' => $patch->id]),
                $OUTPUT->pix_icon('t/edit', get_string('patches:editaction', 'local_securitypatcher'),
                        'local_securitypatcher'),
                [
                        'data-patch' => $patch->id,
                        'class' => 'edit-patch-action align-self-center border-0 bg-transparent'
                ]
        );

        // View action.
        $actions .= \html_writer::tag(
                'button',
                $OUTPUT->pix_icon('t/search', get_string('patches:viewaction', 'local_securitypatcher'),
                        'local_securitypatcher'),
                [
                        'class' => 'view-patch-action align-self-center border-0 bg-transparent',
                        'data-patch' => $patch->id,
                ]
        );

        // Reports action.
        $actions .= \html_writer::tag(
                'button',
                $OUTPUT->pix_icon('t/report', get_string('patches:viewreportaction', 'local_securitypatcher'),
                        'local_securitypatcher'),
                [
                        'class' => 'report-patch-action align-self-center border-0 bg-transparent',
                        'data-patch' => $patch->id,
                ]
        );

        // Apply action.
        $actions .= \html_writer::tag(
                'button',
                $OUTPUT->pix_icon('t/approve', get_string('patches:applyaction', 'local_securitypatcher'),
                        'local_securitypatcher'),
                [
                        'class' => 'apply-patch-action align-self-center border-0 bg-transparent',
                        'data-patch' => $patch->id,
                ]
        );

        // Restore action.
        $actions .= \html_writer::tag(
                'button',
                $OUTPUT->pix_icon('t/refresh', get_string('patches:restoreaction', 'local_securitypatcher'),
                        'local_securitypatcher'),
                [
                        'class' => 'restore-patch-action align-self-center border-0 bg-transparent',
                        'data-patch' => $patch->id,
                ]
        );

        // Delete action.
        $actions .= \html_writer::tag(
                'button',
                $OUTPUT->pix_icon('t/trash', get_string('patches:deleteaction', 'local_securitypatcher'),
                        'local_securitypatcher'),
                [
                        'class' => 'delete-patch-action align-self-center border-0 bg-transparent',
                        'data-patch' => $patch->id,
                ]
        );

        $actions .= \html_writer::end_div();
        return $actions;
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
            $data['timecreated'] = $this->get_date($record->timecreated);
            $data['actions'] = '';
            $reports[] = $data;
        }
        return $reports;
    }
}
