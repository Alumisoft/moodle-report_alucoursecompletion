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
 * Web service functions. In both editions; the function is registered only when its PRO class is installed
 * (classes/pro/, not in the free zip), so the free edition has no web service. Read on install / upgrade:
 * free -> PRO is an upgrade (PRO version one day later), so the function is added then.
 *
 * @package    report_alucoursecompletion
 * @copyright  2026 Alumisoft developer@alumisoft.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [];
if (class_exists('\report_alucoursecompletion\pro\external\get_student_details')) {
    $functions['report_alucoursecompletion_get_student_details'] = [
        'classname' => '\report_alucoursecompletion\pro\external\get_student_details',
        'description' => 'Student profile, course progress and activity list for the report modal.',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'report/alucoursecompletion:view',
    ];
}
