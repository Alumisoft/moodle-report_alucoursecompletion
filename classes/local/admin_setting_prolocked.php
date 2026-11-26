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
 * Free edition: a PRO setting shown disabled (core textarea / multiselect template with readonly = disabled).
 * A heading underneath: stores nothing, is not submitted and is left out of the settings export.
 *
 * @package    report_alucoursecompletion
 * @copyright  2026 Alumisoft developer@alumisoft.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_setting_prolocked extends \admin_setting_heading {
    /** @var string Field type: 'textarea', 'text', 'multiselect' or 'checkbox'. */
    protected string $type;

    /** @var string[] Multiselect choices: value => label. */
    protected array $choices;

    /** @var string[] Multiselect values shown selected. */
    protected array $selected;

    /** @var bool Add the "PRO edition only" note. */
    protected bool $pronote;

    /**
     * Constructor.
     *
     * @param string $name Setting name ('report_alucoursecompletion/...'), not the PRO setting's own name
     * @param string $visiblename Label
     * @param string $description Description
     * @param string $type 'textarea', 'text', 'multiselect' or 'checkbox'
     * @param string[] $choices Multiselect choices: value => label
     * @param string[] $selected Multiselect values shown selected
     * @param bool $pronote Add the "PRO edition only" note (false: a placeholder field, e.g. "coming soon")
     */
    public function __construct(
        string $name,
        string $visiblename,
        string $description,
        string $type,
        array $choices = [],
        array $selected = [],
        bool $pronote = true
    ) {
        $this->type = $type;
        $this->pronote = $pronote;
        $this->choices = $choices;
        $this->selected = $selected;
        parent::__construct($name, $visiblename, $description);
    }

    /**
     * The disabled field with its label, description and a "pro edition only" note.
     *
     * @param mixed $data Unused
     * @param string $query Admin search text
     * @return string
     */
    public function output_html($data, $query = '') {
        global $OUTPUT;
        $context = (object)['id' => $this->get_id(), 'name' => $this->get_full_name(), 'readonly' => true];
        if ($this->type === 'multiselect') {
            $context->size = min(10, max(3, count($this->choices)));
            $context->options = [];
            foreach ($this->choices as $value => $label) {
                $context->options[] = ['name' => $label, 'value' => $value,
                    'selected' => in_array((string)$value, $this->selected, true)];
            }
        } else if ($this->type === 'checkbox') {
            $context->no = '0';
            $context->value = '1';
            $context->checked = !empty($this->selected);
        } else if ($this->type === 'text') {
            $context->size = 30;
            $context->value = '';
        } else {
            $context->rows = 4;
            $context->cols = 60;
            $context->value = '';
        }
        $element = $OUTPUT->render_from_template('core_admin/setting_config' . $this->type, $context);
        $description = $this->description . (!$this->pronote ? '' : ' ' .
            \html_writer::span(get_string('proonly', 'report_alucoursecompletion'), 'badge bg-secondary'));
        return format_admin_setting($this, $this->visiblename, $element, $description, true, '', null, $query);
    }
}
