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
 * Admin tabs at the top of the settings page (no stored value; a heading, so left out of the settings export).
 *
 * Its own output: a heading's description goes through markdown_to_html, which can break the tab HTML.
 *
 * @package    report_alucoursecompletion
 * @copyright  2026 Alumisoft developer@alumisoft.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_setting_tabs extends \admin_setting_heading {
    /** @var string Current tab id (helper::admin_tabs). */
    protected string $current;

    /**
     * Constructor.
     *
     * @param string $name Setting name ('report_alucoursecompletion/...')
     * @param string $current Current tab id
     */
    public function __construct(string $name, string $current) {
        $this->current = $current;
        parent::__construct($name, '', '');
    }

    /**
     * The tabs, with the current one highlighted.
     *
     * @param mixed $data Unused
     * @param string $query Admin search text (unused)
     * @return string
     */
    public function output_html($data, $query = '') {
        return helper::admin_tabs($this->current);
    }
}
