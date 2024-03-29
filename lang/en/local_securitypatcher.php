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
 * @copyright   2023 Andrei-Robert Țîcă <andreastsika@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// General.
$string['pluginname'] = 'Security Patcher';
$string['loading'] = 'Loading...';
$string['confirm_title'] = 'Confirm';
$string['confirm_cancel'] = 'Cancel';
$string['apply'] = 'Apply';
$string['restore'] = 'Restore';
$string['none'] = 'None';
$string['new_patch_button'] = 'Add new security patch';
$string['operation_success'] = 'Successful';
$string['operation_error'] = 'Error occurred';

// Settings.
$string['settings:manage'] = 'Manage Security Patcher';
$string['settings:manage:git'] = 'Path to Git';
$string['settings:manage:git_desc'] = 'Path to Git. On Linux it is something like /usr/bin/git.
On Windows it is something like C:\Program Files (x86)\Git\bin\git.exe. On Mac it is something like /opt/local/bin/git.
To be able to execute the security patching, you must have installed the git executable and point to it here.';
$string['settings:addsecuritypatches'] = 'Add Security Patches';
$string['settings:patchesreport'] = 'Security Patches List';

// Capabilities.
$string['securitypatcher:viewpatch'] = 'View security patch information';
$string['securitypatcher:addpatch'] = 'Add security patches';
$string['securitypatcher:editpatch'] = 'Edit security patches';
$string['securitypatcher:viewreports'] = 'View the security patch reports';
$string['securitypatcher:config'] = 'Change the security patcher configuration';
$string['securitypatcher:deletepatch'] = 'Delete security patch and associated reports';
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

// Patches List.
$string['patches:title'] = 'Security Patch List';
$string['patches:heading'] = 'Security Patch List';
$string['patches:applyaction'] = 'Apply';
$string['patches:deleteaction'] = 'Delete';
$string['patches:restoreaction'] = 'Restore';
$string['patches:editaction'] = 'Edit';
$string['patches:viewaction'] = 'View';
$string['patches:viewreportaction'] = 'Report';
$string['patches:patch_confirmdelete'] = 'You are about to delete a security patch and reports, are you sure?';
$string['patches:patch_confirmdeletebtn'] = 'Delete';
$string['patches:patch_confirmapply'] = 'You are about to apply a security patch, are you sure?';
$string['patches:patch_confirmapplybtn'] = 'Apply';
$string['patches:patch_confirmrestore'] = 'You are about to restore a security patch, are you sure?';
$string['patches:patch_confirmrestorebtn'] = 'Restore';

// Patches Report List.
$string['patchesreport:deleteaction'] = 'Delete';
$string['patchesreport:viewaction'] = 'View';
$string['patchesreport:report_confirmdelete'] = 'You are about to delete a security patch report, are you sure?';
$string['patchesreport:report_confirmdeletebtn'] = 'Delete';

// Patches Datatables.
$string['datatable:patches:id'] = 'ID';
$string['datatable:patches:name'] = 'Patch Name';
$string['datatable:patches:lastaction'] = 'Last Action';
$string['datatable:patches:filename'] = 'File Name';
$string['datatable:patches:created'] = 'Created At';
$string['datatable:patches:modified'] = 'Modified At';
$string['datatable:patches:applied'] = 'Applied At';
$string['datatable:patches:restored'] = 'Restored At';
$string['datatable:patches:actions'] = 'Actions';

// Patches Report Datatables.
$string['datatable:patchesreport'] = 'Reports for: {$a}';
$string['datatable:patchesreport:id'] = 'ID';
$string['datatable:patchesreport:timecreated'] = 'Created At';
$string['datatable:patchesreport:status'] = 'Status';
$string['datatable:patchesreport:operation'] = 'Operation';
$string['datatable:patchesreport:actions'] = 'Actions';

// Exceptions.
$string['exception:patchfilenotfound'] = 'The security patch file of \'{$a}\' was not found!';
$string['exception:operationnotfound'] = 'No operation action was found!';
$string['exception:invalidoperation'] = 'Invalid operation action \'{$a}\' was provided!';
$string['exception:gitpathnotfound'] = 'Git command path was not found!';
