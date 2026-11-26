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
$string['about_edition'] = 'Edition';
$string['about_edition_free'] = 'Free';
$string['about_edition_marketplace'] = 'Free (Moodle Marketplace)';
$string['about_edition_pro'] = 'PRO';
$string['about_licence'] = 'Licence';
$string['about_licence_desc'] = 'GNU GPL v3 or later';
$string['about_moodle'] = 'Moodle versions';
$string['about_moodle_desc'] = 'Supported: 4.5, 5.2 and 5.3. It also installs on 5.0 and 5.1 (Moodle versions that only get security fixes), without support.';
$string['about_plugin'] = 'Plugin';
$string['about_source'] = 'Source code';
$string['about_support'] = 'Support and bug reports';
$string['about_version'] = 'Version';
$string['accountcreated'] = 'Account created';
$string['act_complete'] = 'Completed';
$string['act_fail'] = 'Completed (fail)';
$string['act_incomplete'] = 'Not completed';
$string['act_pass'] = 'Completed (pass)';
$string['activities'] = 'Activities';
$string['activity'] = 'Activity';
$string['activitycompletedon'] = 'Completed on';
$string['activityprogress'] = 'Activity progress';
$string['all'] = 'All';
$string['allcourses'] = 'All courses';
$string['allyears'] = 'All years';
$string['alucoursecompletion:view'] = 'View the course completion report';
$string['certificateissued'] = 'Certificate issued';
$string['clickrow'] = 'Click a row to see the student details and activities.';
$string['comingsoon'] = 'Not yet implemented, Coming soon';
$string['completedon'] = 'Course completed';
$string['continued'] = '{$a} (continued)';
$string['course'] = 'Course';
$string['course_closed'] = 'Closed';
$string['course_hidden'] = 'Hidden';
$string['course_open'] = 'Open';
$string['coursecertificatecolumn'] = 'Enable Certificate column (mod_coursecertificate)';
$string['coursecertificateheading'] = 'Workplace course certificate (mod_coursecertificate)';
$string['courseprogress'] = 'Course progress';
$string['coursestatus'] = 'Course status';
$string['created'] = 'Created';
$string['customcertcolumn'] = 'Enable Certificate column';
$string['customcertcolumn_desc'] = 'Shows a "Certificate issued" column (date of the student\'s first certificate in the course, Custom certificate activity, mod_customcert) in Course Enrolments, its exports and the student details. The next setting can be changed only while this one is on. No effect when Custom certificate is not installed.';
$string['customcertcompleted'] = 'Certificate issued counts as completed';
$string['customcertcompleted_desc'] = 'When on, a student with a certificate issued in the course (Custom certificate activity, mod_customcert) counts as Completed, even without a Moodle course completion date, and "Course completed" shows the certificate issued date. Applies to the status column and filter, the course summary, Course Totals and the exports. Works only while "Enable Certificate column" is on. No effect when Custom certificate is not installed.';
$string['customcertheading'] = 'Custom Certificate plugin (customcert)';
$string['customcertheading_desc'] = 'Integration settings with "Custom certificate" plugin (mod_customcert).';
$string['customfield'] = 'custom field';
$string['dateformat'] = 'Date format';
$string['dateformat_desc'] = 'Format of all dates in the report and the exports.';
$string['dateformat_dmy'] = 'DD/MM/YYYY ({$a})';
$string['dateformat_lang'] = 'Language default ({$a})';
$string['dateformat_long'] = 'Day Month Year ({$a})';
$string['dateformat_longus'] = 'Month Day, Year ({$a})';
$string['dateformat_mdy'] = 'MM/DD/YYYY ({$a})';
$string['dateformat_ymd'] = 'YYYY-MM-DD ({$a})';
$string['dedicationcolumn'] = 'Enable Time spent column (block_dedication)';
$string['dedicationcolumn_desc'] = 'Shows a "Time spent" column (time the student spent in the course, as recorded by the Course dedication block) in Course Enrolments, its exports and the student details. Requires the Course dedication block (block_dedication) to be enabled on the site and added to the course: courses without the block show no time.';
$string['dedicationheading'] = 'Integration settings with "Course Dedication" plugin (block_dedication)';
$string['defaultsort'] = 'Default sort';
$string['defaultsort_desc'] = 'Order of the Course Enrolments table when the report is opened. Dates sort newest first, student name A to Z. A user who sorted the table keeps their own sort.';
$string['description'] = 'Student enrolments per course and year, progress and completion status, with course totals.';
$string['editionfree'] = 'Free';
$string['editionpro'] = 'PRO';
$string['email'] = 'Email';
$string['enroldate'] = 'Enrol date';
$string['enrolend'] = 'Enrol end';
$string['excludedcourses'] = 'Excluded courses';
$string['excludedcourses_desc'] = 'Courses left out of the report: both tabs, the course filter and the exports.';
$string['export'] = 'Export settings';
$string['export_csv'] = 'Download CSV';
$string['export_desc'] = 'Downloads this report\'s settings as a file (JSON).';
$string['export_excel'] = 'Download Excel';
$string['export_pdf'] = 'Download PDF';
$string['exportprefix'] = 'Export prefix';
$string['exportprefix_desc'] = 'Added at the start of the file name of every export, e.g. "ACME" gives "ACME-course-totals-20261231.csv". Leave empty for no prefix.';
$string['feature'] = 'Feature';
$string['feature_certificate'] = 'Certificate integration column';
$string['feature_csv'] = 'CSV export';
$string['feature_enrolments'] = 'Course Enrolments tab (filters, sorting, paging, course summary box)';
$string['feature_excelpdf'] = 'Excel and PDF export, prefix';
$string['feature_excludedcourses'] = 'Excluded courses setting';
$string['feature_profilefields'] = 'Profile fields in student details';
$string['feature_settings'] = 'Settings (default sort, date format)';
$string['feature_studentdetails'] = 'Student details window + Excel / PDF download';
$string['feature_timespent'] = 'Dedication Time spent column';
$string['feature_totals'] = 'Course Totals tab (filters, course status)';
$string['field'] = 'Field';
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
$string['installed'] = 'Installed';
$string['lastaccess'] = 'Last access';
$string['licence'] = 'PRO licence';
$string['licence_anysite'] = 'any site';
$string['licence_entrykey'] = 'Enter a licence key';
$string['licence_expired'] = 'The licence key expired on {$a->expires}.';
$string['licence_invalid'] = 'This is not a valid licence key.';
$string['licence_missing'] = 'No licence key: the PRO features are off, the free report works as usual.';
$string['licence_noexpiry'] = 'no end date';
$string['licence_prooff'] = 'PRO features are off.';
$string['licence_valid'] = 'Licence valid: {$a->customer}, site {$a->site}, until {$a->expires}.';
$string['licence_wrongproduct'] = 'This licence key is for another product.';
$string['licence_wrongsite'] = 'This licence key is for site {$a->site}; this site is {$a->here}.';
$string['licencekey'] = 'Licence key';
$string['licencekey_desc'] = 'The PRO licence key you received. Leave empty to use the free features only. The key is not included in settings export files.';
$string['name'] = 'Name';
$string['never'] = 'Never';
$string['noactivities'] = 'No activities with completion tracking in this course.';
$string['nocriteria'] = 'No criteria set';
$string['noenrolend'] = 'No end date';
$string['notinstalled'] = 'Not installed';
$string['perpage'] = 'Rows per page';
$string['pluginname'] = 'Course completion report';
$string['privacy:metadata'] = 'The Course completion report only shows existing data. It does not store any personal data.';
$string['probadge'] = 'PRO';
$string['profeatures'] = 'Features by edition';
$string['profilefields'] = 'Profile fields in student details';
$string['profilefields_desc'] = 'Fields shown in the student details (and their export) besides the name. A custom profile field is shown only to users allowed to see it by its visibility setting. Custom profile fields are matched by short name when settings are imported on another site.';
$string['progress'] = 'Progress';
$string['proonly'] = 'PRO edition only';
$string['proreadmore'] = 'Read more about PRO edition';
$string['reset'] = 'Reset';
$string['result'] = 'Result';
$string['result_applied'] = 'Changed';
$string['result_invalid'] = 'Not accepted';
$string['result_unchanged'] = 'Unchanged';
$string['result_unknown'] = 'Not known by this version (left out)';
$string['search'] = 'Search';
$string['searchcourse'] = 'Search course name';
$string['searchstudent'] = 'Search student name';
$string['section'] = 'Section';
$string['setting'] = 'Setting';
$string['settingstitle'] = 'Settings | {$a}';
$string['settingstransfer'] = 'Export / import settings';
$string['settingstransfer_desc'] = 'Keep this report\'s settings when you uninstall and install it again (for example when going from the PRO edition back to the free one: uninstalling removes the settings), or copy them to another site.';
$string['settingstransfer_desc_nopro'] = 'Keep this report\'s settings when you uninstall and install it again (uninstalling removes the settings), or copy them to another site.';
$string['showing'] = 'Showing {$a->count} of {$a->total}';
$string['status'] = 'Status';
$string['status_completed'] = 'Completed';
$string['status_inprogress'] = 'In progress';
$string['status_notfinished'] = 'Did not finish';
$string['student'] = 'Student';
$string['studentdetails'] = 'Student details';
$string['studentdetails_off'] = 'The student detail modal is switched off in the report settings.';
$string['studentdetailsenable'] = 'Enable student detail modal';
$string['studentdetailsenable_desc'] = 'The student detail modal is a window that opens over the report when you click a student row in the Course Enrolments tab. It shows the student profile (the fields chosen below), the course progress and status, and every activity with completion tracking (section, type, status, completion date), with buttons to download it as Excel or PDF. When off, the rows are not clickable and the window and its downloads are not available.';
$string['studentprofile'] = 'Student profile';
$string['sum_completed'] = 'Completed';
$string['sum_enrolled'] = 'Total enrolled';
$string['sum_inprogress'] = 'In progress';
$string['sum_notfinished'] = 'Did not finish';
$string['tab_about'] = 'About';
$string['tab_certificate'] = 'Certificate';
$string['tab_dedication'] = 'Dedication time';
$string['tab_details'] = 'Student detail modal';
$string['tab_enrolments'] = 'Course Enrolments';
$string['tab_export'] = 'Report export';
$string['tab_pro'] = 'PRO';
$string['tab_settings'] = 'Report view';
$string['tab_totals'] = 'Course Totals';
$string['timespent'] = 'Time spent';
$string['tracking_disabled'] = 'Course completion tracking is disabled for this course.';
$string['tracking_noconditions'] = 'Course completion tracking is enabled for this course, but no completion conditions are set up.';
$string['tracking_sitedisabled'] = 'Completion tracking is disabled for this site.';
$string['type'] = 'Type';
$string['value'] = 'Value';
$string['year'] = 'Enrol year';
