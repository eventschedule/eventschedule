# Mobile event update clarifications

This memo summarizes the current backend behavior around updating events via the public API.

## 1. Update endpoint contract
- There is **no API route** registered for updating events. The only event write endpoints are `POST /api/events/{subdomain}` (create) and `POST /api/events/flyer/{event_id}` (flyer upload/replacement). 【F:routes/api.php†L16-L22】
- Because no update route exists, there is no supported method/path, no minimal JSON body, and partial updates are not available.

## 2. Role fields on update
- Not applicable—update is not implemented. Event creation sets `venue_id`, `members`, and curator membership automatically based on the subdomain and request body. 【F:app/Http/Controllers/Api/ApiEventController.php†L70-L213】

## 3. Validation rules for role IDs
- Not applicable for updates. Creation validates venue fields (`venue_id` or address/url) and resolves member role IDs within the authenticated user’s roles. 【F:app/Http/Controllers/Api/ApiEventController.php†L85-L213】

## 4. Error semantics
- The codebase does not surface field-specific errors for a nonexistent update route. Creation returns validation errors (HTTP 422) when schedule/group/category lookups fail and 403 when the authenticated user is not a member of the target subdomain. 【F:app/Http/Controllers/Api/ApiEventController.php†L72-L156】【F:app/Http/Controllers/Api/ApiEventController.php†L81-L83】

## 5. Create vs. update differences
- Only creation is supported via the API; there is no update payload to compare. Any differences that exist are between creation and flyer upload handling.

## 6. Include/expand parameters
- Not applicable for updates. Existing event endpoints do not implement `include` query handling. 【F:routes/api.php†L16-L22】【F:app/Http/Controllers/Api/ApiEventController.php†L19-L67】

## 7. Versioning and rollout
- There has been no route change from PUT/PATCH/POST for updates because an update route has not been added yet.

## Example requests (available today)
- **Create event:** `POST /api/events/{subdomain}` with `name`, `starts_at`, and venue/address/url per the creation validation rules. 【F:docs/MOBILE_EVENTS_API_GUIDE.md†L68-L160】
- **Upload/replace flyer:** `POST /api/events/flyer/{event_id}` to set or clear `flyer_image_id`, or upload a `flyer_image` file. 【F:app/Http/Controllers/Api/ApiEventController.php†L215-L289】

If an update endpoint is introduced in the future, its method, payload, and validation rules will need to be defined and documented; currently the API only supports creation and flyer upload for events.
