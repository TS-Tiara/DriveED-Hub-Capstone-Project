# Panel Findings Fixes - November 25, 2026

## Branch

Changes are on `more-fixes-that-I-Found`, based on `main`.

## Implemented Updates

### School Admin

- Removed the duplicate Archived Enrollments control from the enrollment filters area.
- Improved additional-instructor display in Manage Schedule.
- Changed schedule capacity and availability wording from "Spots" to "Slots".
- Improved export dropdown positioning and scrolling.
- Improved modal action-footer positioning in Branch, Branch Manager, and Vehicle Management modals.
- Added CSV export for enrollment records, including filtered records and Student ID.
- Added consistent rounded styling to shared modal and page controls.

### Branch Manager

- Changed the license-reminder action to an icon-only button.
- Added an accessible tooltip and screen-reader label.

### Instructor

- Added an Excel export option beside Export PDF on the Student Training List page.
- Added a CSV export option to the Instructor Grades export menu.

### Student

- Reduced vertical roadmap spacing between steps and step descriptions.

### Global Interface

- Added an active notification-bell state with a solid bell icon.
- Preserved the notification badge while the dropdown is open.
- Added consistent rounded styling to shared buttons.

### System Admin

- Added a Branch Managers section below School Admins.
- Added Branch Manager name, school, branch, status, and creation details.
- Applied search and school filters to both School Admin and Branch Manager views.
- Existing Branch Manager activity remains recorded through existing user-management and SystemLog flows.

## Schedule and Export Support

- Added Branch filter support to the Manage Schedule controller and view.
- Added a Branch column to the schedule list.
- Preserved school primary-color usage for the Create Schedule button and schedule states.
- Improved export dropdown positioning for Verify Session Completion and Manage Schedule.

## Validation

- PHP syntax checks passed for changed controllers.
- Blade template compilation completed successfully.
- The enrollment CSV route is registered.
- The final Git diff passed whitespace validation.

Regression testing should cover enrollment exports, branch-filtered schedules, export positioning, modal footers, Branch Manager visibility, and notification bell behavior.

## Known Warning

Laravel validation reports a pre-existing PHP deprecation warning in app/Support/helpers.php for the nullable $request parameter. It does not prevent Blade compilation or route registration.
