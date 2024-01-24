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
 * A javascript module to handle web service calls.
 *
 * @package   local_securitypatcher
 * @copyright 2023 onwards Andrei-Robert Tica <andreastsika@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/ajax', 'core/notification'], function ($, Ajax, Notification) {

    let get_patches = function (args) {

        let request = {
            methodname: 'local_securitypatcher_get_patches',
            args: args
        };

        let promise = Ajax.call([request])[0];

        promise.fail(Notification.exception);

        return promise;
    };

    let delete_patch = function (args) {

        let request = {
            methodname: 'local_securitypatcher_delete_patch',
            args: args
        };

        let promise = Ajax.call([request])[0];

        promise.fail(Notification.exception);

        return promise;
    };

    let apply_patch = function (args) {

        let request = {
            methodname: 'local_securitypatcher_apply_patch',
            args: args
        };

        let promise = Ajax.call([request])[0];

        promise.fail(Notification.exception);

        return promise;
    };

    let restore_patch = function (args) {

        let request = {
            methodname: 'local_securitypatcher_restore_patch',
            args: args
        };

        let promise = Ajax.call([request])[0];

        promise.fail(Notification.exception);

        return promise;
    };

    let get_patch_reports = function (args) {

        let request = {
            methodname: 'local_securitypatcher_get_patch_reports',
            args: args
        };

        let promise = Ajax.call([request])[0];

        promise.fail(Notification.exception);

        return promise;
    };

    let get_patch_info = function (args) {

        let request = {
            methodname: 'local_securitypatcher_get_patch_info',
            args: args
        };

        let promise = Ajax.call([request])[0];

        promise.fail(Notification.exception);

        return promise;
    };

    let delete_patch_report = function (args) {

        let request = {
            methodname: 'local_securitypatcher_delete_patch_report',
            args: args
        };

        let promise = Ajax.call([request])[0];

        promise.fail(Notification.exception);

        return promise;
    };

    let get_patch_report_info = function (args) {

        let request = {
            methodname: 'local_securitypatcher_get_patch_report_info',
            args: args
        };

        let promise = Ajax.call([request])[0];

        promise.fail(Notification.exception);

        return promise;
    };

    return {
        get_patches: get_patches,
        delete_patch: delete_patch,
        apply_patch: apply_patch,
        restore_patch: restore_patch,
        get_patch_reports: get_patch_reports,
        get_patch_info: get_patch_info,
        delete_patch_report: delete_patch_report,
        get_patch_report_info: get_patch_report_info,
    };
});