# Working on mythosevents.com

## Getting changes from Claude to the live site

**A Cowork cloud session cannot push to GitHub.** This was tested exhaustively on
2026-09-05/06: the git proxy rejects the push with a 403 before any credential is
checked ("not in this session's authorized repository set"), a personal access token
does not help because the token is never reached, and the `add_repo` tool the error
message recommends does not exist in Cowork. The GitHub REST API is blocked the same
way. Do not spend a session rediscovering this, and do not ask Wade for a token — it
will not work and he has already been through that.

The pipeline that does work:

1. Claude requests folder access to `~/Documents/GitHub/websites`. One approval covers
   all four site repos.
2. Claude edits the real files on Wade's Mac in place, via `device_stage_files` /
   `device_commit_files`. No patch files, no downloads through the chat.
3. Wade reviews the diff in **GitHub Desktop** and clicks Commit + Push.
4. GitHub Actions FTP-deploys `public_html/` to SiteGround. See `DEPLOYMENT.md`.

Claude Code — the CLI, or claude.ai/code — *can* push directly. A task with many
commits belongs there rather than in Cowork.

This repo's deploys were confirmed working on 2026-09-06.

## The events system

Organizers who are approved can post events, and Wade moderates them before they go
live. The pieces:

- `organizers/post.php` — submission form. Requires a logged-in user whose
  `application_status` is `approved`; submits with status `pending_approval`.
- `admin/events.php` — moderation panel. Gated on `people.is_admin = 1` (same flag as
  `join/admin.php`, the people-approval panel) — **not** an email-domain check. That
  used to be an `@mythosevents.com` email match, which locked Wade's own everyday
  login out; fixed 2026-10-14. The two admin panels live at different paths
  (`/join/admin.php` for people, `/admin/events.php` for events) and are now
  cross-linked in both panels' nav and on the main dashboard — don't assume one links
  to the other without checking, that gap is exactly what caused the original
  confusion.
- `events/index.php` — public listing. Shows `approved` events only, soonest first.
- `organizers/index.php` and `join/dashboard.php` — both show a Create Event /
  Schedule a Date entry point to approved organizers; `join/dashboard.php` is the one
  people actually land on after login, so new organizer-facing links belong there
  first.

The `events` table is created by `events_migration_runner.php`, which is a one-time
script guarded by a key in the query string. **It should be deleted from the server
once it has been run** — if it is still present, that is an outstanding task, not a
fixture.

A 500 on `events/index.php` almost always means the `events` table does not exist yet:
the page prepares a query against it, gets `false` back, and dies on `->execute()`.

## Working with Wade

He is not comfortable navigating in Terminal. Prefer GitHub Desktop or a browser-based
admin page. When a command really is needed, give one complete paste-able line with the
full path in it, not a `cd` he has to compose himself.
