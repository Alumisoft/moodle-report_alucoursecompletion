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

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/adminlib.php');

/**
 * Checkbox setting that can be changed only while another checkbox on the page is ticked (disabled otherwise,
 * amd/src/dependentcheckbox.js). Moodle admin settings have hide_if only (checked 4.5 and 5.3), no "disabled if".
 * A disabled checkbox is not submitted, so its stored value is kept; the code that uses it must check both settings.
 *
 * @package    report_alucoursecompletion
 * @copyright  2026 Alumisoft developer@alumisoft.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_setting_dependentcheckbox extends \admin_setting_configcheckbox {
    /** @var string Element id of the checkbox this one depends on. */
    protected string $dependson;

    /**
     * Constructor.
     *
     * @param string $name Setting name ('report_alucoursecompletion/...')
     * @param string $visiblename Label
     * @param string $description Description
     * @param string $defaultsetting Default '0' or '1'
     * @param string $dependson Element id of the checkbox it depends on (e.g. id_s_report_alucoursecompletion_customcertcolumn)
     */
    public function __construct(
        string $name,
        string $visiblename,
        string $description,
        string $defaultsetting,
        string $dependson
    ) {
        $this->dependson = $dependson;
        parent::__construct($name, $visiblename, $description, $defaultsetting);
    }

    /**
     * The checkbox, plus the script that disables it while the other checkbox is not ticked.
     *
     * @param mixed $data
     * @param string $query
     * @return string
     */
    public function output_html($data, $query = '') {
        global $PAGE;
        $PAGE->requires->js_call_amd(
            'report_alucoursecompletion/dependentcheckbox',
            'init',
            [$this->dependson, $this->get_full_name()]
        );
        return parent::output_html($data, $query);
    }
}
