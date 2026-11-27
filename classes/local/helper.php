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

namespace report_alucoursecompletion\local;

/**
 * Report definitions (who is a student, enrol date/end, year, status) and the shared SQL.
 *
 * Definitions (plan/2_report_completion.txt section B, same as the old bmt report):
 * - Student: user with a role of archetype "student" in the course context.
 * - Enrol date: earliest enrolment start (created time when no start) over all the user's enrolments in the course.
 * - Enrol end: 0 when any enrolment has no end, else the latest end.
 * - Status: completed = course completion time set (or a certificate issued, when that setting is on);
 *   did not finish = not completed and enrol end passed; in progress = everything else.
 * - Year: calendar year (server timezone) of the enrol date.
 * - Courses: only courses with completion tracked (completion on for the site and the course, at least one course
 *   completion condition); the others are left out (user decision 2026-11-20, tracking_sql()).
 *
 * @package    report_alucoursecompletion
 * @copyright  2026 Alumisoft developer@alumisoft.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {
    /** @var string Status: completed. */
    public const STATUS_COMPLETED = 'completed';
    /** @var string Status: did not finish. */
    public const STATUS_NOTFINISHED = 'notfinished';
    /** @var string Status: in progress. */
    public const STATUS_INPROGRESS = 'inprogress';
    /** @var string[] All statuses, in display order. */
    public const STATUSES = [self::STATUS_COMPLETED, self::STATUS_INPROGRESS, self::STATUS_NOTFINISHED];

    /** @var string Course completion tracked: on for the site and the course, with conditions. */
    public const TRACKING_ON = 'on';
    /** @var string Completion tracking off for the whole site. */
    public const TRACKING_SITEDISABLED = 'sitedisabled';
    /** @var string Completion tracking off for the course (even with conditions stored). */
    public const TRACKING_DISABLED = 'disabled';
    /** @var string Completion tracking on for the course, but no completion conditions. */
    public const TRACKING_NOCONDITIONS = 'noconditions';

    /** @var array Date formats (setting, plan step 6), key => strftime format; null = language default. */
    public const DATE_FORMATS = [
        'mdy' => '%m/%d/%Y',
        'dmy' => '%d/%m/%Y',
        'ymd' => '%Y-%m-%d',
        'long' => '%d %B %Y',
        'longus' => '%B %d, %Y',
        'lang' => null,
    ];

    /** @var string Default date format (user decision 2026-11-19: YYYY-MM-DD). */
    public const DATE_FORMAT_DEFAULT = 'ymd';

    /** @var string[] Default sort choices of the Course Enrolments table (setting): column => string id. */
    public const DEFAULT_SORTS = ['enroldate' => 'enroldate', 'studentname' => 'student', 'lastaccess' => 'lastaccess'];

    /**
     * Date in the report's format (setting "Date format", user's timezone), empty for no date.
     * Leading zeros kept (01/02/2026), so all dates have the same width.
     *
     * @param int|string|null $time
     * @param string|null $key format key of DATE_FORMATS; null = the setting
     * @return string
     */
    public static function date($time, ?string $key = null): string {
        if (empty($time)) {
            return '';
        }
        $key = $key ?? (string)get_config('report_alucoursecompletion', 'dateformat');
        if (!array_key_exists($key, self::DATE_FORMATS)) {
            $key = self::DATE_FORMAT_DEFAULT;
        }
        $format = self::DATE_FORMATS[$key];
        return userdate((int)$time, $format ?? get_string('strftimedatefullshort', 'langconfig'), 99, false);
    }

    /**
     * Default sort of the Course Enrolments table (setting): [column, SORT_ASC|SORT_DESC].
     *
     * @return array
     */
    public static function default_sort(): array {
        $sort = (string)get_config('report_alucoursecompletion', 'defaultsort');
        if (!array_key_exists($sort, self::DEFAULT_SORTS)) {
            $sort = 'enroldate';
        }
        return [$sort, $sort === 'studentname' ? SORT_ASC : SORT_DESC];
    }

    /**
     * Export formats offered, only those whose Moodle data format plugin is enabled.
     *
     * @return string[] data format names, e.g. ['csv']
     */
    public static function export_formats(): array {
        $formats = ['csv'];
        $enabled = \core\plugininfo\dataformat::get_enabled_plugins();
        return array_values(array_filter($formats, fn($f) => isset($enabled[$f])));
    }

    /**
     * Export file name (no extension): the name parts and today's date.
     *
     * @param string[] $parts e.g. ['course-totals', '2025']; empty parts are skipped
     * @return string
     */
    public static function export_filename(array $parts): string {
        $prefix = '';
        $parts = array_filter(array_map(fn($p) => trim(preg_replace(
            '/[^\p{L}\p{N}]+/u',
            '-',
            \core_text::strtolower((string)$p)
        ), '-'), $parts), 'strlen');
        $parts[] = date('Ymd');
        return clean_filename($prefix . implode('-', $parts));
    }

    /**
     * Export buttons (one link per format) for a page url with its filters.
     *
     * @param \moodle_url $url
     * @return string
     */
    public static function export_buttons(\moodle_url $url): string {
        $html = '';
        foreach (self::export_formats() as $format) {
            $html .= \html_writer::link(
                new \moodle_url($url, ['download' => $format]),
                get_string('export_' . $format, 'report_alucoursecompletion'),
                ['class' => 'btn btn-secondary text-nowrap ms-2 ml-2']
            );
        }
        return $html;
    }

    /**
     * Link to the settings page, for users who can change site settings ('' otherwise).
     *
     * @return string
     */
    public static function settings_button(): string {
        global $OUTPUT;
        if (!has_capability('moodle/site:config', \context_system::instance())) {
            return '';
        }
        return \html_writer::link(
            new \moodle_url('/admin/settings.php', ['section' => 'reportalucoursecompletion']),
            $OUTPUT->pix_icon('i/settings', '') . get_string('settings'),
            ['class' => 'btn btn-secondary']
        );
    }

    /**
     * Enrol years that have student rows, newest first.
     *
     * @return int[]
     */
    public static function get_year_options(): array {
        global $DB;
        [$sql, $params] = self::enrolments_sql();
        [$stusql, $stuparams] = self::student_sql('r.userid', 'ctx.id');
        $range = $DB->get_record_sql("SELECT MIN(r.enroldate) AS mindate, MAX(r.enroldate) AS maxdate
                                        FROM ($sql) r
                                        JOIN {user} u ON u.id = r.userid AND u.deleted = 0
                                        JOIN {context} ctx ON ctx.instanceid = r.courseid
                                             AND ctx.contextlevel = " . CONTEXT_COURSE . "
                                       WHERE $stusql", $params + $stuparams);
        if (empty($range->mindate)) {
            return [];
        }
        return range(self::year_of((int)$range->maxdate), self::year_of((int)$range->mindate));
    }

    /**
     * Course ids left out of the report.
     *
     * @return int[]
     */
    public static function get_excluded_courses(): array {
        return [];
    }

    /**
     * Are the table rows clickable for a student detail view?
     *
     * @return bool
     */
    public static function student_details_enabled(): bool {
        return false;
    }

    /**
     * Role ids with the student archetype.
     *
     * @return int[]
     */
    public static function get_student_roleids(): array {
        $ids = array_keys(get_archetype_roles('student'));
        return $ids ?: [0];
    }

    /**
     * Courses the report can show (all except the site and the excluded ones), id => name.
     *
     * @return string[]
     */
    public static function get_course_options(): array {
        global $DB;
        $exclude = array_merge([SITEID], self::get_excluded_courses());
        [$notin, $params] = $DB->get_in_or_equal($exclude, SQL_PARAMS_QM, 'param', false);
        $courses = $DB->get_records_select_menu('course', "id $notin", $params, 'fullname ASC', 'id, fullname');
        return array_map('format_string', $courses);
    }

    /**
     * Start (inclusive) and end (exclusive) timestamps of a calendar year in the server timezone.
     *
     * @param int $year
     * @return int[]
     */
    public static function year_bounds(int $year): array {
        $tz = \core_date::get_server_timezone_object();
        $start = new \DateTime($year . '-01-01 00:00:00', $tz);
        $end = new \DateTime(($year + 1) . '-01-01 00:00:00', $tz);
        return [$start->getTimestamp(), $end->getTimestamp()];
    }

    /**
     * Calendar year (server timezone) of a timestamp.
     *
     * @param int $time
     * @return int
     */
    public static function year_of(int $time): int {
        $d = new \DateTime('@' . $time);
        $d->setTimezone(\core_date::get_server_timezone_object());
        return (int)$d->format('Y');
    }

    /**
     * One row per course and user: enrol date and enrol end over all the user's enrolments in the course.
     * The course filter stays inside this grouped subquery (outer filters on it gave wrong counts on MariaDB 11.8
     * in the old report).
     *
     * @param int $courseid 0 = all courses
     * @return array [sql, named params]
     */
    public static function enrolments_sql(int $courseid = 0): array {
        global $DB;
        $exclude = array_merge([SITEID], self::get_excluded_courses());
        [$notin, $params] = $DB->get_in_or_equal($exclude, SQL_PARAMS_NAMED, 'exc', false);
        if ($courseid) {
            $notin .= ' AND e.courseid = :enrolcourseid';
            $params['enrolcourseid'] = $courseid;
        }
        $sql = "SELECT e.courseid, ue.userid,
                       MIN(CASE WHEN ue.timestart > 0 THEN ue.timestart ELSE ue.timecreated END) AS enroldate,
                       CASE WHEN MIN(ue.timeend) = 0 THEN 0 ELSE MAX(ue.timeend) END AS enrolend
                  FROM {user_enrolments} ue
                  JOIN {enrol} e ON e.id = ue.enrolid
                 WHERE e.courseid $notin AND " . self::tracking_sql('e.courseid') . "
              GROUP BY e.courseid, ue.userid";
        return [$sql, $params];
    }

    /**
     * Condition: the course has completion tracked (TRACKING_ON). Same test as Moodle's own completion aggregation
     * (aggregate_completions(): course enablecompletion = 1 and a row in course_completion_criteria).
     *
     * @param string $courseidfield
     * @return string
     */
    public static function tracking_sql(string $courseidfield): string {
        global $CFG;
        if (empty($CFG->enablecompletion)) {
            return '1 = 0';
        }
        return "EXISTS (SELECT 1 FROM {course} tc
                         WHERE tc.id = $courseidfield AND tc.enablecompletion = 1
                               AND EXISTS (SELECT 1 FROM {course_completion_criteria} tcr WHERE tcr.course = tc.id))";
    }

    /**
     * Completion tracking state of a course (TRACKING_*).
     *
     * @param int $courseid
     * @return string
     */
    public static function course_tracking(int $courseid): string {
        global $CFG, $DB;
        if (empty($CFG->enablecompletion)) {
            return self::TRACKING_SITEDISABLED;
        }
        if (!$DB->get_field('course', 'enablecompletion', ['id' => $courseid])) {
            return self::TRACKING_DISABLED;
        }
        return $DB->record_exists('course_completion_criteria', ['course' => $courseid])
            ? self::TRACKING_ON : self::TRACKING_NOCONDITIONS;
    }

    /**
     * Condition: the user has a student role in the course context.
     *
     * @param string $useridfield
     * @param string $contextidfield
     * @return array [sql, named params]
     */
    public static function student_sql(string $useridfield, string $contextidfield): array {
        global $DB;
        [$in, $params] = $DB->get_in_or_equal(self::get_student_roleids(), SQL_PARAMS_NAMED, 'role');
        $sql = "EXISTS (SELECT 1 FROM {role_assignments} ra
                         WHERE ra.userid = $useridfield AND ra.contextid = $contextidfield AND ra.roleid $in)";
        return [$sql, $params];
    }

    /**
     * Is block_dedication (Course dedication) installed?
     *
     * @return bool
     */
    public static function has_dedication(): bool {
        return (bool)\core_plugin_manager::instance()->get_plugin_info('block_dedication');
    }

    /**
     * Is mod_customcert installed (its issues table exists)?
     *
     * @return bool
     */
    public static function has_customcert(): bool {
        global $DB;
        static $has = null;
        if ($has === null) {
            $has = $DB->get_manager()->table_exists('customcert_issues');
        }
        return $has;
    }

    /**
     * Is the Certificate issued column shown?
     *
     * @return bool
     */
    public static function cert_column_shown(): bool {
        return false;
    }

    /**
     * Does an issued certificate count as completed: Certificate column shown and the setting
     * "Certificate issued counts as completed" on (it can be changed only while the column setting is on)?
     *
     * @return bool
     */
    public static function cert_counts_as_completed(): bool {
        return false;
    }

    /**
     * Field: first certificate issue time of the user in the course (NULL without mod_customcert).
     *
     * @param string $alias alias of the enrolments subquery
     * @return string
     */
    public static function cert_field_sql(string $alias): string {
        if (!self::has_customcert()) {
            return "NULL AS certissued";
        }
        return "(SELECT MIN(ci.timecreated) FROM {customcert_issues} ci
                   JOIN {customcert} cert ON cert.id = ci.customcertid
                  WHERE ci.userid = $alias.userid AND cert.course = $alias.courseid) AS certissued";
    }

    /**
     * Condition: the user completed the course (completion time set, or a certificate when that counts).
     *
     * @param string $alias alias of the enrolments subquery
     * @return string
     */
    public static function completed_sql(string $alias): string {
        $sql = "EXISTS (SELECT 1 FROM {course_completions} cc
                         WHERE cc.userid = $alias.userid AND cc.course = $alias.courseid AND cc.timecompleted > 0)";
        if (self::cert_counts_as_completed()) {
            $sql = "($sql OR EXISTS (SELECT 1 FROM {customcert_issues} ci
                                      JOIN {customcert} cert ON cert.id = ci.customcertid
                                     WHERE ci.userid = $alias.userid AND cert.course = $alias.courseid))";
        }
        return $sql;
    }

    /**
     * Status as SQL: 1 completed, 2 did not finish, 3 in progress (same rule as the class docblock).
     *
     * @param string $alias alias of the enrolments subquery
     * @param array $params named params, :$pname is added
     * @param string $pname param name for "now" (Moodle does not allow one named param twice in a query)
     * @return string
     */
    public static function status_sql(string $alias, array &$params, string $pname = 'statusnow'): string {
        $params[$pname] = time();
        return "CASE WHEN " . self::completed_sql($alias) . " THEN 1
                     WHEN $alias.enrolend > 0 AND $alias.enrolend < :$pname THEN 2
                     ELSE 3 END";
    }

    /**
     * Tabs above both report pages.
     *
     * @param string $current 'totals' or 'enrolments'
     * @return string
     */
    public static function tabs(string $current): string {
        global $OUTPUT;
        $tabs = [
            new \tabobject(
                'totals',
                new \moodle_url('/report/alucoursecompletion/totals.php'),
                get_string('tab_totals', 'report_alucoursecompletion')
            ),
            new \tabobject(
                'enrolments',
                new \moodle_url('/report/alucoursecompletion/index.php'),
                get_string('tab_enrolments', 'report_alucoursecompletion')
            ),
        ];
        return $OUTPUT->tabtree($tabs, $current);
    }

    /**
     * About tab: plugin name, version, supported Moodle versions, licence, and the documentation / support /
     * source links that are set (edition::ABOUT_LINKS).
     *
     * @return string HTML
     */
    public static function about_html(): string {
        $c = 'report_alucoursecompletion';
        $plugin = \core_plugin_manager::instance()->get_plugin_info($c);
        $table = new \html_table();
        $table->attributes['class'] = 'generaltable report-alucoursecompletion-about';
        $table->data[] = [get_string('about_plugin', $c), s(get_string('pluginname', $c)) . ' (' . $c . ')'];
        $table->data[] = [get_string('about_version', $c), s(($plugin->release ?? '') . ' (' . ($plugin->versiondisk ?? '') . ')')];
        $table->data[] = [get_string('about_moodle', $c), get_string('about_moodle_desc', $c)];
        $table->data[] = [get_string('about_copyright', $c), s(edition::COPYRIGHT)];
        $table->data[] = [get_string('about_licence', $c), \html_writer::link(
            'https://www.gnu.org/licenses/gpl-3.0.html',
            get_string('about_licence_desc', $c),
            ['target' => '_blank', 'rel' => 'noopener']
        )];
        foreach (edition::ABOUT_LINKS as $key => $url) {
            if ($url !== '') {
                $table->data[] = [get_string($key, $c), \html_writer::link(
                    $url,
                    s($url),
                    ['target' => '_blank', 'rel' => 'noopener']
                )];
            }
        }
        $html = \html_writer::table($table);
        return \html_writer::div($html, 'report-alucoursecompletion-aboutbox');
    }

    /**
     * Main title ("Settings | <plugin name> Report") and tabs, on top of every admin settings page of the plugin.
     * On admin/settings.php core prints the page name just above it: styles.css hides that heading there.
     *
     * @param string $current tab id, e.g. 'settings', 'about' or 'transfer'
     * @return string
     */
    public static function admin_tabs(string $current): string {
        global $OUTPUT;
        $title = $OUTPUT->heading(get_string(
            'settingstitle',
            'report_alucoursecompletion',
            get_string('pluginname', 'report_alucoursecompletion')
        ), 2, 'report-alucoursecompletion-admintitle');
        $tabs = [
            new \tabobject(
                'settings',
                new \moodle_url('/admin/settings.php', ['section' => 'reportalucoursecompletion']),
                get_string('tab_settings', 'report_alucoursecompletion')
            ),
        ];
        $tabs[] = new \tabobject(
            'about',
            new \moodle_url('/admin/settings.php', ['section' => 'reportalucoursecompletion_about']),
            get_string('tab_about', 'report_alucoursecompletion')
        );
        $tabs[] = new \tabobject(
            'transfer',
            new \moodle_url('/report/alucoursecompletion/settingstransfer.php'),
            get_string('settingstransfer', 'report_alucoursecompletion')
        );
        return $title . \html_writer::div($OUTPUT->tabtree($tabs, $current), 'report-alucoursecompletion-admintabs');
    }

    /**
     * Status key of a status number from status_sql().
     *
     * @param int $code
     * @return string
     */
    public static function status_key(int $code): string {
        return [1 => self::STATUS_COMPLETED, 2 => self::STATUS_NOTFINISHED][$code] ?? self::STATUS_INPROGRESS;
    }
}
