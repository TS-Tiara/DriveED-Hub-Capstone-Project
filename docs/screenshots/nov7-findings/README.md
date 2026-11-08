# November 7 findings verification

Verified locally on November 8, 2026 at desktop (1440 × 1000) and mobile (390 × 844) sizes.

| Finding | Implemented behavior |
| --- | --- |
| Missing receipts in My Payments | Student and guest payment history includes receipt buttons on desktop and mobile, using authorized receipt URLs. |
| Pickup notice opens at the top left | The native pickup dialog is centered, constrained to the viewport, and scrolls on short screens. |
| Inconsistent image viewers | Instructor licenses, student documents, admin payment snapshots and receipts use the shared zoom/pan/rotate viewer. Explicit learner payment QR controls retain their dedicated viewer and account information. |
| Missing images open the QR viewer | General images never infer QR mode from GCash text or filenames. Missing and invalid sources show an unavailable-image message. |
| Summaries become zero after pagination | Payment pagination operates on a cloned query, leaving summary aggregates independent of page offsets. Pagination and browser history retain filter parameters. |
| Student license missing from User Management | Profiles load submitted license documents through the existing authorized streaming route, including legacy stored data. Staged enrollment credentials are also shown. |
| User profiles lack documents and activity | Documents and Activity includes license previews, enrollment history, payment transactions and receipts, and bookings. Instructor profiles include teaching bookings. Each activity list paginates inside the profile. |
| Enrollment actions overlap | Desktop actions wrap within their cell; mobile cards retain the existing responsive action layout. |
| Guest enrollment requests lack payment details | Each request displays submitted payment amounts, methods, references, statuses, dates and receipt buttons, including legacy enrollment-only submissions. |
| Inconsistent pagination | The shared numbered paginator and global styles cover enrollment, user, payment and session lists, with consistent sizes, summary format, active/disabled states and mobile touch targets. |

Validation:

- `docker exec --user www-data driveed_hub_app php tests/verify-nov7-findings.php`: 9 checks passed, with all database changes rolled back. Includes cross-school and cross-branch access restrictions.
- `node tests/verify-nov7-findings.mjs`: 12 browser check groups passed, with no JavaScript or application server errors. Temporary accounts, payments, enrollments and sample uploads were removed automatically.
- PHP syntax checks passed for all changed controllers and routes.
- All Blade templates compiled successfully under the web-server user.
- `git diff --check` passed.

Screenshots use temporary QA accounts and sample uploads. `results.json` records the browser checks.

Local data limitation: the existing `student1@driveedhub.test` license file is zero bytes. The profile now exposes the document and handles its failed preview, but its original contents require a fresh upload. No existing uploads were replaced or fabricated.
