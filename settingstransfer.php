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
 * Settings export / import page (site config capability).
 *
 * @package    report_alucoursecompletion
 * @copyright  2026 Alumisoft developer@alumisoft.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use report_alucoursecompletion\form\import_form;
use report_alucoursecompletion\local\settings_transfer;

$component = 'report_alucoursecompletion';

admin_externalpage_setup('report_alucoursecompletion_transfer');
$url = new moodle_url('/report/alucoursecompletion/settingstransfer.php');
$PAGE->set_url($url);

if (optional_param('export', 0, PARAM_BOOL)) {
    require_sesskey();
    $json = settings_transfer::export_json();
    $filename = clean_filename('alucoursecompletion-settings-' . date('Ymd') . '.json');
    send_file($json, $filename, 0, 0, true, true, 'application/json');
    die();
}

$form = new import_form($url);
$results = null;
$error = '';
if ($form->is_submitted() && $form->is_validated()) {
    $content = (string)$form->get_file_content('settingsfile');
    try {
        $data = settings_transfer::parse($content);
        $results = settings_transfer::import($data);
    } catch (moodle_exception $e) {
        $error = $e->getMessage();
    }
}

echo $OUTPUT->header();
echo \report_alucoursecompletion\local\helper::admin_tabs('transfer');

$transferdesc = 'settingstransfer_desc';
echo html_writer::tag('p', get_string($transferdesc, $component));

echo $OUTPUT->heading(get_string('export', $component), 3);
echo html_writer::tag('p', get_string('export_desc', $component));
echo html_writer::start_tag('form', ['method' => 'post', 'action' => $url->out(false)]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'export', 'value' => 1]);
echo html_writer::tag('button', get_string('export', $component), ['type' => 'submit', 'class' => 'btn btn-primary']);
echo html_writer::end_tag('form');

echo $OUTPUT->heading(get_string('import', $component), 3, 'mt-4');
echo html_writer::tag('p', get_string('import_desc', $component));
if ($error !== '') {
    echo $OUTPUT->notification($error, \core\output\notification::NOTIFY_ERROR);
}
if ($results !== null) {
    $counts = array_count_values(array_column($results, 'result'));
    echo $OUTPUT->notification(get_string('importdone', $component, (object)[
        'applied' => $counts[settings_transfer::RESULT_APPLIED] ?? 0,
        'unchanged' => $counts[settings_transfer::RESULT_UNCHANGED] ?? 0,
        'unknown' => $counts[settings_transfer::RESULT_UNKNOWN] ?? 0,
        'invalid' => $counts[settings_transfer::RESULT_INVALID] ?? 0,
    ]), empty($counts[settings_transfer::RESULT_INVALID]) ? \core\output\notification::NOTIFY_SUCCESS
        : \core\output\notification::NOTIFY_WARNING);
    $table = new html_table();
    $table->head = [get_string('setting', $component), get_string('value', $component),
        get_string('result', $component), ''];
    $table->attributes['class'] = 'generaltable report-alucoursecompletion-importresult';
    foreach ($results as $name => $r) {
        $title = get_string_manager()->string_exists($name, $component) ? get_string($name, $component) : $name;
        $table->data[] = [s($title) . html_writer::div(s($name), 'small text-muted'), s($r['value']),
            get_string('result_' . $r['result'], $component), s($r['note'])];
    }
    echo html_writer::table($table);
}
$form->display();

echo $OUTPUT->footer();
