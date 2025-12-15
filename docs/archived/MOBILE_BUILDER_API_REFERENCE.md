# Mobile builder API reference and examples

This reference condenses the endpoints mobile clients need to build venue/curator/talent pickers, event creators, ticket flows, and flyer uploads. All routes live under `/api` and require the `X-API-Key` header.

## 1. Authentication and headers
- Generate/refresh the key in **Settings → Integrations & API** and send `X-API-Key` on every request.
- All non-upload requests expect JSON; flyer uploads accept multipart form data.
- Pagination defaults to 100 items; all list endpoints cap `per_page` at 1000. 【F:app/Http/Middleware/ApiAuthentication.php†L15-L59】【F:app/Http/Controllers/Api/ApiEventController.php†L19-L87】【F:app/Http/Controllers/Api/ApiRoleController.php†L14-L57】【F:app/Http/Controllers/Api/ApiScheduleController.php†L11-L47】

```http
X-API-Key: <your-api-key>
Accept: application/json
Content-Type: application/json
```

## 2. Roles: venues, curators, and talent
Use roles to populate venue/curator/talent selectors or create new records from the mobile app.

### List roles
`GET /api/roles?type=venue,curator,talent&name=<search>&per_page=200`

Filters by type and name substring; returns encoded IDs, contact info, styling, groups, and addresses for venues. 【F:app/Http/Controllers/Api/ApiRoleController.php†L17-L57】

```bash
curl -H "X-API-Key: $API_KEY" "https://example.test/api/roles?type=venue,talent&name=club"
```

### Create a role
`POST /api/roles`

Required: `type` (`venue|curator|talent`), `name`, `email`. Venues also need `address1`. Optional contacts and `groups` create sub-schedules automatically; hosted installs set Pro defaults while self-hosted marks email as verified. 【F:app/Http/Controllers/Api/ApiRoleController.php†L59-L134】

```bash
curl -X POST https://example.test/api/roles \
  -H "X-API-Key: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "venue",
    "name": "Downtown Hall",
    "email": "info@downtownhall.test",
    "address1": "123 Main St",
    "city": "Springfield",
    "timezone": "America/New_York",
    "groups": ["Main Stage", "Side Room"],
    "contacts": [{"name": "Box Office", "email": "tickets@downtownhall.test", "phone": "+15551234567"}]
  }'
```

### Delete a role
`DELETE /api/roles/{role_id}` — requires ownership; deleting a talent also removes events where they are the only member. 【F:app/Http/Controllers/Api/ApiRoleController.php†L136-L165】

```bash
curl -X DELETE -H "X-API-Key: $API_KEY" https://example.test/api/roles/Uk9MRS0z
```

### Remove a contact from a role
`DELETE /api/roles/{role_id}/contacts/{index}` — removes a single contact entry by its array index. 【F:app/Http/Controllers/Api/ApiRoleController.php†L167-L192】

```bash
curl -X DELETE -H "X-API-Key: $API_KEY" https://example.test/api/roles/Uk9MRS0z/contacts/0
```

## 3. Schedules
`GET /api/schedules?type=venue&name=<search>&per_page=200`

Returns the authenticated user's schedules (venues, talent, curators, and other roles) with URLs, contacts, and group metadata for sub-schedules. Use the `subdomain` field from this payload when creating events. 【F:app/Http/Controllers/Api/ApiScheduleController.php†L14-L47】

```bash
curl -H "X-API-Key: $API_KEY" "https://example.test/api/schedules?type=venue&per_page=50"
```

## 4. Event resources picker
`GET /api/events/resources`

Returns three arrays (`venues`, `curators`, `talent`) plus `meta.total_roles`, each serialized with `toApiData()` for rapid picker hydration. 【F:app/Http/Controllers/Api/ApiEventController.php†L29-L47】

```bash
curl -H "X-API-Key: $API_KEY" https://example.test/api/events/resources
```

## 5. Events

### List events
`GET /api/events?per_page=200`

Returns the authenticated user's events with members, schedules (including group pivot data), venue details, ticketing, payments, media, and category summary. Ideal for list/detail screens without extra lookups. 【F:app/Http/Controllers/Api/ApiEventController.php†L49-L88】【F:app/Models/Event.php†L808-L876】

```bash
curl -H "X-API-Key: $API_KEY" "https://example.test/api/events?per_page=200"
```

### Create an event (venue-owned)
`POST /api/events/{subdomain}`

`{subdomain}` is the venue/talent/curator subdomain from `/api/schedules`. Required: `name`, `starts_at`, and one of `venue_id`, `venue_address1`, or `event_url`. Venue subdomains auto-attach `venue_id`; talent subdomains auto-add the talent as a member; curator subdomains auto-add `curators`. Categories accept `category_name` or `category_id`; schedule group slugs resolve via `schedule`. 【F:routes/api.php†L14-L22】【F:app/Http/Controllers/Api/ApiEventController.php†L90-L241】

```bash
curl -X POST https://example.test/api/events/downtown-hall \
  -H "X-API-Key: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Mobile Showcase",
    "starts_at": "2024-07-01 20:00:00",
    "venue_id": "Uk9MRS0z",
    "schedule": "main-stage",
    "category_name": "Concert",
    "members": [{"name": "Headliner"}],
    "tickets_enabled": true,
    "ticket_currency_code": "USD",
    "tickets": [
      {"type": "General Admission", "price": 2500, "quantity": 150},
      {"type": "VIP", "price": 7500, "quantity": 25, "description": "Front row + lounge"}
    ],
    "payment_method": "card",
    "payment_instructions": "Pay at entry",
    "event_url": "https://stream.example.test"
  }'
```

### Update an event
`PATCH /api/events/{event_id}`

Partial updates keep existing venue/members/curators when those arrays are omitted and merge them when provided. Schedule slug and category name resolution mirror creation. Ownership is required. 【F:routes/api.php†L16-L22】【F:app/Http/Controllers/Api/ApiEventController.php†L243-L399】

```bash
curl -X PATCH https://example.test/api/events/RVZFTlQtMg== \
  -H "X-API-Key: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Mobile Showcase (updated)",
    "starts_at": "2024-07-01 21:00:00",
    "members": [{"name": "Headliner"}, {"name": "Guest"}],
    "curators": ["Q1VSQVRPUi0x"]
  }'
```

### Delete an event
`DELETE /api/events/{event_id}` — requires ownership; returns a success message on completion. 【F:routes/api.php†L20-L25】【F:app/Http/Controllers/Api/ApiEventController.php†L401-L415】

```bash
curl -X DELETE -H "X-API-Key: $API_KEY" https://example.test/api/events/RVZFTlQtMg==
```

### Upload or reuse a flyer
`POST /api/events/flyer/{event_id}`

- JSON: send `flyer_image_id` to reuse/remove an existing upload.
- Multipart: send `flyer_image` to upload a new image; previous file is deleted automatically.
- Requires event ownership. 【F:routes/api.php†L20-L25】【F:app/Http/Controllers/Api/ApiEventController.php†L418-L484】

```bash
# Reuse an existing flyer by ID
curl -X POST https://example.test/api/events/flyer/RVZFTlQtMg== \
  -H "X-API-Key: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{"flyer_image_id": 42}'

# Upload a new flyer image
curl -X POST https://example.test/api/events/flyer/RVZFTlQtMg== \
  -H "X-API-Key: $API_KEY" \
  -F "flyer_image=@/path/to/flyer.png"
```

## 6. Error handling quick look
- 401: missing/invalid key.
- 403: not an owner/member of the target resource.
- 404: unknown ID or subdomain.
- 422: validation failures (missing venue details, invalid schedule slug/category, etc.).
- 423: API key temporarily blocked after repeated failures.
- 429: rate limit exceeded.

All error payloads include an `error` or `message` field; validation errors return a Laravel error bag for field-level messaging. 【F:app/Http/Middleware/ApiAuthentication.php†L15-L59】【F:app/Http/Controllers/Api/ApiEventController.php†L90-L341】【F:app/Http/Controllers/Api/ApiRoleController.php†L59-L192】
