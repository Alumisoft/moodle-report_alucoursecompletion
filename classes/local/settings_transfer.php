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
 * Settings export / import (JSON file) - e.g. to keep the settings over an uninstall (uninstall removes the
 * settings), or to copy them to another site.
 *
 * Known settings = the settings on this plugin's settings page (admin tree), so an edition only imports its own
 * settings; values are written through the admin settings themselves (same validation as the settings page) and
 * read back to report values that were not accepted. Excluded courses travel with their short names and are mapped
 * to this site's course ids.
 *
 * @package    report_alucoursecompletion
 * @copyright  2026 Alumisoft developer@alumisoft.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class settings_transfer {
    /** @var string Component. */
    public const COMPONENT = 'report_alucoursecompletion';

    /** @var int File format version. */
    public const FORMAT = 1;

    /** @var int Largest accepted file (bytes). */
    public const MAXBYTES = 102400;

    /** @var string Result: value written. */
    public const RESULT_APPLIED = 'applied';
    /** @var string Result: same value already set. */
    public const RESULT_UNCHANGED = 'unchanged';
    /** @var string Result: setting not known by this edition / version. */
    public const RESULT_UNKNOWN = 'unknown';
    /** @var string Result: value not accepted. */
    public const RESULT_INVALID = 'invalid';

    /** @var string[] Settings never exported / imported. */
    public const NOT_TRANSFERRED = [
    ];

    /** @var string[] Admin settings pages exported / imported. */
    public const PAGES = [
        'reportalucoursecompletion',
    ];

    /**
     * Edition of this code, written to the export file.
     *
     * @return string
     */
    public static function edition(): string {
        $edition = 'free';
        return $edition;
    }

    /**
     * This plugin's settings from the admin tree, name => admin_setting.
     *
     * @return \admin_setting[]
     */
    public static function get_settings(): array {
        global $CFG;
        require_once($CFG->libdir . '/adminlib.php');
        $root = admin_get_root(false, true);
        $settings = [];
        foreach (self::PAGES as $section) {
            $page = $root->locate($section);
            if (!($page instanceof \admin_settingpage)) {
                continue;
            }
            foreach ($page->settings as $setting) {
                if (
                    $setting->plugin === self::COMPONENT && !($setting instanceof \admin_setting_heading)
                    && !in_array($setting->name, self::NOT_TRANSFERRED, true)
                ) {
                    $settings[$setting->name] = $setting;
                }
            }
        }
        return $settings;
    }

    /**
     * Export data: format, component, edition, version, release, time, settings (stored values), course map.
     *
     * @return array
     */
    public static function export(): array {
        global $DB;
        $plugin = \core_plugin_manager::instance()->get_plugin_info(self::COMPONENT);
        $values = [];
        foreach (array_keys(self::get_settings()) as $name) {
            $value = get_config(self::COMPONENT, $name);
            $values[$name] = $value === false ? null : (string)$value;
        }
        $courses = [];
        $ids = array_filter(array_map('intval', explode(',', (string)($values['excludedcourses'] ?? ''))));
        if ($ids) {
            $courses = $DB->get_records_list('course', 'id', $ids, '', 'id, shortname');
            $courses = array_map(fn($c) => $c->shortname, $courses);
        }
        return [
            'format' => self::FORMAT,
            'component' => self::COMPONENT,
            'edition' => self::edition(),
            'version' => (int)$plugin->versiondisk,
            'release' => (string)$plugin->release,
            'exported' => time(),
            'settings' => $values,
            'courses' => (object)$courses,
        ];
    }

    /**
     * Export as JSON text.
     *
     * @return string
     */
    public static function export_json(): string {
        return json_encode(self::export(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Read and check an export file.
     *
     * @param string $json file content
     * @return array decoded data
     * @throws \moodle_exception invalid file (string importerror_*)
     */
    public static function parse(string $json): array {
        if (strlen($json) > self::MAXBYTES) {
            throw new \moodle_exception('importerror_size', self::COMPONENT);
        }
        $data = json_decode($json, true);
        if (!is_array($data)) {
            throw new \moodle_exception('importerror_json', self::COMPONENT);
        }
        if (($data['component'] ?? '') !== self::COMPONENT || (int)($data['format'] ?? 0) !== self::FORMAT) {
            throw new \moodle_exception('importerror_component', self::COMPONENT);
        }
        if (!isset($data['settings']) || !is_array($data['settings'])) {
            throw new \moodle_exception('importerror_json', self::COMPONENT);
        }
        return $data;
    }

    /**
     * Import settings from parsed data (parse()).
     *
     * @param array $data
     * @return array name => ['result' => RESULT_*, 'value' => shown value, 'note' => text]
     */
    public static function import(array $data): array {
        $settings = self::get_settings();
        $results = [];
        foreach ($data['settings'] as $name => $value) {
            $name = (string)$name;
            if (!isset($settings[$name])) {
                $shown = in_array($name, self::NOT_TRANSFERRED, true) ? '' : self::show($value);
                $results[$name] = ['result' => self::RESULT_UNKNOWN, 'value' => $shown, 'note' => ''];
                continue;
            }
            $setting = $settings[$name];
            if ($value !== null && !is_scalar($value)) {
                $results[$name] = ['result' => self::RESULT_INVALID, 'value' => '', 'note' => ''];
                continue;
            }
            $value = $value === null ? null : (string)$value;
            $note = '';

            if ($setting instanceof \admin_setting_configmultiselect) {
                $ids = array_filter(array_map('trim', explode(',', (string)$value)), 'strlen');
                if ($name === 'excludedcourses') {
                    [$ids, $missing] = self::map_courses($ids, (array)($data['courses'] ?? []));
                    if ($missing) {
                        $note = get_string('importcoursesmissing', self::COMPONENT, implode(', ', $missing));
                    }
                } else {
                    $setting->load_choices();
                    $missing = array_diff($ids, array_map('strval', array_keys((array)$setting->choices)));
                    if ($missing) {
                        $ids = array_values(array_diff($ids, $missing));
                        $note = get_string('importvaluesmissing', self::COMPONENT, implode(', ', $missing));
                    }
                }
                $input = $ids;
                $want = implode(',', $ids);
            } else if ($setting instanceof \admin_setting_configcheckbox) {
                if (!in_array($value, [(string)$setting->yes, (string)$setting->no], true)) {
                    $results[$name] = ['result' => self::RESULT_INVALID, 'value' => self::show($value), 'note' => ''];
                    continue;
                }
                $input = $want = $value;
            } else {
                $input = $want = (string)$value;
            }

            $old = get_config(self::COMPONENT, $name);
            if ($old !== false && (string)$old === $want) {
                $results[$name] = ['result' => self::RESULT_UNCHANGED, 'value' => self::show($want), 'note' => $note];
                continue;
            }
            $error = $setting->write_setting($input);
            $now = get_config(self::COMPONENT, $name);
            if ($error !== '' || $now === false || (string)$now !== $want) {
                $results[$name] = ['result' => self::RESULT_INVALID, 'value' => self::show($value),
                    'note' => trim($error . ' ' . $note)];
                continue;
            }
            $results[$name] = ['result' => self::RESULT_APPLIED, 'value' => self::show($want), 'note' => $note];
        }
        foreach (array_diff_key($settings, $data['settings']) as $name => $notused) {
            $results[$name] = ['result' => self::RESULT_UNCHANGED, 'value' => self::show(get_config(self::COMPONENT, $name)),
                'note' => get_string('importnotinfile', self::COMPONENT)];
        }
        return $results;
    }

    /**
     * Course ids of the file mapped to this site by short name (ids kept when the file has no short names).
     *
     * @param string[] $ids course ids from the file
     * @param array $shortnames file course map id => short name
     * @return array [this site's ids, short names / ids not found]
     */
    protected static function map_courses(array $ids, array $shortnames): array {
        global $DB;
        $found = [];
        $missing = [];
        foreach ($ids as $id) {
            if (isset($shortnames[$id])) {
                $local = $DB->get_field('course', 'id', ['shortname' => (string)$shortnames[$id]]);
                $label = (string)$shortnames[$id];
            } else {
                $local = $DB->record_exists('course', ['id' => (int)$id]) ? (int)$id : false;
                $label = 'id ' . $id;
            }
            if ($local && (int)$local !== SITEID) {
                $found[] = (string)$local;
            } else {
                $missing[] = $label;
            }
        }
        return [array_values(array_unique($found)), $missing];
    }

    /**
     * Value for display.
     *
     * @param mixed $value
     * @return string
     */
    protected static function show($value): string {
        if ($value === null || $value === false) {
            return '';
        }
        return is_scalar($value) ? (string)$value : json_encode($value);
    }
}
