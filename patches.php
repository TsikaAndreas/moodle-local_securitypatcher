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
 * Report page for the local_codepatcher plugin.
 *
 * @package   local_codepatcher
 * @copyright 2023 onwards Andrei-Robert Țîcă <andreastsika@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_codepatcher\api;

require_once(__DIR__ . '/../../config.php');

global $CFG, $OUTPUT, $PAGE;
require_login();
// Set page configuration.
$PAGE->set_pagelayout('admin');
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/local/codepatcher/patches.php', []);
$PAGE->set_title(get_string('patches:title', 'local_codepatcher'));
$PAGE->set_heading(get_string('patches:heading', 'local_codepatcher'));
require_capability('local/codepatcher:viewreports', $context);
// Load css.
api::load_datatables_css();
$PAGE->requires->css('/local/codepatcher/styles/patches.css');

// Render the reports page.
echo $OUTPUT->header();
echo $OUTPUT->render(new local_codepatcher\output\patches());
echo $OUTPUT->footer();
