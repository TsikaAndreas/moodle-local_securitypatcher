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
 * @package     local_codepatcher
 * @category    admin
 * @copyright   2023 Andrei-Robert Țîcă <andreastsika@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $manage = new admin_category('local_codepatcher', get_string('pluginname', 'local_codepatcher'));
    $ADMIN->add('server', $manage);

    // Add manage settings page.
    $settingspage = new admin_settingpage('managelocalcodepatcher',
            new lang_string('settings:manage', 'local_codepatcher'),
            'local/codepatcher:config');

    if ($ADMIN->fulltree) {
        // Git command.
        $settingspage->add(new admin_setting_configexecutable(
                'local_codepatcher/git',
                new lang_string('settings:manage:git', 'local_codepatcher'),
                new lang_string('settings:manage:git_desc', 'local_codepatcher'),
                ''
        ));
    }

    $ADMIN->add('local_codepatcher', $settingspage);

    // Add code patches page.
    $ADMIN->add('local_codepatcher', new admin_externalpage('addcodepatches',
            get_string('settings:addcodepatches', 'local_codepatcher'),
            new moodle_url('/local/codepatcher/patch.php'),
            'local/codepatcher:addpatch'
    ));
    // Add patches page.
    $ADMIN->add('local_codepatcher', new admin_externalpage('reportcodepatches',
            get_string('settings:patchesreport', 'local_codepatcher'),
            new moodle_url('/local/codepatcher/patches.php'),
            'local/codepatcher:viewreports'
    ));
}
