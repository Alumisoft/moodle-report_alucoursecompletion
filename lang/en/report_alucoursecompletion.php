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
 * English strings.
 *
 * @package    report_alucoursecompletion
 * @copyright  2026 Alumisoft developer@alumisoft.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['about_copyright'] = 'Copyright';
$string['about_docs'] = 'Documentation';
$string['about_licence'] = 'Licence';
$string['about_licence_desc'] = 'GNU GPL v3 or later';
$string['about_moodle'] = 'Moodle versions';
$string['about_moodle_desc'] = 'Supported: 4.5, 5.2 and 5.3. It also installs on 5.0 and 5.1 (Moodle versions that only get security fixes), but is not tested or supported there.';
$string['about_plugin'] = 'Plugin';
$string['about_source'] = 'Source code';
$string['about_support'] = 'Support and bug reports';
$string['about_version'] = 'Version';
$string['activityprogress'] = 'Activity progress';
$string['all'] = 'All';
$string['allcourses'] = 'All courses';
$string['allyears'] = 'All years';
$string['alucoursecompletion:view'] = 'View the course completion report';
$string['certificateissued'] = 'Certificate issued';
$string['completedon'] = 'Course completed';
$string['course'] = 'Course';
$string['course_closed'] = 'Closed';
$string['course_hidden'] = 'Hidden';
$string['course_open'] = 'Open';
$string['coursestatus'] = 'Course status';
$string['created'] = 'Created';
$string['dateformat'] = 'Date format';
$string['dateformat_desc'] = 'Format of all dates in the report and the exports.';
$string['dateformat_dmy'] = 'DD/MM/YYYY ({$a})';
$string['dateformat_lang'] = 'Language default ({$a})';
$string['dateformat_long'] = 'Day Month Year ({$a})';
$string['dateformat_longus'] = 'Month Day, Year ({$a})';
$string['dateformat_mdy'] = 'MM/DD/YYYY ({$a})';
$string['dateformat_ymd'] = 'YYYY-MM-DD ({$a})';
$string['defaultsort'] = 'Default sort';
$string['defaultsort_desc'] = 'Order of the Course enrolments table when the report is opened. Dates sort newest first, student name A to Z. A user who sorts the table keeps that sort for the rest of their session.';
$string['description'] = 'Student enrolments per course and year, progress and completion status, with course totals.';
$string['email'] = 'Email';
$string['enroldate'] = 'Enrol date';
$string['export'] = 'Export settings';
$string['export_csv'] = 'Download CSV';
$string['export_desc'] = 'Downloads this report\'s settings as a file (JSON).';
$string['filter'] = 'Filter';
$string['import'] = 'Import settings';
$string['import_desc'] = 'Upload a settings file exported from this report (this site or another one). Only settings this version knows are imported, each checked like on the settings page; the others are listed and left out. Excluded courses are matched by course short name.';
$string['importcoursesmissing'] = 'Courses not found on this site: {$a}';
$string['importdone'] = 'Import done: {$a->applied} changed, {$a->unchanged} unchanged, {$a->unknown} not known by this version, {$a->invalid} not accepted.';
$string['importerror_component'] = 'This file is not a settings export of this report.';
$string['importerror_json'] = 'The file could not be read (not a valid settings export).';
$string['importerror_size'] = 'The file is too large for a settings export.';
$string['importfile'] = 'Settings file';
$string['importnotinfile'] = 'Not in the file, left as it is.';
$string['importvaluesmissing'] = 'Not available on this site, left out: {$a}';
$string['lastaccess'] = 'Last access';
$string['never'] = 'Never';
$string['nocriteria'] = 'No criteria set';
$string['perpage'] = 'Rows per page';
$string['pluginname'] = 'Course completion report';
$string['privacy:metadata'] = 'The Course completion report only shows existing data. It does not store any personal data.';
$string['progress'] = 'Progress';
$string['reset'] = 'Reset';
$string['result'] = 'Result';
$string['result_applied'] = 'Changed';
$string['result_invalid'] = 'Not accepted';
$string['result_unchanged'] = 'Unchanged';
$string['result_unknown'] = 'Not known by this version (left out)';
$string['search'] = 'Search';
$string['searchcourse'] = 'Search course name';
$string['searchstudent'] = 'Search student name';
$string['setting'] = 'Setting';
$string['settingstitle'] = 'Settings | {$a}';
$string['settingstransfer'] = 'Export / import settings';
$string['settingstransfer_desc'] = 'Keep this report\'s settings when you uninstall and install it again (uninstalling removes the settings), or copy them to another site.';
$string['showing'] = 'Showing {$a->count} of {$a->total}';
$string['status'] = 'Status';
$string['status_completed'] = 'Completed';
$string['status_inprogress'] = 'In progress';
$string['status_notfinished'] = 'Did not finish';
$string['student'] = 'Student';
$string['sum_completed'] = 'Completed';
$string['sum_enrolled'] = 'Total enrolled';
$string['sum_inprogress'] = 'In progress';
$string['sum_notfinished'] = 'Did not finish';
$string['tab_about'] = 'About';
$string['tab_enrolments'] = 'Course enrolments';
$string['tab_settings'] = 'Report view';
$string['tab_totals'] = 'Course totals';
$string['timespent'] = 'Time spent';
$string['tracking_disabled'] = 'Course completion tracking is disabled for this course.';
$string['tracking_noconditions'] = 'Course completion tracking is enabled for this course, but no completion conditions are set up.';
$string['tracking_sitedisabled'] = 'Completion tracking is disabled for this site.';
$string['value'] = 'Value';
$string['year'] = 'Enrol year';
