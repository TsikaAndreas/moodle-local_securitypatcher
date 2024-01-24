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
 * Patches output file for the local_securitypatcher plugin.
 *
 * @package   local_securitypatcher
 * @copyright 2023 onwards Andrei-Robert Tica <andreastsika@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_securitypatcher\output;

use local_securitypatcher\datatables\tables\patchesreport;
use renderer_base;

/**
 * Class containing data for security patches datatable.
 *
 * @package   local_securitypatcher
 * @copyright 2023 onwards Andrei-Robert Tica <andreastsika@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class patches implements \renderable, \templatable {

    /**
     * Return security patches datatable data.
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output) {

        $filters = [
                'patches' => [
                        'status' => \local_securitypatcher\datatables\tables\patches::html_status_select_options(),
                ],
                'patchesreport' => [
                        'status' => patchesreport::html_status_select_options(),
                        'operation' => patchesreport::html_operation_select_options(),
                ],
        ];

        return [
                'filters' => json_encode($filters, JSON_THROW_ON_ERROR),
        ];
    }
}
