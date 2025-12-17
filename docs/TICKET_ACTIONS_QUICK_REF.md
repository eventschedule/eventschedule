# Ticket Status Actions - Quick Reference

## Endpoint
```
PATCH /api/tickets/{sale_id}
```

## All 7 Actions

### 1. Mark Paid
```json
{
  "action": "mark_paid"
}
```
**Valid when:** status = `unpaid`  
**Result:** status = `paid`

---

### 2. Mark Unpaid
```json
{
  "action": "mark_unpaid"
}
```
**Valid when:** status = `paid` OR `cancelled`  
**Result:** status = `unpaid`

---

### 3. Refund
```json
{
  "action": "refund"
}
```
**Valid when:** status = `paid`  
**Result:** status = `refunded`

---

### 4. Cancel
```json
{
  "action": "cancel"
}
```
**Valid when:** status = `unpaid` OR `paid`  
**Result:** status = `cancelled`

---

### 5. Delete (Soft Delete)
```json
{
  "action": "delete"
}
```
**Valid when:** any status  
**Result:** `is_deleted` flag set to true

---

### 6. Mark Used
```json
{
  "action": "mark_used"
}
```
**Valid when:** entries have `scanned_at = NULL`  
**Result:** All null `scanned_at` values set to current timestamp

---

### 7. Mark Unused
```json
{
  "action": "mark_unused"
}
```
**Valid when:** entries have `scanned_at != NULL`  
**Result:** All `scanned_at` values cleared (set to NULL)

---

## Optional Parameters

You can also update holder info:

```json
{
  "action": "mark_paid",
  "name": "Updated Name",
  "email": "newemail@example.com"
}
```

---

## State Machine Quick View

```
unpaid → mark_paid → paid
  ↓                    ↓
cancel → cancelled    refund → refunded
           ↓            ↓
           mark_unpaid
                ↑
           (any status)
           
(any status) → delete → is_deleted = true

(any status) → mark_used/mark_unused → (entries only, status unchanged)
```

---

## Response Format

### Success (200 OK)
```json
{
  "data": {
    "id": 123,
    "status": "paid"
  }
}
```

### Invalid State Transition (200 OK, no change)
```json
{
  "data": {
    "id": 123,
    "status": "paid"  // unchanged because action wasn't valid for this status
  }
}
```

### Validation Error (422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "action": ["The selected action is invalid."]
  }
}
```

### Not Authorized (403)
```json
{
  "error": "Unauthorized"
}
```

---

## Example Requests

### Mark a paid ticket as cancelled
```bash
curl -X PATCH https://api.eventschedule.app/api/tickets/42 \
  -H "X-API-Key: abc123def456" \
  -H "Content-Type: application/json" \
  -d '{
    "action": "cancel"
  }'
```

### Refund a paid ticket and update holder email
```bash
curl -X PATCH https://api.eventschedule.app/api/tickets/42 \
  -H "X-API-Key: abc123def456" \
  -H "Content-Type: application/json" \
  -d '{
    "action": "refund",
    "email": "refund@customer.com"
  }'
```

### Mark all ticket entries as used (checked in)
```bash
curl -X PATCH https://api.eventschedule.app/api/tickets/42 \
  -H "X-API-Key: abc123def456" \
  -H "Content-Type: application/json" \
  -d '{
    "action": "mark_used"
  }'
```

---

## Status Values

- `pending` - Awaiting payment
- `unpaid` - Not yet paid (manual entry)
- `paid` - Payment received
- `refunded` - Payment refunded
- `cancelled` - Cancelled by user
- `expired` - Past event date without payment

---

## Common Workflows

### Workflow 1: Approve Pending Payment
1. Create sale with status `pending`
2. Customer pays via Stripe (auto-sets to `paid`)
3. If manual approval needed: `mark_paid` action

### Workflow 2: Process Refund
1. Sale has status `paid`
2. Call `refund` action
3. Status becomes `refunded`
4. (You handle actual payment refund separately)

### Workflow 3: Cancel Order
1. Sale has status `unpaid` or `paid`
2. Call `cancel` action
3. Status becomes `cancelled`
4. If paid, follow with `refund` action if needed

### Workflow 4: Check In at Event
1. Sale has tickets with entries
2. Call `mark_used` action for entire sale
3. OR call scan endpoint for individual entries

---

## Debugging

**Status not changing?**
- Verify current status is valid for action
- Check API response - does it show correct status?
- Check database directly: `SELECT status FROM sales WHERE id = ?`
- Ensure you have `resources.manage` ability

**Getting validation error?**
- Check action name spelling (case-sensitive)
- Verify JSON format
- Check Content-Type header is `application/json`

**Getting 403 error?**
- Verify API key is valid
- Verify you own this ticket
- Verify user has `resources.manage` ability

---

## Migration from Old API

If you were using a different ticket status endpoint before, these are the action names:

| Old | New |
|-----|-----|
| `pay` | `mark_paid` |
| N/A | `mark_unpaid` |
| `refund` | `refund` |
| `void` | `cancel` |
| `delete` | `delete` |
| `scan` | `mark_used` |
| N/A | `mark_unused` |

---

**Updated:** December 16, 2025  
**API Version:** 2.0.0

