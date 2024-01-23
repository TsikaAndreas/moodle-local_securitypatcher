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
 * Patches process class for the local_securitypatcher plugin.
 *
 * @package   local_securitypatcher
 * @copyright 2024 onwards Andrei-Robert Tica <andreastsika@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_securitypatcher\datatables\tables;

use local_securitypatcher\api;
use local_securitypatcher\datatables\ssp;
use local_securitypatcher\managers\patch_manager;
use stdClass;

/**
 * Patches process class for the server side of DataTables.
 *
 * @package   local_securitypatcher
 * @copyright 2024 onwards Andrei-Robert Tica <andreastsika@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class patches {

    /** @var array $request An array containing the request data. */
    private array $request;
    /** @var stdClass $request An object containing the result. */
    private stdClass $result;

    /**
     * Constructor for the patches class.
     *
     * @param array $request An array containing the request data.
     */
    public function __construct(array $request) {
        $this->request = $request;
        $this->init();
    }

    /**
     * Initialize the Patches object.
     *
     * This method performs the initialization process for the Patches object.
     * It creates an instance of the 'ssp' class, initializes it with the provided request data,
     * sets the count SQL and main SQL, retrieves the result, and processes the data.
     */
    private function init(): void {
        $ssp = new ssp();
        $ssp->init($this->request);
        $ssp->set_countsql($this->count_sql());
        $ssp->set_mainsql($this->main_sql());
        $ssp->set_wheresql($this->where_sql());
        $ssp->set_column_type_map($this->searchable_column_types_mapping());

        $this->result = $ssp->result();

        $this->process_data();
    }

    /**
     * Generate HTML select options for patch status.
     *
     * @return string The HTML select options for patch status.
     */
    public static function html_status_select_options(): string {
        $options = [
                patch_manager::PATCH_CLEAN => get_string('none', 'local_securitypatcher'),
                patch_manager::PATCH_APPLIED => get_string('apply', 'local_securitypatcher'),
                patch_manager::PATCH_RESTORED => get_string('restore', 'local_securitypatcher'),
        ];
        return \html_writer::select($options, 'status', '', ['' => 'choosedots'], ['class' => 'w-100']);
    }

    /**
     * Get the mapping of searchable column names to their corresponding data types.
     *
     * @return array The searchable column types mapping.
     */
    private function searchable_column_types_mapping(): array {
        return [
                'name' => 'text',
                'status' => 'int',
                'timeapplied' => 'timestamp',
                'timerestored' => 'timestamp',
                'timecreated' => 'timestamp',
                'timemodified' => 'timestamp',
        ];
    }

    /**
     * Process data retrieved from the 'ssp' instance.
     *
     * This method transforms the raw data obtained from the 'ssp' result into a formatted
     * array of patches. It utilizes a 'patch_manager' instance to retrieve additional details
     * and ensures proper formatting of date fields.
     */
    private function process_data(): void {
        $patches = [];
        $manager = new patch_manager();
        foreach ($this->result->data as $item) {
            $data['id'] = $item->id;
            $data['name'] = $item->name;
            $data['lastaction'] = $manager->get_last_operation_name($item->status);
            $data['applied'] = api::get_date($item->timeapplied);
            $data['restored'] = api::get_date($item->timerestored);
            $data['modified'] = api::get_date($item->timemodified);
            $data['created'] = api::get_date($item->timecreated);
            $data['actions'] = $this->parse_actions($item);
            $patches[] = $data;
        }
        $this->result->data = $patches;
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
    public function parse_actions(object $patch): string {
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
     * Returns the result object containing the processed data.
     *
     * @return stdClass The result object containing the processed data.
     */
    public function get_result(): stdClass {
        return $this->result;
    }

    /**
     * Get the count SQL query for pagination.
     *
     * This method returns the SQL query used to count the total number of records.
     *
     * @return string The count SQL query.
     */
    private function count_sql(): string {
        return "SELECT count(*) FROM {local_securitypatcher}";
    }

    /**
     * Get the main SQL query for retrieving data.
     *
     * This method returns the SQL query used to retrieve data from the database.
     *
     * @return string The main SQL query.
     */
    private function main_sql(): string {
        return "SELECT * FROM {local_securitypatcher}";
    }

    /**
     * Get the WHERE clause SQL query.
     *
     * This method returns the WHERE clause for the SQL query.
     * If no conditions are specified, an empty string is returned.
     *
     * @return string The WHERE clause SQL query.
     */
    private function where_sql(): string {
        return '';
    }
}
