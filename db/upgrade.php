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
 * This file keeps track of upgrades to the local_securitypatcher plugin.
 *
 * Sometimes, changes between versions involve alterations to database structures
 * and other major things that may break installations.
 *
 * The upgrade function in this file will attempt to perform all the necessary
 * actions to upgrade your older installation to the current version.
 *
 * If there's something it cannot do itself, it will tell you what you need to do.
 *
 * The commands in here will all be database-neutral, using the methods of
 * database_manager class
 *
 * Please do not forget to use upgrade_set_timeout()
 * before any action that may take longer time to finish.
 *
 * @package   local_securitypatcher
 * @copyright 2024 onwards Andrei-Robert Țîcă <andreastsika@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade the local_securitypatcher plugin
 *
 * @param int $oldversion the version we are upgrading from
 * @param bool result
 */
function xmldb_local_securitypatcher_upgrade(int $oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2023083006) {

        // Define table local_securitypatcher_data to be created.
        $table = new xmldb_table('local_securitypatcher_data');

        // Adding fields to table local_securitypatcher_data.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('patchid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('status', XMLDB_TYPE_INTEGER, '1', null, null, null, null);
        $table->add_field('data', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('operation', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_securitypatcher_data.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('patchid', XMLDB_KEY_FOREIGN, ['patchid'], 'local_securitypatcher', ['id']);

        // Conditionally launch create table for local_securitypatcher_data.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Securitypatcher savepoint reached.
        upgrade_plugin_savepoint(true, 2023083006, 'local', 'securitypatcher');
    }

    if ($oldversion < 2023083008) {

        // Changing type of field status on table local_securitypatcher_data to char.
        $table = new xmldb_table('local_securitypatcher_data');
        $field = new xmldb_field('status', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null, 'patchid');

        // Launch change of type for field status.
        $dbman->change_field_type($table, $field);

        // Define field statuscode to be added to local_securitypatcher_data.
        $field = new xmldb_field('statuscode', XMLDB_TYPE_INTEGER, '2', null, null, null, null, 'status');

        // Conditionally launch add field statuscode.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Securitypatcher savepoint reached.
        upgrade_plugin_savepoint(true, 2023083008, 'local', 'securitypatcher');
    }

    return true;
}
