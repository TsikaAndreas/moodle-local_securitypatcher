<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin capabilities are defined here.
 *
 * @package   local_securitypatcher
 * @category  access
 * @copyright 2023 onwards Andrei-Robert Tica <andreastsika@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = [

        'local/securitypatcher:viewpatch' => [
                'captype' => 'view',
                'contextlevel' => CONTEXT_SYSTEM,
                'archetypes' => [
                ],
        ],

        'local/securitypatcher:addpatch' => [
                'riskbitmask' => RISK_SPAM | RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_SYSTEM,
                'archetypes' => [
                ],
        ],

        'local/securitypatcher:editpatch' => [
                'riskbitmask' => RISK_SPAM | RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_SYSTEM,
                'archetypes' => [
                ],
        ],

        'local/securitypatcher:viewreports' => [
                'captype' => 'view',
                'contextlevel' => CONTEXT_SYSTEM,
                'archetypes' => [
                ],
        ],

        'local/securitypatcher:config' => [
                'riskbitmask' => RISK_CONFIG,
                'captype' => 'write',
                'contextlevel' => CONTEXT_SYSTEM,
                'archetypes' => [
                ],
        ],

        'local/securitypatcher:deletepatch' => [
                'captype' => 'write',
                'contextlevel' => CONTEXT_SYSTEM,
                'archetypes' => [
                ],
        ],

        'local/securitypatcher:restorepatch' => [
                'captype' => 'write',
                'contextlevel' => CONTEXT_SYSTEM,
                'archetypes' => [
                ],
        ],

        'local/securitypatcher:applypatch' => [
                'captype' => 'write',
                'contextlevel' => CONTEXT_SYSTEM,
                'archetypes' => [
                ],
        ],
        'local/securitypatcher:deletepatchreport' => [
                'captype' => 'write',
                'contextlevel' => CONTEXT_SYSTEM,
                'archetypes' => [
                ],
        ],
];
