# Course completion report

Moodle site report: who is enrolled in which course, since when, how far they got and whether they completed,
plus totals per course.

`report_alucoursecompletion` · Site administration > Reports > Course completion report


## Features

- **Course enrolments**: one row per student per course - Student, Email, Course, Enrol date, Activity progress,
  Progress %, Status, Course completed, Last access.
  - Status: **Completed**, **In progress**, or **Did not finish** (enrolment ended, course not completed).
  - Filters: course, enrol year, status, name search (several names separated by commas).
  - Sortable columns (a user's sort is kept for the rest of their session), 25 / 50 / 100 rows per page.
  - With one course selected: a summary box with its totals.
- **Course totals**: one row per course - created date, course status (Open / Closed / Hidden), total enrolled,
  in progress, completed, did not finish. Filters: course name, enrol year, course status.
- **CSV export** of both tables (all rows of the current filters).
- **Settings**: default sort and date format (Report view tab); export / import the settings as a JSON file,
  e.g. to copy them to another site.

Only courses where Moodle tracks course completion are reported: completion tracking on for the site and the
course, and at least one course completion condition.


## Who can see it

- Site administrators, and users with the **Manager** role at site level
  (capability `report/alucoursecompletion:view`).
- Only site administrators can change the settings.

The report shows personal data (names, emails, progress): give the capability only to people allowed to see it.


## Requirements

| | |
|---|---|
| Moodle | Supported: 4.5, 5.2, 5.3. Also installs on 5.0 and 5.1 (Moodle versions that only get security fixes), but is not tested or supported there. |
| PHP | 8.1 to 8.4 (tested on 8.1 and 8.4) |
| Database | Tested on MariaDB and PostgreSQL (MySQL not tested) |
| Data formats | CSV must be enabled: Site administration > Plugins > Data formats |

No other plugins needed.


## Install

1. Site administration > Plugins > Install plugins > upload the zip > follow the steps.
2. The report is under **Site administration > Reports > Course completion report**.

No steps after installing.

Uninstall: Site administration > Plugins > Plugins overview > Course completion report > Uninstall. It removes
the plugin's settings and capability.


## Privacy

The plugin stores no personal data. It only reads Moodle's own enrolment, completion and log data to show it to
the people allowed to see the report.


## Support

- Bug reports and feature requests: https://github.com/Alumisoft/moodle-report_alucoursecompletion/issues
- Documentation: this README, https://github.com/Alumisoft/moodle-report_alucoursecompletion#readme
- Source code: https://github.com/Alumisoft/moodle-report_alucoursecompletion


## Licence

GNU GPL v3 or later - see [LICENSE](LICENSE).

Copyright Alumisoft - developer@alumisoft.com
