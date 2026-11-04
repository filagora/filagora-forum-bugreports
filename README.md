# Filagora forum bug reports

Bug reports on forum threads, an optional add-on for [`filagora-forums`](https://github.com/filagora/filagora-forums). A site that does not install it has no table, no staff screen and no bug report UI.

Moderators open a report on a thread, assign it and move it through its statuses. Bug reports are a moderator's tool: only people who may moderate the forums can open, change or remove them.

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

## What the host does

Like forums, this package has no public pages: the host's own thread pages call it.

- `BugReports`: `report()`, `update()`, `forThread()`, `latestStatusByThread()`, and `hideResolved()` for a thread query.
- `ResolvedBugThreadVisibility`: a moderator's choice to hide threads whose reports are all closed out.
- `$thread->bugReports`: the relation on the forums `Thread` model.
- `BugReportChanged`: fired on every opened or changed report, from the host's pages or the staff screen, with one translated line per change. The package sends no mail of its own, so listen for this to tell people.

## Configuration

`config/filagora-forum-bugreports.php`:

- `thread_route`: the host's named route for a thread page (default `threads.show`). The staff screens link to it, and show no link if the route does not exist.
- `user_attribute`: the attribute that names a person on a report. Null uses core's `filagora.user_title_attribute`.

## License

MIT.
