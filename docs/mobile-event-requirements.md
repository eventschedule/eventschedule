# Mobile Add/Edit Event Requirements

The mobile client must only surface the same fields and actions that appear on the existing web add/edit event page. No additional hidden fields or behaviors are allowed.

## Event venue
- Event type toggles: in-person and online checkboxes; at least one must remain selected. 【F:resources/views/event/edit.blade.php†L468-L488】
- Venue selection: choose between using an existing venue or creating a new one. Existing venues are loaded for selection when present. 【F:resources/views/event/edit.blade.php†L468-L500】
- Venue search: when entering a venue email, trigger search of venues and allow selecting a result to populate the form. 【F:resources/views/event/edit.blade.php†L514-L535】
- Venue creation/edit fields: name, email, street address, city, state/province, postal code, country code. Include buttons for “View map,” optional “Validate address,” and “Done” to confirm selection. 【F:resources/views/event/edit.blade.php†L264-L353】

## Participants
- List existing participants with name, email (if present), YouTube link (if present), and controls to edit or remove non-linked users. 【F:resources/views/event/edit.blade.php†L430-L463】
- Participant sourcing: toggle between using an existing member or creating a new one; existing members are selectable from a dropdown. 【F:resources/views/event/edit.blade.php†L468-L500】
- New participant fields: required name plus optional email and YouTube video URL, with an Add action; searching by email should surface existing members to select. 【F:resources/views/event/edit.blade.php†L503-L535】

## Event details
- Required event name field. 【F:resources/views/event/edit.blade.php†L538-L546】
- Optional URL slug field with live preview and copy link action when an event already exists. 【F:resources/views/event/edit.blade.php†L547-L576】
- Optional schedule grouping dropdown when groups are available. 【F:resources/views/event/edit.blade.php†L577-L597】
- Optional category dropdown. 【F:resources/views/event/edit.blade.php†L599-L607】
- Schedule type radio buttons: one-time or recurring; recurring exposes day-of-week checkboxes. 【F:resources/views/event/edit.blade.php†L609-L688】
- Start date and time (required), duration in hours, flyer image picker with delete option, and rich-text description. 【F:resources/views/event/edit.blade.php†L691-L735】
- Cross-posting to other curators: checkbox list plus optional group selection per curator when available. 【F:resources/views/event/edit.blade.php†L737-L780】

## Ticketing and payments
- Payment method dropdown (cash, Stripe, Invoice Ninja, or payment URL when configured) with link to manage payment methods. 【F:resources/views/event/edit.blade.php†L840-L856】
- Required ticket currency selector. 【F:resources/views/event/edit.blade.php†L860-L885】
- Cash-only helper field: payment instructions (rich text) shown when payment method is cash. 【F:resources/views/event/edit.blade.php†L887-L891】
- Ticket types: each entry captures price, quantity (or unlimited), optional type when multiple tickets exist, optional description, and supports add/remove actions. 【F:resources/views/event/edit.blade.php†L893-L956】
- Total tickets mode toggle: individual quantities vs combined total when multiple tickets share the same quantity. 【F:resources/views/event/edit.blade.php†L926-L952】
- Ticket notes (rich text) for additional purchase details. 【F:resources/views/event/edit.blade.php†L961-L965】
- Unpaid ticket handling (shown only for limited paid tickets): enable/disable auto-expiration with hours value, enable/disable reminder emails with repeat interval hours, and hidden fields defaulting values to 0 when toggles are off. 【F:resources/views/event/edit.blade.php†L967-L1017】
- Optional “Save as default” checkbox for members. 【F:resources/views/event/edit.blade.php†L1020-L1027】

## Submission and sync
- Primary actions: Save and Cancel; Delete is available when editing an existing event. 【F:resources/views/event/edit.blade.php†L1046-L1060】
- Google Calendar sync controls for existing events linked to a Google account: show sync status, and buttons to sync or remove from Google Calendar. 【F:resources/views/event/edit.blade.php†L1062-L1095】

## Open questions for the web API
Subject: Clarifications needed for Event Update API (to align mobile with web)

The mobile client needs clarification on how to mirror web update behavior while avoiding hidden logic. Please confirm the following, keeping responses aligned to the web UI’s visible fields only:

1. **Update endpoint contract**
   - What is the exact HTTP method and path for updating an event (e.g., `PATCH /api/events/{id}`, `POST /api/events/{id}`)?
   - What is the minimal valid payload for updating only basic fields (name, description, dates)? Please include a working example JSON body.
   - Do you support partial updates? If so, which fields are optional/omittable on update?

2. **Role fields on update (venue, members, curator)**
   - Should `venue_id` be included on update? If the event was created under a venue subdomain (venue-scoped schedule), should `venue_id` always be omitted when updating?
   - If `venue_id` is included but unchanged, is that allowed?
   - How should participants be updated? Does the update endpoint accept a `members` array of role IDs? If not, is there a separate endpoint for adding/removing participants? If accepted, should the client send the full replacement list or only diffs (add/remove)?
   - Are there any other role-based fields (e.g., `curator_role_id`) that must be included or must be omitted on update?

3. **Validation rules for role IDs**
   - What are the valid sources of `venue_id` for edits? Must the venue belong to the same schedule/subdomain as the event, and can an event be reassigned to another venue during update?
   - For `members`, which role types are accepted? Do member IDs have to belong to a specific schedule or subdomain?
   - Under what constraints could `"No query results for model [App\Models\Role]"` occur even when the ID exists elsewhere (e.g., different schedule or tenant)?

4. **Error semantics**
   - When `"No query results for model [App\Models\Role]"` occurs during update, which field is being resolved (`venue_id`, `members`, `curator_role_id`)?
   - Can the server return field-specific errors (e.g., `errors: { venue_id: ["not found"] }`) to help the client diagnose issues?
   - Are logs or middleware available that can reveal which specific ID failed lookup so we can pinpoint the problematic field/value?

5. **Create vs update differences**
   - Are there intentional differences between create and update payloads (e.g., create accepts `members` while update does not; create infers venue from subdomain while update expects no `venue_id`)?

6. **Include/expand parameters**
   - Is `include=venue,talent,tickets` (or similar) supported on update responses? Which include values are allowed or recommended for edit responses?

7. **Versioning and rollout**
   - Were there recent changes to the update route (e.g., method switched from PUT to POST)?
   - Do clients need to gate behavior based on API version or capabilities? If so, what capability flags or version markers should the client use?

If possible, please also share:
- A curl example that successfully updates only name/description (no role fields).
- A curl example that successfully updates `venue_id` (if supported), and one that updates `members` (if supported), noting whether they are full replacements or diffs.
