# API Endpoints Implementation Summary

This document outlines the new REST API endpoints added to the Laravel event management system.

## Overview

All endpoints use:
- **Authentication**: API key via `X-API-Key` header (handled by `ApiAuthentication` middleware)
- **Response Format**: JSON with snake_case keys
- **Base URL**: `/api/`
- **Authorization**: Resources require `resources.view` or `resources.manage` abilities

## 1. Talent/Performers API

### Endpoints

- **GET /api/talent** - List all talent
  - Returns: `{data: [...]}`
  - Ability: `resources.view`

- **GET /api/talent/{id}** - Get single talent
  - Returns: Talent object
  - Ability: `resources.view`

- **POST /api/talent** - Create talent
  - Request Body:
    ```json
    {
      "name": "string (required)",
      "email": "string (optional)",
      "phone": "string (optional)",
      "website": "url (optional)",
      "description": "string (optional)",
      "address1": "string (optional)",
      "address2": "string (optional)",
      "city": "string (optional)",
      "state": "string (optional)",
      "postal_code": "string (optional)",
      "country_code": "string (optional)",
      "timezone": "string (optional)"
    }
    ```
  - Returns: Talent object (201)
  - Ability: `resources.manage`

- **PUT /api/talent/{id}** - Update talent
  - Request Body: Same as POST (all fields optional)
  - Returns: Updated talent object
  - Ability: `resources.manage`

- **DELETE /api/talent/{id}** - Delete talent
  - Returns: `{message: "Talent deleted successfully"}`
  - Ability: `resources.manage`

### Response Format

```json
{
  "id": 1,
  "name": "Artist Name",
  "email": "artist@example.com",
  "phone": "+1234567890",
  "website": "https://example.com",
  "description": "Bio...",
  "address1": "123 Main St",
  "address2": "Suite 100",
  "city": "New York",
  "state": "NY",
  "postal_code": "10001",
  "country_code": "US",
  "timezone": "America/New_York",
  "subdomain": "artist-name",
  "created_at": "2025-01-01T00:00:00Z",
  "updated_at": "2025-01-01T00:00:00Z"
}
```

## 2. Venues API

### Endpoints

- **GET /api/venues** - List all venues
  - Returns: `{data: [...]}`
  - Ability: `resources.view`

- **GET /api/venues/{id}** - Get single venue
  - Returns: Venue object
  - Ability: `resources.view`

- **POST /api/venues** - Create venue
  - Request Body:
    ```json
    {
      "name": "string (required)",
      "email": "string (optional)",
      "phone": "string (optional)",
      "website": "url (optional)",
      "description": "string (optional)",
      "address1": "string (optional)",
      "address2": "string (optional)",
      "city": "string (optional)",
      "state": "string (optional)",
      "postal_code": "string (optional)",
      "country_code": "string (optional)",
      "timezone": "string (optional)",
      "formatted_address": "string (optional)",
      "geo_lat": "number (optional, -90 to 90)",
      "geo_lon": "number (optional, -180 to 180)"
    }
    ```
  - Returns: Venue object (201)
  - Ability: `resources.manage`

- **PUT /api/venues/{id}** - Update venue
  - Request Body: Same as POST (all fields optional)
  - Returns: Updated venue object
  - Ability: `resources.manage`

- **DELETE /api/venues/{id}** - Delete venue
  - Returns: `{message: "Venue deleted successfully"}`
  - Ability: `resources.manage`

### Response Format

```json
{
  "id": 1,
  "name": "Venue Name",
  "email": "venue@example.com",
  "phone": "+1234567890",
  "website": "https://example.com",
  "description": "Description...",
  "address1": "123 Main St",
  "address2": "Suite 100",
  "city": "New York",
  "state": "NY",
  "postal_code": "10001",
  "country_code": "US",
  "formatted_address": "123 Main St, New York, NY 10001",
  "geo_lat": 40.7128,
  "geo_lon": -74.0060,
  "timezone": "America/New_York",
  "subdomain": "venue-name",
  "created_at": "2025-01-01T00:00:00Z",
  "updated_at": "2025-01-01T00:00:00Z"
}
```

## 3. Check-ins API

### Endpoints

- **POST /api/checkins** - Record a check-in
  - Request Body:
    ```json
    {
      "event_id": 123,
      "attendee_name": "John Doe",
      "attendee_email": "john@example.com",
      "notes": "VIP guest"
    }
    ```
  - Returns: Check-in object (201)
  - Ability: `resources.manage`

- **GET /api/checkins?event_id={id}** - List check-ins for an event
  - Query Parameters:
    - `event_id` (required): Event ID
  - Returns: `{data: [...]}`
  - Ability: `resources.view`

### Response Format

```json
{
  "id": 1,
  "event_id": 123,
  "attendee_name": "John Doe",
  "attendee_email": "john@example.com",
  "checked_in_at": "2025-01-01T14:30:00Z",
  "notes": "VIP guest"
}
```

## 4. Enhanced Tickets API

### New Endpoints

- **POST /api/tickets/{id}/reassign** - Reassign ticket to new holder
  - Request Body:
    ```json
    {
      "new_holder_name": "Jane Smith",
      "new_holder_email": "jane@example.com"
    }
    ```
  - Returns:
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
  - Ability: `resources.manage`

- **POST /api/tickets/{id}/notes** - Add internal note to ticket
  - Request Body:
    ```json
    {
      "note": "Customer requested seat change"
    }
    ```
  - Returns:
    ```json
    {
      "message": "Note added successfully",
      "data": {
        "note": "Customer requested seat change",
        "created_at": "2025-01-01T14:30:00Z"
      }
    }
    ```
  - Ability: `resources.manage`

### Existing Tickets Endpoints

The existing `GET /api/tickets?event_id={id}&query={query}` endpoint already supports both direct array response and `{data: []}` wrapped response.

## Files Created/Modified

### New Files

1. **Models**:
   - `app/Models/CheckIn.php` - Check-in model

2. **Controllers**:
   - `app/Http/Controllers/Api/ApiTalentController.php` - Talent CRUD operations
   - `app/Http/Controllers/Api/ApiVenueController.php` - Venue CRUD operations
   - `app/Http/Controllers/Api/ApiCheckInController.php` - Check-in operations

3. **Migrations**:
   - `database/migrations/2027_12_15_000000_create_check_ins_table.php` - Check-ins table
   - `database/migrations/2027_12_15_000001_add_notes_to_sales_table.php` - Notes column for tickets

### Modified Files

1. **Controllers**:
   - `app/Http/Controllers/Api/ApiTicketController.php` - Added `reassign()` and `addNote()` methods

2. **Routes**:
   - `routes/api.php` - Added all new routes

## Database Migrations

Run the following command to create the necessary database tables:

```bash
php artisan migrate
```

This will:
1. Create the `check_ins` table with columns:
   - id, event_id, attendee_name, attendee_email, checked_in_at, notes, timestamps
2. Add `notes` column to the `sales` table for internal ticket notes

## Implementation Notes

### Talent & Venues
- Both use the existing `Role` model with `type` field set to 'talent' or 'venue'
- Automatically generates unique subdomain based on name
- Scoped to authenticated user (users can only see/manage their own talent/venues)

### Check-ins
- New `CheckIn` model for tracking event attendees
- Requires event ownership verification
- Timestamps when check-in occurred

### Tickets Enhancement
- Reassign: Updates ticket holder name and email
- Notes: Stores as JSON array in `notes` column with timestamp and user ID
- Maintains backwards compatibility with existing ticket endpoints

## Authentication

All endpoints require API key authentication via the `X-API-Key` header. The API key is stored in the `users` table (`api_key` column).

Example request:
```bash
curl -X GET https://your-domain.com/api/talent \
  -H "X-API-Key: your-api-key-here" \
  -H "Accept: application/json"
```

## Error Responses

All endpoints return standard HTTP status codes:
- 200: Success
- 201: Created
- 403: Unauthorized (no access to resource)
- 404: Not Found
- 422: Validation Error

Error response format:
```json
{
  "error": "Error message",
  "message": "Detailed error description"
}
```
