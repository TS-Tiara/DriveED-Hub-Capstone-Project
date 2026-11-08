# All-role regression audit — November 8, 2026

**Result: seven remaining bugs confirmed.** Application code was not changed during this audit.

Tested commit `7d42f0e` in an isolated local application on port 8006, using a copy of the local database. The original app on port 8004 and its records were not used for mutation tests. Mail used the log driver, so registration and password-reset tests did not deliver email.

## Coverage and results

| Check | Result |
| --- | --- |
| Existing backend workflow suites | 17 of 17 passed; [individual logs](server-logs/) and [suite results](server-results.json) |
| Role/login and registered GET-route checks | 207; two distinct HTTP 500 defects reproduced in three role cases |
| AJAX page/API checks | 136; the branch-secretary Admin Management denial was expected |
| Export/document response checks | 52; 28 returned PDFs with valid `%PDF-` headers |
| Mobile page checks at 390 × 844 | 76; no document-level horizontal overflow detected |
| Additional workflow checks | 28: 22 passed; six failed expectations identify real defects |
| Initial role/access probes | 16: 14 passed; learner-to-learner receipt access failed in both directions |
| Focused security/UI checks | 11: nine passed; a branch-restricted payment image and an advertised admin lesson action failed |
| Cross-school portal checks | All five school-bound roles were denied access to another school's matched dashboard route |
| Browser JavaScript exceptions | None captured; HTTP 500 responses are recorded separately |

Each role used a separate browser context and a real login. The main page scan used representative populated records. Additional checks used both a student without an approved enrollment and an actively approved student to distinguish expected access denials from broken learning workflows.

### Roles and modules

| Role | Main route/login checks | Modules exercised |
| --- | --- | --- |
| System admin | 8 | Login, dashboard, schools, admins, users, logs and log details; password-reset request tested separately |
| School admin | 61 | Dashboard, users and documents, admin management, branches, courses/modules/lessons, enrollments and verification, schedules, sessions, payments, financial and activity reports, vehicles, settings, training records, notifications and exports |
| Branch secretary | 61 | The same registered admin modules, own-branch records, restricted Admin Management, foreign-branch API/detail/document probes and restricted mutation attempts |
| Instructor | 46 | Dashboard, schedules, students, progress, grades, session logging/editing, training records, reports, materials/modules/lessons, assessments, Question Bank, profile, settings, notifications and exports |
| Student | 20 | Dashboard, courses, module outlines, enrollment checkout, payment history/details, current course, progress, schedule, profile, settings, reports and notifications; approved-enrollment lessons, assessments and completion tested separately |
| Guest | 11 | Dashboard, courses, enrollment requests, checkout, payment history, document/QR access and notifications; registration tested separately |

Raw evidence: [browser results](browser-results.json), [workflow results](workflow-results.json), and [security results](security-results.json).

## Confirmed remaining bugs

### 1. P1 — Receipt downloads do not consistently enforce ownership or branch access

**Affected roles:** student, guest and branch secretary.

The student downloaded the guest's receipt, and the guest downloaded the student's receipt. Both requests returned HTTP 200. The fixture receipts belonged to different payment records and different owners.

A branch secretary was correctly denied the other branch's enrollment API (403), user activity (404), and payment detail record (403), but downloading that enrollment's payment image returned **200 with 165,483 bytes**.

Reproduce in the isolated audit:

1. Sign in as a student and request `/drived-hub/guest/receipt?path=receipts%2Fqa-audit-guest.png`.
2. Sign in as a guest and request `/drived-hub/guest/receipt?path=receipts%2Fqa-audit-student.png`.
3. Sign in as the branch-1 secretary and request `/drived-hub/admin/enrollments/2/view-payment-proof`, which belongs to another branch in this data set.

Expected: deny foreign-owner or foreign-branch documents. Observed: HTTP 200.

Sources: [StorageController::streamReceipt](../../../app/Http/Controllers/StorageController.php#L91) validates login, school context and directory prefixes without resolving receipt ownership. [EnrollmentRequestController::viewPaymentProof](../../../app/Http/Controllers/EnrollmentRequestController.php#L1819) checks the admin's school but does not enforce the enrollment's branch.

Recommendation: authorize the payment or legacy enrollment record before opening its file, including owner, school and secretary-branch restrictions. Apply the same policy to every receipt endpoint.

### 2. P1 — Branch secretaries can perform school-admin-only actions

**Affected role:** branch secretary.

Confirmed through actual requests in the cloned database:

- GET school settings returned 200.
- POST settings persisted `login_school_name_text = QA SECRETARY SETTINGS WRITE`.
- GET financial reports returned 200.
- POST course creation returned 201 and created a course.

The app's role helpers state that [course management and financial reports are school-admin-only](../../../app/Models/Admin.php). Admin Management itself was correctly blocked, showing that restrictions are applied inconsistently across modules.

Sources: [settings](../../../app/Http/Controllers/AdminController.php#L1516), [settings updates](../../../app/Http/Controllers/AdminController.php#L1543), [course creation](../../../app/Http/Controllers/AdminController.php#L2549), [financial reports](../../../app/Http/Controllers/ReportController.php#L22), and the corresponding route groups in [routes/web.php](../../../routes/web.php).

Recommendation: enforce central-admin permissions on both read and write routes, then test the other course/package and school-wide administration actions under the same policy.

Evidence: [settings write](screenshots/secretary-settings-write.png), [financial reports](screenshots/secretary-financial-reports.png), and [course creation](screenshots/secretary-created-course.png).

### 3. P2 — Enrollment details return HTTP 500

**Affected roles:** school admin and branch secretary.

Opening `/drived-hub/admin/enrollments/2` as school admin, or the secretary's own-branch enrollment `/drived-hub/admin/enrollments/3`, returns 500.

The controller requests `school.admin.enrollment-requests.show`, but that view file does not exist. Log message: `View [school.admin.enrollment-requests.show] not found.`

Source: [EnrollmentRequestController::show](../../../app/Http/Controllers/EnrollmentRequestController.php#L204).

Recommendation: implement the detail view or route these URLs to the supported verification interface. Add direct and AJAX detail-route regression checks.

Evidence: [admin enrollment failure](screenshots/school-admin-schools.admin.enrollments.show-failure.png), [secretary enrollment failure](screenshots/branch-secretary-schools.admin.enrollments.show-failure.png).

### 4. P2 — Instructor progress editing returns HTTP 500

**Affected role:** instructor.

Opening `/drived-hub/instructor/progress/2/edit` for an assigned student returns 500. The form passes an integer as the second argument to `$schoolRoute`, while the helper requires an array.

Source: [progress-edit.blade.php](../../../resources/views/school/instructor/progress-edit.blade.php#L90).

Recommendation: pass the progress identifier as a named route-parameter array, then test form loading and saving.

Evidence: [progress edit failure](screenshots/instructor-schools.instructor.progress.edit-failure.png).

### 5. P2 — Admin lesson actions lead to forbidden pages

**Affected role:** school admin; the registered secretary lesson create/edit endpoints are also denied.

The admin module page advertises Create Lesson and Edit actions. Clicking Create Lesson returned 403. Direct create/edit requests also returned 403 because the controller permits instructors only.

Sources: [visible Create Lesson link](../../../resources/views/school/admin/modules/show.blade.php#L17), [visible Edit link](../../../resources/views/school/admin/modules/show.blade.php#L45), [create authorization](../../../app/Http/Controllers/ModuleLessonController.php#L71), and [edit authorization](../../../app/Http/Controllers/ModuleLessonController.php#L277).

Recommendation: align the UI with the intended policy: hide unavailable actions, or explicitly permit the authorized admin role in the relevant read/write controller methods.

Evidence: [advertised action returns forbidden](screenshots/admin-create-lesson-forbidden.png).

### 6. P2 — Duplicate course titles return HTTP 500

**Affected module:** course creation.

Submitting the same valid course title twice in one school returns 201 on the first request and 500 on the second. The database rejects the duplicate through `courses_school_id_title_unique`, but the request does not validate that uniqueness before insertion.

Source: [AdminController::storeCourse](../../../app/Http/Controllers/AdminController.php#L2549).

Recommendation: validate title uniqueness within the current school and return a field-level validation response. Check the corresponding update path as well.

Evidence: [workflow-results.json](workflow-results.json), test `Duplicate course title returns a validation response`.

### 7. P2 — System-admin password reset returns HTTP 500

**Affected role:** system admin.

Submitting a valid registered system-admin email to `/system-admin/forgot-password` returns 500. The global route calls `sendResetLink` without a school, but `getResetThrottleMessage` requires a non-null `School`.

Log message: `PasswordResetController::getResetThrottleMessage(): Argument #2 ($school) must be of type App\Models\School, null given`.

Sources: [nullable controller entry point](../../../app/Http/Controllers/PasswordResetController.php#L43) and [non-null throttle helper](../../../app/Http/Controllers/PasswordResetController.php#L339).

Recommendation: support the global/no-school case consistently across the reset helpers and token queries, then verify the complete system-admin recovery flow.

Evidence: [workflow-results.json](workflow-results.json), test `System-admin password-reset request avoids server errors`. Password-reset checks used separate reserved test client IPs after the initial shared client reached the request limit; the final malformed-email check returned 422 and the valid system-admin request reproduced 500.

## Workflows that passed

- All 17 existing PHP suites, including enrollment/media behavior, receipt serving, session editing/features/exports, training completion and bundle transitions, user/session archiving, license reminders, and earlier findings regressions.
- Create, update and delete for all four Question Bank types: multiple choice, true/false, identification and enumeration.
- Instructor lesson creation and editing.
- Approved-student lesson list, lesson detail, assessment rendering and lesson completion.
- Guest registration with valid details and consent; malformed password-reset email validation.
- Cross-school portal isolation for school admin, branch secretary, instructor, student and guest.
- Anonymous receipt denial, path-traversal denial, and several own-/foreign-branch record restrictions.
- PDF response signatures and the tested mobile page layouts.

The first student's lesson/assessment 403 responses were expected: that account did not have an approved enrollment in the tested course. A separate approved student completed those checks successfully. Three document checks returned 404 because the referenced local license/credential uploads were absent; these are data availability limits, not additional confirmed application bugs.

## Scope and reproduction

This was a local regression and access-control audit, not a test of the deployed site. It sampled representative record IDs and existing data states. It does not cover every field combination, concurrent action, physical mobile device, actual GCash transaction, email delivery, or successful end-to-end password recovery. Assessment rendering was exercised; a complete graded assessment submission was not verified.

Reproduction scripts:

- [Isolated fixture setup](../../../tests/audit-all-roles-fixture.php), guarded to run only against `drivingapp_regression_20261108`.
- [Existing backend suites](../../../tests/audit-server-regressions.mjs).
- [All-role route/browser audit](../../../tests/audit-all-roles.mjs).
- [CRUD and learning workflows](../../../tests/audit-role-workflows.mjs).
- [Focused tenant/branch/UI probes](../../../tests/audit-security-boundaries.mjs).

The copied database and temporary application container were removed after the audit. Original application data, passwords and uploads were unchanged. Only test scripts, audit artifacts and their ignore-file entries were added; none of the findings above have been fixed by this audit.
