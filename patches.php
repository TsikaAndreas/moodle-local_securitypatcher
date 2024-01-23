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

use local_securitypatcher\managers\report_manager;

require_once(__DIR__ . '/../../config.php');

global $CFG, $OUTPUT, $PAGE;
$CFG->cachejs = false;
//$CFG->cachetemplates = false;
require_login();
// Set page configuration.
$PAGE->set_pagelayout('admin');
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/local/securitypatcher/patches.php', []);
$PAGE->set_title(get_string('patches:title', 'local_securitypatcher'));
$PAGE->set_heading(get_string('patches:heading', 'local_securitypatcher'));

$PAGE->requires->css('/local/securitypatcher/styles/patches.css');

require_capability('local/securitypatcher:viewreports', $context);

// Load datatable css.
$reportmanager = new report_manager();
$reportmanager->load_datatables_css();

// Render the reports page.
echo $OUTPUT->header();
echo $OUTPUT->render(new local_securitypatcher\output\patches());
echo $OUTPUT->footer();

