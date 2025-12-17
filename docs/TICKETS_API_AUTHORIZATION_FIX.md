# Tickets API Authorization Fix - December 17, 2025

## Issue Summary

The iOS app was receiving empty ticket results from `GET /api/tickets` despite tickets existing in the database. The backend endpoint was filtering by ticket **purchaser** (`user_id`) instead of filtering by **event manager** (event owner or role member).

### What Was Wrong

```php
// OLD CODE - Only returned tickets the user purchased
$sales = Sale::with(['event', 'saleTickets.ticket'])
    ->where('user_id', $user->id)  // ❌ Purchaser only
    ->where('is_deleted', false)
```

### What's Fixed Now

```php
// NEW CODE - Returns tickets for events the user manages
$managedEventIds = Event::where('user_id', $user->id)->pluck('id');
// + all event_role associations where user is a member
$sales = Sale::with(['event', 'saleTickets.ticket'])
    ->whereIn('event_id', $managedEventIds)
    ->where('is_deleted', false)
```

---

## API Endpoint Documentation

### GET /api/tickets

List all ticket sales for events the authenticated API key holder manages.

**Authentication:** `X-API-Key` header (required)

**Authorization:** User must own the event OR be a member of the event's associated roles (venue, curator, talent)

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `event_id` | integer | No | Filter by specific event ID. Returns 403 if user doesn't manage this event. |
| `query` | string | No | Search by ticket holder name or email (substring match) |
| `page` | integer | No | Pagination page number (default: 1) |
| `per_page` | integer | No | Results per page (default: 50) |

**Example Requests:**

```bash
# Get all tickets for managed events
curl -H "X-API-Key: h4m9FLpJjWpDfZ4FlOy4mFHANhKhIbGd" \
  "https://your-domain.com/api/tickets"

# Get tickets for specific event
curl -H "X-API-Key: h4m9FLpJjWpDfZ4FlOy4mFHANhKhIbGd" \
  "https://your-domain.com/api/tickets?event_id=5"

# Search by name or email
curl -H "X-API-Key: h4m9FLpJjWpDfZ4FlOy4mFHANhKhIbGd" \
  "https://your-domain.com/api/tickets?query=john@example.com"
```

**Success Response (200):**

```json
{
    "data": [
        {
            "id": 1,
            "status": "paid",
            "name": "John Doe",
            "email": "john@example.com",
            "event_id": 5,
            "event": {
                "id": "RVZFTlQtNQ==",
                "name": "Summer Concert",
                "slug": "summer-concert",
                "description": "An amazing concert",
                "starts_at": "2025-07-20T19:00:00Z",
                "duration": 120,
                "timezone": "UTC",
                "tickets_enabled": true,
                "ticket_currency_code": "USD"
            },
            "tickets": [
                {
                    "id": 101,
                    "ticket_id": 10,
                    "quantity": 2,
                    "usage_status": "unused"
                }
            ]
        }
    ],
    "meta": {
        "current_page": 1,
        "last_page": 1,
        "per_page": 50,
        "total": 1
    }
}
```

**Error Responses:**

| Status | Scenario | Response |
|--------|----------|----------|
| 401 | Missing/invalid API key | `{"error": "Invalid API key"}` |
| 403 | User doesn't manage the requested event | `{"error": "Unauthorized"}` |
| 404 | Ticket sale not found | `{"message": "Not Found"}` |

---

## Field Definitions

### Ticket Sale Object

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Unique sale/transaction ID |
| `status` | string | `"pending"` \| `"paid"` \| `"unpaid"` \| `"cancelled"` \| `"refunded"` \| `"expired"` \| `"deleted"` |
| `name` | string | Ticket holder's full name |
| `email` | string | Ticket holder's email address |
| `event_id` | integer | Associated event ID |
| `event` | object | Event details (optional, includes name, slug, times, etc.) |
| `tickets` | array | Individual tickets in this sale |

### Ticket Entry Object

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Sale ticket line item ID |
| `ticket_id` | integer | Ticket type/SKU ID |
| `quantity` | integer | Number of tickets of this type |
| `usage_status` | string | `"used"` \| `"unused"` |

---

## Authorization Model

The API uses role-based access control. A user with a given API key can access tickets for:

1. **Events they created** (event `user_id` matches API key's user)
2. **Events where they're a member** (user is member of event's venue/curator/talent roles)

### Authorization Decision Tree

```
User requests GET /api/tickets?event_id=5

Is event.user_id == API_user.id?
├─ YES → ✅ Return sales
└─ NO → Check roles
    
    User.roles().where(type ∈ [venue, curator, talent])
    |
    EventRole.where(role_id ∈ user.role_ids, event_id = 5)?
    ├─ YES → ✅ Return sales
    └─ NO → ❌ Return 403 Unauthorized
```

### Example: Multi-Role Manager

```
API User "Alice" has these roles:
- Owner of "Jazz Club" (venue)
- Member of "Downtown Curators" (curator)
- Talent "Alice Solo" (talent)

Events Alice can access tickets for:
✅ Events she created (user_id = Alice.id)
✅ Events at "Jazz Club" venue
✅ Events curated by "Downtown Curators"
✅ Events featuring "Alice Solo"
❌ Events she's not involved with
```

---

## Implementation Changes

### File Modified
- **Path:** `app/Http/Controllers/Api/ApiTicketController.php`
- **Methods Changed:** `index()`, `update()`
- **Lines Changed:** 20-107, 108-122

### What Changed

#### 1. index() Method - Authorization & Filtering

**Before:**
```php
$sales = Sale::with(['event', 'saleTickets.ticket'])
    ->where('user_id', $user->id)
    ->where('is_deleted', false)
    ->paginate(50);
```

**After:**
```php
// Get events the user owns
$managedEventIds = Event::where('user_id', $user->id)->pluck('id')->toArray();

// Get events where user is a member
$roleIds = $user->roles()->pluck('id')->toArray();
if (!empty($roleIds)) {
    $eventRoleIds = EventRole::whereIn('role_id', $roleIds)
        ->pluck('event_id')
        ->toArray();
    $managedEventIds = array_unique(array_merge($managedEventIds, $eventRoleIds));
}

// Query sales for managed events
$query = Sale::with(['event', 'saleTickets.ticket'])
    ->whereIn('event_id', $managedEventIds)
    ->where('is_deleted', false);

// Optional filtering
if ($request->filled('event_id')) {
    // Verify user manages this event
    if (in_array($eventId, $managedEventIds)) {
        $query->where('event_id', $eventId);
    } else {
        return response()->json(['error' => 'Unauthorized'], 403);
    }
}

if ($request->filled('query')) {
    $query->where(function ($q) use ($searchTerm) {
        $q->where('name', 'like', "%{$searchTerm}%")
          ->orWhere('email', 'like', "%{$searchTerm}%");
    });
}
```

#### 2. update() Method - Authorization

**Before:**
```php
if ($sale->user_id !== $request->user()->id) {
    return response()->json(['error' => 'Unauthorized'], 403);
}
```

**After:**
```php
$user = $request->user();
$isOwner = $sale->user_id === $user->id;
$isEventManager = $user->canEditEvent($sale->event);

if (!$isOwner && !$isEventManager) {
    return response()->json(['error' => 'Unauthorized'], 403);
}
```

Now event managers can update sale status (mark paid, refund, etc.) in addition to sale purchasers.

---

## Testing Checklist

### Prerequisites
- Ticket sales exist in database
- API key belongs to user who manages the events with those sales

### Test Cases

#### ✅ TC1: Retrieve All Tickets
```bash
curl -H "X-API-Key: $API_KEY" \
  "https://your-domain.com/api/tickets"
```
**Expected:** Returns array of tickets for all managed events

#### ✅ TC2: Filter by Event ID
```bash
curl -H "X-API-Key: $API_KEY" \
  "https://your-domain.com/api/tickets?event_id=5"
```
**Expected:** Returns only sales for event 5

#### ✅ TC3: Search by Name
```bash
curl -H "X-API-Key: $API_KEY" \
  "https://your-domain.com/api/tickets?query=john"
```
**Expected:** Returns sales where name contains "john"

#### ✅ TC4: Pagination
```bash
curl -H "X-API-Key: $API_KEY" \
  "https://your-domain.com/api/tickets?page=2&per_page=25"
```
**Expected:** Returns second page with 25 results per page

#### ✅ TC5: Authorization - Unauthorized Event
```bash
curl -H "X-API-Key: $API_KEY" \
  "https://your-domain.com/api/tickets?event_id=999"
```
**Expected:** Returns 403 if user doesn't manage event 999

#### ✅ TC6: Invalid API Key
```bash
curl -H "X-API-Key: invalid_key" \
  "https://your-domain.com/api/tickets"
```
**Expected:** Returns 401 Unauthorized

#### ✅ TC7: Update Sale Status (Event Manager)
```bash
curl -X PATCH \
  -H "X-API-Key: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{"action": "mark_paid"}' \
  "https://your-domain.com/api/tickets/123"
```
**Expected:** Event manager can update sale status (now authorized)

---

## Performance Considerations

### Query Optimization

The endpoint performs these database queries:

1. **Get owned events** (1 query)
   ```sql
   SELECT id FROM events WHERE user_id = ?
   ```

2. **Get user roles** (1 query)
   ```sql
   SELECT id FROM roles WHERE user_id = ? AND type IN ('venue', 'curator', 'talent')
   ```

3. **Get events from roles** (1 query if user has roles)
   ```sql
   SELECT DISTINCT event_id FROM event_role WHERE role_id IN (?, ?, ...)
   ```

4. **Get sales with eager loading** (1 query with relationships)
   ```sql
   SELECT * FROM sales 
   WHERE event_id IN (?, ?, ...) AND is_deleted = 0
   -- WITH event, saleTickets.ticket
   ```

### Optimization Tips for Large Datasets

- Use `event_id` parameter to filter to specific events
- Use `query` parameter to search by name/email
- Use `per_page` parameter to limit results (default 50, max recommended 100)
- Paginate through results rather than fetching all at once

---

## Backward Compatibility

⚠️ **Breaking Change:** The endpoint behavior has changed.

- **Previously:** Returned only tickets the API key holder **purchased**
- **Now:** Returns tickets for events the API key holder **manages**

**Migration Path:**
If clients were relying on the old behavior, they should:
1. Authenticate as the ticket purchaser (not the event manager)
2. Or contact support for per-user API key assignment

---

## Related Documentation

- [Complete API Reference](./COMPLETE_API_REFERENCE.md) - Full API documentation
- [Event Authorization](./authorization.md) - Authorization system overview
- [Ticket Actions](./TICKET_ACTIONS_TEST_GUIDE.md) - Ticket status action guide

---

## Support

For questions or issues:
1. Check that API key has `resources.view` ability
2. Verify user manages the events with desired tickets
3. Check error response status code and message
4. Enable API logs: `tail -f storage/logs/laravel.log | grep API`

