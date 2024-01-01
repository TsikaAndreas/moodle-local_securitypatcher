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
 * Plugin administration pages are defined here.
 *
 * @package     local_securitypatcher
 * @category    admin
 * @copyright   2023 Andrei-Robert Tica <andreastsika@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $manage = new admin_category('local_securitypatcher', get_string('pluginname', 'local_securitypatcher'));
    $ADMIN->add('server', $manage);

    $settingspage = new admin_settingpage('managelocalsecuritypatcher',
            new lang_string('settings:manage', 'local_securitypatcher'));
    $ADMIN->add('local_securitypatcher', $settingspage);

    // Add security patches page.
    $ADMIN->add('local_securitypatcher', new admin_externalpage('addsecuritypatches',
            get_string('settings:addsecuritypatches', 'local_securitypatcher'),
            new moodle_url('/local/securitypatcher/patch.php')
    ));
    // Add reports page.
    $ADMIN->add('local_securitypatcher', new admin_externalpage('reportsecuritypatches',
            get_string('settings:report', 'local_securitypatcher'),
            new moodle_url('/local/securitypatcher/report.php')
    ));
}
