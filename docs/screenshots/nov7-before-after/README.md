# Before-and-after evidence

Captured November 8, 2026. Open `index.html` for the complete side-by-side gallery, or open any `*-comparison.png` for a labeled comparison image. Original, unaltered browser screenshots are named `*-before.png` and `*-after.png`.

There are **15 matched screenshot pairs**, covering student receipts, guest payment details, profile documents and activity, pickup dialogs, page-2 payment summaries, license and receipt viewers, missing images, enrollment actions, and pagination. Desktop and mobile versions are included where applicable.

The before version was reconstructed from Git revision `6a85c5c` in a separate container on local port 8005. The after version used the current application on port 8004. Both used the same temporary QA accounts, payment records and sample uploads. These are fresh baseline captures, not the original screenshots embedded in the findings document.

Observed comparisons, also recorded in `results.json`:

| Behavior | Before | After |
| --- | --- | --- |
| Page-2 payment summary | ₱0.00; 0 approved; 0 pending | ₱5,200.00; 14 approved; 15 pending |
| Profile documents and activity | Absent | Present |
| GCash receipt opens QR payment viewer | Yes | No; opens the standard image viewer |
| Pickup dialog at 1440 × 1000 | Top-left, x=0 and y=0 | Centered, x=452.5 and y=275 |
| Enrollment actions | No wrapping | Wrapping enabled |
| Student receipt controls | None | Present in desktop rows and mobile cards |
| Guest submitted payment details | Absent | Present |

Payment values include the temporary test payments and are evidence from the test run, not current business totals. Each account was given the same sample image to test receipt and license viewing; no existing uploads were changed.

Repeated validation passed:

- 9 transactional server checks, including totals across pages, preserved filters, and school/branch document-access restrictions.
- 12 desktop/mobile browser check groups, including real receipt clicks, nested viewers, profile pagination, centered dialogs, responsive actions and preserved payment QR details.
- No JavaScript or application server errors in the regression run.
- No JavaScript errors during either comparison capture.
- `git diff --check` passed.

All temporary QA records and uploads were removed. The baseline container was stopped and removed. The current application remained running.

Reproduction scripts: `tests/capture-nov7-comparison.mjs`, `tests/verify-nov7-findings.php`, and `tests/verify-nov7-findings.mjs`. The comparison script requires the isolated baseline to be available on port 8005; the two regression scripts use the existing local app on port 8004.
