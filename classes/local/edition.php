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
 * Edition: free, or PRO (PRO files present = classes/pro/, the only folder left out of the free zip).
 * Free code uses a PRO part only when pro_active(): PRO files present AND licence valid.
 *
 * @package    report_alucoursecompletion
 * @copyright  2026 Alumisoft developer@alumisoft.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class edition {
    /** @var string Licence class, only in the PRO edition. */
    public const LICENCE_CLASS = '\report_alucoursecompletion\pro\licence';

    /** @var string PRO promotion switch, in the free and PRO zips only (not in the Moodle Marketplace zip). */
    public const PROMO_CLASS = '\report_alucoursecompletion\local\promo';

    /** @var string "Read more about PRO edition" link (Pro tab, free edition). TO DO: placeholder, real page later. */
    public const PRO_INFO_URL = 'https://alumisoft.com';

    /** @var string Copyright holder on the About tab, plain text (no link) - user decision 2026-11-26. */
    public const COPYRIGHT = 'Alumisoft - developer@alumisoft.com';

    /**
     * @var string[] About tab links, label string key => URL. An empty URL is not shown. The Marketplace needs a
     * documentation URL and a public issue tracker. Public repo of the Marketplace edition (user, 2026-11-26).
     */
    public const ABOUT_LINKS = [
        'about_docs' => 'https://github.com/Alumisoft/moodle-report_alucoursecompletion#readme',
        'about_support' => 'https://github.com/Alumisoft/moodle-report_alucoursecompletion/issues',
        'about_source' => 'https://github.com/Alumisoft/moodle-report_alucoursecompletion',
    ];

    /** @var array Features table on the Pro tab (free edition): lang string key => [in free, in PRO]. */
    public const FEATURES = [
        'feature_enrolments' => [true, true],
        'feature_totals' => [true, true],
        'feature_csv' => [true, true],
        'feature_settings' => [true, true],
        'feature_excludedcourses' => [false, true],
        'feature_excelpdf' => [false, true],
        'feature_studentdetails' => [false, true],
        'feature_profilefields' => [false, true],
        'feature_certificate' => [false, true],
        'feature_timespent' => [false, true],
    ];

    /**
     * Are the PRO files installed?
     *
     * @return bool
     */
    public static function pro_installed(): bool {
        return class_exists(self::LICENCE_CLASS);
    }

    /**
     * Edition name: 'pro' when the PRO files are installed (licence or not), else 'free'.
     *
     * @return string
     */
    public static function name(): string {
        return self::pro_installed() ? 'pro' : 'free';
    }

    /**
     * Show the PRO promotion (PRO-only tabs with locked settings, Pro tab, locked setting on Report view)?
     * Off in the Moodle Marketplace edition, which has no classes/local/promo.php.
     *
     * @return bool
     */
    public static function promo_shown(): bool {
        return class_exists(self::PROMO_CLASS);
    }

    /**
     * Package this code came from: 'pro' (PRO files), 'free' (free zip) or 'marketplace' (Moodle Marketplace zip).
     *
     * @return string
     */
    public static function package(): string {
        if (self::pro_installed()) {
            return 'pro';
        }
        return self::promo_shown() ? 'free' : 'marketplace';
    }

    /**
     * Are the PRO-only admin tabs shown (PRO edition, or free edition with the locked settings)?
     *
     * @return bool
     */
    public static function pro_tabs_shown(): bool {
        return self::pro_installed() || self::promo_shown();
    }

    /**
     * Can PRO parts be used: PRO files installed and the licence valid?
     *
     * @return bool
     */
    public static function pro_active(): bool {
        return self::pro_installed() && \report_alucoursecompletion\pro\licence::is_valid();
    }

    /**
     * Warning for site admins on the report pages when PRO is installed but the licence is not valid ('' else).
     *
     * @return string
     */
    public static function licence_notice(): string {
        global $OUTPUT;
        if (
            !self::pro_installed() || self::pro_active()
            || !has_capability('moodle/site:config', \context_system::instance())
        ) {
            return '';
        }
        $status = \report_alucoursecompletion\pro\licence::status();
        $text = \report_alucoursecompletion\pro\licence::describe($status);
        if ($status['state'] !== \report_alucoursecompletion\pro\licence::STATE_MISSING) {
            $text = get_string('licence_prooff', 'report_alucoursecompletion') . ' ' . $text;
        }
        $link = \html_writer::link(
            new \moodle_url('/admin/settings.php', ['section' => 'reportalucoursecompletion']),
            get_string('licence_entrykey', 'report_alucoursecompletion')
        );
        return $OUTPUT->notification(
            s($text) . ' ' . $link,
            \core\output\notification::NOTIFY_WARNING,
            false
        );
    }
}
