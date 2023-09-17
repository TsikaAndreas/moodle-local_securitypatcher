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
 * Plugin strings are defined here.
 *
 * @package     local_securitypatcher
 * @category    string
 * @copyright   2023 Andrei-Robert Tica <andreastsika@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// General.
$string['pluginname'] = 'Security Patcher';

// Settings.
$string['settings:manage'] = 'Manage Security Patcher';
$string['settings:addsecuritypatches'] = 'Add Security Patches';

// Capabilities.

// Form.
$string['add:title'] = 'Add Security Patch';
$string['add:header'] = 'Add Security Patch';
$string['add:name'] = 'Security Patch Name';
$string['add:name_help'] = 'Add a short identifier for the security patch file.';
$string['add:file'] = 'Upload Security Patch File:';
$string['add:save'] = 'Save';

// Form validation.
$string['err:namerequired'] = 'The name field is required.';
$string['err:filerequired'] = 'The file is required.';
$string['err:existingname'] = 'The provided security patch name ({$a}) already exists.';

// Notifications.
$string['notification:successnewpatchsave'] = 'The security patch was successfully saved.';
$string['notification:failnewpatchsave'] = 'The security patch could not be saved.';

// Privacy Provider.
$string['privacy:metadata'] = 'The Security Patcher plugin does not store any personal data.';

// Reports.
$string['report:title'] = 'Security Patch Reports';
$string['report:heading'] = 'Security Patch Reports';

// Datatables.
$string['datatable:id'] = 'ID';
$string['datatable:name'] = 'Patch Name';
$string['datatable:filename'] = 'File Name';
$string['datatable:created'] = 'Created At';
$string['datatable:modified'] = 'Modified At';
$string['datatable:applied'] = 'Applied At';
$string['datatable:actions'] = 'Actions';
