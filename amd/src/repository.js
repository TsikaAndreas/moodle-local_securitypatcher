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

    var get_reports = function (args) {

        var request = {
            methodname: 'local_securitypatcher_get_reports',
            args: args
        };

        var promise = Ajax.call([request])[0];

        promise.fail(Notification.exception);

        return promise;
    };

    var delete_patch = function (args) {

        var request = {
            methodname: 'local_securitypatcher_delete_patch',
            args: args
        };

        var promise = Ajax.call([request])[0];

        promise.fail(Notification.exception);

        return promise;
    };

    var apply_patch = function (args) {

        var request = {
            methodname: 'local_securitypatcher_apply_patch',
            args: args
        };

        var promise = Ajax.call([request])[0];

        promise.fail(Notification.exception);

        return promise;
    };

    var restore_patch = function (args) {

        var request = {
            methodname: 'local_securitypatcher_restore_patch',
            args: args
        };

        var promise = Ajax.call([request])[0];

        promise.fail(Notification.exception);

        return promise;
    };

    return {
        get_reports: get_reports,
        delete_patch: delete_patch,
        apply_patch: apply_patch,
        restore_patch: restore_patch,
    };
});