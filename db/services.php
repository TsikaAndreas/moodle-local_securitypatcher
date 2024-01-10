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
 * Security patcher external functions and service definitions.
 *
 * @package   local_securitypatcher
 * @copyright 2023 onwards Andrei-Robert Tica <andreastsika@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$functions = array(
        'local_securitypatcher_get_patches' => array(
                'classname' => 'local_securitypatcher\external\get_patches',
                'description' => 'Get security patches',
                'capabilities' => 'local/securitypatcher:viewpatch',
                'type' => 'read',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        ),
        'local_securitypatcher_delete_patch' => array(
                'classname' => 'local_securitypatcher\external\delete_patch',
                'description' => 'Delete security patch',
                'capabilities' => 'local/securitypatcher:deletepatch',
                'type' => 'write',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        ),
        'local_securitypatcher_apply_patch' => array(
                'classname' => 'local_securitypatcher\external\apply_patch',
                'description' => 'Apply security patch',
                'capabilities' => 'local/securitypatcher:applypatch',
                'type' => 'write',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        ),
        'local_securitypatcher_restore_patch' => array(
                'classname' => 'local_securitypatcher\external\restore_patch',
                'description' => 'Restore security patch',
                'capabilities' => 'local/securitypatcher:restorepatch',
                'type' => 'write',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        ),
        'local_securitypatcher_get_patch_reports' => array(
                'classname' => 'local_securitypatcher\external\get_patch_reports',
                'description' => 'Get security patch reports',
                'capabilities' => 'local/securitypatcher:viewreports',
                'type' => 'read',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        ),
        'local_securitypatcher_get_patch_info' => array(
                'classname' => 'local_securitypatcher\external\get_patch_info',
                'description' => 'Get security patch info',
                'capabilities' => 'local/securitypatcher:viewpatch',
                'type' => 'read',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        ),
);
