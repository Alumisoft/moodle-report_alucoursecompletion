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

$protabs = \report_alucoursecompletion\local\edition::pro_tabs_shown();

$exportsettings = new admin_settingpage(
    'reportalucoursecompletion_export',
    get_string('tab_export', 'report_alucoursecompletion'),
    'moodle/site:config',
    true
);
if ($protabs) {
    $ADMIN->add('reports', $exportsettings);
}

$detailsettings = new admin_settingpage(
    'reportalucoursecompletion_details',
    get_string('tab_details', 'report_alucoursecompletion'),
    'moodle/site:config',
    true
);
if ($protabs) {
    $ADMIN->add('reports', $detailsettings);
}

$certsettings = new admin_settingpage(
    'reportalucoursecompletion_cert',
    get_string('tab_certificate', 'report_alucoursecompletion'),
    'moodle/site:config',
    true
);
if ($protabs) {
    $ADMIN->add('reports', $certsettings);
}

$dedicationsettings = new admin_settingpage(
    'reportalucoursecompletion_dedication',
    get_string('tab_dedication', 'report_alucoursecompletion'),
    'moodle/site:config',
    true
);
if ($protabs) {
    $ADMIN->add('reports', $dedicationsettings);
}

$prosettings = new admin_settingpage(
    'reportalucoursecompletion_pro',
    get_string('tab_pro', 'report_alucoursecompletion'),
    'moodle/site:config',
    true
);
if ($protabs) {
    $ADMIN->add('reports', $prosettings);
}

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

    $pro = \report_alucoursecompletion\local\helper::pro_badge();

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

    $choices = [];
    $courses = $DB->get_records_select_menu('course', 'id <> ?', [SITEID], 'fullname ASC', 'id, fullname');
    foreach ($courses as $id => $name) {
        $choices[$id] = format_string($name, true, ['escape' => false]) . ' (id ' . $id . ')';
    }
    if (\report_alucoursecompletion\local\edition::pro_installed()) {
        $settings->add(new admin_setting_configmultiselect(
            'report_alucoursecompletion/excludedcourses',
            $s('excludedcourses') . $pro,
            $s('excludedcourses_desc'),
            [],
            $choices
        ));
    } else if (\report_alucoursecompletion\local\edition::promo_shown()) {
        $settings->add(new \report_alucoursecompletion\local\admin_setting_prolocked(
            'report_alucoursecompletion/excludedcourses_locked',
            $s('excludedcourses') . $pro,
            $s('excludedcourses_desc'),
            'multiselect',
            $choices
        ));
    }

    $detailsettings->add(new \report_alucoursecompletion\local\admin_setting_tabs(
        'report_alucoursecompletion/admintabs_details',
        'details'
    ));
    if (\report_alucoursecompletion\local\edition::pro_installed()) {
        $detailsettings->add(new admin_setting_configcheckbox(
            'report_alucoursecompletion/studentdetails',
            $s('studentdetailsenable') . $pro,
            $s('studentdetailsenable_desc'),
            1
        ));
    } else {
        $detailsettings->add(new \report_alucoursecompletion\local\admin_setting_prolocked(
            'report_alucoursecompletion/studentdetails_locked',
            $s('studentdetailsenable') . $pro,
            $s('studentdetailsenable_desc'),
            'checkbox',
            [],
            ['1']
        ));
    }
    if (\report_alucoursecompletion\local\edition::pro_installed()) {
        $detailsettings->add(new admin_setting_configmultiselect(
            'report_alucoursecompletion/profilefields',
            $s('profilefields') . $pro,
            $s('profilefields_desc'),
            \report_alucoursecompletion\pro\profile_fields::DEFAULT,
            \report_alucoursecompletion\pro\profile_fields::get_choices()
        ));
    } else {
        $detailsettings->add(new \report_alucoursecompletion\local\admin_setting_prolocked(
            'report_alucoursecompletion/profilefields_locked',
            $s('profilefields') . $pro,
            $s('profilefields_desc'),
            'multiselect',
            \report_alucoursecompletion\local\profile_field_choices::get(),
            \report_alucoursecompletion\local\profile_field_choices::DEFAULT
        ));
    }

    $certsettings->add(new \report_alucoursecompletion\local\admin_setting_tabs(
        'report_alucoursecompletion/admintabs_cert',
        'certificate'
    ));
    $certsettings->add(new admin_setting_heading(
        'report_alucoursecompletion/customcertheading',
        $s('customcertheading') . ' ' . (\report_alucoursecompletion\local\helper::has_customcert()
            ? html_writer::span($s('installed'), 'badge report-alucoursecompletion-installed')
            : html_writer::span($s('notinstalled'), 'badge bg-warning text-dark')),
        $s('customcertheading_desc')
    ));
    if (\report_alucoursecompletion\local\edition::pro_installed()) {
        $certsettings->add(new admin_setting_configcheckbox(
            'report_alucoursecompletion/customcertcolumn',
            $s('customcertcolumn') . $pro,
            $s('customcertcolumn_desc'),
            0
        ));
        $certsettings->add(new \report_alucoursecompletion\local\admin_setting_dependentcheckbox(
            'report_alucoursecompletion/customcertcompleted',
            $s('customcertcompleted') . $pro,
            $s('customcertcompleted_desc'),
            '0',
            'id_s_report_alucoursecompletion_customcertcolumn'
        ));
    } else {
        foreach (['customcertcolumn', 'customcertcompleted'] as $name) {
            $certsettings->add(new \report_alucoursecompletion\local\admin_setting_prolocked(
                "report_alucoursecompletion/{$name}_locked",
                $s($name) . $pro,
                $s($name . '_desc'),
                'checkbox'
            ));
        }
    }

    $certsettings->add(new admin_setting_heading(
        'report_alucoursecompletion/coursecertificateheading',
        $s('coursecertificateheading'),
        ''
    ));
    $certsettings->add(new \report_alucoursecompletion\local\admin_setting_prolocked(
        'report_alucoursecompletion/coursecertificatecolumn_soon',
        $s('coursecertificatecolumn') . $pro,
        $s('comingsoon'),
        'checkbox',
        [],
        [],
        false
    ));

    $exportsettings->add(new \report_alucoursecompletion\local\admin_setting_tabs(
        'report_alucoursecompletion/admintabs_export',
        'export'
    ));
    if (\report_alucoursecompletion\local\edition::pro_installed()) {
        $exportsettings->add(\report_alucoursecompletion\pro\export_prefix::setting());
    } else {
        $exportsettings->add(new \report_alucoursecompletion\local\admin_setting_prolocked(
            'report_alucoursecompletion/exportprefix_locked',
            $s('exportprefix') . $pro,
            $s('exportprefix_desc'),
            'text'
        ));
    }

    $dedicationsettings->add(new \report_alucoursecompletion\local\admin_setting_tabs(
        'report_alucoursecompletion/admintabs_dedication',
        'dedication'
    ));
    $dedicationsettings->add(new admin_setting_heading(
        'report_alucoursecompletion/dedicationheading',
        $s('dedicationheading') . ' ' . (\report_alucoursecompletion\local\helper::has_dedication()
            ? html_writer::span($s('installed'), 'badge report-alucoursecompletion-installed')
            : html_writer::span($s('notinstalled'), 'badge bg-warning text-dark')),
        ''
    ));
    if (\report_alucoursecompletion\local\edition::pro_installed()) {
        $dedicationsettings->add(new admin_setting_configcheckbox(
            'report_alucoursecompletion/dedicationcolumn',
            $s('dedicationcolumn') . $pro,
            $s('dedicationcolumn_desc'),
            1
        ));
    } else {
        $dedicationsettings->add(new \report_alucoursecompletion\local\admin_setting_prolocked(
            'report_alucoursecompletion/dedicationcolumn_locked',
            $s('dedicationcolumn') . $pro,
            $s('dedicationcolumn_desc'),
            'checkbox',
            [],
            ['1']
        ));
    }

    $prosettings->add(new \report_alucoursecompletion\local\admin_setting_tabs('report_alucoursecompletion/admintabs_pro', 'pro'));
    if (\report_alucoursecompletion\local\edition::pro_installed()) {
        $status = \report_alucoursecompletion\pro\licence::describe(\report_alucoursecompletion\pro\licence::status());
        $prosettings->add(new admin_setting_heading(
            'report_alucoursecompletion/licenceheading',
            $s('licence'),
            html_writer::div(s($status), \report_alucoursecompletion\local\edition::pro_active()
                ? 'alert alert-success' : 'alert alert-warning')
        ));
        $prosettings->add(new \report_alucoursecompletion\pro\admin_setting_licencekey(
            'report_alucoursecompletion/licencekey',
            $s('licencekey') . $pro,
            $s('licencekey_desc'),
            '',
            PARAM_RAW_TRIMMED,
            60,
            4
        ));
    } else {
        $prosettings->add(new admin_setting_heading(
            'report_alucoursecompletion/profeatures',
            $s('profeatures'),
            \report_alucoursecompletion\local\helper::pro_features_html()
        ));
    }

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
