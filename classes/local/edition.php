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
 * Plugin edition and About tab data.
 *
 * @package    report_alucoursecompletion
 * @copyright  2026 Alumisoft developer@alumisoft.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class edition {
    /** @var string Copyright holder on the About tab, plain text (no link) - user decision 2026-11-26. */
    public const COPYRIGHT = 'Alumisoft - developer@alumisoft.com';

    /**
     * @var string[] About tab links, label string key => URL. An empty URL is not shown. The Marketplace needs a
     * documentation URL and a public issue tracker.
     */
    public const ABOUT_LINKS = [
        'about_docs' => 'https://github.com/Alumisoft/moodle-report_alucoursecompletion#readme',
        'about_support' => 'https://github.com/Alumisoft/moodle-report_alucoursecompletion/issues',
        'about_source' => 'https://github.com/Alumisoft/moodle-report_alucoursecompletion',
    ];
}
