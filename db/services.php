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
 * Code patcher external functions and service definitions.
 *
 * @package   local_codepatcher
 * @copyright 2023 onwards Andrei-Robert Țîcă <andreastsika@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$functions = [
        'local_codepatcher_get_patches' => [
                'classname' => 'local_codepatcher\external\get_patches',
                'description' => 'Get code patches',
                'capabilities' => 'local/codepatcher:viewpatch',
                'type' => 'read',
                'ajax' => true,
                'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
        ],
        'local_codepatcher_delete_patch' => [
                'classname' => 'local_codepatcher\external\delete_patch',
                'description' => 'Delete code patch',
                'capabilities' => 'local/codepatcher:deletepatch',
                'type' => 'write',
                'ajax' => true,
                'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
        ],
        'local_codepatcher_apply_patch' => [
                'classname' => 'local_codepatcher\external\apply_patch',
                'description' => 'Apply code patch',
                'capabilities' => 'local/codepatcher:applypatch',
                'type' => 'write',
                'ajax' => true,
                'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
        ],
        'local_codepatcher_restore_patch' => [
                'classname' => 'local_codepatcher\external\restore_patch',
                'description' => 'Restore code patch',
                'capabilities' => 'local/codepatcher:restorepatch',
                'type' => 'write',
                'ajax' => true,
                'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
        ],
        'local_codepatcher_get_patch_reports' => [
                'classname' => 'local_codepatcher\external\get_patch_reports',
                'description' => 'Get code patch reports',
                'capabilities' => 'local/codepatcher:viewreports',
                'type' => 'read',
                'ajax' => true,
                'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
        ],
        'local_codepatcher_get_patch_info' => [
                'classname' => 'local_codepatcher\external\get_patch_info',
                'description' => 'Get code patch info',
                'capabilities' => 'local/codepatcher:viewpatch',
                'type' => 'read',
                'ajax' => true,
                'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
        ],
        'local_codepatcher_delete_patch_report' => [
                'classname' => 'local_codepatcher\external\delete_patch_report',
                'description' => 'Delete code patch report',
                'capabilities' => 'local/codepatcher:deletepatchreport',
                'type' => 'write',
                'ajax' => true,
                'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
        ],
        'local_codepatcher_get_patch_report_info' => [
                'classname' => 'local_codepatcher\external\get_patch_report_info',
                'description' => 'Get code patch report_info',
                'capabilities' => 'local/codepatcher:viewpatch',
                'type' => 'read',
                'ajax' => true,
                'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
        ],
];
