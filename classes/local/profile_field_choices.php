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
 * Choices of the "Profile fields in student details" setting (PRO setting; shared so the free edition can show
 * it disabled): standard user fields and custom profile fields (key profile_field_<shortname>).
 *
 * @package    report_alucoursecompletion
 * @copyright  2026 Alumisoft developer@alumisoft.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class profile_field_choices {
    /** @var string[] Standard user fields offered: key => [string id, component]. */
    public const STANDARD = [
        'email' => ['email', 'moodle'],
        'username' => ['username', 'moodle'],
        'idnumber' => ['idnumber', 'moodle'],
        'institution' => ['institution', 'moodle'],
        'department' => ['department', 'moodle'],
        'phone1' => ['phone1', 'moodle'],
        'phone2' => ['phone2', 'moodle'],
        'address' => ['address', 'moodle'],
        'city' => ['city', 'moodle'],
        'country' => ['country', 'moodle'],
        'timecreated' => ['accountcreated', 'report_alucoursecompletion'],
        'lastaccess' => ['lastsiteaccess', 'moodle'],
    ];

    /** @var string[] Default selection (the fields shown before this setting existed). */
    public const DEFAULT = ['email', 'timecreated'];

    /** @var string Key prefix of custom profile fields. */
    public const CUSTOM_PREFIX = 'profile_field_';

    /**
     * Choices for the setting: standard fields, then custom fields in their profile order.
     *
     * @return string[] key => label
     */
    public static function get(): array {
        global $DB;
        $choices = [];
        foreach (self::STANDARD as $key => [$id, $component]) {
            $choices[$key] = get_string($id, $component);
        }
        $fields = $DB->get_records_sql('SELECT f.id, f.shortname, f.name
                                          FROM {user_info_field} f
                                          JOIN {user_info_category} c ON c.id = f.categoryid
                                      ORDER BY c.sortorder, f.sortorder');
        foreach ($fields as $f) {
            $choices[self::CUSTOM_PREFIX . $f->shortname] = format_string($f->name, true, ['escape' => false]) .
                ' (' . get_string('customfield', 'report_alucoursecompletion') . ')';
        }
        return $choices;
    }
}
