# Seven remaining bugs fixed — November 8, 2026

All seven findings from the [all-role audit](../nov8-all-roles/README.md) are fixed. [Open the screenshot gallery](index.html) or [download the evidence bundle](../nov8-seven-fixes.zip).

## Before and after

Before captures use commit `7d42f0e`; after captures use the working fixes. Each pair uses a 1440 × 1000 browser viewport, matching role and action, and an isolated copy of the local database. Receipts shown in access tests are labeled QA samples. There are eight pairs because the receipt defect included both learner ownership and branch isolation.

| Finding | Before | After |
| --- | --- | --- |
| Receipt ownership and branch isolation | Another learner's receipt and another branch's payment proof returned 200 | Unauthorized requests return 403; owners and authorized staff retain access |
| Secretary privileges | School-wide settings, courses and financial reports were accessible | Server-side checks deny school administrator operations, including write requests |
| Enrollment detail | Missing view returned 500 | Redirects to the existing verification dashboard and opens the requested enrollment |
| Instructor progress edit | Route parameters caused 500 | Edit page loads; updates save and persist |
| Administrator lesson actions | Create/Edit Lesson returned 403 | Administrator create, edit, update and delete use admin routes; instructor routes remain usable |
| Duplicate course title | Database constraint exception returned 500 | School-scoped title validation returns 422 with a field error; unchanged titles and titles in other schools remain valid |
| System administrator password recovery | Null school caused 500 | Email renders, hashed global tokens persist, reset completes and redirects to system administrator login |

## Verification

| Check | Result |
| --- | --- |
| Backend regression suites | **18/18 passed**, including the seven-fix suite; [results](server-results.json), [logs](server-logs/) |
| Focused seven-fix assertions | **40/40 passed**; covers positive and denied receipt access, legacy paths, role checks, duplicate titles, lessons, enrollment detail, global/tenant password recovery, invalid/expired/replayed tokens |
| Six-role login and GET-route checks | **207 checks**, zero server or JavaScript errors; [results](browser-results.json) |
| AJAX responses | **127 checked** |
| Export/document responses | **52 checked** |
| Mobile pages at 390 × 844 | **68 checked**, zero document-level horizontal overflows |
| Initial access-boundary probes | **16/16 passed** |
| Focused branch, cross-school and anonymous access checks | **10/10 passed**; [results](security-results.json) |
| Browser workflows | **38/38 passed**; includes admin lesson CRUD, repeated enrollment AJAX navigation, persisted instructor progress, four question types, approved-student learning, registration and password-reset requests; [results](workflow-results.json) |
| Matched screenshot assertions | All eight before/after cases matched their expected HTTP status; [before](before-results.json), [after](after-results.json) |
| Blade compilation and diff whitespace | Passed |

The final commit review also hardened the backend runner to recognize printed `AssertionError` failures even when Laravel exits with code zero. The session-edit regression now selects a conflict-free past date, preserves the enrollment's available hours, runs only against the copied database and rolls back its changes. All 18 backend suites were rerun after that correction.

The route sweep includes expected 401/403 responses for denied roles and 404 responses for missing optional documents. Successful approved-student learning and owned receipt paths are covered separately. The scan is regression evidence for these fixtures, not a claim that every possible input or production integration was tested.

## Test isolation

The temporary application ran on port 8006 using `drivingapp_regression_20261108`. Credential changes, workflow mutations and reset tests affected that copy only. Mail was faked or logged; no test emails were delivered. The original application on port 8004 and its database records were preserved. The temporary container and copied database were removed after verification.

Test sources: `tests/verify-seven-fixes.php`, `tests/capture-seven-fixes.mjs`, `tests/build-seven-fixes-gallery.mjs`, and the four `tests/audit-*.mjs` runners. Audit fixture creation refuses to run outside the explicitly named copied database. The screenshot runner accepts `before` or `after`; browser audit runners accept `AUDIT_OUTPUT`, and fixed workflow/server expectations use `EXPECT_FIXED=1`.
