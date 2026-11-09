# DriveED Hub

A school portal for managing students, instructors, courses, schedules, payments, and training records.

[November 9](#fixes-november-9-2026) · [November 8](#fixes-november-8-2026) · [November 5](#fixes-november-5-2026) · [November 4](#latest-fixes-november-4-2026) · [November 1](#fixes-november-1-2026) · [October 31](#latest-fixes-october-31-2026) · [October 30](#fixes-october-30-2026) · [October 29](#fixes-october-29-2026) · [October 27](#fixes-october-27-2026) · [Earlier fixes](#earlier-fixes-october-20-to-26-2026)

## Fixes November 9 2026

Addressed the findings in the November 8 report, with changes to learner pages, scheduling, school settings, account removal and instructor session logging.

Screenshots are shown directly below each change group. Before images come from the supplied report; after images show the fixed local app with QA records. Branding, data and framing differ. Click any image to view it at full size.

### Course information and learner dashboards

- Available-course summaries display package training hours and price ranges instead of zero-valued course defaults.
- Dashboard training totals use enrollment hour allocations, including the selected package.
- Progress bars render with no colored fill at 0%, including after AJAX navigation.
- Removed the redundant active-enrollment count from desktop and mobile learner dashboard cards.


**Course training hours and package prices**

| Before: reported issue | After: fixed local app |
| --- | --- |
| ![Before: Course training hours and package prices](docs/screenshots/nov8-document-findings/course-summary-reported-before.png) | ![After: Course training hours and package prices](docs/screenshots/nov8-document-findings/course-summary-after.png) |


**Package information**

| Before: reported issue | After: fixed local app |
| --- | --- |
| ![Before: Package information](docs/screenshots/nov8-document-findings/course-packages-reported-before.png) | ![After: Package information](docs/screenshots/nov8-document-findings/course-packages-after.png) |


**Zero-percent learning progress**

| Before: reported issue | After: fixed local app |
| --- | --- |
| ![Before: Zero-percent learning progress](docs/screenshots/nov8-document-findings/progress-enrollment-reported-before.png) | ![After: Zero-percent learning progress](docs/screenshots/nov8-document-findings/progress-enrollment-after.png) |


**Enrollment status without the redundant count**

| Before: reported issue | After: fixed local app |
| --- | --- |
| ![Before: Enrollment status without the redundant count](docs/screenshots/nov8-document-findings/enrollment-count-reported-before.png) | ![After: Enrollment status without the redundant count](docs/screenshots/nov8-document-findings/progress-enrollment-after.png) |

### Scheduling

- Students and instructors can choose one day, all days or selected dates from a sequential schedule batch. Failed batch requests roll back earlier reservations or assignments.
- Administrators can create multiple schedules on one date using additional start/end time dropdown pairs. Non-sequential hours are supported; overlapping or invalid ranges are rejected.
- Same-day time ranges and sequential multi-day scheduling cannot be enabled together.
- PDC instructor capacity is configurable even when no instructor is pre-assigned. Multiple instructors can join up to that capacity, while retaining one learner per instructor.
- The additional-instructor indicator expands the remaining names. Calendar overflow opens the day's schedule details.
- Fixed the instructor schedule success notification running before its toast helper was available.


**Instructor batch schedule selection**

| Before: reported issue | After: fixed local app |
| --- | --- |
| ![Before: Instructor batch schedule selection](docs/screenshots/nov8-document-findings/instructor-batch-reported-before.png) | ![After: Instructor batch schedule selection](docs/screenshots/nov8-document-findings/instructor-batch-after.png) |


**Student batch schedule selection**

| Before: reported issue | After: fixed local app |
| --- | --- |
| ![Before: Student batch schedule selection](docs/screenshots/nov8-document-findings/student-batch-reported-before.png) | ![After: Student batch schedule selection](docs/screenshots/nov8-document-findings/student-batch-after.png) |


**Multiple time ranges on one date**

| Before: reported issue | After: fixed local app |
| --- | --- |
| ![Before: Multiple time ranges on one date](docs/screenshots/nov8-document-findings/schedule-creation-reported-before.png) | ![After: Multiple time ranges on one date](docs/screenshots/nov8-document-findings/schedule-creation-after.png) |


**Configurable PDC instructor capacity**

| Before: reported issue | After: fixed local app |
| --- | --- |
| ![Before: Configurable PDC instructor capacity](docs/screenshots/nov8-document-findings/pdc-capacity-reported-before.png) | ![After: Configurable PDC instructor capacity](docs/screenshots/nov8-document-findings/pdc-capacity-after.png) |


**Expandable additional instructor names**

| Before: reported issue | After: fixed local app |
| --- | --- |
| ![Before: Expandable additional instructor names](docs/screenshots/nov8-document-findings/schedule-instructors-reported-before.png) | ![After: Expandable additional instructor names](docs/screenshots/nov8-document-findings/schedule-instructors-after.png) |

### School appearance settings

- Added text-color and bold-text controls alongside background colors for pending, approved and cancelled status labels. Labels share these settings and consistent dimensions across school pages.
- Fixed solid-color and uploaded-image backgrounds being covered by the main content area.
- Corrected escaped image URLs in CSS and background-layer stacking so uploaded backgrounds load and appear correctly.
- Added and applied a migration for the three status-label bold settings in the running local application.


**Consistent payment status labels**

| Before: reported issue | After: fixed local app |
| --- | --- |
| ![Before: Consistent payment status labels](docs/screenshots/nov8-document-findings/payment-status-reported-before.png) | ![After: Consistent payment status labels](docs/screenshots/nov8-document-findings/payment-status-after.png) |


**Status background, text color and bold settings**

| Before: reported issue | After: fixed local app |
| --- | --- |
| ![Before: Status background, text color and bold settings](docs/screenshots/nov8-document-findings/status-settings-reported-before.png) | ![After: Status background, text color and bold settings](docs/screenshots/nov8-document-findings/status-settings-after.png) |


**School background settings**

| Before: reported issue | After: fixed local app |
| --- | --- |
| ![Before: School background settings](docs/screenshots/nov8-document-findings/background-reported-before.png) | ![After: School background settings](docs/screenshots/nov8-document-findings/background-after.png) |

**Uploaded image background after the fix**

![Uploaded school background rendered correctly](docs/screenshots/nov8-document-findings/background-image-after.png)

### Account removal and historical records

- System administrators must type `delete {name}` before removing an account from active use.
- Removal archives the account and preserves enrollments, payments, bookings and training history.
- Archived accounts lose existing portal access. Archived rows no longer offer permanent deletion.


**Typed account removal confirmation and archival**

| Before: reported issue | After: fixed local app |
| --- | --- |
| ![Before: Typed account removal confirmation and archival](docs/screenshots/nov8-document-findings/user-removal-reported-before.png) | ![After: Typed account removal confirmation and archival](docs/screenshots/nov8-document-findings/user-removal-after.png) |

### Instructor session logging: duplicate sessions and missing grades

Fixed the reported duplicate submissions, missing grades and misleading success messages:

- Logging the same session for the same learner, instructor, date and start time is rejected, including older records without a separate start-time field.
- Attended sessions require a grade from 0 to 100. Zero is a valid grade; absences can be recorded without a grade. An existing attended session's grade cannot be cleared.
- Successful submissions persist the session, attendance and grade and link the session to its booking. Session lists show whether admin verification is pending or complete.
- Failed saves roll back without leaving partial session or booking records. Validation errors are displayed instead of a generic success message.

Verified locally through the [focused backend regression assertions](docs/screenshots/nov8-document-findings/focused-server.log) and [browser checks](docs/screenshots/nov8-document-findings/focused-browser-results.json). The backend checks confirm saved grades and booking links, duplicate rejection without extra records, and verification status changes. Browser checks confirm required grades, the absence exception and the displayed verification status.

[Session logging screenshots](docs/screenshots/nov8-document-findings/index.html#session-grade) · [Verification status screenshots](docs/screenshots/nov8-document-findings/index.html#session-verification)


**Required session grades**

| Before: reported issue | After: fixed local app |
| --- | --- |
| ![Before: Required session grades](docs/screenshots/nov8-document-findings/session-grade-reported-before.png) | ![After: Required session grades](docs/screenshots/nov8-document-findings/session-grade-after.png) |


**Visible admin verification status**

| Before: reported issue | After: fixed local app |
| --- | --- |
| ![Before: Visible admin verification status](docs/screenshots/nov8-document-findings/session-verification-reported-before.png) | ![After: Visible admin verification status](docs/screenshots/nov8-document-findings/session-verification-after.png) |

### Verification and screenshots

Passed **18 backend suites, 36 focused backend assertions, 58 focused browser checks, 38 existing browser workflows and 207 route/login checks across six roles**. The route scan checked 126 AJAX responses and 53 document/export responses. All 27 access-boundary probes passed, and 68 mobile pages had no document-level horizontal overflow. Blade compilation and whitespace checks passed.

Mutation tests ran against an isolated database copy with mail faked or logged. The temporary test application, copied database and dump were removed afterward. The original local application remains on port 8004; its existing user, enrollment and payment records were preserved.

The gallery contains **15 before-and-after comparisons**. Before images come from the supplied report; after images were captured in the fixed local application using QA records. Branding, data and framing differ.

[Before-and-after screenshots](docs/screenshots/nov8-document-findings/index.html) · [Detailed test report](docs/screenshots/nov8-document-findings/README.md) · [Download evidence](docs/screenshots/nov8-document-findings.zip)

## Fixes November 8 2026

Fixed all seven remaining bugs found in the follow-up all-role audit:

1. Receipts enforce learner ownership, school boundaries and secretary branch access, including older payment records.
2. School-wide settings, course management and financial reports require a school administrator; unauthorized write requests are denied.
3. Enrollment detail links open the existing verification dashboard instead of returning a missing-view error.
4. Instructor progress editing loads correctly, and saved updates persist.
5. School administrators can create, edit and delete lessons through administrator routes; instructor lesson actions continue to work.
6. Duplicate course titles return a field validation error instead of a server error, with uniqueness scoped to each school.
7. System administrator password recovery supports global reset tokens, email rendering, password updates and the correct login redirect.

Verification passed **18 backend suites, 40 focused assertions, 38 browser workflow checks and 207 checks across six roles**, with no server or JavaScript errors in the route scan. An additional 26 access-boundary probes passed, and 68 mobile pages had no document-level horizontal overflow. Tests used an isolated database copy; original application records were preserved.

[View all eight before-and-after comparisons](docs/screenshots/nov8-seven-fixes/index.html) · [Test report and logs](docs/screenshots/nov8-seven-fixes/README.md) · [Download screenshots](docs/screenshots/nov8-seven-fixes.zip).

Fixed the issues reported in the November 7 findings document and checked them in the local app on desktop and mobile.

The comparisons show **Before on the left** and **After on the right**. Before was captured from Git revision `6a85c5c` in a separate local container. Both versions used the same temporary QA accounts and sample uploads. The amounts shown include test payments.

[View all 15 comparisons](docs/screenshots/nov7-before-after/index.html) · [Download the screenshots](docs/screenshots/nov7-before-after.zip) · [Test results and capture details](docs/screenshots/nov7-before-after/README.md)

### Payment totals and receipt access

Payment totals and approved/pending counts stay correct when navigating beyond page 1. Pagination retains the selected filters, and browser history preserves the page's query parameters. The comparison shows the same records on page 2, with the corrected totals.

![Before and after showing payment totals on page 2](docs/screenshots/nov7-before-after/payment-summary-comparison.png)

Student and guest payment history includes a **View receipt** button for submitted uploads. Receipt controls are available in both desktop rows and mobile cards.

![Before and after showing receipt access in mobile payment history](docs/screenshots/nov7-before-after/payments-mobile-comparison.png)

### Guest enrollment payment details

Guests can review submitted payment amounts, methods, reference or OR numbers, statuses, dates and receipts directly on My Enrollment Requests. Older submissions stored on the enrollment record are included.

![Before and after showing submitted payment details on guest enrollment requests](docs/screenshots/nov7-before-after/guest-mobile-comparison.png)

### User documents and activity

User Management profiles include a **Documents and Activity** section with submitted student permits or driver's licenses, enrollment credentials, enrollment history, payment transactions, receipt previews and bookings. Instructor profiles include their teaching bookings.

Activity lists paginate inside the profile, and the profile body scrolls while keeping its footer visible. Document access respects school and branch restrictions.

![Before and after showing documents and activity in a student profile](docs/screenshots/nov7-before-after/profile-desktop-comparison.png)

### Shared image viewer

Instructor licenses, student documents, admin payment snapshots and receipts use the shared viewer with zoom, pan, rotate and download controls. Closing an image returns keyboard focus to the control that opened it and keeps the underlying profile or verification window open.

![Before and after showing the shared license image viewer](docs/screenshots/nov7-before-after/license-viewer-comparison.png)

GCash receipt names no longer cause the payment QR viewer to open. Student and guest payment QR controls retain their dedicated scan viewer and account information.

![Before and after showing a GCash receipt in the standard image viewer](docs/screenshots/nov7-before-after/receipt-viewer-comparison.png)

Missing or invalid images show an unavailable-image message.

![Before and after showing the missing-image state](docs/screenshots/nov7-before-after/missing-image-comparison.png)

### Pickup notice dialog

The pickup notice dialog opens in the center of the viewport. It fits narrow screens and scrolls when vertical space is limited.

![Before and after showing the centered pickup notice dialog](docs/screenshots/nov7-before-after/pickup-desktop-comparison.png)

### Enrollment actions and pagination

Enrollment action buttons wrap within their table cell. Mobile enrollment cards keep their responsive action layout.

![Before and after showing wrapping enrollment action buttons](docs/screenshots/nov7-before-after/enrollment-actions-comparison.png)

Enrollment, User Management, payment and session lists use the shared pagination styling, with consistent numbered controls, result summaries, active and disabled states, and larger mobile touch targets.

![Before and after showing standardized enrollment pagination](docs/screenshots/nov7-before-after/enrollment-pagination-comparison.png)

![Before and after showing standardized payment pagination](docs/screenshots/nov7-before-after/payment-pagination-comparison.png)

### Verification

- **9 transactional server checks passed**, covering page-independent totals, preserved filters, submitted documents, and school/branch access restrictions. Database changes were rolled back.
- **12 browser check groups passed** at desktop (1440 × 1000) and mobile (390 × 844) sizes. Checks included receipt clicks, nested viewers, profile pagination, dialog placement, action wrapping and preserved QR account details.
- **15 matched before-and-after screenshot pairs** were captured. The browser regression reported no JavaScript or application server errors.
- PHP syntax checks, Blade template compilation and `git diff --check` passed.

The temporary QA accounts, payments, enrollments and uploads were removed after testing. The baseline container was also removed.

One existing local demo license file is empty and requires a fresh upload to display its original contents. Existing uploads were not replaced.

## Fixes November 5 2026

The **before** screenshots come from the two November 5 findings documents. The **after** screenshots come from local Chrome tests with QA records, so names and dates can differ.

### Question Bank

The Question Bank saves new questions. Chrome verified all four question types.

![Before and after showing saved questions in the Question Bank](docs/screenshots/nov5-findings/compare-question.png)

### Instructor dashboard navigation

Today's Lessons and This Week open Schedules. Active Students opens My Students. Chrome verified all three destinations.

![Before and after showing the instructor dashboard cards](docs/screenshots/nov5-findings/compare-dashboard.png)

### Enrolled Course hours

Enrolled Course shows the selected package's required hours. The after screenshot shows a 15-hour practical package.

![Before and after showing the required hours for a practical enrollment](docs/screenshots/nov5-findings/compare-hours.png)

### My Payments date

The after screenshot shows an approved ₱100.00 test payment with a populated date and a ₱100.00 Total Amount Paid.

![Before and after showing the payment date and approved ₱100.00 total](docs/screenshots/nov5-findings/compare-payment.png)

### TDC batch scheduling

The batch controls appear for theoretical courses and are hidden for practical courses.

![Before and after showing the schedule form for a practical course](docs/screenshots/nov5-findings/compare-batch.png)

### Instructor notes in session history

Student Session History shows the notes saved with the logged session.

![Before and after showing instructor notes in Student Session History](docs/screenshots/nov5-findings/compare-notes.png)

### Manually logged sessions in Grades Management

Grades Management includes manually logged sessions pending admin review. The test row shows one session, its date, and its 94.7 grade.

![Before and after showing a manually logged session in Grades Management](docs/screenshots/nov5-findings/compare-grades.png)

### Theoretical Training hours and PDF export

Theoretical Training uses the enrollment's required hours. Export PDF sits on the right side of the header, and the download returned a valid PDF in Chrome.

![Before and after showing the theoretical hours and Export PDF placement](docs/screenshots/nov5-findings/compare-theoretical.png)

## Latest Fixes November 4 2026

Updated November 5, 2026.

- **School Admin color preview.** The primary, secondary, and accent swatches show the selected colors when the preview opens and update as the colors change. All three swatches were checked in the local browser before saving.
- **Student invitation activation.** Completing an invitation now verifies the student's email. For students already stuck, a [repair migration](database/migrations/2026_11_05_000001_verify_students_from_used_invitations.php) verifies accounts that match a used invitation and an activation record. In the local browser test, a repaired account signed in with its original password, completed the required first-login password change, and reached the student dashboard without registering again.
- **Student payment total.** My Payments sums approved payments across all pages of the current results. A temporary approved payment of ₱2,301.00 appeared in both the payment list and the total during local browser verification.

### Before and after

The **Before** screenshots come from the Nov 4 report. The **After** screenshots come from local browser tests.

| Finding | Before | After |
| --- | --- | --- |
| School Admin color preview | ![Blank primary, secondary, and accent swatches](docs/screenshots/nov4-findings/colors-before.png) | ![Swatches filled with the selected colors](docs/screenshots/nov4-findings/colors-after.png) |
| Student invitation activation | ![Invitation email from the reported activation flow](docs/screenshots/nov4-findings/activation-before.jpeg) The student received this invitation but was redirected to registration after submitting their profile. | ![Student dashboard after signing in with a repaired account](docs/screenshots/student-activation-repair/02-student-dashboard.png) The repaired test account reached the dashboard after the [first-login password change](docs/screenshots/student-activation-repair/01-signed-in.png). |
| Student payment total | ![Approved ₱2,301.00 payment with a ₱0.00 total](docs/screenshots/nov4-findings/payments-before.png) | ![Approved ₱2,301.00 payment included in the total](docs/screenshots/nov4-findings/payments-after.png) |

The local database had no matching records for the reported student. Applying the migration and verifying affected accounts on the database used for the report are still pending.

## Fixes November 1 2026

Updated November 1, 2026.

The issues reported in the November 1 findings document have been fixed and checked in the local app. The screenshots and videos below show the results.

- Saved package changes now appear when an admin opens Edit or Preview again.
- Long package lists scroll automatically. Hovering pauses them so you can scroll with the mouse wheel. Moving the cursor away starts automatic scrolling again.
- Admin Preview now uses the same package details shown during guest and student enrollment, including names, prices, descriptions, and features.
- Enrollment includes **New Driver (Have Student Driver's License)**. Choosing **Experienced Driver** changes the upload label to **Driver's License**; choosing the new-driver option changes it back to **Student Driver's License**.

### Saved packages and course preview

The saved package name, price, description, and features appear when Edit is reopened. Quotation marks and blank feature rows were also checked.

![Saved package reopened in Edit](docs/screenshots/nov1-findings/package-edit-saved.png)

The admin preview shows the updated details on desktop and mobile.

![Admin course preview on desktop](docs/screenshots/nov1-findings/admin-preview-desktop.png)

![Admin course preview on mobile](docs/screenshots/nov1-findings/admin-preview-mobile.png)

<details>
<summary>Full admin Courses page</summary>

![Admin Courses page after the fixes](docs/screenshots/nov1-findings/admin-current.png)

</details>

### Package scrolling

This recording shows automatic scrolling, the pause on hover, manual mouse-wheel scrolling, and automatic scrolling starting again when the cursor leaves. Extra demo rows were added only in the browser to make the list long enough; they were not saved to the database.

[![Play the package scrolling demo](docs/screenshots/nov1-findings/package-scrolling-video-poster.png)](docs/screenshots/nov1-findings/package-scrolling-demo.webm)

[Watch or download the package scrolling video](docs/screenshots/nov1-findings/package-scrolling-demo.webm)

![Package list during manual scrolling](docs/screenshots/nov1-findings/packages-manual-scroll.png)

### Driver experience and license labels

The guest enrollment form asks experienced drivers for a Driver's License.

![Guest enrollment with Experienced Driver selected](docs/screenshots/nov1-findings/guest-experienced-license.png)

Choosing New Driver (Have Student Driver's License) changes the label to Student Driver's License.

![Guest enrollment with New Driver selected](docs/screenshots/nov1-findings/guest-new-driver-license.png)

The new-driver option is also available in student enrollment.

![Student enrollment with New Driver selected](docs/screenshots/nov1-findings/student-new-driver-license.png)

This recording switches between both options in the student form so you can see the upload label change. It uses a demo student without a license on file. No enrollment was submitted.

[![Play the student license label demo](docs/screenshots/nov1-findings/student-experienced-video-poster.png)](docs/screenshots/nov1-findings/student-license-switching.webm)

[Watch or download the student enrollment video](docs/screenshots/nov1-findings/student-license-switching.webm)

### What was checked

The browser checks covered saving and reopening a package, previewing the saved details, desktop and mobile preview sizes, automatic and manual scrolling, and both enrollment forms. Automatic scrolling also stops when reduced motion is enabled. Server checks confirmed that the new-driver option is accepted and that the updated PHP and page templates compile.

The package used during testing was restored after the checks. The videos demonstrate form behavior; they do not submit an enrollment.

<details>
<summary>Original screenshots from the November 1 findings document — before the fixes</summary>

These are the eight reference screenshots supplied in the findings document. They show the reported issues, rather than the finished changes above.

![Original finding screenshot 1](docs/screenshots/nov1-findings/image1.png)

![Original finding screenshot 2](docs/screenshots/nov1-findings/image2.png)

![Original finding screenshot 3](docs/screenshots/nov1-findings/image3.png)

![Original finding screenshot 4](docs/screenshots/nov1-findings/image4.png)

![Original finding screenshot 5](docs/screenshots/nov1-findings/image5.png)

![Original finding screenshot 6](docs/screenshots/nov1-findings/image6.png)

![Original finding screenshot 7](docs/screenshots/nov1-findings/image7.png)

![Original finding screenshot 8](docs/screenshots/nov1-findings/image8.png)

</details>


## Latest Fixes October 31 2026

- Students who pass TDC can apply for PDC without cancelling their finished TDC course.
- Previous training records and Combo courses are kept unchanged.
- Finishing an older course does not interrupt a student's newer course.
- Passing notes are saved and kept when the passing action is repeated.
- Extra sessions cannot be added once the required training hours are complete.
- Grades and feedback can still be corrected without adding duplicate sessions.
- Saving two sessions at the same time cannot exceed the student's remaining training hours.
- Admins, instructors, and students can have their own header colors.
- Review windows close properly, including when using the Escape key.

## School Admin and Home Page Fixes

<details>
<summary>Course and package hours save correctly</summary>

Required course hours can be edited and saved. Package TDC and PDC hours remain saved when reopened, including fractional hours.

![Course and package hours save correctly](docs/screenshots/hours-login-fix/course-hours.png)

![Course and package hours save correctly](docs/screenshots/hours-login-fix/package-created.png)

</details>

<details>
<summary>Login fits different screen sizes</summary>

The login form uses the available screen width with readable text and larger controls on phones.

![Login fits different screen sizes](docs/screenshots/hours-login-fix/login-390.png)

</details>

<details>
<summary>New schools can open User Management</summary>

Schools without courses can open User Management. The empty-course message links to the correct course page.

![New schools can open User Management](docs/screenshots/new-school-user-management/user-management-loaded.png)

![New schools can open User Management](docs/screenshots/new-school-user-management/no-courses-student-modal.png)

</details>

<details>
<summary>Payments keeps the admin layout</summary>

The Payments page keeps the admin navigation and shows one empty-state message on each screen size.

![Payments keeps the admin layout](docs/screenshots/new-school-audit/payments.index.png)

</details>

<details>
<summary>Inactive users appear in the matching list</summary>

Archived and deactivated users appear under Inactive Users. The card and list show matching totals: two students and three instructors when all five belong to the same school.

![Inactive users appear in the matching list](docs/screenshots/inactive-roster-fix/inactive-card-five-users.png)

</details>

<details>
<summary>Each school sees its own inactive users</summary>

Archived and deactivated users stay in their own school. Four archived users in one school and a deactivated instructor in another appear in their respective lists.

![Each school sees its own inactive users](docs/screenshots/inactive-roster-fix/qa-inactive-roster-school-scoped.png)

![Each school sees its own inactive users](docs/screenshots/inactive-roster-fix/qa-inactive-roster-other-school-scoped.png)

</details>

<details>
<summary>Home page uses DriveED Hub branding</summary>

The home page title and copyright now use DriveED Hub.

![Home page uses DriveED Hub branding](docs/screenshots/home-branding/driveed-hub-home.png)

</details>

## TDC to PDC Enrollment

**Before:** students who already passed TDC were still blocked from applying for PDC.

**After:** passing TDC finishes the old course and lets the student submit a separate PDC application. Payment and school approval are still required. Previous training records and Combo courses are kept.

| Before | After |
| --- | --- |
| ![PDC application blocked](docs/screenshots/tdc-pdc-reproduction/before-blocked.png) | ![PDC application accepted](docs/screenshots/tdc-pdc-reproduction/after-submitted.png) |

## Other Fixes

Expand a finding to view its before-and-after pictures. Before pictures come from the findings report; after pictures show the updated app.

<details>
<summary>1. Pending Emails is easier to read</summary>

Names, email addresses, and descriptions have more space. The section can be opened when needed and closed afterward.

| Before | After |
| --- | --- |
| ![Pending emails before](docs/screenshots/oct31-findings/review/before/image2.png) | ![Pending emails after](docs/screenshots/oct31-findings/review/emails-expanded.png) |

</details>

<details>
<summary>2. Schedule sections can be opened and closed</summary>

Today's Lessons, Upcoming This Week, and Cancelled Schedules no longer need to stay open all the time.

| Before | After |
| --- | --- |
| ![Schedule sections before](docs/screenshots/oct31-findings/review/before/image3.png) | ![Schedule sections after](docs/screenshots/oct31-findings/review/panels-collapsed.png) |

</details>

<details>
<summary>3. Instructors can close the calendar window</summary>

The Close button now dismisses the window without showing an error.

| Before | After |
| --- | --- |
| ![Instructor calendar close error](docs/screenshots/oct31-findings/review/before/image4.png) | ![Instructor calendar after closing](docs/screenshots/oct31-findings/review/instructor-dialog-closed.png) |

</details>

<details>
<summary>4. Students can close the calendar window</summary>

Students can also close the window without an error message.

| Before | After |
| --- | --- |
| ![Student calendar close error](docs/screenshots/oct31-findings/review/before/image5.png) | ![Student calendar after closing](docs/screenshots/oct31-findings/review/student-dialog-closed.png) |

</details>

<details>
<summary>5. The calendar fits the screen</summary>

The calendar fits computer and phone screens without stretching beyond the page.

| Before | After |
| --- | --- |
| ![Oversized calendar before](docs/screenshots/oct31-findings/review/before/image6.png) | ![Calendar fitting the page after](docs/screenshots/oct31-findings/student-calendar-desktop.png) |

[View the calendar on a phone](docs/screenshots/oct31-findings/student-calendar-mobile.png).

</details>

<details>
<summary>6. Calendar details are easier to read</summary>

The instructor's calendar window has clearer headings, spacing, and a border. Cancelled lessons no longer offer a Log Session button here.

| Before | After |
| --- | --- |
| ![Calendar details before](docs/screenshots/oct31-findings/review/before/image7.png) | ![Calendar details after](docs/screenshots/oct31-findings/review/instructor-dialog-open.png) |

</details>

<details>
<summary>7. Download options stay out of the way</summary>

Schedule download options stay closed until needed and can be opened with one click.

| Before | After |
| --- | --- |
| ![Download options before](docs/screenshots/oct31-findings/review/before/image8.png) | ![Download options closed after](docs/screenshots/oct31-findings/review/exports-collapsed.png) |

[View the opened download options](docs/screenshots/oct31-findings/review/exports-expanded.png).

</details>

<details>
<summary>8. Each user group can have its own header colors</summary>

Settings now has separate header background and text colors for admins, instructors, and students. Colors save correctly even when the login page uses a picture.

The report did not include a before picture for this finding.

![New header color settings](docs/screenshots/oct31-findings/role-header-settings.png)

</details>

<details>
<summary>9. Past schedules are easier to recognize</summary>

Past schedules have a clear label and a different heading color so they are easier to distinguish from upcoming lessons.

| Before | After |
| --- | --- |
| ![Past schedules before](docs/screenshots/oct31-findings/review/before/image9.png) | ![Past schedules after](docs/screenshots/oct31-findings/review/past-schedules.png) |

</details>

<details>
<summary>10. Student training records can be downloaded</summary>

An Export PDF button lets users download the training list or an individual student's training record.

| Before | After |
| --- | --- |
| ![Training page before](docs/screenshots/oct31-findings/review/before/image10.png) | ![Training page with download button](docs/screenshots/oct31-findings/review/training-pdf-button.png) |

</details>

<details>
<summary>11. Enrollment review is properly arranged</summary>

Student details, licenses, and receipts appear in the correct places. The window fits computer and phone screens. Missing documents show a clear explanation.

| Before | After |
| --- | --- |
| ![Broken enrollment review before](docs/screenshots/oct31-findings/review/before/image11.png) | ![Enrollment review after](docs/screenshots/oct31-findings/enrollment-verification-desktop.png) |

[View enrollment review on a phone](docs/screenshots/oct31-findings/enrollment-verification-mobile.png).

</details>

<details>
<summary>12. Enrollments can be searched</summary>

Admins can find enrollments by student name, email, course, or enrollment number.

| Before | After |
| --- | --- |
| ![Enrollment list before](docs/screenshots/oct31-findings/review/before/image12.png) | ![Enrollment search after](docs/screenshots/oct31-findings/enrollment-search.png) |

</details>

<details>
<summary>13. Dashboard totals open the right list</summary>

Clicking a dashboard total opens its matching list, such as students, instructors, new students, active users, or active enrollments.

| Before | After clicking Students |
| --- | --- |
| ![Dashboard totals before](docs/screenshots/oct31-findings/review/before/image13.png) | ![Matching student list after](docs/screenshots/oct31-findings/review/dashboard-filtered-students.png) |

</details>

<details>
<summary>14. Finished training hours are clearly explained</summary>

When a student has completed the required hours, the form explains why another session cannot be added. TDC and PDC hours are counted separately for Combo students.

| Before | After |
| --- | --- |
| ![Extra session could be added before](docs/screenshots/oct31-findings/review/before/image15.png) | ![Completed training hours explanation after](docs/screenshots/oct31-findings/training-hours-completed.png) |

</details>

## Fixes October 30 2026

These earlier pictures show the fixes when they were added. Expand a fix to see its screenshot.

<details>
<summary>1. Pending account emails are visible</summary>

Admins can see who still needs to finish setting up an account, when the invitation expires, and whether to resend or remove it.

![Pending account emails](docs/screenshots/bug-report-2026-10-30/admin-user-management.png)

</details>

<details>
<summary>2. The Add Student window has a clear title</summary>

The window now says Add New Student, matching the other account forms.

![Add New Student window](docs/screenshots/bug-report-2026-10-30/admin-add-student.png)

</details>

<details>
<summary>3. Report downloads are in one menu</summary>

Users can download all records or just the selected records, as PDF or Excel files.

![Report download menu](docs/screenshots/bug-report-2026-10-30/admin-verify-session-completion.png)

</details>

<details>
<summary>4. Student training details open correctly</summary>

The View button opens the student's training record. If no training information is available, a clear message is shown.

![Student training details](docs/screenshots/bug-report-2026-10-30/admin-student-training.png)

</details>

<details>
<summary>5. Instructors can open calendar day details</summary>

Selecting a day shows its lessons and the available session actions.

![Instructor calendar day details](docs/screenshots/bug-report-2026-10-30/instructor-calendar-day.png)

</details>

<details>
<summary>6. Attendance, grades, and notes can be recorded together</summary>

Instructors can record lesson details directly from their schedule. The student list shows students assigned to them or students they have previously taught.

![Record a lesson from the schedule](docs/screenshots/bug-report-2026-10-30/instructor-session-log.png)

</details>

<details>
<summary>7. Recorded lessons appear for school approval</summary>

Admins can review the same lesson with its attendance, grade, training hours, and instructor notes.

![Recorded lesson ready for school approval](docs/screenshots/bug-report-2026-10-30/admin-logged-session-visible.png)

</details>

<details>
<summary>8. Instructors can view past schedules</summary>

Show Past Schedules reveals earlier lessons and their available actions.

![Past instructor schedules](docs/screenshots/bug-report-2026-10-30/instructor-past-schedules.png)

</details>

<details>
<summary>9. The session form includes attendance and grades</summary>

Lessons entered through the session form stay connected to the same training record used on the schedule.

![Session form with attendance and grades](docs/screenshots/bug-report-2026-10-30/instructor-manual-session.png)

</details>

<details>
<summary>10. Students can open calendar day details</summary>

Students can select a day to see their lessons, instructors, and lesson status.

![Student calendar day details](docs/screenshots/bug-report-2026-10-30/student-calendar-day.png)

</details>

<details>
<summary>11. The student calendar works on phones</summary>

The calendar and lesson window fit smaller screens.

![Student calendar on a phone](docs/screenshots/bug-report-2026-10-30/student-calendar-mobile.png)

</details>

<details>
<summary>12. School name and logo choices are respected</summary>

The header follows the school's chosen name, colors, and logo. Hidden names and removed logos stay hidden.

![School name and logo hidden](docs/screenshots/bug-report-2026-10-30/branding-name-and-logo-hidden.png)

</details>

## Fixes October 29 2026

<details>
<summary>1. Enrollment, payment, and receipt numbers are easier to follow</summary>

Each enrollment, payment, and receipt has a clear tracking number so staff can find the right record.

![Enrollment tracking numbers](docs/screenshots/capstone_01_enrollment_uids.png)

![Payment and receipt tracking numbers](docs/screenshots/capstone_02_payments_uids.png)

</details>

<details>
<summary>2. User groups have different colors</summary>

Admins, branch secretaries, instructors, students, and guests are easier to recognize in account lists.

![Different colors for user groups](docs/screenshots/capstone_03_role_badges_unique_colors.png)

</details>

<details>
<summary>3. Training logs can be archived and restored</summary>

Staff can move a log out of the active list without permanently deleting it, then restore it when needed.

![Active and archived training logs](docs/screenshots/capstone_04_session_logs_archive.png)

</details>

<details>
<summary>4. Inactive accounts can be archived and restored</summary>

System admins can archive unused accounts while keeping their history. Archived students and instructors can be restored to active status. Staff can also choose how long an account must be inactive before archiving it.

![Account archiving options](docs/screenshots/capstone_05_system_admin_user_archive.png)

![Archived accounts with restore buttons](docs/screenshots/capstone_05_system_admin_archived_tab.png)

</details>

<details>
<summary>5. Instructors and students have calendar views</summary>

Users can browse lessons by month and see their dates, course types, and booking status.

![Instructor calendar view](docs/screenshots/capstone_06_instructor_calendar_view.png)

![Student calendar view](docs/screenshots/capstone_07_student_calendar_view.png)

</details>

## Fixes October 27 2026

<details>
<summary>1. Payment codes and document pictures are easier to view</summary>

Payment codes open in a larger window for scanning. Users can enlarge or download the code and copy the payment number. Receipts and license pictures can also be enlarged, moved, rotated, or downloaded.

![Student payment page](docs/screenshots/12_actual_payment_checkout_page.png)

![Larger payment code window](docs/screenshots/12_qr_scan_modal_popup_opened.png)

![Copying the payment number](docs/screenshots/13_qr_scan_modal_copied_number.png)

![Payment code opened above the receipt form](docs/screenshots/17_qr_scan_modal_stacked_on_enrollment_modal.png)

</details>

<details>
<summary>2. Table action buttons stay within reach</summary>

On smaller screens, users can scroll across wide tables while keeping the action buttons visible.

![Table actions on a smaller screen](docs/screenshots/07_admin_table_narrow_screen_sticky_actions.png)

![Edit window opened from the table](docs/screenshots/08_admin_edit_modal_interacted.png)

</details>

<details>
<summary>3. User search and filters work together</summary>

Admins can search by name, email, or phone number and narrow the list by user group, account status, branch, or license expiry. Clear removes the selections when users want to start again.

![User search and filters](docs/screenshots/22_admin_user_management_search_and_filters.png)

![Users with expiring licenses](docs/screenshots/23_admin_user_management_license_filter_results.png)

![Empty search results with a Clear option](docs/screenshots/24_admin_user_management_empty_state_clear_filters.png)

</details>

<details>
<summary>4. Schedule downloads can be narrowed down</summary>

Users can choose dates and lesson status before downloading. School staff can also choose a student or instructor. Students receive their own records, and instructors receive records for their assigned lessons.

![Schedule download choices](docs/screenshots/03_admin_schedules_session_exports.png)

![Student schedule downloads](docs/screenshots/06_student_schedule_export.png)

</details>

## Earlier Fixes October 20 to 26 2026

<details>
<summary>1. Payment receipts open correctly</summary>

View Receipt opens the uploaded receipt instead of showing a missing-page error.

![Payment records with View Receipt](docs/screenshots/01_admin_payments_table.png)

![Opened payment receipt](docs/screenshots/02_payment_receipt_image_stream.png)

</details>

<details>
<summary>2. Instructors can download training logs</summary>

Instructors can download their full session history or one selected lesson as a PDF or Excel file.

![Training log download buttons](docs/screenshots/08_instructor_sessions_list_exports.png)

![Single lesson download buttons](docs/screenshots/09_instructor_session_detail_export.png)

</details>

<details>
<summary>3. License expiry reminders are easier to find</summary>

Students and instructors can see when their licenses are close to expiring, along with renewal guidance. Admins can find affected users and send a reminder.

![Student license reminder and renewal guide](docs/screenshots/12_student_license_expiration_alert.png)

![School license expiry overview](docs/screenshots/13_admin_license_expiration_monitoring_widget.png)

![License reminder actions in the user list](docs/screenshots/14_admin_user_management_license_filter.png)

![Instructor license reminder](docs/screenshots/15_instructor_license_expiration_alert.png)

</details>

<details>
<summary>4. Course transitions and certificate notices are easier to manage</summary>

Combo students can request their move from TDC to PDC from their dashboard for school review. Staff can also tell students when their certificate or claim stub is ready for pickup.

![Student training and pickup notice options](docs/screenshots/04_admin_student_training_claim_notice.png)

</details>

<details>
<summary>5. Schedule records can be downloaded</summary>

School staff and students can download schedule and attendance records. Schedule pages are also easier to read on computers and phones.

![School schedule download options](docs/screenshots/03_admin_schedules_session_exports.png)

![Student schedule download options](docs/screenshots/06_student_schedule_export.png)

</details>

<details>
<summary>6. Licenses and receipts can be uploaded without leaving the page</summary>

Users can replace a rejected license or submit a receipt in a pop-up window. The payment window also shows the school payment details and a scannable payment code.

![Receipt upload window](docs/screenshots/10_reupload_receipt_popup_modal.png)

![License upload window](docs/screenshots/11_reupload_license_popup_modal.png)

</details>

<details>
<summary>7. School admins can sign in and reach the dashboard</summary>

Sign-in problems that repeatedly sent school admins away from the dashboard were fixed. Missing information in a dashboard section no longer stops the rest of the page from opening.

![School admin dashboard](docs/screenshots/16_school_admin_dashboard_login_verified.png)

</details>

<details>
<summary>8. Progress pages handle missing lesson dates</summary>

Student progress and instructor reports can still open when a lesson does not yet have a scheduled date.

![Student progress page](docs/screenshots/17_instructor_student_progress_verified.png)

</details>

<details>
<summary>9. Students can enroll again after finishing a course</summary>

Completed courses stay in the student's history. Students can apply for another course or choose Enroll Again on a course they previously completed.

![Completed course with Enroll Again](docs/screenshots/18_course_completed_reenroll.png)

</details>

<details>
<summary>10. School pictures and branding can be changed</summary>

Users can update their profile pictures. School staff can change course pictures, the school logo, and header appearance. Removed or replaced pictures follow the user's choices.

![School appearance settings](docs/screenshots/20_admin_settings_smart_logo.png)

![Updated school logo in the header](docs/screenshots/21_admin_header_smart_logo.png)

</details>

<details>
<summary>11. Training downloads are available with session review</summary>

School staff can download training records from Verify Session Completion and narrow them by student, instructor, or dates. Reports include the selected names and dates.

![Training download choices beside session review](docs/screenshots/19_verify_session_completion_export_section.png)

</details>

