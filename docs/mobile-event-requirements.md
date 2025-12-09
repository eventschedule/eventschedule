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

## Web API update expectations (resolved)
The backend now exposes `PATCH /api/events/{event_id}` for edits. Key behaviors the mobile client should mirror:

1. **Endpoint contract** — Ownership required; otherwise 403. Partial payloads are allowed; `name`/`starts_at` become required only when present, and venue/address/url fields keep the required-without-all rule when any are supplied. 【F:routes/api.php†L16-L22】【F:app/Http/Controllers/Api/ApiEventController.php†L231-L254】
2. **Role fields** — Existing venue, members, and curators are preserved when omitted; providing new arrays merges with current associations so the client can add/edit/remove talent, curators, or venues without rebuilding the full list. Schedule slugs are resolved to `current_role_group_id` or return 422 if unknown. 【F:app/Http/Controllers/Api/ApiEventController.php†L256-L316】
3. **Validation and errors** — Category slugs map to IDs; unknown categories or schedule slugs return 422. Unknown event IDs return 404. 【F:app/Http/Controllers/Api/ApiEventController.php†L256-L320】
4. **Create vs update** — Creation infers defaults from `{subdomain}`; updates reuse the existing event’s roles and keep associations unless explicitly changed. Responses always return the full `toApiData()` payload. 【F:app/Http/Controllers/Api/ApiEventController.php†L70-L220】【F:app/Http/Controllers/Api/ApiEventController.php†L231-L320】【F:app/Models/Event.php†L808-L876】
