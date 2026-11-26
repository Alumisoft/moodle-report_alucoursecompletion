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

use report_alucoursecompletion\local\edition;
use report_alucoursecompletion\local\helper;
use report_alucoursecompletion\reportmanager\provider;

/**
 * Course Enrolments table: one row per student and course, data from the provider.
 *
 * @package    report_alucoursecompletion
 * @copyright  2026 Alumisoft developer@alumisoft.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrolments_table extends \table_sql {
    /** @var provider */
    protected provider $provider;

    /** @var string HTML above the table, left (rows per page). */
    protected string $topleft = '';

    /** @var string HTML above the table, right (export buttons). */
    protected string $topright = '';

    /** @var bool PDF export grouped by course (PRO): one table per course, no Course column. */
    protected bool $grouppdf = false;

    /** @var float[] PDF column weights (provider pdfwidth), in column order. */
    protected array $pdfweights = [];

    /** @var string PDF summary box HTML (PRO, one course selected). */
    protected string $pdfsummary = '';

    /** @var bool Student details modal (PRO): rows clickable. */
    protected bool $details;

    /**
     * Constructor.
     *
     * @param \moodle_url $baseurl page url with the filters
     * @param array $filters cleaned filters (provider::clean_filters())
     */
    public function __construct(\moodle_url $baseurl, array $filters) {
        parent::__construct('report_alucoursecompletion_enrolments');
        $this->provider = new provider();
        $this->details = helper::student_details_enabled();

        $columns = $this->provider->get_columns($filters);
        $this->pdfweights = array_column($columns, 'pdfwidth');
        $this->define_columns(array_keys($columns));
        $this->define_headers(array_column($columns, 'title'));
        $this->define_baseurl($baseurl);

        [$sort, $dir] = helper::default_sort();
        $this->sortable(true, $sort, $dir);
        foreach ($columns as $key => $col) {
            if (!$col['sortable']) {
                $this->no_sorting($key);
            }
        }
        $this->collapsible(false);
        $this->pageable(true);
        $this->show_download_buttons_at([]);
        $this->set_attribute('class', 'generaltable table-hover report-alucoursecompletion-table');

        [$fields, $from, $where, $params] = $this->provider->get_sql($filters);
        $this->set_sql($fields, $from, $where, $params);
        $this->set_count_sql("SELECT COUNT(1) FROM $from WHERE $where", $params);
    }

    /**
     * Sort: column keys mapped to the provider's sort SQL; row id last so paging is stable.
     *
     * @return string
     */
    public function get_sql_sort() {
        $parts = [];
        foreach ($this->get_sort_columns() as $column => $dir) {
            if (!isset(provider::SORTS[$column])) {
                continue;
            }
            $d = $dir == SORT_ASC ? 'ASC' : 'DESC';
            foreach (explode(', ', provider::SORTS[$column]) as $field) {
                $parts[] = "$field $d";
            }
        }
        $parts[] = 'rowid ASC';
        if ($this->grouppdf) {
            array_unshift($parts, 'coursename ASC', 'courseid ASC');
        }
        return implode(', ', $parts);
    }

    /**
     * Rows + computed values (status key, progress, completion date).
     *
     * @param int $pagesize
     * @param bool $useinitialsbar
     */
    public function query_db($pagesize, $useinitialsbar = true) {
        parent::query_db($pagesize, false);
        foreach ($this->rawdata as $row) {
            provider::add_computed($row);
        }
    }

    /**
     * Status counts of the current filters (all rows, not only this page).
     *
     * @return int[] enrolled, completed, inprogress, notfinished
     */
    public function get_status_counts(): array {
        global $DB;
        $rows = $DB->get_records_sql_menu("SELECT statuscode, COUNT(1) AS n
                                             FROM {$this->sql->from}
                                            WHERE {$this->sql->where}
                                         GROUP BY statuscode", $this->sql->params);
        $counts = ['enrolled' => array_sum($rows)];
        foreach ([1, 3, 2] as $code) {
            $counts[helper::status_key($code)] = (int)($rows[$code] ?? 0);
        }
        return $counts;
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
        if ($format === 'pdf' && edition::pro_active()) {
            $keys = array_keys($this->columns);
            $i = array_search('coursename', $keys, true);
            $headers = $this->headers;
            unset($headers[$i], $keys[$i], $this->pdfweights[$i]);
            $this->define_columns(array_values($keys));
            $this->define_headers(array_values($headers));
            $this->pdfweights = array_values($this->pdfweights);
            $this->grouppdf = true;
        }
        $this->setup();
        $this->is_downloading($format, $filename, $title);
        \raise_memory_limit(MEMORY_EXTRA);
        \core_php_time_limit::raise();
        $this->out(0, false);
    }

    /**
     * PDF summary box (PRO, shown above the rows), e.g. the selected course's counts.
     *
     * @param string $html
     */
    public function set_pdf_summary(string $html): void {
        $this->pdfsummary = $html;
    }

    /**
     * PDF (PRO): own export class with column widths, groups and the summary box.
     *
     * @param \core_table\dataformat_export_format|null $exportclass
     * @return \core_table\dataformat_export_format|null
     */
    public function export_class_instance($exportclass = null) {
        if ($exportclass === null && $this->exportclass === null && $this->download === 'pdf' && edition::pro_active()) {
            $this->exportclass = new \report_alucoursecompletion\pro\pdf_export_format(
                $this,
                $this->pdfweights,
                $this->grouppdf
            );
            $this->exportclass->get_writer()->set_summary($this->pdfsummary);
            $this->exportclass->start_document($this->filename, $this->sheettitle);
        }
        return parent::export_class_instance($exportclass);
    }

    /**
     * Rows; grouped PDF starts a new group (course title + heading row) when the course changes.
     */
    public function build_table() {
        if (!$this->grouppdf || !($this->exportclass instanceof \report_alucoursecompletion\pro\pdf_export_format)) {
            parent::build_table();
            return;
        }
        $writer = $this->exportclass->get_writer();
        $courseid = null;
        foreach ($this->rawdata as $row) {
            if ($row->courseid !== $courseid) {
                $courseid = $row->courseid;
                $writer->start_group(format_string($row->coursename, true, ['escape' => false]));
            }
            $this->add_data_keyed($this->format_row($row), $this->get_row_class($row));
        }
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
     * "Showing x of y".
     *
     * @return string
     */
    protected function showing(): string {
        $total = (int)$this->totalrows;
        $count = $this->use_pages ? max(0, min($this->pagesize, $total - $this->currpage * $this->pagesize)) : $total;
        return \html_writer::span(get_string(
            'showing',
            'report_alucoursecompletion',
            (object)['count' => $count, 'total' => $total]
        ), 'text-muted');
    }

    /**
     * Top bar: rows per page + "Showing x of y" left, export buttons right.
     *
     * @return string
     */
    protected function top_bar(): string {
        $left = \html_writer::div(
            $this->topleft . \html_writer::div($this->showing(), 'ms-3 ml-3'),
            'd-flex align-items-center flex-wrap'
        );
        return \html_writer::div(
            $left . \html_writer::div($this->topright, 'd-flex'),
            'd-flex justify-content-between align-items-center flex-wrap mb-2'
        );
    }

    /**
     * Top bar (rows per page + showing) before the table.
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
     * Student name.
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_studentname($row) {
        if ($this->details && !$this->is_downloading()) {
            return \html_writer::span(s($row->studentname), 'report-alucoursecompletion-student', [
                'data-userid' => $row->userid,
                'data-courseid' => $row->courseid,
            ]);
        }
        return $this->text($row->studentname);
    }

    /**
     * Row class: clickable rows when the student details modal is on (PRO).
     *
     * @param \stdClass $row
     * @return string
     */
    public function get_row_class($row) {
        return $this->details ? 'report-alucoursecompletion-row' : '';
    }

    /**
     * Email.
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_email($row) {
        return $this->text($row->email);
    }

    /**
     * Course name.
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_coursename($row) {
        $escape = in_array((string)$this->is_downloading(), ['', 'pdf'], true);
        return format_string($row->coursename, true, ['escape' => $escape]);
    }

    /**
     * Enrol date.
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_enroldate($row) {
        return $this->text(helper::date($row->enroldate));
    }

    /**
     * Activity progress "done/total".
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_activityprogress($row) {
        return $row->progtotal ? $row->progdone . '/' . $row->progtotal : '';
    }

    /**
     * Progress percent with a bar.
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_progress($row) {
        if ($row->progress === null) {
            return $this->text(get_string('nocriteria', 'report_alucoursecompletion'));
        }
        $text = $row->progress . '%';
        if ($this->is_downloading()) {
            return $text;
        }
        $bar = \html_writer::div(\html_writer::div('', 'progress-bar', [
            'role' => 'progressbar',
            'style' => 'width: ' . $row->progress . '%',
            'aria-valuenow' => $row->progress, 'aria-valuemin' => 0, 'aria-valuemax' => 100,
        ]), 'progress report-alucoursecompletion-bar');
        return \html_writer::div($bar . \html_writer::span($text, 'small'), 'report-alucoursecompletion-progress');
    }

    /**
     * Status badge.
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_status($row) {
        $label = get_string('status_' . $row->status, 'report_alucoursecompletion');
        if ($this->is_downloading()) {
            return $this->text($label);
        }
        $classes = [
            helper::STATUS_COMPLETED => 'bg-success',
            helper::STATUS_NOTFINISHED => 'bg-danger',
            helper::STATUS_INPROGRESS => 'bg-info',
        ];
        return \html_writer::span($label, 'badge text-white ' . $classes[$row->status]);
    }

    /**
     * Time spent (PRO, Course dedication block): '' for a course without the block.
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_timespent($row) {
        return $this->text(\report_alucoursecompletion\pro\time_spent::cell($row));
    }

    /**
     * Course completed date.
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_timecompleted($row) {
        return $this->text(helper::date($row->completiondate));
    }

    /**
     * Certificate issued date (column only when a certificate counts as completed).
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_certissued($row) {
        return $this->text(helper::date($row->certissued));
    }

    /**
     * Last access in the course.
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_lastaccess($row) {
        return $this->text($row->lastaccess ? helper::date($row->lastaccess)
            : get_string('never', 'report_alucoursecompletion'));
    }
}
