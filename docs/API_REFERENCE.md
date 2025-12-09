# REST API Reference

Event Schedule exposes a JSON API that mirrors the web application's scheduling, booking, and media features. All endpoints are now available to every account tier – no Pro subscription required – while still enforcing authentication and per-user authorization.

## Authentication

Provide the API key that is generated from **Settings → Integrations & API** with every request.

```
X-API-Key: <your-api-key>
Accept: application/json
Content-Type: application/json
```

### Rate limits & throttling

* **Per IP:** 60 requests per minute (HTTP 429 when exceeded).
* **Brute-force protection:** After 10 failed API key attempts the key is blocked for 15 minutes (HTTP 423).
* **Missing or invalid key:** HTTP 401 with an `error` message.

Example error payloads:

```json
{
  "error": "API key is required"
}
```

```json
{
  "error": "Invalid API key"
}
```

## Roles

### `GET /api/roles`
Lists the authenticated user's venues, curators, and talent schedules. Supports filtering by `type` (comma-separated list) and `name` substring. Results are paginated up to 1000 per page and include the role's encoded ID, contact info, styling, and groups. 【F:routes/api.php†L15-L18】【F:app/Http/Controllers/Api/ApiRoleController.php†L17-L57】

### `POST /api/roles`
Creates a new venue, curator, or talent owned by the requesting user. Requires the `resources.manage` ability and validates standard role fields (name, email, type, venue address when applicable). Contacts and groups can be provided and are persisted when present. 【F:routes/api.php†L15-L18】【F:app/Http/Controllers/Api/ApiRoleController.php†L59-L134】

### `DELETE /api/roles/{role_id}`
Deletes a venue, curator, or talent owned by the authenticated user. Talent deletion also removes any events where that talent is the only member. Non-schedule role types return 422. 【F:routes/api.php†L15-L18】【F:app/Http/Controllers/Api/ApiRoleController.php†L136-L165】

### `DELETE /api/roles/{role_id}/contacts/{contact}`
Removes a single contact entry by its zero-based index. Returns the updated role payload and a success message, or 404 if the contact index does not exist. 【F:routes/api.php†L15-L18】【F:app/Http/Controllers/Api/ApiRoleController.php†L167-L192】

## Schedules

### `GET /api/schedules`
Returns the authenticated user's venues, talents, curators, and other schedules.

#### Successful response (200)
```json
{
  "data": [
    {
      "id": "YmFzZTY0LWVuY29kZWQ=",
      "url": "https://eventschedule.test/sample-venue",
      "type": "venue",
      "subdomain": "sample-venue",
      "name": "Sample Venue",
      "timezone": "UTC",
      "groups": [
        {"id": "R1JPVVAtMQ==", "name": "Main Stage", "slug": "main-stage"}
      ],
      "contacts": [
        {"name": "Support", "email": "support@example.com", "phone": "+1234567890"}
      ]
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 100,
    "total": 1,
    "path": "https://eventschedule.test/api/schedules"
  }
}
```

#### Failure scenarios
* **401** – Missing or invalid API key.
* **423** – API key temporarily blocked after repeated failures.
* **429** – IP-level rate limit exceeded.

## Events

### `GET /api/events`
Lists all events owned by the authenticated user, including ticketing, scheduling, and media metadata.

#### Successful response (200)
```json
{
  "data": [
    {
      "id": "RVZFTlQtMQ==",
      "name": "API Showcase",
      "slug": "api-showcase",
      "description": "Showcase description",
      "description_html": "<p>Showcase description</p>",
      "starts_at": "2024-01-01 20:00:00",
      "duration": 120,
      "timezone": "UTC",
      "tickets_enabled": true,
      "ticket_currency_code": "USD",
      "tickets": [
        {"id": "VElDS0VULTE=", "type": "General Admission", "price": 2500, "quantity": 100}
      ],
      "members": {
        "Uk9MRS0y": {"name": "Performer", "email": null, "youtube_url": null}
      },
      "schedules": [
        {"id": "Uk9MRS0y", "name": "Performer", "type": "talent"},
        {"id": "Uk9MRS0z", "name": "The Club", "type": "venue"}
      ],
      "venue": {
        "id": "Uk9MRS0z",
        "type": "venue",
        "name": "The Club",
        "address1": null,
        "city": null
      },
      "flyer_image_url": "https://eventschedule.test/storage/flyers/api-showcase.png",
      "registration_url": "https://events.example.com/register",
      "event_url": "https://events.example.com/stream",
      "payment_method": null,
      "payment_instructions": "Pay at the door",
      "ticket_notes": "Bring your ticket"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 100,
    "total": 1
  }
}
```

#### Failure scenarios
* **401 / 423 / 429** – Same authentication and throttling responses as `/api/schedules`.

### `POST /api/events/{subdomain}`
Creates a new event attached to the provided schedule (talent, venue, or curator subdomain). Posting to `/api/events` without the trailing subdomain is not supported and will return HTTP 405 because the bare route is reserved for GET requests. 【F:routes/api.php†L14-L20】

To determine `{subdomain}`, use the `subdomain` field returned by `/api/schedules` (e.g., `sample-venue`). The subdomain is the schedule identifier used in public URLs and is required for event creation. Example request targeting a venue schedule:

```http
POST /api/events/sample-venue HTTP/1.1
X-API-Key: <your-api-key>
Accept: application/json
Content-Type: application/json

{
  "name": "API Showcase",
  "starts_at": "2024-04-01 20:00:00",
  "venue_id": "Uk9MRS0z",
  "category_name": "Concert",
  "members": [{"name": "Performer"}]
}
```

#### Required fields
* `name` – string
* `starts_at` – `Y-m-d H:i:s`
* At least one of `venue_id`, `venue_address1`, or `event_url`

Optional fields include `members`, `schedule` (sub-schedule slug), `category`, ticketing fields, payment instructions, etc. Category names are resolved automatically when `category_name` is supplied.

#### Successful response (201)
```json
{
  "data": {
    "id": "RVZFTlQtMg==",
    "name": "API Created Event",
    "starts_at": "2024-03-15 19:00:00",
    "tickets_enabled": false,
    "members": {
      "Uk9MRS0y": {"name": "Performer", "email": null, "youtube_url": null}
    }
  },
  "meta": {
    "message": "Event created successfully"
  }
}
```

#### Failure scenarios
* **401** – Missing API key.
* **403** – Authenticated user does not belong to the targeted subdomain.
* **404** – Schedule subdomain not found.
* **422** – Validation errors (invalid dates, missing venue details, unknown categories, etc.).

### `PATCH /api/events/{event_id}`
Updates an event owned by the authenticated user. The route enforces the `resources.manage` ability. Omit fields to keep existing values; venue, member, and curator relationships are preserved when their arrays are not provided. Supplying `members` or `curators` merges your payload with the existing lists so you can add, edit, or remove talent/curators without re-sending the full roster, and providing `venue_id` or venue fields will re-link the event to the chosen venue (or update the existing unclaimed venue details). 【F:routes/api.php†L16-L22】【F:app/Http/Controllers/Api/ApiEventController.php†L231-L316】

#### Minimal example
```http
PATCH /api/events/RVZFTlQtMg== HTTP/1.1
X-API-Key: <your-api-key>
Accept: application/json
Content-Type: application/json

{
  "name": "Updated title",
  "starts_at": "2024-05-01 20:00:00"
}
```

#### Validation rules
* `name` and `starts_at` are required **when present**; date format remains `Y-m-d H:i:s`.
* If you supply any of `venue_id`, `venue_address1`, or `event_url`, at least one must be present (same as creation).
* `members` and `curators` accept arrays of encoded role IDs; unknown schedule slugs or category names return 422. `members` entries may include optional `name`, `email`, or `youtube_url` fields to update unclaimed talent in place. 【F:app/Http/Controllers/Api/ApiEventController.php†L244-L316】

#### Successful response (200)
```json
{
  "data": {
    "id": "RVZFTlQtMg==",
    "name": "Updated title",
    "starts_at": "2024-05-01 20:00:00",
    "members": {
      "Uk9MRS0y": {"name": "Performer", "email": null, "youtube_url": null}
    },
    "venue": {"id": "Uk9MRS0z", "name": "The Club", "type": "venue"}
  },
  "meta": {
    "message": "Event updated successfully"
  }
}
```

#### Failure scenarios
* **403** – Authenticated user does not own the event.
* **404** – Unknown event ID.
* **422** – Validation errors for venue, schedule slug, or category lookups.

### `POST /api/events/flyer/{event_id}`
Uploads or swaps the flyer for an event you own. Supply either a multipart `flyer_image` file or a JSON payload containing `flyer_image_id` to reuse an existing upload.

#### Successful response (200)
```json
{
  "data": {
    "id": "RVZFTlQtMQ==",
    "flyer_image_url": "https://eventschedule.test/storage/flyers/api-showcase.png"
  },
  "meta": {
    "message": "Flyer uploaded successfully"
  }
}
```

#### Failure scenarios
* **401** – Missing API key.
* **403** – Event does not belong to the authenticated user.
* **404** – Event ID not found.
* **422** – Validation error (e.g., invalid `flyer_image_id`).

### `DELETE /api/events/{event_id}`
Deletes an event owned by the authenticated user. The route enforces the `resources.manage` ability (same as creation and update).

#### Successful response (200)
```json
{
  "meta": {
    "message": "Event deleted successfully"
  }
}
```

#### Failure scenarios
* **403** – Authenticated user does not own the event.
* **404** – Unknown event ID.
* **405** – Method not allowed when targeting `/api/events` without an ID.

## Error handling summary

| Status | Reason | Payload |
| ------ | ------ | ------- |
| 401 | Missing or invalid `X-API-Key` | `{ "error": "API key is required" }` or `{ "error": "Invalid API key" }` |
| 403 | User is not authorized for the requested resource | `{ "error": "Unauthorized" }` |
| 404 | Resource not found | `{ "message": "Not Found" }` |
| 422 | Validation failure | Standard Laravel validation error bag |
| 423 | API key temporarily blocked | `{ "error": "API key temporarily blocked" }` |
| 429 | Rate limit exceeded | `{ "error": "Rate limit exceeded" }` |

With these responses and the expanded payloads, every feature that is exposed in the Event Schedule UI—schedules, talent assignments, venue details, ticketing, payments, and media—can now be queried or created via the public REST API across all plan levels.
