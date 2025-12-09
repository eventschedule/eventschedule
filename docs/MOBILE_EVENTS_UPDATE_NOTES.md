# Mobile event update clarifications

This memo summarizes the current backend behavior around updating events via the public API.

## 1. Update endpoint contract
- New route: `PATCH /api/events/{event_id}` guarded by the `resources.manage` ability. It requires ownership of the event (`event.user_id === auth()->id()`) and returns 403 otherwise. 【F:routes/api.php†L16-L22】【F:app/Http/Controllers/Api/ApiEventController.php†L231-L242】
- Minimal payload: send only the fields you want to change. `name` and `starts_at` remain required **only when provided**, and at least one of `venue_id`, `venue_address1`, or `event_url` must be present if any of them are supplied. 【F:app/Http/Controllers/Api/ApiEventController.php†L244-L254】
- Successful updates return the fully refreshed event object plus `meta.message: "Event updated successfully"`. 【F:app/Http/Controllers/Api/ApiEventController.php†L316-L320】

## 2. Role fields on update
- If no role fields are provided, the backend preserves existing venue, member, and curator relationships by seeding the request with current values before persisting. When role arrays are provided, the update logic merges them with existing associations so mobile clients can add, edit, or prune venue/curator/talent links without reconstructing the full roster. 【F:app/Http/Controllers/Api/ApiEventController.php†L281-L316】
- When a `schedule` slug is supplied, it is resolved against the updater's `currentRole` groups; unknown slugs return 422. 【F:app/Http/Controllers/Api/ApiEventController.php†L256-L268】

## 3. Validation rules for role IDs
- `venue_id` remains optional but must accompany `venue_address1`/`event_url` rules when those fields are present. Category name slugs are resolved the same way as creation, with a 422 response when no match is found. 【F:app/Http/Controllers/Api/ApiEventController.php†L244-L298】
- Member and curator arrays accept encoded role IDs; existing associations are retained when omitted thanks to the preservation step noted above. 【F:app/Http/Controllers/Api/ApiEventController.php†L281-L314】

## 4. Error semantics
- 403 when the authenticated user does not own the event. 422 when schedule slugs or category names cannot be resolved, or when validation rules for venue fields fail. 404 when the event ID is unknown. 【F:app/Http/Controllers/Api/ApiEventController.php†L231-L242】【F:app/Http/Controllers/Api/ApiEventController.php†L256-L299】

## 5. Create vs. update differences
- Creation infers venue/talent/curator defaults from the `{subdomain}` path parameter. Updates omit the subdomain and instead reuse the event's creator/linked roles, preserving existing associations unless new arrays are provided. 【F:app/Http/Controllers/Api/ApiEventController.php†L70-L220】【F:app/Http/Controllers/Api/ApiEventController.php†L231-L314】

## 6. Include/expand parameters
- No include/expand parameters are implemented; responses always return the full event payload through `toApiData()`. 【F:app/Http/Controllers/Api/ApiEventController.php†L316-L320】【F:app/Models/Event.php†L808-L876】

## 7. Versioning and rollout
- First introduction of `PATCH /api/events/{event_id}`; no version gating is present.

## Example requests (available today)
- **Create event:** `POST /api/events/{subdomain}` with `name`, `starts_at`, and venue/address/url per the creation validation rules. 【F:docs/MOBILE_EVENTS_API_GUIDE.md†L68-L160】
- **Update event:** `PATCH /api/events/{event_id}` with any editable fields (e.g., `{"name": "New title", "starts_at": "2024-05-01 20:00:00"}`); omitted venue/members/curators remain unchanged. 【F:app/Http/Controllers/Api/ApiEventController.php†L244-L320】
- **Upload/replace flyer:** `POST /api/events/flyer/{event_id}` to set or clear `flyer_image_id`, or upload a `flyer_image` file. 【F:app/Http/Controllers/Api/ApiEventController.php†L330-L395】
