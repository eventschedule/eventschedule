# Tickets API Test Guide - iOS Integration Testing

## Quick Validation

### 1. Manual Test via cURL

```bash
# Set your API key
export API_KEY="your-api-key-here"
export BASE_URL="https://your-domain.com"

# Test 1: Get all tickets for managed events
curl -H "X-API-Key: $API_KEY" \
  "$BASE_URL/api/tickets" \
  -w "\nHTTP Status: %{http_code}\n"

# Expected: HTTP 200 with ticket data
```

### 2. Verify Response Structure

Look for this exact JSON structure in the response:

```json
{
    "data": [
        {
            "id": 123,                          // ✓ Sale ID
            "status": "paid",                   // ✓ Status enum
            "name": "John Doe",                 // ✓ Ticket holder name
            "email": "john@example.com",        // ✓ Ticket holder email
            "event_id": 5,                      // ✓ Event ID
            "event": {                          // ✓ Event object
                "id": "RVZFTlQtNQ==",
                "name": "Summer Concert",
                "slug": "summer-concert",
                "starts_at": "2025-07-20T19:00:00Z",
                "duration": 120,
                "timezone": "UTC",
                "tickets_enabled": true,
                "ticket_currency_code": "USD"
            },
            "tickets": [                        // ✓ Tickets array
                {
                    "id": 101,                  // Sale ticket line item ID
                    "ticket_id": 10,            // Ticket type ID
                    "quantity": 2,              // Number of tickets
                    "usage_status": "unused"    // or "used"
                }
            ]
        }
    ],
    "meta": {                                   // ✓ Pagination metadata
        "current_page": 1,
        "last_page": 1,
        "per_page": 50,
        "total": 1
    }
}
```

---

## Test Scenarios

### Scenario 1: Event Owner Retrieves Their Tickets

**Setup:**
- User Alice created Event "Jazz Night"
- 3 customers purchased tickets for this event

**Test:**
```bash
curl -H "X-API-Key: alice-api-key" \
  "$BASE_URL/api/tickets"
```

**Expected Result:**
- ✅ HTTP 200
- ✅ Returns 3 ticket sales
- ✅ All sales have `event_id` matching "Jazz Night"
- ✅ `meta.total` = 3

**Validation Script:**
```bash
RESPONSE=$(curl -s -H "X-API-Key: alice-api-key" \
  "$BASE_URL/api/tickets")

# Check status
TOTAL=$(echo $RESPONSE | jq '.meta.total')
if [ "$TOTAL" = "3" ]; then
  echo "✅ Scenario 1 PASSED: Returned 3 tickets"
else
  echo "❌ Scenario 1 FAILED: Expected 3 tickets, got $TOTAL"
fi

# Check data format
EVENT_ID=$(echo $RESPONSE | jq '.data[0].event_id')
echo "Event ID: $EVENT_ID"
```

---

### Scenario 2: Role Member Retrieves Event Tickets

**Setup:**
- User Bob is a member of "Downtown Venue" role
- Event "Summer Concert" is associated with "Downtown Venue"
- 5 customers purchased tickets for this event

**Test:**
```bash
curl -H "X-API-Key: bob-api-key" \
  "$BASE_URL/api/tickets"
```

**Expected Result:**
- ✅ HTTP 200
- ✅ Returns 5 ticket sales (even though Bob didn't purchase them)
- ✅ All sales have same `event_id`

**Validation:**
User Bob should have access even though he's not the event creator—he's a member of the venue role.

---

### Scenario 3: Filter by Event ID

**Setup:**
- User has managed 3 events with 10 total tickets

**Test:**
```bash
# Get only tickets for event 5
curl -H "X-API-Key: $API_KEY" \
  "$BASE_URL/api/tickets?event_id=5"
```

**Expected Result:**
- ✅ HTTP 200
- ✅ All returned sales have `event_id` = 5
- ✅ `meta.total` <= total tickets

**Validation Script:**
```bash
RESPONSE=$(curl -s -H "X-API-Key: $API_KEY" \
  "$BASE_URL/api/tickets?event_id=5")

# Verify all items have correct event_id
for event_id in $(echo $RESPONSE | jq -r '.data[].event_id'); do
  if [ "$event_id" != "5" ]; then
    echo "❌ FAILED: Got event_id=$event_id, expected 5"
    exit 1
  fi
done
echo "✅ All tickets filtered correctly"
```

---

### Scenario 4: Search by Name/Email

**Setup:**
- 10 tickets exist for managed event
- One ticket holder is "jane@example.com"

**Test:**
```bash
curl -H "X-API-Key: $API_KEY" \
  "$BASE_URL/api/tickets?query=jane"
```

**Expected Result:**
- ✅ HTTP 200
- ✅ Returns only tickets where name or email contains "jane"
- ✅ `meta.total` = 1 (or number matching search)

---

### Scenario 5: Authorization - Unauthorized Event

**Setup:**
- User Alice manages events 1, 2, 3
- Event 999 exists but is managed by User Bob

**Test:**
```bash
curl -H "X-API-Key: alice-api-key" \
  "$BASE_URL/api/tickets?event_id=999"
```

**Expected Result:**
- ❌ HTTP 403 Forbidden
- ✅ Response: `{"error": "Unauthorized"}`

**Validation Script:**
```bash
STATUS=$(curl -s -o /dev/null -w "%{http_code}" \
  -H "X-API-Key: alice-api-key" \
  "$BASE_URL/api/tickets?event_id=999")

if [ "$STATUS" = "403" ]; then
  echo "✅ Correctly denied access to unauthorized event"
else
  echo "❌ Expected 403, got $STATUS"
fi
```

---

### Scenario 6: Invalid API Key

**Setup:**
- API key is invalid or expired

**Test:**
```bash
curl -H "X-API-Key: invalid-key" \
  "$BASE_URL/api/tickets"
```

**Expected Result:**
- ❌ HTTP 401 Unauthorized
- ✅ Response: `{"error": "Invalid API key"}`

---

### Scenario 7: Pagination

**Setup:**
- User has 75 tickets across managed events
- Default pagination is 50 per page

**Test:**
```bash
# Page 1
curl -H "X-API-Key: $API_KEY" \
  "$BASE_URL/api/tickets?page=1"

# Page 2
curl -H "X-API-Key: $API_KEY" \
  "$BASE_URL/api/tickets?page=2&per_page=25"
```

**Expected Result:**
- ✅ Page 1: Returns 50 items, `meta.current_page` = 1, `meta.last_page` = 2
- ✅ Page 2: Returns 25 items, `meta.current_page` = 2, `meta.last_page` = 2

**Validation Script:**
```bash
RESPONSE=$(curl -s -H "X-API-Key: $API_KEY" \
  "$BASE_URL/api/tickets?page=1")

COUNT=$(echo $RESPONSE | jq '.data | length')
TOTAL=$(echo $RESPONSE | jq '.meta.total')
CURRENT=$(echo $RESPONSE | jq '.meta.current_page')
LAST=$(echo $RESPONSE | jq '.meta.last_page')

echo "Page 1: $COUNT items shown, $TOTAL total, page $CURRENT of $LAST"
```

---

## Ticket Status Values

The endpoint returns these valid status values. Verify they match expectations:

| Status | Meaning | Example |
|--------|---------|---------|
| `paid` | Payment received | Regular paid ticket |
| `unpaid` | Awaiting payment | Created but not paid |
| `pending` | Pending confirmation | Reserved but not confirmed |
| `cancelled` | Ticket cancelled | User or admin cancelled |
| `refunded` | Payment refunded | Originally paid, then refunded |
| `expired` | Reservation expired | Too long without payment |
| `deleted` | Ticket deleted | Marked as deleted |

**Test:**
```bash
curl -s -H "X-API-Key: $API_KEY" \
  "$BASE_URL/api/tickets" | jq '.data[].status' | sort | uniq
```

Should show valid statuses only.

---

## Usage Status Values

Individual tickets in sales have usage status indicating if they've been scanned:

```json
"usage_status": "unused"    // Not checked in yet
// or
"usage_status": "used"      // Checked in at event
```

**Test:**
```bash
curl -s -H "X-API-Key: $API_KEY" \
  "$BASE_URL/api/tickets" | jq '.data[].tickets[].usage_status'
```

Should show only `"used"` or `"unused"`.

---

## Response Time Test

**Objective:** Verify API responds quickly even with many tickets

```bash
time curl -H "X-API-Key: $API_KEY" \
  "$BASE_URL/api/tickets?per_page=100" \
  > /dev/null 2>&1
```

**Expected:** < 500ms for 100 tickets

---

## Complete Test Suite Script

Save this as `test-tickets-api.sh`:

```bash
#!/bin/bash

API_KEY="${1:-your-api-key}"
BASE_URL="${2:-https://localhost}"

echo "🧪 Tickets API Test Suite"
echo "API Key: ${API_KEY:0:10}..."
echo "Base URL: $BASE_URL"
echo ""

# Color codes
GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m'

test_count=0
pass_count=0

run_test() {
  local name="$1"
  local expected_status="$2"
  local url="$3"
  
  test_count=$((test_count + 1))
  
  response=$(curl -s -w "\n%{http_code}" \
    -H "X-API-Key: $API_KEY" \
    "$BASE_URL$url")
  
  status=$(echo "$response" | tail -1)
  body=$(echo "$response" | head -n -1)
  
  if [ "$status" = "$expected_status" ]; then
    echo -e "${GREEN}✅ $name${NC} (HTTP $status)"
    pass_count=$((pass_count + 1))
  else
    echo -e "${RED}❌ $name${NC} (Expected HTTP $expected_status, got $status)"
    echo "   Response: $(echo $body | jq -r '.error // .message // .' 2>/dev/null)"
  fi
}

# Run tests
run_test "Get all tickets" "200" "/api/tickets"
run_test "Filter by event_id" "200" "/api/tickets?event_id=1"
run_test "Search by query" "200" "/api/tickets?query=test"
run_test "Pagination" "200" "/api/tickets?page=1&per_page=25"

echo ""
echo "Results: $pass_count/$test_count tests passed"

if [ $pass_count -eq $test_count ]; then
  exit 0
else
  exit 1
fi
```

**Run it:**
```bash
chmod +x test-tickets-api.sh
./test-tickets-api.sh "your-api-key" "https://your-domain.com"
```

---

## iOS Swift Integration Example

Here's how the iOS team can decode the response:

```swift
import Foundation

struct TicketSale: Codable {
    let id: Int
    let status: String
    let name: String
    let email: String
    let eventId: Int
    let event: Event?
    let tickets: [Ticket]
    
    enum CodingKeys: String, CodingKey {
        case id, status, name, email
        case eventId = "event_id"
        case event, tickets
    }
}

struct Event: Codable {
    let id: String
    let name: String
    let slug: String
    let startsAt: String?
    let duration: Int?
    let timezone: String?
    
    enum CodingKeys: String, CodingKey {
        case id, name, slug, timezone, duration
        case startsAt = "starts_at"
    }
}

struct Ticket: Codable {
    let id: Int
    let ticketId: Int
    let quantity: Int
    let usageStatus: String
    
    enum CodingKeys: String, CodingKey {
        case id, quantity
        case ticketId = "ticket_id"
        case usageStatus = "usage_status"
    }
}

struct TicketsResponse: Codable {
    let data: [TicketSale]
    let meta: Pagination
}

struct Pagination: Codable {
    let currentPage: Int
    let lastPage: Int
    let perPage: Int
    let total: Int
    
    enum CodingKeys: String, CodingKey {
        case currentPage = "current_page"
        case lastPage = "last_page"
        case perPage = "per_page"
        case total
    }
}

// Usage
let urlString = "https://your-domain.com/api/tickets"
var request = URLRequest(url: URL(string: urlString)!)
request.setValue("your-api-key", forHTTPHeaderField: "X-API-Key")
request.setValue("application/json", forHTTPHeaderField: "Accept")

URLSession.shared.dataTask(with: request) { data, response, error in
    guard let data = data else {
        print("Network error:", error?.localizedDescription ?? "Unknown")
        return
    }
    
    do {
        let response = try JSONDecoder().decode(TicketsResponse.self, from: data)
        print("Got \(response.data.count) tickets")
        
        for sale in response.data {
            print("Sale \(sale.id): \(sale.name) - \(sale.status)")
            for ticket in sale.tickets {
                print("  Ticket: \(ticket.ticketId) x\(ticket.quantity) (\(ticket.usageStatus))")
            }
        }
    } catch {
        print("Decode error:", error)
    }
}.resume()
```

---

## Troubleshooting

### Problem: Still Getting Empty Results

**Checklist:**
- [ ] API key is valid (test with `/api/roles` endpoint)
- [ ] User has created events or is member of event roles
- [ ] Events have tickets sold
- [ ] API key belongs to correct user (not different team member)

**Debug:**
```bash
# Check if user has events
curl -H "X-API-Key: $API_KEY" \
  "$BASE_URL/api/events" | jq '.data | length'

# Check if user has roles
curl -H "X-API-Key: $API_KEY" \
  "$BASE_URL/api/roles" | jq '.data | length'
```

### Problem: Getting 403 Unauthorized

**Causes:**
- Event ID doesn't exist
- User doesn't manage that event
- Event is archived/deleted

**Solution:**
- Verify `event_id` parameter matches a managed event
- Use `/api/events` endpoint to list managed events first
- Remove `event_id` filter to see all tickets

### Problem: Slow Response

**Causes:**
- Large number of tickets
- Database query performance
- Network latency

**Solutions:**
- Use `event_id` filter to limit results
- Use `per_page` parameter with smaller value
- Paginate through results gradually
- Check server logs: `tail -f storage/logs/laravel.log | grep "tickets"`

---

## Before & After Comparison

### Old Behavior (Broken)

```bash
curl -H "X-API-Key: organizer-api-key" \
  "https://example.com/api/tickets"

# Only returned tickets purchased by this organizer
{
  "data": [],  // ❌ Empty even though they manage events with sales
  "meta": { "total": 0 }
}
```

### New Behavior (Fixed)

```bash
curl -H "X-API-Key: organizer-api-key" \
  "https://example.com/api/tickets"

# Now returns all tickets for events they manage
{
  "data": [
    {
      "id": 1,
      "name": "Customer Name",
      "email": "customer@example.com",
      "event_id": 5,
      // ...
    }
  ],
  "meta": { "total": 1 }  // ✅ Now has data
}
```

---

## Documentation Links

- [Full API Reference](./COMPLETE_API_REFERENCE.md)
- [Tickets Authorization Fix](./TICKETS_API_AUTHORIZATION_FIX.md)
- [Ticket Status Actions](./TICKET_ACTIONS_TEST_GUIDE.md)

