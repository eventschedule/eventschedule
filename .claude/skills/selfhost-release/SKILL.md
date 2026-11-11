---
name: selfhost-release
version: 1.0.0
description: "Tag and publish the next selfhost release of Event Schedule: write the release notes, bump the version, run the Build Release workflow, and put the notes on the GitHub release. Use when the user says 'tag a release', 'tag the next selfhost release', 'cut a release', 'ship a release' or 'publish the release'. For release notes alone (no tag), follow CLAUDE.md's Release Notes section instead."
---

# Selfhost release

A release is the zip `.github/workflows/build.yml` builds and attaches to a GitHub release. Every
selfhost install's updater downloads it (`config/self-update.php`, `AppUpdateService`), so the tag
must point at a commit that is green on CI. Releases are patch bumps by default (`v1.0.140` ->
`v1.0.141`); bump the minor or major only if the user asks.

## 0. Before anything

- `git fetch --tags -q origin && git status -sb`: local `main` matches `origin/main` and the tree
  holds nothing of yours uncommitted. Other sessions share this checkout: never commit their files.
- The last release is `gh release view --json tagName` (cross-check `version_installed` in
  `config/self-update.php`). The new version is that plus one patch.
- CI is green at HEAD: `gh run list --branch main --limit 3 --json headSha,conclusion,name`. A red
  or running HEAD is a stop: report it and wait, never tag over it.
- If the user did not ask for a review first, offer one in a line: a release goes to every selfhost
  install, and a bug found afterwards ships with it.
- Raising the PHP minimum is a release of its own with a renamed zip. Follow CLAUDE.md's "Raising
  the PHP minimum" rule if `composer.json`'s `require.php` changed since the last tag.

## 1. Release notes

Follow CLAUDE.md's **Release Notes** section exactly (house style, `Added:` / `Updated:` / `Fixed:`
bullets, a user-guide link on every Added and Updated bullet taken from `getDocSearchIndex()` or
`HelpUtils`, never invented; none on Fixed; no em dashes; "schedule", "selfhost").

- Read `git log <last-tag>..HEAD --oneline` and the commits behind each bullet. Skip test-only, CI,
  version bumps, hosted-only work (the demo reset, the growth export, marketing and SEO pages).
- Check each anchor exists: `grep -o 'id="<anchor>"' resources/views/marketing/docs/<page>.blade.php`.
- A security fix whose advisory is still a draft
  (`gh api repos/eventschedule/eventschedule/security-advisories/<GHSA id> --jq .state`) is described
  in plain words with no advisory link and no exploit detail. Tell the user afterwards that the
  advisory can be published now, with the new version as the patched one.
- Write the bullets (body only, no title line) to `release-notes-vX.Y.Z.md` in the session
  scratchpad, outside the repo, and print them in chat. The user copies from that file.

## 2. Bump the version

Exactly two lines change, as in every earlier "Update version" commit (`git log --grep="Update version"`):

- `.github/workflows/build.yml`: `tag_name: "vX.Y.Z"`
- `config/self-update.php`: the default of `SELF_UPDATER_VERSION_INSTALLED`

`git grep -n "<old version>"` finds them. Leave the other hits alone: the tests in
`AdminAppUpdateTest` and the example tag in the Docker guide name a version on purpose.

Commit only those two files, message `Update version`, with no attribution trailer (CLAUDE.md), and
push by id: `git push origin $(git rev-parse HEAD):main`.

## 3. Run the Build Release workflow

```bash
gh workflow run build.yml --ref main
gh run list --workflow build.yml --limit 1 --json databaseId,headSha,status
gh run watch <id> --exit-status
```

Check that the run's `headSha` is the bump commit. The workflow builds the zip, then creates the
tag `vX.Y.Z` at that commit and a release named "Latest Release" with `eventschedule.zip` attached.
It sets no body. If it fails, read `gh run view <id> --log-failed`; a run that failed after the
release step may have left a tag or release behind, so check before re-running.

## 4. Put the notes on the release, and check

```bash
gh release edit vX.Y.Z --notes-file <scratchpad>/release-notes-vX.Y.Z.md
gh release view vX.Y.Z --json tagName,name,isDraft,isLatest,body,assets,targetCommitish
```

Confirm all of these, and say so:
- the body is the notes file, byte for byte. Compare them with
  `diff <(gh release view vX.Y.Z --json body --jq .body) <notes file>`, which should print nothing
  but a possible trailing newline;
- `isLatest` is true and `isDraft` false, and `eventschedule.zip` is attached;
- `gh api repos/eventschedule/eventschedule/releases/latest --jq .tag_name` answers the new tag,
  which is what the updaters read.

## 5. Report

The version, the release link, the notes file path, and anything left for the user: a draft
security advisory to publish, or a hosted deploy (that is separate: `docs/NEXUS_RELEASE.md`).
