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
 * External function get_patches for local_codepatcher.
 *
 * @package   local_codepatcher
 * @copyright 2023 onwards Andrei-Robert Țîcă <andreastsika@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_codepatcher\external;

use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use local_codepatcher\datatables\tables\patches;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php');

/**
 * External class get_patches for local_codepatcher.
 *
 * @package   local_codepatcher
 * @copyright 2023 onwards Andrei-Robert Țîcă <andreastsika@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_patches extends external_api {

    /**
     * Returns the list of code patches.
     *
     * @param string $data
     * @return object
     */
    public static function execute(string $data): object {
        global $PAGE;
        // Validate all the parameters.
        $params = self::validate_parameters(self::execute_parameters(), [
                'data' => $data,
        ]);

        $context = \context_system::instance();
        require_capability('local/codepatcher:viewpatch', $context);
        $PAGE->set_context($context);

        $request = json_decode($params['data'], true, 512, JSON_THROW_ON_ERROR);
        $manager = new patches($request);

        // Return value as described in the returns function.
        return $manager->get_result();
    }

    /**
     * Parameters for execute
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
                'data' => new external_value(PARAM_RAW, 'the datatable data'),
        ]);
    }

    /**
     * Return for execute
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
                'draw' => new external_value(PARAM_INT, 'datatable draw number'),
                'recordsTotal' => new external_value(PARAM_INT, 'datatable total records number'),
                'recordsFiltered' => new external_value(PARAM_INT, 'datatable filtered records number'),
                'data' => new external_multiple_structure(
                        new external_single_structure([
                                'id' => new external_value(PARAM_INT, 'patch identifier'),
                                'name' => new external_value(PARAM_TEXT, 'patch name'),
                                'lastaction' => new external_value(PARAM_TEXT, 'patch last operation'),
                                'applied' => new external_value(PARAM_TEXT, 'patch apply time'),
                                'restored' => new external_value(PARAM_TEXT, 'patch restoration time'),
                                'modified' => new external_value(PARAM_TEXT, 'patch modification time'),
                                'created' => new external_value(PARAM_TEXT, 'patch creation time'),
                                'actions' => new external_value(PARAM_RAW, 'patch actions'),
                        ])
                        , 'Result with list of code patches'),
        ]);
    }
}
