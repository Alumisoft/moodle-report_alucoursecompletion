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
 * Report link (Site administration > Reports) and settings page (Site administration > Plugins > Reports).
 *
 * @package    report_alucoursecompletion
 * @copyright  2026 Alumisoft developer@alumisoft.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$ADMIN->add('reports', new admin_externalpage(
    'report_alucoursecompletion',
    get_string('pluginname', 'report_alucoursecompletion'),
    new moodle_url('/report/alucoursecompletion/index.php'),
    'report/alucoursecompletion:view'
));

$ADMIN->add('reports', new admin_externalpage(
    'report_alucoursecompletion_transfer',
    get_string('settingstransfer', 'report_alucoursecompletion'),
    new moodle_url('/report/alucoursecompletion/settingstransfer.php'),
    'moodle/site:config',
    true
));

$aboutsettings = new admin_settingpage(
    'reportalucoursecompletion_about',
    get_string('tab_about', 'report_alucoursecompletion'),
    'moodle/site:config',
    true
);
$ADMIN->add('reports', $aboutsettings);

if ($ADMIN->fulltree) {
    $s = fn(string $id, $a = null): string => get_string($id, 'report_alucoursecompletion', $a);

    $settings->add(new \report_alucoursecompletion\local\admin_setting_tabs('report_alucoursecompletion/admintabs', 'settings'));

    $settings->add(new admin_setting_configselect(
        'report_alucoursecompletion/defaultsort',
        $s('defaultsort'),
        $s('defaultsort_desc'),
        'enroldate',
        array_map($s, \report_alucoursecompletion\local\helper::DEFAULT_SORTS)
    ));

    $formats = [];
    foreach (array_keys(\report_alucoursecompletion\local\helper::DATE_FORMATS) as $key) {
        $formats[$key] = $s('dateformat_' . $key, \report_alucoursecompletion\local\helper::date(time(), $key));
    }
    $settings->add(new admin_setting_configselect(
        'report_alucoursecompletion/dateformat',
        $s('dateformat'),
        $s('dateformat_desc'),
        \report_alucoursecompletion\local\helper::DATE_FORMAT_DEFAULT,
        $formats
    ));

    $aboutsettings->add(new \report_alucoursecompletion\local\admin_setting_tabs(
        'report_alucoursecompletion/admintabs_about',
        'about'
    ));
    $aboutsettings->add(new admin_setting_heading(
        'report_alucoursecompletion/about',
        '',
        \report_alucoursecompletion\local\helper::about_html()
    ));
}
