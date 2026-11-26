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
 * Admin settings: a checkbox setting (all inputs with its name, incl. the hidden "no" value) is disabled while
 * another checkbox is not ticked or is itself disabled. Disabled inputs are not submitted: the stored value is kept.
 *
 * @module     report_alucoursecompletion/dependentcheckbox
 * @copyright  2026 Alumisoft developer@alumisoft.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([], function() {
    return {
        /**
         * @param {string} sourceid Element id of the checkbox it depends on
         * @param {string} targetname Form name of the dependent setting
         */
        init: function(sourceid, targetname) {
            var source = document.getElementById(sourceid);
            var targets = document.querySelectorAll('input[name="' + targetname + '"]');
            var apply = function() {
                var on = !!source && source.checked && !source.disabled;
                for (var i = 0; i < targets.length; i++) {
                    targets[i].disabled = !on;
                }
            };
            if (source) {
                source.addEventListener('change', apply);
            }
            apply();
        }
    };
});
