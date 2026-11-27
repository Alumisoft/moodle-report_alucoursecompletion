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
 * Course completion report - Course Enrolments page.
 *
 * @package    report_alucoursecompletion
 * @copyright  2026 Alumisoft developer@alumisoft.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use report_alucoursecompletion\local\helper;
use report_alucoursecompletion\reportmanager\provider;
use report_alucoursecompletion\table\enrolments_table;

$component = 'report_alucoursecompletion';
$perpageoptions = [25, 50, 100];

admin_externalpage_setup('report_alucoursecompletion', '', null, '', ['pagelayout' => 'report']);

$provider = new provider();
$filters = $provider->clean_filters([
    'courseid' => optional_param('courseid', 0, PARAM_INT),
    'year' => optional_param('year', 0, PARAM_INT),
    'status' => optional_param('status', '', PARAM_ALPHA),
    'search' => optional_param('search', '', PARAM_TEXT),
]);
$courses = helper::get_course_options();
$years = helper::get_year_options();
if ($filters['year'] && !in_array($filters['year'], $years)) {
    $filters['year'] = 0;
}
$perpage = optional_param('perpage', 25, PARAM_INT);
if (!in_array($perpage, $perpageoptions)) {
    $perpage = 25;
}

$params = array_filter($filters);
if ($perpage != 25) {
    $params['perpage'] = $perpage;
}
$url = new moodle_url('/report/alucoursecompletion/index.php', $params);
$PAGE->set_url($url);

$table = new enrolments_table($url, $filters);
$tracking = $filters['courseid'] ? helper::course_tracking($filters['courseid']) : helper::TRACKING_ON;

$download = optional_param('download', '', PARAM_ALPHA);
if ($download !== '' && in_array($download, helper::export_formats(), true) && $tracking === helper::TRACKING_ON) {
    $coursename = $filters['courseid']
        ? format_string(get_course($filters['courseid'])->fullname, true, ['escape' => false]) : '';
    $name = helper::export_filename(['course-enrolments', $coursename, $filters['year'] ?: '',
        $filters['status'], $filters['search']]);
    $table->download($download, $name, get_string('tab_enrolments', $component));
    die();
}

echo $OUTPUT->header();
$badge = '';
echo html_writer::div(
    $OUTPUT->heading(get_string('pluginname', $component) . $badge, 2, 'mb-0') . helper::settings_button(),
    'd-flex justify-content-between align-items-center flex-wrap mb-3'
);
echo helper::tabs('enrolments');

$s = fn(string $id): string => get_string($id, $component);
$form = html_writer::start_tag('form', ['method' => 'get', 'action' => $url->out_omit_querystring(),
    'class' => 'report-alucoursecompletion-filters d-flex flex-wrap align-items-center gap-2 mb-3']);
$form .= html_writer::label($s('course'), 'alucc-courseid', false, ['class' => 'mb-0']);
$form .= html_writer::select(
    [0 => $s('allcourses')] + $courses,
    'courseid',
    $filters['courseid'],
    false,
    ['id' => 'alucc-courseid', 'class' => 'custom-select form-select w-auto me-2 mr-2']
);
$form .= html_writer::label($s('year'), 'alucc-year', false, ['class' => 'mb-0']);
$form .= html_writer::select(
    [0 => $s('allyears')] + ($years ? array_combine($years, $years) : []),
    'year',
    $filters['year'],
    false,
    ['id' => 'alucc-year', 'class' => 'custom-select form-select w-auto me-2 mr-2']
);
$statusopts = ['' => $s('all')];
foreach (helper::STATUSES as $st) {
    $statusopts[$st] = $s('status_' . $st);
}
$form .= html_writer::label($s('status'), 'alucc-status', false, ['class' => 'mb-0']);
$form .= html_writer::select(
    $statusopts,
    'status',
    $filters['status'],
    false,
    ['id' => 'alucc-status', 'class' => 'custom-select form-select w-auto me-2 mr-2']
);
$form .= html_writer::label($s('searchstudent'), 'alucc-search', false, ['class' => 'sr-only visually-hidden']);
$form .= html_writer::empty_tag('input', ['type' => 'search', 'name' => 'search', 'id' => 'alucc-search',
    'value' => $filters['search'], 'placeholder' => $s('searchstudent'), 'class' => 'form-control w-auto me-2 mr-2']);
if ($perpage != 25) {
    $form .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'perpage', 'value' => $perpage]);
}
$form .= html_writer::tag('button', $s('filter'), ['type' => 'submit', 'class' => 'btn btn-primary me-2 mr-2']);
if (array_filter($filters)) {
    $form .= html_writer::link(new moodle_url(
        '/report/alucoursecompletion/index.php',
        $perpage != 25 ? ['perpage' => $perpage] : []
    ), $s('reset'), ['class' => 'btn btn-secondary']);
}
$form .= html_writer::end_tag('form');
echo $form;

if ($tracking !== helper::TRACKING_ON) {
    $courselink = html_writer::link(
        new moodle_url('/course/view.php', ['id' => $filters['courseid']]),
        $courses[$filters['courseid']]
    );
    echo $OUTPUT->notification(html_writer::div($courselink, 'fw-bold mb-1') . $s('tracking_' . $tracking), 'info', false);
    echo $OUTPUT->footer();
    die();
}

if ($filters['courseid']) {
    $cells = '';
    foreach ($table->get_status_counts() as $key => $value) {
        $cells .= html_writer::div(html_writer::div($s('sum_' . $key), 'small text-muted') .
            html_writer::div($value, 'fs-4 fw-bold'), 'report-alucoursecompletion-sumcell me-4 mr-4');
    }
    $courselink = html_writer::link(
        new moodle_url('/course/view.php', ['id' => $filters['courseid']]),
        $courses[$filters['courseid']]
    );
    echo html_writer::div(
        html_writer::div($courselink, 'fw-bold mb-2') . html_writer::div($cells, 'd-flex flex-wrap'),
        'report-alucoursecompletion-summary border rounded p-3 mb-3'
    );
}

$perpageurl = new moodle_url($url);
$perpageurl->remove_params('perpage');
$select = new single_select($perpageurl, 'perpage', array_combine($perpageoptions, $perpageoptions), $perpage, null);
$select->set_label($s('perpage'), ['class' => 'me-2 mr-2']);
$select->class = 'd-inline-block';
$table->set_top_left($OUTPUT->render($select));
$table->set_top_right(helper::export_buttons($url));

$table->out($perpage, false);

echo $OUTPUT->footer();
