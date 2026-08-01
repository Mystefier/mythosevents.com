# Mythosevents.com Deployment Workflow

## Standing Authorization

Claude has standing permission to:
- **Commit changes** to git (with descriptive commit messages)
- **Push to `main` branch** — automatically triggers GitHub Actions deploy to SiteGround FTP
- **Edit/create/delete files** in this folder (and the repo)
- Access credentials via `.claude/secrets.local.json` (stored locally, git-ignored)

## Typical Workflow

When making changes to mythosevents.com:

1. **Code changes** — edit HTML/PHP/CSS files in `public_html/`
2. **Stage changes** — `git add public_html/<files>`
3. **Commit** — `git commit -m "descriptive message"`
4. **Push** — `git push origin main`
5. **Deploy** — GitHub Actions automatically pushes to SiteGround within ~30 seconds
6. **Live** — changes go live at `https://mythosevents.com/`

## Notes

- This site has its own standalone repo: `github.com/Mystefier/mythosevents.com`
  (previously nested inside `wadehawkins.world` — split out on 2026-07-31)
- Repo secrets (`FTP_SERVER`, `FTP_USERNAME`, `FTP_PASSWORD`) are configured directly
  on this repo under Settings → Secrets and variables → Actions
- No manual approval needed for routine pushes to `main`
- All changes are git-tracked and can be reviewed in the commit history

## For Reference

Permissions contract carried over from `wadehawkins.world/.claude/permissions.md` —
same standing authorization applies here (push to main, edit/create files, no
destructive git ops without approval).
