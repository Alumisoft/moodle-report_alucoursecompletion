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
 * Course completion report - Course Totals page.
 *
 * @package    report_alucoursecompletion
 * @copyright  2026 Alumisoft developer@alumisoft.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use report_alucoursecompletion\local\edition;
use report_alucoursecompletion\local\helper;
use report_alucoursecompletion\local\totals;
use report_alucoursecompletion\table\totals_table;

$component = 'report_alucoursecompletion';
$perpageoptions = [25, 50, 100];

admin_externalpage_setup(
    'report_alucoursecompletion',
    '',
    null,
    new moodle_url('/report/alucoursecompletion/totals.php'),
    ['pagelayout' => 'report']
);

$filters = totals::clean_filters([
    'year' => optional_param('year', 0, PARAM_INT),
    'status' => optional_param('status', '', PARAM_ALPHA),
    'search' => optional_param('search', '', PARAM_TEXT),
]);
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
$url = new moodle_url('/report/alucoursecompletion/totals.php', $params);
$PAGE->set_url($url);

$table = new totals_table($url, $filters);

$download = optional_param('download', '', PARAM_ALPHA);
if ($download !== '' && in_array($download, helper::export_formats(), true)) {
    $name = helper::export_filename(['course-totals', $filters['year'] ?: '', $filters['status'], $filters['search']]);
    $table->download($download, $name, get_string('tab_totals', $component));
    die();
}

echo $OUTPUT->header();
echo html_writer::div(
    $OUTPUT->heading(get_string('pluginname', $component), 2, 'mb-0') . helper::settings_button(),
    'd-flex justify-content-between align-items-center flex-wrap mb-3'
);
echo edition::licence_notice();
echo helper::tabs('totals');

$s = fn(string $id): string => get_string($id, $component);
$form = html_writer::start_tag('form', ['method' => 'get', 'action' => $url->out_omit_querystring(),
    'class' => 'report-alucoursecompletion-filters d-flex flex-wrap align-items-center gap-2 mb-3']);
$form .= html_writer::label($s('searchcourse'), 'alucc-search', false, ['class' => 'sr-only visually-hidden']);
$form .= html_writer::empty_tag('input', ['type' => 'search', 'name' => 'search', 'id' => 'alucc-search',
    'value' => $filters['search'], 'placeholder' => $s('searchcourse'), 'class' => 'form-control w-auto me-2 mr-2']);
$form .= html_writer::label($s('year'), 'alucc-year', false, ['class' => 'mb-0']);
$form .= html_writer::select(
    [0 => $s('allyears')] + ($years ? array_combine($years, $years) : []),
    'year',
    $filters['year'],
    false,
    ['id' => 'alucc-year', 'class' => 'custom-select form-select w-auto me-2 mr-2']
);
$statusopts = ['' => $s('all')];
foreach (totals::COURSE_STATUSES as $st) {
    $statusopts[$st] = $s('course_' . $st);
}
$form .= html_writer::label($s('coursestatus'), 'alucc-status', false, ['class' => 'mb-0']);
$form .= html_writer::select(
    $statusopts,
    'status',
    $filters['status'],
    false,
    ['id' => 'alucc-status', 'class' => 'custom-select form-select w-auto me-2 mr-2']
);
if ($perpage != 25) {
    $form .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'perpage', 'value' => $perpage]);
}
$form .= html_writer::tag('button', $s('filter'), ['type' => 'submit', 'class' => 'btn btn-primary me-2 mr-2']);
if (array_filter($filters)) {
    $form .= html_writer::link(new moodle_url(
        '/report/alucoursecompletion/totals.php',
        $perpage != 25 ? ['perpage' => $perpage] : []
    ), $s('reset'), ['class' => 'btn btn-secondary']);
}
$form .= html_writer::end_tag('form');
echo $form;

$perpageurl = new moodle_url($url);
$perpageurl->remove_params('perpage');
$select = new single_select($perpageurl, 'perpage', array_combine($perpageoptions, $perpageoptions), $perpage, null);
$select->set_label($s('perpage'), ['class' => 'me-2 mr-2']);
$select->class = 'd-inline-block';
$table->set_top_left($OUTPUT->render($select));
$table->set_top_right(helper::export_buttons($url));

$table->out($perpage, false);

echo $OUTPUT->footer();
