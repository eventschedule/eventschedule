---
name: support
description: Draft answers to Event Schedule support questions (the in-app support chat, GitHub issues, pasted email) from the code and the user guide, let the operator edit them, send only what he approves, and learn from his edits. Use when the user types /support, says "answer support", "any support questions", "draft a reply" for a customer, or pastes a customer's question.
---

# Support answers

Three channels, one loop: gather what waits, draft, he edits, send what he approves, learn
from the difference. Nothing is ever sent without his explicit go-ahead for THAT reply in this
session. A support reply reaches a real customer, and a GitHub comment is public for good.

Everything about customers stays on this machine, under `~/.claude/support/` (the repo is
public): drafts, the learning log, the style guide. Never write a customer's name, address or
question into the repo, a commit, a test or a memory file. A memory may hold a PRODUCT fact a
correction taught (with no customer in it).

## 0. Read what earlier edits taught

- `~/.claude/support/STYLE.md`: his voice, rules and facts learned from his edits. Follow it over
  your own defaults.
- The newest ten files in `~/.claude/support/learning/` (each a draft beside what he sent).

## 1. Gather

Only the channels he asked for; all three for a bare `/support`.

- **Support chat** (hosted, `/admin/support`):
  `~/.config/eventschedule/do-console.py eventschedule eventschedule 'php artisan app:support-pending' --timeout 120`
  prints `{"waiting":[...]}`: each thread whose latest message is theirs, with its encoded id,
  who is asking, their schedules and plan, and the newest messages. Read-only.
- **GitHub issues**: `gh issue list --repo eventschedule/eventschedule --state open --json number,title,author,updatedAt,comments`,
  then `gh issue view <n> --repo eventschedule/eventschedule --comments` for any whose last comment
  is not from `hillelcoren` (or that has none).
- **Email**: what he pasted. There is no inbox to read.

Say in one line what is waiting (counts per channel) before drafting. A thread whose last message
only closes it ("thanks", "amazing, thank you") needs no reply: list it as answered and draft
nothing, unless he asks for a sign-off.

## 2. Draft

For each question:

- Find the answer in the code and the user guide, not from memory: `docs/FEATURES.md` for what a
  plan includes, `resources/views/marketing/docs/` for how the guide explains it (link the guide
  page, `https://eventschedule.com/docs/{slug}#{anchor}`, with anchors taken from
  `MarketingController::getDocSearchIndex()` or `app/Utils/HelpUtils.php`, never invented), the
  controllers and views for what really happens. Tight greps, a few lines each.
- A bug is not a support answer. Say in the draft's notes that it is one, where, and what the fix
  would be, and ask whether to fix it. Do not fix it unasked. The reply can say it is being looked
  at, or give a way around it.
- Selfhost vs hosted matters: a GitHub issue is almost always selfhost (its version, its own mail
  and storage); the chat is hosted. A selfhost version older than the fix: say which release has
  it.
- Write the reply in the language they wrote in. Keep it short and plain, in his voice (STYLE.md).
  Follow the repo's writing rules: no em dashes, "schedule" never "role", "selfhost".
- A chat reply is at most 2000 characters (the reply box's limit).

Write each draft to `~/.claude/support/drafts/<YYYY-MM-DD>/<channel>-<id>.md`, where channel is
`chat`, `github` or `email` and id is the chat's encoded id, the issue number, or a short slug:

```
# <channel> <id>: <one-line summary of the question>
To: <name, and where it goes: chat / issue #n / email>
Notes (not sent): <facts checked, anything unverified, a bug found, what is his to decide>

--- reply ---
<the reply>
```

Copy each one to `<same name>.draft` beside it: that copy is what you wrote, and step 4 compares
his version against it. Then tell him, in a few lines, where the drafts are and what each one
says, and that he can edit the `--- reply ---` part of any file (or tell you the change in chat)
and then say `send <id>`, `send all`, or `skip <id>`.

## 3. Send what he approved

Only the replies he named. Read each file again first: his edit is the reply.

- **Chat**: base64 the reply and run
  `~/.config/eventschedule/do-console.py eventschedule eventschedule 'php artisan app:support-reply <id> --body=<base64>' --timeout 120`.
  It sends exactly as the reply box does (saved, emailed to them a few minutes later if unread,
  pushed). Check its "Sent as message ..." line; on an error, send nothing else and tell him.
- **GitHub**: `gh issue comment <n> --repo eventschedule/eventschedule --body-file <a file with the reply>`.
  Never close the issue unless he says so.
- **Email**: copy the reply to his clipboard (`pbcopy`) and say so; he sends it himself.

A reply he changed in chat rather than in the file: write his version into the file first, so
step 4 sees it.

## 4. Learn from his edits

For each reply sent (or copied):

- Compare the `.draft` copy with what went out. If he changed nothing, note that it was sent as
  drafted.
- Save `~/.claude/support/learning/<YYYY-MM-DD>-<channel>-<id>.md`: the question in a line, the
  draft, what was sent, and what changed and why it probably changed (shorter, warmer, a fact
  corrected, a link added, a promise removed).
- Fold anything that will recur into `~/.claude/support/STYLE.md`, as a short rule with one
  example, under Voice, Facts or Never. Merge with what is there rather than adding a duplicate,
  and drop a rule his later edits contradict. A fact about the product goes there too; if it also
  matters beyond support, save it to memory as well (without the customer).
- Tell him in one line what you took from his edits.

Delete a `drafts/` folder once everything in it is sent or skipped. `learning/` is kept.
