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
 * Course Totals data: one row per course (site and excluded courses left out) with student counts per status.
 * Counts use the same definitions as the Course Enrolments tab (helper).
 *
 * @package    report_alucoursecompletion
 * @copyright  2026 Alumisoft developer@alumisoft.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class totals {
    /** @var string Course status: visible, started and not ended. */
    public const COURSE_OPEN = 'open';
    /** @var string Course status: visible, start date in the future or end date passed. */
    public const COURSE_CLOSED = 'closed';
    /** @var string Course status: hidden. */
    public const COURSE_HIDDEN = 'hidden';
    /** @var string[] Course statuses, in display order. */
    public const COURSE_STATUSES = [self::COURSE_OPEN, self::COURSE_HIDDEN, self::COURSE_CLOSED];

    /** @var string[] Sortable columns => SQL (on the wrapped query q). */
    public const SORTS = [
        'coursename' => 'coursename',
        'timecreated' => 'timecreated',
        'enrolled' => 'enrolled',
        'inprogress' => 'inprogress',
        'completed' => 'completed',
        'notfinished' => 'notfinished',
    ];

    /**
     * Cleaned filters: year (1970-2100 or 0), status (course status or ''), search (course name text).
     *
     * @param array $filters
     * @return array
     */
    public static function clean_filters(array $filters): array {
        $year = (int)($filters['year'] ?? 0);
        $status = (string)($filters['status'] ?? '');
        return [
            'year' => ($year >= 1970 && $year <= 2100) ? $year : 0,
            'status' => in_array($status, self::COURSE_STATUSES, true) ? $status : '',
            'search' => trim(clean_param((string)($filters['search'] ?? ''), PARAM_TEXT)),
        ];
    }

    /**
     * SQL for the table: [fields, from, where, params]; columns id, coursename, timecreated, visible, startdate,
     * enddate, enrolled, completed, inprogress, notfinished.
     *
     * @param array $filters cleaned filters (clean_filters())
     * @return array
     */
    public static function get_sql(array $filters): array {
        global $DB;
        [$inner, $params] = helper::enrolments_sql();
        [$stusql, $stuparams] = helper::student_sql('r.userid', 'ctx.id');
        $params += $stuparams;
        $status = helper::status_sql('r', $params);
        $swhere = $stusql;
        if ($filters['year']) {
            [$start, $end] = helper::year_bounds($filters['year']);
            $swhere .= ' AND r.enroldate >= :ystart AND r.enroldate < :yend';
            $params['ystart'] = $start;
            $params['yend'] = $end;
        }
        $counts = "SELECT s.courseid, COUNT(1) AS enrolled,
                          SUM(CASE WHEN s.statuscode = 1 THEN 1 ELSE 0 END) AS completed,
                          SUM(CASE WHEN s.statuscode = 2 THEN 1 ELSE 0 END) AS notfinished,
                          SUM(CASE WHEN s.statuscode = 3 THEN 1 ELSE 0 END) AS inprogress
                     FROM (SELECT r.courseid, $status AS statuscode
                             FROM ($inner) r
                             JOIN {user} u ON u.id = r.userid AND u.deleted = 0
                             JOIN {context} ctx ON ctx.instanceid = r.courseid
                                  AND ctx.contextlevel = " . CONTEXT_COURSE . "
                            WHERE $swhere) s
                 GROUP BY s.courseid";

        $exclude = array_merge([SITEID], helper::get_excluded_courses());
        [$notin, $cparams] = $DB->get_in_or_equal($exclude, SQL_PARAMS_NAMED, 'cexc', false);
        $params += $cparams;
        $where = ["c.id $notin", helper::tracking_sql('c.id')];
        if ($filters['search'] !== '') {
            $where[] = $DB->sql_like('c.fullname', ':csearch', false, false);
            $params['csearch'] = '%' . $DB->sql_like_escape($filters['search']) . '%';
        }
        if ($filters['status'] !== '') {
            $closed = '(c.startdate > :cnow1 OR (c.enddate > 0 AND c.enddate < :cnow2))';
            $params['cnow1'] = $params['cnow2'] = time();
            $where[] = [
                self::COURSE_HIDDEN => 'c.visible = 0',
                self::COURSE_CLOSED => "c.visible = 1 AND $closed",
                self::COURSE_OPEN => "c.visible = 1 AND NOT $closed",
            ][$filters['status']];
        }
        $where = implode(' AND ', $where);
        $fields = "c.id, c.fullname AS coursename, c.timecreated, c.visible, c.startdate, c.enddate,
                   COALESCE(t.enrolled, 0) AS enrolled, COALESCE(t.completed, 0) AS completed,
                   COALESCE(t.inprogress, 0) AS inprogress, COALESCE(t.notfinished, 0) AS notfinished";
        return ['q.*', "(SELECT $fields FROM {course} c LEFT JOIN ($counts) t ON t.courseid = c.id WHERE $where) q",
            '1 = 1', $params];
    }

    /**
     * All rows for the filters (no paging), for exports and checks.
     *
     * @param array $filters raw filters (cleaned here)
     * @param string $sort key of SORTS
     * @param bool $desc
     * @return \stdClass[]
     */
    public static function get_rows(array $filters, string $sort = 'timecreated', bool $desc = true): array {
        global $DB;
        [$fields, $from, $where, $params] = self::get_sql(self::clean_filters($filters));
        $order = (self::SORTS[$sort] ?? self::SORTS['timecreated']) . ($desc ? ' DESC' : ' ASC') . ', id DESC';
        $rows = $DB->get_records_sql("SELECT $fields FROM $from WHERE $where ORDER BY $order", $params);
        foreach ($rows as $row) {
            self::add_computed($row);
        }
        return array_values($rows);
    }

    /**
     * Ints for the counts + course status key.
     *
     * @param \stdClass $row
     */
    public static function add_computed(\stdClass $row): void {
        foreach (['enrolled', 'completed', 'inprogress', 'notfinished'] as $k) {
            $row->$k = (int)$row->$k;
        }
        $row->coursestatus = self::course_status((int)$row->visible, (int)$row->startdate, (int)$row->enddate);
    }

    /**
     * Course status: hidden (not visible), closed (start date in the future or end date passed) or open.
     *
     * @param int $visible
     * @param int $startdate
     * @param int $enddate
     * @return string
     */
    public static function course_status(int $visible, int $startdate, int $enddate): string {
        if (!$visible) {
            return self::COURSE_HIDDEN;
        }
        $now = time();
        if ($startdate > $now || ($enddate > 0 && $enddate < $now)) {
            return self::COURSE_CLOSED;
        }
        return self::COURSE_OPEN;
    }
}
