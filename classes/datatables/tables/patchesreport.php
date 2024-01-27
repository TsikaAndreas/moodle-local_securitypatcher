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
 * Patch report process class for the local_securitypatcher plugin.
 *
 * @package   local_securitypatcher
 * @copyright 2024 onwards Andrei-Robert Tica <andreastsika@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_securitypatcher\datatables\tables;

use html_writer;
use local_securitypatcher\api;
use local_securitypatcher\datatables\ssp;
use local_securitypatcher\managers\patch_manager;
use stdClass;

/**
 * Patch report process class for the server side of DataTables.
 *
 * @package   local_securitypatcher
 * @copyright 2024 onwards Andrei-Robert Tica <andreastsika@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class patchesreport {

    /** @var array $request An array containing the request data. */
    private array $request;
    /** @var stdClass $request An object containing the result. */
    private stdClass $result;
    /** @var int $patchid An int containing the patch identifier. */
    private int $patchid;

    /**
     * Constructor for the patchesreport class.
     *
     * @param array $request An array containing the request data.
     * @param int $patchid An int containing the patch identifier.
     */
    public function __construct(array $request, int $patchid) {
        $this->request = $request;
        $this->patchid = $patchid;
        $this->init();
    }

    /**
     * Initialize the Patches report object.
     *
     * This method performs the initialization process for the Patchesreport object.
     * It creates an instance of the 'ssp' class, initializes it with the provided request data,
     * sets the count SQL and main SQL, retrieves the result, and processes the data.
     */
    private function init(): void {
        $ssp = new ssp();
        $ssp->init($this->request);
        $ssp->set_countsql($this->count_sql());
        $ssp->set_mainsql($this->main_sql());
        $ssp->set_wheresql($this->where_sql());
        $ssp->set_whereparams($this->where_params());
        $ssp->set_column_type_map($this->searchable_column_types_mapping());

        $this->result = $ssp->result();

        $this->process_data();
    }

    /**
     * Get the mapping of searchable column names to their corresponding data types.
     *
     * @return array The searchable column types mapping.
     */
    private function searchable_column_types_mapping(): array {
        return [
                'timecreated' => 'timestamp',
                'status' => 'text',
                'operation' => 'text',
        ];
    }

    /**
     * Generate HTML select options for patch report status.
     *
     * @return string The HTML select options for patch report status.
     */
    public static function html_status_select_options(): string {
        $options = [
                patch_manager::PATCH_REPORT_SUCCESS => get_string('operation_success', 'local_securitypatcher'),
                patch_manager::PATCH_REPORT_ERROR => get_string('operation_error', 'local_securitypatcher'),
        ];
        return html_writer::select($options, 'status', '', ['' => 'choosedots'], ['class' => 'w-100']);
    }

    /**
     * Generate HTML select options for patch report opearation.
     *
     * @return string The HTML select options for patch report operation.
     */
    public static function html_operation_select_options(): string {
        $options = [
                'apply' => get_string('apply', 'local_securitypatcher'),
                'restore' => get_string('restore', 'local_securitypatcher'),
        ];
        return html_writer::select($options, 'operation', '', ['' => 'choosedots'], ['class' => 'w-100']);
    }

    /**
     * Process data retrieved from the 'ssp' instance.
     *
     * This method transforms the raw data obtained from the 'ssp' result into a formatted
     * array of patches. It utilizes a 'patch_manager' instance to retrieve additional details
     * and ensures proper formatting of date fields.
     */
    private function process_data(): void {
        $reports = [];
        foreach ($this->result->data as $item) {
            $data['id'] = $item->id;
            $data['status'] = patch_manager::get_patch_report_status($item->status);
            $data['operation'] = patch_manager::get_operation_name($item->operation);
            $data['timecreated'] = api::get_date($item->timecreated);
            $data['actions'] = $this->parse_actions($item);
            $reports[] = $data;
        }
        $this->result->data = $reports;
    }

    /**
     * Parse actions for a given security patch report object.
     *
     * This method generates HTML code for various actions for a security patch report.
     *
     * @param object $report The security patch report object to generate actions for.
     *
     * @return string HTML code containing the security patch report actions.
     */
    public function parse_actions(object $report): string {
        global $OUTPUT;

        $actions = html_writer::start_div('patch-report-actions-wrapper');

        // View action.
        $actions .= html_writer::tag(
                'button',
                $OUTPUT->pix_icon('t/search', get_string('patchesreport:viewaction', 'local_securitypatcher'),
                        'local_securitypatcher'),
                [
                        'class' => 'view-report-action align-self-center border-0 bg-transparent',
                        'data-report' => $report->id,
                ]
        );

        // Delete action.
        $actions .= html_writer::tag(
                'button',
                $OUTPUT->pix_icon('t/trash', get_string('patchesreport:deleteaction', 'local_securitypatcher'),
                        'local_securitypatcher'),
                [
                        'class' => 'delete-report-action align-self-center border-0 bg-transparent',
                        'data-report' => $report->id,
                ]
        );

        $actions .= html_writer::end_div();
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
        return "SELECT count(*) FROM {local_securitypatcher_data}";
    }

    /**
     * Get the main SQL query for retrieving data.
     *
     * This method returns the SQL query used to retrieve data from the database.
     *
     * @return string The main SQL query.
     */
    private function main_sql(): string {
        return "SELECT * FROM {local_securitypatcher_data}";
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
        return 'patchid = ?';
    }

    /**
     * Get the WHERE clause parameters for the SQL query.
     *
     * This method returns the WHERE clause parameters for the SQL query.
     * If no conditions are specified, an empty array is returned.
     *
     * @return array The WHERE clause parameters for the SQL query.
     */
    private function where_params(): array {
        return [$this->patchid];
    }
}
