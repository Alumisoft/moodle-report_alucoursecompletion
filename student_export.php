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
 * One student's details as Excel / PDF: access checks here, the file itself in the PRO class pro\student_export
 * (classes/pro/, not in the free zip: in the free edition this page only refuses).
 *
 * @package    report_alucoursecompletion
 * @copyright  2026 Alumisoft developer@alumisoft.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

use report_alucoursecompletion\local\helper;

$userid = required_param('userid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);
$format = required_param('format', PARAM_ALPHA);

require_login(null, false);
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/report/alucoursecompletion/student_export.php', [
    'userid' => $userid, 'courseid' => $courseid, 'format' => $format,
]));
require_capability('report/alucoursecompletion:view', $context);
if (!helper::student_details_enabled() || !in_array($format, array_intersect(helper::export_formats(), ['excel', 'pdf']), true)) {
    throw new moodle_exception('invalidparameter', 'debug');
}
\report_alucoursecompletion\pro\student_export::send($courseid, $userid, $format);
