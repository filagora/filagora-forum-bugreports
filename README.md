# Filagora forum bug reports

Bug reports on forum threads, an optional add-on for [`filagora-forums`](https://github.com/filagora/filagora-forums). A site that does not install it has no table, no staff screen and no bug report UI.

Moderators open a report on a thread, assign it and move it through its statuses. Bug reports are a moderator's tool: only people who may moderate the forums can open, change or remove them. The dashboard screen needs the full `filagora.forums.moderate` ability: a user holding only core's frontend-only `filagora.moderate_frontend` can work reports from your own pages but does not see the screen.

## What it provides

### What the host does

Like forums, this package has no public pages: the host's own thread pages call it.

- `BugReports`: `report()`, `update()`, `forThread()`, `latestStatusByThread()`, and `hideResolved()` for a thread query.
- `ResolvedBugThreadVisibility`: a moderator's choice to hide threads whose reports are all closed out.
- `$thread->bugReports`: the relation on the forums `Thread` model.
- `BugReportChanged`: fired on every opened or changed report, from the host's pages or the staff screen, with one translated line per change. The package sends no mail of its own, so listen for this to tell people.

## Requirements

PHP 8.4, Laravel 13 and Filament 5. Tested against MariaDB 11.4. Requires [`filagora/filagora-core`](https://github.com/filagora/filagora-core) and [`filagora/filagora-forums`](https://github.com/filagora/filagora-forums).

## Install

```bash
composer require filagora/filagora-forum-bugreports
php artisan migrate
```

A site that already has the `bug_reports` table runs `php artisan filagora:adopt` instead.

Add the staff screen to your panel:

```php
->plugins([FilagoraForumBugReportsPlugin::make()])
```

## Configuration

`config/filagora-forum-bugreports.php`:

- `thread_route`: the host's named route for a thread page (default `threads.show`). The staff screens link to it, and show no link if the route does not exist.
- `user_attribute`: the attribute that names a person on a report. Null uses core's `filagora.user_title_attribute`.

## Extending

Extend through documented contracts, events and registries. Anything not documented
is `@internal`.

## Testing

```bash
composer test
```

Tests run against a real MariaDB, never SQLite, so engine behaviour (FULLTEXT,
`ONLY_FULL_GROUP_BY`, collation) matches production. Host and credentials come from
your environment; the database is always named `testing`, never your development one.

## Local development (DDEV)

`.ddev/` is included. `ddev start` creates both `db` and `testing` (run `ddev restart` once
if `testing` is missing), then `ddev exec vendor/bin/pest`.

## Licence

MIT. See [LICENSE](LICENSE).
