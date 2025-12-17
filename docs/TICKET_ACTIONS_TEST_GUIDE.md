# Ticket Status Actions - Testing Guide

**Last Updated:** December 16, 2025  
**API Endpoint:** `PATCH /api/tickets/{sale_id}`

## Overview

This guide documents all ticket status change actions and provides test cases to verify each one is working correctly.

## All Available Actions

| Action | Input Status | Output Status | Description |
|--------|--------------|---------------|-------------|
| `mark_paid` | unpaid | paid | Mark an unpaid ticket as paid |
| `mark_unpaid` | paid, cancelled | unpaid | Mark a paid/cancelled ticket as unpaid |
| `refund` | paid | refunded | Refund a paid ticket |
| `cancel` | unpaid, paid | cancelled | Cancel an unpaid or paid ticket |
| `delete` | any | deleted (via is_deleted flag) | Soft delete a ticket |
| `mark_used` | any | (marks entries) | Mark all ticket entries as scanned/used |
| `mark_unused` | any | (clears entries) | Clear scanned flag from all entries |

## State Machine Diagram

```
pending → paid (mark_paid)
        ↘ cancelled (cancel)
        
unpaid → paid (mark_paid)
       → cancelled (cancel)

paid → unpaid (mark_unpaid)
    → refunded (refund)
    → cancelled (cancel)

cancelled → unpaid (mark_unpaid)

any status → deleted (delete)
```

## Test Cases

### Test 1: Mark Unpaid Ticket as Paid

**Precondition:** Ticket with status = "unpaid"

**Request:**
```bash
curl -X PATCH https://your-domain.com/api/tickets/123 \
  -H "X-API-Key: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "action": "mark_paid"
  }'
```

**Expected Response:**
```json
HTTP 200 OK
{
  "data": {
    "id": 123,
    "status": "paid"
  }
}
```

**Verify:** Check database - `sales` table, ticket 123 should have `status = 'paid'`

---

### Test 2: Mark Paid Ticket as Unpaid

**Precondition:** Ticket with status = "paid"

**Request:**
```bash
curl -X PATCH https://your-domain.com/api/tickets/123 \
  -H "X-API-Key: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "action": "mark_unpaid"
  }'
```

**Expected Response:**
```json
HTTP 200 OK
{
  "data": {
    "id": 123,
    "status": "unpaid"
  }
}
```

**Verify:** Check database - ticket 123 should have `status = 'unpaid'`

---

### Test 3: Cancel a Paid Ticket ⚠️ (This is what the iOS team is testing)

**Precondition:** Ticket with status = "paid"

**Request:**
```bash
curl -X PATCH https://your-domain.com/api/tickets/123 \
  -H "X-API-Key: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "action": "cancel"
  }'
```

**Expected Response:**
```json
HTTP 200 OK
{
  "data": {
    "id": 123,
    "status": "cancelled"
  }
}
```

**Verify:** Check database - ticket 123 should have `status = 'cancelled'`

---

### Test 4: Refund a Paid Ticket

**Precondition:** Ticket with status = "paid"

**Request:**
```bash
curl -X PATCH https://your-domain.com/api/tickets/123 \
  -H "X-API-Key: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "action": "refund"
  }'
```

**Expected Response:**
```json
HTTP 200 OK
{
  "data": {
    "id": 123,
    "status": "refunded"
  }
}
```

**Verify:** Check database - ticket 123 should have `status = 'refunded'`

---

### Test 5: Delete a Ticket (Soft Delete)

**Precondition:** Ticket with any status

**Request:**
```bash
curl -X PATCH https://your-domain.com/api/tickets/123 \
  -H "X-API-Key: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "action": "delete"
  }'
```

**Expected Response:**
```json
HTTP 200 OK
{
  "data": {
    "id": 123,
    "status": "paid"  // or whatever status it was
  }
}
```

**Verify:** Check database - ticket 123 should have `is_deleted = 1` (or true)

---

### Test 6: Mark Ticket Entries as Used

**Precondition:** Ticket with entries where `scanned_at = NULL`

**Request:**
```bash
curl -X PATCH https://your-domain.com/api/tickets/123 \
  -H "X-API-Key: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "action": "mark_used"
  }'
```

**Expected Response:**
```json
HTTP 200 OK
{
  "data": {
    "id": 123,
    "status": "paid"  // status unchanged
  }
}
```

**Verify:** Check database - all `sale_ticket_entries` records for this sale should have `scanned_at` populated with a timestamp

---

### Test 7: Mark Ticket Entries as Unused

**Precondition:** Ticket with entries where `scanned_at != NULL`

**Request:**
```bash
curl -X PATCH https://your-domain.com/api/tickets/123 \
  -H "X-API-Key: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "action": "mark_unused"
  }'
```

**Expected Response:**
```json
HTTP 200 OK
{
  "data": {
    "id": 123,
    "status": "paid"  // status unchanged
  }
}
```

**Verify:** Check database - all `sale_ticket_entries` records for this sale should have `scanned_at = NULL`

---

### Test 8: Invalid State Transition (Should Return Original Status)

**Precondition:** Ticket with status = "paid"

**Request:** Try to mark as paid (already paid)
```bash
curl -X PATCH https://your-domain.com/api/tickets/123 \
  -H "X-API-Key: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "action": "mark_paid"
  }'
```

**Expected Response:** HTTP 200 with status still "paid" (no state transition occurs)
```json
HTTP 200 OK
{
  "data": {
    "id": 123,
    "status": "paid"
  }
}
```

**Verify:** No database change - ticket should still be paid

---

## Database Verification Commands

### Check ticket status:
```sql
SELECT id, status, is_deleted, transaction_reference FROM sales WHERE id = 123;
```

### Check ticket entries (for mark_used/mark_unused):
```sql
SELECT id, sale_ticket_id, scanned_at FROM sale_ticket_entries WHERE sale_id = 123;
```

### Check audit logs (if audit logging is enabled):
```sql
SELECT * FROM audit_logs WHERE model_type = 'orders' AND model_id = 123 ORDER BY created_at DESC LIMIT 10;
```

---

## Implementation Details

### Code Location
- **API Controller:** `app/Http/Controllers/Api/ApiTicketController.php` (lines 80-150)
- **Web Controller:** `app/Http/Controllers/TicketController.php` (lines 1023-1160)

### How It Works

1. Request comes in to `PATCH /api/tickets/{sale_id}` with action in body
2. Validation checks that action is in allowed list
3. Authorization check ensures user owns the ticket
4. Based on action and current status, update is performed:
   - Status change is persisted to database via query builder
   - Model is re-fetched fresh from database to ensure correct status in response
5. Response contains the updated ticket data

### Key Fix (Dec 16, 2025)

**Problem:** Status updates were being persisted but the in-memory model wasn't being refreshed, causing responses to return old status values.

**Solution:** Changed from using `$sale->update()` + `$sale->refresh()` to using direct query builder `Sale::where('id', $id)->update()` followed by re-fetching with `$sale = Sale::findOrFail($id)`. This guarantees the response contains the actual database value.

---

## Troubleshooting

### Symptom: Status remains unchanged after action

**Check:**
1. Is the action name spelled correctly? (case-sensitive: `cancel`, not `Cancel`)
2. Is the current status valid for this action?
   - `mark_paid`: only works if status = "unpaid"
   - `mark_unpaid`: only works if status = "paid" or "cancelled"
   - `cancel`: works if status = "unpaid" or "paid"
   - `refund`: only works if status = "paid"
3. Did you clear application cache? (`php artisan optimize:clear`)
4. Check server logs for any errors

### Symptom: HTTP 422 validation error

**Check:**
1. Is the action in the allowed list?
2. Is the request body valid JSON?
3. Is Content-Type header set to `application/json`?

### Symptom: HTTP 403 Unauthorized

**Check:**
1. Is your API key valid?
2. Does the authenticated user own this ticket?
3. Is the user allowed to manage resources (`resources.manage` ability)?

---

## API Documentation Reference

Full documentation: [COMPLETE_API_REFERENCE.md](./COMPLETE_API_REFERENCE.md#patch-apiticketsale_id)

---

## Support

If tests are failing:
1. Verify your database has test data with correct statuses
2. Check that the code changes are deployed and cache is cleared
3. Review server logs for exceptions
4. Verify the Sale model has no custom observers that might prevent updates

