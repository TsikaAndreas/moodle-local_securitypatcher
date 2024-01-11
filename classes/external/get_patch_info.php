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
 * External function get_patch_info for local_securitypatcher.
 *
 * @package   local_securitypatcher
 * @copyright 2023 onwards Andrei-Robert Tica <andreastsika@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_securitypatcher\external;

use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use local_securitypatcher\managers\patch_manager;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php');

/**
 * External class get_patch_info for local_securitypatcher.
 *
 * @package   local_securitypatcher
 * @copyright 2023 onwards Andrei-Robert Tica <andreastsika@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_patch_info extends external_api {

    /**
     * Parameters for execute
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
                'patchid' => new external_value(PARAM_INT, 'id of security patch'),
        ]);
    }

    /**
     * Returns the information of security patch.
     *
     * @param int $patchid
     * @return array
     */
    public static function execute(int $patchid): array {
        // Validate all the parameters.
        $params = self::validate_parameters(self::execute_parameters(), [
                'patchid' => $patchid,
        ]);

        $context = \context_system::instance();
        require_capability('local/securitypatcher:viewpatch', $context);

        $manager = new patch_manager();
        $result = $manager->get_patch_info($params['patchid']);

        // Return a value as described in the returns function.
        return array('result' => $result);
    }

    /**
     * Return for execute
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
                'result' => new external_single_structure([
                    'name' => new external_value(PARAM_TEXT, 'Security patch file name.'),
                    'content' => new external_value(PARAM_RAW, 'Security patch file content.')
                ], 'Result object with the security patch info.'),
        ]);
    }
}