# November 8 document findings: fixes and verification

Completed locally on November 9, 2026. [Open the 15 before-and-after comparisons](index.html).

Before images in the gallery come from the screenshots embedded in the supplied findings document. After images show the updated local application using QA accounts and an isolated database. Branding, data and framing differ. Thirteen full-page browser baselines were also captured before source edits; see [baseline captures](before-results.json). No before image was recreated to simulate a failure.

## Findings addressed

| Finding | Result |
| --- | --- |
| Courses show zero hours and cost despite paid packages | Available-course summaries use package hours and price ranges. Dashboard training totals use enrollment hour allocations. |
| Green bar at 0% | Progress width and color render on the server, including AJAX navigation. Zero progress has no colored fill. |
| Redundant active-enrollment count | Removed from desktop and mobile learner dashboard cards. |
| Inconsistent approved status and missing settings | Pending, approved and cancelled labels share school colors, text color, bold weight and badge dimensions. Admin controls save and preview these settings. |
| Sequential schedules require selecting each day | Students and instructors get a dialog for one, all or selected dates. Batch requests use the existing authorization, capacity and conflict checks; failures roll back earlier changes. |
| Same-day batch creation | Administrators can add separate start/end dropdown pairs, including non-sequential hours. Overlapping ranges and invalid durations are rejected. Same-day ranges and sequential dates are mutually exclusive. |
| Unassigned PDC schedules allow only one instructor | The configured instructor capacity remains editable and persists. Two instructors successfully joined an initially unassigned PDC slot in the regression test. |
| Noninteractive additional-instructor indicator | The indicator expands the remaining names. Calendar overflow also opens the day's schedule dialog. |
| Background color/image does not change | Main content is transparent; uploaded image URLs are emitted as CSS strings correctly. The image layer has its own stacking context. Color and image saves were verified. The rendered image margin was sampled at RGB 220, 232, 250, matching the uploaded QA image. |
| Unsafe user deletion | Exact `delete {name}` confirmation archives the account. Historical relationships remain intact. Archived users lose existing portal access. Archived rows no longer advertise permanent deletion. |
| Missing admin verification status | Instructor and administrator session lists, mobile instructor cards and session detail display the linked booking's verification state. |
| Duplicate sessions, missing grades and misleading success | Attended sessions require a grade, including zero. Absences can be recorded without a grade. Duplicate date/time logs, including legacy logs, are rejected. Saving and booking linkage are transactional. Native form submission displays validation failures instead of a generic AJAX success. |

## Verification

| Check | Result | Evidence |
| --- | --- | --- |
| Existing backend regression suites | 18/18 passed | [Results](server-results.json), [logs](server-logs/) |
| Focused backend assertions | 36/36 passed | [Log](focused-server.log) |
| Focused browser checks | 58/58 passed, zero JavaScript exceptions | [Results](focused-browser-results.json) |
| Existing browser workflows | 38/38 passed | [Results](workflow-results.json) |
| Six-role route/login scan | 207 checks, zero server or JavaScript errors | [Results](browser-results.json) |
| AJAX and document responses | 126 AJAX responses, 53 document/export responses checked | [Route results](browser-results.json) |
| Access boundaries | 16 route probes and 11 focused probes passed | [Route results](browser-results.json), [focused probes](security-results.json) |
| Mobile layout | 68 pages at 390 × 844 without document-level horizontal overflow, plus five focused mobile captures | [Route results](browser-results.json) |
| Blade compilation and whitespace | Passed | `php artisan view:cache`, `git diff --check` |
| Running original local app | Login page returned HTTP 200 after the new settings migration | Local port 8004 |

The first repeated route scan hit the system-admin login rate limit. Its eight checks were rerun successfully after the rate-limit window, and the final route report incorporates that recheck. Both the [initial scan](browser-initial-scan.json) and [role recheck](system-admin-recheck/browser-results.json) are retained. File-download routes are tested as downloads, so a successful attachment does not count as an aborted page navigation.

The 36 backend assertions cover package summaries, same-day ranges, mutually exclusive modes, invalid-range rollback, configurable PDC capacity and multiple joins, instructor batch selection and rollback, student batch selection and rollback, duplicate bookings, unrelated batches, required grades, grade zero, saved booking linkage, verification state, legacy duplicate logs, grade clearing, typed confirmation and preserved account history.

## Local application and test isolation

Source changes are mounted into the original application at port 8004. The tested migration adds three boolean badge-weight fields to `school_settings`; it has been applied there. Existing user, enrollment and payment records were not changed in the original database.

Mutation tests used `drivingapp_regression_20261108` through a temporary application bound only to `127.0.0.1:8006`, with debugging disabled. QA fixtures refuse to run against another database. Mail was faked or logged. Real payment processing, email delivery and a deployed production environment were outside this local verification.

The temporary test container, copied database and database dump were removed after verification. The original application remains running on port 8004.

## Reproduction sources

- `tests/verify-nov8-document-findings.php`: transactional backend assertions.
- `tests/nov8-document-fixture.php`: isolated browser fixtures, output as JSON to `tmp/nov8-document-manifest.json`.
- `tests/verify-nov8-document-ui.mjs`: browser workflows and final captures.
- `tests/capture-nov8-findings.mjs`: original full-page baseline capture.
- `tests/build-nov8-document-gallery.mjs`: comparison gallery using the report's extracted media.
- Existing `tests/audit-*.mjs` runners: route, workflow, access and backend regression coverage.

## UI delivery checks

Scope: the changed controls and reported issues, preserving the school's existing visual direction. ENERGY 2 / RHYTHM 2 / MOTION 1.

- Hard Gate PASS: changed controls have real save/select/expand/confirm behavior; validation, failure and cancellation paths use existing forms or native dialogs. Browser checks and five focused mobile views reported no page overflow or JavaScript exceptions.
- Purpose Gate PASS: badge styling communicates workflow status, dialogs group a consequential selection, and background settings implement the school's chosen appearance. No new decorative assets or navigation were introduced.
- Liveliness PASS: existing school headers, card hierarchy, typography and accent colors were retained; the changes support the app's course and scheduling workflow.
- Craftsmanship PASS: persistence, denied operations, rollback, grade-zero handling, saved session linkage and account-history preservation have explicit assertions. Desktop and mobile captures were visually reviewed; the uploaded background was checked in the rendered pixels.
