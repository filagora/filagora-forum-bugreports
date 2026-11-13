# Changelog

All notable changes to this package are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

## [0.1.2] - 2026-11-13

### Changed

- The README follows the layout shared by every Filagora package (What it provides, Requirements, Install, Configuration, Extending, Testing, Local development, Licence), so the docs page built from it reads the same as the others. No code changes.

## [0.1.1] - 2026-11-12

### Fixed

- The dashboard's bug reports screen needs the full `filagora.forums.moderate` ability, like the forum screens. It was gated only by `BugReportPolicy`, which asks `ForumAccess::canModerate()`, and since `filagora-forums` 0.6.5 that also accepts core's frontend-only `filagora.moderate_frontend`, so a frontend-only moderator could open the screen. The policy is unchanged: they can still work the reports from the host's own pages.

### Changed

- Requires `filagora-core` 0.2.3 and `filagora-forums` 0.6.5 or later.

## [0.1.0] - 2026-11-04

### Added

- Bug reports on forum threads: the `bug_reports` table with an adoption snapshot, the `BugReport` model and `BugReportStatus`, `BugReportPolicy` (moderators only), the `BugReports` service, `ResolvedBugThreadVisibility`, the `BugReportChanged` event, the `bugReports` relation on threads, and a staff screen through `FilagoraForumBugReportsPlugin`.
