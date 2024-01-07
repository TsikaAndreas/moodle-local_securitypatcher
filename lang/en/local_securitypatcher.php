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
$string['loading'] = 'Loading...';
$string['confirm_title'] = 'Confirm';
$string['confirm_cancel'] = 'Cancel';

// Settings.
$string['settings:manage'] = 'Manage Security Patcher';
$string['settings:manage:git'] = 'Path to Git';
$string['settings:manage:git_desc'] = 'Path to Git. On Linux it is something like /usr/bin/git.
On Windows it is something like C:\Program Files (x86)\Git\bin\git.exe. On Mac it is something like /opt/local/bin/git.
To be able to execute the security patching, you must have installed the git executable and point to it here.';
$string['settings:addsecuritypatches'] = 'Add Security Patches';
$string['settings:report'] = 'Security Patches Report';

// Capabilities.
$string['securitypatcher:viewpatch'] = 'View security patch information';
$string['securitypatcher:addpatch'] = 'Add security patches';
$string['securitypatcher:editpatch'] = 'Edit security patches';
$string['securitypatcher:viewreports'] = 'View the security patch reports';
$string['securitypatcher:config'] = 'Change the security patcher configuration';
$string['securitypatcher:deletepatch'] = 'Delete security patch';
$string['securitypatcher:restorepatch'] = 'Restore security patch';
$string['securitypatcher:applypatch'] = 'Apply security patch';

// Form.
$string['patch:title'] = 'Add Security Patch';
$string['patch:header'] = 'Add Security Patch';
$string['patch:name'] = 'Security Patch Name';
$string['patch:name_help'] = 'Add a short identifier for the security patch file.';
$string['patch:file'] = 'Upload Security Patch File:';
$string['patch:save'] = 'Save';

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
$string['report:applyaction'] = 'Apply';
$string['report:deleteaction'] = 'Delete';
$string['report:restoreaction'] = 'Restore';
$string['report:editaction'] = 'Edit';
$string['report:viewreportaction'] = 'View Report';
$string['report:patch_confirmdelete'] = 'You are about to delete a security patch, are you sure?';
$string['report:patch_confirmdeletebtn'] = 'Delete';
$string['report:patch_confirmapply'] = 'You are about to apply a security patch, are you sure?';
$string['report:patch_confirmapplybtn'] = 'Apply';
$string['report:patch_confirmrestore'] = 'You are about to restore a security patch, are you sure?';
$string['report:patch_confirmrestorebtn'] = 'Restore';

// Datatables.
$string['datatable:id'] = 'ID';
$string['datatable:name'] = 'Patch Name';
$string['datatable:filename'] = 'File Name';
$string['datatable:created'] = 'Created At';
$string['datatable:modified'] = 'Modified At';
$string['datatable:applied'] = 'Applied At';
$string['datatable:restored'] = 'Restored At';
$string['datatable:actions'] = 'Actions';
