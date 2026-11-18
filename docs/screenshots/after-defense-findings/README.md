# Panel findings before-and-after screenshots

The `before` images are screenshots extracted from the supplied `after defense comments of panel.docx`. The `after` images are captures of the updated local app on November 17, 2026.

| Finding | Before | After |
| --- | --- | --- |
| Guest registration consent | <img src="before/image1.png" alt="Before: registration consent checkboxes" width="320"><br><img src="before/image2.png" alt="Before: privacy policy dialog" width="320"> | <img src="after/registration-policy-gate.png" alt="After: scroll gate in the privacy policy" width="380"><br><img src="after/registration-consent-enabled.png" alt="After: consent boxes unlocked after reading both documents" width="380"> |
| Instructor session logging | <img src="before/image3.png" alt="Before: single enrollment selector in session logging" width="360"> | <img src="after/instructor-session-search-batch-grade.png" alt="After: session search, filters, and batch grading" width="380"> |
| Branch on course enrollment | <img src="before/image4.png" alt="Before: course enrollment without branch selection" width="320"> | <img src="after/enrollment-branch-selector.png" alt="After: enrollment form with preferred branch" width="380"> |
| Branch-specific schedule and instructors | <img src="before/image5.png" alt="Before: schedule creation without branches" width="320"><br><img src="before/image6.png" alt="Before: instructor choices not filtered by branch" width="320"> | <img src="after/schedule-branch-selector.png" alt="After: selected branch in schedule creation" width="380"><br><img src="after/instructor-branch-filter.png" alt="After: instructor choices filtered to the selected branch" width="380"> |
| Misleading enrolled badge | <img src="before/image7.png" alt="Before: enrolled labels on available schedules" width="360"> | <img src="after/student-schedule-clean-label.png" alt="After: student schedule without the enrolled badge" width="380"> |

## Browser verification

- Registration: each policy’s acceptance action remained unavailable until its own content was scrolled to the bottom. After both policies were read, the two consent checkboxes were enabled and remained unchecked; no account was created.
- Instructor sessions: searching for `Miguel Francisco Ramos` returned two matching session rows. The filtered view showed type/date filters and the batch-grade controls.
- Branch assignment: the course-enrollment form exposed a preferred-branch selector. The schedule form allowed branch selection, and selecting Main Campus - Clark left three instructors available for assignment.
- Student schedule: the demo student used for the capture had no confirmed schedule slots. The screenshot documents the updated schedule view; the removed badge is also covered by the source regression check.

The Node regression checks passed: `node --test tests/verify-after-defense-findings.mjs tests/verify-nov15-findings.mjs` (**8 tests**). `git diff --check` also passed. PHP/Docker integration tests were unavailable in this environment.
