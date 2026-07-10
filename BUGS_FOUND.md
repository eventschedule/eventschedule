# Bugs found during the refactor campaign

Latent bugs surfaced by characterization tests (REFACTOR_PLAN.md rule R4).
Each is pinned by a test asserting the CURRENT behavior and is preserved
bug-for-bug through the refactor. Fixes happen only outside refactor phases,
as owner-approved standalone commits that flip the pinned test in the same
commit as the fix.

Entry template:

```markdown
## BUG-NNN - <one-line summary>
- Found: <date>, Phase <id> (<context>)
- Location: <file> ~<line>
- Repro: <request/state that triggers it>
- Current behavior: <what happens>
- Expected behavior: <what the code's own comments/intent imply>
- Pinned by: <TestClass::test_method>
- Severity: low|medium|high | Status: preserved, not fixed
```

---

## BUG-001 - claim_venue_ownership's email_verified_at copy is undone by the Role updating hook

- Found: 2026-07-10, Phase P11 (saveEvent characterization)
- Location: app/Repos/EventRepo.php ~409-434 (claim block) vs app/Models/Role.php ~313-317 (updating hook)
- Repro: authenticated user POSTs event.store with venue fields + `claim_venue_ownership=1` on a hosted install
- Current behavior: the claim block sets `$venue->email = $authUser->email` and copies `email_verified_at`, but the second `save()` runs the `Role::updating` hook, which sees the dirty email and (hosted) nulls `email_verified_at` and sends a verification email. `isClaimed()` therefore returns FALSE for the just-claimed venue (the unclaimed-venue editability gate stays open), and the claimer receives a verify-your-email notification for a venue they explicitly claimed. The same applies to the copied `phone_verified_at` when the user has a phone.
- Expected behavior: the block's own comment says the copy exists "so isClaimed() returns true and the editability gate locks out unrelated followers" - i.e. the venue should end up verified without a redundant verification email.
- Pinned by: EventSaveVenueCharacterizationTest::test_claim_venue_ownership_sets_owner_and_default_role
- Severity: low | Status: preserved, not fixed
