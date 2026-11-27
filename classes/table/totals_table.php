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

namespace report_alucoursecompletion\table;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/tablelib.php');

use report_alucoursecompletion\local\helper;
use report_alucoursecompletion\local\totals;

/**
 * Course Totals table: one row per course with student counts per status.
 *
 * @package    report_alucoursecompletion
 * @copyright  2026 Alumisoft developer@alumisoft.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class totals_table extends \table_sql {
    /** @var int Enrol year filter, passed on to the Course Enrolments tab. */
    protected int $year;

    /** @var string HTML above the table, left (rows per page). */
    protected string $topleft = '';

    /** @var string HTML above the table, right (export buttons). */
    protected string $topright = '';

    /**
     * Constructor.
     *
     * @param \moodle_url $baseurl page url with the filters
     * @param array $filters cleaned filters (totals::clean_filters())
     */
    public function __construct(\moodle_url $baseurl, array $filters) {
        parent::__construct('report_alucoursecompletion_totals');
        $s = fn(string $id): string => get_string($id, 'report_alucoursecompletion');
        $columns = [
            'coursename' => $s('course'),
            'timecreated' => $s('created'),
            'coursestatus' => $s('status'),
            'enrolled' => $s('sum_enrolled'),
            'inprogress' => $s('sum_inprogress'),
            'completed' => $s('sum_completed'),
            'notfinished' => $s('sum_notfinished'),
        ];
        $this->define_columns(array_keys($columns));
        $this->define_headers(array_values($columns));
        $this->define_baseurl($baseurl);

        $this->sortable(true, 'timecreated', SORT_DESC);
        $this->no_sorting('coursestatus');
        foreach (['enrolled', 'inprogress', 'completed', 'notfinished'] as $col) {
            $this->column_class($col, 'text-end text-right');
        }
        $this->collapsible(false);
        $this->pageable(true);
        $this->show_download_buttons_at([]);
        $this->set_attribute('class', 'generaltable table-hover report-alucoursecompletion-totals');

        $this->year = $filters['year'];
        [$fields, $from, $where, $params] = totals::get_sql($filters);
        $this->set_sql($fields, $from, $where, $params);
        $this->set_count_sql("SELECT COUNT(1) FROM $from WHERE $where", $params);
    }

    /**
     * Sort: column keys mapped to totals::SORTS; newest course last so paging is stable.
     *
     * @return string
     */
    public function get_sql_sort() {
        $parts = [];
        foreach ($this->get_sort_columns() as $column => $dir) {
            if (isset(totals::SORTS[$column])) {
                $parts[] = totals::SORTS[$column] . ($dir == SORT_ASC ? ' ASC' : ' DESC');
            }
        }
        $parts[] = 'id DESC';
        return implode(', ', $parts);
    }

    /**
     * Rows + computed values (int counts, course status).
     *
     * @param int $pagesize
     * @param bool $useinitialsbar
     */
    public function query_db($pagesize, $useinitialsbar = true) {
        parent::query_db($pagesize, false);
        foreach ($this->rawdata as $row) {
            totals::add_computed($row);
        }
    }

    /**
     * Send all rows of the current filters as a file and stop.
     * setup() runs first: the download closes the session, and setup() stores the table preferences in it
     * (debug "mutated the session after it was closed" otherwise).
     *
     * @param string $format data format, e.g. 'csv'
     * @param string $filename without extension
     * @param string $title sheet title
     */
    public function download(string $format, string $filename, string $title): void {
        $this->setup();
        $this->is_downloading($format, $filename, $title);
        \raise_memory_limit(MEMORY_EXTRA);
        \core_php_time_limit::raise();
        $this->out(0, false);
    }

    /**
     * Cell text: escaped for the screen and PDF (HTML), plain for CSV / Excel.
     *
     * @param string $text
     * @return string
     */
    protected function text(string $text): string {
        return in_array((string)$this->is_downloading(), ['', 'pdf'], true) ? s($text) : $text;
    }

    /**
     * HTML shown above the table on the left (rows per page selector).
     *
     * @param string $html
     */
    public function set_top_left(string $html): void {
        $this->topleft = $html;
    }

    /**
     * HTML shown above the table on the right (export buttons).
     *
     * @param string $html
     */
    public function set_top_right(string $html): void {
        $this->topright = $html;
    }

    /**
     * Top bar: rows per page + "Showing x of y" left, export buttons right.
     *
     * @return string
     */
    protected function top_bar(): string {
        $total = (int)$this->totalrows;
        $count = $this->use_pages ? max(0, min($this->pagesize, $total - $this->currpage * $this->pagesize)) : $total;
        $showing = \html_writer::span(get_string(
            'showing',
            'report_alucoursecompletion',
            (object)['count' => $count, 'total' => $total]
        ), 'text-muted');
        $left = \html_writer::div(
            $this->topleft . \html_writer::div($showing, 'ms-3 ml-3'),
            'd-flex align-items-center flex-wrap'
        );
        return \html_writer::div(
            $left . \html_writer::div($this->topright, 'd-flex'),
            'd-flex justify-content-between align-items-center flex-wrap mb-2'
        );
    }

    /**
     * Top bar before the table.
     */
    public function start_html() {
        echo $this->top_bar();
        parent::start_html();
    }

    /**
     * Top bar also when there are no rows.
     */
    public function print_nothing_to_display() {
        echo $this->top_bar();
        parent::print_nothing_to_display();
    }

    /**
     * Course name: link to the Course Enrolments tab for the course (and the same year).
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_coursename($row) {
        if ($this->is_downloading()) {
            return format_string($row->coursename, true, ['escape' => $this->is_downloading() === 'pdf']);
        }
        $name = format_string($row->coursename);
        $url = new \moodle_url(
            '/report/alucoursecompletion/index.php',
            array_filter(['courseid' => $row->id, 'year' => $this->year])
        );
        return \html_writer::link($url, $name);
    }

    /**
     * Course created date.
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_timecreated($row) {
        return $this->text(helper::date($row->timecreated));
    }

    /**
     * Course status badge.
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_coursestatus($row) {
        $label = get_string('course_' . $row->coursestatus, 'report_alucoursecompletion');
        if ($this->is_downloading()) {
            return $this->text($label);
        }
        $classes = [
            totals::COURSE_OPEN => 'bg-success text-white',
            totals::COURSE_CLOSED => 'bg-secondary text-white',
            totals::COURSE_HIDDEN => 'bg-warning text-dark',
        ];
        return \html_writer::span($label, 'badge ' . $classes[$row->coursestatus]);
    }
}
