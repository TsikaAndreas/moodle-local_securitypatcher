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
 * Report page for the local_securitypatcher plugin.
 *
 * @package   local_securitypatcher
 * @copyright 2023 onwards Andrei-Robert Tica <andreastsika@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../config.php');

use local_securitypatcher\api;

global $CFG, $OUTPUT, $PAGE;

require_login();
// Set page configuration.
$PAGE->set_pagelayout('admin');
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/local/securitypatcher/report.php', []);
$PAGE->set_title(get_string('report:title', 'local_securitypatcher'));
$PAGE->set_heading(get_string('report:heading', 'local_securitypatcher'));

require_capability('local/securitypatcher:viewreports', $context);

// Load datatable css.
api::load_datatables_css();

// Render the reports page.
echo $OUTPUT->header();
echo $OUTPUT->render(new local_securitypatcher\output\report());
echo $OUTPUT->footer();

