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

namespace report_alucoursecompletion\reportmanager;

use report_alucoursecompletion\local\edition;
use report_alucoursecompletion\local\helper;

/**
 * Data provider, contract v1 (plan/1_plan_report_pack.txt section D).
 *
 * The report's own pages read their data here, and so will the Report Manager later, so screen and exports
 * always show the same rows. Rows: one per student and course (Course Enrolments).
 *
 * @package    report_alucoursecompletion
 * @copyright  2026 Alumisoft developer@alumisoft.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider {
    /** @var string[] Sort keys => SQL. Default: enrol date, newest first. */
    public const SORTS = [
        'enroldate' => 'enroldate',
        'studentname' => 'lastname, firstname',
        'lastaccess' => 'lastaccess',
        'progress' => 'progresssort',
        'status' => 'statuscode',
    ];

    /**
     * Contract version this provider implements.
     *
     * @return int
     */
    public static function contract_version(): int {
        return 1;
    }

    /**
     * Report name.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('pluginname', 'report_alucoursecompletion');
    }

    /**
     * Short description.
     *
     * @return string
     */
    public function get_description(): string {
        return get_string('description', 'report_alucoursecompletion');
    }

    /**
     * Icon: [pix identifier, component].
     *
     * @return string[]
     */
    public function get_icon(): array {
        return ['i/report', 'core'];
    }

    /**
     * Capability needed (system context).
     *
     * @return string
     */
    public function get_capability(): string {
        return 'report/alucoursecompletion:view';
    }

    /**
     * Can the report run on this site?
     *
     * @return bool
     */
    public function is_available(): bool {
        return true;
    }

    /**
     * Filters: key => [type, label]. Types are the standard filter types of the contract.
     *
     * @return array
     */
    public function get_filters(): array {
        $s = fn(string $id): string => get_string($id, 'report_alucoursecompletion');
        return [
            'courseid' => ['type' => 'course', 'label' => $s('course')],
            'year' => ['type' => 'year', 'label' => $s('year')],
            'status' => ['type' => 'status', 'label' => $s('status'), 'options' => helper::STATUSES],
            'search' => ['type' => 'search', 'label' => $s('search')],
        ];
    }

    /**
     * Columns: key => [title, sortable, pdfwidth (% of the page width)].
     * Optional columns: certificate issued (setting), time spent (PRO, Course dedication block used by the
     * selected course - all courses: by at least one reported course).
     *
     * @param array $filters cleaned filters (only courseid is used)
     * @return array
     */
    public function get_columns(array $filters = []): array {
        $s = fn(string $id): string => get_string($id, 'report_alucoursecompletion');
        $col = fn(string $title, bool $sortable, int $width): array =>
            ['title' => $title, 'sortable' => $sortable, 'pdfwidth' => $width];
        $columns = [
            'studentname' => $col($s('student'), true, 16),
            'email' => $col($s('email'), false, 18),
            'coursename' => $col($s('course'), false, 16),
            'enroldate' => $col($s('enroldate'), true, 9),
            'activityprogress' => $col($s('activityprogress'), false, 8),
            'progress' => $col($s('progress'), true, 7),
            'status' => $col($s('status'), true, 9),
            'timecompleted' => $col($s('completedon'), false, 9),
            'lastaccess' => $col($s('lastaccess'), true, 8),
        ];
        if (helper::cert_column_shown()) {
            $columns = array_slice($columns, 0, 8, true) + ['certissued' => $col($s('certificateissued'), false, 9)]
                + array_slice($columns, 8, null, true);
        }
        if (self::timespent_shown((int)($filters['courseid'] ?? 0))) {
            $pos = array_search('status', array_keys($columns), true) + 1;
            $columns = array_slice($columns, 0, $pos, true) + ['timespent' => $col($s('timespent'), false, 7)]
                + array_slice($columns, $pos, null, true);
        }
        return $columns;
    }

    /**
     * Time spent column shown (PRO active + Course dedication block used, see get_columns())?
     *
     * @param int $courseid selected course, 0 = all
     * @return bool
     */
    public static function timespent_shown(int $courseid): bool {
        return edition::pro_active()
            && \report_alucoursecompletion\pro\time_spent::shown($courseid, array_keys(helper::get_course_options()));
    }

    /**
     * Filter values checked against their allowed values; unknown keys dropped.
     *
     * @param array $filters raw values
     * @return array courseid, year, status, search
     */
    public function clean_filters(array $filters): array {
        $courseid = (int)($filters['courseid'] ?? 0);
        if ($courseid && !array_key_exists($courseid, helper::get_course_options())) {
            $courseid = 0;
        }
        $year = (int)($filters['year'] ?? 0);
        if ($year < 1970 || $year > 2100) {
            $year = 0;
        }
        $status = (string)($filters['status'] ?? '');
        if (!in_array($status, helper::STATUSES, true)) {
            $status = '';
        }
        $search = trim(clean_param((string)($filters['search'] ?? ''), PARAM_TEXT));
        return ['courseid' => $courseid, 'year' => $year, 'status' => $status, 'search' => $search];
    }

    /**
     * SQL parts of the rows for the filters (shared with the on-screen table).
     *
     * @param array $filters cleaned filters (clean_filters())
     * @return array [fields, from, where, params]
     */
    public function get_sql(array $filters): array {
        global $CFG, $DB;
        require_once($CFG->libdir . '/completionlib.php');
        [$inner, $params] = helper::enrolments_sql($filters['courseid']);
        [$stusql, $stuparams] = helper::student_sql('r.userid', 'ctx.id');
        $params += $stuparams;
        $status = helper::status_sql('r', $params);
        $name = $DB->sql_concat('u.firstname', "' '", 'u.lastname');
        $rowid = $DB->sql_concat('r.courseid', "'_'", 'r.userid');

        $fields = "$rowid AS rowid, r.courseid, r.userid, r.enroldate, r.enrolend,
                   $name AS studentname, u.firstname, u.lastname, u.email, c.fullname AS coursename,
                   (SELECT ula.timeaccess FROM {user_lastaccess} ula
                     WHERE ula.userid = r.userid AND ula.courseid = r.courseid) AS lastaccess,
                   (SELECT cc.timecompleted FROM {course_completions} cc
                     WHERE cc.userid = r.userid AND cc.course = r.courseid) AS timecompleted,
                   " . helper::cert_field_sql('r') . ",
                   (SELECT COUNT(DISTINCT pcc.moduleinstance) FROM {course_completion_criteria} pcc
                     WHERE pcc.course = r.courseid AND pcc.criteriatype = :crittype1) AS progtotal,
                   (SELECT COUNT(DISTINCT pcc.moduleinstance) FROM {course_completion_criteria} pcc
                      JOIN {course_completion_crit_compl} pccc ON pccc.criteriaid = pcc.id
                           AND pccc.userid = r.userid AND pccc.timecompleted > 0
                     WHERE pcc.course = r.courseid AND pcc.criteriatype = :crittype2) AS progdone,
                   $status AS statuscode";
        if (edition::pro_active() && \report_alucoursecompletion\pro\time_spent::get_courses()) {
            $fields .= ', ' . \report_alucoursecompletion\pro\time_spent::field_sql('r');
        }
        $params['crittype1'] = COMPLETION_CRITERIA_TYPE_ACTIVITY;
        $params['crittype2'] = COMPLETION_CRITERIA_TYPE_ACTIVITY;

        $from = "($inner) r
                 JOIN {user} u ON u.id = r.userid AND u.deleted = 0
                 JOIN {course} c ON c.id = r.courseid
                 JOIN {context} ctx ON ctx.instanceid = c.id AND ctx.contextlevel = " . CONTEXT_COURSE;

        $where = [$stusql];
        if ($filters['year']) {
            [$start, $end] = helper::year_bounds($filters['year']);
            $where[] = 'r.enroldate >= :ystart AND r.enroldate < :yend';
            $params['ystart'] = $start;
            $params['yend'] = $end;
        }
        if ($filters['status'] !== '') {
            $code = array_search($filters['status'], [1 => helper::STATUS_COMPLETED, 2 => helper::STATUS_NOTFINISHED,
                3 => helper::STATUS_INPROGRESS], true);
            $where[] = helper::status_sql('r', $params, 'statusnow2') . ' = :statuscode';
            $params['statuscode'] = $code;
        }
        if ($filters['search'] !== '') {
            $terms = array_values(array_unique(array_filter(
                array_map('trim', explode(',', $filters['search'])),
                'strlen'
            )));
            $or = [];
            foreach ($terms as $i => $term) {
                $like = '%' . $DB->sql_like_escape($term) . '%';
                $or[] = $DB->sql_like('u.firstname', ":s1_$i", false, false) . ' OR '
                    . $DB->sql_like('u.lastname', ":s2_$i", false, false) . ' OR '
                    . $DB->sql_like($name, ":s3_$i", false, false);
                $params["s1_$i"] = $like;
                $params["s2_$i"] = $like;
                $params["s3_$i"] = $like;
            }
            $where[] = '(' . implode(' OR ', $or) . ')';
        }
        $where = implode(' AND ', $where);
        return [
            "q.*, CASE WHEN q.progtotal = 0 THEN -1 ELSE FLOOR(q.progdone * 100 / q.progtotal) END AS progresssort",
            "(SELECT $fields FROM $from WHERE $where) q",
            '1 = 1',
            $params,
        ];
    }

    /**
     * All rows for the filters (no paging), for exports and the manager.
     *
     * @param array $filters raw filter values (checked here)
     * @param string $sort sort key (SORTS), default enrol date
     * @param bool $desc descending
     * @return \stdClass[] rows with raw values + status (key) + progress (percent or null = no criteria)
     */
    public function get_rows(array $filters, string $sort = 'enroldate', bool $desc = true): array {
        global $DB;
        [$fields, $from, $where, $params] = $this->get_sql($this->clean_filters($filters));
        $order = self::SORTS[$sort] ?? self::SORTS['enroldate'];
        $dir = $desc ? 'DESC' : 'ASC';
        $order = implode(', ', array_map(fn($f) => "$f $dir", explode(', ', $order))) . ', rowid ASC';
        $rows = $DB->get_records_sql("SELECT $fields FROM $from WHERE $where ORDER BY $order", $params);
        foreach ($rows as $row) {
            self::add_computed($row);
        }
        return array_values($rows);
    }

    /**
     * Adds status (key), progress (percent, null = no activity criteria) and completiondate to a row.
     *
     * @param \stdClass $row
     */
    public static function add_computed(\stdClass $row): void {
        $row->status = helper::status_key((int)$row->statuscode);
        $row->progtotal = (int)$row->progtotal;
        $row->progdone = (int)$row->progdone;
        $row->progress = $row->progtotal ? (int)floor($row->progdone * 100 / $row->progtotal) : null;
        $row->completiondate = (!empty($row->certissued) && helper::cert_counts_as_completed())
            ? (int)$row->certissued : (empty($row->timecompleted) ? null : (int)$row->timecompleted);
    }

    /**
     * Link to the report's own page with these filters.
     *
     * @param array $filters
     * @return \moodle_url
     */
    public function get_url(array $filters = []): \moodle_url {
        return new \moodle_url('/report/alucoursecompletion/index.php', array_filter($this->clean_filters($filters)));
    }
}
