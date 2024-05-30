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
 * External function delete_patch_report for local_codepatcher.
 *
 * @package   local_codepatcher
 * @copyright 2023 onwards Andrei-Robert Țîcă <andreastsika@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_codepatcher\external;

use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php');

/**
 * External class delete_patch_report for local_codepatcher.
 *
 * @package   local_codepatcher
 * @copyright 2023 onwards Andrei-Robert Țîcă <andreastsika@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class delete_patch_report extends external_api {

    /**
     * Parameters for execute
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
                'reportid' => new external_value(PARAM_INT, 'id of code patch report'),
        ]);
    }

    /**
     * Delete a code patch report.
     *
     * @param int $reportid
     * @return array
     */
    public static function execute(int $reportid): array {
        global $DB;

        // Validate all the parameters.
        $params = self::validate_parameters(self::execute_parameters(), [
                'reportid' => $reportid,
        ]);

        $context = \context_system::instance();
        require_capability('local/codepatcher:deletepatchreport', $context);

        // Perform the delete action.
        $deleted = false;
        if ($DB->record_exists('local_codepatcher_data', ['id' => $params['reportid']])) {
            $DB->delete_records('local_codepatcher_data', ['id' => $params['reportid']]);
            $deleted = true;
        }

        // Return a value as described in the returns function.
        return ['result' => $deleted];
    }

    /**
     * Return for execute
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
                'result' => new external_value(PARAM_BOOL, 'result of the delete action'),
        ]);
    }
}
