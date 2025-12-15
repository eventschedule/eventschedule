# EventSchedule Complete API Reference

**Version:** 2.0.0  
**Last Updated:** December 15, 2025

## Table of Contents

1. [Authentication](#authentication)
2. [Error Handling](#error-handling)
3. [Schedules](#schedules)
4. [Roles](#roles)
5. [Events](#events)
6. [Tickets & Sales](#tickets--sales)
7. [Talent/Performers](#talentperformers)
8. [Venues](#venues)
9. [Check-ins](#check-ins)
10. [Media Library](#media-library)
11. [Profile](#profile)
12. [OpenAPI/Swagger Specification](#openapi-specification)

---

## Authentication

All API endpoints require authentication using an API key. Generate your key from **Settings → Integrations & API** in the web interface.

### Headers

```http
X-API-Key: <your-api-key>
Accept: application/json
Content-Type: application/json
```

### Rate Limits & Security

- **Per IP:** 60 requests per minute (HTTP 429 when exceeded)
- **Brute-force protection:** After 10 failed API key attempts, the key is blocked for 15 minutes (HTTP 423)
- **Missing or invalid key:** HTTP 401 with error message

### Authorization

Endpoints enforce user-level authorization using Laravel's `ability` middleware:
- **`resources.view`** - Read-only access to user's resources
- **`resources.manage`** - Full CRUD access to user's resources

---

## Error Handling

All endpoints return standard HTTP status codes with JSON error payloads.

### Error Response Format

```json
{
  "error": "Error message",
  "message": "Detailed description"
}
```

### Status Codes

| Code | Reason | Example |
|------|--------|---------|
| 200 | OK | Successful GET/PATCH/DELETE |
| 201 | Created | Successful POST |
| 401 | Unauthorized | Missing or invalid API key |
| 403 | Forbidden | User lacks access to resource |
| 404 | Not Found | Resource doesn't exist |
| 422 | Validation Error | Invalid request data |
| 423 | Locked | API key temporarily blocked |
| 429 | Too Many Requests | Rate limit exceeded |

### Example Error Responses

**Missing API Key (401):**
```json
{
  "error": "API key is required"
}
```

**Invalid API Key (401):**
```json
{
  "error": "Invalid API key"
}
```

**Unauthorized Access (403):**
```json
{
  "error": "Unauthorized"
}
```

**Validation Error (422):**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "name": ["The name field is required."],
    "email": ["The email must be a valid email address."]
  }
}
```

---

## Schedules

Schedules represent the authenticated user's venues, talent, curators, and other roles.

### GET /api/schedules

List all schedules for the authenticated user.

**Ability:** `resources.view`

**Query Parameters:**
- `type` (optional) - Filter by type (comma-separated): `venue`, `curator`, `talent`
- `name` (optional) - Filter by name substring
- `per_page` (optional) - Results per page (max 1000, default 100)

**Example Request:**
```bash
curl -H "X-API-Key: $API_KEY" \
  "https://your-domain.com/api/schedules?type=venue&per_page=50"
```

**Success Response (200):**
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
        {
          "id": "R1JPVVAtMQ==",
          "name": "Main Stage",
          "slug": "main-stage"
        }
      ],
      "contacts": [
        {
          "name": "Support",
          "email": "support@example.com",
          "phone": "+1234567890"
        }
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

---

## Roles

Roles represent venues, curators, and talent. Use these endpoints to manage organizational entities.

### GET /api/roles

List roles owned by the authenticated user.

**Ability:** `resources.view`

**Query Parameters:**
- `type` (optional) - Filter by type (comma-separated): `venue`, `curator`, `talent`
- `name` (optional) - Filter by name substring
- `per_page` (optional) - Results per page (max 1000)

**Example Request:**
```bash
curl -H "X-API-Key: $API_KEY" \
  "https://your-domain.com/api/roles?type=venue,talent"
```

**Success Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "The Jazz Club",
      "type": "venue",
      "subdomain": "jazz-club",
      "email": "info@jazzclub.com",
      "phone": "+1234567890",
      "website": "https://jazzclub.com",
      "address1": "123 Music St",
      "city": "New York",
      "state": "NY",
      "postal_code": "10001",
      "timezone": "America/New_York"
    }
  ]
}
```

### POST /api/roles

Create a new role (venue, curator, or talent).

**Ability:** `resources.manage`

**Request Body:**
```json
{
  "type": "venue",
  "name": "Downtown Hall",
  "email": "info@downtownhall.com",
  "address1": "123 Main St",
  "city": "Springfield",
  "state": "IL",
  "postal_code": "62701",
  "timezone": "America/Chicago",
  "groups": ["Main Stage", "Side Room"],
  "contacts": [
    {
      "name": "Box Office",
      "email": "tickets@downtownhall.com",
      "phone": "+15551234567"
    }
  ]
}
```

**Required Fields:**
- `type` - One of: `venue`, `curator`, `talent`
- `name` - String
- `email` - Valid email
- `address1` - Required for venues

**Success Response (201):**
```json
{
  "id": 12,
  "name": "Downtown Hall",
  "type": "venue",
  "subdomain": "downtown-hall",
  "email": "info@downtownhall.com"
}
```

### PATCH /api/roles/{role_id}

Update an existing role.

**Ability:** `resources.manage`

**Request Body:**
```json
{
  "name": "Updated Venue Name",
  "phone": "+1555999888"
}
```

**Success Response (200):**
```json
{
  "id": 12,
  "name": "Updated Venue Name",
  "type": "venue"
}
```

### DELETE /api/roles/{role_id}

Delete a role. Deleting a talent also removes events where they are the only member.

**Ability:** `resources.manage`

**Success Response (200):**
```json
{
  "success": true,
  "message": "Role deleted successfully"
}
```

### DELETE /api/roles/{role_id}/contacts/{index}

Remove a contact from a role by its array index.

**Ability:** `resources.manage`

**Example:**
```bash
curl -X DELETE \
  -H "X-API-Key: $API_KEY" \
  "https://your-domain.com/api/roles/12/contacts/0"
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Contact removed successfully"
}
```

### POST /api/roles/{role_id}/members

Add a member to a role.

**Ability:** `resources.manage`

**Request Body:**
```json
{
  "email": "newmember@example.com",
  "role_label": "volunteer"
}
```

**Success Response (201):**
```json
{
  "id": 345,
  "user_id": 123,
  "email": "newmember@example.com",
  "role_label": "volunteer",
  "status": "invited"
}
```

### PATCH /api/roles/{role_id}/members/{member_id}

Update a role member.

**Ability:** `resources.manage`

**Request Body:**
```json
{
  "role_label": "admin",
  "status": "active"
}
```

### DELETE /api/roles/{role_id}/members/{member_id}

Remove a member from a role.

**Ability:** `resources.manage`

**Success Response (200):**
```json
{
  "success": true
}
```

---

## Events

Events are the core scheduling entities. They can be attached to venues, talent, or curators.

### GET /api/events

List all events owned by the authenticated user.

**Ability:** `resources.view`

**Query Parameters:**
- `per_page` (optional) - Results per page (default 100)

**Example Request:**
```bash
curl -H "X-API-Key: $API_KEY" \
  "https://your-domain.com/api/events?per_page=50"
```

**Success Response (200):**
```json
{
  "data": [
    {
      "id": "RVZFTlQtMQ==",
      "numeric_id": 1,
      "name": "Jazz Night",
      "slug": "jazz-night",
      "description": "An evening of smooth jazz",
      "starts_at": "2024-01-15T20:00:00Z",
      "ends_at": "2024-01-15T23:00:00Z",
      "duration": 180,
      "timezone": "America/New_York",
      "tickets_enabled": true,
      "ticket_currency_code": "USD",
      "payment_method": "stripe",
      "tickets": [
        {
          "id": "VElDS0VULTE=",
          "type": "General Admission",
          "price": 2500,
          "quantity": 100,
          "sold": 45
        }
      ],
      "members": {
        "Uk9MRS0y": {
          "name": "The Quartet",
          "email": "quartet@example.com",
          "youtube_url": null
        }
      },
      "venue": {
        "id": "Uk9MRS0z",
        "type": "venue",
        "name": "The Jazz Club",
        "address1": "123 Music St",
        "city": "New York"
      },
      "flyer_image_url": "https://cdn.example.com/flyers/jazz-night.jpg"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 100,
    "total": 25
  }
}
```

### GET /api/events/resources

Get a quick picker of all venues, curators, and talent for building event forms.

**Ability:** `resources.view`

**Success Response (200):**
```json
{
  "venues": [...],
  "curators": [...],
  "talent": [...],
  "meta": {
    "total_roles": 15
  }
}
```

### POST /api/events/{subdomain}

Create a new event under a schedule (identified by subdomain).

**Ability:** `resources.manage`

**URL Parameters:**
- `subdomain` - The subdomain of the venue/talent/curator (from `/api/schedules`)

**Request Body:**
```json
{
  "name": "Summer Concert",
  "starts_at": "2024-07-01 20:00:00",
  "timezone": "America/Los_Angeles",
  "venue_id": "Uk9MRS0z",
  "schedule": "main-stage",
  "category_name": "Concert",
  "description": "An outdoor summer concert",
  "duration": 180,
  "members": [
    {
      "name": "Headliner Band",
      "email": "booking@headliner.com"
    }
  ],
  "tickets_enabled": true,
  "ticket_currency_code": "USD",
  "tickets": [
    {
      "type": "General Admission",
      "price": 3500,
      "quantity": 200
    },
    {
      "type": "VIP",
      "price": 7500,
      "quantity": 50,
      "description": "Includes backstage pass"
    }
  ],
  "payment_method": "stripe",
  "event_url": "https://stream.example.com/summer"
}
```

**Required Fields:**
- `name` - String
- `starts_at` - Format: `Y-m-d H:i:s`
- `timezone` - IANA timezone (e.g., `America/New_York`)
- At least one of: `venue_id`, `venue_address1`, or `event_url`

**Optional Fields:**
- `description` - Event description
- `duration` - Duration in minutes
- `members` - Array of talent/performers
- `curators` - Array of curator IDs
- `schedule` - Sub-schedule slug
- `category_name` or `category_id` - Event category
- `tickets_enabled` - Boolean
- `tickets` - Array of ticket types
- `payment_method` - `cash`, `stripe`, `invoiceninja`, `payment_url`
- `payment_instructions` - Custom payment text
- `registration_url` - External registration link
- `event_url` - Online event URL
- `flyer_image_id` - Existing media asset ID

**Success Response (201):**
```json
{
  "data": {
    "id": "RVZFTlQtNQ==",
    "name": "Summer Concert",
    "starts_at": "2024-07-01T20:00:00Z",
    "tickets_enabled": true
  },
  "meta": {
    "message": "Event created successfully"
  }
}
```

### PATCH /api/events/{event_id}

Update an existing event.

**Ability:** `resources.manage`

**URL Parameters:**
- `event_id` - Encoded event ID

**Request Body:** (All fields optional)
```json
{
  "name": "Updated Event Name",
  "starts_at": "2024-07-01 21:00:00",
  "timezone": "America/Los_Angeles",
  "description": "Updated description",
  "members": [
    {
      "name": "New Performer"
    }
  ]
}
```

**Success Response (200):**
```json
{
  "data": {
    "id": "RVZFTlQtNQ==",
    "name": "Updated Event Name",
    "starts_at": "2024-07-01T21:00:00Z"
  },
  "meta": {
    "message": "Event updated successfully"
  }
}
```

### DELETE /api/events/{event_id}

Delete an event.

**Ability:** `resources.manage`

**Success Response (200):**
```json
{
  "meta": {
    "message": "Event deleted successfully"
  }
}
```

### POST /api/events/flyer/{event_id}

Upload or update an event flyer image.

**Ability:** `resources.view`

**Request Options:**

**Option 1: Upload new image (multipart/form-data)**
```bash
curl -X POST \
  -H "X-API-Key: $API_KEY" \
  -F "flyer_image=@/path/to/flyer.jpg" \
  "https://your-domain.com/api/events/flyer/RVZFTlQtNQ=="
```

**Option 2: Reuse existing media asset (JSON)**
```json
{
  "flyer_image_id": 42
}
```

**Success Response (200):**
```json
{
  "data": {
    "id": "RVZFTlQtNQ==",
    "flyer_image_url": "https://cdn.example.com/flyers/summer-concert.jpg"
  },
  "meta": {
    "message": "Flyer uploaded successfully"
  }
}
```

---

## Tickets & Sales

Manage ticket sales, scanning, and checkout processes.

### GET /api/tickets

List ticket sales for the authenticated user.

**Ability:** `resources.view`

**Query Parameters:**
- `event_id` (optional) - Filter by event ID
- `query` (optional) - Search by name or email

**Success Response (200):**
```json
{
  "data": [
    {
      "id": 123,
      "status": "paid",
      "name": "John Doe",
      "email": "john@example.com",
      "event_id": 1,
      "event": {
        "id": "RVZFTlQtMQ==",
        "name": "Jazz Night"
      },
      "tickets": [
        {
          "id": 456,
          "ticket_id": 789,
          "quantity": 2,
          "usage_status": "unused"
        }
      ]
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 50,
    "total": 234
  }
}
```

### PATCH /api/tickets/{sale_id}

Update a ticket sale (mark as paid, refund, cancel, delete, or update holder info).

**Ability:** `resources.manage`

**Request Body:**
```json
{
  "action": "mark_paid",
  "name": "Jane Doe",
  "email": "jane@example.com"
}
```

**Actions:**
- `mark_paid` - Mark sale as paid
- `refund` - Refund the sale
- `cancel` - Cancel the sale
- `delete` - Soft delete the sale

**Success Response (200):**
```json
{
  "data": {
    "id": 123,
    "status": "paid"
  }
}
```

### POST /api/tickets/{sale_id}/scan

Record a ticket scan at the event.

**Ability:** `resources.manage`

**Request Body:**
```json
{
  "sale_ticket_id": 456,
  "seat_number": "A12"
}
```

**Success Response (201):**
```json
{
  "data": {
    "entry_id": 789,
    "scanned_at": "2024-01-15T20:30:00Z"
  }
}
```

### POST /api/tickets/{sale_id}/checkout

Create a Stripe checkout session for a sale.

**Ability:** `resources.manage`

**Success Response (201):**
```json
{
  "data": {
    "url": "https://checkout.stripe.com/pay/cs_test_ABC123",
    "id": "cs_test_ABC123"
  }
}
```

### POST /api/events/{subdomain}/checkout

Create a new ticket sale for an event.

**Ability:** `resources.manage`

**Request Body:**
```json
{
  "name": "Jane Smith",
  "email": "jane@example.com",
  "event_date": "2024-07-01",
  "tickets": {
    "VElDS0VULTE=": 2,
    "VElDS0VULTI=": 1
  }
}
```

**Success Response (201):**
```json
{
  "data": {
    "id": "U0FMRS0xMjM=",
    "status": "pending",
    "payment_method": "stripe"
  }
}
```

### POST /api/tickets/{sale_id}/reassign

Reassign a ticket to a new holder.

**Ability:** `resources.manage`

**Request Body:**
```json
{
  "new_holder_name": "Jane Smith",
  "new_holder_email": "jane@example.com"
}
```

**Success Response (200):**
```json
{
  "message": "Ticket reassigned successfully",
  "data": {
    "id": 123,
    "new_holder_name": "Jane Smith",
    "new_holder_email": "jane@example.com"
  }
}
```

### POST /api/tickets/{sale_id}/notes

Add an internal note to a ticket.

**Ability:** `resources.manage`

**Request Body:**
```json
{
  "note": "Customer requested aisle seat"
}
```

**Success Response (201):**
```json
{
  "message": "Note added successfully",
  "data": {
    "note": "Customer requested aisle seat",
    "created_at": "2024-01-15T14:30:00Z"
  }
}
```

---

## Talent/Performers

Manage talent and performers separately from the roles system.

### GET /api/talent

List all talent owned by the authenticated user.

**Ability:** `resources.view`

**Success Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "The Jazz Quartet",
      "email": "booking@jazzquartet.com",
      "phone": "+1234567890",
      "website": "https://jazzquartet.com",
      "description": "Smooth jazz ensemble",
      "address1": "456 Music Ave",
      "city": "Nashville",
      "state": "TN",
      "postal_code": "37201",
      "country_code": "US",
      "timezone": "America/Chicago",
      "subdomain": "jazz-quartet",
      "created_at": "2024-01-01T00:00:00Z",
      "updated_at": "2024-01-15T10:30:00Z"
    }
  ]
}
```

### GET /api/talent/{id}

Get a single talent by ID.

**Ability:** `resources.view`

**Success Response (200):**
```json
{
  "id": 1,
  "name": "The Jazz Quartet",
  "email": "booking@jazzquartet.com",
  "description": "Smooth jazz ensemble",
  "subdomain": "jazz-quartet"
}
```

### POST /api/talent

Create new talent.

**Ability:** `resources.manage`

**Request Body:**
```json
{
  "name": "New Artist",
  "email": "artist@example.com",
  "phone": "+1555123456",
  "website": "https://newartist.com",
  "description": "Emerging talent in folk music",
  "address1": "789 Artist Lane",
  "city": "Austin",
  "state": "TX",
  "postal_code": "78701",
  "country_code": "US",
  "timezone": "America/Chicago"
}
```

**Required Fields:**
- `name` - String

**Success Response (201):**
```json
{
  "id": 2,
  "name": "New Artist",
  "email": "artist@example.com",
  "subdomain": "new-artist",
  "created_at": "2024-01-15T14:00:00Z"
}
```

### PUT /api/talent/{id}

Update existing talent.

**Ability:** `resources.manage`

**Request Body:** (All fields optional)
```json
{
  "name": "Updated Artist Name",
  "phone": "+1555999888"
}
```

**Success Response (200):**
```json
{
  "id": 2,
  "name": "Updated Artist Name",
  "phone": "+1555999888"
}
```

### DELETE /api/talent/{id}

Delete talent.

**Ability:** `resources.manage`

**Success Response (200):**
```json
{
  "message": "Talent deleted successfully"
}
```

---

## Venues

Manage venues independently from the roles system.

### GET /api/venues

List all venues owned by the authenticated user.

**Ability:** `resources.view`

**Success Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "The Grand Theater",
      "email": "info@grandtheater.com",
      "phone": "+1234567890",
      "website": "https://grandtheater.com",
      "description": "Historic theater downtown",
      "address1": "100 Main Street",
      "address2": "Suite 200",
      "city": "Boston",
      "state": "MA",
      "postal_code": "02101",
      "country_code": "US",
      "formatted_address": "100 Main Street, Suite 200, Boston, MA 02101",
      "geo_lat": 42.3601,
      "geo_lon": -71.0589,
      "timezone": "America/New_York",
      "subdomain": "grand-theater",
      "created_at": "2024-01-01T00:00:00Z",
      "updated_at": "2024-01-15T10:30:00Z"
    }
  ]
}
```

### GET /api/venues/{id}

Get a single venue by ID.

**Ability:** `resources.view`

**Success Response (200):**
```json
{
  "id": 1,
  "name": "The Grand Theater",
  "address1": "100 Main Street",
  "city": "Boston",
  "geo_lat": 42.3601,
  "geo_lon": -71.0589
}
```

### POST /api/venues

Create a new venue.

**Ability:** `resources.manage`

**Request Body:**
```json
{
  "name": "New Venue",
  "email": "contact@newvenue.com",
  "phone": "+1555789456",
  "website": "https://newvenue.com",
  "description": "Modern event space",
  "address1": "200 Event Plaza",
  "city": "Chicago",
  "state": "IL",
  "postal_code": "60601",
  "country_code": "US",
  "timezone": "America/Chicago",
  "geo_lat": 41.8781,
  "geo_lon": -87.6298
}
```

**Required Fields:**
- `name` - String

**Success Response (201):**
```json
{
  "id": 2,
  "name": "New Venue",
  "subdomain": "new-venue",
  "created_at": "2024-01-15T14:00:00Z"
}
```

### PUT /api/venues/{id}

Update an existing venue.

**Ability:** `resources.manage`

**Request Body:** (All fields optional)
```json
{
  "name": "Updated Venue Name",
  "phone": "+1555999777"
}
```

**Success Response (200):**
```json
{
  "id": 2,
  "name": "Updated Venue Name",
  "phone": "+1555999777"
}
```

### DELETE /api/venues/{id}

Delete a venue.

**Ability:** `resources.manage`

**Success Response (200):**
```json
{
  "message": "Venue deleted successfully"
}
```

---

## Check-ins

Track event attendee check-ins.

### POST /api/checkins

Record a check-in for an event.

**Ability:** `resources.manage`

**Request Body:**
```json
{
  "event_id": 123,
  "attendee_name": "John Doe",
  "attendee_email": "john@example.com",
  "notes": "VIP guest, early arrival"
}
```

**Required Fields:**
- `event_id` - Integer
- `attendee_name` - String

**Success Response (201):**
```json
{
  "id": 456,
  "event_id": 123,
  "attendee_name": "John Doe",
  "attendee_email": "john@example.com",
  "checked_in_at": "2024-01-15T20:15:00Z",
  "notes": "VIP guest, early arrival"
}
```

### GET /api/checkins

List check-ins for an event.

**Ability:** `resources.view`

**Query Parameters:**
- `event_id` (required) - Event ID

**Example Request:**
```bash
curl -H "X-API-Key: $API_KEY" \
  "https://your-domain.com/api/checkins?event_id=123"
```

**Success Response (200):**
```json
{
  "data": [
    {
      "id": 456,
      "event_id": 123,
      "attendee_name": "John Doe",
      "attendee_email": "john@example.com",
      "checked_in_at": "2024-01-15T20:15:00Z",
      "notes": "VIP guest"
    },
    {
      "id": 457,
      "event_id": 123,
      "attendee_name": "Jane Smith",
      "attendee_email": "jane@example.com",
      "checked_in_at": "2024-01-15T20:18:00Z",
      "notes": null
    }
  ]
}
```

---

## Media Library

Manage media assets for events and roles.

### GET /api/media

List media assets.

**Ability:** `resources.view`

**Success Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "filename": "event-banner.jpg",
      "url": "https://cdn.example.com/media/event-banner.jpg",
      "mime_type": "image/jpeg",
      "size": 245678,
      "variants": [
        {
          "label": "thumb",
          "url": "https://cdn.example.com/media/event-banner_thumb.jpg"
        }
      ],
      "tags": [
        {"id": 1, "name": "banners"},
        {"id": 3, "name": "events"}
      ]
    }
  ]
}
```

### POST /api/media

Upload a new media asset.

**Ability:** `resources.manage`

**Request (multipart/form-data):**
```bash
curl -X POST \
  -H "X-API-Key: $API_KEY" \
  -F "file=@/path/to/image.jpg" \
  -F "tags[]=1" \
  -F "tags[]=3" \
  "https://your-domain.com/api/media"
```

**Success Response (201):**
```json
{
  "id": 555,
  "filename": "image.jpg",
  "url": "https://cdn.example.com/media/image.jpg",
  "mime_type": "image/jpeg",
  "size": 123456
}
```

### DELETE /api/media/{asset}

Delete a media asset.

**Ability:** `resources.manage`

**Success Response (200):**
```json
{
  "success": true
}
```

### POST /api/media/{asset}/variant

Upload a variant (e.g., thumbnail) for an existing asset.

**Ability:** `resources.manage`

**Request (multipart/form-data):**
```bash
curl -X POST \
  -H "X-API-Key: $API_KEY" \
  -F "file=@/path/to/thumbnail.jpg" \
  -F "label=thumb" \
  "https://your-domain.com/api/media/555/variant"
```

**Success Response (201):**
```json
{
  "label": "thumb",
  "url": "https://cdn.example.com/media/image_thumb.jpg"
}
```

### GET /api/media/tags

List all media tags.

**Ability:** `resources.view`

**Success Response (200):**
```json
{
  "data": [
    {"id": 1, "name": "photos"},
    {"id": 2, "name": "logos"},
    {"id": 3, "name": "banners"}
  ]
}
```

### POST /api/media/tags

Create a new media tag.

**Ability:** `resources.manage`

**Request Body:**
```json
{
  "name": "flyers"
}
```

**Success Response (201):**
```json
{
  "id": 4,
  "name": "flyers"
}
```

### DELETE /api/media/tags/{tag}

Delete a media tag.

**Ability:** `resources.manage`

**Success Response (200):**
```json
{
  "success": true
}
```

### POST /api/media/{asset}/sync-tags

Sync tags for a media asset (replaces all existing tags).

**Ability:** `resources.manage`

**Request Body:**
```json
{
  "tags": [1, 3, 4]
}
```

**Success Response (200):**
```json
{
  "success": true
}
```

---

## Profile

Manage the authenticated user's profile.

### PATCH /api/profile

Update the user's profile.

**Ability:** `resources.manage`

**Request Body:**
```json
{
  "name": "John Smith",
  "email": "john.smith@example.com",
  "bio": "Event organizer and music enthusiast"
}
```

**Success Response (200):**
```json
{
  "id": 1,
  "name": "John Smith",
  "email": "john.smith@example.com",
  "bio": "Event organizer and music enthusiast"
}
```

### DELETE /api/profile

Delete the authenticated user's account.

**Ability:** `resources.manage`

**Request Body:**
```json
{
  "password": "user-password"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Account deleted successfully"
}
```

---

## OpenAPI Specification

### Complete OpenAPI/Swagger YAML

```yaml
openapi: 3.0.3
info:
  title: EventSchedule API
  version: 2.0.0
  description: >-
    Complete REST API for EventSchedule event management system.
    Includes schedules, roles, events, ticketing, talent, venues, check-ins, and media management.

servers:
  - url: https://your-domain.com/api
    description: Production server
  - url: http://localhost/api
    description: Local development

components:
  securitySchemes:
    ApiKeyAuth:
      type: apiKey
      in: header
      name: X-API-Key
      description: API key from Settings → Integrations & API

  schemas:
    Error:
      type: object
      properties:
        error:
          type: string
          example: "Invalid API key"
        message:
          type: string
          example: "Detailed error description"

    Talent:
      type: object
      properties:
        id:
          type: integer
          example: 1
        name:
          type: string
          example: "The Jazz Quartet"
        email:
          type: string
          example: "booking@jazzquartet.com"
        phone:
          type: string
          example: "+1234567890"
        website:
          type: string
          example: "https://jazzquartet.com"
        description:
          type: string
        address1:
          type: string
        city:
          type: string
        state:
          type: string
        postal_code:
          type: string
        country_code:
          type: string
        timezone:
          type: string
          example: "America/New_York"
        subdomain:
          type: string
          example: "jazz-quartet"
        created_at:
          type: string
          format: date-time
        updated_at:
          type: string
          format: date-time

    Venue:
      type: object
      properties:
        id:
          type: integer
        name:
          type: string
        email:
          type: string
        phone:
          type: string
        website:
          type: string
        description:
          type: string
        address1:
          type: string
        address2:
          type: string
        city:
          type: string
        state:
          type: string
        postal_code:
          type: string
        country_code:
          type: string
        formatted_address:
          type: string
        geo_lat:
          type: number
          format: float
          minimum: -90
          maximum: 90
        geo_lon:
          type: number
          format: float
          minimum: -180
          maximum: 180
        timezone:
          type: string
        subdomain:
          type: string
        created_at:
          type: string
          format: date-time
        updated_at:
          type: string
          format: date-time

    CheckIn:
      type: object
      properties:
        id:
          type: integer
        event_id:
          type: integer
        attendee_name:
          type: string
        attendee_email:
          type: string
        checked_in_at:
          type: string
          format: date-time
        notes:
          type: string

    TicketSale:
      type: object
      properties:
        id:
          type: integer
        status:
          type: string
          enum: [pending, paid, cancelled, refunded, expired]
        name:
          type: string
        email:
          type: string
        event_id:
          type: integer
        tickets:
          type: array
          items:
            type: object
            properties:
              id:
                type: integer
              ticket_id:
                type: integer
              quantity:
                type: integer
              usage_status:
                type: string

security:
  - ApiKeyAuth: []

paths:
  /schedules:
    get:
      summary: List schedules
      tags: [Schedules]
      parameters:
        - name: type
          in: query
          schema:
            type: string
          description: Filter by type (venue,curator,talent)
        - name: name
          in: query
          schema:
            type: string
          description: Filter by name substring
      responses:
        '200':
          description: Success
        '401':
          description: Unauthorized

  /talent:
    get:
      summary: List all talent
      tags: [Talent]
      responses:
        '200':
          description: Success
          content:
            application/json:
              schema:
                type: object
                properties:
                  data:
                    type: array
                    items:
                      $ref: '#/components/schemas/Talent'
    post:
      summary: Create talent
      tags: [Talent]
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              required: [name]
              properties:
                name:
                  type: string
                email:
                  type: string
                phone:
                  type: string
                website:
                  type: string
                description:
                  type: string
      responses:
        '201':
          description: Created
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/Talent'

  /talent/{id}:
    get:
      summary: Get single talent
      tags: [Talent]
      parameters:
        - name: id
          in: path
          required: true
          schema:
            type: integer
      responses:
        '200':
          description: Success
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/Talent'
    put:
      summary: Update talent
      tags: [Talent]
      parameters:
        - name: id
          in: path
          required: true
          schema:
            type: integer
      requestBody:
        content:
          application/json:
            schema:
              type: object
      responses:
        '200':
          description: Updated
    delete:
      summary: Delete talent
      tags: [Talent]
      parameters:
        - name: id
          in: path
          required: true
          schema:
            type: integer
      responses:
        '200':
          description: Deleted

  /venues:
    get:
      summary: List all venues
      tags: [Venues]
      responses:
        '200':
          description: Success
          content:
            application/json:
              schema:
                type: object
                properties:
                  data:
                    type: array
                    items:
                      $ref: '#/components/schemas/Venue'
    post:
      summary: Create venue
      tags: [Venues]
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              required: [name]
      responses:
        '201':
          description: Created

  /venues/{id}:
    get:
      summary: Get single venue
      tags: [Venues]
      parameters:
        - name: id
          in: path
          required: true
          schema:
            type: integer
      responses:
        '200':
          description: Success
    put:
      summary: Update venue
      tags: [Venues]
      parameters:
        - name: id
          in: path
          required: true
          schema:
            type: integer
      responses:
        '200':
          description: Updated
    delete:
      summary: Delete venue
      tags: [Venues]
      parameters:
        - name: id
          in: path
          required: true
          schema:
            type: integer
      responses:
        '200':
          description: Deleted

  /checkins:
    get:
      summary: List check-ins for an event
      tags: [Check-ins]
      parameters:
        - name: event_id
          in: query
          required: true
          schema:
            type: integer
      responses:
        '200':
          description: Success
          content:
            application/json:
              schema:
                type: object
                properties:
                  data:
                    type: array
                    items:
                      $ref: '#/components/schemas/CheckIn'
    post:
      summary: Record a check-in
      tags: [Check-ins]
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              required: [event_id, attendee_name]
              properties:
                event_id:
                  type: integer
                attendee_name:
                  type: string
                attendee_email:
                  type: string
                notes:
                  type: string
      responses:
        '201':
          description: Created
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/CheckIn'

  /tickets/{sale_id}/reassign:
    post:
      summary: Reassign ticket to new holder
      tags: [Tickets]
      parameters:
        - name: sale_id
          in: path
          required: true
          schema:
            type: integer
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              required: [new_holder_name, new_holder_email]
              properties:
                new_holder_name:
                  type: string
                new_holder_email:
                  type: string
      responses:
        '200':
          description: Success

  /tickets/{sale_id}/notes:
    post:
      summary: Add internal note to ticket
      tags: [Tickets]
      parameters:
        - name: sale_id
          in: path
          required: true
          schema:
            type: integer
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              required: [note]
              properties:
                note:
                  type: string
                  maxLength: 1000
      responses:
        '201':
          description: Created
```

---

## Quick Start Examples

### Authentication Example

```bash
# Set your API key
export API_KEY="your-api-key-here"

# Make authenticated request
curl -H "X-API-Key: $API_KEY" \
     -H "Accept: application/json" \
     "https://your-domain.com/api/schedules"
```

### Create Complete Event

```bash
curl -X POST "https://your-domain.com/api/events/my-venue" \
  -H "X-API-Key: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Summer Concert",
    "starts_at": "2024-07-01 20:00:00",
    "timezone": "America/New_York",
    "duration": 180,
    "description": "An evening of great music",
    "venue_id": "dmVudWUtMTIz",
    "members": [{"name": "The Band"}],
    "tickets_enabled": true,
    "ticket_currency_code": "USD",
    "tickets": [
      {"type": "General", "price": 2500, "quantity": 200}
    ]
  }'
```

### Track Check-ins

```bash
# Record check-in
curl -X POST "https://your-domain.com/api/checkins" \
  -H "X-API-Key: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "event_id": 123,
    "attendee_name": "John Doe",
    "attendee_email": "john@example.com"
  }'

# List check-ins
curl -H "X-API-Key: $API_KEY" \
  "https://your-domain.com/api/checkins?event_id=123"
```

---

## Support & Resources

- **Documentation:** This file and related docs in `/docs`
- **Route Definitions:** `routes/api.php`
- **Controllers:** `app/Http/Controllers/Api/`
- **Models:** `app/Models/`
- **Middleware:** `app/Http/Middleware/ApiAuthentication.php`

For additional help, refer to the Laravel documentation or contact support.

---

**Last Updated:** December 15, 2025  
**Version:** 2.0.0
