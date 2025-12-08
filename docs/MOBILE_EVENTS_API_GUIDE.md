# Mobile app guide: interacting with Events Schedule via API

This guide explains how a mobile client can integrate with the Event Schedule REST API to list schedules, fetch events, create events, and manage flyers. It summarizes authentication, rate limiting, request/response shapes, and edge cases surfaced by the API controllers.

## 1) Authentication and headers

- Generate an API key from **Settings → Integrations & API**; enabling the API creates a 32-character key and disabling it clears the key. 【F:app/Http/Controllers/Api/ApiSettingsController.php†L11-L36】
- Send the key on every request using the `X-API-Key` header. All endpoints also expect JSON unless you are uploading multipart form data.

```http
X-API-Key: <your-api-key>
Accept: application/json
Content-Type: application/json
```

### Rate limits and abuse controls
- 60 requests per minute per client IP; exceeding this returns HTTP 429 with `{"error":"Rate limit exceeded"}`. 【F:app/Http/Middleware/ApiAuthentication.php†L23-L59】
- Ten consecutive invalid key attempts block the key for 15 minutes (HTTP 423 with `API key temporarily blocked`). 【F:app/Http/Middleware/ApiAuthentication.php†L32-L59】
- Missing or invalid keys return HTTP 401 with an `error` message. 【F:app/Http/Middleware/ApiAuthentication.php†L15-L52】

## 2) Available endpoints

All API routes are namespaced under `/api` and require authentication via the middleware above. 【F:routes/api.php†L9-L17】

### GET `/api/schedules`
Returns paginated schedules (venues, talents, curators, etc.) owned by the authenticated user.

Query parameters:
- `per_page` — max 1000, default 100. 【F:app/Http/Controllers/Api/ApiScheduleController.php†L10-L24】
- `name` — optional substring match on schedule name. 【F:app/Http/Controllers/Api/ApiScheduleController.php†L18-L23】
- `type` — optional filter by schedule type (e.g., `venue`, `talent`). 【F:app/Http/Controllers/Api/ApiScheduleController.php†L18-L23】

Response payload includes `data` (array of schedules) and `meta` pagination details. Schedule objects expose IDs, URLs, time zone, contact info, and group metadata. 【F:app/Http/Controllers/Api/ApiScheduleController.php†L10-L37】【F:app/Models/Role.php†L667-L714】

### GET `/api/events`
Returns all events owned by the authenticated user with pagination.

Query parameters:
- `per_page` — max 1000, default 100. 【F:app/Http/Controllers/Api/ApiEventController.php†L19-L67】

Each event object contains:
- Identity and timing: encoded `id`, `slug`, `name`, `description` (Markdown and HTML), `starts_at`, `duration`, and event `timezone` (from the primary schedule). 【F:app/Models/Event.php†L808-L834】
- Location and schedules: `venue_id`, embedded `venue` object, and `schedules` showing all linked roles plus pivot data for group assignment and acceptance. 【F:app/Models/Event.php†L824-L873】
- Participants: `members` keyed by encoded talent IDs with names/emails/YouTube URLs. 【F:app/Models/Event.php†L843-L849】
- Tickets: `tickets_enabled`, currency/mode, ticket notes (plain and HTML), and ticket objects including price, quantity, and sold breakdowns. 【F:app/Models/Event.php†L826-L860】
- Links and media: guest `url`, `registration_url`, `event_url`, `payment_method`, `payment_instructions` (plain and HTML), `flyer_image_url`, and `category` summary. 【F:app/Models/Event.php†L815-L839】【F:app/Models/Event.php†L832-L836】
- Curator metadata: `curator_role` populated when the creator is a curator. 【F:app/Models/Event.php†L875-L876】

### POST `/api/events/{subdomain}`
Creates a new event attached to the schedule identified by `{subdomain}`. The subdomain can be a venue, talent, or curator; the API automatically populates related fields based on the schedule type. 【F:app/Http/Controllers/Api/ApiEventController.php†L70-L213】

Required inputs:
- `name` (string, ≤255) and `starts_at` (`Y-m-d H:i:s`). 【F:app/Http/Controllers/Api/ApiEventController.php†L85-L95】
- One of `venue_id`, `venue_address1`, or `event_url`. 【F:app/Http/Controllers/Api/ApiEventController.php†L85-L95】

Key behaviors and optional fields:
- If the subdomain is a venue, `venue_id` is auto-assigned. If it is a talent, that talent is auto-added to `members`; if it is a curator, the curator is added to `curators`. 【F:app/Http/Controllers/Api/ApiEventController.php†L72-L213】
- `members` array accepts talents; known talents belonging to the user are resolved to encoded IDs, while unknown entries are kept as-provided. 【F:app/Http/Controllers/Api/ApiEventController.php†L158-L207】
- Provide `schedule` to target a specific group slug within the schedule; the API resolves it to `current_role_group_id` or returns 422 if not found. 【F:app/Http/Controllers/Api/ApiEventController.php†L97-L107】
- Categories can be passed as `category_id` or human-friendly `category_name`; the latter is slug-matched against configured event types and fallbacks, otherwise returns 422. 【F:app/Http/Controllers/Api/ApiEventController.php†L108-L156】
- Venues can be linked by address/name if the user owns a matching venue; the API encodes the venue ID automatically. 【F:app/Http/Controllers/Api/ApiEventController.php†L162-L176】

Responses:
- **201** with `data` containing the full event payload plus `meta.message` when creation succeeds. 【F:app/Http/Controllers/Api/ApiEventController.php†L215-L220】
- **403** if the authenticated user is not a member of the targeted subdomain. 【F:app/Http/Controllers/Api/ApiEventController.php†L81-L83】
- **422** for validation errors (missing venue info, invalid schedule/group, unknown category, etc.). 【F:app/Http/Controllers/Api/ApiEventController.php†L97-L155】

### POST `/api/events/flyer/{event_id}`
Uploads, replaces, or removes an event flyer. Requires ownership of the event. 【F:app/Http/Controllers/Api/ApiEventController.php†L223-L289】

Options:
- Provide `flyer_image_id` (JSON) to reuse/remove an existing upload. Validation ensures the ID exists. 【F:app/Http/Controllers/Api/ApiEventController.php†L231-L250】
- Upload `flyer_image` (multipart/form-data) to store a new flyer; the API deletes any previous file when replacing. 【F:app/Http/Controllers/Api/ApiEventController.php†L253-L282】

Responses:
- **200** with updated event payload and a success message on upload or removal. 【F:app/Http/Controllers/Api/ApiEventController.php†L284-L289】
- **403** if the event is not owned by the requester. 【F:app/Http/Controllers/Api/ApiEventController.php†L225-L229】
- **404** if the event ID is unknown; **422** for invalid flyer IDs.

## 3) Error handling summary

The API returns structured errors with meaningful HTTP status codes:

| Status | Reason | Example payload |
| ------ | ------ | ---------------- |
| 401 | Missing or invalid `X-API-Key` | `{ "error": "API key is required" }` or `{ "error": "Invalid API key" }` |
| 403 | Unauthorized for the requested resource | `{ "error": "Unauthorized" }` |
| 404 | Resource not found | `{ "message": "Not Found" }` |
| 422 | Validation failure | Laravel validation error bag (e.g., unknown schedule/category) |
| 423 | API key temporarily blocked after repeated failures | `{ "error": "API key temporarily blocked" }` |
| 429 | Rate limit exceeded | `{ "error": "Rate limit exceeded" }` |

## 4) Mobile integration tips

- Cache the `per_page` limit and pagination metadata so the app can page through schedules/events efficiently. The events endpoint defaults to 100 items but supports up to 1000. 【F:app/Http/Controllers/Api/ApiEventController.php†L19-L67】
- When creating events, prefer sending `category_name` and `schedule` slugs for readability; the backend resolves them to IDs and surfaces clear 422 errors if unmatched. 【F:app/Http/Controllers/Api/ApiEventController.php†L97-L156】
- Use the embedded `schedules`, `members`, and `venue` objects from GET `/api/events` responses to hydrate list and detail screens without extra calls. 【F:app/Models/Event.php†L824-L873】【F:app/Models/Event.php†L843-L860】
- For flyer uploads, detect whether you can reuse an `flyer_image_id` to avoid re-uploading large assets; otherwise send multipart form data. 【F:app/Http/Controllers/Api/ApiEventController.php†L231-L282】
- Handle 401/423/429 responses with UI prompts (refresh API key, wait before retrying) to stay within the built-in abuse protections. 【F:app/Http/Middleware/ApiAuthentication.php†L15-L59】

